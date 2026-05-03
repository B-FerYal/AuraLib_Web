<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['id_user']) || !isset($_POST['id_doc'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$id_user = (int)$_SESSION['id_user'];
$id_doc = (int)$_POST['id_doc'];

// التحقق إذا كان الكتاب موجود مسبقاً (لحذفه) أو غير موجود (لإضافته)
$check = $conn->query("SELECT id_wishlist FROM wishlist WHERE id_user = $id_user AND id_doc = $id_doc");

if ($check->num_rows > 0) {
    $conn->query("DELETE FROM wishlist WHERE id_user = $id_user AND id_doc = $id_doc");
    echo json_encode(['status' => 'removed']);
} else {
    $conn->query("INSERT INTO wishlist (id_user, id_doc) VALUES ($id_user, $id_doc)");
    echo json_encode(['status' => 'added']);
}