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
    <title>Tableau de bord </title>
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
            <?php $messages_count = (int) ($unread_messages ?? $db->query("SELECT COUNT(*) FROM customer_messages")->fetchColumn()); ?>
            <div class="sidebar-nav">
                <a href="dashboard.php"
                    class="sidebar-link <?= ($page ?? 'dashboard') === 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de bord</span>
                </a>
                <a href="dashboard.php?page=orders"
                    class="sidebar-link <?= ($page ?? '') === 'orders' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Commandes</span>
                </a>
                <a href="dashboard.php?page=products"
                    class="sidebar-link <?= ($page ?? '') === 'products' ? 'active' : '' ?>">
                    <i class="fas fa-tshirt"></i>
                    <span>Produits</span>
                </a>
                <a href="dashboard.php?page=categories"
                    class="sidebar-link <?= ($page ?? '') === 'categories' ? 'active' : '' ?>">
                    <i class="fas fa-list-alt"></i>
                    <span>Catégories</span>
                </a>
                <a href="dashboard.php?page=messages"
                    class="sidebar-link <?= ($page ?? '') === 'messages' ? 'active' : '' ?>">
                    <i class="fas fa-comments"></i>
                    <span>Messages <span class="badge"><?= (int) $messages_count ?></span></span>
                </a>
                <a href="dashboard.php?page=wilayas"
                    class="sidebar-link <?= ($page ?? '') === 'wilayas' ? 'active' : '' ?>">
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

            <?php $page = $_GET['page'] ?? 'dashboard'; ?>

            <div id="dashboard" class="tab-content <?= $page === 'dashboard' ? 'active' : '' ?>">
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
                    <div class="stat-card">
                        <i class="fas fa-box-open"></i>
                        <h3>Produits</h3>
                        <p><?= (int) ($total_products ?? 0) ?></p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-envelope"></i>
                        <h3>Messages non lus</h3>
                        <p><?= (int) ($unread_messages ?? 0) ?></p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Dernières commandes</h2>
                        <a href="dashboard.php?page=orders" class="btn">Voir tout</a>
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
                                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
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

            <div id="orders" class="tab-content <?= $page === 'orders' ? 'active' : '' ?>">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Toutes les commandes</h2>
                        <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                            <input type="hidden" name="page" value="orders">
                            <input type="text" name="search_phone" placeholder="Rechercher par téléphone"
                                value="<?= isset($_GET['search_phone']) ? htmlspecialchars($_GET['search_phone']) : '' ?>"
                                style="padding: 6px; border-radius: 4px; border: 1px solid #ccc;">
                            <button type="submit" class="btn btn-small btn-info"><i class="fas fa-search"></i>
                                Rechercher</button>
                            <?php if (!empty($_GET['search_phone'])): ?>
                                <a href="dashboard.php?page=orders" class="btn btn-small btn-secondary">Réinitialiser</a>
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
                                            <?php
                                            // Prefer new schema column names, fall back to older ones if present
                                            $firstImage = null;
                                            if (!empty($items) && !empty($items[0]['image_path'])) {
                                                $firstImage = $items[0]['image_path'];
                                            } elseif (!empty($items) && !empty($items[0]['product_image'])) {
                                                $firstImage = $items[0]['product_image'];
                                            } elseif (!empty($order['image_path'])) {
                                                $firstImage = $order['image_path'];
                                            } elseif (!empty($order['product_image'])) {
                                                $firstImage = $order['product_image'];
                                            }
                                            if (!empty($firstImage)): ?>
                                                <img src="../<?= $firstImage ?>" class="product-image-thumb">
                                            <?php else: ?>
                                                <img src="../images/default_product.jpg" class="product-image-thumb">
                                            <?php endif; ?>
                                        </td>
                                        <td class="price-details">
                                            <?php if ($items): ?>
                                                <?php $total = 0;
                                                foreach ($items as $item) {
                                                    $unit = $item['unit_price'] ?? $item['product_price'] ?? $item['price'] ?? 0;
                                                    $qty = $item['quantity'] ?? 1;
                                                    $total += $unit * $qty;
                                                } ?>
                                                <span class="label">Produit:</span> <?= number_format($total, 2, ',', ' ') ?>
                                                DA<br>
                                                <span class="label">Livraison:</span>
                                                <?= number_format($order['delivery_price'] ?? 0, 2, ',', ' ') ?> DA<br>
                                                <span class="label">Total:</span>
                                                <?= number_format($order['total_price'] ?? ($total + ($order['delivery_price'] ?? 0)), 2, ',', ' ') ?>
                                                DA
                                            <?php else: ?>
                                                <?php $singleProductPrice = ($order['product_price'] ?? null);
                                                if ($singleProductPrice === null) {
                                                    $singleProductPrice = ($order['total_price'] ?? 0) - ($order['delivery_price'] ?? 0);
                                                }
                                                ?>
                                                <span class="label">Produit:</span>
                                                <?= number_format($singleProductPrice, 2, ',', ' ') ?> DA<br>
                                                <span class="label">Livraison:</span>
                                                <?= number_format($order['delivery_price'] ?? 0, 2, ',', ' ') ?> DA<br>
                                                <span class="label">Total:</span>
                                                <?= number_format($order['total_price'] ?? ($singleProductPrice + ($order['delivery_price'] ?? 0)), 2, ',', ' ') ?>
                                                DA
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'] ?? $order['order_date'] ?? 'now')) ?>
                                        </td>
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

            <div id="archives" class="tab-content <?= $page === 'archives' ? 'active' : '' ?>">
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
                                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
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


            <div id="products" class="tab-content <?= $page === 'products' ? 'active' : '' ?>">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Gestion des Produits</h2>
                        <button class="btn" onclick="showAddProductModal()">
                            <i class="fas fa-plus"></i> Ajouter un produit
                        </button>
                    </div>

                    <form method="get"
                        style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;margin-bottom:16px">
                        <input type="hidden" name="page" value="products">
                        <select name="type" style="padding:8px;border:1px solid #ddd;border-radius:6px;">
                            <option value="">Tous les types</option>
                            <option value="made_to_order" <?= (isset($_GET['type']) && $_GET['type'] === 'made_to_order') ? 'selected' : '' ?>>Sur Commande</option>
                            <option value="available" <?= (isset($_GET['type']) && $_GET['type'] === 'available') ? 'selected' : '' ?>>Disponible</option>
                        </select>
                        <?php $cats = $db->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC); ?>
                        <select name="category" style="padding:8px;border:1px solid #ddd;border-radius:6px;">
                            <option value="">Toutes catégories</option>
                            <?php foreach ($cats as $ct): ?>
                                <option value="<?= $ct['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $ct['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ct['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="search" name="q" placeholder="Rechercher nom / description"
                            value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                            style="padding:8px;border:1px solid #ddd;border-radius:6px; min-width:200px;">
                        <button type="submit" class="btn">Filtrer</button>
                        <a href="dashboard.php?page=products" class="btn btn-secondary">Réinitialiser</a>
                    </form>

                    <div class="product-list">
                        <?php
                        $typeFilter = $_GET['type'] ?? null;
                        $categoryFilter = $_GET['category'] ?? null;
                        $q = trim($_GET['q'] ?? '');
                        $productsToShow = $products;
                        if ($typeFilter === 'made_to_order' || $typeFilter === 'available') {
                            $productsToShow = array_filter($productsToShow, function ($p) use ($typeFilter) {
                                return ($p['product_type'] ?? 'made_to_order') === $typeFilter;
                            });
                        }
                        if (!empty($categoryFilter)) {
                            $productsToShow = array_filter($productsToShow, function ($p) use ($categoryFilter) {
                                return ($p['category_id'] ?? '') == $categoryFilter;
                            });
                        }
                        if ($q !== '') {
                            $productsToShow = array_filter($productsToShow, function ($p) use ($q) {
                                return (stripos($p['name'] ?? '', $q) !== false) || (stripos($p['description'] ?? '', $q) !== false);
                            });
                        }
                        ?>
                        <?php foreach ($productsToShow as $product): ?>
                            <div class="product-card">
                                <?php
                                // Determine primary product image (new schema uses product_images table)
                                $primaryImage = null;
                                if (!empty($product['images'])) {
                                    $primaryImage = $product['images'][0]['image_path'];
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
                                            if (!isset($colors_by_type[$t]))
                                                $colors_by_type[$t] = [];
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
                                                            <div class="color-swatch"
                                                                title="<?= htmlspecialchars($color['color_name']) ?>"
                                                                style="background-color: <?= htmlspecialchars($color['color_code'] ?? '#ffffff') ?>;">
                                                                <span
                                                                    class="color-name"><?= htmlspecialchars($color['color_name']) ?></span>
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
                                                            <div class="color-swatch"
                                                                title="<?= htmlspecialchars($color['color_name']) ?>"
                                                                style="background-color: <?= htmlspecialchars($color['color_code'] ?? '#ffffff') ?>;">
                                                                <span
                                                                    class="color-name"><?= htmlspecialchars($color['color_name']) ?></span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($product['material_catalogs'])): ?>
                                        <div class="form-group">
                                            <label>Model</label>
                                            <div style="display:flex; gap:8px; align-items:center;">
                                                <?php foreach ($product['material_catalogs'] as $m): ?>
                                                    <img src="../<?= htmlspecialchars($m['image_path']) ?>"
                                                        style="width:40px;height:40px;object-fit:cover;border:1px solid #ddd;border-radius:4px;"
                                                        title="<?= htmlspecialchars($m['material_type']) ?>">
                                                <?php endforeach; ?>
                                            </div>
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
            <div id="categories" class="tab-content <?= $page === 'categories' ? 'active' : '' ?>">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Catégories de produits</h2>
                        <button class="btn"
                            onclick="document.getElementById('addProductCategoryModal').style.display='block'">Ajouter</button>

                    </div>
                    <div class="card-body">
                        <?php $prod_cats = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC); ?>
                        <?php if (empty($prod_cats)): ?>
                            <div class="empty-state">Aucune catégorie. <button class="btn btn-small"
                                    onclick="document.getElementById('addProductCategoryModal').style.display='block'">Ajouter</button>
                            </div>
                        <?php else: ?>
                            <div style="overflow-x:auto;">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Nom</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($prod_cats as $c): ?>
                                            <tr data-cat-id="<?= $c['id'] ?>">
                                                <td style="width:90px;"><?php if (!empty($c['image_path'])): ?><img
                                                            src="../<?= htmlspecialchars($c['image_path']) ?>"
                                                            style="height:60px;object-fit:cover;border-radius:6px;"><?php else: ?>
                                                        <div
                                                            style="height:60px;width:90px;background:#f1f1f1;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#888">
                                                            No</div><?php endif; ?>
                                                </td>
                                                <td class="cat-name"><?= htmlspecialchars($c['name']) ?></td>
                                                <td class="cat-desc" style="max-width:520px;">
                                                    <?= nl2br(htmlspecialchars(mb_substr($c['description'], 0, 350))) ?>
                                                    <?= mb_strlen($c['description']) > 350 ? '...' : '' ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-small"
                                                        onclick='openEditCategory(<?= $c['id'] ?>, <?= json_encode($c['name']) ?>, <?= json_encode($c['description']) ?>, <?= json_encode($c['image_path'] ?? '') ?>)'><i
                                                            class="fas fa-edit"></i> Modifier</button>
                                                    <form method="post" style="display:inline;"
                                                        onsubmit="return deleteCategoryForm(this)">
                                                        <input type="hidden" name="cat_id" value="<?= $c['id'] ?>">
                                                        <button class="btn btn-small btn-danger"
                                                            name="delete_product_category">Supprimer</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Add Product Category Modal -->
                <div id="addProductCategoryModal" class="modal"
                    style="display:none; position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);">
                    <div style="background:white;padding:20px;margin:50px auto;max-width:600px;">
                        <h3>Ajouter une catégorie</h3>
                        <form id="addProductCategoryForm" method="post" enctype="multipart/form-data">
                            <label>Nom</label><br>
                            <input type="text" name="cat_name" required><br>
                            <label>Description (optionnel)</label><br>
                            <textarea name="cat_description" style="width:100%;height:100px;margin-top:6px"></textarea>
                            <label style="margin-top:8px">Image (optionnel)</label><br>
                            <input id="add_cat_image" type="file" name="cat_image" accept="image/*"><br>
                            <img id="add_cat_preview" src="" class="img-preview" style="display:none;">
                            <div style="margin-top:12px;text-align:right">
                                <button class="btn" type="submit" name="add_product_category">Ajouter</button>
                                <button type="button" class="btn btn-secondary"
                                    onclick="document.getElementById('addProductCategoryModal').style.display='none'">Fermer</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Edit Product Category Modal -->
                <div id="editProductCategoryModal" class="modal"
                    style="display:none; position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);">
                    <div style="background:white;padding:20px;margin:50px auto;max-width:600px;">
                        <h3>Modifier la catégorie</h3>
                        <form id="editProductCategoryForm" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="cat_id" id="edit_cat_id">
                            <input type="hidden" name="cat_remove_image" id="edit_cat_remove_image" value="0">
                            <label>Nom</label><br>
                            <input type="text" name="cat_name" id="edit_cat_name" required><br>
                            <label>Description (optionnel)</label><br>
                            <textarea name="cat_description" id="edit_cat_description"
                                style="width:100%;height:100px;margin-top:6px"></textarea>
                            <label style="margin-top:8px">Image (optionnel — remplace l'existante)</label><br>
                            <input id="edit_cat_image" type="file" name="cat_image" accept="image/*"><br>
                            <img id="edit_cat_preview" src="" class="img-preview" style="display:none;">
                            <div style="margin-top:8px;margin-bottom:8px;">
                                <button type="button" id="edit_remove_image_btn" class="btn btn-small btn-danger"
                                    style="display:none;">Supprimer l'image</button>
                            </div>
                            <div style="margin-top:12px;text-align:right">
                                <button class="btn" type="submit" name="update_product_category">Enregistrer</button>
                                <button type="button" class="btn btn-secondary"
                                    onclick="document.getElementById('editProductCategoryModal').style.display='none'">Fermer</button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                    function previewFile(file, previewId) {
                        const img = document.getElementById(previewId);
                        if (!img) return;
                        if (!file) { img.style.display = 'none'; img.src = ''; return; }
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            img.src = e.target.result;
                            img.style.display = 'block';
                        }
                        reader.readAsDataURL(file);
                    }

                    document.getElementById('edit_cat_image').addEventListener('change', function (e) {
                        previewFile(this.files[0], 'edit_cat_preview');
                    });
                    document.getElementById('add_cat_image').addEventListener('change', function (e) {
                        previewFile(this.files[0], 'add_cat_preview');
                    });

                    function openEditCategory(id, name, desc, imagePath) {
                        document.getElementById('edit_cat_id').value = id;
                        document.getElementById('edit_cat_name').value = name;
                        document.getElementById('edit_cat_description').value = desc;
                        // if image exists, show preview
                        const preview = document.getElementById('edit_cat_preview');
                        if (preview) {
                            if (imagePath && imagePath.length) { preview.src = '../' + imagePath; preview.style.display = 'block'; } else { preview.style.display = 'none'; preview.src = ''; }
                        }
                        document.getElementById('editProductCategoryModal').style.display = 'block';
                    }

                    function deleteCategoryForm(form) {
                        const id = form.querySelector('input[name="cat_id"]').value;
                        if (!confirm('Supprimer cette catégorie ? Les produits resteront mais perdront la catégorie.')) return false;
                        deleteCategoryAjax(id, form);
                        return false; // prevent actual form submit; AJAX will handle
                    }

                    function deleteCategoryAjax(id, formOrBtn) {
                        const btn = formOrBtn.querySelector('button') || formOrBtn;
                        // show spinner
                        const spinner = document.createElement('span'); spinner.className = 'spinner';
                        btn.disabled = true; btn.appendChild(spinner);

                        fetch('backendadmin.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'delete_product_category=1&cat_id=' + encodeURIComponent(id) + '&ajax=1'
                        }).then(res => res.json()).then(data => {
                            btn.disabled = false; spinner.remove();
                            if (data.ok) {
                                // remove table row
                                const tr = formOrBtn.closest('tr'); if (tr) tr.remove();
                                // remove category options from selects
                                document.querySelectorAll('select[name="category_id"]').forEach(sel => { const opt = sel.querySelector('option[value="' + id + '"]'); if (opt) opt.remove(); });
                                showToast('Catégorie supprimée', 'success');
                            } else {
                                showToast('Erreur: ' + (data.error || 'Impossible de supprimer'), 'error');
                            }
                        }).catch(err => {
                            btn.disabled = false; spinner.remove();
                            showToast('Erreur réseau', 'error');
                        });
                    }

                    // Toast helper
                    function showToast(message, type = 'info') {
                        let container = document.getElementById('toast_container');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'toast_container';
                            container.style.position = 'fixed';
                            container.style.top = '20px';
                            container.style.right = '20px';
                            container.style.zIndex = 2000;
                            document.body.appendChild(container);
                        }
                        const t = document.createElement('div');
                        t.className = 'toast ' + (type === 'error' ? 'toast-error' : (type === 'success' ? 'toast-success' : 'toast-info'));
                        t.textContent = message;
                        t.style.marginTop = '8px';
                        t.style.padding = '10px 14px';
                        t.style.borderRadius = '6px';
                        t.style.boxShadow = '0 4px 10px rgba(0,0,0,0.08)';
                        t.style.color = '#fff';
                        t.style.background = (type === 'success' ? '#28a745' : (type === 'error' ? '#dc3545' : '#333'));
                        container.appendChild(t);
                        setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 400); }, 4000);
                    }

                    // Add category via AJAX
                    document.getElementById('addProductCategoryForm').addEventListener('submit', function (e) {
                        e.preventDefault();
                        const form = this;
                        const btn = form.querySelector('button[type="submit"]');
                        const spinner = document.createElement('span'); spinner.className = 'spinner';
                        btn.disabled = true; btn.appendChild(spinner);
                        const fd = new FormData(form);
                        fd.append('ajax', '1');
                        fd.append('add_product_category', '1');
                        fetch('backendadmin.php', { method: 'POST', body: fd }).then(r => r.json()).then(data => {
                            btn.disabled = false; spinner.remove();
                            if (data.ok) {
                                const c = data.category;
                                // create table row
                                const tbody = document.querySelector('#categories table tbody');
                                if (tbody) {
                                    const tr = document.createElement('tr'); tr.setAttribute('data-cat-id', c.id);
                                    let imgCell = '<td style="width:90px;">';
                                    if (c.image_path) imgCell += '<img src="../' + c.image_path + '" style="height:60px;object-fit:cover;border-radius:6px;">'; else imgCell += '<div style="height:60px;width:90px;background:#f1f1f1;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#888">No</div>';
                                    imgCell += '</td>';
                                    tr.innerHTML = imgCell + '<td class="cat-name">' + escapeHtml(c.name) + '</td><td class="cat-desc">' + escapeHtml(c.description).substring(0, 350) + (c.description && c.description.length > 350 ? '...' : '') + '</td><td><button class="btn btn-small" onclick="openEditCategory(' + c.id + ', ' + JSON.stringify(c.name) + ', ' + JSON.stringify(c.description) + ', ' + JSON.stringify(c.image_path || '') + ')"><i class="fas fa-edit"></i> Modifier</button> <form method="post" style="display:inline;" onsubmit="return deleteCategoryForm(this)"><input type="hidden" name="cat_id" value="' + c.id + '"><button class="btn btn-small btn-danger" name="delete_product_category">Supprimer</button></form></td>';
                                    tbody.insertBefore(tr, tbody.firstChild);
                                }
                                // add option to selects
                                document.querySelectorAll('select[name="category_id"]').forEach(sel => {
                                    const opt = document.createElement('option'); opt.value = c.id; opt.textContent = c.name; sel.appendChild(opt);
                                });
                                document.getElementById('addProductCategoryModal').style.display = 'none';
                                form.reset(); document.getElementById('add_cat_preview').style.display = 'none';
                                showToast('Catégorie ajoutée', 'success');
                            } else {
                                showToast('Erreur: ' + (data.error || 'Impossible d\'ajouter'), 'error');
                            }
                        }).catch(err => { btn.disabled = false; spinner.remove(); showToast('Erreur réseau', 'error'); });
                    });

                    // Edit category via AJAX
                    document.getElementById('editProductCategoryForm').addEventListener('submit', function (e) {
                        e.preventDefault();
                        const form = this;
                        const btn = form.querySelector('button[type="submit"]');
                        const spinner = document.createElement('span'); spinner.className = 'spinner';
                        btn.disabled = true; btn.appendChild(spinner);
                        const fd = new FormData(form);
                        fd.append('ajax', '1');
                        fd.append('update_product_category', '1');
                        fetch('backendadmin.php', { method: 'POST', body: fd }).then(r => r.json()).then(data => {
                            btn.disabled = false; spinner.remove();
                            if (data.ok) {
                                const c = data.category;
                                // update table row
                                const tr = document.querySelector('tr[data-cat-id="' + c.id + '"]');
                                if (tr) {
                                    const imgCell = tr.querySelector('td:first-child');
                                    if (imgCell) {
                                        if (c.image_path) imgCell.innerHTML = '<img src="../' + c.image_path + '" style="height:60px;object-fit:cover;border-radius:6px;">'; else imgCell.innerHTML = '<div style="height:60px;width:90px;background:#f1f1f1;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#888">No</div>';
                                    }
                                    const nameEl = tr.querySelector('.cat-name'); if (nameEl) nameEl.textContent = c.name;
                                    const descEl = tr.querySelector('.cat-desc'); if (descEl) descEl.textContent = c.description.length > 350 ? c.description.substring(0, 350) + '...' : c.description;
                                }
                                // update selects labels
                                document.querySelectorAll('select[name="category_id"] option').forEach(opt => { if (opt.value == c.id) opt.textContent = c.name; });
                                document.getElementById('editProductCategoryModal').style.display = 'none';
                                showToast('Catégorie mise à jour', 'success');
                            } else {
                                showToast('Erreur: ' + (data.error || 'Impossible de modifier'), 'error');
                            }
                        }).catch(err => { btn.disabled = false; spinner.remove(); showToast('Erreur réseau', 'error'); });
                    });

                    // remove image handler in edit modal (client-side + set flag)
                    document.getElementById('edit_remove_image_btn').addEventListener('click', function () {
                        document.getElementById('edit_cat_preview').style.display = 'none';
                        document.getElementById('edit_cat_preview').src = '';
                        document.getElementById('edit_cat_remove_image').value = '1';
                        this.style.display = 'none';
                        showToast('Image supprimée (enregistrée après sauvegarde)', 'info');
                    });

                    // when an image file is chosen in edit modal, reset remove flag and show preview and show remove button
                    document.getElementById('edit_cat_image').addEventListener('change', function () {
                        const f = this.files && this.files[0];
                        if (f) { document.getElementById('edit_cat_remove_image').value = '0'; document.getElementById('edit_remove_image_btn').style.display = 'inline-block'; }
                    });

                    // helper to escape text for insertion into HTML (basic)
                    function escapeHtml(text) { if (!text) return ''; return text.replace(/[&<>"']/g, function (m) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": "&#39;" }[m]; }); }

                </script>
            </div>

            <!-- Customer messages page -->
            <div id="messages" class="tab-content <?= $page === 'messages' ? 'active' : '' ?>">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Messages clients</h2>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $messages = $db->query("SELECT * FROM customer_messages ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
                        } catch (Exception $e) {
                            $messages = [];
                            echo '<div class="alert alert-error">Erreur: ' . htmlspecialchars($e->getMessage()) . '</div>';
                        }
                        ?>
                        <div style="margin-bottom:10px;color:#666;">Messages:
                            <?= (int) count($messages) ?>
                            <?php if (!empty($unread_messages)): ?>(<strong>
                                    <?= (int) $unread_messages ?>
                                </strong> non lus)
                            <?php endif; ?>
                        </div>
                        <?php if (empty($messages)): ?>
                            <div class="empty-state">Aucun message pour le moment.</div>
                        <?php else: ?>
                            <div class="message-list">
                                <?php foreach ($messages as $m): ?>
                                    <?php $initials = strtoupper(substr(trim($m['client_name']), 0, 1) ?: 'U'); ?>
                                    <div class="message-card" data-id="<?= $m['id'] ?>"
                                        data-email="<?= htmlspecialchars($m['client_email'], ENT_QUOTES) ?>"
                                        data-phone="<?= htmlspecialchars($m['client_phone'], ENT_QUOTES) ?>"
                                        data-name="<?= htmlspecialchars($m['client_name'], ENT_QUOTES) ?>"
                                        data-comment="<?= htmlspecialchars($m['comment'], ENT_QUOTES) ?>"
                                        data-date="<?= $m['created_at'] ?>" data-is-read="<?= (int) ($m['is_read'] ?? 0) ?>">
                                        <div class="avatar">
                                            <?= $initials ?>
                                        </div>
                                        <div class="message-main">
                                            <div class="message-meta">
                                                <strong>
                                                    <?= htmlspecialchars($m['client_name']) ?>
                                                </strong>
                                                <?php if (empty($m['is_read'])): ?> <span
                                                        class="badge badge-unread">Nouveau</span>
                                                <?php endif; ?>
                                                <span class="message-date">
                                                    <?= date('d/m/Y H:i', strtotime($m['created_at'])) ?>
                                                </span>
                                            </div>
                                            <div class="message-excerpt">
                                                <?= nl2br(htmlspecialchars(mb_substr($m['comment'], 0, 300))) ?>
                                                <?= mb_strlen($m['comment']) > 300 ? '...' : '' ?>
                                            </div>
                                        </div>
                                        <div class="message-actions">
                                            <button class="btn btn-small btn-info" onclick="viewMessageCard(this)"> <i
                                                    class="fas fa-eye"></i></button>
                                            <?php if (empty($m['is_read'])): ?>
                                                <button class="btn btn-small btn-success"
                                                    onclick="markMessageRead(this, <?= $m['id'] ?>)"><i
                                                        class="fas fa-check"></i></button>
                                            <?php endif; ?>
                                            <button class="btn btn-small btn-danger"
                                                onclick="deleteMessageCard(this, <?= $m['id'] ?>)"><i
                                                    class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Message View Modal -->
                <div class="modal" id="messageModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title">Détails du message</h3>
                            <button class="modal-close" onclick="hideMessageModal()">&times;</button>
                        </div>
                        <div style="padding:10px;">
                            <p><strong>Nom:</strong> <span id="msg_name"></span></p>
                            <p><strong>Email:</strong> <span id="msg_email"></span></p>
                            <p><strong>Téléphone:</strong> <span id="msg_phone"></span></p>
                            <p><strong>Date:</strong> <span id="msg_date"></span></p>
                            <div style="margin-top:12px;"><strong>Message:</strong>
                                <div id="msg_comment"
                                    style="white-space:pre-wrap;margin-top:6px;padding:10px;background:#f8f8f8;border-radius:6px;border:1px solid #eee;">
                                </div>
                            </div>
                            <div style="margin-top:12px;text-align:right;"><button class="btn btn-secondary"
                                    onclick="hideMessageModal()">Fermer</button></div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="wilayas" class="tab-content <?= $page === 'wilayas' ? 'active' : '' ?>">





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
            <form id="add_product_form" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_product_v2" value="1">

                <div style="margin-bottom:10px; font-size:13px; color:#555;">Champs obligatoires marqués par
                    <strong>*</strong>. Utilisez les boutons <em>Filtres</em> sur la page Produits pour afficher
                    uniquement "Sur Commande" ou "Disponible".
                </div>

                <div class="modal-grid">
                    <div class="modal-col">
                        <div class="form-group">
                            <label for="product_images">Images du produit <strong>*</strong></label>
                            <input type="file" id="product_images" name="product_images[]" accept="image/*"
                                style="display:none;">
                            <div id="add-images-grid" class="image-grid" data-target-input="product_images"></div>
                            <div style="font-size:12px; color:#666; margin-top:6px;">Cliquez sur le + pour ajouter des
                                images. Au moins une image requise.</div>
                        </div>

                        <div class="form-group">
                            <label for="product_type">Type de produit <strong>*</strong></label>
                            <select id="product_type" name="product_type" required>
                                <option value="made_to_order">Sur Commande</option>
                                <option value="available">Disponible</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="category_id">Catégorie</label>
                            <?php $cats = $db->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC); ?>
                            <select name="category_id" id="category_id">
                                <option value="">Aucune</option>
                                <?php foreach ($cats as $ct): ?>
                                    <option value="<?= $ct['id'] ?>"><?= htmlspecialchars($ct['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>

                    <div class="modal-col">
                        <div class="form-group">
                            <label for="name">Nom du produit <strong>*</strong></label>
                            <input type="text" id="name" name="name" placeholder="Ex: Table basse 120x70" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description <strong>*</strong></label>
                            <textarea id="description" name="description" placeholder="Brève description du produit"
                                required></textarea>
                        </div>

                        <div class="form-group">
                            <label>Dimensions (variantes) <strong>*</strong></label>
                            <div id="dimensions-container">
                                <!-- dimension rows will be added dynamically -->
                            </div>
                            <button type="button" class="btn btn-small btn-secondary" onclick="addDimensionRow()">
                                <i class="fas fa-plus"></i> Ajouter une dimension
                            </button>
                            <div style="font-size:12px; color:#666; margin-top:6px;">Ajoutez au moins une dimension et
                                renseignez le label.</div>
                        </div>

                        <div class="form-group">
                            <label>Couleurs du produit (optionnel)</label>
                            <div id="colors-container"></div>
                            <button type="button" class="btn btn-small btn-secondary" onclick="addColorRow()">
                                <i class="fas fa-plus"></i> Ajouter une couleur
                            </button>
                        </div>

                        <div class="form-group">
                            <label>Matériaux (nuanciers)</label>
                            <div style="display:flex; gap:12px; align-items:flex-start; flex-wrap:wrap;"
                                id="add-material-inputs">
                                <div style="flex:1; min-width:200px;">
                                    <label>Tissu (nuancier)</label>
                                    <input type="file" name="material_tissu" accept="image/*">
                                    <textarea name="material_tissu_description" placeholder="Description (optionnel)"
                                        style="width:100%; margin-top:6px; height:60px;"></textarea>
                                </div>
                                <div style="flex:1; min-width:200px;">
                                    <label>Bois (nuancier)</label>
                                    <input type="file" name="material_bois" accept="image/*">
                                    <textarea name="material_bois_description" placeholder="Description (optionnel)"
                                        style="width:100%; margin-top:6px; height:60px;"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn"><i class="fas fa-save"></i> Ajouter</button>
                    <button type="button" class="btn btn-secondary" onclick="hideAddProductModal()">Annuler</button>
                </div>
            </form>

            <script>
                // Client-side validation for add product (updated to match new layout)
                document.getElementById('add_product_form').addEventListener('submit', function (e) {
                    const dims = Array.from(document.querySelectorAll('#dimensions-container .dimension-row'));
                    if (dims.length === 0) {
                        e.preventDefault();
                        alert('Au moins une dimension est requise.');
                        return false;
                    }

                    for (let i = 0; i < dims.length; i++) {
                        const row = dims[i];
                        const label = row.querySelector('input[name="dim_label[]"]');
                        const price = row.querySelector('input[name="dim_price[]"]');
                        const stock = row.querySelector('input[name="dim_stock[]"]');
                        const unlimited = row.querySelector('input[name="dim_unlimited[]"]');

                        if (!label || !label.value.trim()) {
                            e.preventDefault();
                            alert('La dimension #' + (i + 1) + ' nécessite un label.');
                            return false;
                        }
                        if (!price || price.value === '' || isNaN(price.value) || Number(price.value) < 0) {
                            e.preventDefault();
                            alert('La dimension #' + (i + 1) + ' nécessite un prix valide.');
                            return false;
                        }
                        if (unlimited && unlimited.checked) {
                            // stock may be ignored
                        } else {
                            if (!stock || stock.value === '' || isNaN(stock.value) || Number(stock.value) < 0) {
                                e.preventDefault();
                                alert('La dimension #' + (i + 1) + ' nécessite un stock valide (ou cochez Illimité).');
                                return false;
                            }
                        }
                    }

                    // ensure at least one image selected (supports image grid hidden inputs)
                    const imgInputs = Array.from(document.querySelectorAll('input[name="product_images[]"]'));
                    let hasImage = false;
                    imgInputs.forEach(i => { if (i.files && i.files.length) hasImage = true; });
                    if (!hasImage) {
                        e.preventDefault();
                        alert('Au moins une image de produit est requise.');
                        return false;
                    }
                });
            </script>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal" id="editProductModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Modifier le produit</h3>
                <button class="modal-close" onclick="hideEditProductModal()">&times;</button>
            </div>
            <form id="edit_product_form" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_product_v2" value="1">
                <input type="hidden" name="product_id" id="edit_product_id">
                <input type="hidden" name="edit_current_image_path" id="edit_current_image_path" value="">

                <div class="form-group">
                    <label for="edit_product_type">Type de produit</label>
                    <select id="edit_product_type" name="product_type">
                        <option value="made_to_order">Sur Commande</option>
                        <option value="available">Disponible</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Images actuelles</label>
                    <div id="edit-product-images" style="display:flex; gap:8px; align-items:center;"></div>
                </div>
                <!-- Removed separate material-catalogs display and duplicate add-images block - images are managed in "Images actuelles" grid -->
                <div class="form-group">
                    <label>Mettre à jour les Model</label>
                    <div style="display:flex; gap:12px; align-items:flex-start; margin-top:6px;">
                        <div style="flex:1">
                            <label>Tissu (nuancier)</label><br>
                            <input type="file" name="material_tissu" accept="image/*"><br>
                            <textarea name="material_tissu_description" placeholder="Description (optionnel)"
                                style="width:100%; margin-top:6px; height:60px;"></textarea>
                        </div>
                        <div style="flex:1">
                            <label>Bois (nuancier)</label><br>
                            <input type="file" name="material_bois" accept="image/*"><br>
                            <textarea name="material_bois_description" placeholder="Description (optionnel)"
                                style="width:100%; margin-top:6px; height:60px;"></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_name">Nom du produit</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>

                <div class="form-group">
                    <label>Catégorie (optionnel)</label>
                    <?php $cats_for_edit = $db->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC); ?>
                    <select name="category_id" id="edit_category_id">
                        <option value="">Aucune</option>
                        <?php foreach ($cats_for_edit as $ct): ?>
                            <option value="<?= $ct['id'] ?>"><?= htmlspecialchars($ct['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>div>

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
                const el = document.querySelector('.sidebar-link[href="dashboard.php"]'); if (el) el.classList.add('active');
            } else if (tabId === 'products') {
                // ensure the products sidebar link is marked active (link may contain query param)
                const el = document.querySelector('.sidebar-link[href*="page=products"]'); if (el) el.classList.add('active');
            } else {
                const el = document.querySelector(`.sidebar-link[href="#${tabId}"]`); if (el) el.classList.add('active');
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
            // Reset product type and image inputs
            const pt = document.getElementById('product_type'); if (pt) pt.value = 'made_to_order';
            const pimg = document.getElementById('product_images'); if (pimg) {
                pimg.value = '';
                // ensure grid is initialized with the add tile
                const grid = document.getElementById('add-images-grid'); if (grid) {
                    grid.innerHTML = '';
                    initImageGrid('product_images', 'add-images-grid');
                }
            }
        }

        function hideAddProductModal() {
            document.getElementById('addProductModal').style.display = 'none';
            const inp = document.getElementById('product_images'); if (inp) { inp.value = ''; }
            const grid = document.getElementById('add-images-grid'); if (grid) grid.innerHTML = '';
            // remove any dynamically created hidden inputs for images
            const addForm = document.getElementById('add_product_form');
            if (addForm) {
                Array.from(addForm.querySelectorAll('input[name="product_images[]"]')).forEach(i => i.remove());
            }
        }

        // Edit product modal functions
        function showEditProductModal(product, dimensions, colors) {
            // Populate form fields with product data (defensive: only set if element exists)
            const pidEl = document.getElementById('edit_product_id'); if (pidEl && typeof product.id !== 'undefined') pidEl.value = product.id;
            const nameEl = document.getElementById('edit_name'); if (nameEl && typeof product.name !== 'undefined') nameEl.value = product.name;
            const descEl = document.getElementById('edit_description'); if (descEl && typeof product.description !== 'undefined') descEl.value = product.description;
            // set product type
            if (product.product_type) {
                const et = document.getElementById('edit_product_type');
                if (et) et.value = product.product_type;
            }
            const curImgEl = document.getElementById('edit_current_image_path'); if (curImgEl) curImgEl.value = '';

            // set category if present
            const editCatEl = document.getElementById('edit_category_id'); if (editCatEl && typeof product.category_id !== 'undefined') editCatEl.value = product.category_id || '';

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

            // Populate existing product images into the edit image grid
            const imgContainer = document.getElementById('edit-product-images');
            if (imgContainer) {
                imgContainer.innerHTML = '';
                if (product.images && product.images.length) {
                    product.images.forEach(img => {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'image-tile existing-image';
                        wrapper.style.position = 'relative';

                        const primaryBadge = '';

                        wrapper.innerHTML = `
                            <img src="../${img.image_path}" style="width:100%;height:100%;object-fit:cover;border:1px solid #ddd;border-radius:6px;">
                            <input type="hidden" name="existing_product_images[]" value="${img.id}">
                            <button type="button" class="btn btn-small btn-danger image-remove" style="position:absolute;right:6px;top:6px;" onclick="deleteProductImage(this, ${img.id})">&times;</button>
                        `;
                        imgContainer.appendChild(wrapper);
                    });
                }

                // add the '+' tile so admin can add new images
                const addTile = document.createElement('div');
                addTile.className = 'image-tile image-add-tile';
                addTile.innerHTML = '<div class="plus">+</div>';
                addTile.addEventListener('click', () => document.getElementById('edit_product_images').click());
                imgContainer.appendChild(addTile);

                // wire up change handler for edit file input so selected new files show as previews
                initImageGrid('edit_product_images', 'edit-product-images');

                // populate material catalogs (unchanged)
                const matContainer = document.getElementById('edit-material-catalogs');
                if (matContainer) {
                    matContainer.innerHTML = '';
                    if (product.material_catalogs && product.material_catalogs.length) {
                        product.material_catalogs.forEach(m => {
                            const w = document.createElement('div');
                            w.style.position = 'relative';
                            w.style.display = 'inline-block';
                            w.style.marginRight = '8px';
                            w.innerHTML = `
                                <img src="../${m.image_path}" style="width:80px;height:80px;object-fit:cover;border:1px solid #ddd;">
                                <button type="button" class="btn btn-small btn-danger" style="position:absolute;right:0;top:0;" onclick="deleteMaterialCatalog(${m.id}, this)">&times;</button>
                                <div style="position:absolute;left:0;bottom:0;background:rgba(0,0,0,0.6);color:#fff;padding:2px 6px;border-radius:4px;font-size:12px;">${m.material_type}</div>
                            `;
                            matContainer.appendChild(w);
                        });
                    }
                }
            }

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

            const editInput = document.getElementById('edit_product_images'); if (editInput) editInput.value = '';
            const editGrid = document.getElementById('edit-product-images'); if (editGrid) editGrid.innerHTML = '';
            // remove any dynamically created hidden inputs for edit images
            const editForm = document.getElementById('edit_product_form');
            if (editForm) {
                Array.from(editForm.querySelectorAll('input[name="edit_product_images[]"], input[name="product_images[]"]')).forEach(i => i.remove());
            }
        }

        function deleteProductImage(btn, imageId) {
            if (!confirm('Supprimer cette image ?')) return;
            fetch('backendadmin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'delete_product_image=1&image_id=' + encodeURIComponent(imageId)
            }).then(res => res.json()).then(data => {
                if (data && data.ok) {
                    const wrapper = btn.closest('div');
                    if (wrapper) wrapper.remove();
                } else {
                    alert('Erreur lors de la suppression de l\'image');
                }
            }).catch(err => {
                console.error(err);
                alert('Erreur réseau');
            });
        }

        function deleteMaterialCatalog(id, btn) {
            if (!confirm('Supprimer ce nuancier ?')) return;
            fetch('backendadmin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'delete_material_catalog=1&id=' + encodeURIComponent(id)
            }).then(res => res.json()).then(data => {
                if (data && data.ok) {
                    const wrapper = btn.closest('div');
                    if (wrapper) wrapper.remove();
                } else {
                    alert('Erreur lors de la suppression du nuancier');
                }
            }).catch(err => {
                console.error(err);
                alert('Erreur réseau');
            });
        }

        // primary image selection removed: no persistent `is_primary` column in the schema. If you want to reintroduce this feature, add an `is_primary` column via a migration and restore the handler.

        // Image grid helpers (add / edit) + dimension unlimited handling
        // New behavior: each click on the + tile creates a new hidden file input so
        // users can pick one image at a time. Each selected image keeps its own
        // input (name ending with []) so it will be submitted with the form. The
        // + tile remains available for further selections.
        function initImageGrid(inputId, gridId) {
            const original = document.getElementById(inputId); // may exist in HTML
            const grid = document.getElementById(gridId);
            if (!grid) return;
            // clear grid and recreate add tile
            grid.innerHTML = '';
            const addTile = document.createElement('div');
            addTile.className = 'image-tile image-add-tile';
            addTile.innerHTML = '<div class="plus">+</div>';
            grid.appendChild(addTile);

            addTile.addEventListener('click', () => {
                // create a new hidden file input for a single image
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.accept = 'image/*';
                // use the original input's name if available, otherwise fallback
                const baseName = (original && original.name) ? original.name : (inputId + '[]');
                // ensure name ends with [] so multiple inputs are submitted as an array
                fileInput.name = baseName.endsWith('[]') ? baseName : baseName + '[]';
                fileInput.style.display = 'none';

                // when a file is selected, create a preview tile and keep the input in DOM
                fileInput.addEventListener('change', () => {
                    if (!fileInput.files || fileInput.files.length === 0) {
                        fileInput.remove();
                        return;
                    }
                    const file = fileInput.files[0];
                    const url = URL.createObjectURL(file);
                    const tile = document.createElement('div');
                    tile.className = 'image-tile new-file';
                    tile.innerHTML = `<img src="${url}" style="width:100%;height:100%;object-fit:cover;border-radius:6px;"> <button type="button" class="btn btn-small btn-danger image-remove" title="Retirer">×</button>`;
                    const removeBtn = tile.querySelector('.image-remove');
                    removeBtn.addEventListener('click', () => {
                        // remove the associated input so it won't be submitted
                        fileInput.remove();
                        tile.remove();
                    });
                    grid.insertBefore(tile, addTile);
                });

                // append input to the closest form so it's submitted, otherwise to grid
                const form = original ? original.closest('form') : grid.closest('form');
                if (form) form.appendChild(fileInput); else grid.appendChild(fileInput);

                // open file dialog
                fileInput.click();
            });

            // keep original input hidden and clear its value to avoid duplicates
            if (original) {
                try { original.removeAttribute('multiple'); } catch (e) { }
                original.value = '';
                original.style.display = 'none';
            }
        }

        function renderFilePreviews(input, grid) {
            // remove previous new-file previews
            Array.from(grid.querySelectorAll('.image-tile.new-file')).forEach(n => n.remove());
            const files = input.files;
            if (!files || !files.length) return;
            const addTile = grid.querySelector('.image-add-tile');
            Array.from(files).forEach((file, idx) => {
                const url = URL.createObjectURL(file);
                const tile = document.createElement('div');
                tile.className = 'image-tile new-file';
                tile.innerHTML = `<img src="${url}" style="width:100%;height:100%;object-fit:cover;border-radius:6px;"> <button type="button" class="btn btn-small btn-danger image-remove" title="Retirer">×</button>`;
                const removeBtn = tile.querySelector('.image-remove');
                removeBtn.addEventListener('click', () => {
                    removeFileFromInput(input, idx);
                    tile.remove();
                });
                grid.insertBefore(tile, addTile);
            });
        }

        function removeFileFromInput(input, index) {
            const dt = new DataTransfer();
            Array.from(input.files).forEach((f, i) => { if (i !== index) dt.items.add(f); });
            input.files = dt.files;
            input.dispatchEvent(new Event('change'));
        }

        function onDimensionUnlimitedToggle(checkbox) {
            const row = checkbox.closest('.dimension-row');
            if (!row) return;
            const stockInput = row.querySelector('input[name="dim_stock[]"]');
            if (checkbox.checked) {
                if (stockInput) {
                    stockInput.value = 9999999;
                    stockInput.disabled = true;
                }
            } else {
                if (stockInput) {
                    stockInput.disabled = false;
                    if (String(stockInput.value) === '9999999') stockInput.value = '';
                }
            }
        }

        // Message view/delete helpers
        function viewMessageCard(btn) {
            const card = btn.closest('.message-card');
            if (!card) return;
            document.getElementById('msg_name').textContent = card.dataset.name || '';
            document.getElementById('msg_email').textContent = card.dataset.email || '';
            document.getElementById('msg_phone').textContent = card.dataset.phone || '';
            document.getElementById('msg_date').textContent = card.dataset.date || '';
            document.getElementById('msg_comment').textContent = card.dataset.comment || '';
            document.getElementById('messageModal').style.display = 'flex';

            // If message is unread, mark it as read immediately
            if (typeof card.dataset.isRead !== 'undefined' && card.dataset.isRead === '0') {
                fetch('backendadmin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'mark_message_read=1&message_id=' + encodeURIComponent(card.dataset.id) + '&ajax=1'
                }).then(res => res.json()).then(data => {
                    if (data && data.ok) {
                        const badge = card.querySelector('.badge-unread'); if (badge) badge.remove();
                        const sb = document.querySelector('.sidebar .badge');
                        if (sb) {
                            const n = parseInt(sb.textContent || '0', 10) - 1;
                            sb.textContent = n > 0 ? n : '';
                        }
                        card.dataset.isRead = '1';
                    }
                }).catch(err => {
                    console.error('Failed to mark message read', err);
                });
            }
        }

        function hideMessageModal() {
            const m = document.getElementById('messageModal');
            if (m) m.style.display = 'none';
        }

        function deleteMessageCard(btn, id) {
            if (!confirm('Supprimer ce message ?')) return;
            fetch('backendadmin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'delete_message=1&message_id=' + encodeURIComponent(id) + '&ajax=1'
            }).then(res => res.json()).then(data => {
                if (data && data.ok) {
                    const card = btn.closest('.message-card');
                    if (card) card.remove();
                } else {
                    alert('Erreur lors de la suppression');
                }
            }).catch(err => {
                console.error(err);
                alert('Erreur réseau');
            });
        }

        function markMessageRead(btn, id) {
            fetch('backendadmin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'mark_message_read=1&message_id=' + encodeURIComponent(id) + '&ajax=1'
            }).then(res => res.json()).then(data => {
                if (data && data.ok) {
                    const card = btn.closest('.message-card');
                    if (card) {
                        // remove unread badge
                        const badge = card.querySelector('.badge-unread'); if (badge) badge.remove();
                        // remove the mark button
                        btn.remove();
                        // update sidebar badge count
                        const sb = document.querySelector('.sidebar .badge');
                        if (sb) {
                            const n = parseInt(sb.textContent || '0', 10) - 1;
                            sb.textContent = n > 0 ? n : '';
                        }
                    }
                } else {
                    alert('Erreur lors de la modification');
                }
            }).catch(err => {
                console.error(err);
                alert('Erreur réseau');
            });
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

            // inner HTML: simple dimension row (no images per dimension in new schema)
            row.innerHTML = `
        <div class="dimension-row-inner">
            <input type="text" name="dim_label[]" placeholder="Label (ex: 120x70)" class="dim-field dim-label" required>
            <input type="number" name="width_cm[]" placeholder="L (cm)" class="dim-field" min="0">
            <input type="number" name="height_cm[]" placeholder="H (cm)" class="dim-field" min="0">
            <input type="number" name="depth_cm[]" placeholder="P (cm)" class="dim-field" min="0">
            <input type="number" name="dim_price[]" placeholder="Prix (DA)" class="dim-field dim-price" step="0.01" min="0" required>
            <input type="number" name="dim_stock[]" placeholder="Stock" class="dim-field dim-stock" min="0" required>
            <input type="number" name="dim_price_new[]" placeholder="Prix promo (DA)" class="dim-field dim-price-new" step="0.01" min="0">
            <input type="number" name="dim_promo_percent[]" placeholder="% Promo" class="dim-field dim-promo" min="0" max="100">
        </div>
        <div class="dimension-row-actions">
            <label class="dim-checkbox"><input type="checkbox" name="dim_unlimited[]" value="1" onchange="onDimensionUnlimitedToggle(this)"> Illimité</label>
            <label class="dim-checkbox">Par défaut <input type="checkbox" name="dim_is_default[]" value="1" onchange="ensureSingleDefault(this)"></label>
            <button type="button" class="btn btn-small btn-danger" onclick="removeDimensionRow(this)"><i class="fas fa-trash"></i></button>
        </div>
    `;
            container.appendChild(row);
        }


        function removeDimensionRow(btn) {
            const row = btn.closest('.dimension-row');
            if (row) row.remove();
        }

        function ensureSingleDefault(checkbox) {
            if (!checkbox || !checkbox.checked) return;
            // Uncheck all other default checkboxes in the same modal
            const container = checkbox.closest('.modal') || document;
            const others = container.querySelectorAll('input[name="dim_is_default[]"]');
            others.forEach(cb => { if (cb !== checkbox) cb.checked = false; });
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
        <div class="dimension-row-inner">
            <input type="text" name="dim_label[]" placeholder="Label (ex: 120x70)" class="dim-field dim-label" value="${label}" required>
            <input type="number" name="width_cm[]" placeholder="L (cm)" class="dim-field" min="0" value="${width}">
            <input type="number" name="height_cm[]" placeholder="H (cm)" class="dim-field" min="0" value="${height}">
            <input type="number" name="depth_cm[]" placeholder="P (cm)" class="dim-field" min="0" value="${dimData && dimData.depth_cm ? dimData.depth_cm : ''}">
            <input type="number" name="dim_price[]" placeholder="Prix (DA)" class="dim-field dim-price" step="0.01" min="0" value="${price}" required>
            <input type="number" name="dim_stock[]" placeholder="Stock" class="dim-field dim-stock" min="0" value="${stock}" required>
            <input type="number" name="dim_price_new[]" placeholder="Prix promo (DA)" class="dim-field dim-price-new" step="0.01" min="0" value="${dimData && dimData.price_new ? dimData.price_new : ''}">
            <input type="number" name="dim_promo_percent[]" placeholder="% Promo" class="dim-field dim-promo" min="0" max="100" value="${dimData && dimData.promo_percent ? dimData.promo_percent : ''}">
        </div>
        <div class="dimension-row-actions">
            <label class="dim-checkbox"><input type="checkbox" name="dim_unlimited[]" value="1" ${dimData && (dimData.stock == 9999999) ? 'checked' : ''} onchange="onDimensionUnlimitedToggle(this)"> Illimité</label>
            <label class="dim-checkbox">Par défaut <input type="checkbox" name="dim_is_default[]" value="1" ${dimData && dimData.is_default ? 'checked' : ''} onchange="ensureSingleDefault(this)"></label>
            <button type="button" class="btn btn-small btn-danger" onclick="removeDimensionRow(this)"><i class="fas fa-trash"></i></button>
        </div>
    `;
            container.appendChild(row);

            // wire up unlimited checkbox to disable stock input when applicable
            (function wireStockHandlers() {
                const chk = row.querySelector('input[name="dim_unlimited[]"]');
                const stockInput = row.querySelector('input[name="dim_stock[]"]');
                if (chk && stockInput) {
                    // initial state
                    if (chk.checked) {
                        stockInput.disabled = true;
                        stockInput.value = 9999999;
                    }
                }
            })();

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

            // AJAX upload helper: upload new images from edit modal without full form submit
            const editInput = document.getElementById('edit_product_images');
            if (editInput) {
                editInput.addEventListener('change', function () {
                    const files = editInput.files;
                    const pid = document.getElementById('edit_product_id').value;
                    if (!pid) return;
                    Array.from(files).forEach(file => {
                        if (!file.type.startsWith('image/')) return;
                        const fd = new FormData();
                        fd.append('upload_product_image', 1);
                        fd.append('product_id', pid);
                        fd.append('image', file);
                        fetch('backendadmin.php', { method: 'POST', body: fd }).then(res => res.json()).then(data => {
                            if (data && data.ok) {
                                const container = document.getElementById('edit-product-images');
                                if (!container) return;
                                const wrapper = document.createElement('div');
                                wrapper.style.position = 'relative';
                                wrapper.style.display = 'inline-block';
                                wrapper.style.marginRight = '8px';
                                wrapper.innerHTML = `
                                    <img src="../${data.path}" style="width:80px;height:80px;object-fit:cover;border:1px solid #ddd;">
                                    <input type="hidden" name="existing_product_images[]" value="${data.id}">
                                    <button type="button" class="btn btn-small btn-danger" style="position:absolute;right:0;top:0;" onclick="deleteProductImage(this, ${data.id})">&times;</button>
                                `;
                                container.appendChild(wrapper);
                            } else {
                                alert('Erreur lors du téléversement de l\'image');
                            }
                        }).catch(err => { console.error(err); alert('Erreur réseau'); });
                    });
                    editInput.value = '';
                });
            }

            // Submenu toggle behaviour (click and keyboard accessible)
            document.querySelectorAll('.sidebar .has-sub').forEach(el => {
                el.addEventListener('click', function (e) {
                    // ignore clicks on actual submenu links
                    if (e.target.closest('.sidebar-sublink')) return;
                    this.classList.toggle('open');
                    this.setAttribute('aria-expanded', this.classList.contains('open') ? 'true' : 'false');
                });
                el.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });

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
            const baseName = container ? (container.getAttribute('data-name') || 'product_images') : 'product_images';

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

        // Order status AJAX handlers (init at end)
        (function initOrderStatusAjax() {
            // lightweight notification helper if not present
            if (typeof window.showNotification === 'undefined') {
                window.showNotification = function (message, type = 'success') {
                    const div = document.createElement('div');
                    div.className = 'top-notif' + (type === 'error' ? ' error' : '');
                    div.textContent = message;
                    document.body.appendChild(div);
                    setTimeout(() => { try { div.remove(); } catch (e) { } }, 3000);
                };
            }

            document.querySelectorAll('.status-form').forEach(form => {
                form.addEventListener('submit', function (e) { e.preventDefault(); });
                const sel = form.querySelector('select[name="status"]');
                if (!sel) return;
                sel.addEventListener('change', function () {
                    const orderId = form.querySelector('input[name="order_id"]').value;
                    const status = sel.value;
                    sel.disabled = true;
                    fetch('backendadmin.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'update_order_status=1&order_id=' + encodeURIComponent(orderId) + '&status=' + encodeURIComponent(status) + '&ajax=1'
                    }).then(res => res.json()).then(data => {
                        sel.disabled = false;
                        if (data && data.ok) {
                            window.showNotification('Statut de commande mis à jour', 'success');
                        } else {
                            window.showNotification((data && data.error) ? data.error : 'Erreur lors de la mise à jour', 'error');
                        }
                    }).catch(err => {
                        sel.disabled = false;
                        console.error(err);
                        window.showNotification('Erreur réseau lors de la mise à jour', 'error');
                    });
                });
            });
        })();

    </script>
</body>

</html>