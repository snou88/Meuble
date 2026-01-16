<!DOCTYPE html>
<html lang="fr" lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ama Meuble - Le meuble qui donne une âme à votre maison</title>
    <meta name="description"
        content="Découvrez notre collection de meubles haut de gamme. Design moderne, bois de qualité, fabrication soignée.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/cart.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
</head>

<body>
    <?php
    if (session_status() === PHP_SESSION_NONE)
        session_start();
    $cartItems = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? array_values($_SESSION['cart']) : [];
    $cartCount = 0;
    foreach ($cartItems as $it)
        $cartCount += (int) $it['qty'];

    // DB connection for color codes lookup
    require_once __DIR__ . '/config/database.php';
    try {
        $db = getDBConnection();
        // Use two distinct placeholders to avoid driver issues with repeated named params
        $colorLookupStmt = $db->prepare("SELECT color_code FROM product_colors WHERE product_id = :pid AND (color_name = :name OR color_code = :code) LIMIT 1");
        // Load active wilayas for the checkout select
        $wilayas = [];
        try {
            $wstmt = $db->prepare('SELECT id, name, domicile_price FROM wilayas WHERE is_active = 1 ORDER BY name');
            $wstmt->execute();
            $wilayas = $wstmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // ignore - leave $wilayas empty
        }
    } catch (Exception $e) {
        $db = null;
        $colorLookupStmt = null;
    }
    ?>
    <!-- Header / Navbar -->
    <header class="header" id="header">
        <nav class="nav container">
            <div class="nav-logo">
                <a href="index.php"><img src="assets/images/LOGO-blanc.png" alt="Ama Meuble Logo" class="logo-img"></a>
            </div>

            <div class="nav-menu" id="navMenu">
                <ul class="nav-list">
                    <li class="nav-item"><a href="index.php" class="nav-link active">Accueil</a></li>
                    <li class="nav-item nav-dropdown"><a href="produits.php" class="nav-link">Produits</a>
                        <div class="nav-dropdown-content">
                            <a href="produits.php">Tous</a>
                            <a href="produits.php?type=made_to_order">Sur Commande</a>
                            <a href="produits.php?type=available">Disponible</a>
                        </div>
                    </li>
                    <li class="nav-item"><a href="index.php#collections" class="nav-link">Collections</a></li>
                    <li class="nav-item"><a href="index.php#apropos" class="nav-link">À propos</a></li>
                    <li class="nav-item"><a href="index.php#contact" class="nav-link">Contact</a></li>
                </ul>
                <a href="panier.php">
                    <div class="cart-icon">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M7 4h-2l-1 2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96a2 2 0 1 0 2-2h6a2 2 0 1 0 2 2c0-.35-.09-.68-.25-.96l-1.1-2.04h-6.45l.75-1.35h5.95c.75 0 1.41-.41 1.75-1.03l3.24-5.87h-14.3z" />
                        </svg>
                        <span class="cart-count"><?= (int) $cartCount ?></span>
                    </div>
                </a>
            </div>

            <div class="nav-toggle" id="navToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="accueil" style="height: 10vh;">
        <div class="hero-overlay"></div>
        <div class="hero-scroll">
        </div>
    </section>

    <div class="cart-page">

        <h1>Votre panier</h1>

        <div class="cart-container">

            <!-- Liste produits -->
            <div class="cart-items">
                <?php if (empty($cartItems)): ?>
                    <div class="empty-cart">
                        <p>Votre panier est vide.</p>
                        <a class="btn" href="produits.php">Voir les produits</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($cartItems as $item): ?>
                        <?php
                        // Resolve fabric/wood color codes if possible
                        $fabric_code = null;
                        $wood_code = null;
                        if ($colorLookupStmt && !empty($item['fabric'])) {
                            $colorLookupStmt->execute(['pid' => $item['product_id'], 'name' => $item['fabric'], 'code' => $item['fabric']]);
                            $row = $colorLookupStmt->fetch();
                            if ($row && !empty($row['color_code']))
                                $fabric_code = $row['color_code'];
                            else {
                                // maybe the stored value is already a code
                                if (preg_match('/^#?[0-9A-Fa-f]{3,6}$/', $item['fabric']))
                                    $fabric_code = $item['fabric'];
                            }
                            if ($fabric_code && $fabric_code[0] !== '#')
                                $fabric_code = '#' . $fabric_code;
                            // validate final format (3 or 6 hex digits)
                            if ($fabric_code && !preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $fabric_code))
                                $fabric_code = null;
                        }
                        if ($colorLookupStmt && !empty($item['wood'])) {
                            $colorLookupStmt->execute(['pid' => $item['product_id'], 'name' => $item['wood'], 'code' => $item['wood']]);
                            $row = $colorLookupStmt->fetch();
                            if ($row && !empty($row['color_code']))
                                $wood_code = $row['color_code'];
                            else {
                                if (preg_match('/^#?[0-9A-Fa-f]{3,6}$/', $item['wood']))
                                    $wood_code = $item['wood'];
                            }
                            if ($wood_code && $wood_code[0] !== '#')
                                $wood_code = '#' . $wood_code;
                            if ($wood_code && !preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $wood_code))
                                $wood_code = null;
                        }
                        ?>
                        <div class="cart-item" data-key="<?= htmlspecialchars($item['key']) ?>">
                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            <div class="item-info">
                                <h3><?= htmlspecialchars($item['name']) ?></h3>
                                <p><?= htmlspecialchars($item['dimension_label']) ?></p>
                                <div style="margin-top:6px; display:flex; gap:10px; align-items:center;">
                                    <div style="display:flex; gap:8px; align-items:center;">
                                        <small style="color:#666">Tissu</small>
                                        <?php if ($fabric_code): ?>
                                            <span class="color-swatch"
                                                style="background: <?= htmlspecialchars($fabric_code) ?>"></span>

                                        <?php else: ?>
                                        <?php endif; ?>
                                    </div>
                                    <div style="display:flex; gap:8px; align-items:center;">
                                        <small style="color:#666">Bois</small>
                                        <?php if ($wood_code): ?>
                                            <span class="color-swatch"
                                                style="background: <?= htmlspecialchars($wood_code) ?>"></span>
                                        <?php else: ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="price"
                                    style="display:block; margin-top:8px"><?= number_format($item['unit_price'], 0, ',', ' ') ?>
                                    DA</span>
                            </div>

                            <div class="quantity-selector">
                                <button class="qty-decrease">-</button>
                                <span class="qty"><?= (int) $item['qty'] ?></span>
                                <button class="qty-increase">+</button>
                            </div>

                            <button class="remove" data-key="<?= htmlspecialchars($item['key']) ?>" title="Supprimer"
                                aria-label="Supprimer cet article">✕</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Résumé -->
            <div class="cart-summary">
                <h2>Résumé</h2>
                <?php
                $subtotal = 0;
                foreach ($cartItems as $it)
                    $subtotal += (float) $it['total_price'];
                $shipping = $subtotal > 0 ? 0 : 0;
                $total = $subtotal + $shipping;
                ?>

                <div class="summary-line">
                    <span>Sous-total</span>
                    <span><?= number_format($subtotal, 0, ',', ' ') ?> DA</span>
                </div>

                <div class="summary-line">
                    <span>Livraison</span>
                    <span id="main_shipping"><?= number_format($shipping, 0, ',', ' ') ?> DA</span>
                </div>

                <div class="summary-line total">
                    <span>Total</span>
                    <span><?= number_format($total, 0, ',', ' ') ?> DA</span>
                </div>

                <button class="checkout fade-in <?= $subtotal <= 0 ? 'is-empty' : '' ?>" <?= $subtotal <= 0 ? 'aria-disabled="true" data-empty="1" title="Panier vide — voir les produits"' : '' ?>>Commander</button>
            </div>

        </div>

    </div>

    <script src="assets/js/cart.js"></script>

    <!-- Modals -->
    <div class="modal-overlay" id="confirmOverlay">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
            <h3 id="confirmTitle">Confirmer la suppression</h3>
            <p id="confirmMessage">Voulez-vous supprimer cet article du panier ?</p>
            <div class="modal-actions">
                <button class="btn btn-secondary" id="confirmCancel">Annuler</button>
                <button class="btn btn-primary" id="confirmOk">Supprimer</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="checkoutOverlay">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="checkoutTitle">
            <h3 id="checkoutTitle">Informations de commande</h3>
            <form id="checkoutForm">
                <div class="form-row">
                    <div style="flex:1">
                        <label>Prénom</label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div style="flex:1">
                        <label>Nom</label>
                        <input type="text" name="last_name" required>
                    </div>
                </div>
                <div class="form-row">
                    <div style="flex:1">
                        <label>Téléphone</label>
                        <input type="text" name="phone" required>
                    </div>
                    <div style="flex:1">
                        <label>Wilaya</label>
                        <select name="wilaya" id="wilayaSelect" required>
                            <option value="">Choisir la wilaya</option>
                            <?php foreach ($wilayas as $w): ?>
                                <option value="<?= (int) $w['id'] ?>" data-price="<?= (int) $w['domicile_price'] ?>">
                                    <?= htmlspecialchars($w['name']) ?> -
                                    <?= number_format($w['domicile_price'], 0, ',', ' ') ?> DA
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div style="flex:1">
                        <label>Commune</label>
                        <input type="text" name="commune" required>
                    </div>
                    <div style="flex:1">
                        <label>Adresse</label>
                        <input type="text" name="address" required>
                    </div>
                </div>
                <div class="order-summary" id="checkoutSummary">
                    <div style="display:flex; justify-content:space-between;"><span>Sous-total</span><span
                            id="sum_subtotal"><?= number_format($subtotal, 0, ',', ' ') ?> DA</span></div>
                    <div style="display:flex; justify-content:space-between;"><span>Livraison</span><span
                            id="sum_shipping"><?= number_format($shipping, 0, ',', ' ') ?> DA</span></div>
                    <div style="display:flex; justify-content:space-between; font-weight:700; margin-top:6px">
                        <span>Total</span><span id="sum_total"><?= number_format($total, 0, ',', ' ') ?> DA</span>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="checkoutCancel">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="checkoutSubmit">Commander</button>
                </div>
            </form>
        </div>
    </div>

<div class="modal-overlay" id="successOverlay">
    <div class="modal" role="dialog" aria-modal="true">
        <h3>Commande passée <span style="color: green; font-size: 22px;">✔</span></h3>
        <p id="successMessage"></p> <!-- on laisse vide, JS remplira le texte -->
        <div class="modal-actions">
            <button class="btn btn-primary" id="successOk">OK</button>
        </div>
    </div>
</div>
    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-col">
                    <div class="footer-logo">
                        <a href="index.php"><img src="assets/images/LOGO-blanc.png" alt="Ama Meuble Logo"
                                class="footer-logo-img"></a>
                    </div>
                    <p class="footer-description">L'élégance du bois au service de votre bien-être</p>
                    <div class="footer-social">
                        <a href="https://www.facebook.com/profile.php?id=61584495925564" class="social-link"
                            aria-label="Facebook">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M22 12C22 6.48 17.52 2 12 2C6.48 2 2 6.48 2 12C2 17.02 5.66 21.13 10.44 21.88V14.89H7.9V12H10.44V9.8C10.44 7.29 11.93 5.91 14.21 5.91C15.3 5.91 16.44 6.1 16.44 6.1V8.56H15.09C13.77 8.56 13.56 9.36 13.56 10.18V12H16.31L15.86 14.89H13.56V21.88C18.34 21.13 22 17.02 22 12Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </a>
                        <a href="https://www.instagram.com/ama.meuble/" class="social-link" aria-label="Instagram">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                        <a href="https://www.tiktok.com/@ama.meuble" class="social-link" aria-label="TikTok"
                            target="_blank" rel="noopener noreferrer">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"
                                fill="currentColor">
                                <path d="M16.7 5.3c-.9-1-1.4-2.3-1.4-3.7h-3.1v13.2c0 1.5-1.2 2.7-2.7 2.7s-2.7-1.2-2.7-2.7 
        1.2-2.7 2.7-2.7c.3 0 .6 0 .9.1V8.1c-.3 0-.6-.1-.9-.1-3.2 0-5.8 2.6-5.8 5.8 
        0 3.2 2.6 5.8 5.8 5.8 3.2 0 5.8-2.6 5.8-5.8V9.4c1.1.8 2.4 1.2 3.8 1.2V7.5
        c-1.2 0-2.3-.5-3.1-1.2z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="footer-col">
                    <h4 class="footer-title">Liens Rapides</h4>
                    <ul class="footer-links">
                        <li><a href="#accueil">Accueil</a></li>
                        <li><a href="#produits">Nos Produits</a></li>
                        <li><a href="#collections">Collections</a></li>
                        <li><a href="#apropos">À propos</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4 class="footer-title">Informations</h4>
                    <ul class="footer-links">
                        <li><a href="#">Livraison</a></li>
                        <li><a href="#">Retours</a></li>
                        <li><a href="#">Garantie</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Mentions légales</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4 class="footer-title">Contact</h4>
                    <ul class="footer-links">
                        <li>
                            <a href="tel:+213557533900">05 57 53 39 00</a>
                        </li>

                        <li>
                            <a href="mailto:Medjsalons@gmail.com">Medjsalons@gmail.com</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2025 Ama Meuble. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Bouton Retour en haut -->
    <button class="scroll-top" id="scrollTop" aria-label="Retour en haut">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 19V5M5 12l7-7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
    </button>

    <script src="assets/js/script.js"></script>
</body>

</html>