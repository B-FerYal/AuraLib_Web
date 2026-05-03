<?php
session_start();

include "../includes/db.php";

$id = intval($_GET['id']);

/* تمديد 5 أيام */
$conn->query("
UPDATE emprunt
SET date_fin = DATE_ADD(date_fin, INTERVAL 5 DAY)
WHERE id_emprunt = $id
");

header("Location: mes_emprunts.php");
exit;
?>