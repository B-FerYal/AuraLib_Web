<?php
session_start();
require_once "../includes/db.php";
include_once '../includes/languages.php';

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$emprunt_id = isset($_GET['emprunt_id']) ? (int)$_GET['emprunt_id'] : 0;
if (!$emprunt_id) { header("Location: admin_dashboard.php"); exit; }

// ── Labels multilingue ────────────────────────────────
$labels = [
    'fr' => [
        'title'         => 'Détails de l\'emprunt',
        'back'          => 'Retour',
        'emprunt_num'   => 'Emprunt',
        'borrower'      => 'Emprunteur',
        'document'      => 'Document',
        'date_debut'    => 'Date de début',
        'date_fin'      => 'Date de fin',
        'date_retour'   => 'Date retour prévue',
        'statut'        => 'Statut',
        'amende'        => 'Amende',
        'days_late'     => 'jours de retard',
        'actions'       => 'Actions',
        'mark_returned' => 'Marquer comme retourné',
        'mark_encours'  => 'Marquer en cours',
        'not_found'     => 'Emprunt introuvable.',
        'email'         => 'Email',
        'phone'         => 'Téléphone',
        'author'        => 'Auteur',
        'type'          => 'Type',
        'saved'         => 'Statut mis à jour avec succès.',
        'statut_rendu'      => 'Retourné',
        'statut_en_cours'   => 'En cours',
        'statut_retard'     => 'En retard',
        'statut_refusee'    => 'Refusée',
        'statut_en_attente' => 'En attente',
    ],
    'en' => [
        'title'         => 'Loan details',
        'back'          => 'Back',
        'emprunt_num'   => 'Loan',
        'borrower'      => 'Borrower',
        'document'      => 'Document',
        'date_debut'    => 'Start date',
        'date_fin'      => 'End date',
        'date_retour'   => 'Expected return',
        'statut'        => 'Status',
        'amende'        => 'Fine',
        'days_late'     => 'days late',
        'actions'       => 'Actions',
        'mark_returned' => 'Mark as returned',
        'mark_encours'  => 'Mark as active',
        'not_found'     => 'Loan not found.',
        'email'         => 'Email',
        'phone'         => 'Phone',
        'author'        => 'Author',
        'type'          => 'Type',
        'saved'         => 'Status updated successfully.',
        'statut_rendu'      => 'Returned',
        'statut_en_cours'   => 'Active',
        'statut_retard'     => 'Overdue',
        'statut_refusee'    => 'Refused',
        'statut_en_attente' => 'Pending',
    ],
    'ar' => [
        'title'         => 'تفاصيل الاستعارة',
        'back'          => 'رجوع',
        'emprunt_num'   => 'استعارة',
        'borrower'      => 'المستعير',
        'document'      => 'الوثيقة',
        'date_debut'    => 'تاريخ البداية',
        'date_fin'      => 'تاريخ النهاية',
        'date_retour'   => 'تاريخ الإعادة المتوقع',
        'statut'        => 'الحالة',
        'amende'        => 'الغرامة',
        'days_late'     => 'أيام تأخير',
        'actions'       => 'الإجراءات',
        'mark_returned' => 'تعيين كمُعادة',
        'mark_encours'  => 'تعيين كنشطة',
        'not_found'     => 'الاستعارة غير موجودة.',
        'email'         => 'البريد الإلكتروني',
        'phone'         => 'الهاتف',
        'author'        => 'المؤلف',
        'type'          => 'النوع',
        'saved'         => 'تم تحديث الحالة بنجاح.',
        'statut_rendu'      => 'مُعادة',
        'statut_en_cours'   => 'نشطة',
        'statut_retard'     => 'متأخرة',
        'statut_refusee'    => 'مرفوضة',
        'statut_en_attente' => 'قيد الانتظار',
    ],
];
$l     = $labels[$lang] ?? $labels['fr'];
$isRtl = ($lang === 'ar');

// ── Handle action ─────────────────────────────────────
$success = '';
if (isset($_GET['action'])) {
    $new_statut = '';
    if ($_GET['action'] === 'mark_returned') $new_statut = 'rendu';
    if ($_GET['action'] === 'mark_encours')  $new_statut = 'en_cours';
    if ($new_statut) {
        $stmt = $conn->prepare("UPDATE emprunt SET statut=?, date_fin=? WHERE id_emprunt=?");
        $date_fin = ($new_statut === 'rendu') ? date('Y-m-d') : null;
        $stmt->bind_param("ssi", $new_statut, $date_fin, $emprunt_id);
        $stmt->execute();
        $success = $l['saved'];
    }
    header("Location: gestion_emprunts.php?emprunt_id=$emprunt_id&saved=1");
    exit;
}
if (isset($_GET['saved'])) $success = $l['saved'];

// ── Fetch emprunt ─────────────────────────────────────
$stmt = $conn->prepare("
    SELECT e.*,
           u.firstname, u.lastname, u.email, u.phone,
           d.titre, d.auteur, d.image_doc,
           t.libelle_type
    FROM emprunt e
    JOIN users u     ON e.id_user = u.id
    JOIN documents d ON e.id_doc  = d.id_doc
    LEFT JOIN types_documents t ON d.id_type = t.id_type
    WHERE e.id_emprunt = ?
");
$stmt->bind_param("i", $emprunt_id);
$stmt->execute();
$e = $stmt->get_result()->fetch_assoc();

if (!$e) {
    include '../includes/header.php';
    echo '<div style="padding:60px;text-align:center;color:#9A8C7E">' . $l['not_found'] . '</div>';
    include '../includes/footer.php';
    exit;
}

$statut      = $e['statut'] ?? '';
$days_late   = 0;
if ($e['date_retour_prevue'] && in_array($statut, ['retard','en_cours'])) {
    $days_late = max(0, (int)floor((time() - strtotime($e['date_retour_prevue'])) / 86400));
}
$amende = (float)($e['amende'] ?? 0);

$statut_label = match($statut) {
    'rendu'      => $l['statut_rendu'],
    'en_cours'   => $l['statut_en_cours'],
    'retard'     => $l['statut_retard'],
    'refusée'    => $l['statut_refusee'],
    'en_attente' => $l['statut_en_attente'],
    default      => ucfirst($statut),
};
$statut_color = match($statut) {
    'rendu'      => '#2E7D52',
    'en_cours'   => '#B8832A',
    'retard'     => '#C0392B',
    'refusée'    => '#C0392B',
    default      => '#9A8C7E',
};
$statut_bg = match($statut) {
    'rendu'    => 'rgba(46,125,82,.1)',
    'en_cours' => 'rgba(184,131,42,.1)',
    'retard'   => 'rgba(192,57,43,.1)',
    default    => 'rgba(154,140,126,.1)',
};

$img_path = '/MEMOIR/uploads/' . (int)$e['id_doc'] . '.jpg';
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $img_path))
    $img_path = !empty($e['image_doc']) ? '/MEMOIR/uploads/' . $e['image_doc'] : '/MEMOIR/uploads/default.jpg';

include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
<meta charset="UTF-8">
<?php include '../includes/dark_init.php'; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AuraLib Admin · <?= $l['title'] ?> ARL-<?= str_pad($emprunt_id,4,'0',STR_PAD_LEFT) ?>-<?= date('Y') ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<style>
/* ═══════════════════════════════════════════════════════
   AURALIB · gestion_emprunts — Premium Luxury CSS
   ═══════════════════════════════════════════════════════ */
:root {
    --gold:           #C4A46B;
    --gold2:          #D4B47B;
    --gold-deep:      #A8884E;
    --gold-faint:     rgba(196,164,107,.08);
    --gold-border:    rgba(196,164,107,.22);
    --gold-shadow:    0 8px 32px rgba(196,164,107,.18);
    --ink:            #1A0E05;
    --ink2:           #2C1F0E;
    --ink3:           #3A2A14;
    --page-bg:        #F2EDE3;
    --page-bg2:       #EDE5D4;
    --page-white:     #FDFAF5;
    --page-text:      #2A1F14;
    --page-muted:     #9A8C7E;
    --page-border:    #D8CFC0;
    --success:        #276749;
    --success-bg:     rgba(39,103,73,.09);
    --success-border: rgba(39,103,73,.22);
    --danger:         #C0392B;
    --danger-bg:      rgba(192,57,43,.08);
    --danger-border:  rgba(192,57,43,.22);
    --warning:        #92400E;
    --warning-bg:     rgba(146,64,14,.08);
    --warning-border: rgba(146,64,14,.2);
    --font-serif:     'EB Garamond', Georgia, serif;
    --font-ui:        'Plus Jakarta Sans', sans-serif;
    --nav-h:          68px;
    --radius:         16px;
    --radius-sm:      10px;
    --shadow-sm:      0 2px 12px rgba(42,31,20,.06);
    --shadow-md:      0 8px 32px rgba(42,31,20,.10);
    --shadow-gold:    0 6px 24px rgba(196,164,107,.16);
    --ease:           cubic-bezier(.4,0,.2,1);
    --tr:             .22s var(--ease);
}
html.dark {
    --page-bg:     #100C07;
    --page-bg2:    #1A1308;
    --page-white:  #1E1610;
    --page-text:   #EDE5D4;
    --page-muted:  #9A8C7E;
    --page-border: #3A2E1E;
    --ink:         #0A0603;
    --ink2:        #1A1308;
    --ink3:        #2A1F0E;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: var(--font-ui);
    background: var(--page-bg);
    color: var(--page-text);
    padding-top: var(--nav-h);
    min-height: 100vh;
    transition: background .35s, color .35s;
}
@keyframes fadeUp   { from { opacity:0; transform:translateY(16px) } to { opacity:1; transform:translateY(0) } }
@keyframes slideIn  { from { opacity:0; transform:translateX(-10px) } to { opacity:1; transform:translateX(0) } }
@keyframes pulseDot { 0%,100%{transform:scale(1)} 50%{transform:scale(1.4)} }

/* ─── HERO ─────────────────────────────────────────────── */
.page-hero {
    background:
        radial-gradient(ellipse 70% 120% at 10% 60%, rgba(196,164,107,.07) 0%, transparent 60%),
        linear-gradient(160deg, #0D0805 0%, #1C1208 45%, #0D0805 100%);
    padding: 36px 5% 32px;
    position: relative; overflow: hidden;
    border-bottom: 1px solid rgba(196,164,107,.14);
}
.page-hero::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23C4A46B' fill-opacity='0.025'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none; opacity: .6;
}
.page-hero::after {
    content: '';
    position: absolute; bottom: 0; left: 0; right: 0; height: 1px;
    background: linear-gradient(90deg, transparent 0%, rgba(196,164,107,.35) 50%, transparent 100%);
}
.hero-inner {
    max-width: 1020px; margin: 0 auto;
    display: flex; align-items: flex-end;
    justify-content: space-between; gap: 20px; flex-wrap: wrap;
    animation: fadeUp .5s var(--ease) both;
    position: relative;
}
.hero-eyebrow {
    display: flex; align-items: center; gap: 8px;
    font-size: 10px; color: rgba(196,164,107,.45);
    letter-spacing: 3.5px; text-transform: uppercase;
    margin-bottom: 8px; font-family: var(--font-ui);
}
.hero-eyebrow i { font-size: 9px; }
.hero-title {
    font-family: var(--font-serif);
    font-size: clamp(26px, 4vw, 42px);
    font-weight: 700; color: #FDFAF5; line-height: 1;
    letter-spacing: -.3px;
}
.hero-title span {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold2) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
}
.admin-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 13px; border-radius: 30px; margin-top: 10px;
    background: rgba(196,164,107,.1);
    border: 1.5px solid rgba(196,164,107,.25);
    color: rgba(196,164,107,.75);
    font-size: 9px; font-weight: 800; letter-spacing: 2.5px; text-transform: uppercase;
}
.btn-back {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 22px; border-radius: 50px;
    background: rgba(196,164,107,.08);
    border: 1.5px solid rgba(196,164,107,.22);
    color: rgba(196,164,107,.75);
    font-family: var(--font-ui); font-size: 12px; font-weight: 700;
    text-decoration: none; letter-spacing: .3px;
    transition: all var(--tr);
}
.btn-back:hover {
    background: rgba(196,164,107,.16);
    border-color: rgba(196,164,107,.45);
    color: var(--gold);
    transform: translateX(-2px);
}

/* ─── ALERT ─────────────────────────────────────────────── */
.alert-wrap { max-width: 1020px; margin: 20px auto 0; padding: 0 5%; }
.alert-success {
    display: flex; align-items: center; gap: 10px;
    background: var(--success-bg);
    border: 1px solid var(--success-border);
    color: var(--success);
    padding: 12px 18px; border-radius: var(--radius-sm);
    font-size: 13px; font-weight: 600;
    animation: fadeUp .35s var(--ease) both;
}

/* ─── MAIN GRID ──────────────────────────────────────────── */
.page-main {
    max-width: 1020px; margin: 28px auto 80px;
    padding: 0 5%;
    display: grid; grid-template-columns: 300px 1fr;
    gap: 22px;
    animation: fadeUp .5s .1s var(--ease) both;
}
@media (max-width: 740px) { .page-main { grid-template-columns: 1fr; } }

/* ─── SHARED CARD BASE ───────────────────────────────────── */
.lux-card {
    background: var(--page-white);
    border: 1px solid var(--page-border);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: box-shadow var(--tr);
}
.lux-card:hover { box-shadow: var(--shadow-md); }

.lux-card-head {
    background: linear-gradient(135deg, var(--ink) 0%, var(--ink3) 100%);
    padding: 15px 20px;
    display: flex; align-items: center; gap: 10px;
    border-bottom: 1px solid rgba(196,164,107,.12);
}
.lux-card-head i { color: var(--gold); font-size: 14px; flex-shrink: 0; }
.lux-card-head-title {
    font-family: var(--font-serif);
    font-size: 18px; font-weight: 700; color: #FDFAF5;
}

/* ─── DOCUMENT CARD ──────────────────────────────────────── */
.doc-cover {
    height: 220px; overflow: hidden;
    background: linear-gradient(135deg, var(--ink) 0%, var(--ink3) 100%);
    position: relative;
}
.doc-cover img {
    width: 100%; height: 100%; object-fit: cover;
    display: block;
    transition: transform .5s var(--ease);
}
.lux-card:hover .doc-cover img { transform: scale(1.03); }
.doc-cover::after {
    content: '';
    position: absolute; bottom: 0; left: 0; right: 0;
    height: 80px;
    background: linear-gradient(transparent, rgba(10,6,3,.6));
    pointer-events: none;
}
.doc-body { padding: 18px; }
.doc-title {
    font-family: var(--font-serif);
    font-size: 20px; font-weight: 700;
    color: var(--page-text); line-height: 1.2; margin-bottom: 5px;
}
.doc-author {
    font-size: 12px; color: var(--page-muted);
    margin-bottom: 12px; display: flex; align-items: center; gap: 5px;
}
.doc-type-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 12px; border-radius: 30px;
    background: var(--gold-faint);
    border: 1px solid var(--gold-border);
    color: var(--gold-deep); font-size: 10px; font-weight: 700;
    letter-spacing: .5px; text-transform: uppercase;
}

/* ─── BORROWER CARD ──────────────────────────────────────── */
.borrower-head {
    display: flex; align-items: center; gap: 14px;
    padding: 16px 18px;
    border-bottom: 1px solid var(--page-border);
    background: linear-gradient(135deg, var(--ink) 0%, var(--ink3) 100%);
}
.borrower-av {
    width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-deep) 100%);
    color: var(--ink2);
    font-family: var(--font-serif); font-size: 18px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid rgba(196,164,107,.35);
    box-shadow: 0 3px 10px rgba(196,164,107,.25);
}
.borrower-name {
    font-weight: 700; font-size: 14px; color: #FDFAF5; line-height: 1.2;
}
.borrower-email { font-size: 11px; color: rgba(255,255,255,.4); margin-top: 2px; }
.borrower-row {
    display: flex; align-items: center;
    justify-content: space-between;
    padding: 10px 18px;
    border-bottom: 1px solid var(--page-border);
    font-size: 12px;
    transition: background var(--tr);
}
.borrower-row:last-child { border-bottom: none; }
.borrower-row:hover { background: var(--gold-faint); }
.borrower-row-lbl {
    font-size: 9px; font-weight: 700; letter-spacing: 1.2px;
    text-transform: uppercase; color: var(--page-muted);
    display: flex; align-items: center; gap: 6px;
}
.borrower-row-lbl i { font-size: 11px; color: var(--gold-deep); opacity: .7; }
.borrower-row-val { color: var(--page-text); font-weight: 600; font-size: 13px; }

/* ─── ACTIONS CARD ───────────────────────────────────────── */
.actions-body { padding: 18px; }
.actions-lbl {
    font-size: 9px; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; color: var(--page-muted);
    margin-bottom: 14px; padding-bottom: 10px;
    border-bottom: 1px solid var(--page-border);
    display: flex; align-items: center; gap: 7px;
}
.actions-lbl i { color: var(--gold); font-size: 11px; }
.btn-lux-action {
    display: flex; align-items: center; gap: 11px;
    width: 100%; padding: 12px 16px;
    border-radius: var(--radius-sm);
    border: 1.5px solid;
    font-family: var(--font-ui); font-size: 12px; font-weight: 700;
    text-decoration: none; cursor: pointer;
    transition: all var(--tr); margin-bottom: 9px;
    letter-spacing: .2px;
}
.btn-lux-action:last-child { margin-bottom: 0; }
.btn-lux-action i { font-size: 14px; flex-shrink: 0; }
.btn-action-green {
    background: var(--success-bg); border-color: var(--success-border); color: var(--success);
}
.btn-action-green:hover {
    background: rgba(39,103,73,.16); border-color: rgba(39,103,73,.4);
    transform: translateX(3px);
    box-shadow: 0 4px 14px rgba(39,103,73,.15);
}
.btn-action-amber {
    background: var(--warning-bg); border-color: var(--warning-border); color: var(--warning);
}
.btn-action-amber:hover {
    background: rgba(146,64,14,.15); border-color: rgba(146,64,14,.38);
    transform: translateX(3px);
}
.btn-action-gold {
    background: var(--gold-faint); border-color: var(--gold-border); color: var(--gold-deep);
}
.btn-action-gold:hover {
    background: rgba(196,164,107,.15); border-color: rgba(196,164,107,.42); color: var(--gold);
    transform: translateX(3px); box-shadow: var(--shadow-gold);
}

/* ─── DETAILS CARD ───────────────────────────────────────── */
.details-body { padding: 0; }
.info-row {
    display: flex; align-items: center;
    justify-content: space-between; gap: 16px;
    padding: 13px 22px;
    border-bottom: 1px solid var(--page-border);
    transition: background var(--tr);
}
.info-row:last-child { border-bottom: none; }
.info-row:hover { background: var(--gold-faint); }
.info-lbl {
    display: flex; align-items: center; gap: 8px;
    font-size: 9px; font-weight: 700; letter-spacing: 1.2px;
    text-transform: uppercase; color: var(--page-muted); flex-shrink: 0;
}
.info-lbl i { color: var(--gold-deep); font-size: 12px; opacity: .7; }
.info-val {
    font-size: 13px; color: var(--page-text); font-weight: 600;
    text-align: right;
}
.info-val.danger { color: var(--danger); }
.info-val.warning { color: var(--warning); }

/* Statut badge */
.statut-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 14px; border-radius: 30px;
    font-size: 11px; font-weight: 700; letter-spacing: .3px;
    border: 1.5px solid;
}
.statut-badge .badge-dot {
    width: 6px; height: 6px; border-radius: 50%;
    animation: pulseDot 2s var(--ease) infinite;
}

/* ─── RETARD BANNER ──────────────────────────────────────── */
.retard-banner {
    background: linear-gradient(135deg, rgba(192,57,43,.10) 0%, rgba(192,57,43,.06) 100%);
    border: 1.5px solid var(--danger-border);
    border-radius: var(--radius-sm);
    padding: 14px 18px;
    display: flex; align-items: center; gap: 14px;
    margin-bottom: 16px;
    box-shadow: 0 3px 14px rgba(192,57,43,.08);
    animation: slideIn .4s var(--ease) both;
}
.retard-icon-wrap {
    width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
    background: rgba(192,57,43,.1);
    border: 1.5px solid rgba(192,57,43,.22);
    display: flex; align-items: center; justify-content: center;
}
.retard-icon-wrap i { color: var(--danger); font-size: 16px; }
.retard-text strong { display: block; font-size: 14px; font-weight: 700; color: var(--danger); }
.retard-text span { font-size: 12px; color: rgba(192,57,43,.75); margin-top: 2px; display: block; }

/* ─── SPACING UTILS ──────────────────────────────────────── */
.mt-14 { margin-top: 14px; }
.mt-16 { margin-top: 16px; }
</style>
</head>
<body>

<!-- HERO -->
<div class="page-hero">
    <div class="hero-inner">
        <div>
            <div class="hero-eyebrow">
                <i class="fa-solid fa-shield-halved" style="font-size:9px"></i>
                Administration · <?= $l['title'] ?>
            </div>
            <div class="hero-title">
                <?= $l['emprunt_num'] ?> <span>ARL-<?= str_pad($emprunt_id,4,'0',STR_PAD_LEFT) ?>-<?= date('Y') ?></span>
            </div>
            <div class="admin-badge">
                <i class="fa-solid fa-user-shield" style="font-size:9px"></i> Admin
            </div>
        </div>
        <a href="javascript:history.back()" class="btn-back">
            <i class="fa-solid fa-arrow-left" style="font-size:10px"></i>
            <?= $l['back'] ?>
        </a>
    </div>
</div>

<?php if ($success): ?>
<div class="alert-wrap">
    <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?></div>
</div>
<?php endif; ?>

<div class="page-main">

    <!-- LEFT COL -->
    <div>
        <!-- Document -->
        <div class="lux-card">
            <div class="doc-cover">
                <img src="<?= htmlspecialchars($img_path) ?>"
                     alt="<?= htmlspecialchars($e['titre']) ?>"
                     onerror="this.src='/MEMOIR/uploads/default.jpg'">
            </div>
            <div class="doc-body">
                <div class="doc-title"><?= htmlspecialchars($e['titre']) ?></div>
                <div class="doc-author">
                    <i class="fa-solid fa-user-pen" style="font-size:10px"></i>
                    <?= htmlspecialchars($e['auteur'] ?? '') ?>
                </div>
                <?php if (!empty($e['libelle_type'])): ?>
                <span class="doc-type-pill">
                    <i class="fa-solid fa-tag" style="font-size:9px"></i>
                    <?= htmlspecialchars($e['libelle_type']) ?>
                </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Borrower -->
        <div class="lux-card mt-14">
            <div class="borrower-head">
                <div class="borrower-av"><?= strtoupper(substr($e['firstname'] ?? 'U', 0, 1)) ?></div>
                <div>
                    <div class="borrower-name"><?= htmlspecialchars(trim(($e['firstname'] ?? '') . ' ' . ($e['lastname'] ?? ''))) ?></div>
                    <div class="borrower-email"><?= htmlspecialchars($e['email'] ?? '') ?></div>
                </div>
            </div>
            <?php if (!empty($e['phone'])): ?>
            <div class="borrower-row">
                <span class="borrower-row-lbl">
                    <i class="fa-solid fa-phone"></i> <?= $l['phone'] ?>
                </span>
                <span class="borrower-row-val"><?= htmlspecialchars($e['phone']) ?></span>
            </div>
            <?php endif; ?>
            <div class="borrower-row">
                <span class="borrower-row-lbl">
                    <i class="fa-solid fa-at"></i> <?= $l['email'] ?>
                </span>
                <span class="borrower-row-val" style="font-size:11px"><?= htmlspecialchars($e['email'] ?? '') ?></span>
            </div>
        </div>

        <!-- Actions -->
        <div class="lux-card mt-14">
            <div class="lux-card-head">
                <i class="fa-solid fa-bolt"></i>
                <div class="lux-card-head-title"><?= $l['actions'] ?></div>
            </div>
            <div class="actions-body">
                <div class="actions-lbl">
                    <i class="fa-solid fa-sliders"></i>
                    Changer le statut
                </div>
                <?php if ($statut !== 'rendu'): ?>
                <a href="?emprunt_id=<?= $emprunt_id ?>&action=mark_returned"
                   class="btn-lux-action btn-action-green"
                   onclick="return confirm('<?= $lang==='ar'?'تعيين كمُعادة؟':'Confirmer le retour ?' ?>')">
                    <i class="fa-solid fa-circle-check"></i>
                    <?= $l['mark_returned'] ?>
                </a>
                <?php endif; ?>
                <?php if ($statut === 'rendu'): ?>
                <a href="?emprunt_id=<?= $emprunt_id ?>&action=mark_encours"
                   class="btn-lux-action btn-action-amber">
                    <i class="fa-solid fa-rotate-left"></i>
                    <?= $l['mark_encours'] ?>
                </a>
                <?php endif; ?>
                <a href="/MEMOIR/admin/gerer_emprunts.php" class="btn-lux-action btn-action-gold">
                    <i class="fa-solid fa-list-ul"></i>
                    Tous les emprunts
                </a>
            </div>
        </div>
    </div>

    <!-- RIGHT COL -->
    <div>
        <?php if ($statut === 'retard' && $days_late > 0): ?>
        <div class="retard-banner">
            <div class="retard-icon-wrap">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="retard-text">
                <strong><?= $days_late ?> <?= $l['days_late'] ?></strong>
                <?php if ($amende > 0): ?>
                <span>Amende accumulée : <strong><?= number_format($amende, 2) ?> DA</strong></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="lux-card">
            <div class="lux-card-head">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <div class="lux-card-head-title"><?= $l['title'] ?> ARL-<?= str_pad($emprunt_id,4,'0',STR_PAD_LEFT) ?>-<?= date('Y') ?></div>
            </div>
            <div class="details-body">

                <div class="info-row">
                    <span class="info-lbl">
                        <i class="fa-solid fa-circle-half-stroke"></i> <?= $l['statut'] ?>
                    </span>
                    <span class="statut-badge" style="background:<?= $statut_bg ?>;color:<?= $statut_color ?>;border-color:<?= $statut_color ?>44">
                        <span class="badge-dot" style="background:<?= $statut_color ?>"></span>
                        <?= $statut_label ?>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-lbl">
                        <i class="fa-regular fa-calendar-plus"></i> <?= $l['date_debut'] ?>
                    </span>
                    <span class="info-val">
                        <?= !empty($e['date_debut']) ? date('d/m/Y', strtotime($e['date_debut'])) : '—' ?>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-lbl">
                        <i class="fa-regular fa-calendar-check"></i> <?= $l['date_retour'] ?>
                    </span>
                    <span class="info-val <?= $statut==='retard'?'danger':'' ?>">
                        <?= !empty($e['date_retour_prevue']) ? date('d/m/Y', strtotime($e['date_retour_prevue'])) : '—' ?>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-lbl">
                        <i class="fa-regular fa-calendar-xmark"></i> <?= $l['date_fin'] ?>
                    </span>
                    <span class="info-val">
                        <?= !empty($e['date_fin']) ? date('d/m/Y', strtotime($e['date_fin'])) : '—' ?>
                    </span>
                </div>

                <?php if ($days_late > 0): ?>
                <div class="info-row">
                    <span class="info-lbl">
                        <i class="fa-solid fa-hourglass-end"></i> <?= $l['days_late'] ?>
                    </span>
                    <span class="info-val danger"><?= $days_late ?> j</span>
                </div>
                <?php endif; ?>

                <?php if ($amende > 0): ?>
                <div class="info-row">
                    <span class="info-lbl">
                        <i class="fa-solid fa-coins"></i> <?= $l['amende'] ?>
                    </span>
                    <span class="info-val danger" style="font-size:15px">
                        <?= number_format($amende, 2) ?> <small style="font-size:11px">DA</small>
                    </span>
                </div>
                <?php endif; ?>

                <div class="info-row">
                    <span class="info-lbl">
                        <i class="fa-solid fa-tag"></i> Référence
                    </span>
                    <span class="info-val" style="font-family:var(--font-serif);font-size:15px;color:var(--gold-deep);letter-spacing:.5px">
                        ARL-<?= str_pad($emprunt_id, 4, '0', STR_PAD_LEFT) ?>-<?= date('Y') ?>
                    </span>
                </div>

            </div>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>