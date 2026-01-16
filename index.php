<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ama Meuble - Le meuble qui donne une âme à votre maison</title>
    <meta name="description"
        content="Découvrez notre collection de meubles haut de gamme. Design moderne, bois de qualité, fabrication soignée.">
    <link rel="stylesheet" href="assets/css/style.css">
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

    // Handle contact message submission from homepage
    $messageStatus = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
        require_once __DIR__ . '/config/database.php';
        try {
            $db = getDBConnection();
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $comment = trim($_POST['message'] ?? '');
            if ($name === '' || $email === '' || $comment === '') {
                $messageStatus = ['ok' => false, 'msg' => 'Veuillez remplir le nom, l\'email et le message.'];
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $messageStatus = ['ok' => false, 'msg' => 'Email invalide.'];
            } else {
                $stmt = $db->prepare('INSERT INTO customer_messages (client_name, client_email, client_phone, comment, created_at) VALUES (?, ?, ?, ?, NOW())');
                $stmt->execute([$name, $email, $phone, $comment]);
                $messageStatus = ['ok' => true, 'msg' => 'Message envoyé. Merci!'];
            }
        } catch (Exception $e) {
            $messageStatus = ['ok' => false, 'msg' => 'Erreur serveur.'];
        }
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
                    <li class="nav-item"><a href="#accueil" class="nav-link active">Accueil</a></li>
                    <li class="nav-item nav-dropdown"><a href="produits.php" class="nav-link">Produits</a>
                        <div class="nav-dropdown-content">
                            <a href="produits.php">Tous</a>
                            <a href="produits.php?type=made_to_order">Sur Commande</a>
                            <a href="produits.php?type=available">Disponible</a>
                        </div>
                    </li>
                    <li class="nav-item"><a href="#collections" class="nav-link">Collections</a></li>
                    <li class="nav-item"><a href="#apropos" class="nav-link">À propos</a></li>
                    <li class="nav-item"><a href="#contact" class="nav-link">Contact</a></li>
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
    <section class="hero" id="accueil">
        <div class="hero-overlay"></div>
        <div class="hero-content container">
            <h1 class="hero-title fade-in">Le meuble qui donne une âme à votre maison</h1>
            <p class="hero-subtitle fade-in">Découvrez notre collection exclusive de meubles artisanaux alliant élégance
                intemporelle et confort moderne</p>
            <a href="produits.php"><button class="btn btn-hero fade-in">Découvrir nos meubles</button></a>
        </div>
        <div class="hero-scroll">
            <span>Scroll</span>
            <div class="scroll-line"></div>
        </div>
    </section>

    <!-- Section Catégories -->
    <section class="categories section" id="collections">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Sur commandes</h2>
                <p class="section-subtitle">Explorez nos univers dédiés à chaque pièce de votre maison</p>
            </div>

            <?php
            require_once __DIR__ . '/config/database.php';
            try {
                $db = getDBConnection();
                $catStmt = $db->query("SELECT id, name, slug, image_path, description FROM categories ORDER BY name");
                $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $categories = [];
            }

            if (empty($categories)): ?>
                <div class="categories-grid">
                    <p>Aucune catégorie disponible pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="categories-grid">
                    <?php foreach ($categories as $i => $cat):
                        $img = !empty($cat['image_path']) ? $cat['image_path'] : 'assets/images/default_category.jpg';
                        $name = htmlspecialchars($cat['name']);
                        $desc = !empty($cat['description']) ? htmlspecialchars($cat['description']) : '';
                        $link = 'produits.php?category_id=' . (int) $cat['id'];
                        $delay = ($i % 4) * 100;
                        ?>
                        <a href="<?= htmlspecialchars($link) ?>">
                            <div class="category-card" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                                <div class="category-image">
                                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= $name ?>">
                                    <div class="category-overlay">
                                        <h3><?= $name ?></h3>
                                        <p><?= $desc ?></p>
                                        <span class="category-link">Explorer →</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script>
        // Poll category timestamp and reload the homepage when categories change (so admins additions/edits show without manual refresh)
        (function () {
            let lastTs = null;
            async function check() {
                try {
                    const res = await fetch('category_ts.php');
                    if (!res.ok) return;
                    const json = await res.json();
                    const ts = parseInt(json.ts || 0);
                    if (lastTs === null) lastTs = ts;
                    if (ts > lastTs) {
                        // categories changed by admin: reload to reflect new categories
                        location.reload();
                    }
                } catch (e) { /* ignore */ }
            }
            check();
            setInterval(check, 8000);
        })();
    </script>

    <!-- Section Produits Vedettes -->
    <section class="products section" id="produits">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Produits disponibles</h2>
                <p class="section-subtitle">Une sélection de nos pièces les plus prisées</p>
            </div>

            <div class="products-grid">
                <?php
                // Fetch 4 random featured products
                require_once __DIR__ . '/config/database.php';
                try {
                    $db = getDBConnection();
                    $sql = "SELECT p.*, 
                                (SELECT MIN(pd.price) FROM product_dimensions pd WHERE pd.product_id = p.id) AS min_price,
                                (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.id LIMIT 1) AS primary_image
                                FROM products p WHERE p.product_type = 'available' ORDER BY RAND() LIMIT 3";
                    $stmt = $db->query($sql);
                    $featured = $stmt->fetchAll();
                } catch (Exception $e) {
                    $featured = [];
                }

                if (empty($featured)) {
                    echo '<p>Aucun produit vedette pour le moment.</p>';
                } else {
                    foreach ($featured as $i => $prod):
                        $img = !empty($prod['primary_image']) ? $prod['primary_image'] : 'assets/images/default_product.jpg';
                        $price = isset($prod['min_price']) ? number_format($prod['min_price'], 0, ',', ' ') . ' DA' : '';
                        $name = htmlspecialchars($prod['name']);
                        $desc = !empty($prod['description']) ? htmlspecialchars($prod['description']) : '';
                        $link = 'produit.php?id=' . (int) $prod['id'];
                        ?>
                        <div class="product-card" data-aos="fade-up" data-aos-delay="<?= ($i % 4) * 100 ?>">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= $name ?>">
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?= $name ?></h3>
                                <p class="product-description"><?= $desc ?></p>
                                <div class="product-footer">
                                    <span class="product-price"><?= $price ?></span>
                                    <a href="<?= $link ?>"><button class="btn btn-secondary">Voir le produit</button></a>
                                </div>
                            </div>
                        </div>
                        <?php
                    endforeach;
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Section Pourquoi Ama Meuble -->
    <section class="why-us section" id="apropos">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title" style="color: white;">Pourquoi Ama Meuble</h2>
                <p class="section-subtitle" style="color: white;">L’art des meubles sur mesure pour votre maison</p>
            </div>

            <div class="features-grid">
                <div class="feature-card" data-aos="zoom-in">
                    <div class="feature-icon">
                        <img style="width: 130px;" src="assets/images/solidite.png" alt="">
                    </div>
                    <h3 class="feature-title">La solidité</h3>
                    <p class="feature-description">Notre priorité.
                        Parce qu’un bon meuble commence par une base solide.</p>
                </div>

                <div class="feature-card" data-aos="zoom-in" data-aos-delay="100">
                    <div class="feature-icon">
                        <img style="width: 130px;" src="assets/images/confiance.png" alt="">
                    </div>
                    <h3 class="feature-title">La confiance</h3>
                    <p class="feature-description">Un service fiable et un suivi responsable à chaque étape.</p>
                </div>

                <div class="feature-card" data-aos="zoom-in" data-aos-delay="200">
                    <div class="feature-icon">
                        <img style="width: 130px;" src="assets/images/1986.png" alt="">
                    </div>
                    <h3 class="feature-title">Depuis 1986</h3>
                    <p class="feature-description">Une expérience de plus de 40 ans au cœur de notre atelier.</p>
                </div>

                <div class="feature-card" data-aos="zoom-in" data-aos-delay="300">
                    <div class="feature-icon">
                        <img style="width: 130px;" src="assets/images/livraison.png" alt="">
                    </div>
                    <h3 class="feature-title">Livraison Rapide</h3>
                    <p class="feature-description">Service de livraison professionnel et installation à domicile dans
                        toute l'Algerie</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Inspiration -->
    <section class="inspiration section">
        <div class="inspiration-content">
            <div class="inspiration-text" data-aos="fade-right">
                <h2 class="inspiration-title">Inspirez-vous</h2>
                <p class="inspiration-description">
                    Chaque meuble raconte une histoire. Celle de l'arbre qui l'a vu naître,
                    celle de l'artisan qui l'a façonné, et bientôt, celle de votre foyer qu'il embellira.
                    Ama Meuble, c'est l'art de transformer le bois en émotions.
                </p>
                <blockquote class="inspiration-quote">
                    "Un meuble n'est pas qu'un objet, c'est une présence qui accompagne vos moments précieux"
                </blockquote>
            </div>
            <div class="inspiration-gallery">
                <div class="gallery-item large" data-aos="fade-left">
                    <img src="https://images.pexels.com/photos/1743229/pexels-photo-1743229.jpeg?auto=compress&cs=tinysrgb&w=800"
                        alt="Inspiration 1">
                </div>
                <div class="gallery-item" data-aos="fade-left" data-aos-delay="100">
                    <img src="https://images.pexels.com/photos/1457842/pexels-photo-1457842.jpeg?auto=compress&cs=tinysrgb&w=800"
                        alt="Inspiration 2">
                </div>
                <div class="gallery-item" data-aos="fade-left" data-aos-delay="200">
                    <img src="https://images.pexels.com/photos/1350789/pexels-photo-1350789.jpeg?auto=compress&cs=tinysrgb&w=800"
                        alt="Inspiration 3">
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section section" id="contact">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Contactez-nous</h2>
                <p class="section-subtitle">Une question ? Un projet personnalisé ? Envoyez-nous un message.</p>
            </div>
            <div class="contact-grid">
                <div class="contact-form">
                    <?php if (isset($messageStatus)): ?>
                        <div class="alert <?= $messageStatus['ok'] ? 'alert-success' : 'alert-error' ?>">
                            <?= htmlspecialchars($messageStatus['msg']) ?>
                        </div>
                    <?php endif; ?>
                    <form method="post" class="form-contact">
                        <input type="hidden" name="send_message" value="1">
                        <div class="form-row"><input type="text" name="name" placeholder="Votre nom" required></div>
                        <div class="form-row"><input type="email" name="email" placeholder="Votre email" required></div>
                        <div class="form-row"><input type="text" name="phone" placeholder="Téléphone (optionnel)"></div>
                        <div class="form-row"><textarea name="message" placeholder="Votre message" required
                                rows="5"></textarea></div>
                        <div class="form-row" style="text-align:right;"><button class="btn"
                                type="submit">Envoyer</button></div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <div id="popup" class="popup-overlay">
        <div class="popup-content" id="popup-content">
            <p id="popup-message"></p>
            <button class="popup-close" onclick="closePopup()">OK</button>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-col">
                    <div class="footer-logo">
                        <a href="index.php"><img src="assets/images/LOGO-blanc.png" alt="Ama Meuble Logo"
                                class="logo-img"></a>
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
                        <li><a href="#apropos">Livraison</a></li>
                        <li><a href="#apropos">Retours</a></li>
                        <li><a href="#apropos">FAQ</a></li>
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
<?php if (isset($messageStatus)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showPopup("<?= htmlspecialchars($messageStatus['msg']) ?>", <?= $messageStatus['ok'] ? 'true' : 'false' ?>);
        });
    </script>
<?php endif; ?>
</html>