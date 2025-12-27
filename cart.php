<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = null;
if ($method === 'POST') {
    $body = file_get_contents('php://input');
    $input = json_decode($body, true);
    if (!is_array($input)) {
        parse_str($body, $input);
    }
}

if (!$input || !isset($input['action'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$action = $input['action'];

function cart_count() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) return 0;
    $c = 0;
    foreach ($_SESSION['cart'] as $item) $c += (int)$item['qty'];
    return $c;
}

try {
    $db = getDBConnection();
    if ($action === 'add') {
        $productId = isset($input['productId']) ? (int)$input['productId'] : 0;
        $dimensionId = isset($input['dimensionId']) ? (int)$input['dimensionId'] : 0;
        $fabric = isset($input['fabric']) ? trim($input['fabric']) : null;
        $wood = isset($input['wood']) ? trim($input['wood']) : null;
        $qty = isset($input['qty']) ? max(1, (int)$input['qty']) : 1;

        if (!$productId || !$dimensionId) {
            echo json_encode(['success' => false, 'error' => 'Missing product or dimension']);
            exit;
        }

        // require both fabric and wood selections
        $missing = [];
        if (empty($fabric)) $missing[] = 'tissu';
        if (empty($wood)) $missing[] = 'bois';
        if (!empty($missing)) {
            $msg = 'Veuillez sélectionner ' . implode(' et ', $missing);
            echo json_encode(['success' => false, 'error' => $msg]);
            exit;
        }

        // fetch product and dimension details
        $p = $db->prepare('SELECT id, name FROM products WHERE id = :id');
        $p->execute([':id' => $productId]);
        $prod = $p->fetch();
        if (!$prod) throw new Exception('Product not found');

        $d = $db->prepare('SELECT id, label, price FROM product_dimensions WHERE id = :id AND product_id = :pid');
        $d->execute([':id' => $dimensionId, ':pid' => $productId]);
        $dim = $d->fetch();
        if (!$dim) throw new Exception('Dimension not found');

        // image for dimension
        $imgStmt = $db->prepare('SELECT image_path FROM dimension_images WHERE dimension_id = :did ORDER BY is_primary DESC, id LIMIT 1');
        $imgStmt->execute([':did' => $dimensionId]);
        $imgRow = $imgStmt->fetch();
        $image = $imgRow ? $imgRow['image_path'] : 'assets/images/default_product.jpg';

        $unitPrice = (float)$dim['price'];

        $key = $productId . '_' . $dimensionId . '_' . ($fabric ?: 'none') . '_' . ($wood ?: 'none');

        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['qty'] += $qty;
            $_SESSION['cart'][$key]['total_price'] = $_SESSION['cart'][$key]['qty'] * $_SESSION['cart'][$key]['unit_price'];
        } else {
            $_SESSION['cart'][$key] = [
                'key' => $key,
                'product_id' => $productId,
                'dimension_id' => $dimensionId,
                'name' => $prod['name'],
                'dimension_label' => $dim['label'],
                'fabric' => $fabric,
                'wood' => $wood,
                'unit_price' => $unitPrice,
                'qty' => $qty,
                'total_price' => $unitPrice * $qty,
                'image' => $image
            ];
        }

        echo json_encode(['success' => true, 'count' => cart_count(), 'cart' => array_values($_SESSION['cart'])]);
        exit;
    }

    if ($action === 'get') {
        echo json_encode(['success' => true, 'count' => cart_count(), 'cart' => isset($_SESSION['cart']) ? array_values($_SESSION['cart']) : []]);
        exit;
    }

    if ($action === 'remove') {
        $key = isset($input['key']) ? $input['key'] : null;
        if ($key && isset($_SESSION['cart'][$key])) {
            unset($_SESSION['cart'][$key]);
        }
        echo json_encode(['success' => true, 'count' => cart_count(), 'cart' => isset($_SESSION['cart']) ? array_values($_SESSION['cart']) : []]);
        exit;
    }

    if ($action === 'update') {
        $key = isset($input['key']) ? $input['key'] : null;
        $qty = isset($input['qty']) ? max(1, (int)$input['qty']) : null;
        if ($key && $qty !== null && isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['qty'] = $qty;
            $_SESSION['cart'][$key]['total_price'] = $_SESSION['cart'][$key]['unit_price'] * $qty;
        }
        echo json_encode(['success' => true, 'count' => cart_count(), 'cart' => isset($_SESSION['cart']) ? array_values($_SESSION['cart']) : []]);
        exit;
    }

    if ($action === 'checkout') {
        // expected fields: first_name, last_name, phone, wilaya, commune, address, delivery_type (optional)
        $first = isset($input['first_name']) ? trim($input['first_name']) : '';
        $last = isset($input['last_name']) ? trim($input['last_name']) : '';
        $phone = isset($input['phone']) ? trim($input['phone']) : '';
        // wilaya is expected to be the wilaya id (from select)
        $wilaya_id = isset($input['wilaya']) ? (int)$input['wilaya'] : 0;
        $commune = isset($input['commune']) ? trim($input['commune']) : '';
        $address = isset($input['address']) ? trim($input['address']) : '';
        $delivery_type = isset($input['delivery_type']) ? trim($input['delivery_type']) : 'standard';

        if (empty($first) || empty($last) || empty($phone) || $wilaya_id <= 0 || empty($commune) || empty($address)) {
            echo json_encode(['success' => false, 'error' => 'Informations client incomplètes']);
            exit;
        }

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || empty($_SESSION['cart'])) {
            echo json_encode(['success' => false, 'error' => 'Panier vide']);
            exit;
        }

        // compute totals
        $subtotal = 0;
        foreach ($_SESSION['cart'] as $it) $subtotal += (float)$it['total_price'];
        // Resolve wilaya delivery price from DB
        $delivery_price = 0;
        $wilaya_name = '';
        if ($subtotal > 0 && $wilaya_id > 0) {
            $wstmt = $db->prepare('SELECT name, domicile_price FROM wilayas WHERE id = :id AND is_active = 1');
            $wstmt->execute([':id' => $wilaya_id]);
            $wrow = $wstmt->fetch();
            if ($wrow && isset($wrow['domicile_price'])) {
                $delivery_price = (float)$wrow['domicile_price'];
                $wilaya_name = $wrow['name'];
            } else {
                // fallback to default delivery if wilaya not found
                $delivery_price = 1500;
            }
        }
        $total_price = $subtotal + $delivery_price;

        // insert order
        $orderStmt = $db->prepare('INSERT INTO orders (customer_name, customer_phone, customer_address, commune, wilaya_name, delivery_type, delivery_price, total_price, status) VALUES (:name, :phone, :address, :commune, :wilaya, :delivery_type, :delivery_price, :total_price, :status)');
        $customerName = $first . ' ' . $last;
        $orderStmt->execute([
            ':name' => $customerName,
            ':phone' => $phone,
            ':address' => $address,
            ':commune' => $commune,
            ':wilaya' => $wilaya_name,
            ':delivery_type' => $delivery_type,
            ':delivery_price' => $delivery_price,
            ':total_price' => $total_price,
            ':status' => 'pending'
        ]);

        $orderId = $db->lastInsertId();

        // insert order items
        $itemStmt = $db->prepare('INSERT INTO order_items (order_id, product_id, dimension_id, dimension_label, tissu_color, bois_color, unit_price, quantity, total_price) VALUES (:order_id, :product_id, :dimension_id, :dimension_label, :tissu_color, :bois_color, :unit_price, :quantity, :total_price)');
        foreach ($_SESSION['cart'] as $it) {
            $itemStmt->execute([
                ':order_id' => $orderId,
                ':product_id' => $it['product_id'],
                ':dimension_id' => $it['dimension_id'],
                ':dimension_label' => $it['dimension_label'],
                ':tissu_color' => $it['fabric'],
                ':bois_color' => $it['wood'],
                ':unit_price' => $it['unit_price'],
                ':quantity' => $it['qty'],
                ':total_price' => $it['total_price']
            ]);
        }

        // Clear cart
        unset($_SESSION['cart']);

        echo json_encode(['success' => true, 'order_id' => $orderId]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

exit;
