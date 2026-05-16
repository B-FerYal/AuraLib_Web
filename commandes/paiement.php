<?php
session_start();
require_once "../includes/db.php";
include_once '../includes/languages.php';

// ── نصوص الصفحة حسب اللغة ──────────────────────────────
$pg = [
    'fr' => [
        'page_title'      => 'Paiement — AuraLib',
        'header_title'    => 'Paiement sécurisé',
        'header_sub'      => 'AuraLib Library',
        'step1_lbl'       => 'Méthode',
        'step2_lbl'       => 'Informations',
        'step3_lbl'       => 'Confirmation',
        'total_lbl'       => 'Total à payer',
        'method_title'    => 'Choisissez votre méthode de paiement',
        'baridi_title'    => 'Baridi Mob — CCP / Poste Algérie',
        'baridi_sub'      => 'Paiement par carte CIB ou Baridi Mob',
        'cash_title'      => 'Paiement à la livraison',
        'cash_sub'        => 'Payez en espèces à la réception du colis',
        'continue'        => 'Continuer',
        'baridi_h3'       => 'Baridi Mob / CCP',
        'baridi_p'        => 'Algérie Poste · Paiement électronique',
        'cash_msg_title'  => 'Paiement à la livraison',
        'cash_msg_body'   => 'Vous réglez le montant de',
        'cash_msg_body2'  => 'directement au livreur lors de la réception de votre commande.',
        'lbl_card'        => 'Numéro de carte CIB / Baridi',
        'hint_card'       => '16 chiffres — imprimés sur votre carte',
        'lbl_name'        => 'Nom du titulaire',
        'ph_name'         => 'NOM PRÉNOM',
        'lbl_exp'         => "Date d'expiration",
        'lbl_cvv'         => 'Code CVV',
        'hint_cvv'        => '3 chiffres au dos',
        'ssl_note'        => 'Connexion chiffrée SSL 256 bits — vos données sont protégées',
        'validate'        => 'Valider le paiement',
        'back'            => '← Retour',
        'confirmed_title' => 'Paiement confirmé !',
        'confirmed_sub'   => 'Votre commande a été traitée avec succès.<br>Un reçu vous sera envoyé par e-mail.',
        'rk_method'       => 'Méthode',
        'rk_date'         => 'Date',
        'rk_amount'       => 'Montant payé',
        'rk_status'       => 'Statut',
        'rv_status'       => '✔️ Validé',
        'finalize'        => 'Finaliser &amp; accéder à mes achats',
        'back_catalogue'  => 'Retour au catalogue',
        'footer_note'     => 'Paiement 100% sécurisé · AuraLib ©',
        'err_fields'      => "Veuillez remplir tous les champs correctement.",
        'err_month'       => "Le mois d'expiration doit être compris entre 01 et 12.",
        'err_year'        => "La carte est expirée. L'année doit être 26 ou plus (ex: 26, 27, 28…).",
        'label_baridi'    => 'Baridi Mob / CCP',
        'label_cash'      => 'Paiement à la livraison',
    ],
    'en' => [
        'page_title'      => 'Payment — AuraLib',
        'header_title'    => 'Secure payment',
        'header_sub'      => 'AuraLib Library',
        'step1_lbl'       => 'Method',
        'step2_lbl'       => 'Information',
        'step3_lbl'       => 'Confirmation',
        'total_lbl'       => 'Total to pay',
        'method_title'    => 'Choose your payment method',
        'baridi_title'    => 'Baridi Mob — CCP / Poste Algérie',
        'baridi_sub'      => 'Pay by CIB or Baridi Mob card',
        'cash_title'      => 'Cash on delivery',
        'cash_sub'        => 'Pay in cash upon receiving the package',
        'continue'        => 'Continue',
        'baridi_h3'       => 'Baridi Mob / CCP',
        'baridi_p'        => 'Algérie Poste · Electronic payment',
        'cash_msg_title'  => 'Cash on delivery',
        'cash_msg_body'   => 'You will pay the amount of',
        'cash_msg_body2'  => 'directly to the delivery person upon receiving your order.',
        'lbl_card'        => 'CIB / Baridi card number',
        'hint_card'       => '16 digits — printed on your card',
        'lbl_name'        => 'Cardholder name',
        'ph_name'         => 'FIRST LAST',
        'lbl_exp'         => 'Expiry date',
        'lbl_cvv'         => 'CVV code',
        'hint_cvv'        => '3 digits on the back',
        'ssl_note'        => 'SSL 256-bit encrypted connection — your data is protected',
        'validate'        => 'Confirm payment',
        'back'            => '← Back',
        'confirmed_title' => 'Payment confirmed!',
        'confirmed_sub'   => 'Your order has been processed successfully.<br>A receipt will be sent to you by email.',
        'rk_method'       => 'Method',
        'rk_date'         => 'Date',
        'rk_amount'       => 'Amount paid',
        'rk_status'       => 'Status',
        'rv_status'       => '✔️ Validated',
        'finalize'        => 'Finalize &amp; access my purchases',
        'back_catalogue'  => 'Back to catalogue',
        'footer_note'     => '100% secure payment · AuraLib ©',
        'err_fields'      => 'Please fill in all fields correctly.',
        'err_month'       => 'Expiry month must be between 01 and 12.',
        'err_year'        => 'Card is expired. Year must be 26 or later (e.g. 26, 27, 28…).',
        'label_baridi'    => 'Baridi Mob / CCP',
        'label_cash'      => 'Cash on delivery',
    ],
    'ar' => [
        'page_title'      => 'الدفع — AuraLib',
        'header_title'    => 'دفع آمن',
        'header_sub'      => 'AuraLib Library',
        'step1_lbl'       => 'الطريقة',
        'step2_lbl'       => 'المعلومات',
        'step3_lbl'       => 'التأكيد',
        'total_lbl'       => 'المبلغ الإجمالي',
        'method_title'    => 'اختر طريقة الدفع',
        'baridi_title'    => 'Baridi Mob — CCP / Poste Algérie',
        'baridi_sub'      => 'الدفع ببطاقة CIB أو Baridi Mob',
        'cash_title'      => 'الدفع عند الاستلام',
        'cash_sub'        => 'ادفع نقداً عند استلام الطرد',
        'continue'        => 'متابعة',
        'baridi_h3'       => 'Baridi Mob / CCP',
        'baridi_p'        => 'Algérie Poste · دفع إلكتروني',
        'cash_msg_title'  => 'الدفع عند الاستلام',
        'cash_msg_body'   => 'ستدفع مبلغ',
        'cash_msg_body2'  => 'مباشرةً للمندوب عند استلام طلبك.',
        'lbl_card'        => 'رقم بطاقة CIB / Baridi',
        'hint_card'       => '16 رقماً — مطبوعة على بطاقتك',
        'lbl_name'        => 'اسم حامل البطاقة',
        'ph_name'         => 'الاسم الكامل',
        'lbl_exp'         => 'تاريخ الانتهاء',
        'lbl_cvv'         => 'رمز CVV',
        'hint_cvv'        => '3 أرقام على ظهر البطاقة',
        'ssl_note'        => 'اتصال مشفر SSL 256 بت — بياناتك محمية',
        'validate'        => 'تأكيد الدفع',
        'back'            => 'رجوع →',
        'confirmed_title' => 'تم تأكيد الدفع!',
        'confirmed_sub'   => 'تمت معالجة طلبك بنجاح.<br>سيتم إرسال إيصال إلى بريدك الإلكتروني.',
        'rk_method'       => 'الطريقة',
        'rk_date'         => 'التاريخ',
        'rk_amount'       => 'المبلغ المدفوع',
        'rk_status'       => 'الحالة',
        'rv_status'       => '✔️ مُوثَّق',
        'finalize'        => 'إتمام &amp; الوصول لمشترياتي',
        'back_catalogue'  => 'العودة للكتالوج',
        'footer_note'     => 'دفع آمن 100% · AuraLib ©',
        'err_fields'      => 'يرجى ملء جميع الحقول بشكل صحيح.',
        'err_month'       => 'يجب أن يكون شهر الانتهاء بين 01 و 12.',
        'err_year'        => 'البطاقة منتهية الصلاحية. يجب أن تكون السنة 26 أو أحدث.',
        'label_baridi'    => 'Baridi Mob / CCP',
        'label_cash'      => 'الدفع عند الاستلام',
    ],
];
$p     = $pg[$lang] ?? $pg['fr'];
$isRtl = ($lang === 'ar');

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
<html lang="<?= $lang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <?php include '../includes/dark_init.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $p['page_title'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Lato:wght@300;400;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
    <script>
    (function(){
        if(localStorage.getItem('auralib_theme')==='dark')
            document.documentElement.classList.add('dark');
    })();
    </script>
    <style>
        /* ── Reset & Base ─────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "'Lato', sans-serif" ?>;
            background: #F5F0E8;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
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
            flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
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
            font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "'Cormorant Garamond', serif" ?>;
            font-size: 22px;
            font-weight: 700;
            color: #FFFFFF;
            line-height: 1.2;
            text-align: <?= $isRtl ? 'right' : 'left' ?>;
        }
        .pay-header-text p {
            font-size: 12px;
            color: rgba(255,255,255,0.5);
            margin-top: 3px;
            letter-spacing: 0.3px;
            text-align: <?= $isRtl ? 'right' : 'left' ?>;
        }

        /* ── Step indicator ───────────────────────────── */
        .step-bar {
            display: flex;
            align-items: center;
            padding: 20px 36px;
            background: #FAF6EF;
            border-bottom: 1px solid #EDE5D4;
            gap: 0;
            flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
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
        .step-dot.active {
            background: #C4A46B;
            color: #2C1F0E;
        }
        .step-dot.done {
            background: #2E7D52;
            color: white;
        }
        .step-line {
            flex: 1;
            height: 1px;
            background: #EDE5D4;
        }
        .step-line.done { background: #C4A46B; }
        .step-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: <?= $isRtl ? '0' : '1px' ?>;
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
            padding: 24px;
            margin-bottom: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
        }
        .order-summary .lbl {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: <?= $isRtl ? '0' : '1.5px' ?>;
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
        .order-ref { text-align: <?= $isRtl ? 'left' : 'right' ?>; }
        .order-ref .lbl { text-align: <?= $isRtl ? 'left' : 'right' ?>; }
        .order-ref .ref {
            font-family: 'Cormorant Garamond', serif;
            font-size: 20px;
            font-weight: 600;
            color: #2C1F0E;
        }

        /* ── Method selector (step 1) ─────────────────── */
        .method-title {
            font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "'Cormorant Garamond', serif" ?>;
            font-size: 20px;
            font-weight: 700;
            color: #1A1008;
            margin-bottom: 16px;
            text-align: <?= $isRtl ? 'right' : 'left' ?>;
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
            flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
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
            text-align: <?= $isRtl ? 'right' : 'left' ?>;
        }
        .method-info p {
            font-size: 12px;
            color: #9A8C7E;
            text-align: <?= $isRtl ? 'right' : 'left' ?>;
        }
        .method-radio {
            margin-<?= $isRtl ? 'right' : 'left' ?>: auto;
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
            flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
        }
        .baridi-logo-icon { font-size: 28px; }
        .baridi-header-text h3 {
            font-size: 15px;
            font-weight: 700;
            color: #2C1F0E;
            text-align: <?= $isRtl ? 'right' : 'left' ?>;
        }
        .baridi-header-text p {
            font-size: 11px;
            color: rgba(44,31,14,0.65);
            text-align: <?= $isRtl ? 'right' : 'left' ?>;
        }

        /* Form fields */
        .field-group { margin-bottom: 18px; }
        .field-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: <?= $isRtl ? '0' : '1px' ?>;
            text-transform: uppercase;
            color: #7A6A55;
            margin-bottom: 7px;
            display: block;
            text-align: <?= $isRtl ? 'right' : 'left' ?>;
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
            direction: ltr; /* card numbers always LTR */
        }
        .field-input:focus { border-color: #C4A46B; }
        .field-input.error { border-color: #e74c3c; }
        .field-hint {
            font-size: 11px;
            color: #9A8C7E;
            margin-top: 5px;
            text-align: <?= $isRtl ? 'right' : 'left' ?>;
        }
        .two-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

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
            flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
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
            font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "'Cormorant Garamond', serif" ?>;
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
            flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
        }
        .receipt-row:last-child { border-bottom: none; }
        .receipt-row .rk { color: #9A8C7E; }
        .receipt-row .rv { color: #2C1F0E; font-weight: 700; }
        .receipt-row .rv.gold {
            color: #B8924A;
            font-family: 'Cormorant Garamond', serif;
            font-size: 18px;
        }

        /* ── Buttons ──────────────────────────────────── */
        .btn-primary {
            display: block;
            width: 100%;
            padding: 15px;
            background: #C4A46B;
            color: #2C1F0E;
            font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "'Lato', sans-serif" ?>;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: <?= $isRtl ? '0' : '1.5px' ?>;
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
            font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "'Lato', sans-serif" ?>;
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
            font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "'Lato', sans-serif" ?>;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: <?= $isRtl ? '0' : '1.5px' ?>;
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
            text-align: <?= $isRtl ? 'right' : 'left' ?>;
        }
        .error-msg.show { display: block; }

        /* ── Loading spinner ──────────────────────────── */
        .spinner {
            display: none;
            width: 16px; height: 16px;
            border: 2px solid rgba(44,31,14,0.3);
            border-top-color: #2C1F0E;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            vertical-align: middle;
            margin-right: 8px;
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
            flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
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
                <h2><?= $p['header_title'] ?></h2>
                <p><?= $p['header_sub'] ?></p>
            </div>
        </div>

        <!-- Step bar -->
        <div class="step-bar" id="stepBar">
            <div class="step-wrap">
                <div class="step-dot active" id="dot1"><span class="sn">1</span></div>
                <div class="step-label active" id="lbl1"><?= $p['step1_lbl'] ?></div>
            </div>
            <div class="step-line" id="line1"></div>
            <div class="step-wrap">
                <div class="step-dot" id="dot2"><span class="sn">2</span></div>
                <div class="step-label" id="lbl2"><?= $p['step2_lbl'] ?></div>
            </div>
            <div class="step-line" id="line2"></div>
            <div class="step-wrap">
                <div class="step-dot" id="dot3"><span class="sn">3</span></div>
                <div class="step-label" id="lbl3"><?= $p['step3_lbl'] ?></div>
            </div>
        </div>

        <!-- Body -->
        <div class="pay-body">

            <!-- Order summary (always visible) -->
            <div class="order-summary">
                <div>
                    <div class="lbl"><?= $p['total_lbl'] ?></div>
                    <div class="amount"><?= number_format($total, 0, ',', ' ') ?><span>DA</span></div>
                </div>
            </div>

            <!-- ══ STEP 1 — Choose method ══ -->
            <div id="step1">
                <div class="method-title"><?= $p['method_title'] ?></div>

                <div class="method-list">
                    <label class="method-option selected" id="methodBaridi" onclick="selectMethod('baridi')">
                        <div class="method-icon-wrap">🟡</div>
                        <div class="method-info">
                            <h4><?= $p['baridi_title'] ?></h4>
                            <p><?= $p['baridi_sub'] ?></p>
                        </div>
                        <input type="radio" class="method-radio" name="method" value="baridi" checked>
                    </label>

                    <label class="method-option" id="methodCash" onclick="selectMethod('cash')">
                        <div class="method-icon-wrap">💵</div>
                        <div class="method-info">
                            <h4><?= $p['cash_title'] ?></h4>
                            <p><?= $p['cash_sub'] ?></p>
                        </div>
                        <input type="radio" class="method-radio" name="method" value="cash">
                    </label>
                </div>

                <button class="btn-primary" onclick="goStep2()">
                    <?= $p['continue'] ?> &nbsp;→
                </button>
            </div>
            <!-- ══ END STEP 1 ══ -->

            <!-- ══ STEP 2 — Baridi Mob form / Cash message ══ -->
            <div id="step2" style="display:none;">

                <!-- Baridi header (only shown for baridi) -->
                <div id="baridiHeader" class="baridi-header">
                    <div class="baridi-logo-icon">🏦</div>
                    <div class="baridi-header-text">
                        <h3><?= $p['baridi_h3'] ?></h3>
                        <p><?= $p['baridi_p'] ?></p>
                    </div>
                </div>

                <!-- Cash fallback message -->
                <div id="cashMsg" style="display:none; text-align:center; padding: 20px 0 10px;">
                    <div style="font-size:48px; margin-bottom:14px;">📦</div>
                    <p style="font-family:<?= $isRtl ? "'Tajawal'" : "'Cormorant Garamond'" ?>,serif; font-size:20px; font-weight:700; color:#1A1008; margin-bottom:8px;"><?= $p['cash_msg_title'] ?></p>
                    <p style="font-size:13px; color:#7A6A55; line-height:1.7;">
                        <?= $p['cash_msg_body'] ?>
                        <strong style="color:#B8924A;"><?= number_format($total, 0, ',', ' ') ?> DA</strong>
                        <?= $p['cash_msg_body2'] ?>
                    </p>
                    <br>
                </div>

                <!-- Baridi form fields -->
                <div id="baridiForm">
                    <div id="errorMsg" class="error-msg">
                        <i class="fa fa-exclamation-circle"></i> <?= $p['err_fields'] ?>
                    </div>

                    <!-- Card number -->
                    <div class="field-group">
                        <label class="field-label" for="cardNum"><?= $p['lbl_card'] ?></label>
                        <input type="text" id="cardNum" class="field-input" placeholder="0000 0000 0000 0000"
                               maxlength="19" oninput="formatCard(this)" inputmode="numeric">
                        <div class="field-hint"><?= $p['hint_card'] ?></div>
                    </div>

                    <!-- Name -->
                    <div class="field-group">
                        <label class="field-label" for="cardName"><?= $p['lbl_name'] ?></label>
                        <input type="text" id="cardName" class="field-input" placeholder="<?= $p['ph_name'] ?>" style="text-transform:uppercase;">
                    </div>

                    <!-- Expiry + CVV -->
                    <div class="two-fields">
                        <div class="field-group">
                            <label class="field-label" for="cardExp"><?= $p['lbl_exp'] ?></label>
                            <input type="text" id="cardExp" class="field-input" placeholder="MM / AA"
                                   maxlength="7" oninput="formatExpiry(this)" inputmode="numeric">
                        </div>
                        <div class="field-group">
                            <label class="field-label" for="cardCvv"><?= $p['lbl_cvv'] ?></label>
                            <input type="password" id="cardCvv" class="field-input" placeholder="• • •"
                                   maxlength="3" inputmode="numeric">
                            <div class="field-hint"><?= $p['hint_cvv'] ?></div>
                        </div>
                    </div>
                </div>
                <!-- ══ END baridiForm ══ -->

                <!-- Security note -->
                <div class="security-note" id="securityNote">
                    <i class="fa fa-shield-alt"></i>
                    <span><?= $p['ssl_note'] ?></span>
                </div>

                <button class="btn-primary" onclick="goStep3()">
                    <span class="spinner" id="spinner"></span>
                    <?= $p['validate'] ?>
                </button>
                <button class="btn-secondary" onclick="goStep1()"><?= $p['back'] ?></button>

            </div>
            <!-- ══ END STEP 2 ══ -->

            <!-- ══ STEP 3 — Confirmation ══ -->
            <div id="step3" style="display:none;">
                <div class="confirm-visual">
                    <div class="confirm-circle">✅</div>
                    <h3><?= $p['confirmed_title'] ?></h3>
                    <p><?= $p['confirmed_sub'] ?></p>
                </div>

                <div class="confirm-receipt">
                    <div class="receipt-row">
                        <span class="rk"><?= $p['rk_method'] ?></span>
                        <span class="rv" id="receiptMethod">—</span>
                    </div>
                    <div class="receipt-row">
                        <span class="rk"><?= $p['rk_date'] ?></span>
                        <span class="rv"><?= date('d/m/Y à H:i') ?></span>
                    </div>
                    <div class="receipt-row">
                        <span class="rk"><?= $p['rk_amount'] ?></span>
                        <span class="rv gold"><?= number_format($total, 0, ',', ' ') ?> DA</span>
                    </div>
                   <div class="receipt-row">
    <span class="rk"><?= $p['rk_status'] ?></span>
    <span class="rv" id="receiptStatus"></span>
</div>
                </div>

                <!-- This actually submits to finaliser_paiement.php -->
                <form method="POST" action="finaliser_paiement.php" id="finalForm">
                    <input type="hidden" name="id_commande" value="<?= $id_commande ?>">
                    <input type="hidden" name="montant" value="<?= $total ?>">
                    <input type="hidden" name="methode" id="finalMethod" value="">
                    <button type="submit" class="btn-success">
                        <?= $p['finalize'] ?> &nbsp;→
                    </button>
                </form>

                <a href="/MEMOIR/client/library.php" class="btn-secondary" style="margin-top:10px; display:block; text-align:center; text-decoration:none;">
                    <?= $p['back_catalogue'] ?>
                </a>
            </div>
            <!-- ══ END STEP 3 ══ -->

        </div><!-- /pay-body -->
    </div><!-- /pay-card -->

    <div class="pay-footer">
        <i class="fa fa-lock"></i>
        <?= $p['footer_note'] ?> <?= date('Y') ?>
    </div>

    <script>
        // PHP values passed via data attributes — no PHP inside JS
        const PAGE = {
            idCommande: parseInt(document.body.dataset.idCommande, 10),
            total:      parseFloat(document.body.dataset.total),
            dateNow:    document.body.dataset.dateNow
        };

        // Translated labels for JS
        const LABELS = {
            baridi  : <?= json_encode($p['label_baridi']) ?>,
            cash    : <?= json_encode($p['label_cash']) ?>,
            errFields: <?= json_encode($p['err_fields']) ?>,
            errMonth : <?= json_encode($p['err_month']) ?>,
            errYear  : <?= json_encode($p['err_year']) ?>,
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
                document.getElementById('baridiHeader').style.display = 'none';
                document.getElementById('baridiForm').style.display  = 'none';
                document.getElementById('securityNote').style.display = 'none';
                document.getElementById('cashMsg').style.display = 'block';
            } else {
                document.getElementById('baridiHeader').style.display = 'flex';
                document.getElementById('baridiForm').style.display  = 'block';
                document.getElementById('securityNote').style.display = 'flex';
                document.getElementById('cashMsg').style.display = 'none';
            }
        }

        function goStep1() {
            setStep(2, 1);
            document.getElementById('step2').style.display = 'none';
            document.getElementById('step1').style.display = 'block';
        }

        function goStep3() {
            // Validate only if baridi
            if (selectedMethod === 'baridi') {
                const card = document.getElementById('cardNum').value.replace(/\s/g, '');
                const name = document.getElementById('cardName').value.trim();
                const exp  = document.getElementById('cardExp').value.trim();
                const cvv  = document.getElementById('cardCvv').value.trim();
                const err  = document.getElementById('errorMsg');

                // ── Basic empty-field check ──
                if (card.length !== 16 || name === '' || exp.length < 5 || cvv.length !== 3) {
                    err.innerHTML = '<i class="fa fa-exclamation-circle"></i> ' + LABELS.errFields;
                    err.classList.add('show');
                    document.getElementById('cardExp').classList.toggle('error', exp.length < 5);
                    return;
                }

                // ── Expiry: parse MM and AA ──
                const expParts = exp.replace(/\s/g, '').split('/');
                const expMonth = parseInt(expParts[0], 10);
                const expYear  = parseInt(expParts[1], 10);

                if (isNaN(expMonth) || expMonth < 1 || expMonth > 12) {
                    err.innerHTML = '<i class="fa fa-exclamation-circle"></i> ' + LABELS.errMonth;
                    err.classList.add('show');
                    document.getElementById('cardExp').classList.add('error');
                    return;
                }

                if (isNaN(expYear) || expYear < 26) {
                    err.innerHTML = '<i class="fa fa-exclamation-circle"></i> ' + LABELS.errYear;
                    err.classList.add('show');
                    document.getElementById('cardExp').classList.add('error');
                    return;
                }

                document.getElementById('cardExp').classList.remove('error');
                err.classList.remove('show');
            }

            // Show spinner briefly then go to step 3
            const spinner = document.getElementById('spinner');
            spinner.style.display = 'inline-block';

            setTimeout(() => {
                spinner.style.display = 'none';
                setStep(2, 3);
                document.getElementById('step2').style.display = 'none';
                document.getElementById('step3').style.display = 'block';

                const label = selectedMethod === 'baridi' ? LABELS.baridi : LABELS.cash;
                document.getElementById('receiptMethod').textContent = label;
const statusEl = document.getElementById('receiptStatus');

if (selectedMethod === 'baridi') {
    statusEl.innerHTML = '✔️ Validé';
    statusEl.style.color = '#2E7D52';
} else {
    statusEl.innerHTML = '⏳ En attente de paiement';
    statusEl.style.color = '#B8924A';
}


                document.getElementById('finalMethod').value = selectedMethod;
            }, 1400);
        }

        // ── FIX: Proper setStep that handles both forward AND backward navigation ──
        function setStep(from, to) {
            const goingForward = to > from;

            const dotFrom = document.getElementById('dot' + from);
            const lblFrom = document.getElementById('lbl' + from);

            dotFrom.classList.remove('active');
            lblFrom.classList.remove('active');

            if (goingForward) {
                // Mark previous step as done (green checkmark)
                dotFrom.classList.add('done');
                dotFrom.innerHTML = '✓';
                lblFrom.classList.add('done');

                const line = document.getElementById('line' + from);
                if (line) line.classList.add('done');
            } else {
                // Going backward: reset previous step to plain numbered state
                dotFrom.classList.remove('done');
                dotFrom.innerHTML = '<span class="sn">' + from + '</span>';
                lblFrom.classList.remove('done');

                const line = document.getElementById('line' + to);
                if (line) line.classList.remove('done');
            }

            // Activate destination step
            const dotTo = document.getElementById('dot' + to);
            const lblTo = document.getElementById('lbl' + to);

            dotTo.classList.remove('done');
            dotTo.classList.add('active');
            dotTo.innerHTML = '<span class="sn">' + to + '</span>';

            lblTo.classList.remove('done');
            lblTo.classList.add('active');
        }

        /* Card number formatting: 0000 0000 0000 0000 */
        function formatCard(input) {
            let v = input.value.replace(/\D/g, '').substring(0, 16);
            input.value = v.replace(/(.{4})/g, '$1 ').trim();
        }

        /* Expiry formatting: MM / AA
           - mois : bloqué à 12 max (impossible d'écrire 45 etc.)
           - année: validée à la soumission (>= 26) */
        function formatExpiry(input) {
            let v = input.value.replace(/\D/g, '').substring(0, 4);

            if (v.length >= 1) {
                // Si premier chiffre > 1, préfixer 0 automatiquement (ex: "3" → "03")
                if (parseInt(v[0], 10) > 1) {
                    v = '0' + v;
                }
            }

            if (v.length >= 2) {
                // Bloquer le mois à 12 max
                let month = parseInt(v.substring(0, 2), 10);
                if (month < 1)  month = 1;
                if (month > 12) month = 12;
                v = String(month).padStart(2, '0') + v.substring(2);
            }

            if (v.length >= 3) {
                v = v.substring(0, 2) + ' / ' + v.substring(2);
            }

            input.value = v;
        }
    </script>

</body>
</html>