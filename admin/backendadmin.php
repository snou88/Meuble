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

// Migration: ensure customer_messages has is_read column (automatic, idempotent)
try {
    $col = $pdo->query("SHOW COLUMNS FROM customer_messages LIKE 'is_read'")->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE customer_messages ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0");
    }
} catch (Exception $e) {
    // If the table doesn't exist yet or other error, ignore - the dump handles schema
}

// Migration: ensure orders has is_seen column so admins can track unseen orders
try {
    $col = $pdo->query("SHOW COLUMNS FROM orders LIKE 'is_seen'")->fetch();
    if (!$col) {
        // safe add column
        $pdo->exec("ALTER TABLE orders ADD COLUMN is_seen TINYINT(1) NOT NULL DEFAULT 0");
    }
} catch (Exception $e) {
    // ignore: older installs without orders table will fail here - that's fine
}

// Helper: upload an image file and return relative path or false
function uploadImageFile($tmp, $origName, $prefix = 'img_') {
    global $uploadDir;
    if (empty($tmp) || empty($origName)) return false;
    // Ensure it's an uploaded file
    if (!is_uploaded_file($tmp)) return false;

    // Validate MIME type using finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];
    if (!isset($map[$mime])) return false;
    $ext = $map[$mime];

    // Size limit (5MB)
    $maxSize = 5 * 1024 * 1024;
    if (filesize($tmp) > $maxSize) return false;

    $fileName = $prefix . uniqid() . '.' . $ext;
    $dest = $uploadDir . $fileName;
    if (move_uploaded_file($tmp, $dest)) {
        return 'images/' . $fileName;
    }
    return false;
} 

function deleteFilePath($relPath) {
    if (empty($relPath)) return;
    $full = dirname(__DIR__) . '/' . $relPath;
    if (file_exists($full)) @unlink($full);
}

// Helper: slugify a string
function slugify($text) {
    // transliterate
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // trim
    $text = trim($text, '-');
    // lowercase
    $text = strtolower($text);
    // remove unwanted characters
    $text = preg_replace('~[^-a-z0-9]+~', '', $text);

    if (empty($text)) return 'cat-' . uniqid();
    return $text;
}

// Ensure slug is unique in categories table
function generate_unique_slug($pdo, $name, $currentId = null) {
    $base = slugify($name);
    $slug = $base;
    $i = 1;
    while (true) {
        if ($currentId) {
            $s = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ? AND id != ?");
            $s->execute([$slug, $currentId]);
        } else {
            $s = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
            $s->execute([$slug]);
        }
        $cnt = (int)$s->fetchColumn();
        if ($cnt === 0) break;
        $slug = $base . '-' . $i;
        $i++;
    }
    return $slug;
}

// Helper: detect AJAX requests (XHR or explicit ajax param)
function isAjax() {
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || !empty($_POST['ajax']);
}

function jsonResponse($payload, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit();
}


// AJAX: delete a product image (expects image_id)
if (isset($_POST['delete_product_image'])) {
    try {
        $imgId = (int) ($_POST['image_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE id = ?");
        $stmt->execute([$imgId]);
        $img = $stmt->fetchColumn();
        if ($img) deleteFilePath($img);
        $pdo->prepare("DELETE FROM product_images WHERE id = ?")->execute([$imgId]);
        jsonResponse(['ok' => true]);
    } catch (Exception $e) {
        jsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
    }
}

// NOTE: primary image selection removed — `is_primary` column not present in current schema. Admins can reorder images by removing and reuploading, or we can add a migration to support persistent primary selection later.

// AJAX: upload a single product image for a product (returns id and path)
if (isset($_POST['upload_product_image'])) {
    try {
        $product_id = (int)($_POST['product_id'] ?? 0);
        if (!$product_id) throw new Exception('Missing product_id');
        if (empty($_FILES['image']) || empty($_FILES['image']['tmp_name'])) throw new Exception('No file uploaded');
        $path = uploadImageFile($_FILES['image']['tmp_name'], $_FILES['image']['name'], 'prod_');
        if (!$path) throw new Exception('Upload failed');
        // Determine if any image exists already (first image will be treated as primary on displays)
        $s = $pdo->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ?");
        $s->execute([$product_id]);
        $cnt = (int)$s->fetchColumn();
        $isPrimary = $cnt === 0 ? 1 : 0;
        $pdo->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)")->execute([$product_id, $path]);
        $id = $pdo->lastInsertId();
        jsonResponse(['ok'=>true,'id'=>$id,'path'=>$path,'is_primary'=>$isPrimary]);
    } catch (Exception $e) {
        jsonResponse(['ok'=>false,'error'=>$e->getMessage()], 500);
    }
}

// AJAX: upload or replace a material color catalog image (single image per product & material_type)
if (isset($_POST['upload_material_catalog'])) {
    try {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $material_type = $_POST['material_type'] ?? '';
        if (!$product_id || !in_array($material_type, ['tissu','bois'])) throw new Exception('Invalid parameters');
        if (empty($_FILES['material_image']) || empty($_FILES['material_image']['tmp_name'])) throw new Exception('No file uploaded');
        $path = uploadImageFile($_FILES['material_image']['tmp_name'], $_FILES['material_image']['name'], 'mat_');
        if (!$path) throw new Exception('Upload failed or invalid file');
        $stmt = $pdo->prepare("SELECT id, image_path FROM material_color_catalogs WHERE product_id = ? AND material_type = ?");
        $stmt->execute([$product_id, $material_type]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            // delete old file
            if (!empty($existing['image_path'])) deleteFilePath($existing['image_path']);
            $pdo->prepare("UPDATE material_color_catalogs SET image_path = ?, description = ? WHERE id = ?")->execute([$path, trim($_POST['description'] ?? ''), $existing['id']]);
        } else {
            $pdo->prepare("INSERT INTO material_color_catalogs (product_id, material_type, image_path, description) VALUES (?, ?, ?, ?)")->execute([$product_id, $material_type, $path, trim($_POST['description'] ?? '')]);
        }
        jsonResponse(['ok'=>true,'path'=>$path]);
    } catch (Exception $e) {
        jsonResponse(['ok'=>false,'error'=>$e->getMessage()], 500);
    }
}

// AJAX: delete material color catalog image (expects id)
if (isset($_POST['delete_material_catalog'])) {
    try {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) throw new Exception('Missing id');
        $s = $pdo->prepare("SELECT image_path FROM material_color_catalogs WHERE id = ?");
        $s->execute([$id]);
        $path = $s->fetchColumn();
        if ($path) deleteFilePath($path);
        $pdo->prepare("DELETE FROM material_color_catalogs WHERE id = ?")->execute([$id]);
        jsonResponse(['ok'=>true]);
    } catch (Exception $e) {
        jsonResponse(['ok'=>false,'error'=>$e->getMessage()], 500);
    }
} 

// AJAX: mark a message as read
if (isset($_POST['mark_message_read'])) {
    try {
        $msgId = (int)($_POST['message_id'] ?? 0);
        if (!$msgId) throw new Exception('Missing message_id');
        $pdo->prepare("UPDATE customer_messages SET is_read = 1 WHERE id = ?")->execute([$msgId]);
        if (isAjax()) jsonResponse(['ok'=>true]);
        $success_message = 'Message marqué comme lu';
    } catch (Exception $e) {
        if (isAjax()) jsonResponse(['ok'=>false,'error'=>$e->getMessage()], 500);
        $error_message = $e->getMessage();
    }
}

// Add product (v2) - supports product_images and color images
if (isset($_POST['add_product_v2'])) {
    try {
        if (empty($_POST['name']) || empty($_POST['description'])) {
            throw new Exception('Le nom et la description du produit sont requis.');
        }
        $ptype = in_array($_POST['product_type'] ?? '', ['made_to_order','available']) ? $_POST['product_type'] : 'made_to_order';
        $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
        $stmt = $pdo->prepare("INSERT INTO products (name, description, product_type, category_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([trim($_POST['name']), trim($_POST['description']), $ptype, $category_id]);
        $product_id = $pdo->lastInsertId();

        // Dimensions
        $widths = $_POST['width_cm'] ?? [];
        $heights = $_POST['height_cm'] ?? [];
        $depths = $_POST['depth_cm'] ?? [];
        $labels = $_POST['dim_label'] ?? [];
        $prices = $_POST['dim_price'] ?? [];
        $price_news = $_POST['dim_price_new'] ?? [];
        $promos = $_POST['dim_promo_percent'] ?? [];
        $stocks = $_POST['dim_stock'] ?? [];
        $is_defaults = $_POST['dim_is_default'] ?? [];

        $default_dimension_id = null;
        for ($i = 0; $i < count($labels); $i++) {
            $label = trim($labels[$i] ?? '');
            if ($label === '') continue;
            $width = (int) ($widths[$i] ?? 0);
            $height = (int) ($heights[$i] ?? 0);
            $depth = isset($depths[$i]) && $depths[$i] !== '' ? (int)$depths[$i] : null;
            $price = isset($prices[$i]) ? (float)$prices[$i] : 0;
            $price_new = isset($price_news[$i]) && $price_news[$i] !== '' ? (float)$price_news[$i] : null;
            $promo = !empty($promos[$i]) ? (int)$promos[$i] : 0;
            $dim_unlimiteds = $_POST['dim_unlimited'] ?? [];
            $unlimited = isset($dim_unlimiteds[$i]) && $dim_unlimiteds[$i] == '1';
            if ($unlimited) {
                // Unlimited stock represented by a large sentinel value
                $stock = 9999999;
            } else {
                $stock = isset($stocks[$i]) && $stocks[$i] !== '' ? (int)$stocks[$i] : null;
            }

            // If product is 'available', stock is required (unless unlimited)
            if ($ptype === 'available' && !$unlimited && ($stock === null || $stock < 0)) {
                throw new Exception('Le stock est requis pour les produits disponibles (chaque dimension doit avoir un stock).');
            }

            $is_default = !empty($is_defaults[$i]) ? 1 : 0;
            $pdo->prepare("INSERT INTO product_dimensions (product_id, width_cm, height_cm, depth_cm, label, price, price_new, promo_percent, stock, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([$product_id, $width, $height, $depth, $label, $price, $price_new, $promo, $stock, $is_default]);
            $dimensions_inserted++;

            if ($is_default) {
                $default_dimension_id = $pdo->lastInsertId();
            }
        }

        if (!isset($dimensions_inserted) || $dimensions_inserted === 0) {
            throw new Exception('Au moins une dimension est requise pour le produit.');
        }

        // Ensure only one dimension is default
        if ($default_dimension_id !== null) {
            $pdo->prepare("UPDATE product_dimensions SET is_default = 0 WHERE product_id = ?")->execute([$product_id]);
            $pdo->prepare("UPDATE product_dimensions SET is_default = 1 WHERE id = ? AND product_id = ?")->execute([$default_dimension_id, $product_id]);
        }

        // Product images
        if (!empty($_FILES['product_images']) && !empty($_FILES['product_images']['name'])) {
            $files = $_FILES['product_images'];
            for ($i = 0; $i < count($files['name']); $i++) {
                if (empty($files['tmp_name'][$i])) continue;
                $path = uploadImageFile($files['tmp_name'][$i], $files['name'][$i], 'prod_');
                if ($path) {
                    // Insert image row (no is_primary column in new schema). First image remains first by id order.
                    $pdo->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)")->execute([$product_id, $path]);
                }
            }
        }

        // Ensure at least one image exists
        $imgCount = (int)$pdo->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ?")->execute([$product_id]) ?: 0;
        // Properly fetch the count
        $simg = $pdo->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ?"); $simg->execute([$product_id]); $imgCount = (int)$simg->fetchColumn();
        if ($imgCount === 0) {
            throw new Exception('Au moins une image de produit est requise.');
        }

        // Colors (no images: adhere to schema)
        if (isset($_POST['color_names']) && is_array($_POST['color_names'])) {
            $types = $_POST['color_types'] ?? [];
            $names = $_POST['color_names'];
            $codes = $_POST['color_codes'] ?? [];
            $mods = $_POST['price_modifiers'] ?? [];
            for ($idx = 0; $idx < count($names); $idx++) {
                $cname = trim($names[$idx] ?? '');
                if ($cname === '') continue;
                $ctype = in_array($types[$idx] ?? 'tissu', ['tissu','bois']) ? $types[$idx] : 'tissu';
                $ccode = $codes[$idx] ?? null;
                $mod = isset($mods[$idx]) && $mods[$idx] !== '' ? (float)$mods[$idx] : 0;
                $pdo->prepare("INSERT INTO product_colors (product_id, type, color_name, color_code, price_modifier) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$product_id, $ctype, $cname, $ccode, $mod]);
            }
        }

        // Material catalogs (optional single image per material type)
        foreach (['tissu','bois'] as $mt) {
            if (!empty($_FILES["material_{$mt}"]['tmp_name'])) {
                $file_tmp = $_FILES["material_{$mt}"]['tmp_name'];
                $file_name = $_FILES["material_{$mt}"]['name'];
                $path = uploadImageFile($file_tmp, $file_name, 'mat_');
                if ($path) {
                    $stmt = $pdo->prepare("SELECT id, image_path FROM material_color_catalogs WHERE product_id = ? AND material_type = ?");
                    $stmt->execute([$product_id, $mt]);
                    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($existing) {
                        if (!empty($existing['image_path'])) deleteFilePath($existing['image_path']);
                        $pdo->prepare("UPDATE material_color_catalogs SET image_path = ?, description = ? WHERE id = ?")->execute([$path, trim($_POST["material_{$mt}_description"] ?? ''), $existing['id']]);
                    } else {
                        $pdo->prepare("INSERT INTO material_color_catalogs (product_id, material_type, image_path, description) VALUES (?, ?, ?, ?)")->execute([$product_id, $mt, $path, trim($_POST["material_{$mt}_description"] ?? '')]);
                    }
                }
            }
        }

        // Respond to AJAX or redirect
        if (isAjax()) {
            jsonResponse(['ok'=>true,'product_id'=>$product_id]);
        }

        $_SESSION['product_added_success'] = true;
        header("Location: dashboard.php?page=products");
        exit();

    } catch (PDOException $e) {
        $error_message = "Erreur lors de l'ajout du produit: " . $e->getMessage();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Update product (v2)
if (isset($_POST['update_product_v2'])) {
    try {
        $product_id = (int) $_POST['product_id'];
        if (empty($_POST['name']) || empty($_POST['description'])) {
            throw new Exception('Le nom et la description sont requis.');
        }
        $ptype = in_array($_POST['product_type'] ?? '', ['made_to_order','available']) ? $_POST['product_type'] : 'made_to_order';
        $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
        $pdo->prepare("UPDATE products SET name = ?, description = ?, product_type = ?, category_id = ? WHERE id = ?")->execute([trim($_POST['name']), trim($_POST['description']), $ptype, $category_id, $product_id]);

        // Dimensions: delete and reinsert
        $pdo->prepare("DELETE FROM product_dimensions WHERE product_id = ?")->execute([$product_id]);
        $widths = $_POST['width_cm'] ?? [];
        $heights = $_POST['height_cm'] ?? [];
        $depths = $_POST['depth_cm'] ?? [];
        $labels = $_POST['dim_label'] ?? [];
        $prices = $_POST['dim_price'] ?? [];
        $price_news = $_POST['dim_price_new'] ?? [];
        $promos = $_POST['dim_promo_percent'] ?? [];
        $stocks = $_POST['dim_stock'] ?? [];
        $is_defaults = $_POST['dim_is_default'] ?? [];

        $default_dimension_id = null;
        for ($i = 0; $i < count($labels); $i++) {
            $label = trim($labels[$i] ?? '');
            if ($label === '') continue;
            $width = (int) ($widths[$i] ?? 0);
            $height = (int) ($heights[$i] ?? 0);
            $depth = isset($depths[$i]) && $depths[$i] !== '' ? (int)$depths[$i] : null;
            $price = isset($prices[$i]) ? (float)$prices[$i] : 0;
            $price_new = isset($price_news[$i]) && $price_news[$i] !== '' ? (float)$price_news[$i] : null;
            $promo = !empty($promos[$i]) ? (int)$promos[$i] : 0;
            $dim_unlimiteds = $_POST['dim_unlimited'] ?? [];
            $unlimited = isset($dim_unlimiteds[$i]) && $dim_unlimiteds[$i] == '1';
            if ($unlimited) {
                $stock = 9999999;
            } else {
                $stock = isset($stocks[$i]) && $stocks[$i] !== '' ? (int)$stocks[$i] : null;
            }

            // If product is 'available', stock is required (unless unlimited)
            if ($ptype === 'available' && !$unlimited && ($stock === null || $stock < 0)) {
                throw new Exception('Le stock est requis pour les produits disponibles (chaque dimension doit avoir un stock).');
            }

            $is_default = !empty($is_defaults[$i]) ? 1 : 0;

            $pdo->prepare("INSERT INTO product_dimensions (product_id, width_cm, height_cm, depth_cm, label, price, price_new, promo_percent, stock, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([$product_id, $width, $height, $depth, $label, $price, $price_new, $promo, $stock, $is_default]);

            if ($is_default) {
                $default_dimension_id = $pdo->lastInsertId();
            }
        }

        if ($default_dimension_id !== null) {
            $pdo->prepare("UPDATE product_dimensions SET is_default = 0 WHERE product_id = ?")->execute([$product_id]);
            $pdo->prepare("UPDATE product_dimensions SET is_default = 1 WHERE id = ? AND product_id = ?")->execute([$default_dimension_id, $product_id]);
        }

        // Product images: keep existing, delete removed, add uploaded
        $keep = $_POST['existing_product_images'] ?? [];
        $keep = array_map('intval', $keep);
        $stmt = $pdo->prepare("SELECT id, image_path FROM product_images WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Delete images that are not in the keep list
        foreach ($rows as $r) {
            if (!in_array($r['id'], $keep)) {
                deleteFilePath($r['image_path']);
                $pdo->prepare("DELETE FROM product_images WHERE id = ?")->execute([$r['id']]);
            }
        }
        // After deletions, check how many remain
        $simg = $pdo->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ?"); $simg->execute([$product_id]); $imgCountAfterDelete = (int)$simg->fetchColumn();

        // Handle new uploaded images. Accept both 'product_images' (add modal) and 'edit_product_images' (edit modal hidden inputs)
        $uploadKeys = ['product_images','edit_product_images'];
        foreach ($uploadKeys as $key) {
            if (!empty($_FILES[$key]) && !empty($_FILES[$key]['name'])) {
                $files = $_FILES[$key];
                for ($i = 0; $i < count($files['name']); $i++) {
                    if (empty($files['tmp_name'][$i])) continue;
                    $path = uploadImageFile($files['tmp_name'][$i], $files['name'][$i], 'prod_');
                    if ($path) {
                        $pdo->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)")->execute([$product_id, $path]);
                        $imgCountAfterDelete++;
                    }
                }
            }
        }

        // Ensure at least one image exists after update
        $simg = $pdo->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ?"); $simg->execute([$product_id]); $imgCount = (int)$simg->fetchColumn();
        if ($imgCount === 0) {
            throw new Exception('Au moins une image de produit est requise.');
        }

        // Ensure at least one image exists after update
        $simg = $pdo->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ?"); $simg->execute([$product_id]); $imgCount = (int)$simg->fetchColumn();
        if ($imgCount === 0) {
            throw new Exception('Au moins une image de produit est requise.');
        }

        // Colors: delete and re-insert (no images)
        $pdo->prepare("DELETE FROM product_colors WHERE product_id = ?")->execute([$product_id]);
        if (isset($_POST['color_names']) && is_array($_POST['color_names'])) {
            $types = $_POST['color_types'] ?? [];
            $names = $_POST['color_names'];
            $codes = $_POST['color_codes'] ?? [];
            $mods = $_POST['price_modifiers'] ?? [];

            for ($idx = 0; $idx < count($names); $idx++) {
                $cname = trim($names[$idx] ?? '');
                if ($cname === '') continue;
                $ctype = in_array($types[$idx] ?? 'tissu', ['tissu','bois']) ? $types[$idx] : 'tissu';
                $ccode = $codes[$idx] ?? null;
                $mod = isset($mods[$idx]) && $mods[$idx] !== '' ? (float)$mods[$idx] : 0;
                $pdo->prepare("INSERT INTO product_colors (product_id, type, color_name, color_code, price_modifier) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$product_id, $ctype, $cname, $ccode, $mod]);
            }
        }

        // Material catalogs (optional single image per material type)
        foreach (['tissu','bois'] as $mt) {
            if (!empty($_FILES["material_{$mt}"]['tmp_name'])) {
                $file_tmp = $_FILES["material_{$mt}"]['tmp_name'];
                $file_name = $_FILES["material_{$mt}"]['name'];
                $path = uploadImageFile($file_tmp, $file_name, 'mat_');
                if ($path) {
                    $stmt = $pdo->prepare("SELECT id, image_path FROM material_color_catalogs WHERE product_id = ? AND material_type = ?");
                    $stmt->execute([$product_id, $mt]);
                    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($existing) {
                        if (!empty($existing['image_path'])) deleteFilePath($existing['image_path']);
                        $pdo->prepare("UPDATE material_color_catalogs SET image_path = ?, description = ? WHERE id = ?")->execute([$path, trim($_POST["material_{$mt}_description"] ?? ''), $existing['id']]);
                    } else {
                        $pdo->prepare("INSERT INTO material_color_catalogs (product_id, material_type, image_path, description) VALUES (?, ?, ?, ?)")->execute([$product_id, $mt, $path, trim($_POST["material_{$mt}_description"] ?? '')]);
                    }
                }
            }
        }

        $success_message = "Produit mis à jour avec succès!";

        if (isAjax()) {
            jsonResponse(['ok'=>true,'product_id'=>$product_id]);
        }

        header("Location: dashboard.php?page=products");
        exit(); 

    } catch (PDOException $e) {
        $error_message = "Erreur lors de la mise à jour du produit: " . $e->getMessage();
    } catch (Exception $e) {
        $error_message = "Erreur lors de la mise à jour du produit: " . $e->getMessage();
    }
}

// --- Category CRUD (product categories) ---
if (isset($_POST['add_product_category'])) {
    try {
        $name = trim($_POST['cat_name'] ?? '');
        $desc = trim($_POST['cat_description'] ?? '');
        if ($name === '') throw new Exception('Le nom est requis');
        $imagePath = null;
        if (!empty($_FILES['cat_image']) && !empty($_FILES['cat_image']['tmp_name'])) {
            $path = uploadImageFile($_FILES['cat_image']['tmp_name'], $_FILES['cat_image']['name'], 'cat_');
            if (!$path) throw new Exception('Échec de l' . "upload d'image");
            $imagePath = $path;
        }
        // generate unique slug and insert
        $slug = generate_unique_slug($pdo, $name);
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, image_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $desc, $imagePath]);
        $newId = $pdo->lastInsertId();
        // Fetch the inserted row
        $s = $pdo->prepare("SELECT id, name, slug, description, image_path, created_at FROM categories WHERE id = ?");
        $s->execute([$newId]);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        // update categories timestamp so frontend pages can auto-refresh
        $tsFile = dirname(__DIR__) . '/cache/categories_ts.txt';
        if (!is_dir(dirname($tsFile))) @mkdir(dirname($tsFile), 0777, true);
        @file_put_contents($tsFile, (string) time());
        if (isAjax()) {
            jsonResponse(['ok'=>true,'category'=>$row,'category_ts'=>filemtime($tsFile)]);
        
        }
        $success_message = 'Catégorie ajoutée avec succès!';
        header('Location: dashboard.php?page=categories'); exit();
    } catch (Exception $e) { $error_message = $e->getMessage(); }
}

if (isset($_POST['update_product_category'])) {
    try {
        $id = (int)($_POST['cat_id'] ?? 0);
        $name = trim($_POST['cat_name'] ?? '');
        $desc = trim($_POST['cat_description'] ?? '');
        if (!$id || $name === '') throw new Exception('Paramètres invalides');
        // generate unique slug
        $slug = generate_unique_slug($pdo, $name, $id);
        // Handle remove flag
        $removeFlag = !empty($_POST['cat_remove_image']) && $_POST['cat_remove_image'] == '1';
        if ($removeFlag) {
            $stmt = $pdo->prepare("SELECT image_path FROM categories WHERE id = ?"); $stmt->execute([$id]); $old = $stmt->fetchColumn(); if ($old) deleteFilePath($old);
            $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, image_path = NULL WHERE id = ?")->execute([$name, $slug, $desc, $id]);
        } else if (!empty($_FILES['cat_image']) && !empty($_FILES['cat_image']['tmp_name'])) {
            $stmt = $pdo->prepare("SELECT image_path FROM categories WHERE id = ?"); $stmt->execute([$id]); $old = $stmt->fetchColumn(); if ($old) deleteFilePath($old);
            $path = uploadImageFile($_FILES['cat_image']['tmp_name'], $_FILES['cat_image']['name'], 'cat_');
            $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, image_path = ? WHERE id = ?")->execute([$name, $slug, $desc, $path, $id]);
        } else {
            $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?")->execute([$name, $slug, $desc, $id]);
        }
        // fetch updated row
        $s = $pdo->prepare("SELECT id, name, slug, description, image_path, created_at FROM categories WHERE id = ?");
        $s->execute([$id]);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        // update categories timestamp
        $tsFile = dirname(__DIR__) . '/cache/categories_ts.txt';
        if (!is_dir(dirname($tsFile))) @mkdir(dirname($tsFile), 0777, true);
        @file_put_contents($tsFile, (string) time());
        if (isAjax()) jsonResponse(['ok'=>true,'category'=>$row,'category_ts'=>filemtime($tsFile)]);
        $success_message = 'Catégorie mise à jour';
        header('Location: dashboard.php?page=categories'); exit();
    } catch (Exception $e) { $error_message = $e->getMessage(); }
}

if (isset($_POST['delete_product_category'])) {
    try {
        $id = (int)($_POST['cat_id'] ?? 0);
        if (!$id) throw new Exception('Id manquant');
        // set products.category_id to NULL instead of deleting products
        $pdo->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?")->execute([$id]);
        // remove image
        $stmt = $pdo->prepare("SELECT image_path FROM categories WHERE id = ?"); $stmt->execute([$id]); $img = $stmt->fetchColumn(); if ($img) deleteFilePath($img);
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        // update categories timestamp so front pages can reload
        $tsFile = dirname(__DIR__) . '/cache/categories_ts.txt';
        if (!is_dir(dirname($tsFile))) @mkdir(dirname($tsFile), 0777, true);
        @file_put_contents($tsFile, (string) time());
        if (isAjax()) jsonResponse(['ok'=>true,'category_ts'=>filemtime($tsFile)]);
        $success_message = 'Catégorie supprimée';
        header('Location: dashboard.php?page=categories'); exit();
    } catch (Exception $e) { if (isAjax()) jsonResponse(['ok'=>false,'error'=>$e->getMessage()],500); $error_message = $e->getMessage(); }
}

// Legacy category-price ranges handlers remain (if present)

if (isset($_POST['delete_message'])) {
    try {
        $id = (int)($_POST['message_id'] ?? 0);
        $pdo->prepare("DELETE FROM customer_messages WHERE id = ?")->execute([$id]);
        if (isAjax()) jsonResponse(['ok'=>true]);
        $success_message = 'Message supprimé';
        header('Location: dashboard.php?page=messages'); exit();
    } catch (Exception $e) { if (isAjax()) jsonResponse(['ok'=>false,'error'=>$e->getMessage()],500); $error_message = $e->getMessage(); }
} 



// (Removed site-settings / hero / categories / videos UI — not supported by simplified schema)

// Get all products (new schema)
$products = $db->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch dimensions, images and colors for each product
foreach ($products as &$product) {
    // Dimensions
    $dimStmt = $db->prepare("SELECT * FROM product_dimensions WHERE product_id = ? ORDER BY id");
    $dimStmt->execute([$product['id']]);
    $product['dimensions'] = $dimStmt->fetchAll(PDO::FETCH_ASSOC);

    // Product images (new table)
    $imgStmt = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY id");
    $imgStmt->execute([$product['id']]);
    $product['images'] = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

    // Colors
    $colorStmt = $db->prepare("SELECT * FROM product_colors WHERE product_id = ? ORDER BY id");
    $colorStmt->execute([$product['id']]);
    $product['colors'] = $colorStmt->fetchAll(PDO::FETCH_ASSOC);

    // Material color catalogs (single images for tissu and bois)
    $matStmt = $db->prepare("SELECT * FROM material_color_catalogs WHERE product_id = ? ORDER BY material_type");
    $matStmt->execute([$product['id']]);
    $product['material_catalogs'] = $matStmt->fetchAll(PDO::FETCH_ASSOC); 
}
unset($product);
// Legacy product image/gallery/sizes handling removed for simplified schema



// Gallery image handlers removed (not used in current schema)

// Handle product deletion
if (isset($_POST['delete_product'])) {
    try {
        $product_id = (int)$_POST['product_id'];

        // Remove related product images from filesystem
        $imgStmt = $db->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
        $imgStmt->execute([$product_id]);
        $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($images as $img) {
            if (!empty($img)) {
                $path = dirname(__DIR__) . '/' . $img;
                if (file_exists($path)) @unlink($path);
            }
        }

        // Remove color images (if any) - legacy (shouldn't exist in new schema)
        $cimgStmt = $db->prepare("SELECT image_path FROM product_colors WHERE product_id = ? AND image_path IS NOT NULL AND image_path != ''");
        $cimgStmt->execute([$product_id]);
        $cimgs = $cimgStmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($cimgs as $cimg) {
            if (!empty($cimg)) {
                $path = dirname(__DIR__) . '/' . $cimg;
                if (file_exists($path)) @unlink($path);
            }
        }

        // Remove material catalog images (tissu/bois) if present
        $mimgStmt = $db->prepare("SELECT image_path FROM material_color_catalogs WHERE product_id = ?");
        $mimgStmt->execute([$product_id]);
        $mimgs = $mimgStmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($mimgs as $mimg) {
            if (!empty($mimg)) {
                $path = dirname(__DIR__) . '/' . $mimg;
                if (file_exists($path)) @unlink($path);
            }
        }
        $db->prepare("DELETE FROM material_color_catalogs WHERE product_id = ?")->execute([$product_id]);
        // Delete product (cascades to product_dimensions, product_images, product_colors per schema)
        $db->prepare("DELETE FROM products WHERE id = ?")->execute([$product_id]);

        $success_message = "Produit supprimé avec succès!";
        // Refresh products list
        $products = $db->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        header("Location: dashboard.php?page=products");
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

        // Update basic product (respect product_type)
        $product_type = in_array($_POST['product_type'] ?? '', ['made_to_order', 'available']) ? $_POST['product_type'] : 'made_to_order';
        $pdo->prepare("UPDATE products SET name = ?, description = ?, product_type = ? WHERE id = ?")->execute([
            trim($_POST['name']),
            trim($_POST['description']),
            $product_type,
            $product_id
        ]);
        // legacy dimension_images logic removed - current schema stores images in product_images table linked to product only.
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


        // legacy: dimension_existing_images removed (not used in new schema)

        $examplePaths = [];




        // Delete existing dimensions and colors (we'll re-insert)
        // dimension_images removed in new schema; just delete dimensions and colors (they will cascade as needed)
        // No action needed for dimension images table because it is not used in the new schema.
        $pdo->prepare("DELETE FROM product_dimensions WHERE product_id = ?")->execute([$product_id]);
        $pdo->prepare("DELETE FROM product_colors WHERE product_id = ?")->execute([$product_id]);

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

// Dimension created. Note: per-dimension images are not supported by the current schema.
            // If you need per-dimension imagery later, convert to product-level images with a link from images to product and reference as needed.
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
        $order_id = (int)($_POST['order_id'] ?? 0);
        $rawStatus = trim($_POST['status'] ?? '');
        // Normalize status: lower, remove accents and non-alphanum (so 'Livré' or 'expédié' map)
        $norm = mb_strtolower($rawStatus, 'UTF-8');
        $norm = iconv('UTF-8', 'ASCII//TRANSLIT', $norm);
        $norm = preg_replace('/[^a-z0-9 ]+/', '', $norm);
        $norm = trim($norm);

        // Map common French labels (and direct slugs) to canonical slugs
        $map = [
            'pending' => 'pending', 'en attente' => 'pending', 'enattente' => 'pending',
            'processing' => 'processing', 'en traitement' => 'processing', 'entrattement' => 'processing',
            'shipped' => 'shipped', 'expedie' => 'shipped', 'expedier' => 'shipped', 'expediee' => 'shipped',
            'delivered' => 'delivered', 'livre' => 'delivered', 'livre' => 'delivered', 'livre' => 'delivered', 'livree' => 'delivered',
            'canceled' => 'canceled', 'annule' => 'canceled', 'annulee' => 'canceled', 'annule' => 'canceled',
            'archived' => 'archived', 'archive' => 'archived',
        ];

        $status = $map[$norm] ?? $norm;

        $allowed = ['pending','processing','shipped','delivered','canceled','archived'];
        if (!$order_id || !in_array($status, $allowed)) throw new Exception('Invalid parameters');

        // Fetch old status to detect transition
        $old = $db->prepare("SELECT status FROM orders WHERE id = ?"); $old->execute([$order_id]); $oldStatus = $old->fetchColumn();

        $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);

        // If transitioning into delivered from a non-delivered state, decrement stock
        if ($oldStatus !== 'delivered' && $status === 'delivered') {
            $items = getOrderItems($db, $order_id);
            foreach ($items as $it) {
                $qty = isset($it['quantity']) ? (int)$it['quantity'] : (int)($it['qty'] ?? 0);
                if ($qty <= 0) continue;
                if (empty($it['dimension_id'])) continue;
                // Check current stock
                $dstmt = $db->prepare('SELECT stock FROM product_dimensions WHERE id = ? FOR UPDATE');
                $dstmt->execute([$it['dimension_id']]);
                $stock = $dstmt->fetchColumn();
                if ($stock === false) continue;
                // Do not decrement unlimited stock (represented by a very large number)
                if ((int)$stock >= 9999999) continue;
                $newStock = max(0, (int)$stock - $qty);
                $db->prepare('UPDATE product_dimensions SET stock = ? WHERE id = ?')->execute([$newStock, $it['dimension_id']]);
            }
        }

        if (isAjax()) jsonResponse(['ok'=>true]);
        $success_message = "Statut de commande mis à jour avec succès!";
    } catch (Exception $e) { if (isAjax()) jsonResponse(['ok'=>false,'error'=>$e->getMessage()],500); $error_message = "Erreur lors de la mise à jour du statut: " . $e->getMessage(); }
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

            // Dimension created (images per-dimension are not supported by the current schema)
            // If needed, product-level images should be uploaded via the product_images field (handled elsewhere).

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
        if (isAjax()) jsonResponse(['ok'=>true]);

        // Refresh orders list
        if (!empty($_GET['search_phone'])) {
            $searchPhone = trim($_GET['search_phone']);
            $stmt = $db->prepare("SELECT * FROM orders WHERE status != 'archived' AND status != 'canceled' AND customer_phone LIKE ? ORDER BY created_at DESC");
            $stmt->execute(['%' . $searchPhone . '%']);
            $all_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $all_orders = $db->query("SELECT * FROM orders WHERE status != 'archived' AND status != 'canceled' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        }

        // Refresh stats
        $total_orders = $db->query("SELECT COALESCE(COUNT(*), 0) FROM orders WHERE  status != 'canceled'")->fetchColumn();
        $total_revenue = $db->query("SELECT COALESCE(SUM(total_price - delivery_price), 0) FROM orders WHERE  status != 'canceled' AND status != 'pending'")->fetchColumn();
        $pending_orders = $db->query("SELECT COALESCE(COUNT(*), 0) FROM orders WHERE status = 'pending'")->fetchColumn();
        $recent_orders = $db->query("SELECT * FROM orders WHERE status != 'archived' AND status != 'canceled' ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $error_message = "Erreur lors de la suppression de la commande: " . $e->getMessage();
    }
}

// Get stats for dashboard
$total_orders = $db->query("SELECT COALESCE(COUNT(*), 0) FROM orders WHERE status != 'canceled'")->fetchColumn();
$total_revenue = $db->query("SELECT COALESCE(SUM(total_price - delivery_price), 0) FROM orders WHERE status != 'canceled' AND status != 'pending'")->fetchColumn();
$pending_orders = $db->query("SELECT COALESCE(COUNT(*), 0) FROM orders WHERE status = 'pending'")->fetchColumn();

// Additional stats
$total_products = $db->query("SELECT COALESCE(COUNT(*), 0) FROM products")->fetchColumn();
$unread_messages = $db->query("SELECT COALESCE(COUNT(*), 0) FROM customer_messages WHERE is_read = 0")->fetchColumn();

// Get recent orders (last 5)
$recent_orders = $db->query("SELECT * FROM orders WHERE status != 'archived' AND status != 'canceled' ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
// Get all orders
$searchPhone = isset($_GET['search_phone']) ? trim($_GET['search_phone']) : '';
if ($searchPhone !== '') {
    $stmt = $db->prepare("SELECT * FROM orders WHERE status != 'archived' AND status != 'canceled' AND customer_phone LIKE ? ORDER BY created_at DESC");
    $stmt->execute(['%' . $searchPhone . '%']);
    $all_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $all_orders = $db->query("SELECT * FROM orders  WHERE status != 'archived' AND status != 'canceled' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
}

if ($searchPhone !== '') {
    $stmt = $db->prepare("SELECT * FROM orders WHERE customer_phone LIKE ? AND (status = 'archived' OR status = 'canceled') ORDER BY created_at DESC");
    $stmt->execute(['%' . $searchPhone . '%']);
    $all_archives = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $all_archives = $db->query("SELECT * FROM orders WHERE status = 'archived' OR status = 'canceled' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
}

// If orders store wilaya_id, resolve wilaya name for display
$resolveWilayaName = function(&$arr) use ($db) {
    if (!is_array($arr)) return;
    $wstmt = $db->prepare('SELECT name FROM wilayas WHERE id = ? LIMIT 1');
    foreach ($arr as &$o) {
        if (!empty($o['wilaya_name'])) continue; // already present
        if (!empty($o['wilaya_id'])) {
            $wstmt->execute([$o['wilaya_id']]);
            $wrow = $wstmt->fetch(PDO::FETCH_ASSOC);
            if ($wrow) $o['wilaya_name'] = $wrow['name'];
            else $o['wilaya_name'] = null;
        }
    }
};

$resolveWilayaName($recent_orders);
$resolveWilayaName($all_orders);
$resolveWilayaName($all_archives);

// Helper to get order items
function getOrderItems($db, $order_id)
{
    // Join order_items with products and dimensions to get consistent fields
    $stmt = $db->prepare(
        "SELECT oi.*, p.name AS product_name, pd.label AS size, oi.unit_price AS product_price,
            (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = oi.product_id ORDER BY pi.id LIMIT 1) AS product_image
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

        // If stored as color ids, look them up
        if (!empty($item['tissu_color_id'])) {
            $tstmt = $db->prepare('SELECT color_code, color_name FROM product_colors WHERE id = ? LIMIT 1');
            $tstmt->execute([$item['tissu_color_id']]);
            $trow = $tstmt->fetch(PDO::FETCH_ASSOC);
            if ($trow) {
                $item['tissu_color_code'] = $trow['color_code'] ?? null;
                $item['tissu_color'] = $trow['color_name'] ?? null;
            }
        } elseif (!empty($item['tissu_color']) && !empty($item['product_id'])) {
            $t = $item['tissu_color'];
            if (preg_match('/^#?[0-9A-Fa-f]{3,6}$/', $t)) {
                if ($t[0] !== '#') $t = '#' . $t;
                $item['tissu_color_code'] = $t;
            } else {
                $tstmt = $db->prepare('SELECT color_code FROM product_colors WHERE product_id = ? AND type = "tissu" AND (color_name = ? OR color_code = ?) LIMIT 1');
                $tstmt->execute([$item['product_id'], $item['tissu_color'], $item['tissu_color']]);
                $trow = $tstmt->fetch(PDO::FETCH_ASSOC);
                if ($trow && !empty($trow['color_code'])) $item['tissu_color_code'] = $trow['color_code'];
            }
        }

        if (!empty($item['bois_color_id'])) {
            $bstmt = $db->prepare('SELECT color_code, color_name FROM product_colors WHERE id = ? LIMIT 1');
            $bstmt->execute([$item['bois_color_id']]);
            $brow = $bstmt->fetch(PDO::FETCH_ASSOC);
            if ($brow) {
                $item['bois_color_code'] = $brow['color_code'] ?? null;
                $item['bois_color'] = $brow['color_name'] ?? null;
            }
        } elseif (!empty($item['bois_color']) && !empty($item['product_id'])) {
            $b = $item['bois_color'];
            if (preg_match('/^#?[0-9A-Fa-f]{3,6}$/', $b)) {
                if ($b[0] !== '#') $b = '#' . $b;
                $item['bois_color_code'] = $b;
            } else {
                $bstmt = $db->prepare('SELECT color_code FROM product_colors WHERE product_id = ? AND type = "bois" AND (color_name = ? OR color_code = ?) LIMIT 1');
                $bstmt->execute([$item['product_id'], $item['bois_color'], $item['bois_color']]);
                $brow = $bstmt->fetch(PDO::FETCH_ASSOC);
                if ($brow && !empty($brow['color_code'])) $item['bois_color_code'] = $brow['color_code'];
            }
        }

        // Ensure product_image exists (already selected in query), else try to get one
        if (empty($item['product_image']) && !empty($item['product_id'])) {
            $imgStmt = $db->prepare('SELECT image_path FROM product_images WHERE product_id = ? ORDER BY id LIMIT 1');
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