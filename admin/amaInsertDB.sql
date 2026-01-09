--
-- Database: `ama`
--

-- --------------------------------------------------------

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE `customer_messages` (
  `id` int(11) NOT NULL,
  `client_name` varchar(150) NOT NULL,
  `client_email` varchar(150) DEFAULT NULL,
  `client_phone` varchar(30) DEFAULT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE `material_color_catalogs` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `material_type` enum('tissu','bois') NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `customer_address` text NOT NULL,
  `commune` varchar(100) DEFAULT NULL,
  `wilaya_id` int(11) DEFAULT NULL,
  `delivery_type` varchar(50) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `delivery_price` decimal(10,2) DEFAULT 0.00,
  `status` varchar(30) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `dimension_id` int(11) NOT NULL,
  `tissu_color_id` int(11) DEFAULT NULL,
  `bois_color_id` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `total_price` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_type` enum('made_to_order','available') NOT NULL DEFAULT 'made_to_order',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE `product_colors` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `type` enum('tissu','bois') NOT NULL,
  `color_name` varchar(80) NOT NULL,
  `color_code` varchar(20) DEFAULT NULL,
  `price_modifier` decimal(10,2) DEFAULT 0.00,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE `product_dimensions` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `width_cm` int(11) DEFAULT NULL,
  `height_cm` int(11) DEFAULT NULL,
  `depth_cm` int(11) DEFAULT NULL,
  `label` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `price_new` decimal(10,2) DEFAULT NULL,
  `promo_percent` tinyint(3) UNSIGNED DEFAULT 0,
  `stock` int(11) NOT NULL DEFAULT 0,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE `wilayas` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `domicile_price` int(11) NOT NULL DEFAULT 0,
  `stopdesk_price` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for tables
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image_path`, `created_at`) VALUES
(1, 'Salon', 'salon', 'Canapés & Tables basses', 'images/cat_695f304b1a036.jpg', '2026-01-08 03:19:23'),
(2, 'Chambre', 'chambre', 'Lits & Dressings', 'images/cat_695f3a1b620cb.jpg', '2026-01-08 04:01:15'),
(3, 'Salle à manger', 'salle-a-manger', 'Tables & Chaises', 'images/cat_695f3a2d29d38.webp', '2026-01-08 04:01:33'),
(4, 'Bureau', 'bureau', 'Bureaux & Rangements', 'images/cat_695f3a3ac5e77.webp', '2026-01-08 04:01:46');

--

INSERT INTO `material_color_catalogs` (`id`, `product_id`, `material_type`, `image_path`, `description`, `created_at`) VALUES
(3, 3, 'tissu', 'images/mat_695f395936771.jpg', '', '2026-01-08 04:58:01'),
(4, 3, 'bois', 'images/mat_695f39596fd8f.jpg', '', '2026-01-08 04:58:01');


INSERT INTO `products` (`id`, `name`, `description`, `category_id`, `product_type`, `created_at`) VALUES
(3, 'pouffe', 'pouffe exemple', 1, 'available', '2026-01-08 04:58:00'),
(4, 'Canapé', 'Canapé 3 places tissu moderne', 1, 'made_to_order', '2026-01-08 06:12:30'),
(5, 'Table marbre', 'Plateau en marbre - Tiroirs en rotin - Noir et or', 1, 'made_to_order', '2026-01-08 06:15:36'),
(6, 'Chaises', 'Chaise en tissu coloré avec poignée, métal, Tissu', 3, 'made_to_order', '2026-01-08 06:19:55'),
(8, 'Table à manger', 'Table salle à manger bois massif pied mikado', 3, 'available', '2026-01-08 06:28:04');

INSERT INTO `product_colors` (`id`, `product_id`, `type`, `color_name`, `color_code`, `price_modifier`, `image_path`, `created_at`) VALUES
(1, 1, 'tissu', 'teste', '#ff0000', 0.00, '', '2026-01-08 04:21:38'),
(2, 1, 'tissu', 'test2', '#00ff62', 0.00, '', '2026-01-08 04:21:38'),
(3, 1, 'bois', 'teste3', '#ff1a75', 0.00, '', '2026-01-08 04:21:38'),
(4, 1, 'bois', 'test4', '#4de1ff', 0.00, '', '2026-01-08 04:21:38'),
(5, 2, 'tissu', 'blanc', '#e8e8e8', 0.00, '', '2026-01-08 04:42:05'),
(6, 2, 'tissu', 'grey', '#696969', 0.00, '', '2026-01-08 04:42:05'),
(7, 2, 'bois', 'marron', '#885a3f', 0.00, '', '2026-01-08 04:42:05'),
(8, 2, 'tissu', 'blanc', '#c2b79a', 0.00, '', '2026-01-08 04:42:05'),
(9, 3, 'tissu', 'blanc', '#e2e1d9', 0.00, '', '2026-01-08 04:58:01'),
(10, 3, 'tissu', 'gris', '#9a9292', 0.00, '', '2026-01-08 04:58:01'),
(11, 3, 'bois', 'marron', '#9f6947', 0.00, '', '2026-01-08 04:58:01'),
(12, 3, 'bois', 'jeune', '#decd9f', 0.00, '', '2026-01-08 04:58:01'),
(13, 4, 'bois', 'blanc', '#ccae84', 0.00, '', '2026-01-08 06:12:31'),
(14, 4, 'tissu', 'gris', '#b4b1ab', 0.00, '', '2026-01-08 06:12:31'),
(15, 6, 'tissu', 'bleu', '#4d658b', 0.00, '', '2026-01-08 06:19:56'),
(16, 6, 'tissu', 'vert', '#66563c', 0.00, '', '2026-01-08 06:19:56'),
(17, 6, 'tissu', 'gris', '#4c4a4d', 0.00, '', '2026-01-08 06:19:56'),
(18, 6, 'tissu', 'pink', '#c6b2a8', 0.00, '', '2026-01-08 06:19:56');


INSERT INTO `product_dimensions` (`id`, `product_id`, `width_cm`, `height_cm`, `depth_cm`, `label`, `price`, `price_new`, `promo_percent`, `stock`, `is_default`, `created_at`) VALUES
(1, 1, 140, 150, 100, '123x223x15', 5000.00, 4000.00, 10, 20, 0, '2026-01-08 04:21:37'),
(8, 2, 50, 30, 30, '50x30x30', 5000.00, NULL, 0, 10, 0, '2026-01-08 04:54:54'),
(9, 2, 80, 30, 30, '80x30x30', 7000.00, NULL, 0, 20, 0, '2026-01-08 04:54:54'),
(10, 3, 50, 30, 30, '50x30x30', 5000.00, NULL, 0, 10, 0, '2026-01-08 04:58:00'),
(11, 3, 80, 30, 30, '80x30x30', 7000.00, NULL, 0, 20, 0, '2026-01-08 04:58:00'),
(12, 4, 111, 212, NULL, '111x212', 70000.00, NULL, 0, 9999999, 0, '2026-01-08 06:12:30'),
(13, 5, 70, 120, NULL, '120x70', 15000.00, 12000.00, 15, 10, 0, '2026-01-08 06:15:36'),
(14, 6, 46, 59, 92, '46x59x92', 7000.00, NULL, 0, 20, 0, '2026-01-08 06:19:55'),
(18, 7, 200, 0, NULL, '200cm', 15000.00, 12000.00, 15, 30, 0, '2026-01-08 06:26:32'),
(19, 7, 240, 0, NULL, '240cm', 22000.00, 16000.00, 20, 20, 0, '2026-01-08 06:26:32'),
(20, 7, 340, 0, NULL, '340cm', 35000.00, 28000.00, 28, 5, 0, '2026-01-08 06:26:32'),
(21, 8, 200, 0, NULL, '200cm', 15000.00, 12000.00, 15, 30, 0, '2026-01-08 06:28:05'),
(22, 8, 240, 0, NULL, '240cm', 22000.00, 16000.00, 20, 20, 0, '2026-01-08 06:28:05'),
(23, 8, 340, 0, NULL, '340cm', 35000.00, 28000.00, 28, 5, 0, '2026-01-08 06:28:05');


INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `created_at`) VALUES
(1, 1, 'images/prod_695f30d1f2b10.png', '2026-01-08 04:21:37'),
(2, 1, 'images/prod_695f30d20b3e6.png', '2026-01-08 04:21:38'),
(3, 1, 'images/prod_695f30d212566.png', '2026-01-08 04:21:38'),
(6, 3, 'images/prod_695f3958e26a5.jpg', '2026-01-08 04:58:00'),
(7, 3, 'images/prod_695f3958ec6a1.jpg', '2026-01-08 04:58:00'),
(8, 4, 'images/prod_695f4acf01007.webp', '2026-01-08 06:12:31'),
(9, 5, 'images/prod_695f4b89114d2.jpg', '2026-01-08 06:15:37'),
(10, 5, 'images/prod_695f4b89294f4.webp', '2026-01-08 06:15:37'),
(11, 6, 'images/prod_695f4c8c013d1.jpg', '2026-01-08 06:19:56'),
(12, 6, 'images/prod_695f4c8c09b8f.jpg', '2026-01-08 06:19:56'),
(13, 6, 'images/prod_695f4c8c1625e.jpg', '2026-01-08 06:19:56'),
(14, 8, 'images/prod_695f4e751f1f5.jpg', '2026-01-08 06:28:05'),
(15, 8, 'images/prod_695f4e752df26.jpg', '2026-01-08 06:28:05'),
(16, 8, 'images/prod_695f4e7537e68.jpg', '2026-01-08 06:28:05');
