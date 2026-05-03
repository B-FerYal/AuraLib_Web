<?php
session_start();
include_once __DIR__ . "/../includes/db.php";

if (isset($_GET['id']) && isset($_SESSION['id_user'])) {
    $id_cmd = (int)$_GET['id'];
    $id_user = (int)$_SESSION['id_user'];

    // On vérifie que la commande appartient bien à l'utilisateur et n'est pas payée
    $check = $conn->query("SELECT id_commande FROM commande WHERE id_commande = $id_cmd AND id_user = $id_user AND statut != 'payée'");
    
    if ($check->num_rows > 0) {
        // Supprimer d'abord les items (contrainte clé étrangère)
        $conn->query("DELETE FROM commande_item WHERE id_commande = $id_cmd");
        // Supprimer la commande
        $conn->query("DELETE FROM commande WHERE id_commande = $id_cmd");
    }
}

header("Location: commande_list.php"); // Retour à la liste
exit;