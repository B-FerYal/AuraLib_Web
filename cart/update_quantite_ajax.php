<?php
ob_start();
session_start();
error_reporting(0);
ini_set('display_errors', 0);

ob_clean();
header('Content-Type: application/json');

// ── Auth check ────────────────────────────────────────────────────────────
if (!isset($_SESSION['id_user'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

// ── Input validation ──────────────────────────────────────────────────────
if (!isset($_POST['id_item'], $_POST['qty'])) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

$id_item = (int)$_POST['id_item'];
$new_qty = (int)$_POST['qty'];

if ($id_item <= 0 || $new_qty < 1) {
    echo json_encode(['success' => false, 'message' => 'Valeurs invalides']);
    exit;
}

// ── DB connection ─────────────────────────────────────────────────────────
require_once __DIR__ . "/../includes/db.php";

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion DB']);
    exit;
}

// ── Verify this item belongs to the logged-in user ────────────────────────
$check = $conn->prepare("
    SELECT i.id_item, d.prix
    FROM panier_item i
    JOIN panier p ON p.id_panier = i.id_panier
    JOIN documents d ON d.id_doc = i.id_doc
    WHERE i.id_item = ? AND p.id_user = ?
    LIMIT 1
");
$check->bind_param("ii", $id_item, $_SESSION['id_user']);
$check->execute();
$row = $check->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Article introuvable']);
    exit;
}

$prix = (float)$row['prix'];

// ── Update quantity ───────────────────────────────────────────────────────
$stmt = $conn->prepare("UPDATE panier_item SET quantite = ? WHERE id_item = ?");
$stmt->bind_param("ii", $new_qty, $id_item);

if ($stmt->execute()) {
    echo json_encode([
        'success'      => true,
        'new_qty'      => $new_qty,
        'new_subtotal' => $new_qty * $prix,   // frontend uses this directly
        'prix'         => $prix
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Échec de la mise à jour']);
}

$conn->close();
exit;
