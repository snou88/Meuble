
-- default categories insertions
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image_path`, `created_at`) VALUES
(1, 'Salon', 'salon', 'Canapés & Tables basses', 'images/cat_695f304b1a036.jpg', '2026-01-08 04:19:23'),
(2, 'Chambre', 'chambre', 'Lits & Dressings', 'images/cat_695f3a1b620cb.jpg', '2026-01-08 05:01:15'),
(3, 'Salle à manger', 'salle-a-manger', 'Tables & Chaises', 'images/cat_695f3a2d29d38.webp', '2026-01-08 05:01:33'),
(4, 'Bureau', 'bureau', 'Bureaux & Rangements', 'images/cat_695f3a3ac5e77.webp', '2026-01-08 05:01:46');

-- default products insertions
INSERT INTO `products` (`id`, `name`, `description`, `category_id`, `product_type`, `created_at`) VALUES
(3, 'pouffe', 'pouffe exemple', 1, 'available', '2026-01-08 04:58:00'),
(4, 'Canapé', 'Canapé 3 places tissu moderne', 1, 'made_to_order', '2026-01-08 06:12:30'),
(5, 'Table marbre', 'Plateau en marbre - Tiroirs en rotin - Noir et or', 1, 'made_to_order', '2026-01-08 06:15:36'),
(6, 'Chaises', 'Chaise en tissu coloré avec poignée, métal, Tissu', 3, 'made_to_order', '2026-01-08 06:19:55'),
(8, 'Table à manger', 'Table salle à manger bois massif pied mikado', 3, 'available', '2026-01-08 06:28:04');
