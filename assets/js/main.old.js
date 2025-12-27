/* ============================================
   AMA MEUBLE - JAVASCRIPT PRINCIPAL
   Fonctionnalités client avec API PHP
   ============================================ */

// Variables globales pour le cache
let categoriesData = [];
let productsData = [];


// ===== UTILITAIRES =====

// Récupérer un produit par ID depuis l'URL
function getProductIdFromURL() {
    const params = new URLSearchParams(window.location.search);
    return parseInt(params.get('id')) || null;
}

// Formater le prix
function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
}

// Animation au scroll
function initScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.section, .product-card, .category-card, .feature-card').forEach(el => {
        observer.observe(el);
    });
}

// ===== MENU MOBILE =====

function initMobileMenu() {
    const toggle = document.getElementById('mobileMenuToggle');
    const menu = document.getElementById('navMenu');

    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            toggle.classList.toggle('active');
            menu.classList.toggle('active');
        });

        // Fermer le menu au clic sur un lien
        menu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                toggle.classList.remove('active');
                menu.classList.remove('active');
            });
        });
    }
}

// ===== PAGE ACCUEIL =====

async function renderCategories() {
    const container = document.getElementById('categoriesGrid');
    if (!container) return;

    try {
        categoriesData = await CategoriesAPI.getAll();
        
        // Utiliser la classe premium si disponible
        const isPremium = container.classList.contains('categories-grid-premium');
        const cardClass = isPremium ? 'category-card-premium' : 'category-card';
        const contentClass = isPremium ? 'category-card-premium-content' : 'category-card-content';
        
        container.innerHTML = categoriesData.map(category => `
            <div class="${cardClass}" data-category-id="${category.id}">
                <img src="${category.image || 'https://via.placeholder.com/400x300?text=' + encodeURIComponent(category.name)}" alt="${category.name}">
                <div class="${contentClass}">
                    <h3>${category.name}</h3>
                    ${category.description ? `<p>${category.description}</p>` : ''}
                </div>
            </div>
        `).join('');

        // Ajouter les événements de clic
        container.querySelectorAll(`.${cardClass}`).forEach(card => {
            card.addEventListener('click', () => {
                const categoryId = card.dataset.categoryId;
                window.location.href = `products.html?category=${categoryId}`;
            });
        });
    } catch (error) {
        container.innerHTML = '<p style="text-align: center; color: var(--color-olive); padding: var(--spacing-xl);">Erreur lors du chargement des catégories.</p>';
        console.error('Erreur:', error);
    }
}

async function renderFeaturedProducts() {
    const container = document.getElementById('featuredProducts');
    if (!container) return;

    try {
        const products = await ProductsAPI.getAll({ featured: true });
        const isPremium = container.classList.contains('products-grid-premium');
        
        container.innerHTML = products.map(product => {
            const stockStatus = getStockStatus(product.quantity);
            return `
                <div class="product-card" data-product-id="${product.id}">
                    <div class="product-card-image-wrapper">
                        ${product.featured ? '<div class="product-badge">En vedette</div>' : ''}
                        <img src="${product.main_image || product.images?.[0] || 'https://via.placeholder.com/400x300?text=' + encodeURIComponent(product.name)}" 
                             alt="${product.name}" class="product-card-image">
                    </div>
                    <div class="product-card-content">
                        <div class="product-card-category">${product.category_name || ''}</div>
                        <h3>${product.name}</h3>
                        <div class="product-card-price">${formatPrice(product.price)}</div>
                        <div class="product-card-stock ${stockStatus.class}">${stockStatus.text}</div>
                        <a href="product.html?id=${product.id}" class="btn-secondary">Voir le produit</a>
                    </div>
                </div>
            `;
        }).join('');
    } catch (error) {
        container.innerHTML = '<p style="text-align: center; color: var(--color-olive); padding: var(--spacing-xl);">Erreur lors du chargement des produits.</p>';
        console.error('Erreur:', error);
    }
}

// ===== PAGE PRODUITS =====

let allProductsData = [];
let filteredProducts = [];

async function renderProducts(products = null, isListView = false) {
    const container = document.getElementById('productsGrid');
    const noResults = document.getElementById('noResults');
    const loadingState = document.getElementById('loadingState');
    
    if (!container) return;

    try {
        if (loadingState) loadingState.style.display = 'block';
        if (container) container.style.display = 'none';
        if (noResults) noResults.style.display = 'none';

        if (!products) {
            const urlParams = new URLSearchParams(window.location.search);
            const categoryId = urlParams.get('category');
            const params = categoryId ? { category_id: categoryId } : {};
            products = await ProductsAPI.getAll(params);
            allProductsData = products;
        }

        filteredProducts = products;

        // Appliquer le tri
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) {
            const sortValue = sortSelect.value;
            filteredProducts = sortProducts(filteredProducts, sortValue);
        }

        if (filteredProducts.length === 0) {
            if (loadingState) loadingState.style.display = 'none';
            container.style.display = 'none';
            if (noResults) noResults.style.display = 'block';
            updateProductsCount(0);
            return;
        }

        if (loadingState) loadingState.style.display = 'none';
        container.style.display = isListView ? 'block' : 'grid';
        if (noResults) noResults.style.display = 'none';

        // Appliquer la classe list-view si nécessaire
        if (isListView) {
            container.classList.add('list-view');
        } else {
            container.classList.remove('list-view');
        }

        container.innerHTML = filteredProducts.map(product => {
            const stockStatus = getStockStatus(product.quantity);
            const stockClass = stockStatus.class;
            const stockText = stockStatus.text;
            
            return `
                <div class="product-card" data-product-id="${product.id}">
                    <div class="product-card-image-wrapper">
                        ${product.featured ? '<div class="product-badge">En vedette</div>' : ''}
                        <img src="${product.main_image || product.images?.[0] || 'https://via.placeholder.com/400x300?text=' + encodeURIComponent(product.name)}" 
                             alt="${product.name}" class="product-card-image">
                    </div>
                    <div class="product-card-content">
                        <div class="product-card-category">${product.category_name || ''}</div>
                        <h3>${product.name}</h3>
                        <div class="product-card-price">${formatPrice(product.price)}</div>
                        <div class="product-card-stock ${stockClass}">${stockText}</div>
                        <a href="product.html?id=${product.id}" class="btn-secondary">Voir le produit</a>
                    </div>
                </div>
            `;
        }).join('');
        
        productsData = filteredProducts;
        updateProductsCount(filteredProducts.length);
    } catch (error) {
        if (loadingState) loadingState.style.display = 'none';
        container.innerHTML = '<p style="text-align: center; color: var(--color-olive); padding: var(--spacing-xl);">Erreur lors du chargement des produits.</p>';
        console.error('Erreur:', error);
        updateProductsCount(0);
    }
}

function getStockStatus(quantity) {
    if (!quantity || quantity === 0) {
        return { class: 'out-of-stock', text: 'Rupture de stock' };
    } else if (quantity < 5) {
        return { class: 'low-stock', text: `Plus que ${quantity} en stock` };
    } else {
        return { class: 'in-stock', text: 'En stock' };
    }
}

function sortProducts(products, sortValue) {
    const sorted = [...products];
    switch(sortValue) {
        case 'price-asc':
            return sorted.sort((a, b) => a.price - b.price);
        case 'price-desc':
            return sorted.sort((a, b) => b.price - a.price);
        case 'name-asc':
            return sorted.sort((a, b) => a.name.localeCompare(b.name));
        case 'name-desc':
            return sorted.sort((a, b) => b.name.localeCompare(a.name));
        case 'featured':
        default:
            return sorted.sort((a, b) => (b.featured ? 1 : 0) - (a.featured ? 1 : 0));
    }
}

function updateProductsCount(count) {
    const countEl = document.getElementById('productsCount');
    if (countEl) {
        countEl.textContent = `${count} produit${count > 1 ? 's' : ''}`;
    }
}

async function initFilters() {
    try {
        // Charger les catégories
        categoriesData = await CategoriesAPI.getAll();
        
        // Charger tous les produits
        allProductsData = await ProductsAPI.getAll();
        
        // Remplir les filtres de catégories
        const categoryContainer = document.getElementById('categoryFilters');
        if (categoryContainer) {
            categoryContainer.innerHTML = categoriesData.map(cat => `
                <div class="filter-option">
                    <input type="checkbox" id="cat-${cat.id}" value="${cat.id}" data-filter="category">
                    <label for="cat-${cat.id}">${cat.name}</label>
                </div>
            `).join('');
        }

        // Récupérer les couleurs uniques
        const fabricColors = new Set();
        const woodColors = new Set();

        for (const product of allProductsData) {
            try {
                const fullProduct = await ProductsAPI.getById(product.id);
                if (fullProduct.fabricColors) {
                    fullProduct.fabricColors.forEach(c => fabricColors.add(c.name));
                }
                if (fullProduct.woodColors) {
                    fullProduct.woodColors.forEach(c => woodColors.add(c.name));
                }
            } catch (e) {
                // Ignorer les erreurs
            }
        }

        // Remplir les filtres de couleurs tissu
        const fabricContainer = document.getElementById('fabricFilters');
        if (fabricContainer) {
            fabricContainer.innerHTML = Array.from(fabricColors).map(color => `
                <div class="color-filter-option">
                    <input type="checkbox" id="fabric-${color}" value="${color}" data-filter="fabric">
                    <label for="fabric-${color}">${color}</label>
                </div>
            `).join('');
        }

        // Remplir les filtres de couleurs bois
        const woodContainer = document.getElementById('woodFilters');
        if (woodContainer) {
            woodContainer.innerHTML = Array.from(woodColors).map(color => `
                <div class="color-filter-option">
                    <input type="checkbox" id="wood-${color}" value="${color}" data-filter="wood">
                    <label for="wood-${color}">${color}</label>
                </div>
            `).join('');
        }

        // Prix max
        const priceRange = document.getElementById('priceRange');
        const maxPriceDisplay = document.getElementById('maxPriceDisplay');
        if (priceRange && maxPriceDisplay) {
            const maxPrice = Math.max(...allProductsData.map(p => p.price), 5000);
            priceRange.max = Math.ceil(maxPrice);
            priceRange.value = maxPrice;
            maxPriceDisplay.textContent = Math.ceil(maxPrice) + '€';
            
            priceRange.addEventListener('input', (e) => {
                maxPriceDisplay.textContent = e.target.value + '€';
                applyFilters();
            });
        }

        // Vérifier l'URL pour pré-remplir
        const urlParams = new URLSearchParams(window.location.search);
        const categoryId = urlParams.get('category');
        if (categoryId) {
            const checkbox = document.getElementById(`cat-${categoryId}`);
            if (checkbox) checkbox.checked = true;
        }

        // Événements
        document.querySelectorAll('[data-filter]').forEach(el => {
            el.addEventListener('change', applyFilters);
        });

        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => applyFilters(), 300);
            });
        }

        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) {
            sortSelect.addEventListener('change', () => {
                renderProducts(filteredProducts, document.getElementById('gridView').classList.contains('active') === false);
            });
        }

        // View toggle
        const gridView = document.getElementById('gridView');
        const listView = document.getElementById('listView');
        
        if (gridView) {
            gridView.addEventListener('click', () => {
                gridView.classList.add('active');
                listView.classList.remove('active');
                renderProducts(filteredProducts, false);
            });
        }
        
        if (listView) {
            listView.addEventListener('click', () => {
                listView.classList.add('active');
                gridView.classList.remove('active');
                renderProducts(filteredProducts, true);
            });
        }

        // Sidebar toggle mobile
        const filterToggle = document.getElementById('filterToggle');
        const sidebar = document.getElementById('productsSidebar');
        const sidebarClose = document.getElementById('sidebarClose');
        
        if (filterToggle && sidebar) {
            filterToggle.addEventListener('click', () => {
                sidebar.classList.add('active');
            });
        }
        
        if (sidebarClose && sidebar) {
            sidebarClose.addEventListener('click', () => {
                sidebar.classList.remove('active');
            });
        }

        // Reset filters
        const resetBtn = document.getElementById('resetFilters');
        if (resetBtn) {
            resetBtn.addEventListener('click', resetAllFilters);
        }

        // Appliquer les filtres initiaux
        await applyFilters();
    } catch (error) {
        console.error('Erreur lors de l\'initialisation des filtres:', error);
    }
}

async function applyFilters() {
    const searchInput = document.getElementById('searchInput');
    const priceRange = document.getElementById('priceRange');
    
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const maxPrice = priceRange ? parseFloat(priceRange.value) : Infinity;
    
    // Récupérer les catégories sélectionnées
    const selectedCategories = Array.from(document.querySelectorAll('#categoryFilters input[type="checkbox"]:checked'))
        .map(cb => parseInt(cb.value));
    
    // Récupérer les couleurs sélectionnées
    const selectedFabricColors = Array.from(document.querySelectorAll('#fabricFilters input[type="checkbox"]:checked'))
        .map(cb => cb.value);
    
    const selectedWoodColors = Array.from(document.querySelectorAll('#woodFilters input[type="checkbox"]:checked'))
        .map(cb => cb.value);

    let filtered = allProductsData.filter(product => {
        // Recherche textuelle
        if (searchTerm && !product.name.toLowerCase().includes(searchTerm) && 
            !product.description?.toLowerCase().includes(searchTerm)) {
            return false;
        }
        
        // Catégorie
        if (selectedCategories.length > 0 && !selectedCategories.includes(product.category_id)) {
            return false;
        }
        
        // Prix
        if (product.price > maxPrice) {
            return false;
        }
        
        return true;
    });

    // Filtres de couleurs (nécessitent de charger les détails)
    if (selectedFabricColors.length > 0 || selectedWoodColors.length > 0) {
        const filteredWithColors = [];
        for (const product of filtered) {
            try {
                const fullProduct = await ProductsAPI.getById(product.id);
                if (selectedFabricColors.length > 0 && 
                    (!fullProduct.fabricColors || !fullProduct.fabricColors.some(c => selectedFabricColors.includes(c.name)))) {
                    continue;
                }
                if (selectedWoodColors.length > 0 && 
                    (!fullProduct.woodColors || !fullProduct.woodColors.some(c => selectedWoodColors.includes(c.name)))) {
                    continue;
                }
                filteredWithColors.push(product);
            } catch (e) {
                // Ignorer les erreurs
            }
        }
        filtered = filteredWithColors;
    }

    const isListView = document.getElementById('listView')?.classList.contains('active') || false;
    await renderProducts(filtered, isListView);
}

function resetAllFilters() {
    const searchInput = document.getElementById('searchInput');
    const priceRange = document.getElementById('priceRange');
    const maxPriceDisplay = document.getElementById('maxPriceDisplay');
    
    if (searchInput) searchInput.value = '';
    if (priceRange) {
        priceRange.value = priceRange.max;
        if (maxPriceDisplay) maxPriceDisplay.textContent = priceRange.max + '€';
    }
    
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    
    applyFilters();
}

async function loadRelatedProducts(categoryId, excludeId) {
    const container = document.getElementById('relatedProducts');
    if (!container) return;

    try {
        const products = await ProductsAPI.getAll({ category_id: categoryId });
        const related = products.filter(p => p.id !== excludeId).slice(0, 4);
        
        if (related.length === 0) {
            container.parentElement.style.display = 'none';
            return;
        }

        container.innerHTML = related.map(product => {
            const stockStatus = getStockStatus(product.quantity);
            return `
                <div class="product-card" data-product-id="${product.id}">
                    <div class="product-card-image-wrapper">
                        ${product.featured ? '<div class="product-badge">En vedette</div>' : ''}
                        <img src="${product.main_image || product.images?.[0] || 'https://via.placeholder.com/400x300?text=' + encodeURIComponent(product.name)}" 
                             alt="${product.name}" class="product-card-image">
                    </div>
                    <div class="product-card-content">
                        <div class="product-card-category">${product.category_name || ''}</div>
                        <h3>${product.name}</h3>
                        <div class="product-card-price">${formatPrice(product.price)}</div>
                        <div class="product-card-stock ${stockStatus.class}">${stockStatus.text}</div>
                        <a href="product.html?id=${product.id}" class="btn-secondary">Voir le produit</a>
                    </div>
                </div>
            `;
        }).join('');
    } catch (error) {
        console.error('Erreur lors du chargement des produits similaires:', error);
        container.parentElement.style.display = 'none';
    }
}

// ===== PAGE DÉTAIL PRODUIT =====

async function renderProductDetail() {
    const productId = getProductIdFromURL();
    if (!productId) {
        window.location.href = 'products.html';
        return;
    }

    try {
        const product = await ProductsAPI.getById(productId);
        if (!product) {
            window.location.href = 'products.html';
            return;
        }

        const container = document.getElementById('productDetail');
        if (!container) return;

        // Mettre à jour le breadcrumb
        const breadcrumbName = document.getElementById('breadcrumbProductName');
        if (breadcrumbName) {
            breadcrumbName.textContent = product.name;
        }

        const images = product.images && product.images.length > 0 ? product.images : ['https://via.placeholder.com/600x500?text=' + encodeURIComponent(product.name)];
        const stockStatus = getStockStatus(product.quantity);

        container.innerHTML = `
            <div class="product-detail-premium">
                <div class="product-gallery-premium">
                    <img src="${images[0]}" alt="${product.name}" class="product-main-image-premium" id="mainImage">
                    ${images.length > 1 ? `
                        <div class="product-thumbnails-premium">
                            ${images.map((img, index) => `
                                <img src="${img}" alt="${product.name}" class="product-thumbnail-premium ${index === 0 ? 'active' : ''}"
                                     data-image-index="${index}">
                            `).join('')}
                        </div>
                    ` : ''}
                </div>
                <div class="product-info-premium">
                    ${product.category_name ? `<div class="product-category-badge">${product.category_name}</div>` : ''}
                    <h1>${product.name}</h1>
                    <div class="product-price-premium">${formatPrice(product.price)}</div>
                    <div class="product-description-premium">${product.description || ''}</div>
                    
                    <div class="stock-info-premium ${stockStatus.class}">
                        ${stockStatus.text}
                    </div>
                    
                    ${product.dimensions && product.dimensions.length > 0 ? `
                        <div class="product-specs-premium">
                            <h3>Dimensions</h3>
                            <ul>
                                ${product.dimensions.map(dim => `
                                    <li><strong>${dim.label}:</strong> ${dim.value}</li>
                                `).join('')}
                            </ul>
                        </div>
                    ` : ''}

                    ${product.fabricColors && product.fabricColors.length > 0 ? `
                        <div class="product-options-premium">
                            <div class="option-group-premium">
                                <label>Couleur du Tissu</label>
                                <div class="color-selector-premium">
                                    ${product.fabricColors.map((color, index) => `
                                        <div class="color-option-premium ${index === 0 ? 'selected' : ''}" 
                                             style="background-color: ${color.value}"
                                             data-color-name="${color.name}"
                                             title="${color.name}"></div>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    ` : ''}

                    ${product.woodColors && product.woodColors.length > 0 ? `
                        <div class="product-options-premium">
                            <div class="option-group-premium">
                                <label>Couleur du Bois</label>
                                <div class="color-selector-premium">
                                    ${product.woodColors.map((color, index) => `
                                        <div class="color-option-premium ${index === 0 ? 'selected' : ''}" 
                                             style="background-color: ${color.value}"
                                             data-color-name="${color.name}"
                                             title="${color.name}"></div>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    ` : ''}

                    <div class="quantity-selector-premium">
                        <button type="button" id="decreaseQty">-</button>
                        <input type="number" id="productQuantity" value="1" min="1" max="${product.quantity || 0}">
                        <button type="button" id="increaseQty">+</button>
                    </div>

                    <button class="btn-add-cart-premium" data-product-id="${product.id}">Ajouter au panier</button>
                </div>
            </div>
        `;

        // Gestion de la galerie d'images
        const thumbnails = container.querySelectorAll('.product-thumbnail-premium, .product-thumbnail');
        const mainImage = document.getElementById('mainImage');

        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', () => {
                thumbnails.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
                if (mainImage) {
                    mainImage.src = thumb.src;
                    // Animation de fade
                    mainImage.style.opacity = '0';
                    setTimeout(() => {
                        mainImage.style.opacity = '1';
                    }, 200);
                }
            });
        });

        // Gestion de la quantité
        const qtyInput = document.getElementById('productQuantity');
        const decreaseBtn = document.getElementById('decreaseQty');
        const increaseBtn = document.getElementById('increaseQty');

        if (decreaseBtn && qtyInput) {
            decreaseBtn.addEventListener('click', () => {
                const current = parseInt(qtyInput.value) || 1;
                if (current > 1) {
                    qtyInput.value = current - 1;
                }
            });
        }

        if (increaseBtn && qtyInput) {
            increaseBtn.addEventListener('click', () => {
                const current = parseInt(qtyInput.value) || 1;
                const max = parseInt(qtyInput.max) || product.quantity;
                if (current < max) {
                    qtyInput.value = current + 1;
                }
            });
        }

        // Gestion du bouton ajouter au panier
        const addCartBtn = container.querySelector('.btn-add-cart-premium, .btn-add-cart');
        if (addCartBtn) {
            addCartBtn.addEventListener('click', () => {
                const quantity = parseInt(document.getElementById('productQuantity').value) || 1;
                // TODO: Implémenter l'ajout au panier (sera connecté au backend)
                addCartBtn.textContent = 'Ajouté !';
                addCartBtn.style.background = 'var(--color-olive)';
                setTimeout(() => {
                    addCartBtn.textContent = 'Ajouter au panier';
                    addCartBtn.style.background = '';
                }, 2000);
            });
        }

        // Charger les produits similaires
        loadRelatedProducts(product.category_id, product.id);
    } catch (error) {
        const container = document.getElementById('productDetail');
        if (container) {
            container.innerHTML = '<p style="text-align: center; color: var(--color-olive);">Erreur lors du chargement du produit.</p>';
        }
        console.error('Erreur:', error);
    }
}

// ===== INITIALISATION =====

// Header scroll effect
function initHeaderScroll() {
    const header = document.querySelector('.header');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
}

// ===== DIAPORAMA HERO =====

function initHeroSlider() {
    const slides = document.querySelectorAll('.hero-slide');
    if (slides.length === 0) return;

    let currentSlide = 0;
    const totalSlides = slides.length;

    function showSlide(index) {
        // Retirer la classe active de toutes les slides
        slides.forEach(slide => slide.classList.remove('active'));
        
        // Ajouter la classe active à la slide actuelle
        slides[index].classList.add('active');
        
        // Mettre à jour l'index de la slide actuelle
        currentSlide = index;
    }

    // Fonction pour passer à la slide suivante
    function nextSlide() {
        showSlide((currentSlide + 1) % totalSlides);
    }

    // Démarrer le diaporama (changement toutes les 5 secondes)
    let slideInterval = setInterval(nextSlide, 5000);

    // Gestion du survol pour arrêter le diaporama
    const heroSection = document.querySelector('.hero-premium');
    if (heroSection) {
        heroSection.addEventListener('mouseenter', () => {
            clearInterval(slideInterval);
        });

        heroSection.addEventListener('mouseleave', () => {
            slideInterval = setInterval(nextSlide, 5000);
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    initScrollAnimations();
    initHeaderScroll();
    initHeroSlider();
    initHeroSlider();

    // Page d'accueil
    if (document.getElementById('categoriesGrid')) {
        renderCategories();
    }
    if (document.getElementById('featuredProducts')) {
        renderFeaturedProducts();
    }

    // Page produits
    if (document.getElementById('productsGrid')) {
        initFilters();
    }

    // Page détail produit
    if (document.getElementById('productDetail')) {
        renderProductDetail();
    }
});

