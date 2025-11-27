-- ============================================================
-- Restaurant Les Jomox - Structure de base de données
-- Schéma conforme au diagramme dbdiagram.io
-- ============================================================

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS restaurant_db;
USE restaurant_db;

-- ============================================================
-- Suppression des anciennes tables (dans l'ordre inverse des dépendances)
-- ============================================================

DROP TABLE IF EXISTS reservation;
DROP TABLE IF EXISTS commande_item;
DROP TABLE IF EXISTS commande;
DROP TABLE IF EXISTS table_restaurant;
DROP TABLE IF EXISTS plat;
DROP TABLE IF EXISTS utilisateur;

-- Suppression des anciennes tables si elles existent (compatibilité)
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS tables;
DROP TABLE IF EXISTS dishes;
DROP TABLE IF EXISTS users;

-- ============================================================
-- Création des nouvelles tables selon le schéma
-- ============================================================

-- Table: utilisateur
-- Contient tous les utilisateurs (clients et admins) avec leur statut
-- is_admin: TRUE = admin, FALSE = client
CREATE TABLE utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    telephone VARCHAR(20) DEFAULT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    is_admin BOOLEAN NOT NULL DEFAULT FALSE,
    INDEX idx_email (email),
    INDEX idx_is_admin (is_admin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: plat
-- Catalogue des plats du restaurant
CREATE TABLE plat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    prix DECIMAL(10, 2) NOT NULL,
    categorie VARCHAR(100) NOT NULL,
    disponible BOOLEAN DEFAULT TRUE,
    INDEX idx_categorie (categorie),
    INDEX idx_disponible (disponible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: commande
-- Commandes passées par les clients
CREATE TABLE commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT NOT NULL,
    date_commande DATETIME DEFAULT CURRENT_TIMESTAMP,
    etat VARCHAR(50) DEFAULT 'en_attente',
    total DECIMAL(10, 2) NOT NULL,
    INDEX idx_id_client (id_client),
    INDEX idx_date_commande (date_commande),
    INDEX idx_etat (etat),
    FOREIGN KEY (id_client) REFERENCES utilisateur(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: commande_item
-- Détails des plats dans chaque commande
CREATE TABLE commande_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_commande INT NOT NULL,
    id_plat INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(10, 2) NOT NULL,
    INDEX idx_id_commande (id_commande),
    INDEX idx_id_plat (id_plat),
    FOREIGN KEY (id_commande) REFERENCES commande(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_plat) REFERENCES plat(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: table_restaurant
-- Tables physiques du restaurant
CREATE TABLE table_restaurant (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL UNIQUE,
    capacite INT NOT NULL,
    statut VARCHAR(50) DEFAULT 'libre',
    INDEX idx_numero (numero),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: reservation
-- Réservations de tables par les clients
CREATE TABLE reservation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT NOT NULL,
    id_table INT NOT NULL,
    date_reservation DATETIME NOT NULL,
    duree INT NOT NULL COMMENT 'Durée en minutes',
    statut VARCHAR(50) DEFAULT 'en_attente',
    INDEX idx_id_client (id_client),
    INDEX idx_id_table (id_table),
    INDEX idx_date_reservation (date_reservation),
    INDEX idx_statut (statut),
    FOREIGN KEY (id_client) REFERENCES utilisateur(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_table) REFERENCES table_restaurant(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Données d'exemple (optionnel - à commenter en production)
-- ============================================================

-- Exemple d'utilisateur admin (mot de passe: admin123)
-- À modifier avec password_hash() en PHP après création
-- is_admin = TRUE pour admin, FALSE pour client
INSERT INTO utilisateur (nom, prenom, email, telephone, mot_de_passe, is_admin) VALUES
('Dupont', 'Jean', 'admin@jomox.com', '0123456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);

-- Exemples de plats
INSERT INTO plat (nom, description, prix, categorie, disponible) VALUES
('Pizza Margherita', 'Pizza classique avec tomate, mozzarella et basilic', 12.99, 'Plat principal', TRUE),
('Salade César', 'Salade romaine, poulet grillé, parmesan, croûtons', 8.99, 'Entrée', TRUE),
('Tiramisu', 'Dessert italien au café et mascarpone', 6.99, 'Dessert', TRUE),
('Saumon grillé', 'Filet de saumon avec légumes de saison', 18.99, 'Plat principal', TRUE);

-- Exemples de tables
INSERT INTO table_restaurant (numero, capacite, statut) VALUES
(1, 2, 'libre'),
(2, 4, 'libre'),
(3, 6, 'libre'),
(4, 2, 'libre'),
(5, 4, 'libre'),
(6, 8, 'libre'),
(7, 2, 'libre'),
(8, 4, 'libre');
