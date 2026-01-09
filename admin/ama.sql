DROP TABLE IF EXISTS products;
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS product_dimensions;
CREATE TABLE product_dimensions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  width_cm INT NOT NULL,
  height_cm INT NOT NULL,
  label VARCHAR(50) NOT NULL,   -- ex: 120x70
  price DECIMAL(10,2) NOT NULL,
  stock INT DEFAULT 0,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS dimension_images;
CREATE TABLE dimension_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dimension_id INT NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  is_primary BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (dimension_id) REFERENCES product_dimensions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS product_colors;
CREATE TABLE product_colors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  type ENUM('tissu','bois') NOT NULL,
  color_name VARCHAR(50) NOT NULL,
  color_code VARCHAR(20) DEFAULT NULL,
  price_modifier DECIMAL(10,2) DEFAULT 0,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(100) NOT NULL,
  customer_phone VARCHAR(20) NOT NULL,
  customer_address TEXT NOT NULL,
  commune VARCHAR(100) NOT NULL,
  wilaya_name VARCHAR(50) NOT NULL,
  delivery_type VARCHAR(20) NOT NULL,
  delivery_price DECIMAL(10,2) NOT NULL,
  total_price DECIMAL(10,2) NOT NULL,
  status VARCHAR(20) DEFAULT 'pending',
  order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS order_items;
CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  dimension_id INT NOT NULL,
  tissu_color VARCHAR(50),
  bois_color VARCHAR(50),
  unit_price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL,
  total_price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (dimension_id) REFERENCES product_dimensions(id)
) ;

INSERT INTO products (name, description)
VALUES ('Pouf Confort', 'Pouf moderne en tissu premium');

-- Dimensions
INSERT INTO product_dimensions
(product_id, width_cm, height_cm, label, price, stock)
VALUES
(1, 120, 70, '120x70', 5000, 10),
(1, 140, 80, '140x80', 6500, 5);

-- Images
INSERT INTO dimension_images (dimension_id, image_path, is_primary)
VALUES
(1, 'images/pouf_120x70.png', TRUE),
(2, 'images/pouf_140x80.png', TRUE);

-- Couleurs tissu
INSERT INTO product_colors
(product_id, type, color_name, color_code, price_modifier)
VALUES
(1, 'tissu', 'Gris', '#999999', 0),
(1, 'tissu', 'Velours Noir', '#000000', 500);

-- Couleurs bois
INSERT INTO product_colors
(product_id, type, color_name, color_code, price_modifier)
VALUES
(1, 'bois', 'Chêne', '#c19a6b', 0),
(1, 'bois', 'Noyer', '#654321', 300);


INSERT INTO wilayas (id, name, domicile_price, stopdesk_price, is_active) VALUES
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
(58, 'In Guezzam', 1500, 1200, 1);

