<?php
/**
 * admin/includes/functions.php
 * -----------------------------
 * Fonctions utilitaires pour l'admin
 */

require_once __DIR__ . '/db.php';

/**
 * Formate un montant en euros
 */
function formatPrice($price) {
    return number_format((float)$price, 2, ',', ' ') . ' €';
}

/**
 * Formate une date/heure
 */
function formatDateTime($datetime) {
    if (empty($datetime)) return '-';
    $date = new DateTime($datetime);
    return $date->format('d/m/Y à H:i');
}

/**
 * Formate une date
 */
function formatDate($date) {
    if (empty($date)) return '-';
    $d = new DateTime($date);
    return $d->format('d/m/Y');
}

/**
 * Retourne le badge HTML pour un état
 */
function getStatusBadge($status) {
    $badges = [
        'en_attente' => '<span class="badge badge-warning">En attente</span>',
        'confirmee' => '<span class="badge badge-success">Confirmée</span>',
        'servie' => '<span class="badge badge-info">Servie</span>',
        'annulee' => '<span class="badge badge-danger">Annulée</span>',
        'libre' => '<span class="badge badge-success">Libre</span>',
        'occupee' => '<span class="badge badge-danger">Occupée</span>',
        'reservee' => '<span class="badge badge-warning">Réservée</span>',
    ];
    
    return $badges[$status] ?? '<span class="badge">' . htmlspecialchars($status) . '</span>';
}

/**
 * Échappe les données pour l'affichage HTML
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

