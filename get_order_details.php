<?php
require_once __DIR__ . '/config/database.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'Invalid id']);
    exit;
}
$id = (int)$_GET['id'];
try {
    $db = getDBConnection();
    $orderStmt = $db->prepare('SELECT * FROM orders WHERE id = ?');
    $orderStmt->execute([$id]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) {
        echo json_encode(['error' => 'Order not found']);
        exit;
    }

    // Fetch items with joins
    $itStmt = $db->prepare(
        'SELECT oi.*, p.name AS product_name, pd.label AS size, oi.unit_price AS product_price,
            (SELECT di.image_path FROM dimension_images di WHERE di.dimension_id = oi.dimension_id ORDER BY di.is_primary DESC, di.id LIMIT 1) AS product_image
         FROM order_items oi
         LEFT JOIN products p ON p.id = oi.product_id
         LEFT JOIN product_dimensions pd ON pd.id = oi.dimension_id
         WHERE oi.order_id = ?'
    );
    $itStmt->execute([$id]);
    $items = $itStmt->fetchAll(PDO::FETCH_ASSOC);

    // fallback if empty
    if (empty($items)) {
        // try to read from orders table directly
        $items = [];
        if (!empty($order['product_name'])) {
            $items[] = [
                'product_name' => $order['product_name'],
                'size' => $order['size'] ?? null,
                'quantity' => $order['quantity'] ?? 1,
                'product_price' => $order['product_price'] ?? 0,
                'product_image' => $order['product_image'] ?? null,
                'tissu_color' => $order['tissu_color'] ?? null,
                'bois_color' => $order['bois_color'] ?? null
            ];
        }
    }

    // Normalize colors: compute tissu_color_code and bois_color_code like dashboard helper
    foreach ($items as &$it) {
        $it['tissu_color_code'] = null;
        $it['bois_color_code'] = null;
        if (!empty($it['tissu_color']) && !empty($it['product_id'])) {
            $t = $it['tissu_color'];
            if (preg_match('/^#?[0-9A-Fa-f]{3,6}$/', $t)) {
                if ($t[0] !== '#') $t = '#'.$t;
                $it['tissu_color_code'] = $t;
            } else {
                $tstmt = $db->prepare('SELECT color_code FROM product_colors WHERE product_id = ? AND type = "tissu" AND (color_name = ? OR color_code = ?) LIMIT 1');
                $tstmt->execute([$it['product_id'], $it['tissu_color'], $it['tissu_color']]);
                $trow = $tstmt->fetch(PDO::FETCH_ASSOC);
                if ($trow && !empty($trow['color_code'])) $it['tissu_color_code'] = $trow['color_code'];
            }
        }
        if (!empty($it['bois_color']) && !empty($it['product_id'])) {
            $b = $it['bois_color'];
            if (preg_match('/^#?[0-9A-Fa-f]{3,6}$/', $b)) {
                if ($b[0] !== '#') $b = '#'.$b;
                $it['bois_color_code'] = $b;
            } else {
                $bstmt = $db->prepare('SELECT color_code FROM product_colors WHERE product_id = ? AND type = "bois" AND (color_name = ? OR color_code = ?) LIMIT 1');
                $bstmt->execute([$it['product_id'], $it['bois_color'], $it['bois_color']]);
                $brow = $bstmt->fetch(PDO::FETCH_ASSOC);
                if ($brow && !empty($brow['color_code'])) $it['bois_color_code'] = $brow['color_code'];
            }
        }
    }

    // Compute subtotal
    $subtotal = 0;
    foreach ($items as $it) $subtotal += (float)($it['product_price'] ?? ($it['unit_price'] ?? 0)) * (int)($it['quantity'] ?? 1);

    $response = [
        'order' => $order,
        'items' => $items,
        'subtotal' => $subtotal,
        'delivery_price' => (float)($order['delivery_price'] ?? 0),
        'total_price' => (float)($order['total_price'] ?? 0)
    ];

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

exit;
