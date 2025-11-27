<?php
/**
 * admin/create_admin.php
 * ----------------------
 * Script PHP pour créer un compte admin de test
 * 
 * ⚠️ SUPPRIMEZ CE FICHIER APRÈS UTILISATION EN PRODUCTION !
 */

require_once __DIR__ . '/includes/db.php';

// Configuration
$adminData = [
    'nom' => 'Admin',
    'prenom' => 'Test',
    'email' => 'admin@jomox.com',
    'telephone' => '0123456789',
    'password' => 'admin123', // ⚠️ Changez ce mot de passe !
    'is_admin' => true // TRUE = admin, FALSE = client
];

try {
    $pdo = getPDO();
    
    // Vérifier si l'admin existe déjà
    $stmt = $pdo->prepare('SELECT id FROM utilisateur WHERE email = :email');
    $stmt->execute(['email' => $adminData['email']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Mettre à jour le mot de passe
        $hash = password_hash($adminData['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE utilisateur SET mot_de_passe = :password WHERE email = :email');
        $stmt->execute([
            'email' => $adminData['email'],
            'password' => $hash
        ]);
        $message = "Compte admin mis à jour avec succès !";
    } else {
        // Créer le compte admin (is_admin = TRUE)
        $hash = password_hash($adminData['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO utilisateur (nom, prenom, email, telephone, mot_de_passe, is_admin) VALUES (:nom, :prenom, :email, :telephone, :password, :is_admin)');
        $stmt->execute([
            'nom' => $adminData['nom'],
            'prenom' => $adminData['prenom'],
            'email' => $adminData['email'],
            'telephone' => $adminData['telephone'],
            'password' => $hash,
            'is_admin' => $adminData['is_admin'] ? 1 : 0 // Convertir booléen en INT pour MySQL
        ]);
        $message = "Compte admin créé avec succès !";
    }
    
    // Afficher les informations
    echo "<!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Création Admin - Les Jomox</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .success {
                background: #d4edda;
                color: #155724;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                border: 1px solid #c3e6cb;
            }
            .info {
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .info h2 {
                margin-top: 0;
                color: #333;
            }
            .info p {
                margin: 10px 0;
            }
            .credentials {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 6px;
                margin: 15px 0;
                border-left: 4px solid #FF7A00;
            }
            .credentials strong {
                color: #FF7A00;
            }
            .warning {
                background: #fff3cd;
                color: #856404;
                padding: 15px;
                border-radius: 6px;
                margin-top: 20px;
                border: 1px solid #ffeaa7;
            }
            a {
                display: inline-block;
                margin-top: 20px;
                padding: 10px 20px;
                background: #FF7A00;
                color: white;
                text-decoration: none;
                border-radius: 6px;
            }
            a:hover {
                background: #e66a00;
            }
        </style>
    </head>
    <body>
        <div class='success'>
            <strong>✅ $message</strong>
        </div>
        
        <div class='info'>
            <h2>Identifiants de connexion</h2>
            <div class='credentials'>
                <p><strong>Email:</strong> {$adminData['email']}</p>
                <p><strong>Mot de passe:</strong> {$adminData['password']}</p>
            </div>
            
            <p><strong>Rôle:</strong> " . ($adminData['is_admin'] ? 'Admin' : 'Client') . "</p>
            <p><strong>Nom:</strong> {$adminData['prenom']} {$adminData['nom']}</p>
            
            <div class='warning'>
                <strong>⚠️ IMPORTANT:</strong><br>
                - Supprimez ce fichier (create_admin.php) après utilisation<br>
                - Changez le mot de passe après la première connexion<br>
                - Ne partagez jamais ces identifiants en production
            </div>
            
            <a href='../login.php'>Se connecter à l'admin</a>
        </div>
    </body>
    </html>";
    
} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}

