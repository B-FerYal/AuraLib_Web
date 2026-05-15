<?php
session_start();
require_once "../includes/db.php";

// ── Guards ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /MEMOIR/client/library.php");
    exit;
}

$id_commande = isset($_POST['id_commande']) ? (int)$_POST['id_commande']   : 0;
$montant     = isset($_POST['montant'])     ? (float)$_POST['montant']     : 0;
$methode     = isset($_POST['methode'])     ? $_POST['methode']            : 'baridi';
$id_user     = isset($_SESSION['id_user']) ? (int)$_SESSION['id_user']    : 0;

// Sanitize methode
$methode = in_array($methode, ['baridi', 'cash']) ? $methode : 'baridi';

if (!$id_commande || !$id_user) {
    header("Location: /MEMOIR/client/library.php");
    exit;
}

// ── Verify order belongs to this user ──────────────────
$check = $conn->prepare("SELECT id_commande FROM commande WHERE id_commande = ? AND id_user = ?");
$check->bind_param("ii", $id_commande, $id_user);
$check->execute();
if (!$check->get_result()->fetch_assoc()) {
    header("Location: /MEMOIR/client/library.php");
    exit;
}

$now = date('Y-m-d H:i:s');

// ── Statut selon la méthode ────────────────────────────
// baridi = paiement immédiat → 'payée'
// cash   = paiement à la livraison → 'en attente de paiement'
$statut_commande = ($methode === 'baridi') ? 'payée' : 'en attente de paiement';

// ── 1. Update commande status ──────────────────────────
$stmt = $conn->prepare("
    UPDATE commande 
    SET statut           = ?,
        methode_paiement = ?,
        date_paiement    = ?
    WHERE id_commande = ? AND id_user = ?
");
$stmt->bind_param("sssii", $statut_commande, $methode, $now, $id_commande, $id_user);
$stmt->execute();

// ── 2. Insert into paiement table ─────────────────────
$ins = $conn->prepare("
    INSERT INTO paiement (id_commande, id_user, montant, date_paiement, statut)
    VALUES (?, ?, ?, ?, 'validé')
");
$ins->bind_param("iids", $id_commande, $id_user, $montant, $now);
$ins->execute();

// ── 3. Empty the cart (Secure way) ─────────────────────
$del_items = $conn->prepare("
    DELETE pi FROM panier_item pi
    INNER JOIN panier p ON pi.id_panier = p.id_panier
    WHERE p.id_user = ?
");
$del_items->bind_param("i", $id_user);
$del_items->execute();

$del_panier = $conn->prepare("DELETE FROM panier WHERE id_user = ?");
$del_panier->bind_param("i", $id_user);
$del_panier->execute();

// ── 4. Redirect to success page ───────────────────────
header("Location: /MEMOIR/commandes/succes.php?id=$id_commande&montant=$montant&methode=$methode");
exit;