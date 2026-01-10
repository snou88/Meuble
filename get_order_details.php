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

    // If orders now store wilaya_id, resolve the wilaya name for display
    if (!empty($order['wilaya_id'])) {
        $wstmt = $db->prepare('SELECT name FROM wilayas WHERE id = ? LIMIT 1');
        $wstmt->execute([$order['wilaya_id']]);
        $wrow = $wstmt->fetch(PDO::FETCH_ASSOC);
        if ($wrow) $order['wilaya_name'] = $wrow['name'];
    }

    // Fetch items with joins
    $itStmt = $db->prepare(
        'SELECT oi.*, p.name AS product_name, pd.label AS size, oi.unit_price AS product_price,
            (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = oi.product_id ORDER BY pi.id LIMIT 1) AS product_image
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
        // If color ids are stored, resolve them
        if (!empty($it['tissu_color_id'])) {
            $tstmt = $db->prepare('SELECT color_code, color_name FROM product_colors WHERE id = ? LIMIT 1');
            $tstmt->execute([$it['tissu_color_id']]);
            $trow = $tstmt->fetch(PDO::FETCH_ASSOC);
            if ($trow) {
                $it['tissu_color_code'] = $trow['color_code'] ?? null;
                $it['tissu_color'] = $trow['color_name'] ?? null;
            }
        } elseif (!empty($it['tissu_color']) && !empty($it['product_id'])) {
            // legacy: resolve by name/code
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

        if (!empty($it['bois_color_id'])) {
            $bstmt = $db->prepare('SELECT color_code, color_name FROM product_colors WHERE id = ? LIMIT 1');
            $bstmt->execute([$it['bois_color_id']]);
            $brow = $bstmt->fetch(PDO::FETCH_ASSOC);
            if ($brow) {
                $it['bois_color_code'] = $brow['color_code'] ?? null;
                $it['bois_color'] = $brow['color_name'] ?? null;
            }
        } elseif (!empty($it['bois_color']) && !empty($it['product_id'])) {
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

    // mark order as seen (admin opened details)
    try {
        $u = $db->prepare('UPDATE orders SET is_seen = 1 WHERE id = ?');
        $u->execute([$id]);
    } catch (Exception $e) { /* ignore */ }

    // compute unseen orders count
    $unseenCount = 0;
    try {
        $cstmt = $db->query('SELECT COALESCE(COUNT(*),0) FROM orders WHERE is_seen = 0');
        $unseenCount = (int)$cstmt->fetchColumn();
    } catch (Exception $e) { /* ignore */ }

    $response = [
        'order' => $order,
        'items' => $items,
        'subtotal' => $subtotal,
        'delivery_price' => (float)($order['delivery_price'] ?? 0),
        'total_price' => (float)($order['total_price'] ?? 0),
        'unseen_count' => $unseenCount
    ];

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

exit;
