<?php
/**
 * php/orders.php
 * --------------
 * Functions for handling orders (commandes)
 * Uses the correct database schema: commande and commande_item tables
 */

require_once __DIR__ . '/../db/config.php';

/**
 * Get all orders (Admin)
 */
function getAllOrders() {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        SELECT c.*, u.nom, u.prenom, u.email, u.telephone
        FROM commande c
        JOIN utilisateur u ON c.id_client = u.id
        ORDER BY c.date_commande DESC
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        // Get order items
        $itemsStmt = $conn->prepare("
            SELECT ci.*, p.nom as plat_nom
            FROM commande_item ci
            JOIN plat p ON ci.id_plat = p.id
            WHERE ci.id_commande = ?
        ");
        $itemsStmt->bind_param("i", $row['id']);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        $row['items'] = [];
        while ($item = $itemsResult->fetch_assoc()) {
            $row['items'][] = $item;
        }
        $itemsStmt->close();
        $orders[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $orders;
}

/**
 * Get orders by user (Client)
 */
function getUserOrders($user_id) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        SELECT c.*
        FROM commande c
        WHERE c.id_client = ?
        ORDER BY c.date_commande DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        // Get order items
        $itemsStmt = $conn->prepare("
            SELECT ci.*, p.nom as plat_nom, p.description
            FROM commande_item ci
            JOIN plat p ON ci.id_plat = p.id
            WHERE ci.id_commande = ?
        ");
        $itemsStmt->bind_param("i", $row['id']);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        $row['items'] = [];
        while ($item = $itemsResult->fetch_assoc()) {
            $row['items'][] = $item;
        }
        $itemsStmt->close();
        $orders[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $orders;
}

/**
 * Place order (Client)
 * Creates a commande with commande_item entries
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    requireClient();
    
    $dish_id = intval($_POST['dish_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $user_id = $_SESSION['user_id'] ?? 0;
    
    if ($dish_id <= 0 || $quantity < 1 || $user_id <= 0) {
        $_SESSION['error'] = 'Données invalides pour la commande.';
        header('Location: ../menu.php');
        exit();
    }
    
    $conn = getDBConnection();
    
    // Get dish price
    $dishStmt = $conn->prepare("SELECT prix, nom FROM plat WHERE id = ? AND disponible = 1");
    $dishStmt->bind_param("i", $dish_id);
    $dishStmt->execute();
    $dishResult = $dishStmt->get_result();
    $dish = $dishResult->fetch_assoc();
    
    if (!$dish) {
        $_SESSION['error'] = 'Plat introuvable ou indisponible.';
        $dishStmt->close();
        $conn->close();
        header('Location: ../menu.php');
        exit();
    }
    
    $dishStmt->close();
    
    // Calculate total
    $total = $dish['prix'] * $quantity;
    
    // Create order
    $orderStmt = $conn->prepare("INSERT INTO commande (id_client, total, etat) VALUES (?, ?, 'en_attente')");
    $orderStmt->bind_param("id", $user_id, $total);
    
    if (!$orderStmt->execute()) {
        $_SESSION['error'] = 'Erreur lors de la création de la commande.';
        $orderStmt->close();
        $conn->close();
        header('Location: ../menu.php');
        exit();
    }
    
    $order_id = $conn->insert_id;
    $orderStmt->close();
    
    // Create order item
    $itemStmt = $conn->prepare("INSERT INTO commande_item (id_commande, id_plat, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
    $itemStmt->bind_param("iiid", $order_id, $dish_id, $quantity, $dish['prix']);
    
    if ($itemStmt->execute()) {
        $_SESSION['success'] = 'Commande passée avec succès !';
    } else {
        // Rollback: delete order if item creation fails
        $conn->query("DELETE FROM commande WHERE id = $order_id");
        $_SESSION['error'] = 'Erreur lors de l\'ajout du plat à la commande.';
    }
    
    $itemStmt->close();
    $conn->close();
    header('Location: ../menu.php');
    exit();
}

/**
 * Update order status (Admin)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order_status') {
    requireAdmin();
    
    $order_id = intval($_POST['order_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    
    $allowedStates = ['en_attente', 'confirmee', 'servie', 'annulee'];
    
    if ($order_id <= 0 || !in_array($status, $allowedStates)) {
        $_SESSION['error'] = 'État invalide.';
        header('Location: ../admin/commandes.php');
        exit();
    }
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE commande SET etat = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'État de la commande mis à jour.';
    } else {
        $_SESSION['error'] = 'Erreur lors de la mise à jour.';
    }
    
    $stmt->close();
    $conn->close();
    header('Location: ../admin/commandes.php');
    exit();
}
?>
