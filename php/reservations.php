<?php
/**
 * php/reservations.php
 * --------------------
 * Functions for handling reservations
 * Uses the correct database schema: reservation and table_restaurant tables
 */

require_once __DIR__ . '/../db/config.php';

/**
 * Get all reservations (Admin)
 */
function getAllReservations() {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        SELECT r.*, u.nom, u.prenom, u.email, u.telephone, tr.numero, tr.capacite
        FROM reservation r
        JOIN utilisateur u ON r.id_client = u.id
        JOIN table_restaurant tr ON r.id_table = tr.id
        ORDER BY r.date_reservation DESC
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reservations = [];
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $reservations;
}

/**
 * Get reservations by user (Client)
 */
function getUserReservations($user_id) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        SELECT r.*, tr.numero, tr.capacite
        FROM reservation r
        JOIN table_restaurant tr ON r.id_table = tr.id
        WHERE r.id_client = ?
        ORDER BY r.date_reservation DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reservations = [];
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $reservations;
}

/**
 * Get available tables for a given date/time
 */
function getAvailableTables($date_reservation, $duration_minutes = 120) {
    $conn = getDBConnection();
    
    // Convert date string to datetime
    $datetime = is_string($date_reservation) ? new DateTime($date_reservation) : $date_reservation;
    $datetime_str = $datetime->format('Y-m-d H:i:s');
    
    // Calculate end time
    $end_datetime = clone $datetime;
    $end_datetime->modify("+{$duration_minutes} minutes");
    $end_datetime_str = $end_datetime->format('Y-m-d H:i:s');
    
    // Get tables that are:
    // 1. Free (statut = 'libre')
    // 2. Not reserved during the requested time slot
    $stmt = $conn->prepare("
        SELECT tr.* 
        FROM table_restaurant tr
        WHERE tr.statut = 'libre'
        AND tr.id NOT IN (
            SELECT DISTINCT r.id_table
            FROM reservation r
            WHERE r.statut IN ('en_attente', 'confirmee')
            AND (
                (r.date_reservation <= ? AND DATE_ADD(r.date_reservation, INTERVAL r.duree MINUTE) > ?)
                OR (r.date_reservation < ? AND DATE_ADD(r.date_reservation, INTERVAL r.duree MINUTE) >= ?)
                OR (r.date_reservation >= ? AND r.date_reservation < ?)
            )
        )
        ORDER BY tr.capacite ASC, tr.numero ASC
    ");
    
    $stmt->bind_param("ssssss", 
        $datetime_str, $datetime_str,
        $end_datetime_str, $end_datetime_str,
        $datetime_str, $end_datetime_str
    );
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tables = [];
    while ($row = $result->fetch_assoc()) {
        $tables[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $tables;
}

/**
 * Create reservation (Client)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_reservation') {
    requireClient();
    
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $people = intval($_POST['people'] ?? 0);
    $user_id = $_SESSION['user_id'] ?? 0;
    
    if (empty($date) || empty($time) || $people < 1 || $user_id <= 0) {
        $_SESSION['error'] = 'Veuillez remplir tous les champs correctement.';
        header('Location: ../reservations.php');
        exit();
    }
    
    // Combine date and time
    try {
        $date_reservation = new DateTime($date . ' ' . $time);
    } catch (Exception $e) {
        $_SESSION['error'] = 'Date ou heure invalide.';
        header('Location: ../reservations.php');
        exit();
    }
    
    // Validate date is not in the past
    if ($date_reservation < new DateTime()) {
        $_SESSION['error'] = 'Impossible de réserver une date dans le passé.';
        header('Location: ../reservations.php');
        exit();
    }
    
    // Default duration: 2 hours (120 minutes)
    $duration = 120;
    
    // Find available table that fits the number of people
    $available_tables = getAvailableTables($date_reservation, $duration);
    
    if (empty($available_tables)) {
        $_SESSION['error'] = 'Aucune table disponible pour cette date et heure.';
        header('Location: ../reservations.php');
        exit();
    }
    
    // Find table with capacity >= people needed
    $selected_table = null;
    foreach ($available_tables as $table) {
        if ($table['capacite'] >= $people) {
            $selected_table = $table;
            break;
        }
    }
    
    if (!$selected_table) {
        $_SESSION['error'] = 'Aucune table avec une capacité suffisante n\'est disponible.';
        header('Location: ../reservations.php');
        exit();
    }
    
    $conn = getDBConnection();
    
    // Create reservation
    $date_reservation_str = $date_reservation->format('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO reservation (id_client, id_table, date_reservation, duree, statut) VALUES (?, ?, ?, ?, 'en_attente')");
    $stmt->bind_param("iisi", $user_id, $selected_table['id'], $date_reservation_str, $duration);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Demande de réservation envoyée avec succès !';
    } else {
        $_SESSION['error'] = 'Erreur lors de la création de la réservation.';
    }
    
    $stmt->close();
    $conn->close();
    header('Location: ../reservations.php');
    exit();
}

/**
 * Update reservation status (Admin)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_reservation_status') {
    requireAdmin();
    
    $reservation_id = intval($_POST['reservation_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    
    $allowedStates = ['en_attente', 'confirmee', 'annulee'];
    
    if ($reservation_id <= 0 || !in_array($status, $allowedStates)) {
        $_SESSION['error'] = 'Statut invalide.';
        header('Location: ../admin/reservations.php');
        exit();
    }
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE reservation SET statut = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $reservation_id);
    
    if ($stmt->execute()) {
        // Update table status if confirmed or cancelled
        if ($status === 'confirmee') {
            $tableStmt = $conn->prepare("UPDATE table_restaurant SET statut = 'reservee' WHERE id = (SELECT id_table FROM reservation WHERE id = ?)");
            $tableStmt->bind_param("i", $reservation_id);
            $tableStmt->execute();
            $tableStmt->close();
        } elseif ($status === 'annulee') {
            $tableStmt = $conn->prepare("UPDATE table_restaurant SET statut = 'libre' WHERE id = (SELECT id_table FROM reservation WHERE id = ?)");
            $tableStmt->bind_param("i", $reservation_id);
            $tableStmt->execute();
            $tableStmt->close();
        }
        
        $_SESSION['success'] = 'Statut de la réservation mis à jour.';
    } else {
        $_SESSION['error'] = 'Erreur lors de la mise à jour.';
    }
    
    $stmt->close();
    $conn->close();
    header('Location: ../admin/reservations.php');
    exit();
}
?>
