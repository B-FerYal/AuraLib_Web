<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_item = (int)($_GET['id'] ?? 0);
$id_user = (int)$_SESSION['id_user'];

if ($id_item > 0) {
    // حذف العنصر مع التأكد من ملكية المستخدم للسلة
    $stmt = $conn->prepare("
        DELETE i FROM panier_item i 
        JOIN panier p ON i.id_panier = p.id_panier 
        WHERE i.id_item = ? AND p.id_user = ?
    ");
    $stmt->bind_param("ii", $id_item, $id_user);
    $stmt->execute();
    $stmt->close();
}

header("Location: panier.php");
exit;