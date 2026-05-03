<?php
session_start();
include "../includes/db.php";

$id = intval($_GET['id']);

/* جلب id_livre */
$res = $conn->query("SELECT id_livre FROM emprunt WHERE id_emprunt = $id");
$row = $res->fetch_assoc();
$id_livre = $row['id_livre'];

/* تحديث الحالة */
$conn->query("
UPDATE emprunt
SET statut = 'retourné',
    date_retour = CURDATE()
WHERE id_emprunt = $id
");

/* إعادة الكمية */
$conn->query("
UPDATE livres
SET quantite = quantite + 1
WHERE id = $id_livre
");

header("Location: mes_emprunts.php");
exit;
?>