<?php
session_start();

// Handle success messages from session (for redirects)
if (isset($_SESSION['product_added_success'])) {
    $success_message = "Produit ajouté avec succès!";
    unset($_SESSION['product_added_success']);
}

// Debug: Check if any POST data is received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST request received");
    error_log("POST data: " . print_r($_POST, true));
}

// Database connection
try {
    // create PDO and set attributes in the constructor's 4th argument
    $pdo = new PDO(
        /* 'mysql:host=sql110.infinityfree.com;dbname=if0_40763827_ama;charset=utf8mb4', */
        'mysql:host=localhost;
        dbname=ama;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );

    // Keep $db for backward compatibility with the rest of your code
    $db = $pdo;
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}


// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Create uploads directory if it doesn't exist
$uploadDir = dirname(__DIR__) . '/images/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}



// (Removed site-settings / hero / categories / videos UI — not supported by simplified schema)

// Get all products (simplified schema from ama.sql)
$products = $db->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch dimensions and colors for each product
foreach ($products as &$product) {
    // Dimensions
    $dimStmt = $db->prepare("SELECT * FROM product_dimensions WHERE product_id = ? ORDER BY id");
    $dimStmt->execute([$product['id']]);
    $product['dimensions'] = $dimStmt->fetchAll(PDO::FETCH_ASSOC);

    // For each dimension, fetch images
    foreach ($product['dimensions'] as &$dim) {
        $imgStmt = $db->prepare("SELECT * FROM dimension_images WHERE dimension_id = ? ORDER BY is_primary DESC, id");
        $imgStmt->execute([$dim['id']]);
        $dim['images'] = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($dim);

    // Colors
    $colorStmt = $db->prepare("SELECT * FROM product_colors WHERE product_id = ? ORDER BY id");
    $colorStmt->execute([$product['id']]);
    $product['colors'] = $colorStmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($product);
// Legacy product image/gallery/sizes handling removed for simplified schema



// Gallery image handlers removed (not used in current schema)

// Handle product deletion
if (isset($_POST['delete_product'])) {
    try {
        $product_id = $_POST['product_id'];

        // Remove related dimension images from filesystem
        $imgStmt = $db->prepare("SELECT di.image_path FROM dimension_images di JOIN product_dimensions pd ON di.dimension_id = pd.id WHERE pd.product_id = ?");
        $imgStmt->execute([$product_id]);
        $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($images as $img) {
            if (!empty($img)) {
                $path = dirname(__DIR__) . '/' . $img;
                if (file_exists($path))
                    @unlink($path);
            }
        }

        // Delete product (cascades to dimensions, images and colors per schema)
        $db->prepare("DELETE FROM products WHERE id = ?")->execute([$product_id]);

        $success_message = "Produit supprimé avec succès!";
        // Refresh products list
        $products = $db->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        header("Location: dashboard.php#products");
        exit();
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la suppression du produit: " . $e->getMessage();
    }
}

// Handle product update
if (isset($_POST['update_product'])) {
    try {
        $product_id = (int) $_POST['product_id'];
        // Basic validation
        if (empty($_POST['name']) || empty($_POST['description'])) {
            throw new Exception('Le nom et la description sont requis.');
        }

        // Update basic product
        $pdo->prepare("UPDATE products SET name = ?, description = ? WHERE id = ?")->execute([
            trim($_POST['name']),
            trim($_POST['description']),
            $product_id
        ]);
        // Fetch existing images grouped by dimension index
        $existingImagesByDim = [];

        // assumes you fetch dimensions ordered same as edit UI
        $dimStmt = $pdo->prepare("
    SELECT pd.id AS dimension_id, di.id AS image_id, di.image_path
    FROM product_dimensions pd
    LEFT JOIN dimension_images di ON di.dimension_id = pd.id
    WHERE pd.product_id = ?
    ORDER BY pd.id ASC
");
        $dimStmt->execute([$product_id]);

        while ($row = $dimStmt->fetch(PDO::FETCH_ASSOC)) {
            $existingImagesByDim[] = $row;
        }
        /*         // Remove existing dimension images files and rows
                $imgStmt = $db->prepare("SELECT image_path FROM dimension_images di JOIN product_dimensions pd ON di.dimension_id = pd.id WHERE pd.product_id = ?");
                $imgStmt->execute([$product_id]);
                $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($images as $img) {
                    if (!empty($img)) {
                        $path = dirname(__DIR__) . '/' . $img;
                        if (file_exists($path))
                            @unlink($path);
                    }
                } */



        // Re-insert dimensions same as add_product logic
        $widths = $_POST['width_cm'] ?? [];
        $heights = $_POST['height_cm'] ?? [];
        $labels = $_POST['dim_label'] ?? [];
        $prices = $_POST['dim_price'] ?? [];
        $stocks = $_POST['dim_stock'] ?? [];


        $existingPosted = $_POST['dimension_existing_images'] ?? [];

        $examplePaths = [];

        foreach ($existingPosted as $group) {
            if (empty($group)) {
                $examplePaths[] = [];
                continue;
            }

            // Ensure integers to avoid injection
            $ids = array_map('intval', $group);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "SELECT Id, image_path FROM dimension_images WHERE Id IN ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($ids);

            $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $paths = [];
            foreach ($ids as $id) {
                $paths[] = isset($rows[$id]) ? $rows[$id] : null;
            }

            $examplePaths[] = $paths;
        }


        // Delete existing dimensions and colors (we'll re-insert)
        $pdo->prepare("DELETE FROM dimension_images WHERE dimension_id IN (SELECT id FROM product_dimensions WHERE product_id = ?)")->execute([$product_id]);
        $pdo->prepare("DELETE FROM product_dimensions WHERE product_id = ?")->execute([$product_id]);
        $pdo->prepare("DELETE FROM product_colors WHERE product_id = ?")->execute([$product_id]);
        print ('Existing Posted: ' . print_r($existingPosted, true));
        for ($i = 0; $i < count($labels); $i++) {
            $label = trim($labels[$i] ?? '');
            if ($label === '')
                continue;

            $width = (int) ($widths[$i] ?? 0);
            $height = (int) ($heights[$i] ?? 0);
            $price = (float) ($prices[$i] ?? 0);
            $stock = (int) ($stocks[$i] ?? 0);

            // Insert dimension
            $pdo->prepare("
        INSERT INTO product_dimensions (product_id, width_cm, height_cm, label, price, stock)
        VALUES (?, ?, ?, ?, ?, ?)
    ")->execute([$product_id, $width, $height, $label, $price, $stock]);

            $dimension_id = $pdo->lastInsertId();

            // ---------- KEEP EXISTING IMAGES ----------
            $keptImages = $examplePaths[$i] ?? [];
            $primarySet = false;

            foreach ($keptImages as $imgIdentifier) {
                echo "Keeping existing image: " . $imgIdentifier . "\n";
                $pdo->prepare("
            INSERT INTO dimension_images (dimension_id, image_path, is_primary)
            VALUES (?, ?, ?)
        ")->execute([
                            $dimension_id,
                            $imgIdentifier,
                            $primarySet ? 0 : 1
                        ]);
                $primarySet = true;
            }

            // ---------- HANDLE NEW UPLOADS ----------
            if (!empty($_FILES['dimension_images']['name'][$i])) {
                foreach ($_FILES['dimension_images']['name'][$i] as $j => $name) {
                    if (empty($_FILES['dimension_images']['tmp_name'][$i][$j]))
                        continue;

                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
                        continue;

                    $fileName = 'dim_' . uniqid() . '.' . $ext;
                    $filePath = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['dimension_images']['tmp_name'][$i][$j], $filePath)) {
                        $pdo->prepare("
                    INSERT INTO dimension_images (dimension_id, image_path, is_primary)
                    VALUES (?, ?, ?)
                ")->execute([
                                    $dimension_id,
                                    'images/' . $fileName,
                                    $primarySet ? 0 : 1
                                ]);
                        $primarySet = true;
                    }
                }
            }
        }
        // Cleanup unused files
        $allUsedImages = [];

        // Collect all images currently in DB
        $stmt = $pdo->query("SELECT image_path FROM dimension_images");
        $allUsedImages = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Compare against old images
        foreach ($existingImagesByDim as $old) {
            if (!in_array($old['image_path'], $allUsedImages)) {
                $fullPath = dirname(__DIR__) . '/' . $old['image_path'];
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }
        }
        // Re-insert colors
        if (isset($_POST['color_names']) && is_array($_POST['color_names'])) {
            $types = $_POST['color_types'] ?? [];
            $names = $_POST['color_names'];
            $codes = $_POST['color_codes'] ?? [];
            $mods = $_POST['price_modifiers'] ?? [];

            foreach ($names as $idx => $cname) {
                $cname = trim($cname);
                if ($cname === '')
                    continue;
                $ctype = $types[$idx] ?? 'tissu';
                $ccode = $codes[$idx] ?? null;
                $mod = !empty($mods[$idx]) ? (float) $mods[$idx] : 0;
                $pdo->prepare("INSERT INTO product_colors (product_id, type, color_name, color_code, price_modifier) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$product_id, $ctype, $cname, $ccode, $mod]);
            }
        }

        $success_message = "Produit mis à jour avec succès!";
        // Refresh products
        
        $products = $db->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        header("Location: dashboard.php#products");
        exit();
        
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la mise à jour du produit: " . $e->getMessage();
    } catch (Exception $e) {
        $error_message = "Erreur lors de la mise à jour du produit: " . $e->getMessage();
    }
}

// Category management removed (not supported with simplified schema)

// Handle order status update
if (isset($_POST['update_order_status'])) {
    try {
        $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$_POST['status'], $_POST['order_id']]);
        $success_message = "Statut de commande mis à jour avec succès!";
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la mise à jour du statut: " . $e->getMessage();
    }
}

// Handle product creation (simple schema)
if (isset($_POST['add_product'])) {
    try {
        if (empty($_POST['name']) || empty($_POST['description'])) {
            throw new Exception('Le nom et la description du produit sont requis.');
        }

        $stmt = $pdo->prepare("INSERT INTO products (name, description) VALUES (?, ?)");
        $stmt->execute([trim($_POST['name']), trim($_POST['description'])]);
        $product_id = $pdo->lastInsertId();

        // Dimensions
        $widths = $_POST['width_cm'] ?? [];
        $heights = $_POST['height_cm'] ?? [];
        $labels = $_POST['dim_label'] ?? [];
        $prices = $_POST['dim_price'] ?? [];
        $stocks = $_POST['dim_stock'] ?? [];

        for ($i = 0; $i < max(1, count($labels)); $i++) {
            $label = trim($labels[$i] ?? '');
            if ($label === '')
                continue;
            $width = (int) ($widths[$i] ?? 0);
            $height = (int) ($heights[$i] ?? 0);
            $price = !empty($prices[$i]) ? (float) $prices[$i] : 0;
            $stock = !empty($stocks[$i]) ? (int) $stocks[$i] : 0;

            $dstmt = $pdo->prepare("INSERT INTO product_dimensions (product_id, width_cm, height_cm, label, price, stock) VALUES (?, ?, ?, ?, ?, ?)");
            $dstmt->execute([$product_id, $width, $height, $label, $price, $stock]);
            $dimension_id = $pdo->lastInsertId();

            // Handle multiple images for this dimension (input name: dimension_images[INDEX][]) or single upload
            if (isset($_FILES['dimension_images'])) {
                $files = $_FILES['dimension_images'];
                if (isset($files['name'][$i]) && !empty($files['name'][$i])) {
                    if (is_array($files['name'][$i])) {
                        $firstInserted = false;
                        foreach ($files['name'][$i] as $j => $origName) {
                            if (empty($files['tmp_name'][$i][$j]))
                                continue;
                            $fileExt = pathinfo($origName, PATHINFO_EXTENSION);
                            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                            if (!in_array(strtolower($fileExt), $allowed))
                                continue;
                            $fileName = 'dim_' . uniqid() . '_' . $i . '_' . $j . '.' . $fileExt;
                            $filePath = $uploadDir . $fileName;
                            if (move_uploaded_file($files['tmp_name'][$i][$j], $filePath)) {
                                $imgPath = 'images/' . $fileName;
                                $isPrimary = $firstInserted ? 0 : 1;
                                $pdo->prepare("INSERT INTO dimension_images (dimension_id, image_path, is_primary) VALUES (?, ?, ?)")->execute([$dimension_id, $imgPath, $isPrimary]);
                                $firstInserted = true;
                            }
                        }
                    } else {
                        if (!empty($files['tmp_name'][$i])) {
                            $fileExt = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                            if (in_array(strtolower($fileExt), $allowed)) {
                                $fileName = 'dim_' . uniqid() . '_' . $i . '.' . $fileExt;
                                $filePath = $uploadDir . $fileName;
                                if (move_uploaded_file($files['tmp_name'][$i], $filePath)) {
                                    $imgPath = 'images/' . $fileName;
                                    $pdo->prepare("INSERT INTO dimension_images (dimension_id, image_path, is_primary) VALUES (?, ?, ?)")->execute([$dimension_id, $imgPath, 1]);
                                }
                            }
                        }
                    }
                }
            }
        }

        // Colors
        if (isset($_POST['color_names']) && is_array($_POST['color_names'])) {
            $types = $_POST['color_types'] ?? [];
            $names = $_POST['color_names'];
            $codes = $_POST['color_codes'] ?? [];
            $mods = $_POST['price_modifiers'] ?? [];

            foreach ($names as $idx => $cname) {
                $cname = trim($cname);
                if ($cname === '')
                    continue;
                $ctype = $types[$idx] ?? 'tissu';
                $ccode = $codes[$idx] ?? null;
                $mod = !empty($mods[$idx]) ? (float) $mods[$idx] : 0;
                $pdo->prepare("INSERT INTO product_colors (product_id, type, color_name, color_code, price_modifier) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$product_id, $ctype, $cname, $ccode, $mod]);
            }
        }

        $_SESSION['product_added_success'] = true;
        header("Location: dashboard.php#products");
        exit();
    } catch (PDOException $e) {
        $error_message = "Erreur lors de l'ajout du produit: " . $e->getMessage();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Handle order deletion
if (isset($_POST['delete_order'])) {
    try {
        $order_id = (int) $_POST['order_id'];

        /*         // Get order data to delete receipt file if exists
                $order = $db->prepare("SELECT payment_receipt FROM orders WHERE id = ?");
                $order->execute([$order_id]);
                $orderData = $order->fetch(PDO::FETCH_ASSOC); */


        // Delete order items first (if they exist)
        $db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$order_id]);

        // Delete the order
        $stmt = $db->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);

        /*         // Delete receipt file if exists
                if ($orderData && !empty($orderData['payment_receipt'])) {
                    $receiptPath = dirname(__DIR__) . '/' . $orderData['payment_receipt'];
                    if (file_exists($receiptPath)) {
                        @unlink($receiptPath);
                    }
                } */

        $success_message = "Commande supprimée avec succès!";

        // Refresh orders list
        if (!empty($_GET['search_phone'])) {
            $searchPhone = trim($_GET['search_phone']);
            $stmt = $db->prepare("SELECT * FROM orders WHERE status != 'archived' AND status != 'canceled' AND customer_phone LIKE ? ORDER BY order_date DESC");
            $stmt->execute(['%' . $searchPhone . '%']);
            $all_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $all_orders = $db->query("SELECT * FROM orders WHERE status != 'archived' AND status != 'canceled' ORDER BY order_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        }

        // Refresh stats
        $total_orders = $db->query("SELECT COALESCE(COUNT(*), 0) FROM orders WHERE  status != 'canceled'")->fetchColumn();
        $total_revenue = $db->query("SELECT COALESCE(SUM(total_price - delivery_price), 0) FROM orders WHERE  status != 'canceled' AND status != 'pending'")->fetchColumn();
        $pending_orders = $db->query("SELECT COALESCE(COUNT(*), 0) FROM orders WHERE status = 'pending'")->fetchColumn();
        $recent_orders = $db->query("SELECT * FROM orders WHERE status != 'archived' AND status != 'canceled' ORDER BY order_date DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $error_message = "Erreur lors de la suppression de la commande: " . $e->getMessage();
    }
}

// Get stats for dashboard
$total_orders = $db->query("SELECT COALESCE(COUNT(*), 0) FROM orders WHERE status != 'canceled'")->fetchColumn();
$total_revenue = $db->query("SELECT COALESCE(SUM(total_price - delivery_price), 0) FROM orders WHERE status != 'canceled' AND status != 'pending'")->fetchColumn();
$pending_orders = $db->query("SELECT COALESCE(COUNT(*), 0) FROM orders WHERE status = 'pending'")->fetchColumn();

// Get recent orders (last 5)
$recent_orders = $db->query("SELECT * FROM orders WHERE status != 'archived' AND status != 'canceled' ORDER BY order_date DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
// Get all orders
$searchPhone = isset($_GET['search_phone']) ? trim($_GET['search_phone']) : '';
if ($searchPhone !== '') {
    $stmt = $db->prepare("SELECT * FROM orders WHERE status != 'archived' AND status != 'canceled' AND customer_phone LIKE ? ORDER BY order_date DESC");
    $stmt->execute(['%' . $searchPhone . '%']);
    $all_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $all_orders = $db->query("SELECT * FROM orders  WHERE status != 'archived' AND status != 'canceled' ORDER BY order_date DESC")->fetchAll(PDO::FETCH_ASSOC);
}

if ($searchPhone !== '') {
    $stmt = $db->prepare("SELECT * FROM orders WHERE customer_phone LIKE ? AND status = 'archived' OR status = 'canceled' ORDER BY order_date DESC");
    $stmt->execute(['%' . $searchPhone . '%']);
    $all_archives = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $all_archives = $db->query("SELECT * FROM orders WHERE status = 'archived' OR status = 'canceled' ORDER BY order_date DESC")->fetchAll(PDO::FETCH_ASSOC);
}

// Helper to get order items
function getOrderItems($db, $order_id)
{
    // Join order_items with products and dimensions to get consistent fields
    $stmt = $db->prepare(
        "SELECT oi.*, p.name AS product_name, pd.label AS size, oi.unit_price AS product_price,
            (SELECT di.image_path FROM dimension_images di WHERE di.dimension_id = oi.dimension_id ORDER BY di.is_primary DESC, di.id LIMIT 1) AS product_image
         FROM order_items oi
         LEFT JOIN products p ON p.id = oi.product_id
         LEFT JOIN product_dimensions pd ON pd.id = oi.dimension_id
         WHERE oi.order_id = ?"
    );
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /*     // If no items found, fall back to legacy orders table format
        if (empty($items)) {
            $orderStmt = $db->prepare("SELECT product_id, product_name, product_price, product_image, size, quantity, color FROM orders WHERE id = ?");
            $orderStmt->execute([$order_id]);
            $orderData = $orderStmt->fetch(PDO::FETCH_ASSOC);
            if ($orderData) {
                $items = [$orderData];
            }
        } */

    // Normalize each item to ensure expected keys exist
    foreach ($items as &$item) {
        // Ensure product name
        if (empty($item['product_name']) && !empty($item['product_id'])) {
            $pstmt = $db->prepare('SELECT name FROM products WHERE id = ?');
            $pstmt->execute([$item['product_id']]);
            $pn = $pstmt->fetchColumn();
            if ($pn)
                $item['product_name'] = $pn;
        }

        // Ensure size label
        if (empty($item['size']) && !empty($item['dimension_id'])) {
            $dstmt = $db->prepare('SELECT label FROM product_dimensions WHERE id = ?');
            $dstmt->execute([$item['dimension_id']]);
            $dl = $dstmt->fetchColumn();
            if ($dl)
                $item['size'] = $dl;
        }

        // Map unit price
        if (!isset($item['product_price']) && isset($item['unit_price'])) {
            $item['product_price'] = $item['unit_price'];
        }

        // Map quantity
        if (!isset($item['quantity']) && isset($item['qty'])) {
            $item['quantity'] = $item['qty'];
        }

        // Normalize tissu and bois colors separately and try to resolve hex codes
        $item['tissu_color_code'] = null;
        $item['bois_color_code'] = null;

        if (!empty($item['tissu_color']) && !empty($item['product_id'])) {
            $t = $item['tissu_color'];
            // if already looks like a code, normalize
            if (preg_match('/^#?[0-9A-Fa-f]{3,6}$/', $t)) {
                if ($t[0] !== '#')
                    $t = '#' . $t;
                $item['tissu_color_code'] = $t;
            } else {
                $tstmt = $db->prepare('SELECT color_code FROM product_colors WHERE product_id = ? AND type = "tissu" AND (color_name = ? OR color_code = ?) LIMIT 1');
                $tstmt->execute([$item['product_id'], $item['tissu_color'], $item['tissu_color']]);
                $trow = $tstmt->fetch(PDO::FETCH_ASSOC);
                if ($trow && !empty($trow['color_code']))
                    $item['tissu_color_code'] = $trow['color_code'];
            }
        }

        if (!empty($item['bois_color']) && !empty($item['product_id'])) {
            $b = $item['bois_color'];
            if (preg_match('/^#?[0-9A-Fa-f]{3,6}$/', $b)) {
                if ($b[0] !== '#')
                    $b = '#' . $b;
                $item['bois_color_code'] = $b;
            } else {
                $bstmt = $db->prepare('SELECT color_code FROM product_colors WHERE product_id = ? AND type = "bois" AND (color_name = ? OR color_code = ?) LIMIT 1');
                $bstmt->execute([$item['product_id'], $item['bois_color'], $item['bois_color']]);
                $brow = $bstmt->fetch(PDO::FETCH_ASSOC);
                if ($brow && !empty($brow['color_code']))
                    $item['bois_color_code'] = $brow['color_code'];
            }
        }

        // Ensure product_image exists (already selected in query), else try to get one
        if (empty($item['product_image']) && !empty($item['product_id'])) {
            $imgStmt = $db->prepare('SELECT di.image_path FROM dimension_images di JOIN product_dimensions pd ON di.dimension_id = pd.id WHERE pd.product_id = ? ORDER BY di.is_primary DESC, di.id LIMIT 1');
            $imgStmt->execute([$item['product_id']]);
            $img = $imgStmt->fetchColumn();
            if ($img)
                $item['product_image'] = $img;
        }
    }

    return $items;
}

// Function to get product dimensions
function getProductDimensions($db, $product_id)
{
    $stmt = $db->prepare("SELECT * FROM product_dimensions WHERE product_id = ? ORDER BY id");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get product colors
function getProductColors($db, $product_id)
{
    $colors = $db->prepare("SELECT * FROM product_colors WHERE product_id = ? ORDER BY id");
    $colors->execute([$product_id]);
    return $colors->fetchAll(PDO::FETCH_ASSOC);
}
?>