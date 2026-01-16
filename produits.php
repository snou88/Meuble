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
    $cartCount = 0;
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $it)
            $cartCount += (int) $it['qty'];
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
    <section class="products-page-section">
        <div class="products-container">
            <!-- Sidebar Filters -->
            <aside class="products-sidebar" id="productsSidebar">
                <?php
                require_once __DIR__ . '/config/database.php';
                $db = getDBConnection();

                // Read filters from GET
                $typep = isset($_GET['type']) ? trim($_GET['type']) : '';
                $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                $maxPriceFilter = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float) $_GET['max_price'] : null;
                $selectedFabric = isset($_GET['fabric']) && is_array($_GET['fabric']) ? $_GET['fabric'] : [];
                $selectedWood = isset($_GET['wood']) && is_array($_GET['wood']) ? $_GET['wood'] : [];
                $categoryFilter = isset($_GET['category_id']) && is_numeric($_GET['category_id']) && (int) $_GET['category_id'] > 0 ? (int) $_GET['category_id'] : null;
                $sort = isset($_GET['sort']) ? $_GET['sort'] : 'featured';

                // load categories for category filter UI
                $catList = $db->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

                // Get price range maximum from DB
                $maxPriceStmt = $db->query("SELECT MAX(price) AS max_price FROM product_dimensions");
                $maxPriceRow = $maxPriceStmt->fetch();
                $globalMaxPrice = $maxPriceRow && $maxPriceRow['max_price'] ? (float) $maxPriceRow['max_price'] : 50000;
                if (!$maxPriceFilter)
                    $maxPriceFilter = $globalMaxPrice;

                // Get color lists
                $fabricStmt = $db->prepare("SELECT DISTINCT color_code, color_name FROM product_colors WHERE type = 'tissu'");
                $fabricStmt->execute();
                $fabricColors = $fabricStmt->fetchAll();

                $woodStmt = $db->prepare("SELECT DISTINCT color_code, color_name FROM product_colors WHERE type = 'bois'");
                $woodStmt->execute();
                $woodColors = $woodStmt->fetchAll();

                // Build products query with filters
                $params = [];
                $where = [];

                // search (named param) - use two different placeholders
                if (!empty($search)) {
                    $where[] = "(p.name LIKE :search_name OR p.description LIKE :search_desc)";
                    $params['search_name'] = "%$search%";
                    $params['search_desc'] = "%$search%";
                }
                // search (named param) - use two different placeholders
                if (!empty($typep)) {
                    $where[] = "(p.product_type LIKE :search_typep)";
                    $params['search_typep'] = "%$typep%";
                }

                // MAX PRICE
                if ($maxPriceFilter !== null) {
                    $where[] = "(SELECT MIN(pd.price)
                 FROM product_dimensions pd
                 WHERE pd.product_id = p.id) <= :max_price";
                    $params['max_price'] = $maxPriceFilter;
                }

                // fabrics (named placeholders)
                if (!empty($selectedFabric)) {
                    $placeholders = [];
                    foreach ($selectedFabric as $i => $val) {
                        $key = "fabric_$i";
                        $placeholders[] = ":$key";        // used in SQL
                        $params[$key] = $val;            // used in execute()
                    }
                    $where[] = "EXISTS (
        SELECT 1 FROM product_colors pc 
        WHERE pc.product_id = p.id 
          AND pc.type = 'tissu' 
          AND pc.color_code IN (" . implode(',', $placeholders) . ")
    )";
                }

                // woods (named placeholders)
                if (!empty($selectedWood)) {
                    $placeholders = [];
                    foreach ($selectedWood as $i => $val) {
                        $key = "wood_$i";
                        $placeholders[] = ":$key";
                        $params[$key] = $val;
                    }
                    $where[] = "EXISTS (
        SELECT 1 FROM product_colors pc 
        WHERE pc.product_id = p.id 
          AND pc.type = 'bois' 
          AND pc.color_code IN (" . implode(',', $placeholders) . ")
    )";
                }

                // Category filter (single select)
                if (!empty($categoryFilter)) {
                    $where[] = 'p.category_id = :category_id';
                    $params['category_id'] = $categoryFilter;
                }

                $whereSql = '';
                if (!empty($where)) {
                    $whereSql = 'WHERE ' . implode(' AND ', $where);
                }

                // Sorting
                $orderBy = 'p.id DESC';
                if ($sort === 'price-asc')
                    $orderBy = 'min_price ASC';
                elseif ($sort === 'price-desc')
                    $orderBy = 'min_price DESC';
                elseif ($sort === 'name-asc')
                    $orderBy = 'p.name ASC';
                elseif ($sort === 'name-desc')
                    $orderBy = 'p.name DESC';

                // Final SQL - include min/max price and promote info from dimensions (first new-price if any)
                $sql = "SELECT p.*, 
    (SELECT MIN(pd.price) FROM product_dimensions pd WHERE pd.product_id = p.id) AS min_price,
    (SELECT MAX(pd.price) FROM product_dimensions pd WHERE pd.product_id = p.id) AS max_price,
    (SELECT MIN(pd.price_new) FROM product_dimensions pd WHERE pd.product_id = p.id AND pd.price_new IS NOT NULL) AS min_price_new,
    (SELECT pd.promo_percent FROM product_dimensions pd WHERE pd.product_id = p.id AND pd.price_new IS NOT NULL ORDER BY pd.price_new ASC LIMIT 1) AS promo_percent,
    (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.id LIMIT 1) AS primary_image
    FROM products p $whereSql ORDER BY $orderBy";

                $stmt = $db->prepare($sql);
                $stmt->execute($params); // execute with associative array (keys without colon)
                $productsFromDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

                /*                 // Bind named params first
                                foreach ($params as $k => $v) {
                                    // numeric keys come from selected arrays, bind by position
                                    if (is_string($k)) {
                                        $stmt->bindValue($k, $v);
                                    }
                                }
                                // Execute with numeric params (if any)
                                $executeParams = array_values(array_filter($params, function ($k) {
                                    return !is_string($k); }, ARRAY_FILTER_USE_KEY));
                                if (!empty($executeParams)) {
                                    $stmt->execute($executeParams);
                                } else {
                                    $stmt->execute();
                                }
                                $productsFromDb = $stmt->fetchAll(); */
                ?>
                <div class="sidebar-header">
                    <h2>Filtrer</h2>
                    <button class="sidebar-close" id="sidebarClose">&times;</button>
                </div>

                <form id="filtersForm" method="GET">
                    <!-- Search -->
                    <div class="filter-section">
                        <h3 class="filter-title">Recherche</h3>
                        <input type="text" id="searchInput" name="search" placeholder="Rechercher un produit..."
                            class="search-input" value="<?= htmlspecialchars($search) ?>">
                    </div>

                    <!-- Category Filter -->
                    <div class="filter-section">
                        <h3 class="filter-title">Catégorie</h3>
                        <select name="category_id" id="category_id_filter">
                            <option value="">Toutes</option>
                            <?php foreach ($catList as $c): ?>
                                <option value="<?= (int) $c['id'] ?>" <?= ($categoryFilter === (int) $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-section">
                        <h3 class="filter-title">Prix</h3>
                        <div class="price-range">
                            <input type="range" id="priceRange" name="max_price" min="0"
                                max="<?= (int) $globalMaxPrice ?>" value="<?= (int) $maxPriceFilter ?>" step="100">
                            <div class="price-display">
                                <span>0 DA</span>
                                <span id="maxPriceDisplay"><?= number_format($maxPriceFilter, 0, ',', ' ') ?> DA</span>
                            </div>
                        </div>
                    </div>

                    <!-- Fabric Colors -->
                    <div class="filter-section">
                        <h3 class="filter-title">Couleur Tissu</h3>
                        <div class="filter-options" id="fabricFilters">
                            <?php foreach ($fabricColors as $c): ?>
                                <label class="filter-swatch" title="<?= htmlspecialchars($c['color_name']) ?>">
                                    <input type="checkbox" name="fabric[]" value="<?= htmlspecialchars($c['color_code']) ?>"
                                        <?= in_array($c['color_code'], $selectedFabric) ? 'checked' : '' ?>>
                                    <span class="swatch"
                                        style="background-color: <?= htmlspecialchars($c['color_code'] ?: '#ccc') ?>;"></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Wood Colors -->
                    <div class="filter-section">
                        <h3 class="filter-title">Couleur Bois</h3>
                        <div class="filter-options" id="woodFilters">
                            <?php foreach ($woodColors as $c): ?>
                                <label class="filter-swatch" title="<?= htmlspecialchars($c['color_name']) ?>">
                                    <input type="checkbox" name="wood[]" value="<?= htmlspecialchars($c['color_code']) ?>"
                                        <?= in_array($c['color_code'], $selectedWood) ? 'checked' : '' ?>>
                                    <span class="swatch"
                                        style="background-color: <?= htmlspecialchars($c['color_code'] ?: '#ccc') ?>;"></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div style="margin-top:10px; display:flex; gap:8px;">
                        <a href="produits.php" class="btn-reset-filters">Réinitialiser</a>
                    </div>
                </form>
            </aside>

            <!-- Main Content -->
            <main class="products-main">
                <!-- Filtre Mobile Toggle -->
                <button class="filter-toggle" id="filterToggle">
                    <i class="fas fa-filter"></i>
                    <span>Filtrer</span>
                </button>

                <!-- Products Grid -->
                <!-- Section Produits Vedettes -->
                <section class="products section" id="produits" style="padding: 0;">
                    <div class="container">
                        <div class="section-header">
                            <h2 class="section-title">Nos Produits</h2>
                            <p class="section-subtitle"> Voici les produits </p>
                        </div>

                        <?php if (empty($productsFromDb)): ?>
                            <div id="noResults" class="no-results"
                                style=" display: flex; flex-direction: column; align-items: center;">
                                <div class="no-results-icon">🔍</div>
                                <h3>Aucun produit trouvé</h3>
                                <p>Aucun produit ne correspond à vos critères de recherche.</p>
                                <a href="produits.php" class="btn-reset-filters">Réinitialiser les filtres</a>
                            </div>
                        <?php else: ?>
                            <div class="products-grid">
                                <?php foreach ($productsFromDb as $i => $prod):
                                    $img = !empty($prod['primary_image']) ? $prod['primary_image'] : 'assets/images/default_product.jpg';
                                    $minP = isset($prod['min_price']) ? (float) $prod['min_price'] : 0.0;
                                    $maxP = isset($prod['max_price']) ? (float) $prod['max_price'] : $minP;
                                    $minPriceNew = isset($prod['min_price_new']) && $prod['min_price_new'] !== null ? (float) $prod['min_price_new'] : null;
                                    $promo = isset($prod['promo_percent']) ? (int) $prod['promo_percent'] : 0;
                                    $desc = !empty($prod['description']) ? htmlspecialchars($prod['description']) : '';
                                    $name = htmlspecialchars($prod['name']);
                                    $link = 'produit.php?id=' . (int) $prod['id'];
                                    ?>
                                    <div class="product-card" data-aos="fade-up" data-aos-delay="<?= ($i % 6) * 100 ?>">
                                        <div class="product-image">
                                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= $name ?>">
                                        </div>
                                        <div class="product-info d-flex flex-column h-100">

                                            <h3 class="product-name"><?= $name ?></h3>
                                            <p class="product-description"><?= $desc ?></p>

                                            <div class="product-footer d-flex flex-column flex-grow-1">

                                                <?php if ($minP == $maxP): ?>
                                                    <?php if ($minPriceNew !== null): ?>

                                                        <div class="d-flex align-items-center">

                                                            <span class="product-price-old"
                                                                style="color:#888;text-decoration:line-through;margin-right:8px; font-size: 28px; font-weight: 700;">
                                                                <?= number_format($minP, 0, ',', ' ') ?> DA
                                                            </span>

                                                            <?php if ($promo): ?>
                                                                <span class="product-promo"
                                                                    style="color:#c00;margin-right:8px;font-size: 28px; font-weight: 700; position: relative; left: 100px; top: 25px;">
                                                                    -<?= $promo ?>%
                                                                </span>
                                                            <?php endif; ?>

                                                            <span class="product-price-new"
                                                                style="color:#c00;font-weight:700; font-size: 28px; display: flex;">
                                                                <?= number_format($minPriceNew, 0, ',', ' ') ?> DA
                                                            </span>

                                                        </div>

                                                    <?php else: ?>

                                                        <span class="product-price">
                                                            <?= number_format($minP, 0, ',', ' ') ?> DA
                                                        </span>

                                                    <?php endif; ?>
                                                <?php else: ?>

                                                    <span class="product-price">
                                                        <?= number_format($minP, 0, ',', ' ') ?> DA
                                                    </span>

                                                <?php endif; ?>

                                                <!-- BOUTON FIXE EN BAS -->
                                                <a href="<?= $link ?>" class="mt-auto align-self-start">
                                                    <button class="btn btn-secondary">Voir le produit</button>
                                                </a>

                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </main>
        </div>
    </section>

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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Filtre responsive
            const filterToggle = document.getElementById('filterToggle');
            const productsSidebar = document.getElementById('productsSidebar');
            const sidebarClose = document.getElementById('sidebarClose');

            if (filterToggle && productsSidebar && sidebarClose) {
                filterToggle.addEventListener('click', function () {
                    productsSidebar.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });

                sidebarClose.addEventListener('click', function () {
                    productsSidebar.classList.remove('active');
                    document.body.style.overflow = '';
                });

                // Fermer la sidebar en cliquant à l'extérieur
                document.addEventListener('click', function (e) {
                    if (window.innerWidth <= 768 &&
                        productsSidebar.classList.contains('active') &&
                        !productsSidebar.contains(e.target) &&
                        !filterToggle.contains(e.target)) {
                        productsSidebar.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            }

            const priceRange = document.getElementById('priceRange');
            const maxPriceDisplay = document.getElementById('maxPriceDisplay');
            if (priceRange && maxPriceDisplay) {
                priceRange.addEventListener('input', function () {
                    maxPriceDisplay.textContent = this.value + ' DA';
                });
                priceRange.addEventListener('change', function () {
                    document.getElementById('filtersForm').submit();
                });
            }

            document.querySelectorAll('#fabricFilters input[type=checkbox], #woodFilters input[type=checkbox]').forEach(cb => {
                cb.addEventListener('change', function () { document.getElementById('filtersForm').submit(); });
            });

            // category select change submits filters form
            const catSel = document.getElementById('category_id_filter');
            if (catSel) catSel.addEventListener('change', function () { document.getElementById('filtersForm').submit(); });

            // If search input and user presses Enter, submit form
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        document.getElementById('filtersForm').submit();
                    }
                });
            }
        });
    </script>
</body>

</html>