# Restaurant Les Jomox - Système de Gestion

Application web complète de gestion de restaurant avec interface admin et client.

## 📋 Structure du Projet

```
jomox/
├── admin/              # Panneau d'administration
│   ├── index.php      # Dashboard admin
│   ├── menu.php       # Gestion du menu
│   ├── commandes.php  # Gestion des commandes
│   ├── reservations.php # Gestion des réservations
│   ├── tables.php     # Gestion des tables
│   ├── utilisateurs.php # Gestion des utilisateurs
│   ├── includes/      # Fichiers partagés (auth, db, functions)
│   └── assets/        # CSS et JS
├── assets/            # Assets globaux
│   └── css/          # Styles CSS
├── css/               # Styles CSS principaux
├── db/                # Base de données
│   ├── config.php    # Configuration DB
│   ├── database.sql  # Schéma SQL
│   └── migrate_role_to_boolean.sql # Script de migration
├── php/               # Backend PHP
│   ├── auth.php      # Authentification
│   ├── menu.php      # Fonctions menu
│   ├── orders.php    # Fonctions commandes
│   └── reservations.php # Fonctions réservations
├── index.php          # Point d'entrée (redirection)
├── login.php          # Page de connexion unique
├── register.php       # Inscription client
├── dashboard.php      # Dashboard principal
├── menu.php           # Menu client
└── reservations.php   # Réservations client
```

## 🚀 Installation

### 1. Prérequis
- XAMPP ou WAMP (Apache + MySQL)
- PHP 7.4+

### 2. Configuration de la base de données

1. Créer la base de données :
   ```sql
   CREATE DATABASE restaurant_db;
   ```

2. Importer le schéma :
   - Ouvrir phpMyAdmin : `http://localhost/phpmyadmin`
   - Sélectionner `restaurant_db`
   - Importer `db/database.sql`

3. Créer un compte admin :
   - Exécuter `admin/create_admin.sql` dans phpMyAdmin
   - Ou exécuter `admin/create_admin.php` via navigateur
   - Identifiants par défaut : `admin@jomox.com` / `admin123`

### 3. Configuration

Vérifier les paramètres dans `db/config.php` :
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'restaurant_db');
```

## 🔐 Connexion

1. Accéder à : `http://localhost/jomox/login.php`
2. Le système détecte automatiquement le rôle :
   - **Admin** (`is_admin = TRUE`) → Redirige vers `admin/index.php`
   - **Client** (`is_admin = FALSE`) → Redirige vers `dashboard.php`

## 📱 Fonctionnalités

### Interface Admin
- ✅ Gestion du menu (CRUD plats)
- ✅ Gestion des commandes (voir, modifier état)
- ✅ Gestion des réservations (confirmer, annuler)
- ✅ Gestion des tables (statut, capacité)
- ✅ Gestion des utilisateurs (voir, modifier, supprimer)

### Interface Client
- ✅ Consultation du menu
- ✅ Passer des commandes
- ✅ Faire des réservations
- ✅ Voir ses commandes et réservations

## 🗄️ Base de Données

### Tables principales
- `utilisateur` - Utilisateurs (admin/client avec `is_admin` booléen)
- `plat` - Catalogue des plats
- `commande` - Commandes clients
- `commande_item` - Détails des commandes
- `table_restaurant` - Tables du restaurant
- `reservation` - Réservations de tables

### Migration
Si vous migrez depuis l'ancien système avec `role` VARCHAR, exécutez :
```sql
SOURCE db/migrate_role_to_boolean.sql;
```

## 🔒 Sécurité

- ✅ Requêtes préparées PDO (protection injection SQL)
- ✅ Hashage des mots de passe (`password_hash()`)
- ✅ Validation côté serveur
- ✅ Sessions sécurisées
- ✅ Protection des pages sensibles

## 📝 Notes

- Le système utilise un **login unique** qui détecte automatiquement le rôle
- Les admins sont identifiés par `is_admin = TRUE` dans la table `utilisateur`
- Tous les fichiers de test/développement ont été supprimés pour garder uniquement l'application finale

## 🐛 Dépannage

**Erreur de connexion DB** : Vérifier `db/config.php`

**Page blanche** : Activer l'affichage des erreurs PHP

**Impossible de se connecter** : Vérifier qu'un admin existe avec `is_admin = TRUE`

## 📚 Documentation

- `admin/README.md` - Documentation complète du panneau admin
- `MIGRATION_ROLE_TO_BOOLEAN.md` - Guide de migration role → boolean


