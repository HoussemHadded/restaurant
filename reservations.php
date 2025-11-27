<?php
/**
 * reservations.php
 * ----------------
 * Client reservations page
 */

require_once __DIR__ . '/db/config.php';
requireClient();

require_once __DIR__ . '/php/reservations.php';

$user_reservations = getUserReservations($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservations - Restaurant Les Jomox</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <h2>Restaurant Les Jomox</h2>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    <li><a href="reservations.php">Réservations</a></li>
                    <li><a href="php/auth.php?action=logout">Déconnexion</a></li>
                </ul>
                <div class="user-info">
                    <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_nom'] ?? $_SESSION['user_prenom'] ?? 'Utilisateur'); ?></span>
                </div>
            </nav>
        </header>

        <div class="dashboard">
            <h1>Réservations de tables</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="reservation-form">
                <h2>Faire une réservation</h2>
                <form action="php/reservations.php" method="POST">
                    <input type="hidden" name="action" value="create_reservation">
                    
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="time">Heure</label>
                        <input type="time" id="time" name="time" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="people">Nombre de personnes</label>
                        <input type="number" id="people" name="people" required min="1" max="20">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Soumettre la réservation</button>
                </form>
            </div>

            <div class="table-container mt-30">
                <h2>Mes réservations</h2>
                <?php if (empty($user_reservations)): ?>
                    <p class="text-center mt-20">Vous n'avez aucune réservation pour le moment.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date et heure</th>
                                <th>Table</th>
                                <th>Capacité</th>
                                <th>Durée</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($user_reservations as $reservation): ?>
                                <?php
                                // Parse date_reservation
                                $date_reservation = new DateTime($reservation['date_reservation']);
                                $duree_heures = floor($reservation['duree'] / 60);
                                $duree_minutes = $reservation['duree'] % 60;
                                $duree_display = $duree_heures . 'h';
                                if ($duree_minutes > 0) {
                                    $duree_display .= $duree_minutes . 'min';
                                }
                                
                                // Status badge class
                                $status_class = 'status-' . htmlspecialchars($reservation['statut']);
                                $status_labels = [
                                    'en_attente' => 'En attente',
                                    'confirmee' => 'Confirmée',
                                    'annulee' => 'Annulée'
                                ];
                                $status_label = $status_labels[$reservation['statut']] ?? $reservation['statut'];
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($date_reservation->format('d/m/Y à H:i')); ?></td>
                                    <td>Table <?php echo htmlspecialchars($reservation['numero']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['capacite']); ?> personnes</td>
                                    <td><?php echo $duree_display; ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo htmlspecialchars($status_label); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
