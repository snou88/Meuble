-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 16 jan. 2026 à 16:59
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ama`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `image_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image_path`, `created_at`) VALUES
(1, 'Salon', 'salon', 'Canapés & Tables basses', 'images/cat_695f304b1a036.jpg', '2026-01-08 02:19:23'),
(2, 'Chambre', 'chambre', 'Lits & Dressings', 'images/cat_695f3a1b620cb.jpg', '2026-01-08 03:01:15'),
(3, 'Salle à manger', 'salle-a-manger', 'Tables & Chaises', 'images/cat_695f3a2d29d38.webp', '2026-01-08 03:01:33'),
(4, 'Bureau', 'bureau', 'Bureaux & Rangements', 'images/cat_695f3a3ac5e77.webp', '2026-01-08 03:01:46');

-- --------------------------------------------------------

--
-- Structure de la table `customer_messages`
--

DROP TABLE IF EXISTS `customer_messages`;
CREATE TABLE IF NOT EXISTS `customer_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `client_email` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_phone` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `comment` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `customer_messages`
--

INSERT INTO `customer_messages` (`id`, `client_name`, `client_email`, `client_phone`, `comment`, `created_at`, `is_read`) VALUES
(6, 't-shirt', 'admin@estilo.com', '0540432265', 'dddddddddddddddddddd', '2026-01-16 15:22:07', 1),
(7, 'chaisse', 'admin@estilo.com', '0540432265', 'aa', '2026-01-16 15:28:15', 1);

-- --------------------------------------------------------

--
-- Structure de la table `material_color_catalogs`
--

DROP TABLE IF EXISTS `material_color_catalogs`;
CREATE TABLE IF NOT EXISTS `material_color_catalogs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `material_type` enum('tissu','bois') COLLATE utf8mb4_general_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `material_color_catalogs`
--

INSERT INTO `material_color_catalogs` (`id`, `product_id`, `material_type`, `image_path`, `description`, `created_at`) VALUES
(3, 3, 'tissu', 'images/mat_695f395936771.jpg', '', '2026-01-08 03:58:01'),
(4, 3, 'bois', 'images/mat_695f39596fd8f.jpg', '', '2026-01-08 03:58:01');

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_email` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customer_address` text COLLATE utf8mb4_general_ci NOT NULL,
  `commune` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wilaya_id` int DEFAULT NULL,
  `delivery_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `delivery_price` decimal(10,2) DEFAULT '0.00',
  `status` varchar(30) COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_seen` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `customer_phone`, `customer_email`, `customer_address`, `commune`, `wilaya_id`, `delivery_type`, `total_price`, `delivery_price`, `status`, `created_at`, `is_seen`) VALUES
(2, 'ahmed senouci', '0562548210', NULL, '23rue suidani boudjemaa', 'cheraga', 44, 'standard', 14700.00, 700.00, 'archived', '2026-01-16 11:55:18', 1),
(3, 'ahmed senouci', '0562548210', NULL, '23rue suidani boudjemaa', 'cheraga', 44, 'standard', 15700.00, 700.00, 'delivered', '2026-01-16 12:19:35', 1),
(4, 'ahmed senouci', '0540432265', NULL, '23rue suidani boudjemaa', 'cheraga', 44, 'standard', 15700.00, 700.00, 'delivered', '2026-01-16 12:22:55', 1),
(5, 'ahmed aa', '0562548210', NULL, '23rue suidani boudjemaa', 'cheraga', 46, 'standard', 66800.00, 800.00, 'delivered', '2026-01-16 12:23:38', 1),
(6, 'ahmed senouci', '0540432265', NULL, '23rue suidani boudjemaa', 'cheraga', 1, 'standard', 16200.00, 1200.00, 'delivered', '2026-01-16 12:37:13', 1),
(7, 'ahmed senouci', '0562548210', NULL, '23rue suidani boudjemaa', 'cheraga', 59, 'standard', 15800.00, 800.00, 'delivered', '2026-01-16 12:39:43', 1),
(8, 'ahmed senouci', '0540432266', NULL, '23rue suidani boudjemaa', 'cheraga', 16, 'standard', 15600.00, 600.00, 'delivered', '2026-01-16 12:41:17', 1),
(9, 'ahmed senouci', '0540432265', NULL, '23rue suidani boudjemaa', 'cheraga', 59, 'standard', 7800.00, 800.00, 'archived', '2026-01-16 12:47:03', 1),
(10, 'ahmed senouci', '0562548210', NULL, '23rue suidani boudjemaa', 'cheraga', 44, 'standard', 7700.00, 700.00, 'delivered', '2026-01-16 16:13:26', 1);

-- --------------------------------------------------------

--
-- Structure de la table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `dimension_id` int NOT NULL,
  `tissu_color_id` int DEFAULT NULL,
  `bois_color_id` int DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `total_price` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `dimension_id`, `tissu_color_id`, `bois_color_id`, `unit_price`, `quantity`, `total_price`) VALUES
(1, 0, 8, 21, NULL, NULL, 15000.00, 1, 15000.00),
(2, 2, 6, 14, 15, NULL, 7000.00, 2, 14000.00),
(3, 3, 8, 21, NULL, NULL, 15000.00, 1, 15000.00),
(4, 4, 8, 21, NULL, NULL, 15000.00, 1, 15000.00),
(5, 5, 8, 22, NULL, NULL, 22000.00, 3, 66000.00),
(6, 6, 8, 21, NULL, NULL, 15000.00, 1, 15000.00),
(7, 7, 8, 21, NULL, NULL, 15000.00, 1, 15000.00),
(8, 8, 8, 21, NULL, NULL, 15000.00, 1, 15000.00),
(9, 9, 6, 14, 15, NULL, 7000.00, 1, 7000.00),
(10, 10, 6, 14, 15, NULL, 7000.00, 1, 7000.00);

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `category_id` int DEFAULT NULL,
  `product_type` enum('made_to_order','available') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'made_to_order',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `category_id`, `product_type`, `created_at`) VALUES
(3, 'pouffe', 'pouffe exemple', 1, 'available', '2026-01-08 03:58:00'),
(4, 'Canapé', 'Canapé 3 places tissu moderne', 1, 'made_to_order', '2026-01-08 05:12:30'),
(5, 'Table marbre', 'Plateau en marbre - Tiroirs en rotin - Noir et or', 1, 'made_to_order', '2026-01-08 05:15:36'),
(6, 'Chaises', 'Chaise en tissu coloré avec poignée, métal, Tissu', 3, 'made_to_order', '2026-01-08 05:19:55'),
(8, 'Table à manger', 'Table salle à manger bois massif pied mikado', 3, 'available', '2026-01-08 05:28:04'),
(15, 'chaisse', 'aaaaaaaaaaaaaa', 2, 'made_to_order', '2026-01-16 16:34:30');

-- --------------------------------------------------------

--
-- Structure de la table `product_colors`
--

DROP TABLE IF EXISTS `product_colors`;
CREATE TABLE IF NOT EXISTS `product_colors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `type` enum('tissu','bois') COLLATE utf8mb4_general_ci NOT NULL,
  `color_name` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `color_code` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price_modifier` decimal(10,2) DEFAULT '0.00',
  `image_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `product_colors`
--

INSERT INTO `product_colors` (`id`, `product_id`, `type`, `color_name`, `color_code`, `price_modifier`, `image_path`, `created_at`) VALUES
(1, 1, 'tissu', 'teste', '#ff0000', 0.00, '', '2026-01-08 03:21:38'),
(2, 1, 'tissu', 'test2', '#00ff62', 0.00, '', '2026-01-08 03:21:38'),
(3, 1, 'bois', 'teste3', '#ff1a75', 0.00, '', '2026-01-08 03:21:38'),
(4, 1, 'bois', 'test4', '#4de1ff', 0.00, '', '2026-01-08 03:21:38'),
(5, 2, 'tissu', 'blanc', '#e8e8e8', 0.00, '', '2026-01-08 03:42:05'),
(6, 2, 'tissu', 'grey', '#696969', 0.00, '', '2026-01-08 03:42:05'),
(7, 2, 'bois', 'marron', '#885a3f', 0.00, '', '2026-01-08 03:42:05'),
(8, 2, 'tissu', 'blanc', '#c2b79a', 0.00, '', '2026-01-08 03:42:05'),
(13, 4, 'bois', 'blanc', '#ccae84', 0.00, '', '2026-01-08 05:12:31'),
(14, 4, 'tissu', 'gris', '#b4b1ab', 0.00, '', '2026-01-08 05:12:31'),
(15, 6, 'tissu', 'bleu', '#4d658b', 0.00, '', '2026-01-08 05:19:56'),
(16, 6, 'tissu', 'vert', '#66563c', 0.00, '', '2026-01-08 05:19:56'),
(17, 6, 'tissu', 'gris', '#4c4a4d', 0.00, '', '2026-01-08 05:19:56'),
(18, 6, 'tissu', 'pink', '#c6b2a8', 0.00, '', '2026-01-08 05:19:56'),
(19, 3, 'tissu', '1', '#e2e1d9', 0.00, '', '2026-01-16 16:21:42'),
(20, 3, 'tissu', '2', '#9a9292', 0.00, '', '2026-01-16 16:21:42'),
(21, 3, 'bois', '1', '#9f6947', 0.00, '', '2026-01-16 16:21:42'),
(22, 3, 'bois', '2', '#decd9f', 0.00, '', '2026-01-16 16:21:42'),
(23, 0, 'tissu', '1', '#c20000', 0.00, '', '2026-01-16 16:31:26'),
(24, 15, 'tissu', '1', '#fa0000', 0.00, '', '2026-01-16 16:34:30');

-- --------------------------------------------------------

--
-- Structure de la table `product_dimensions`
--

DROP TABLE IF EXISTS `product_dimensions`;
CREATE TABLE IF NOT EXISTS `product_dimensions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `width_cm` int DEFAULT NULL,
  `height_cm` int DEFAULT NULL,
  `depth_cm` int DEFAULT NULL,
  `label` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `price_new` decimal(10,2) DEFAULT NULL,
  `promo_percent` tinyint UNSIGNED DEFAULT '0',
  `stock` int NOT NULL DEFAULT '0',
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `product_dimensions`
--

INSERT INTO `product_dimensions` (`id`, `product_id`, `width_cm`, `height_cm`, `depth_cm`, `label`, `price`, `price_new`, `promo_percent`, `stock`, `is_default`, `created_at`) VALUES
(1, 1, 140, 150, 100, '123x223x15', 5000.00, 4000.00, 10, 20, 0, '2026-01-08 03:21:37'),
(8, 2, 50, 30, 30, '50x30x30', 5000.00, NULL, 0, 10, 0, '2026-01-08 03:54:54'),
(9, 2, 80, 30, 30, '80x30x30', 7000.00, NULL, 0, 20, 0, '2026-01-08 03:54:54'),
(12, 4, 111, 212, NULL, '111x212', 70000.00, NULL, 0, 9999999, 0, '2026-01-08 05:12:30'),
(13, 5, 70, 120, NULL, '120x70', 15000.00, 12000.00, 15, 10, 0, '2026-01-08 05:15:36'),
(14, 6, 46, 59, 92, '46x59x92', 7000.00, NULL, 0, 14, 0, '2026-01-08 05:19:55'),
(18, 7, 200, 0, NULL, '200cm', 15000.00, 12000.00, 15, 30, 0, '2026-01-08 05:26:32'),
(19, 7, 240, 0, NULL, '240cm', 22000.00, 16000.00, 20, 20, 0, '2026-01-08 05:26:32'),
(20, 7, 340, 0, NULL, '340cm', 35000.00, 28000.00, 28, 5, 0, '2026-01-08 05:26:32'),
(21, 8, 200, 0, NULL, '200cm', 15000.00, 12000.00, 15, 25, 0, '2026-01-08 05:28:05'),
(22, 8, 240, 0, NULL, '240cm', 22000.00, 16000.00, 20, 17, 0, '2026-01-08 05:28:05'),
(23, 8, 340, 0, NULL, '340cm', 35000.00, 28000.00, 28, 5, 0, '2026-01-08 05:28:05'),
(24, 3, 50, 30, 30, '50x30x30', 5000.00, NULL, 0, 10, 0, '2026-01-16 16:21:42'),
(25, 3, 80, 30, 30, '80x30x30', 7000.00, NULL, 0, 20, 0, '2026-01-16 16:21:42'),
(26, 0, 0, 0, NULL, '60x150cm', 5000.00, 7000.00, 15, 9999999, 0, '2026-01-16 16:31:26'),
(27, 15, 0, 0, NULL, '60x150cm', 7000.00, 5000.00, 0, 9999999, 0, '2026-01-16 16:34:30');

-- --------------------------------------------------------

--
-- Structure de la table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `created_at`) VALUES
(1, 1, 'images/prod_695f30d1f2b10.png', '2026-01-08 03:21:37'),
(2, 1, 'images/prod_695f30d20b3e6.png', '2026-01-08 03:21:38'),
(3, 1, 'images/prod_695f30d212566.png', '2026-01-08 03:21:38'),
(6, 3, 'images/prod_695f3958e26a5.jpg', '2026-01-08 03:58:00'),
(7, 3, 'images/prod_695f3958ec6a1.jpg', '2026-01-08 03:58:00'),
(8, 4, 'images/prod_695f4acf01007.webp', '2026-01-08 05:12:31'),
(9, 5, 'images/prod_695f4b89114d2.jpg', '2026-01-08 05:15:37'),
(10, 5, 'images/prod_695f4b89294f4.webp', '2026-01-08 05:15:37'),
(11, 6, 'images/prod_695f4c8c013d1.jpg', '2026-01-08 05:19:56'),
(12, 6, 'images/prod_695f4c8c09b8f.jpg', '2026-01-08 05:19:56'),
(13, 6, 'images/prod_695f4c8c1625e.jpg', '2026-01-08 05:19:56'),
(14, 8, 'images/prod_695f4e751f1f5.jpg', '2026-01-08 05:28:05'),
(15, 8, 'images/prod_695f4e752df26.jpg', '2026-01-08 05:28:05'),
(16, 8, 'images/prod_695f4e7537e68.jpg', '2026-01-08 05:28:05'),
(17, 0, 'images/prod_696a67de0c70c.jpg', '2026-01-16 16:31:26'),
(18, 15, 'images/prod_696a689624866.jpg', '2026-01-16 16:34:30');

-- --------------------------------------------------------

--
-- Structure de la table `wilayas`
--

DROP TABLE IF EXISTS `wilayas`;
CREATE TABLE IF NOT EXISTS `wilayas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `domicile_price` int NOT NULL DEFAULT '0',
  `stopdesk_price` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `wilayas`
--

INSERT INTO `wilayas` (`id`, `name`, `domicile_price`, `stopdesk_price`, `is_active`) VALUES
(1, 'Adrar', 1200, 900, 1),
(2, 'Chlef', 800, 600, 1),
(3, 'Laghouat', 1000, 700, 1),
(4, 'Oum El Bouaghi', 800, 600, 1),
(5, 'Batna', 800, 600, 1),
(6, 'Béjaïa', 800, 600, 1),
(7, 'Biskra', 900, 650, 1),
(8, 'Béchar', 1200, 900, 1),
(9, 'Blida', 600, 400, 1),
(10, 'Bouira', 700, 500, 1),
(11, 'Tamanrasset', 1500, 1200, 1),
(12, 'Tébessa', 900, 650, 1),
(13, 'Tlemcen', 900, 650, 1),
(14, 'Tiaret', 900, 650, 1),
(15, 'Tizi Ouzou', 700, 500, 1),
(16, 'Alger', 600, 400, 1),
(17, 'Djelfa', 900, 650, 1),
(18, 'Jijel', 800, 600, 1),
(19, 'Sétif', 800, 600, 1),
(20, 'Saïda', 900, 650, 1),
(21, 'Skikda', 800, 600, 1),
(22, 'Sidi Bel Abbès', 900, 650, 1),
(23, 'Annaba', 800, 600, 1),
(24, 'Guelma', 800, 600, 1),
(25, 'Constantine', 800, 600, 1),
(26, 'Médéa', 700, 500, 1),
(27, 'Mostaganem', 800, 600, 1),
(28, 'M’Sila', 900, 650, 1),
(29, 'Mascara', 900, 650, 1),
(30, 'Ouargla', 1200, 900, 1),
(31, 'Oran', 800, 600, 1),
(32, 'El Bayadh', 1100, 850, 1),
(33, 'Illizi', 1500, 1200, 1),
(34, 'Bordj Bou Arréridj', 800, 600, 1),
(35, 'Boumerdès', 600, 400, 1),
(36, 'El Tarf', 800, 600, 1),
(37, 'Tindouf', 1500, 1200, 1),
(38, 'Tissemsilt', 900, 650, 1),
(39, 'El Oued', 1000, 750, 1),
(40, 'Khenchela', 900, 650, 1),
(41, 'Souk Ahras', 900, 650, 1),
(42, 'Tipaza', 600, 400, 1),
(43, 'Mila', 800, 600, 1),
(44, 'Aïn Defla', 700, 500, 1),
(45, 'Naâma', 1100, 850, 1),
(46, 'Aïn Témouchent', 800, 600, 1),
(47, 'Ghardaïa', 1100, 850, 1),
(48, 'Relizane', 800, 600, 1),
(49, 'El M’Ghair', 1000, 750, 1),
(50, 'El Meniaa', 1100, 850, 1),
(51, 'Ouled Djellal', 900, 650, 1),
(52, 'Bordj Badji Mokhtar', 1500, 1200, 1),
(53, 'Béni Abbès', 1200, 900, 1),
(54, 'Timimoun', 1200, 900, 1),
(55, 'Touggourt', 1000, 750, 1),
(56, 'Djanet', 1500, 1200, 1),
(57, 'In Salah', 1300, 1000, 1),
(58, 'In Guezzam', 1500, 1200, 1),
(59, 'Aflou', 800, 600, 1),
(60, 'Barika', 800, 600, 1),
(61, 'Ksar Chellala', 800, 600, 1),
(62, 'Messaad', 800, 600, 1),
(63, 'Aïn Oussara', 800, 600, 1),
(64, 'Bou Saâda', 800, 600, 1),
(65, 'El Abiodh Sidi Cheikh', 800, 600, 1),
(66, 'El Kantara', 800, 600, 1),
(67, 'Bir El Ater', 800, 600, 1),
(68, 'Ksar El Boukhari', 800, 600, 1),
(69, 'El Aricha', 800, 600, 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
