<?php
session_start();
require_once "../includes/db.php";

// ── Guards ──────────────────────────────────────────────
if (!isset($_GET['id']) || !isset($_GET['total'])) {
    die("<h3 style='color:#B8924A;font-family:serif;text-align:center;margin-top:80px;'>
         Erreur : numéro de commande ou montant manquant.</h3>");
}

$id_commande = (int)$_GET['id'];
$total       = (float)$_GET['total'];
$id_user     = isset($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : 0;

$stmt = $conn->prepare("SELECT * FROM commande WHERE id_commande = ? AND id_user = ?");
$stmt->bind_param("ii", $id_commande, $id_user);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("<h3 style='text-align:center;margin-top:80px;font-family:serif;color:#B8924A;'>
         Commande introuvable ou accès non autorisé.</h3>");
}
?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement — AuraLib</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ── Reset & Base ─────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Lato', sans-serif;
            background: #F5F0E8;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        /* ── Logo strip ───────────────────────────────── */
        .pay-logo {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
            font-weight: 700;
            color: #2C1F0E;
            margin-bottom: 32px;
            letter-spacing: -0.5px;
        }
        .pay-logo span { color: #C4A46B; }

        /* ── Card ─────────────────────────────────────── */
        .pay-card {
            background: #FFFDF9;
            border: 1px solid #EDE5D4;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(44,31,14,0.10);
            width: 100%;
            max-width: 520px;
            overflow: hidden;
        }

        /* ── Step header ──────────────────────────────── */
        .pay-header {
            background: #2C1F0E;
            padding: 28px 36px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .pay-header-icon {
            width: 48px; height: 48px;
            background: rgba(196,164,107,0.15);
            border: 1px solid rgba(196,164,107,0.35);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .pay-header-text h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px;
            font-weight: 700;
            color: #FFFFFF;
            line-height: 1.2;
        }
        .pay-header-text p {
            font-size: 12px;
            color: rgba(255,255,255,0.5);
            margin-top: 3px;
            letter-spacing: 0.3px;
        }

        /* ── Step indicator ───────────────────────────── */
        .step-bar {
            display: flex;
            align-items: center;
            padding: 20px 36px;
            background: #FAF6EF;
            border-bottom: 1px solid #EDE5D4;
            gap: 0;
        }
        .step-dot {
            width: 28px; height: 28px;
            border-radius: 50%;
            background: #EDE5D4;
            color: #9A8C7E;
            font-size: 11px;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            transition: 0.3s;
        }
        .step-dot.active
{
            background: #C4A46B;
            color: #2C1F0E;
        }
        .step-dot.done {
            background: #2E7D52;
            color: white;
        }
        .step-dot.done::after { content: '✓'; font-size: 12px; }
        .step-dot.done .sn { display: none; }
        .step-line {
            flex: 1;
            height: 1px;
            background: #EDE5D4;
        }
        .step-line.done { background: #C4A46B; }
        .step-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #9A8C7E;
            margin-top: 4px;
            text-align: center;
        }
        .step-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }
        .step-wrap .step-label.active { color: #B8924A; }
        .step-wrap .step-label.done   { color: #2E7D52; }

        /* ── Body ─────────────────────────────────────── */
        .pay-body { padding: 32px 36px; }

        /* ── Order summary box ────────────────────────── */
        .order-summary {
            background: #FAF6EF;
            border: 1px solid #EDE5D4;
            border-radius: 12px;
            padding: 20px 22px;
            margin-bottom: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .order-summary .lbl {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #9A8C7E;
            margin-bottom: 6px;
        }
        .order-summary .amount {
            font-family: 'Cormorant Garamond', serif;
            font-size: 36px;
            font-weight: 700;
            color: #B8924A;
            line-height: 1;
            letter-spacing: -0.5px;
        }
        .order-summary .amount span {
            font-size: 16px;
            font-weight: 400;
            color: #9A8C7E;
            margin-left: 4px;
        }
        .order-ref {
            text-align: right;
        }
        .order-ref .lbl { text-align: right; }
        .order-ref .ref {
            font-family: 'Cormorant Garamond', serif;
            font-size: 20px;
            font-weight: 600;
            color: #2C1F0E;
        }

        /* ── Method selector (step 1) ─────────────────── */
        .method-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 20px;
            font-weight: 700;
            color: #1A1008;
            margin-bottom: 16px;
        }

        .method-list { display: flex; flex-direction: column; gap: 10px; margin-bottom: 28px; }

        .method-option {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            border: 1.5px solid #EDE5D4;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.2s;
            background: #FFFDF9;
        }
        .method-option:hover { border-color: #C4A46B; background: #FAF6EF; }
        .method-option.selected {
            border-color: #C4A46B;
            background: rgba(196,164,107,0.06);
        }
        .method-icon-wrap {
            width: 46px; height: 46px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
            background: #F5F0E8;
            flex-shrink: 0;
        }
        .method-info h4 {
            font-size: 14px;
            font-weight: 700;
            color: #2C1F0E;
            margin-bottom: 2px;
        }
        .method-info p {
            font-size: 12px;
            color: #9A8C7E;
        }
        .method-radio {
            margin-left: auto;
            width: 18px; height: 18px;
            accent-color: #C4A46B;
            cursor: pointer;
        }
/* ── Baridi Mob form (step 2) ─────────────────── */
        .baridi-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 18px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            border-radius: 12px;
            margin-bottom: 24px;
        }
        .baridi-logo-icon {
            font-size: 28px;
        }
        .baridi-header-text h3 {
            font-size: 15px;
            font-weight: 700;
            color: #2C1F0E;
        }
        .baridi-header-text p {
            font-size: 11px;
            color: rgba(44,31,14,0.65);
        }

        /* Form fields */
        .field-group { margin-bottom: 18px; }
        .field-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #7A6A55;
            margin-bottom: 7px;
            display: block;
        }
        .field-input {
            width: 100%;
            padding: 13px 16px;
            border: 1.5px solid #EDE5D4;
            border-radius: 10px;
            font-family: 'Lato', sans-serif;
            font-size: 15px;
            color: #2C1F0E;
            background: #FFFDF9;
            outline: none;
            transition: border-color 0.2s;
            letter-spacing: 1px;
        }
        .field-input:focus { border-color: #C4A46B; }
        .field-input.error { border-color: #e74c3c; }

        .field-hint {
            font-size: 11px;
            color: #9A8C7E;
            margin-top: 5px;
        }

        .two-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        /* PIN dots display */
        .pin-display {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin: 8px 0 4px;
        }
        .pin-dot {
            width: 14px; height: 14px;
            border-radius: 50%;
            border: 2px solid #C4A46B;
            background: transparent;
            transition: 0.2s;
        }
        .pin-dot.filled { background: #C4A46B; }

        /* Security note */
        .security-note {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 14px;
            background: rgba(46,125,82,0.06);
            border: 1px solid rgba(46,125,82,0.2);
            border-radius: 8px;
            margin-bottom: 24px;
        }
        .security-note i { color: #2E7D52; font-size: 14px; }
        .security-note span {
            font-size: 12px;
            color: #2E7D52;
            font-weight: 600;
        }
/* ── Confirmation step (step 3) ───────────────── */
        .confirm-visual {
            text-align: center;
            padding: 10px 0 28px;
        }
        .confirm-circle {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: rgba(46,125,82,0.1);
            border: 2px solid rgba(46,125,82,0.3);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
        }
        .confirm-visual h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px;
            font-weight: 700;
            color: #1A1008;
            margin-bottom: 8px;
        }
        .confirm-visual p {
            font-size: 14px;
            color: #7A6A55;
            line-height: 1.6;
        }
        .confirm-receipt {
            background: #FAF6EF;
            border: 1px solid #EDE5D4;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 28px;
        }
        .receipt-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px dashed #EDE5D4;
            font-size: 13px;
        }
        .receipt-row:last-child { border-bottom: none; }
        .receipt-row .rk { color: #9A8C7E; }
        .receipt-row .rv { color: #2C1F0E; font-weight: 700; }
        .receipt-row .rv.gold { color: #B8924A; font-family: 'Cormorant Garamond', serif; font-size: 18px; }

        /* ── Buttons ──────────────────────────────────── */
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
            transition: background 0.2s, transform 0.1s;
            text-align: center;
            text-decoration: none;
        }
        .btn-primary:hover { background: #D4B47B; }
        .btn-primary:active { transform: scale(0.99); }

        .btn-secondary {
            display: block;
            width: 100%;
            padding: 13px;
            background: transparent;
            color: #9A8C7E;
            font-family: 'Lato', sans-serif;
            font-size: 13px;
            font-weight: 600;
            border: 1.5px solid #EDE5D4;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 10px;
            text-align: center;
        }
        .btn-secondary:hover { border-color: #C4A46B; color: #B8924A; }

        .btn-success {
            display: block;
            width: 100%;
            padding: 15px;
            background: #2E7D52;
            color: white;
            font-family: 'Lato', sans-serif;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.2s;
            text-align: center;
            text-decoration: none;
        }
        .btn-success:hover { background: #236040; }

        /* ── Error message ────────────────────────────── */
        .error-msg {
            background: rgba(231,76,60,0.08);
            border: 1px solid rgba(231,76,60,0.3);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 12px;
            color: #c0392b;
            margin-bottom: 16px;
            display: none;
        }
        .error-msg.show { display: block; }
/* ── Loading spinner ──────────────────────────── */
        .spinner {
            display: inline-block;
            width: 16px; height: 16px;
            border: 2px solid rgba(44,31,14,0.3);
            border-top-color: #2C1F0E;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            vertical-align: middle;
            margin-right: 8px;
            display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Footer note ──────────────────────────────── */
        .pay-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 11px;
            color: #9A8C7E;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .pay-footer i { color: #C4A46B; }

        /* ── Responsive ───────────────────────────────── */
        @media (max-width: 540px) {
            .pay-body { padding: 24px 20px; }
            .pay-header { padding: 22px 20px; }
            .step-bar { padding: 16px 20px; }
            .order-summary { flex-direction: column; gap: 14px; align-items: flex-start; }
            .order-ref { text-align: left; }
            .two-fields { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body
    data-id-commande="<?= $id_commande ?>"
    data-total="<?= $total ?>"
    data-date-now="<?= date('d/m/Y à H:i') ?>">

    <!-- Logo -->
    <div class="pay-logo">Aura<span>Lib</span></div>

    <div class="pay-card">

        <!-- Header -->
        <div class="pay-header">
            <div class="pay-header-icon">💳</div>
            <div class="pay-header-text">
                <h2>Paiement sécurisé</h2>
                <p>Commande #<?= $id_commande ?> · AuraLib Library</p>
            </div>
        </div>

        <!-- Step bar -->
        <div class="step-bar" id="stepBar">
            <div class="step-wrap">
                <div class="step-dot active" id="dot1"><span class="sn">1</span></div>
                <div class="step-label active" id="lbl1">Méthode</div>
            </div>
            <div class="step-line" id="line1"></div>
            <div class="step-wrap">
                <div class="step-dot" id="dot2"><span class="sn">2</span></div>
                <div class="step-label" id="lbl2">Informations</div>
            </div>
            <div class="step-line" id="line2"></div>
            <div class="step-wrap">
                <div class="step-dot" id="dot3"><span class="sn">3</span></div>
                <div class="step-label" id="lbl3">Confirmation</div>
            </div>
        </div>

        <!-- Body -->
        <div class="pay-body">

            <!-- Order summary (always visible) -->
            <div class="order-summary">
                <div>
                    <div class="lbl">Total à payer</div>
                    <div class="amount"><?= number_format($total, 0, ',', ' ') ?><span>DA</span></div>
                </div>
                <div class="order-ref">
                    <div class="lbl">Référence</div>
                    <div class="ref">#<?= str_pad($id_commande, 6, '0', STR_PAD_LEFT) ?></div>
                </div>
            </div>

            <!-- ══ STEP 1 — Choose method ══ -->
            <div id="step1">
                <div class="method-title">Choisissez votre méthode de paiement</div>

                <div class="method-list">
                    <label class="method-option selected" id="methodBaridi" onclick="selectMethod('baridi')">
                        <div class="method-icon-wrap">🟡</div>
                        <div class="method-info">
                            <h4>Baridi Mob — CCP / Poste Algérie</h4>
                            <p>Paiement par carte CIB ou Baridi Mob</p>
                        </div>
                        <input type="radio" class="method-radio" name="method" value="baridi" checked>
                    </label>
<label class="method-option" id="methodCash" onclick="selectMethod('cash')">
                        <div class="method-icon-wrap">💵</div>
                        <div class="method-info">
                            <h4>Paiement à la livraison</h4>
                            <p>Payez en espèces à la réception du colis</p>
                        </div>
                        <input type="radio" class="method-radio" name="method" value="cash">
                    </label>
                </div>

                <button class="btn-primary" onclick="goStep2()">
                    Continuer &nbsp;→
                </button>
            </div>

            <!-- ══ STEP 2 — Baridi Mob form ══ -->
            <div id="step2" style="display:none;">

                <!-- Baridi header -->
                <div class="baridi-header">
                    <div class="baridi-logo-icon">🏦</div>
                    <div class="baridi-header-text">
                        <h3>Baridi Mob / CCP</h3>
                        <p>Algérie Poste · Paiement électronique</p>
                    </div>
                </div>

                <!-- Cash fallback message -->
                <div id="cashMsg" style="display:none; text-align:center; padding: 20px 0 10px;">
                    <div style="font-size:48px; margin-bottom:14px;">📦</div>
                    <p style="font-family:'Cormorant Garamond',serif; font-size:20px; font-weight:700; color:#1A1008; margin-bottom:8px;">Paiement à la livraison</p>
                    <p style="font-size:13px; color:#7A6A55; line-height:1.7;">Vous réglez le montant de <strong style="color:#B8924A;"><?= number_format($total,0,',',' ') ?> DA</strong> directement au livreur lors de la réception de votre commande.</p>
                    <br>
                </div>

                <!-- Baridi form -->
                <div id="baridiForm">
                    <div id="errorMsg" class="error-msg">
                        <i class="fa fa-exclamation-circle"></i> Veuillez vérifier les informations saisies.
                    </div>

                    <!-- Card number -->
                    <div class="field-group">
                        <label class="field-label" for="cardNum">Numéro de carte CIB / Baridi</label>
                        <input type="text" id="cardNum" class="field-input" placeholder="0000 0000 0000 0000"
                               maxlength="19" oninput="formatCard(this)" inputmode="numeric">
                        <div class="field-hint">16 chiffres — imprimés sur votre carte</div>
                    </div>

                    <!-- Name -->
                    <div class="field-group">
                        <label class="field-label" for="cardName">Nom du titulaire</label>
                        <input type="text" id="cardName" class="field-input" placeholder="NOM PRÉNOM" style="text-transform:uppercase;">
                    </div>

                    <!-- Expiry + CCV -->
                    <div class="two-fields">
                        <div class="field-group">
                            <label class="field-label" for="cardExp">Date d'expiration</label>
                            <input type="text" id="cardExp" class="field-input" placeholder="MM / AA"
                                   maxlength="7" oninput="formatExpiry(this)" inputmode="numeric">
                        </div>
                        <div class="field-group">
                            <label class="field-label" for="cardCvv">Code CVV</label>
                            <input type="password" id="cardCvv" class="field-input" placeholder="• • •"
                                   maxlength="3" inputmode="numeric">
                            <div class="field-hint">3 chiffres au dos</div>
                        </div>
                    </div>
<!-- PIN -->
                    <div class="field-group">
                        <label class="field-label" for="cardPin">Code PIN Baridi Mob (4 chiffres)</label>
                        <input type="password" id="cardPin" class="field-input" placeholder="• • • •"
                               maxlength="4" inputmode="numeric" oninput="updatePinDots(this)">
                        <div class="pin-display">
                            <div class="pin-dot" id="pd1"></div>
                            <div class="pin-dot" id="pd2"></div>
                            <div class="pin-dot" id="pd3"></div>
                            <div class="pin-dot" id="pd4"></div>
                        </div>
                        <div class="field-hint">Code secret à 4 chiffres de votre application Baridi Mob</div>
                    </div>
                </div>

                <!-- Security note -->
                <div class="security-note">
                    <i class="fa fa-shield-alt"></i>
                    <span>Connexion chiffrée SSL 256 bits — vos données sont protégées</span>
                </div>

                <button class="btn-primary" onclick="goStep3()">
                    <span class="spinner" id="spinner"></span>
                    Valider le paiement
                </button>
                <button class="btn-secondary" onclick="goStep1()">← Retour</button>
            </div>

            <!-- ══ STEP 3 — Confirmation ══ -->
            <div id="step3" style="display:none;">
                <div class="confirm-visual">
                    <div class="confirm-circle">✅</div>
                    <h3>Paiement confirmé !</h3>
                    <p>Votre commande a été traitée avec succès.<br>Un reçu vous sera envoyé par e-mail.</p>
                </div>

                <div class="confirm-receipt">
                    <div class="receipt-row">
                        <span class="rk">Commande</span>
                        <span class="rv">#<?= str_pad($id_commande, 6, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="receipt-row">
                        <span class="rk">Méthode</span>
                        <span class="rv" id="receiptMethod">—</span>
                    </div>
                    <div class="receipt-row">
                        <span class="rk">Date</span>
                        <span class="rv"><?= date('d/m/Y à H:i') ?></span>
                    </div>
                    <div class="receipt-row">
                        <span class="rk">Montant payé</span>
                        <span class="rv gold"><?= number_format($total, 0, ',', ' ') ?> DA</span>
                    </div>
                    <div class="receipt-row">
                        <span class="rk">Statut</span>
                        <span class="rv" style="color:#2E7D52;">✔️ Validé</span>
                    </div>
                </div>

                <!-- This actually submits to finaliser_paiement.php -->
                <form method="POST" action="finaliser_paiement.php" id="finalForm">
                    <input type="hidden" name="id_commande" value="<?= $id_commande ?>">
                    <input type="hidden" name="montant" value="<?= $total ?>">
                    <input type="hidden" name="methode" id="finalMethod" value="">
                    <button type="submit" class="btn-success">
                        Finaliser &amp; accéder à mes achats &nbsp;→
                    </button>
                </form>

                <a href="/MEMOIR/client/library.php" class="btn-secondary" style="margin-top:10px; display:block; text-align:center; text-decoration:none;">
                    Retour au catalogue
                </a>
            </div>

        </div><!-- /pay-body -->
    </div><!-- /pay-card -->

    <div class="pay-footer">
        <i class="fa fa-lock"></i>
        Paiement 100% sécurisé · AuraLib © <?= date('Y') ?>
    </div>
<script>
    // PHP values passed via data attributes — no PHP inside JS
    const PAGE = {
        idCommande: parseInt(document.body.dataset.idCommande, 10),
        total:      parseFloat(document.body.dataset.total),
        dateNow:    document.body.dataset.dateNow
    };

    let selectedMethod = 'baridi';

    function selectMethod(m) {
        selectedMethod = m;
        document.getElementById('methodBaridi').classList.toggle('selected', m === 'baridi');
        document.getElementById('methodCash').classList.toggle('selected', m === 'cash');
    }

    function goStep2() {
        setStep(1, 2);
        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'block';

        if (selectedMethod === 'cash') {
            document.getElementById('baridiForm').style.display = 'none';
            document.getElementById('cashMsg').style.display = 'block';
        } else {
            document.getElementById('baridiForm').style.display = 'block';
            document.getElementById('cashMsg').style.display = 'none';
        }
    }

    function goStep1() {
        setStep(2, 1);
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step1').style.display = 'block';
    }

    function goStep3() {
        // Validate if baridi
        if (selectedMethod === 'baridi') {
            const card = document.getElementById('cardNum').value.replace(/\s/g,'');
            const name = document.getElementById('cardName').value.trim();
            const exp  = document.getElementById('cardExp').value.trim();
            const cvv  = document.getElementById('cardCvv').value.trim();
            const pin  = document.getElementById('cardPin').value.trim();
            const err  = document.getElementById('errorMsg');

            if (card.length !== 16 || name === '' || exp.length < 5 || cvv.length !== 3 || pin.length !== 4) {
                err.innerHTML = '<i class="fa fa-exclamation-circle"></i> Veuillez remplir tous les champs correctement.';
                return;
            }
            err.classList.remove('show');
        }

        // Show spinner briefly
        const spinner = document.getElementById('spinner');
        spinner.style.display = 'inline-block';
        setTimeout(() => {
            spinner.style.display = 'none';
            setStep(2, 3);
            document.getElementById('step2').style.display = 'none';
            document.getElementById('step3').style.display = 'block';

            const label = selectedMethod === 'baridi' ? 'Baridi Mob / CCP' : 'Paiement à la livraison';
            document.getElementById('receiptMethod').textContent = label;
            document.getElementById('finalMethod').value = selectedMethod;
        }, 1400);
    }

    function setStep(from, to) {
        // Mark "from" as done
        const dotFrom = document.getElementById('dot' + from);
        dotFrom.classList.remove('active');
        dotFrom.classList.add('done');
        dotFrom.innerHTML = '';

        const lblFrom = document.getElementById('lbl' + from);
        lblFrom.classList.remove('active');
        lblFrom.classList.add('done');

        if (from < to) {
            const line = document.getElementById('line' + from);
            if (line) line.classList.add('done');
        }

        // Activate "to"
        const dotTo = document.getElementById('dot' + to);
        dotTo.classList.add('active');
        dotTo.innerHTML = '<span class="sn">' + to + '</span>';

        const lblTo = document.getElementById('lbl' + to);
        lblTo.classList.add('active');
    }

    /* Card number formatting: 0000 0000 0000 0000 */
    function formatCard(input) {
        let v = input.value.replace(/\D/g, '').substring(0, 16);
        input.value = v.replace(/(.{4})/g, '$1 ').trim();
    }
/* Expiry formatting: MM / AA */
    function formatExpiry(input) {
        let v = input.value.replace(/\D/g, '').substring(0, 4);
        if (v.length >= 3) v = v.substring(0,2) + ' / ' + v.substring(2);
        input.value = v;
    }

    /* PIN dot indicator */
    function updatePinDots(input) {
        const len = input.value.length;
        for (let i = 1; i <= 4; i++) {
            document.getElementById('pd' + i).classList.toggle('filled', i <= len);
        }
    }
</script>

</body>
</html>
