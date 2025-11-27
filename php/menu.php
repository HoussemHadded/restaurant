<?php
/**
 * php/menu.php
 * ------------
 * Menu helper functions (client-facing only)
 * Admin menu management is in admin/menu.php
 */

require_once __DIR__ . '/../db/config.php';

/**
 * Get all available dishes, optionally filtered by category
 */
function getAllDishes($category = null) {
    $conn = getDBConnection();
    
    if ($category && $category !== 'all') {
        $stmt = $conn->prepare("SELECT * FROM plat WHERE categorie = ? AND disponible = 1 ORDER BY nom");
        $stmt->bind_param("s", $category);
    } else {
        // Only available dishes
        $stmt = $conn->prepare("SELECT * FROM plat WHERE disponible = 1 ORDER BY nom");
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $dishes = [];
    while ($row = $result->fetch_assoc()) {
        $dishes[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $dishes;
}

/**
 * Get dish by ID
 */
function getDishById($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM plat WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dish = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $dish;
}

/**
 * Get all categories from available dishes
 */
function getCategories() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT DISTINCT categorie FROM plat WHERE disponible = 1 ORDER BY categorie");
    $categories = [];
    
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['categorie'];
    }
    
    $conn->close();
    return $categories;
}
?>
