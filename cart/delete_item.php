<?php
session_start();
include "../includes/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['error'=>'غير مسموح']);
    exit;
}

$id_user = (int)$_SESSION['id_user'];
$id_livre = intval($_POST['id_livre'] ?? 0);
$quantite = intval($_POST['quantite'] ?? 1);
$type_transaction = $_POST['type_transaction'] ?? 'achat';

if ($quantite < 1) $quantite = 1;

// تحديث الكمية
$stmt = $conn->prepare("UPDATE panier_item SET quantite=? WHERE id_livre=? AND id_panier=(SELECT id_panier FROM panier WHERE id_user=?)");
if (!$stmt) die(json_encode(['error'=>"خطأ SQL: " . $conn->error]));

$stmt->bind_param("iii", $quantite, $id_livre, $id_user);
$stmt->execute();

// إعادة حساب المجموع
$q = $conn->query("
    SELECT i.quantite, i.prix_unitaire 
    FROM panier_item i
    JOIN panier p ON p.id_panier=i.id_panier
    WHERE p.id_user=$id_user AND i.type_transaction='achat'
");

$total = 0;
$sous_total = 0;
while ($row = $q->fetch_assoc()) {
    $ligne_total = $row['quantite'] * $row['prix_unitaire'];
    $total += $ligne_total;
    if ($row['quantite'] == $quantite) $sous_total = $ligne_total;
}

echo json_encode([
    'sous_total' => $sous_total,
    'total' => $total
]);