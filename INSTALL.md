# Installation - Ama Meuble

## Prérequis

- WAMP/XAMPP/MAMP avec PHP 7.4+
- MySQL 5.7+
- Apache avec mod_rewrite activé

## Installation

### 1. Base de données

1. Ouvrez phpMyAdmin (http://localhost/phpmyadmin)
2. Importez le fichier `database/schema.sql`
3. Vérifiez que la base `ama_meuble` a été créée

### 2. Configuration

Éditez le fichier `config/database.php` et modifiez les constantes si nécessaire :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ama_meuble');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 3. Permissions

Assurez-vous que le dossier `assets/images/` est accessible en écriture pour l'upload d'images.

### 4. Test

1. Ouvrez http://localhost/new-ama1/
2. Vérifiez que la page d'accueil s'affiche
3. Testez l'admin : http://localhost/new-ama1/admin/dashboard.html

## Utilisateur Admin par défaut

- Username: `admin`
- Password: `admin123` (à changer en production !)

## Problèmes courants

### Erreur de connexion à la base de données
- Vérifiez les identifiants dans `config/database.php`
- Vérifiez que MySQL est démarré

### Erreur 404 sur les API
- Vérifiez que mod_rewrite est activé dans Apache
- Vérifiez le fichier `.htaccess`

### Images non affichées
- Vérifiez que le dossier `assets/images/` existe
- Ajoutez des images de test si nécessaire

