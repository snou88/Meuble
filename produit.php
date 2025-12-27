<?php
require_once __DIR__ . '/config/database.php';

session_start();

$db = getDBConnection();

$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $it)
        $cartCount += (int) $it['qty'];
}

// Read product id from GET
$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($productId <= 0) {
    header('Location: produits.php');
    exit;
}

// Fetch product
$stmt = $db->prepare('SELECT * FROM products WHERE id = :id');
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch();
if (!$product) {
    // Product not found
    header('Location: produits.php');
    exit;
}

// Fetch dimensions
$dimStmt = $db->prepare('SELECT * FROM product_dimensions WHERE product_id = :id ORDER BY price ASC');
$dimStmt->execute([':id' => $productId]);
$dimensions = $dimStmt->fetchAll();

// Fetch images (grouped by dimension)
$imgStmt = $db->prepare('SELECT di.* FROM dimension_images di JOIN product_dimensions pd ON di.dimension_id = pd.id WHERE pd.product_id = :id ORDER BY di.is_primary DESC, di.id');
$imgStmt->execute([':id' => $productId]);
$images = $imgStmt->fetchAll();

// Fetch colors
$colorStmt = $db->prepare('SELECT * FROM product_colors WHERE product_id = :id');
$colorStmt->execute([':id' => $productId]);
$colors = $colorStmt->fetchAll();

// Organize images by dimension
$imagesByDim = [];
foreach ($images as $img) {
    $imagesByDim[$img['dimension_id']][] = $img;
}

// All image paths (for fallback)
$allImagePaths = array_map(function ($i) {
    return $i['image_path'];
}, $images);

// Default selected dimension id (first one) and initial thumbnails
$defaultDimId = !empty($dimensions) ? $dimensions[0]['id'] : (isset($images[0]['dimension_id']) ? $images[0]['dimension_id'] : null);
$initialThumbnails = [];
if ($defaultDimId !== null && isset($imagesByDim[$defaultDimId])) {
    $initialThumbnails = $imagesByDim[$defaultDimId];
} else {
    $initialThumbnails = $images;
}

// Organize colors by type
$fabricColors = array_values(array_filter($colors, function ($c) {
    return $c['type'] === 'tissu';
}));
$woodColors = array_values(array_filter($colors, function ($c) {
    return $c['type'] === 'bois';
}));

// Determine main image and default price
$mainImage = null;
if (!empty($images)) {
    $mainImage = $images[0]['image_path'];
}
if (!$mainImage)
    $mainImage = 'assets/images/default_product.jpg';

$minPrice = null;
foreach ($dimensions as $d) {
    if ($minPrice === null || $d['price'] < $minPrice)
        $minPrice = $d['price'];
}
if ($minPrice === null)
    $minPrice = 0;

// Prefer main image from the default dimension thumbnails if available
if (!empty($initialThumbnails) && isset($initialThumbnails[0]['image_path'])) {
    $mainImage = $initialThumbnails[0]['image_path'];
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ama Meuble - Le meuble qui donne une âme à votre maison</title>
    <meta name="description"
        content="Découvrez notre collection de meubles haut de gamme. Design moderne, bois de qualité, fabrication soignée.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/product.css">
    <script src="assets/js/script.js"></script>
    <script>
        // Product page interactions
        document.addEventListener('DOMContentLoaded', function () {
            const thumbnails = document.querySelectorAll('#thumbnails img.thumb');
            const mainImage = document.getElementById('mainImage');
            thumbnails.forEach(t => t.addEventListener('click', () => { mainImage.src = t.dataset.src; }));

            // Dimensions change price and images
            const dimButtons = document.querySelectorAll('#dimensionOptions .option-btn');
            const priceEl = document.getElementById('price');
            const imagesByDim = <?= json_encode(array_map(function ($arr) {
                return array_map(function ($i) {
                    return $i['image_path'];
                }, $arr);
            }, $imagesByDim)) ?>;
            const allImages = <?= json_encode($allImagePaths) ?>;

            function renderThumbnails(imagesArr) {
                const container = document.getElementById('thumbnails');
                container.innerHTML = '';
                if (!imagesArr || imagesArr.length === 0) return;
                imagesArr.forEach((src, idx) => {
                    const div = document.createElement('div');
                    div.className = 'thumbnail' + (idx === 0 ? ' active' : '');
                    div.dataset.index = idx;
                    const img = document.createElement('img');
                    img.className = 'thumb';
                    img.src = src;
                    img.dataset.src = src;
                    img.alt = '<?= htmlspecialchars($product['name']) ?>';
                    div.appendChild(img);
                    div.addEventListener('click', function () {
                        document.querySelectorAll('#thumbnails .thumbnail').forEach(t => t.classList.remove('active'));
                        div.classList.add('active');
                        mainImage.src = src;
                    });
                    container.appendChild(div);
                });
            }

            // Make function globally accessible
            window.attachInitialThumbnailHandlers = function () {
                document.querySelectorAll('#thumbnails .thumbnail').forEach(el => {
                    el.addEventListener('click', function () {
                        document.querySelectorAll('#thumbnails .thumbnail').forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                        const img = this.querySelector('img.thumb');
                        if (img) mainImage.src = img.dataset.src || img.src;
                    });
                });
            };

            // On dimension click: update price and images
            dimButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    dimButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    const price = parseFloat(this.dataset.price) || 0;
                    priceEl.textContent = 'À partir de ' + price.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' DA';

                    const dimId = this.dataset.dimensionId;
                    const imgs = imagesByDim[dimId] && imagesByDim[dimId].length ? imagesByDim[dimId] : allImages;
                    renderThumbnails(imgs);
                    // set main image to first of new thumbnails
                    if (imgs && imgs.length) mainImage.src = imgs[0];
                });
            });

            // Color selections: single-select per group, keyboard accessible
            function setupColorGroup(containerSelector) {
                const group = document.querySelectorAll(containerSelector + ' .color-option');
                group.forEach(el => {
                    el.addEventListener('click', function () {
                        // toggle: if already active, deselect; otherwise select this and deselect others
                        const isActive = el.classList.contains('active');
                        group.forEach(x => { x.classList.remove('active'); x.setAttribute('aria-pressed', 'false'); });
                        if (!isActive) { el.classList.add('active'); el.setAttribute('aria-pressed', 'true'); }
                    });
                    el.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            el.click();
                        }
                    });
                });
            }

            setupColorGroup('#fabricColorOptions');
            setupColorGroup('#woodColorOptions');

            // Quantity
            const qtySpan = document.getElementById('quantity');
            document.getElementById('increaseQty').addEventListener('click', () => { qtySpan.textContent = parseInt(qtySpan.textContent) + 1; });
            document.getElementById('decreaseQty').addEventListener('click', () => { let v = parseInt(qtySpan.textContent); if (v > 1) qtySpan.textContent = v - 1; });

            // Add to cart (real: call API)
            document.getElementById('addToCart').addEventListener('click', async () => {
                const btn = document.getElementById('addToCart');
                btn.disabled = true;
                const selectedDim = document.querySelector('#dimensionOptions .option-btn.active');
                if (!selectedDim) { alert('Veuillez sélectionner une dimension'); btn.disabled = false; return; }
                const selectedFabric = document.querySelector('#fabricColorOptions .color-option.active');
                const selectedWood = document.querySelector('#woodColorOptions .color-option.active');
                // Require both fabric and wood selection
                const missing = [];
                if (!selectedFabric) missing.push('la couleur du tissu');
                if (!selectedWood) missing.push('la couleur du bois');
                if (missing.length > 0) {
                    alert('Veuillez sélectionner ' + missing.join(' et ') + '.');
                    // focus first missing group for accessibility
                    if (!selectedFabric) {
                        const first = document.querySelector('#fabricColorOptions .color-option');
                        if (first) first.focus();
                    } else if (!selectedWood) {
                        const firstw = document.querySelector('#woodColorOptions .color-option');
                        if (firstw) firstw.focus();
                    }
                    btn.disabled = false;
                    return;
                }
                const qty = parseInt(qtySpan.textContent);

                const payload = {
                    action: 'add',
                    productId: <?= (int) $productId ?>,
                    dimensionId: selectedDim ? selectedDim.dataset.dimensionId : null,
                    fabric: selectedFabric ? selectedFabric.dataset.colorName : null,
                    wood: selectedWood ? selectedWood.dataset.woodName : null,
                    qty: qty
                };

                try {
                    const res = await fetch('cart.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                    const data = await res.json();
                    if (data.success) {
                        document.querySelectorAll('.cart-count').forEach(el => el.textContent = data.count);
                        alert('Produit ajouté au panier');
                        window.location.href = 'panier.php';
                    } else {
                        alert('Erreur: ' + (data.error || 'Impossible d\'ajouter au panier'));
                    }
                } catch (e) {
                    alert('Erreur de communication');
                }
                btn.disabled = false;
            });

            // Attach handlers to thumbnails that were rendered server-side initially
            window.attachInitialThumbnailHandlers();
        });
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
</head>

<body>
    <!-- Header / Navbar -->
    <header class="header" id="header">
        <nav class="nav container">
            <div class="nav-logo">
                <a href="index.php"><img src="assets/images/LOGO-blanc.png" alt="Ama Meuble Logo" class="logo-img"></a>
            </div>

            <div class="nav-menu" id="navMenu">
                <ul class="nav-list">
                    <li class="nav-item"><a href="index.php" class="nav-link active">Accueil</a></li>
                    <li class="nav-item"><a href="produits.php" class="nav-link">Produits</a></li>
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
    <div style="display: flex; justify-content: center; align-items: center;">
        <div class="product-container">
            <div class="product-gallery">
                <div class="main-image-container">
                    <img id="mainImage" src="<?= htmlspecialchars($mainImage) ?>"
                        alt="<?= htmlspecialchars($product['name']) ?>" class="main-image">
                </div>
                <div class="thumbnails" id="thumbnails">
                    <?php if (!empty($initialThumbnails)): ?>
                        <?php foreach ($initialThumbnails as $i => $img): ?>
                            <div class="thumbnail <?= $i === 0 ? 'active' : '' ?>"
                                data-dimension-id="<?= (int) $img['dimension_id'] ?>">
                                <img src="<?= htmlspecialchars($img['image_path']) ?>"
                                    alt="<?= htmlspecialchars($product['name']) ?>" class="thumb"
                                    data-src="<?= htmlspecialchars($img['image_path']) ?>">
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="product-details">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                <p class="price" id="price">À partir de <?= number_format($minPrice, 2, ',', ' ') ?> DA</p>

                <div class="option-group">
                    <h3>Dimensions</h3>
                    <div class="options" id="dimensionOptions">
                        <?php if (!empty($dimensions)): ?>
                            <?php foreach ($dimensions as $i => $d): ?>
                                <button class="option-btn <?= $i === 0 ? 'active' : '' ?>"
                                    data-dimension-id="<?= (int) $d['id'] ?>"
                                    data-price="<?= htmlspecialchars($d['price']) ?>"><?= htmlspecialchars($d['label']) ?></button>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div>Aucune dimension disponible</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="option-group">
                    <h3>Couleur du tissu</h3>
                    <div class="options" id="fabricColorOptions">
                        <?php if (!empty($fabricColors)): ?>
                            <?php foreach ($fabricColors as $c): ?>
                                <button class="color-option" data-color-name="<?= htmlspecialchars($c['color_name']) ?>"
                                    data-color-code="<?= htmlspecialchars($c['color_code']) ?>"
                                    title="<?= htmlspecialchars($c['color_name']) ?>"
                                    style="background-color: <?= htmlspecialchars($c['color_code'] ?: '#ccc') ?>;" tabindex="0"
                                    role="button" aria-pressed="false"></button>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div>Aucune couleur tissu</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="option-group">
                    <h3>Couleur des pieds</h3>
                    <div class="options" id="woodColorOptions">
                        <?php if (!empty($woodColors)): ?>
                            <?php foreach ($woodColors as $c): ?>
                                <button class="color-option" data-wood-name="<?= htmlspecialchars($c['color_name']) ?>"
                                    data-wood-code="<?= htmlspecialchars($c['color_code']) ?>"
                                    title="<?= htmlspecialchars($c['color_name']) ?>"
                                    style="background-color: <?= htmlspecialchars($c['color_code'] ?: '#ccc') ?>; border:1px solid #ddd;"
                                    tabindex="0" role="button" aria-pressed="false">
                                </button>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div>Aucune couleur bois</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="quantity-selector">
                    <button id="decreaseQty">-</button>
                    <span id="quantity">1</span>
                    <button id="increaseQty">+</button>
                </div>

                <button id="addToCart" class="add-to-cart-btn">Ajouter au panier</button>

                <div class="product-description">
                    <h3>Description</h3>
                    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
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
            </div>

            <div class="footer-bottom">
                <p>&copy; 2025 Ama Meuble. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <button class="scroll-top" id="scrollTop" aria-label="Retour en haut">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 19V5M5 12l7-7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
    </button>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const mainImage = document.getElementById('mainImage');
            const imageModal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const closeModal = document.getElementById('closeModal');

            if (!mainImage || !imageModal || !modalImage || !closeModal) {
                console.error('Modal image: élément manquant');
                return;
            }

            /* Ouvrir image */
            mainImage.addEventListener('click', () => {
                modalImage.src = mainImage.src;
                imageModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });

            /* Fermer avec X */
            closeModal.addEventListener('click', closeModalFn);

            /* Fermer en cliquant dehors */
            imageModal.addEventListener('click', (e) => {
                if (e.target === imageModal) closeModalFn();
            });

            function closeModalFn() {
                imageModal.style.display = 'none';
                document.body.style.overflow = '';
            }

        });
    </script>

    <!-- Image Modal -->
    <div id="imageModal" class="image-modal" style="display: none;">
        <span id="closeModal" class="close-modal" aria-label="Fermer l'image">&times;</span>
        <img id="modalImage" class="modal-content" alt="Image agrandie">
    </div>
</body>


</html>