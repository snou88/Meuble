<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'backendadmin.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord | <?= htmlspecialchars($siteName) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>

<body>
    <header>
        <div class="header-content">
            <a href="../index.php" class="logo">
                <span><img src="../assets/images/LOGO-blanc.png" style="width: 280px; height: 80px;" alt=""></span>
            </a>
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <div class="dashboard-container">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de bord</span>
                </a>
                <a href="#orders" class="sidebar-link" onclick="showTab('orders')">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Commandes</span>
                </a>
                <a href="#products" class="sidebar-link" onclick="showTab('products')">
                    <i class="fas fa-tshirt"></i>
                    <span>Produits</span>
                </a>
                <a href="#archives" class="sidebar-link" onclick="showTab('archives')">
                    <i class="fas fa-archive"></i>
                    <span>Archives</span>
                </a>
                <!-- Site settings, hero, categories and videos tabs removed (not in simplified schema) -->
                <a href="#wilayas" class="sidebar-link" onclick="showTab('wilayas')">
                    <i class="fas fa-truck"></i>
                    <span>Wilayas & Livraison</span>
                </a>
                <a href="logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </div>

        <div class="main-content">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <div id="dashboard" class="tab-content active">
                <h1>Tableau de bord</h1>

                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Commandes totales</h3>
                        <p><?= htmlspecialchars($total_orders) ?></p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-euro-sign"></i>
                        <h3>Revenu total</h3>
                        <p><?= number_format($total_revenue, 2, ',', ' ') ?> DA</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-clock"></i>
                        <h3>Commandes en attente</h3>
                        <p><?= htmlspecialchars($pending_orders) ?></p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Dernières commandes</h2>
                        <a href="#orders" class="btn" onclick="showTab('orders')">Voir tout</a>
                    </div>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Produit</th>
                                    <th>Image</th>
                                    <th>Prix</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <?php $items = getOrderItems($db, $order['id']); ?>
                                    <tr>
                                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                        <td>
                                            <?php if ($items): ?>
                                                <?php foreach ($items as $item): ?>
                                                    <?= htmlspecialchars($item['product_name']) ?> (Taille:
                                                    <?= htmlspecialchars($item['size']) ?>, Qte:
                                                    <?= $item['quantity'] ?>
                                                    <?php if (!empty($item['tissu_color_code'])): ?>
                                                        , Couleur tissu: <span
                                                            style="display:inline-block;width:15px;height:15px;background-color: <?= htmlspecialchars($item['tissu_color_code']) ?>;border:1px solid #ddd;border-radius:50%;vertical-align:middle;margin-right:5px;"></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($item['bois_color_code'])): ?>
                                                        , Couleur bois: <span
                                                            style="display:inline-block;width:15px;height:15px;background-color: <?= htmlspecialchars($item['bois_color_code']) ?>;border:1px solid #ddd;border-radius:50%;vertical-align:middle;margin-right:5px;"></span>
                                                    <?php endif; ?>)<br>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <?= htmlspecialchars($order['product_name'] ?? '') ?> (Taille:
                                                <?= htmlspecialchars($order['size'] ?? '') ?>, Qte:
                                                <?= $order['quantity'] ?? 1 ?>
                                                <?php if (!empty($order['tissu_color']) || !empty($order['tissu_color_code'])): ?>
                                                    , Couleur tissu: <span
                                                        style="display:inline-block;width:15px;height:15px;background-color: <?= htmlspecialchars($order['tissu_color_code'] ?? $order['tissu_color'] ?? '') ?>;border:1px solid #ddd;border-radius:50%;vertical-align:middle;margin-right:5px;"></span>
                                                <?php endif; ?>
                                                <?php if (!empty($order['bois_color']) || !empty($order['bois_color_code'])): ?>
                                                    , Couleur bois: <span
                                                        style="display:inline-block;width:15px;height:15px;background-color: <?= htmlspecialchars($order['bois_color_code'] ?? $order['bois_color'] ?? '') ?>;border:1px solid #ddd;border-radius:50%;vertical-align:middle;margin-right:5px;"></span>
                                                <?php endif; ?>)
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($items && !empty($items[0]['product_image'])): ?>
                                                <img src="../<?= $items[0]['product_image'] ?>" class="product-image-thumb">
                                            <?php elseif (!empty($order['product_image'])): ?>
                                                <img src="../<?= $order['product_image'] ?>" class="product-image-thumb">
                                            <?php else: ?>
                                                <img src="../images/default_product.jpg" class="product-image-thumb">
                                            <?php endif; ?>
                                        </td>
                                        <td class="price-details">
                                            <?php if ($items): ?>
                                                <?php $total = 0;
                                                foreach ($items as $item) {
                                                    $total += $item['product_price'] * $item['quantity'];
                                                } ?>
                                                <span class="label">Produit:</span> <?= number_format($total, 2, ',', ' ') ?>
                                                DA<br>
                                                <span class="label">Livraison:</span>
                                                <?= number_format($order['delivery_price'], 2, ',', ' ') ?> DA<br>
                                                <span class="label">Total:</span>
                                                <?= number_format($order['total_price'], 2, ',', ' ') ?> DA
                                            <?php else: ?>
                                                <span class="label">Produit:</span>
                                                <?= number_format($order['product_price'] ?? 0, 2, ',', ' ') ?> DA<br>
                                                <span class="label">Livraison:</span>
                                                <?= number_format($order['delivery_price'], 2, ',', ' ') ?> DA<br>
                                                <span class="label">Total:</span>
                                                <?= number_format($order['total_price'], 2, ',', ' ') ?> DA
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status status-<?= $order['status'] ?>">
                                                <?= $order['status'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 5px;">
                                                <button class="btn btn-small btn-info"
                                                    onclick="showClientDetailsAjax(<?= $order['id'] ?>)">
                                                    <i class="fas fa-info-circle"></i> Détails
                                                </button>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <button type="submit" name="delete_order"
                                                        class="btn btn-small btn-danger"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette commande? Cette action est irréversible.')">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="orders" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Toutes les commandes</h2>
                        <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" name="search_phone" placeholder="Rechercher par téléphone"
                                value="<?= isset($_GET['search_phone']) ? htmlspecialchars($_GET['search_phone']) : '' ?>"
                                style="padding: 6px; border-radius: 4px; border: 1px solid #ccc;">
                            <button type="submit" class="btn btn-small btn-info"><i class="fas fa-search"></i>
                                Rechercher</button>
                            <?php if (!empty($_GET['search_phone'])): ?>
                                <a href="dashboard.php#orders" class="btn btn-small btn-secondary">Réinitialiser</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Produit</th>
                                    <th>Image</th>
                                    <th>Prix</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_orders as $order): ?>
                                    <?php $items = getOrderItems($db, $order['id']); ?>
                                    <tr>
                                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                        <td>
                                            <?php if ($items): ?>
                                                <?php foreach ($items as $item): ?>
                                                    <?= htmlspecialchars($item['product_name']) ?> (Taille:
                                                    <?= htmlspecialchars($item['size']) ?>, Qte:
                                                    <?= $item['quantity'] ?>             <?php if (!empty($item['color'])): ?>, Couleur: <span
                                                            style="display: inline-block; width: 15px; height: 15px; background-color: <?= htmlspecialchars($item['color_code'] ?? $item['color']) ?>; border: 1px solid #ddd; border-radius: 50%; vertical-align: middle; margin-right: 5px;"></span><?= htmlspecialchars($item['color']) ?><?php endif; ?>)<br>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <?= htmlspecialchars($order['product_name'] ?? '') ?> (Taille:
                                                <?= htmlspecialchars($order['size'] ?? '') ?>, Qte:
                                                <?= $order['quantity'] ?? 1 ?>         <?php if (!empty($order['color'])): ?>, Couleur:
                                                    <span
                                                        style="display: inline-block; width: 15px; height: 15px; background-color: <?= htmlspecialchars($order['color_code'] ?? $order['color']) ?>; border: 1px solid #ddd; border-radius: 50%; vertical-align: middle; margin-right: 5px;"></span><?= htmlspecialchars($order['color']) ?><?php endif; ?>)
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($items && !empty($items[0]['product_image'])): ?>
                                                <img src="../<?= $items[0]['product_image'] ?>" class="product-image-thumb">
                                            <?php elseif (!empty($order['product_image'])): ?>
                                                <img src="../<?= $order['product_image'] ?>" class="product-image-thumb">
                                            <?php else: ?>
                                                <img src="../images/default_product.jpg" class="product-image-thumb">
                                            <?php endif; ?>
                                        </td>
                                        <td class="price-details">
                                            <?php if ($items): ?>
                                                <?php $total = 0;
                                                foreach ($items as $item) {
                                                    $total += $item['product_price'] * $item['quantity'];
                                                } ?>
                                                <span class="label">Produit:</span> <?= number_format($total, 2, ',', ' ') ?>
                                                DA<br>
                                                <span class="label">Livraison:</span>
                                                <?= number_format($order['delivery_price'], 2, ',', ' ') ?> DA<br>
                                                <span class="label">Total:</span>
                                                <?= number_format($order['total_price'], 2, ',', ' ') ?> DA
                                            <?php else: ?>
                                                <span class="label">Produit:</span>
                                                <?= number_format($order['product_price'] ?? 0, 2, ',', ' ') ?> DA<br>
                                                <span class="label">Livraison:</span>
                                                <?= number_format($order['delivery_price'], 2, ',', ' ') ?> DA<br>
                                                <span class="label">Total:</span>
                                                <?= number_format($order['total_price'], 2, ',', ' ') ?> DA
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                                        <td>
                                            <form method="POST" class="status-form">
                                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                <select name="status" onchange="this.form.submit()">
                                                    <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>En attente</option>
                                                    <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>En traitement</option>
                                                    <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Expédié</option>
                                                    <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Livré</option>
                                                    <option value="archived" <?= $order['status'] == 'archived' ? 'selected' : '' ?>>Archivé</option>
                                                    <option value="canceled" <?= $order['status'] == 'canceled' ? 'selected' : '' ?>>Annulé</option>
                                                </select>
                                                <input type="hidden" name="update_order_status" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 5px;">
                                                <button class="btn btn-small btn-info"
                                                    onclick="showClientDetailsAjax(<?= $order['id'] ?>)">
                                                    <i class="fas fa-info-circle"></i> Détails
                                                </button>

                                                <?php if (!empty($order['payment_receipt'])):
                                                    $receiptPath = $order['payment_receipt'];
                                                    $isPDF = strtolower(substr($receiptPath, -4)) === '.pdf';
                                                    ?>
                                                    <button class="btn btn-small btn-secondary"
                                                        onclick="<?= $isPDF ? "showPDFPopup('../$receiptPath')" : "showReceiptImage('../$receiptPath')" ?>">
                                                        <i class="fas fa-receipt"></i> Reçu
                                                    </button>
                                                <?php endif; ?>

                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <button type="submit" name="delete_order"
                                                        class="btn btn-small btn-danger"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette commande? Cette action est irréversible.')">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="archives" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Toutes les archives</h2>
                        <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" name="search_phone" placeholder="Rechercher par téléphone"
                                value="<?= isset($_GET['search_phone']) ? htmlspecialchars($_GET['search_phone']) : '' ?>"
                                style="padding: 6px; border-radius: 4px; border: 1px solid #ccc;">
                            <button type="submit" class="btn btn-small btn-info"><i class="fas fa-search"></i>
                                Rechercher</button>
                            <?php if (!empty($_GET['search_phone'])): ?>
                                <a href="dashboard.php#orders" class="btn btn-small btn-secondary">Réinitialiser</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Produit</th>
                                    <th>Image</th>
                                    <th>Prix</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_archives as $order): ?>
                                    <?php $items = getOrderItems($db, $order['id']); ?>
                                    <tr>
                                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                        <td>
                                            <?php if ($items): ?>
                                                <?php foreach ($items as $item): ?>
                                                    <?= htmlspecialchars($item['product_name']) ?> (Taille:
                                                    <?= htmlspecialchars($item['size']) ?>, Qte:
                                                    <?= $item['quantity'] ?>             <?php if (!empty($item['color'])): ?>, Couleur: <span
                                                            style="display: inline-block; width: 15px; height: 15px; background-color: <?= htmlspecialchars($item['color_code'] ?? $item['color']) ?>; border: 1px solid #ddd; border-radius: 50%; vertical-align: middle; margin-right: 5px;"></span><?= htmlspecialchars($item['color']) ?><?php endif; ?>)<br>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <?= htmlspecialchars($order['product_name'] ?? '') ?> (Taille:
                                                <?= htmlspecialchars($order['size'] ?? '') ?>, Qte:
                                                <?= $order['quantity'] ?? 1 ?>         <?php if (!empty($order['color'])): ?>, Couleur:
                                                    <span
                                                        style="display: inline-block; width: 15px; height: 15px; background-color: <?= htmlspecialchars($order['color_code'] ?? $order['color']) ?>; border: 1px solid #ddd; border-radius: 50%; vertical-align: middle; margin-right: 5px;"></span><?= htmlspecialchars($order['color']) ?><?php endif; ?>)
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($items && !empty($items[0]['product_image'])): ?>
                                                <img src="../<?= $items[0]['product_image'] ?>" class="product-image-thumb">
                                            <?php elseif (!empty($order['product_image'])): ?>
                                                <img src="../<?= $order['product_image'] ?>" class="product-image-thumb">
                                            <?php else: ?>
                                                <img src="../images/default_product.jpg" class="product-image-thumb">
                                            <?php endif; ?>
                                        </td>
                                        <td class="price-details">
                                            <?php if ($items): ?>
                                                <?php $total = 0;
                                                foreach ($items as $item) {
                                                    $total += $item['product_price'] * $item['quantity'];
                                                } ?>
                                                <span class="label">Produit:</span> <?= number_format($total, 2, ',', ' ') ?>
                                                DA<br>
                                                <span class="label">Livraison:</span>
                                                <?= number_format($order['delivery_price'], 2, ',', ' ') ?> DA<br>
                                                <span class="label">Total:</span>
                                                <?= number_format($order['total_price'], 2, ',', ' ') ?> DA
                                            <?php else: ?>
                                                <span class="label">Produit:</span>
                                                <?= number_format($order['product_price'] ?? 0, 2, ',', ' ') ?> DA<br>
                                                <span class="label">Livraison:</span>
                                                <?= number_format($order['delivery_price'], 2, ',', ' ') ?> DA<br>
                                                <span class="label">Total:</span>
                                                <?= number_format($order['total_price'], 2, ',', ' ') ?> DA
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                                        <td>
                                            <form method="POST" class="status-form">
                                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                <select name="status" onchange="this.form.submit()">
                                                    <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>En attente</option>
                                                    <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>En traitement</option>
                                                    <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Expédié</option>
                                                    <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Livré</option>
                                                    <option value="archived" <?= $order['status'] == 'archived' ? 'selected' : '' ?>>Archivé</option>
                                                    <option value="canceled" <?= $order['status'] == 'canceled' ? 'selected' : '' ?>>Annulé</option>
                                                </select>
                                                <input type="hidden" name="update_order_status" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 5px;">
                                                <button class="btn btn-small btn-info"
                                                    onclick="showClientDetailsAjax(<?= $order['id'] ?>)">
                                                    <i class="fas fa-info-circle"></i> Détails
                                                </button>

                                                <?php if (!empty($order['payment_receipt'])):
                                                    $receiptPath = $order['payment_receipt'];
                                                    $isPDF = strtolower(substr($receiptPath, -4)) === '.pdf';
                                                    ?>
                                                    <button class="btn btn-small btn-secondary"
                                                        onclick="<?= $isPDF ? "showPDFPopup('../$receiptPath')" : "showReceiptImage('../$receiptPath')" ?>">
                                                        <i class="fas fa-receipt"></i> Reçu
                                                    </button>
                                                <?php endif; ?>

                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <button type="submit" name="delete_order"
                                                        class="btn btn-small btn-danger"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette commande? Cette action est irréversible.')">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <div id="products" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Gestion des Produits</h2>
                        <button class="btn" onclick="showAddProductModal()">
                            <i class="fas fa-plus"></i> Ajouter un produit
                        </button>
                    </div>

                    <div class="product-list">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <?php
                                // Determine primary image from dimensions
                                $primaryImage = null;
                                if (!empty($product['dimensions'])) {
                                    foreach ($product['dimensions'] as $d) {
                                        if (!empty($d['images'])) {
                                            $primaryImage = $d['images'][0]['image_path'];
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <?php if (!empty($primaryImage)): ?>
                                    <img src="../<?= $primaryImage ?>" class="product-image"
                                        alt="<?= htmlspecialchars($product['name']) ?>">
                                <?php else: ?>
                                    <img src="../images/default_product.jpg" class="product-image">
                                <?php endif; ?>
                                <div class="product-content">
                                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                                    <p><strong>Dimensions:</strong></p>
                                    <ul>
                                        <?php foreach ($product['dimensions'] as $dim): ?>
                                            <li><?= htmlspecialchars($dim['label']) ?> —
                                                <?= number_format($dim['price'], 2, ',', ' ') ?> DA
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php
                                        // grouper les couleurs par type pour un affichage clair
                                        $colors_by_type = ['tissu' => [], 'bois' => []];
                                        if (!empty($product['colors'])) {
                                            foreach ($product['colors'] as $c) {
                                                $t = strtolower($c['type'] ?? 'tissu');
                                                if (!isset($colors_by_type[$t])) $colors_by_type[$t] = [];
                                                $colors_by_type[$t][] = $c;
                                            }
                                        }
                                    ?>
                                    <?php if (!empty($product['colors'])): ?>
                                        <div class="color-preview enhanced-color-preview">
                                            <?php if (!empty($colors_by_type['tissu'])): ?>
                                                <div class="color-section">
                                                    <div class="color-section-title">Tissu</div>
                                                    <div class="color-swatches">
                                                        <?php foreach ($colors_by_type['tissu'] as $color): ?>
                                                            <div class="color-swatch" title="<?= htmlspecialchars($color['color_name']) ?>" style="background-color: <?= htmlspecialchars($color['color_code'] ?? '#ffffff') ?>;">
                                                                <span class="color-name"><?= htmlspecialchars($color['color_name']) ?></span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($colors_by_type['bois'])): ?>
                                                <div class="color-section">
                                                    <div class="color-section-title">Bois</div>
                                                    <div class="color-swatches">
                                                        <?php foreach ($colors_by_type['bois'] as $color): ?>
                                                            <div class="color-swatch" title="<?= htmlspecialchars($color['color_name']) ?>" style="background-color: <?= htmlspecialchars($color['color_code'] ?? '#ffffff') ?>;">
                                                                <span class="color-name"><?= htmlspecialchars($color['color_name']) ?></span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <p><?= htmlspecialchars($product['description']) ?></p>
                                </div>
                                <div class="product-actions">
                                    <button class="btn btn-small btn-info"
                                        data-product='<?= htmlspecialchars(json_encode($product), ENT_QUOTES) ?>'
                                        data-dimensions='<?= htmlspecialchars(json_encode($product['dimensions']), ENT_QUOTES) ?>'
                                        data-colors='<?= htmlspecialchars(json_encode($product['colors']), ENT_QUOTES) ?>'
                                        onclick="openEditProductFromData(this)">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="submit" name="delete_product" class="btn btn-small btn-danger"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit?')">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Categories tab removed (not supported by simplified schema) -->

            <div id="wilayas" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Wilayas & Prix de livraison</h2>
                    </div>
                    <div class="card-body">
                        <form method="post"
                            style="margin-bottom:20px; display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
                            <div>
                                <label>ID</label>
                                <input type="number" name="w_id" min="1" style="padding:8px;">
                            </div>
                            <div>
                                <label>Nom</label>
                                <input type="text" name="w_name" style="padding:8px;">
                            </div>
                            <div>
                                <label>Prix domicile</label>
                                <input type="number" name="w_dom" min="0" style="padding:8px;">
                            </div>
                            <div>
                                <label>Prix bureau</label>
                                <input type="number" name="w_stop" min="0" style="padding:8px;">
                            </div>
                            <div>
                                <label>Active</label>
                                <select name="w_active" style="padding:8px;">
                                    <option value="1">Oui</option>
                                    <option value="0">Non</option>
                                </select>
                            </div>
                            <button class="btn" name="save_wilaya" value="1" type="submit">Enregistrer</button>
                        </form>
                        <?php
                        // Handle wilaya save/delete
                        try {
                            $db->exec("CREATE TABLE IF NOT EXISTS wilayas (id INT PRIMARY KEY, name VARCHAR(100) NOT NULL, domicile_price INT NOT NULL DEFAULT 0, stopdesk_price INT NOT NULL DEFAULT 0, is_active TINYINT(1) NOT NULL DEFAULT 1) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                            if (isset($_POST['save_wilaya'])) {
                                $wid = isset($_POST['w_id']) ? (int) $_POST['w_id'] : 0;
                                $wname = trim($_POST['w_name'] ?? '');
                                $wdom = isset($_POST['w_dom']) ? (int) $_POST['w_dom'] : 0;
                                $wstop = isset($_POST['w_stop']) ? (int) $_POST['w_stop'] : 0;
                                $wactive = isset($_POST['w_active']) ? (int) $_POST['w_active'] : 1;
                                if ($wid > 0 && $wname !== '') {
                                    $stmt = $db->prepare("INSERT INTO wilayas (id,name,domicile_price,stopdesk_price,is_active) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name), domicile_price=VALUES(domicile_price), stopdesk_price=VALUES(stopdesk_price), is_active=VALUES(is_active)");
                                    $stmt->execute([$wid, $wname, $wdom, $wstop, $wactive]);
                                    echo '<div class="alert alert-success">Wilaya enregistrée.</div>';
                                } else {
                                    echo '<div class="alert alert-error">Veuillez fournir un ID et un nom.</div>';
                                }
                            }
                            if (isset($_POST['delete_wilaya'])) {
                                $wid = isset($_POST['delete_wilaya']) ? (int) $_POST['delete_wilaya'] : 0;
                                if ($wid > 0) {
                                    $stmt = $db->prepare("DELETE FROM wilayas WHERE id=?");
                                    $stmt->execute([$wid]);
                                    echo '<div class="alert alert-success">Wilaya supprimée.</div>';
                                }
                            }
                            $wlist = $db->query("SELECT * FROM wilayas ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
                        } catch (Exception $e) {
                            $wlist = [];
                            echo '<div class="alert alert-error">Erreur: ' . htmlspecialchars($e->getMessage()) . '</div>';
                        }
                        ?>
                        <div style="overflow:auto;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Domicile</th>
                                        <th>Bureau</th>
                                        <th>Active</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($wlist as $w): ?>
                                        <tr>
                                            <td><?= (int) $w['id'] ?></td>
                                            <td><?= htmlspecialchars($w['name']) ?></td>
                                            <td><?= (int) $w['domicile_price'] ?> DA</td>
                                            <td><?= (int) $w['stopdesk_price'] ?> DA</td>
                                            <td><?= (int) $w['is_active'] ? 'Oui' : 'Non' ?></td>
                                            <td>
                                                <button class="btn"
                                                    onclick="editWilaya(<?= (int) $w['id'] ?>, '<?= htmlspecialchars($w['name']) ?>', <?= (int) $w['domicile_price'] ?>, <?= (int) $w['stopdesk_price'] ?>, <?= (int) $w['is_active'] ?>)">Modifier</button>
                                                <form method="post" style="display:inline;"
                                                    onsubmit="return confirm('Supprimer cette wilaya ?');">
                                                    <button class="btn btn-danger" type="submit" name="delete_wilaya"
                                                        value="<?= (int) $w['id'] ?>">Supprimer</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Site settings, hero and videos tabs removed (not supported by simplified schema) -->
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal" id="addProductModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Ajouter un nouveau produit</h3>
                <button class="modal-close" onclick="hideAddProductModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_product" value="1">

                <div class="form-group">
                    <label for="name">Nom du produit</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required></textarea>
                </div>

                <div class="form-group">
                    <label>Dimensions (variantes)</label>
                    <div id="dimensions-container">
                        <!-- dimension rows will be added dynamically -->
                    </div>
                    <button type="button" class="btn btn-small btn-secondary" onclick="addDimensionRow()">
                        <i class="fas fa-plus"></i> Ajouter une dimension
                    </button>
                    <div class="form-group">
                        <label>Couleurs du produit (optionnel)</label>
                        <div id="colors-container">
                            <!-- Colors will be added by JavaScript -->
                        </div>
                        <button type="button" class="btn btn-small btn-secondary" onclick="addColorRow()">
                            <i class="fas fa-plus"></i> Ajouter une couleur
                        </button>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Ajouter
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="hideAddProductModal()">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal" id="editProductModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Modifier le produit</h3>
                <button class="modal-close" onclick="hideEditProductModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_product" value="1">
                <input type="hidden" name="product_id" id="edit_product_id">
                <input type="hidden" name="current_image_path" id="edit_current_image_path">

                <div class="form-group">
                    <label for="edit_name">Nom du produit</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" required></textarea>
                </div>

                <div class="form-group">
                    <label>Dimensions (variantes)</label>
                    <div id="edit-dimensions-container">
                        <!-- Rows will be added dynamically to support multiple images per dimension -->
                    </div>
                    <button type="button" class="btn btn-small btn-secondary" onclick="addEditDimensionRow()">
                        <i class="fas fa-plus"></i> Ajouter une dimension
                    </button>
                    <!-- Colors container for edit modal (was missing, caused JS errors) -->
                    <div id="edit-colors-container">
                        <!-- Colors will be added by JavaScript -->
                    </div>
                    <button type="button" class="btn btn-small btn-secondary" onclick="addEditColorRow()">
                        <i class="fas fa-plus"></i> Ajouter une couleur
                    </button>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="hideEditProductModal()">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>



    <!-- Receipt Image Modal -->
    <div class="modal" id="receiptModal">
        <div class="modal-content">
            <button class="modal-close" onclick="hideReceiptImage()">&times;</button>
            <div class="modal-title">Reçu de paiement</div>
            <div style="text-align: center;">
                <img id="receiptImageDisplay" src="" style="max-width: 100%; max-height: 70vh;">
            </div>
        </div>
    </div>


    <!-- Client Details Modal -->
    <div class="modal client-details-modal" id="clientDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Détails du client</h3>
                <button class="modal-close" onclick="hideClientDetails()">&times;</button>
            </div>
            <div class="client-details" id="clientDetailsContent">
                <!-- Content will be inserted by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Show/hide tabs
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');

            // Update active state in sidebar
            document.querySelectorAll('.sidebar-link').forEach(link => {
                link.classList.remove('active');
            });

            if (tabId === 'dashboard') {
                document.querySelector('.sidebar-link[href="dashboard.php"]').classList.add('active');
            } else {
                document.querySelector(`.sidebar-link[href="#${tabId}"]`).classList.add('active');
            }

            // Close sidebar on mobile after selection
            if (window.innerWidth < 992) {
                toggleSidebar();
            }
        }

        // Product modals
        function showAddProductModal() {
            document.getElementById('addProductModal').style.display = 'flex';
            // Initialize first dimension row when opening add modal
            const dimContainer = document.getElementById('dimensions-container');
            dimContainer.innerHTML = '';
            dimensionIndex = 0;
            addDimensionRow();
            // Initialize colors container with one row
            const colorContainer = document.getElementById('colors-container');
            if (colorContainer) {
                colorContainer.innerHTML = '';
                addColorRow();
            }
        }

        function hideAddProductModal() {
            document.getElementById('addProductModal').style.display = 'none';
        }

        // Edit product modal functions
        function showEditProductModal(product, dimensions, colors) {
            // Populate form fields with product data
            document.getElementById('edit_product_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_description').value = product.description;
            document.getElementById('edit_current_image_path').value = '';

            // Populate dimensions
            const container = document.getElementById('edit-dimensions-container');
            container.innerHTML = '';
            // reset index counter so uploaded files map to new dimension rows
            dimensionIndex = 0;
            if (dimensions && dimensions.length) {
                dimensions.forEach(dim => addEditDimensionRow(dim));
            } else {
                addEditDimensionRow();
            }

            // Populate colors
            populateEditColors(colors);

            // Show modal
            document.getElementById('editProductModal').style.display = 'flex';
        }

        // Wrapper to read JSON from data attributes (avoids JSON escaping issues in HTML)
        function openEditProductFromData(button) {
            try {
                const product = JSON.parse(button.getAttribute('data-product') || '{}');
                const dimensions = JSON.parse(button.getAttribute('data-dimensions') || '[]');
                const colors = JSON.parse(button.getAttribute('data-colors') || '[]');
                showEditProductModal(product, dimensions, colors);
            } catch (err) {
                alert('Erreur: impossible d\'ouvrir la fenêtre d\'édition. Voir la console pour plus de détails.');
                console.error('openEditProductFromData error', err);
            }
        }

        function hideEditProductModal() {
            const modal = document.getElementById('editProductModal');
            if (modal) {
                modal.style.display = 'none';
                // Clear form if present
                const form = modal.querySelector('form');
                if (form) form.reset();
            }

            const editPreview = document.getElementById('edit_current_image_preview');
            if (editPreview) editPreview.style.display = 'none';

            const editColors = document.getElementById('edit-colors-container');
            if (editColors) editColors.innerHTML = '';
        }

        function populateEditColors(colors) {
            const container = document.getElementById('edit-colors-container');
            if (!container) return; // defensive: avoid errors if element is missing
            container.innerHTML = '';

            if (colors && colors.length > 0) {
                colors.forEach((color, index) => {
                    addEditColorRow(color);
                });
            } else {
                addEditColorRow();
            }
        }

        function addEditColorRow(colorData = null) {
            const container = document.getElementById('edit-colors-container');
            const colorRow = document.createElement('div');
            colorRow.className = 'color-row';

            const colorName = colorData ? colorData.color_name : '';
            const colorCode = colorData ? colorData.color_code : '#000000';
            const colorType = colorData ? colorData.type : 'tissu';
            const priceMod = colorData ? (colorData.price_modifier || '') : '';

            colorRow.innerHTML = `
                <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px;">
                    <select name="color_types[]" style="width:120px;"><option value="tissu" ${colorType == 'tissu' ? 'selected' : ''}>Tissu</option><option value="bois" ${colorType == 'bois' ? 'selected' : ''}>Bois</option></select>
                    <input type="text" name="color_names[]" placeholder="Nom de la couleur" style="flex: 1; width: 140PX;" value="${colorName}">
                    <input type="color" name="color_codes[]" value="${colorCode}" style="width: 50px; height: 40px;">
                    <input type="number" name="price_modifiers[]" placeholder="+Prix" style="width:90px;" step="0.01" min="0" value="${priceMod}">
                    <button type="button" class="btn btn-small btn-danger" onclick="removeEditColorRow(this)"><i class="fas fa-trash"></i></button>
                </div>
            `;
            container.appendChild(colorRow);
        }

        // Dimension row helpers
        // Counter to index dimension rows so each can have its own multi-file input name
        let dimensionIndex = 0;

        /* Remplace ton addDimensionRow par ceci */
        function addDimensionRow() {
            const container = document.getElementById('dimensions-container');
            const index = dimensionIndex++;
            const row = document.createElement('div');
            row.className = 'dimension-row';
            row.style.marginBottom = '10px';

            // inner HTML : on ne crée PAS d'input file visible ici — on utilisera des vignettes cliquables
            row.innerHTML = `
        <div style="display:flex; gap:10px; align-items:center;">
            <input type="number" name="width_cm[]" placeholder="Largeur (cm)" style="width:110px;" min="0">
            <input type="number" name="height_cm[]" placeholder="Hauteur (cm)" style="width:110px;" min="0">
            <input type="text" name="dim_label[]" placeholder="Label (ex: 120x70)" style="width:131px;">
            <input type="number" name="dim_price[]" placeholder="Prix (DA)" style="width:120px;" step="0.01" min="0">
            <input type="number" name="dim_stock[]" placeholder="Stock" style="width:90px;" min="0">


            <button type="button" class="btn btn-small btn-danger" onclick="removeDimensionRow(this)"><i class="fas fa-trash"></i></button>
        </div>
                    <div class="images-wrapper" style="display:flex; gap:8px; align-items:center;">
                <!-- container pour inputs cachés (pour l'envoi du form) -->
                <div class="images-container" data-index="${index}" data-name="dimension_images"></div>

                <!-- bouton + Img : crée une vignette cliquable + input caché -->
                <div class="image-preview-square empty" tabindex="0" role="button" onclick="appendImageInput(this)">+</div>

                <!-- les aperçus (vignettes) seront créés automatiquement par appendImageInput -->
            </div>
    `;
            container.appendChild(row);
        }


        function removeDimensionRow(btn) {
            const row = btn.closest('.dimension-row');
            if (row) row.remove();
        }

        function addEditDimensionRow(dimData = null) {
            const container = document.getElementById('edit-dimensions-container');
            const row = document.createElement('div');
            row.className = 'dimension-row';
            row.style.marginBottom = '10px';
            const width = dimData ? (dimData.width_cm || '') : '';
            const height = dimData ? (dimData.height_cm || '') : '';
            const label = dimData ? (dimData.label || '') : '';
            const price = dimData ? (dimData.price || '') : '';
            const stock = dimData ? (dimData.stock || '') : '';
            const index = dimensionIndex++;
            row.innerHTML = `
    <div style="display:flex; gap:10px; align-items:center;">
      <input type="number" name="width_cm[]" placeholder="Largeur (cm)" style="width:110px;" min="0" value="${width}">
      <input type="number" name="height_cm[]" placeholder="Hauteur (cm)" style="width:110px;" min="0" value="${height}">
      <input type="text" name="dim_label[]" placeholder="Label (ex: 120x70)" style="width:131px;" value="${label}">
      <input type="number" name="dim_price[]" placeholder="Prix (DA)" style="width:120px;" step="0.01" min="0" value="${price}">
      <input type="number" name="dim_stock[]" placeholder="Stock" style="width:110px;" min="0" value="${stock}">

      <button type="button" class="btn btn-small btn-danger" onclick="removeDimensionRow(this)"><i class="fas fa-trash"></i></button>
    </div>

    <div class="images-wrapper" style="display:flex; gap:8px; align-items:center;">
      <div class="images-container" data-index="${index}" data-name="dimension_images"></div>

      <!-- main + preview square (clicking it creates a NEW file input and preview) -->
      <div class="image-preview-square empty" tabindex="0" role="button" onclick="appendImageInput(this)">+</div>

      <!-- previews area will be inserted here by code -->
    </div>
  `;
            container.appendChild(row);

            // If dimension has existing images, show them as thumbnails (and create hidden markers)
            if (dimData && Array.isArray(dimData.images) && dimData.images.length) {
                const wrapper = row.querySelector('.images-wrapper');
                if (wrapper) {
                    let previews = wrapper.querySelector('.image-previews');
                    if (!previews) {
                        previews = document.createElement('div');
                        previews.className = 'image-previews';
                        // insert previews before the images-container's next sibling (keeps layout)
                        wrapper.insertBefore(previews, wrapper.querySelector('.images-container').nextSibling);
                    }

                    // dimData.images expected to be an array of objects { image_path: "...", id: ... }
                    dimData.images.forEach(imgObj => {
                        const imagePath = imgObj.image_path ? (imgObj.image_path.startsWith('/') ? imgObj.image_path : ('../' + imgObj.image_path)) : (imgObj.src || '');
                        const imageId = imgObj.id ?? imgObj.filename ?? imagePath;
                        createThumbnail(previews, imagePath, { existing: true, imageId: imageId, imagePath: imgObj.image_path || imagePath });
                    });
                }
            } else {
                // ensure there's a previews container in case there are none yet
                const wrapper = row.querySelector('.images-wrapper');
                if (wrapper && !wrapper.querySelector('.image-previews')) {
                    const previews = document.createElement('div');
                    previews.className = 'image-previews';
                    wrapper.appendChild(previews);
                }
            }
        }

        // Append a new file input in the same dimension images container
        /* Crée un input type=file caché + vignette vide cliquable */
        function appendImageInput(buttonOrPreview) {
            // buttonOrPreview can be the + square or any element inside .images-wrapper
            const wrapper = buttonOrPreview.closest('.images-wrapper');
            if (!wrapper) return;
            const container = wrapper.querySelector('.images-container');
            if (!container) return;

            // ensure previews container exists
            let previews = wrapper.querySelector('.image-previews');
            if (!previews) {
                previews = document.createElement('div');
                previews.className = 'image-previews';
                wrapper.appendChild(previews);
            }

            const idx = container.dataset.index ?? 0;
            const baseName = container.getAttribute('data-name') || 'dimension_images';

            // create hidden file input for the new image
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.name = `${baseName}[${idx}][]`;
            input.style.display = 'none';

            // create preview square (empty)
            const preview = document.createElement('div');
            preview.className = 'image-preview-square empty';
            preview.tabIndex = 0;
            preview.setAttribute('role', 'button');
            preview.title = 'Cliquer pour choisir une image (Entrée pour ouvrir)';

            // remove button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-image-btn';
            removeBtn.innerText = '✕';
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (input && input.parentNode) input.remove();
                preview.remove();
            });

            // append input & preview to DOM
            container.appendChild(input);
            previews.appendChild(preview);
            preview.appendChild(removeBtn);

            // click / keyboard opens the file selector
            preview.addEventListener('click', () => input.click());
            preview.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    input.click();
                }
            });

            input.addEventListener('change', () => {
                const file = input.files && input.files[0];
                if (!file) {
                    preview.classList.add('empty');
                    const existingImg = preview.querySelector('img.preview-img');
                    if (existingImg) existingImg.remove();
                    return;
                }
                if (!file.type.startsWith('image/')) {
                    alert('Veuillez choisir un fichier image.');
                    input.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (e) {
                    let img = preview.querySelector('img.preview-img');
                    if (!img) {
                        img = document.createElement('img');
                        img.className = 'preview-img';
                        preview.insertBefore(img, removeBtn);
                    }
                    img.src = e.target.result;
                    img.alt = file.name || 'preview';
                    preview.classList.remove('empty');
                    preview.style.backgroundImage = `url(${e.target.result})`;
                };
                reader.readAsDataURL(file);
            });

            // auto-open file dialog
            input.click();
        } function removeImageInput(button) {
            const row = button.closest('.image-input-row');
            if (row) row.remove();
        }

        // Create a thumbnail element in the previews container
        /*         function createThumbnail(previewsContainer, src, isRemote = false) {
                    if (!previewsContainer) return;
                    const thumb = document.createElement('div');
                    thumb.className = 'image-thumb';
                    const img = document.createElement('img');
                    if (isRemote) {
                        img.src = src;
                        thumb.appendChild(img);
                    } else {
                        img.src = src;
                        thumb.appendChild(img);
                    }
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'remove-thumb';
                    btn.innerHTML = '&times;';
                    btn.addEventListener('click', () => thumb.remove());
                    thumb.appendChild(btn);
                    previewsContainer.appendChild(thumb);
                }
         */
        // Listen for file input changes and show previews
        document.addEventListener('change', function (e) {
            const input = e.target;
            if (!input || input.tagName !== 'INPUT' || input.type !== 'file') return;
            if (!input.name || input.name.indexOf('dimension_images') === -1) return;

            // find images-wrapper and previews container
            const wrapper = input.closest('.images-wrapper');
            if (!wrapper) return;
            /*             let previews = wrapper.querySelector('.image-previews');
                        if (!previews) {
                            previews = document.createElement('div');
                            previews.className = 'image-previews';
                            wrapper.insertBefore(previews, wrapper.querySelector('.images-container').nextSibling);
                        } */

            // If multiple files selected on this input, append previews for each
            const files = Array.from(input.files || []);
            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = function (ev) {
                    createThumbnail(previews, ev.target.result, false);
                };
                reader.readAsDataURL(file);
            });
        });

        function removeEditColorRow(button) {
            const colorRow = button.closest('.color-row');
            if (colorRow) {
                colorRow.remove();
            }
        }

        function toggleEditPromotionFields() {
            const isPromotionChecked = document.getElementById('edit_is_promotion').checked;
            const promotionFields = document.querySelectorAll('.edit-promotion-fields');

            promotionFields.forEach(field => {
                field.style.display = isPromotionChecked ? 'block' : 'none';
            });

            // If promotion is unchecked, clear the promotion fields
            if (!isPromotionChecked) {
                document.getElementById('edit_old_price').value = '';
            }
        }



        // Receipt image modal
        function showReceiptImage(imageSrc) {
            document.getElementById('receiptImageDisplay').src = imageSrc;
            document.getElementById('receiptModal').style.display = 'flex';
        }

        function showPDFPopup(src) {
            const popup = window.open('', '_blank', 'width=800,height=600');
            popup.document.write(`
                <html>
                    <head><title>Reçu PDF</title></head>
                    <body style="margin:0;">
                        <iframe src="${src}" width="100%" height="100%" style="border:none;"></iframe>
                    </body>
                </html>
            `);
        }

        function hideReceiptImage() {
            document.getElementById('receiptModal').style.display = 'none';
        }

        function showClientDetailsAjax(orderId) {
            // admin/dashboard.php lives in the admin/ folder, endpoint is at project root => go up one level
            fetch('../get_order_details.php?id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) { alert(data.error); return; }
                    const order = data.order || {};
                    const items = data.items || [];
                    const content = document.getElementById('clientDetailsContent');
                    let itemsHtml = '';
                    if (items.length) {
                        itemsHtml += '<div class="detail-card"><div class="card-title"><i class="fas fa-box"></i> Produits</div>';
                        items.forEach(it => {
                            const price = parseFloat(it.product_price || it.unit_price || 0).toFixed(2);
                            const line = (parseFloat(it.product_price || it.unit_price || 0) * (parseInt(it.quantity || 1))).toFixed(2);
                            itemsHtml += `<div class="detail-line"><span class="detail-label">${it.product_name || 'Produit'}</span><span class="detail-value">Taille: ${it.size || 'N/A'}, Qte: ${it.quantity || 1}, Prix: ${price} DA, Ligne: ${line} DA</span></div>`;
                            if (it.tissu_color_code) itemsHtml += `<div style="margin:6px 0"><strong>Couleur tissu</strong>: <span style="display:inline-block;width:18px;height:18px;background-color:${it.tissu_color_code};border:1px solid #ddd;border-radius:50%;vertical-align:middle;margin-left:8px;"></span></div>`;
                            if (it.bois_color_code) itemsHtml += `<div style="margin:6px 0"><strong>Couleur bois</strong>: <span style="display:inline-block;width:18px;height:18px;background-color:${it.bois_color_code};border:1px solid #ddd;border-radius:50%;vertical-align:middle;margin-left:8px;"></span></div>`;
                            const imageToShow = it.color_image || it.product_image;
                            if (imageToShow) itemsHtml += `<div style="margin-top:8px"><img src="../${imageToShow}" class="product-image-thumb" style="max-width:120px;border:2px solid #eee"></div>`;
                        });
                        itemsHtml += '</div>';
                    }

                    const subtotal = data.subtotal || 0;
                    const delivery = data.delivery_price || 0;
                    const total = data.total_price || (subtotal + delivery);

                    content.innerHTML = `
                        <div class="client-summary">
                            <div class="client-summary-item"><div class="summary-label">Client</div><div class="summary-value">${order.customer_name || 'N/A'}</div></div>
                            <div class="client-summary-item"><div class="summary-label">Téléphone</div><div class="summary-value">${order.customer_phone || 'N/A'}</div></div>
                            <div class="client-summary-item"><div class="summary-label">Wilaya</div><div class="summary-value">${order.wilaya_name || order.wilaya || 'N/A'}</div></div>
                        </div>
                        <div class="detail-cards">
                            <div class="detail-card"><div class="card-title"><i class="fas fa-map-marker-alt"></i> Adresse</div><div class="detail-line"><span class="detail-label">Commune:</span><span class="detail-value">${order.commune || 'N/A'}</span></div><div class="detail-line"><span class="detail-label">Adresse:</span><span class="detail-value">${order.customer_address || order.address || 'N/A'}</span></div></div>
                            ${itemsHtml}
                            <div class="detail-card"><div class="card-title"><i class="fas fa-money-bill-wave"></i> Récapitulatif</div><div class="detail-line"><span class="detail-label">Sous-total:</span><span class="detail-value">${parseFloat(subtotal).toFixed(2)} DA</span></div><div class="detail-line"><span class="detail-label">Livraison:</span><span class="detail-value">${parseFloat(delivery).toFixed(2)} DA</span></div><div class="detail-line"><span class="detail-label">Total:</span><span class="detail-value">${parseFloat(total).toFixed(2)} DA</span></div></div>
                        </div>`;
                    document.getElementById('clientDetailsModal').style.display = 'flex';
                });
        }

        function hideClientDetails() {
            document.getElementById('clientDetailsModal').style.display = 'none';
        }




        // Color management functions
        function addColorRow() {
            const container = document.getElementById('colors-container');
            const colorRow = document.createElement('div');
            colorRow.className = 'color-row';
            colorRow.innerHTML = `
                <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px;">
                    <select name="color_types[]" style="width:120px;"><option value="tissu">Tissu</option><option value="bois">Bois</option></select>
                    <input type="text" name="color_names[]" placeholder="Nom de la couleur" style="flex: 1; width: 140PX;">
                    <input type="color" name="color_codes[]" value="#000000" style="width: 50px; height: 40px;">
                    <input type="number" name="price_modifiers[]" placeholder="+Prix" style="width:90px;" step="0.01" min="0">
                    <button type="button" class="btn btn-small btn-danger" onclick="removeColorRow(this)"><i class="fas fa-trash"></i></button>
                </div>
            `;
            container.appendChild(colorRow);
        }



        function removeColorRow(button) {
            const colorRow = button.closest('.color-row');
            if (colorRow) {
                colorRow.remove();
            }
        }

        // Gallery management functions (add/remove rows in Add Product modal)
        function addGalleryImageRow() {
            const container = document.getElementById('gallery-images-container');
            if (!container) return;
            const row = document.createElement('div');
            row.className = 'gallery-image-row';
            row.style.cssText = 'display:flex; gap:10px; align-items:center; margin-bottom:10px;';
            row.innerHTML = `
                <input type="file" name="gallery_images[]" accept="image/*" style="flex:1;">
                <button type="button" class="btn btn-small btn-danger" onclick="removeGalleryImageRow(this)">Supprimer</button>
            `;
            container.appendChild(row);
        }

        function removeGalleryImageRow(btn) {
            const row = btn.closest('.gallery-image-row');
            if (row) row.remove();
        }

        // (video selection helper removed since video slides feature is removed)

        // Promotion fields toggle function
        function togglePromotionFields() {
            const isPromotionChecked = document.getElementById('is_promotion').checked;
            const promotionFields = document.querySelectorAll('.promotion-fields');

            promotionFields.forEach(field => {
                field.style.display = isPromotionChecked ? 'block' : 'none';
            });

            // If promotion is unchecked, clear the promotion fields
            if (!isPromotionChecked) {
                document.getElementById('old_price').value = '';
            }
        }

        // If URL contains #orders, show the orders tab on load
        window.addEventListener('DOMContentLoaded', function () {
            if (window.location.hash === '#orders') {
                showTab('orders');
            } else if (window.location.hash === '#products') {
                showTab('products');
            } else if (window.location.hash === '#wilayas') {
                showTab('wilayas');
            } else if (window.location.hash === '#archives') {
                showTab('archives');
            }
            // Show success notification if product was added
            <?php if (isset($_SESSION['product_added_success'])): ?>
                // The success message will be displayed by the existing PHP alert system
                <?php unset($_SESSION['product_added_success']); ?>
            <?php endif; ?>
        });

        // Function to edit wilaya - populate form with existing values
        function editWilaya(id, name, domicilePrice, stopdeskPrice, isActive) {
            document.querySelector('input[name="w_id"]').value = id;
            document.querySelector('input[name="w_name"]').value = name;
            document.querySelector('input[name="w_dom"]').value = domicilePrice;
            document.querySelector('input[name="w_stop"]').value = stopdeskPrice;
            document.querySelector('select[name="w_active"]').value = isActive;

            // Scroll to form
            document.querySelector('input[name="w_id"]').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function createThumbnail(previews, imagePath, options = {}) {
            options = options || {};
            // find wrapper and container index for naming
            const wrapper = previews.closest('.images-wrapper');
            const container = wrapper.querySelector('.images-container');
            const idx = container ? container.dataset.index : 0;
            const baseName = container ? (container.getAttribute('data-name') || 'dimension_images') : 'dimension_images';

            // preview element
            const preview = document.createElement('div');
            preview.className = 'image-preview-square';
            preview.tabIndex = 0;
            preview.setAttribute('role', 'button');

            // image element
            const img = document.createElement('img');
            img.className = 'preview-img';
            img.src = imagePath;
            preview.appendChild(img);

            // remove button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-image-btn';
            removeBtn.innerText = '✕';
            preview.appendChild(removeBtn);

            // append preview into previews area
            previews.appendChild(preview);

            // if existing image, insert a hidden input marker (so backend knows to keep it)
            // name: dimension_existing_images[IDX][] (you can change the name; adjust backend)
            let existingMarker = null;
            if (options.existing) {
                existingMarker = document.createElement('input');
                existingMarker.type = 'hidden';
                // prefer id if provided, else send path
                const keepValue = options.imageId ?? options.imagePath ?? imagePath;
                existingMarker.name = `dimension_existing_images[${idx}][]`;
                existingMarker.value = keepValue;
                // store the marker in the images-container so it's submitted with the form
                container.appendChild(existingMarker);

                // mark preview as existing so we can treat it on remove/replace
                preview.dataset.existing = '1';
                preview.dataset.existingValue = keepValue;
            }

            // Setup replace behavior: clicking the thumbnail opens a hidden file input to replace this image
            const replaceInput = document.createElement('input');
            replaceInput.type = 'file';
            replaceInput.accept = 'image/*';
            replaceInput.style.display = 'none';
            // important: new files go to the same server param as other new files
            replaceInput.name = `${baseName}[${idx}][]`;
            // append replaceInput to images-container for form submission
            container.appendChild(replaceInput);

            preview.addEventListener('click', () => {
                replaceInput.click();
            });
            preview.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    replaceInput.click();
                }
            });

            // when user picks a file to replace the existing image
            replaceInput.addEventListener('change', () => {
                const file = replaceInput.files && replaceInput.files[0];
                if (!file) return; // cancelled

                if (!file.type.startsWith('image/')) {
                    alert('Please choose an image file.');
                    replaceInput.value = '';
                    return;
                }

                // Remove existing marker (we will send a new file in its place)
                if (existingMarker && existingMarker.parentNode) existingMarker.remove();
                preview.dataset.existing = '0';
                delete preview.dataset.existingValue;

                // show preview of new file
                const reader = new FileReader();
                reader.onload = (e) => {
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);

                // ensure preview has 'non-empty' style if you use that class logic
                preview.classList.remove('empty');
            });

            // remove button: stops propagation and removes preview + associated inputs
            removeBtn.addEventListener('click', (ev) => {
                ev.stopPropagation();
                // if there is an existing marker, remove it (so server won't keep it)
                if (existingMarker && existingMarker.parentNode) existingMarker.remove();
                // if replaceInput exists in DOM remove it too (prevents sending a file)
                if (replaceInput && replaceInput.parentNode) replaceInput.remove();
                preview.remove();
            });

            return preview;
        }

    </script>
</body>

</html>