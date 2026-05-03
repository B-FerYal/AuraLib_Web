<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once "../includes/db.php"; 
require_once '../includes/head.php'; 

// التحقق من صلاحيات الأدمن
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    die("Accès refusé.");
}

// التأكد من وجود ID الطلب
if (!isset($_GET['id'])) {
    die("ID de commande manquant.");
}

$id_commande = intval($_GET['id']);

// 1. جلب بيانات الطلب والمشتري
$query = "
    SELECT c.*, u.firstname, u.lastname, u.email 
    FROM commande c 
    JOIN users u ON c.id_user = u.id 
    WHERE c.id_commande = $id_commande";
$res = $conn->query($query);
$order = $res->fetch_assoc();

if (!$order) {
    die("Commande non trouvée.");
}

// 2. جلب الكتب من جدول commande_item باستخدام حقل 'prix' الصحيح
$items_query = "
    SELECT ci.prix as prix_unitaire, ci.quantite, d.titre 
    FROM commande_item ci 
    JOIN documents d ON ci.id_doc = d.id_doc 
    WHERE ci.id_commande = $id_commande";
$items_res = $conn->query($items_query);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture #<?= $id_commande ?> - AuraLib</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f1ea; padding: 30px; color: #2C1F0E; }
        .invoice-box {
            max-width: 800px; margin: auto; padding: 40px;
            background: #fff; border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #D4A942; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 32px; font-weight: bold; color: #2C1F0E; letter-spacing: -1px; }
        .invoice-info { text-align: right; font-size: 14px; color: #777; }
        
        .client-section { margin-bottom: 30px; }
        .client-section h3 { font-size: 12px; text-transform: uppercase; color: #D4A942; margin-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { text-align: left; padding: 15px; background: #F9F5EE; font-size: 12px; text-transform: uppercase; color: #999; }
        td { padding: 15px; border-bottom: 1px solid #F9F5EE; }
        
        .total-section { text-align: right; margin-top: 30px; padding-top: 20px; border-top: 2px solid #2C1F0E; }
        .total-amount { font-size: 24px; font-weight: 800; color: #27ae60; }
        
        .btn-print {
            background: #2C1F0E; color: white; padding: 12px 25px;
            border: none; border-radius: 10px; cursor: pointer; font-weight: 600;
            text-decoration: none; display: inline-block; margin-bottom: 20px;
        }

        @media print {
            .no-print { display: none; }
            body { background: white; padding: 0; }
            .invoice-box { box-shadow: none; width: 100%; max-width: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align:center;">
    <button onclick="window.print()" class="btn-print">🖨 Imprimer la Facture</button>
    <a href="all_orders.php" style="margin-left:15px; color:#777; text-decoration:none;">← Retour</a>
</div>

<div class="invoice-box">
    <div class="header">
        <div class="logo">AuraLib</div>
        <div class="invoice-info">
            <strong>Facture N°:</strong> #<?= $id_commande ?><br>
            <strong>Date:</strong> <?= date('d/m/Y', strtotime($order['date_commande'])) ?><br>
            <strong>Statut:</strong> <?= ($order['statut'] == 'payée') ? 'Payée' : 'En attente' ?>
        </div>
    </div>

    <div class="client-section">
        <h3>Destinataire</h3>
        <strong><?= htmlspecialchars($order['firstname'] . ' ' . $order['lastname']) ?></strong><br>
        <?= htmlspecialchars($order['email']) ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Désignation</th>
                <th>Quantité</th>
                <th style="text-align:right;">Prix Unitaire</th>
                <th style="text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while($item = $items_res->fetch_assoc()): 
                $subtotal = $item['prix_unitaire'] * $item['quantite'];
            ?>
            <tr>
                <td><?= htmlspecialchars($item['titre']) ?></td>
                <td>x<?= $item['quantite'] ?></td>
                <td style="text-align:right;"><?= number_format($item['prix_unitaire'], 2) ?> DA</td>
                <td style="text-align:right; font-weight:bold;"><?= number_format($subtotal, 2) ?> DA</td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="total-section">
        <span style="font-size:14px; color:#777;">MONTANT TOTAL</span><br>
        <span class="total-amount"><?= number_format($order['total'], 2) ?> DA</span>
    </div>

    <div style="margin-top:50px; text-align:center; font-size:12px; color:#bbb;">
        AuraLib - Système de Gestion de Bibliothèque & Vente<br>
        Document généré le <?= date('d/m/Y H:i') ?>
    </div>
</div>

</body>
</html>