-- ============================================================
-- Script pour créer un compte admin de test
-- Restaurant Les Jomox
-- ============================================================
-- 
-- Ce script insère un compte administrateur dans la table utilisateur
-- 
-- Identifiants de connexion :
-- Email: admin@jomox.com
-- Mot de passe: admin123
-- 
-- ⚠️ IMPORTANT : Changez ce mot de passe en production !
-- ============================================================

USE restaurant_db;

-- Supprimer l'admin s'il existe déjà (pour éviter les doublons)
DELETE FROM utilisateur WHERE email = 'admin@jomox.com';

-- Insérer le compte admin
-- Le mot de passe "admin123" est hashé avec password_hash() PHP
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- is_admin: TRUE = admin, FALSE = client
INSERT INTO utilisateur (
    nom,
    prenom,
    email,
    telephone,
    mot_de_passe,
    is_admin
) VALUES (
    'Admin',
    'Test',
    'admin@jomox.com',
    '0123456789',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    TRUE
);

-- Vérification
SELECT 
    id,
    nom,
    prenom,
    email,
    is_admin,
    CASE 
        WHEN is_admin = TRUE THEN 'Admin'
        ELSE 'Client'
    END as role_display,
    'Compte admin créé avec succès !' as message
FROM utilisateur 
WHERE email = 'admin@jomox.com';

-- ============================================================
-- Instructions :
-- 1. Importez ce fichier dans phpMyAdmin ou via MySQL
-- 2. Connectez-vous à l'admin avec :
--    - Email: admin@jomox.com
--    - Mot de passe: admin123
-- 3. Changez le mot de passe après la première connexion
-- ============================================================

