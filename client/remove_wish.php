<?php
session_start();
require_once "../includes/db.php";

if (isset($_GET['id']) && isset($_SESSION['id_user'])) {
    $id_w = (int)$_GET['id'];
    $id_u = (int)$_SESSION['id_user'];
    $conn->query("DELETE FROM wishlist WHERE id_wishlist = $id_w AND id_user = $id_u");
}
header("Location: profile.php?tab=wishlist");
exit;