# Ama Meuble - Site E-Commerce Premium

Site e-commerce haut de gamme pour mobilier en bois et tissu, développé avec HTML5, CSS3 et JavaScript Vanilla.

## 🎯 Caractéristiques

- **Design Premium** : Interface élégante et moderne inspirée du bois naturel et du tissu
- **100% Responsive** : Adapté à tous les écrans (mobile, tablette, desktop)
- **Animations Fluides** : Transitions et animations au scroll
- **Prêt pour Backend** : Structure préparée pour intégration PHP/MySQL

## 📂 Structure du Projet

```
/
├── index.html              # Page d'accueil
├── products.html           # Liste des produits avec filtres
├── product.html            # Détail d'un produit
├── admin/
│   ├── dashboard.html      # Tableau de bord admin
│   ├── products.html       # Gestion des produits
│   ├── categories.html     # Gestion des catégories
│   └── stats.html          # Statistiques
├── assets/
│   ├── css/
│   │   ├── main.css        # Styles principaux
│   │   └── admin.css       # Styles admin
│   ├── js/
│   │   ├── main.js         # JavaScript client
│   │   └── admin.js        # JavaScript admin
│   └── images/             # Images du site
└── README.md
```

## 🚀 Utilisation

1. **Ouvrir le site** : Ouvrez `index.html` dans votre navigateur
2. **Navigation** : 
   - Accueil : `index.html`
   - Produits : `products.html`
   - Détail produit : `product.html?id=1`
   - Admin : `admin/dashboard.html`

## 🎨 Palette de Couleurs

- **Bois** : `#8B6F47` (marron bois)
- **Beige** : `#F5F1E8` (beige clair)
- **Olive** : `#7A8471` (vert olive)
- **Noir doux** : `#2C2C2C`

## 📱 Fonctionnalités

### Partie Client
- ✅ Page d'accueil avec hero section
- ✅ Affichage des catégories
- ✅ Produits en vedette
- ✅ Liste des produits avec filtres dynamiques
- ✅ Page détail produit avec galerie
- ✅ Sélection de couleurs (tissu/bois)
- ✅ Gestion de la quantité
- ✅ Menu mobile responsive

### Partie Admin
- ✅ Dashboard avec statistiques
- ✅ Gestion des produits (CRUD)
- ✅ Gestion des catégories (CRUD)
- ✅ Graphiques statistiques
- ✅ Sidebar responsive
- ✅ Formulaires complets

## 🔧 Technologies

- **HTML5** : Structure sémantique
- **CSS3** : Flexbox, Grid, Animations
- **JavaScript Vanilla** : Aucun framework
- **LocalStorage** : Stockage temporaire des données admin

## 📝 Notes pour l'Intégration Backend

Le code est préparé pour être connecté à PHP/MySQL :

- Les données sont mockées dans `main.js` et `admin.js`
- Les attributs `data-*` sont prêts pour les requêtes AJAX
- La structure des données est cohérente avec une base de données
- Les formulaires sont prêts à être soumis via POST

### Exemple de Structure MySQL

```sql
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    category VARCHAR(100),
    price DECIMAL(10,2),
    quantity INT,
    description TEXT,
    images JSON,
    dimensions JSON,
    fabric_colors JSON,
    wood_colors JSON,
    featured BOOLEAN
);

CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    description TEXT
);
```

## 🎯 Prochaines Étapes

1. Connecter les données à une API PHP
2. Implémenter l'upload d'images
3. Ajouter un système de panier complet
4. Intégrer un système de paiement
5. Ajouter l'authentification admin

## 📄 Licence

Projet développé pour Ama Meuble.

