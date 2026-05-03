<?php
session_start();
require_once "../includes/db.php";
$id_user = $_SESSION['id_user'];

// 1. Jib kolch m-l-panier
$res = $conn->query("SELECT pi.* FROM panier_item pi JOIN panier p ON pi.id_panier = p.id_panier WHERE p.id_user = $id_user");

while($row = $res->fetch_assoc()) {
    if($row['type_transaction'] == 'emprunt') {
        // Zid f table emprunt
        $id_livre = $row['id_livre'];
        $date_debut = date('Y-m-d');
        $date_fin = date('Y-m-d', strtotime('+14 days'));
        $conn->query("INSERT INTO emprunt (id_user, id_livre, date_emprunt, date_retour_prevu, statut) 
                      VALUES ($id_user, $id_livre, '$date_debut', '$date_fin', 'en_cours')");
    } else {
        // Zid f table commande ta3 l-achat
        // ... (code ta3 l-achat)
    }
}

// 2. Farregh l-panier
$conn->query("DELETE FROM panier_item WHERE id_panier = (SELECT id_panier FROM panier WHERE id_user = $id_user)");

// 3. DORKA ab3at l-profile (Mes Emprunts)
header("Location: ../client/profile.php");
exit;