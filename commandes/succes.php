<?php
session_start();
require_once "../includes/db.php";
include "../includes/header.php";

$id_commande = isset($_GET['id'])      ? (int)$_GET['id']       : 0;
$montant     = isset($_GET['montant']) ? (float)$_GET['montant'] : 0;
$methode     = isset($_GET['methode']) ? $_GET['methode']        : 'baridi';
$id_user     = isset($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : 0;

// ── Sanitize methode ────────────────────────────────────
$methode = in_array($methode, ['baridi', 'cash']) ? $methode : 'baridi';

// ── Determine statut based on payment method ────────────
// Baridi Mob = paid immediately → 'payée'
// Cash on delivery = not paid yet → 'en attente de paiement'
$statut_commande = ($methode === 'baridi') ? 'payée' : 'en attente de paiement';

$order = null;

if ($id_commande && $id_user) {

    // ── Fetch the order ─────────────────────────────────
    $stmt = $conn->prepare("SELECT * FROM commande WHERE id_commande = ? AND id_user = ?");
    $stmt->bind_param("ii", $id_commande, $id_user);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    // ── Automate: update stock and status only once ─────
    if ($order && $order['statut'] === 'en attente') {

        // Update order status — different per method
        $update_order = $conn->prepare("
            UPDATE commande 
            SET statut = ?, date_paiement = NOW() 
            WHERE id_commande = ?
        ");
        $update_order->bind_param("si", $statut_commande, $id_commande);

        if ($update_order->execute()) {

            // Decrease stock only if actually paid (baridi)
            if ($methode === 'baridi') {
                $items_stmt = $conn->prepare("SELECT id_doc, quantite FROM commande_item WHERE id_commande = ?");
                $items_stmt->bind_param("i", $id_commande);
                $items_stmt->execute();
                $items_result = $items_stmt->get_result();

                while ($item = $items_result->fetch_assoc()) {
                    $id_doc = $item['id_doc'];
                    $qty    = $item['quantite'];

                    $update_stock = $conn->prepare("
                        UPDATE documents 
                        SET exemplaires            = exemplaires - ?,
                            exemplaires_disponibles = exemplaires_disponibles - ?
                        WHERE id_doc = ?
                    ");
                    $update_stock->bind_param("iii", $qty, $qty, $id_doc);
                    $update_stock->execute();
                }
            }

            // Reflect new status in the $order array for display
            $order['statut']        = $statut_commande;
            $order['date_paiement'] = date('Y-m-d H:i:s');
        }
    }
}

// ── UI helpers ──────────────────────────────────────────
// Cash on delivery gets a different icon/color/label
$is_cash = ($methode === 'cash');

$statut_label = $is_cash
    ? '🕐 En attente de paiement'
    : '✔️ Payée';

$statut_color = $is_cash ? '#B8924A' : '#2E7D52';

$page_title = $is_cash
    ? 'Commande confirmée !'
    : 'Merci pour votre commande !';

$page_icon  = $is_cash ? '📦' : '🎉';

$page_desc  = $is_cash
    ? 'Votre commande est enregistrée et sera préparée dès que possible. Le paiement s\'effectuera à la réception du colis.'
    : 'Votre paiement a été traité avec succès. Vous recevrez une confirmation par e-mail très bientôt.';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <?php include '../includes/dark_init.php'; ?>
    <title>Commande confirmée — AuraLib</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
    <style>
        body { background: #F5F0E8; font-family: 'Lato', sans-serif; margin: 0; }

        .success-page {
            max-width: 560px;
            margin: 80px auto;
            padding: 0 20px 80px;
            text-align: center;
        }

        .success-icon {
            width: 90px; height: 90px;
            border-radius: 50%;
            background: rgba(46,125,82,0.10);
            border: 2px solid rgba(46,125,82,0.25);
            display: flex; align-items: center; justify-content: center;
            font-size: 42px;
            margin: 0 auto 28px;
        }

        /* Cash delivery gets a warm amber ring instead of green */
        .success-icon.cash {
            background: rgba(184,146,74,0.10);
            border-color: rgba(184,146,74,0.30);
        }

        .success-page h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 40px;
            font-weight: 700;
            color: #1A1008;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .success-page > p {
            font-size: 15px;
            color: #7A6A55;
            line-height: 1.7;
            margin-bottom: 36px;
        }

        /* ── Cash delivery info banner ─────────────────── */
        .cash-notice {
            background: rgba(184,146,74,0.08);
            border: 1px solid rgba(184,146,74,0.30);
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            text-align: left;
        }
        .cash-notice-icon { font-size: 22px; flex-shrink: 0; margin-top: 1px; }
        .cash-notice-text {
            font-size: 13px;
            color: #7A6A55;
            line-height: 1.6;
        }
        .cash-notice-text strong { color: #B8924A; }

        /* ── Receipt card ──────────────────────────────── */
        .receipt-card {
            background: #FFFDF9;
            border: 1px solid #EDE5D4;
            border-radius: 16px;
            padding: 24px 28px;
            text-align: left;
            margin-bottom: 28px;
            box-shadow: 0 4px 20px rgba(44,31,14,0.06);
        }

        .receipt-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 18px;
            font-weight: 700;
            color: #2C1F0E;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #EDE5D4;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            padding: 9px 0;
            border-bottom: 1px dashed #EDE5D4;
            font-size: 13px;
        }
        .receipt-row:last-child { border-bottom: none; }
        .receipt-row .rk { color: #9A8C7E; }
        .receipt-row .rv { font-weight: 700; color: #2C1F0E; }
        .receipt-row .rv.gold {
            font-family: 'Cormorant Garamond', serif;
            font-size: 20px;
            color: #B8924A;
        }
        .receipt-row .rv.green { color: #2E7D52; }
        .receipt-row .rv.amber { color: #B8924A; }

        /* ── Buttons ───────────────────────────────────── */
        .btn-primary {
            display: block;
            width: 100%;
            padding: 15px;
            background: #C4A46B;
            color: #2C1F0E;
            font-family: 'Lato', sans-serif;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            margin-bottom: 10px;
            transition: background 0.2s;
            text-align: center;
        }
        .btn-primary:hover { background: #D4B47B; }

        .btn-secondary {
            display: block;
            width: 100%;
            padding: 13px;
            background: transparent;
            color: #9A8C7E;
            font-size: 13px;
            font-weight: 600;
            border: 1.5px solid #EDE5D4;
            border-radius: 10px;
            text-decoration: none;
            transition: 0.2s;
            text-align: center;
        }
        .btn-secondary:hover { border-color: #C4A46B; color: #B8924A; }
    </style>
</head>
<body>

<div class="success-page">

    <div class="success-icon <?= $is_cash ? 'cash' : '' ?>">
        <?= $page_icon ?>
    </div>

    <h1><?= $page_title ?></h1>
    <p><?= $page_desc ?></p>

    <?php if ($is_cash): ?>
    <!-- Extra reminder banner for cash on delivery -->
    <div class="cash-notice">
        <div class="cash-notice-icon">💡</div>
        <div class="cash-notice-text">
            Préparez le montant exact de <strong><?= number_format($montant, 0, ',', ' ') ?> DA</strong> lors de la réception.
            Le statut de votre commande passera à <strong>Payée</strong> une fois le livreur confirmé.
        </div>
    </div>
    <?php endif; ?>

    <?php if ($order): ?>
    <div class="receipt-card">
        <div class="receipt-title">Récapitulatif de commande</div>

        <div class="receipt-row">
            <span class="rk">Méthode de paiement</span>
            <span class="rv"><?= $is_cash ? '💵 Paiement à la livraison' : '🟡 Baridi Mob / CCP' ?></span>
        </div>
        <div class="receipt-row">
            <span class="rk">Date</span>
            <span class="rv">
                <?= $order['date_paiement']
                    ? date('d/m/Y à H:i', strtotime($order['date_paiement']))
                    : date('d/m/Y à H:i') ?>
            </span>
        </div>
        <div class="receipt-row">
            <span class="rk">Montant <?= $is_cash ? 'à régler' : 'payé' ?></span>
            <span class="rv gold"><?= number_format($montant, 0, ',', ' ') ?> DA</span>
        </div>
        <div class="receipt-row">
            <span class="rk">Statut</span>
            <span class="rv <?= $is_cash ? 'amber' : 'green' ?>">
                <?= $statut_label ?>
            </span>
        </div>
    </div>
    <?php endif; ?>

    <a href="/MEMOIR/client/library.php" class="btn-primary">📚 Retour au catalogue</a>
    <a href="/MEMOIR/commandes/commande_list.php" class="btn-secondary">Voir mes achats</a>

</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>