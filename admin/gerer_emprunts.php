<?php
session_start();
require_once "../includes/db.php";
require_once '../includes/head.php';
include_once '../includes/languages.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// ── Traductions ──────────────────────────────────────────
$pg = [
    'fr' => [
        'page_title'    => 'AuraLib · Gestion des Emprunts',
        'breadcrumb'    => 'Dashboard',
        'hero_title'    => 'Gestion des',
        'hero_span'     => 'Emprunts',
        'today'         => "Aujourd'hui",
        'btn_back'      => 'Retour au Dashboard',
        // Stats
        'stat_pending'  => 'En attente',
        'stat_accepted' => 'Acceptés',
        'stat_late'     => 'En retard',
        'stat_returned' => 'Rendus',
        'stat_refused'  => 'Refusés',
        // Table headers
        'th_id'         => '#',
        'th_reader'     => 'Lecteur & Document',
        'th_stock'      => 'Stock',
        'th_request'    => 'Demande',
        'th_due'        => 'Retour prévu',
        'th_fine'       => 'Amende',
        'th_status'     => 'Statut',
        'th_actions'    => 'Actions',
        // Stock
        'stock_avail'   => 'dispo',
        'stock_out'     => 'Épuisé',
        // Date / fine
        'days_late'     => 'j de retard',
        // Status labels
        's_pending'     => 'En attente',
        's_accepted'    => 'Acceptée',
        's_late'        => 'Retard',
        's_returned'    => 'Rendu',
        's_refused'     => 'Refusée',
        // Actions
        'btn_accept'    => 'Accepter',
        'btn_refuse'    => 'Refuser',
        'btn_return'    => 'Marquer Rendu',
        'tip_no_stock'  => 'Stock épuisé',
        'tip_accept'    => 'Accepter cet emprunt',
        // SweetAlert
        'swal_return_title' => 'Confirmer le retour ?',
        'swal_return_text'  => 'Le stock sera restauré automatiquement.',
        'swal_return_yes'   => 'Oui, marquer rendu',
        'swal_refuse_title' => 'Refuser cet emprunt ?',
        'swal_refuse_text'  => 'Le lecteur sera notifié du refus.',
        'swal_refuse_yes'   => 'Oui, refuser',
        'swal_cancel'       => 'Annuler',
        // Empty
        'empty_h'       => 'Aucun emprunt enregistré',
        // Flash messages
        'flash_accepted'       => 'Emprunt accepté — stock mis à jour.',
        'flash_refused'        => 'Emprunt refusé.',
        'flash_returned'       => 'Livre retourné — stock restauré.',
        'flash_returned_fine'  => 'Amende :',
        'flash_no_stock'       => "Impossible d'accepter : stock épuisé pour ce document.",
        'flash_invalid'        => 'Action non autorisée pour ce statut.',
        'flash_error'          => 'Une erreur est survenue.',
    ],
    'en' => [
        'page_title'    => 'AuraLib · Loan Management',
        'breadcrumb'    => 'Dashboard',
        'hero_title'    => 'Loan',
        'hero_span'     => 'Management',
        'today'         => 'Today',
        'btn_back'      => 'Back to Dashboard',
        'stat_pending'  => 'Pending',
        'stat_accepted' => 'Accepted',
        'stat_late'     => 'Overdue',
        'stat_returned' => 'Returned',
        'stat_refused'  => 'Refused',
        'th_id'         => '#',
        'th_reader'     => 'Reader & Document',
        'th_stock'      => 'Stock',
        'th_request'    => 'Request date',
        'th_due'        => 'Due date',
        'th_fine'       => 'Fine',
        'th_status'     => 'Status',
        'th_actions'    => 'Actions',
        'stock_avail'   => 'available',
        'stock_out'     => 'Out of stock',
        'days_late'     => 'days late',
        's_pending'     => 'Pending',
        's_accepted'    => 'Accepted',
        's_late'        => 'Overdue',
        's_returned'    => 'Returned',
        's_refused'     => 'Refused',
        'btn_accept'    => 'Accept',
        'btn_refuse'    => 'Refuse',
        'btn_return'    => 'Mark Returned',
        'tip_no_stock'  => 'Out of stock',
        'tip_accept'    => 'Accept this loan',
        'swal_return_title' => 'Confirm return?',
        'swal_return_text'  => 'Stock will be automatically restored.',
        'swal_return_yes'   => 'Yes, mark returned',
        'swal_refuse_title' => 'Refuse this loan?',
        'swal_refuse_text'  => 'The reader will be notified.',
        'swal_refuse_yes'   => 'Yes, refuse',
        'swal_cancel'       => 'Cancel',
        'empty_h'       => 'No loans recorded',
        'flash_accepted'       => 'Loan accepted — stock updated.',
        'flash_refused'        => 'Loan refused.',
        'flash_returned'       => 'Book returned — stock restored.',
        'flash_returned_fine'  => 'Fine:',
        'flash_no_stock'       => 'Cannot accept: out of stock for this document.',
        'flash_invalid'        => 'Action not allowed for this status.',
        'flash_error'          => 'An error occurred.',
    ],
    'ar' => [
        'page_title'    => 'AuraLib · إدارة الاستعارات',
        'breadcrumb'    => 'لوحة التحكم',
        'hero_title'    => 'إدارة',
        'hero_span'     => 'الاستعارات',
        'today'         => 'اليوم',
        'btn_back'      => 'العودة للوحة التحكم',
        'stat_pending'  => 'قيد الانتظار',
        'stat_accepted' => 'مقبولة',
        'stat_late'     => 'متأخرة',
        'stat_returned' => 'مُعادة',
        'stat_refused'  => 'مرفوضة',
        'th_id'         => 'رقم',
        'th_reader'     => 'القارئ والوثيقة',
        'th_stock'      => 'المخزون',
        'th_request'    => 'تاريخ الطلب',
        'th_due'        => 'تاريخ الإعادة',
        'th_fine'       => 'الغرامة',
        'th_status'     => 'الحالة',
        'th_actions'    => 'الإجراءات',
        'stock_avail'   => 'متاح',
        'stock_out'     => 'نفد المخزون',
        'days_late'     => 'أيام تأخير',
        's_pending'     => 'قيد الانتظار',
        's_accepted'    => 'مقبولة',
        's_late'        => 'متأخرة',
        's_returned'    => 'مُعادة',
        's_refused'     => 'مرفوضة',
        'btn_accept'    => 'قبول',
        'btn_refuse'    => 'رفض',
        'btn_return'    => 'تعليم كمُعادة',
        'tip_no_stock'  => 'نفد المخزون',
        'tip_accept'    => 'قبول هذه الاستعارة',
        'swal_return_title' => 'تأكيد الإعادة؟',
        'swal_return_text'  => 'سيتم استعادة المخزون تلقائياً.',
        'swal_return_yes'   => 'نعم، تعليم كمُعادة',
        'swal_refuse_title' => 'رفض هذه الاستعارة؟',
        'swal_refuse_text'  => 'سيتم إبلاغ القارئ بالرفض.',
        'swal_refuse_yes'   => 'نعم، رفض',
        'swal_cancel'       => 'إلغاء',
        'empty_h'       => 'لا توجد استعارات مسجلة',
        'flash_accepted'       => 'تم قبول الاستعارة — تم تحديث المخزون.',
        'flash_refused'        => 'تم رفض الاستعارة.',
        'flash_returned'       => 'تم استعادة الكتاب — تم استعادة المخزون.',
        'flash_returned_fine'  => 'الغرامة:',
        'flash_no_stock'       => 'لا يمكن القبول: نفد مخزون هذه الوثيقة.',
        'flash_invalid'        => 'الإجراء غير مسموح به لهذه الحالة.',
        'flash_error'          => 'حدث خطأ.',
    ],
];
$p     = $pg[$lang] ?? $pg['fr'];
$isRtl = ($lang === 'ar');

// Auto-update retards
$conn->query("UPDATE emprunt SET statut = 'retard'
              WHERE date_retour_prevue < CURDATE()
              AND statut = 'acceptée'
              AND date_fin IS NULL");

// Flash messages (built from translations)
$msg    = $_GET['msg'] ?? '';
$amende = (int)($_GET['amende'] ?? 0);
$flash_messages = [
    'accepted'       => ['type' => 'success', 'text' => $p['flash_accepted']],
    'refused'        => ['type' => 'warning', 'text' => $p['flash_refused']],
    'returned'       => ['type' => 'success', 'text' => $p['flash_returned'] . ($amende > 0 ? " {$p['flash_returned_fine']} <strong>{$amende} DA</strong>" : '')],
    'no_stock'       => ['type' => 'danger',  'text' => $p['flash_no_stock']],
    'invalid_status' => ['type' => 'warning', 'text' => $p['flash_invalid']],
    'error'          => ['type' => 'danger',  'text' => $p['flash_error']],
];

// Stats
$stats = [];
foreach (['en attente','acceptée','retard','rendu','refusée'] as $s) {
    $r = $conn->query("SELECT COUNT(*) as n FROM emprunt WHERE statut = '$s'");
    $stats[$s] = (int)($r->fetch_assoc()['n'] ?? 0);
}

// Status labels map
$status_labels = [
    'en attente' => $p['s_pending'],
    'acceptée'   => $p['s_accepted'],
    'retard'     => $p['s_late'],
    'rendu'      => $p['s_returned'],
    'refusée'    => $p['s_refused'],
];

// Emprunts
$result = $conn->query("
    SELECT e.*, u.firstname, u.lastname, d.titre, d.exemplaires_disponibles
    FROM emprunt e
    JOIN users u ON e.id_user = u.id
    JOIN documents d ON e.id_doc = d.id_doc
    ORDER BY
        CASE
            WHEN e.statut = 'en attente' THEN 1
            WHEN e.statut = 'retard'     THEN 2
            WHEN e.statut = 'acceptée'   THEN 3
            ELSE 4
        END,
        e.id_emprunt DESC
");
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
<meta charset="UTF-8">
<?php include '../includes/dark_init.php'; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $p['page_title'] ?></title>
<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
/* ══ TOKENS ══ */
:root {
    --gold:        #C4A46B; --gold2:       #D4B47B; --gold-deep:   #A8884E;
    --gold-faint:  rgba(196,164,107,.09); --gold-border: rgba(196,164,107,.28);
    --amber:       #B8832A; --brown:       #7A5C3A;
    --page-bg:     #F2EDE3; --page-bg2:    #E8E0D0; --page-white:  #FDFAF5;
    --page-text:   #2A1F14; --page-muted:  #9A8C7E; --page-border: #D8CFC0;
    --danger:      #C0392B; --success:     #276749; --warning:     #92400E;
    --font-serif:  'EB Garamond', Georgia, serif;
    --font-ui:     <?= $isRtl ? "'Tajawal', sans-serif" : "'Plus Jakarta Sans', sans-serif" ?>;
    --nav-h:       62px; --radius: 16px;
    --shadow-sm:   0 3px 10px rgba(42,31,20,.08);
    --shadow-md:   0 8px 28px rgba(42,31,20,.12);
    --shadow-gold: 0 6px 20px rgba(196,164,107,.25);
    --tr:          .25s cubic-bezier(.4,0,.2,1);
}
html.dark {
    --page-bg: #100C07; --page-bg2: #1A1308; --page-white: #1E1610;
    --page-text: #EDE5D4; --page-muted: #9A8C7E; --page-border: #3A2E1E;
    --shadow-sm: 0 3px 10px rgba(0,0,0,.3); --shadow-md: 0 8px 28px rgba(0,0,0,.4);
}
*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
body {
    font-family: var(--font-ui); background: var(--page-bg); color: var(--page-text);
    min-height: 100vh; padding-top: var(--nav-h);
    transition: background .35s, color .35s;
    direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
}
@keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
@keyframes rowIn  { from{opacity:0;transform:translateX(-10px)} to{opacity:1;transform:translateX(0)} }
@keyframes pulse-ring {
    0%   { box-shadow:0 0 0 0 rgba(192,57,43,.45); }
    70%  { box-shadow:0 0 0 7px rgba(192,57,43,0); }
    100% { box-shadow:0 0 0 0 rgba(192,57,43,0); }
}

/* ══ HERO ══ */
.page-hero {
    background: linear-gradient(135deg,#1A0E05 0%,#2E1D08 55%,#1A0E05 100%);
    padding: 36px 5% 32px; position:relative; overflow:hidden;
}
.page-hero::before {
    content:''; position:absolute; inset:0;
    background: radial-gradient(ellipse 60% 90% at 10% 50%,rgba(196,164,107,.11) 0%,transparent 65%);
    pointer-events:none;
}
.page-hero::after {
    content:''; position:absolute; bottom:0; left:0; right:0; height:1px;
    background: linear-gradient(90deg,transparent,rgba(196,164,107,.3),transparent);
}
.hero-inner {
    max-width:1340px; margin:0 auto;
    display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
    animation: fadeUp .5s ease both;
}
.hero-left { display:flex; flex-direction:column; gap:6px; }
.hero-breadcrumb {
    display:flex; align-items:center; gap:8px;
    font-size:11px; color:rgba(196,164,107,.5); letter-spacing:.4px;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.hero-breadcrumb a { color:rgba(196,164,107,.5); text-decoration:none; transition:color var(--tr); }
.hero-breadcrumb a:hover { color:var(--gold); }
.hero-breadcrumb i { font-size:8px; }
.hero-title {
    font-family:var(--font-serif); font-size:clamp(26px,4vw,44px);
    font-weight:700; color:#FDFAF5; line-height:1.05; letter-spacing:-.3px;
}
.hero-title span {
    background:linear-gradient(135deg,var(--gold) 0%,var(--gold2) 100%);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
}
.hero-date { font-size:11px; color:rgba(253,250,245,.4); letter-spacing:.5px; }
.btn-back {
    display:inline-flex; align-items:center; gap:8px;
    padding:10px 20px; border-radius:50px;
    font-family:var(--font-ui); font-size:12px; font-weight:700;
    color:rgba(196,164,107,.8); background:rgba(196,164,107,.1);
    backdrop-filter:blur(12px); border:1.5px solid rgba(196,164,107,.25);
    text-decoration:none; transition:all var(--tr); flex-shrink:0;
}
.btn-back:hover { background:rgba(196,164,107,.2); color:var(--gold2); border-color:rgba(196,164,107,.5); transform:translateY(-1px); }

/* ══ STATS BAR ══ */
.stats-bar {
    max-width:1340px; margin:28px auto 0; padding:0 5%;
    display:flex; gap:14px; flex-wrap:wrap;
    animation: fadeUp .5s .1s ease both;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.stat-pill {
    display:flex; align-items:center; gap:9px;
    padding:10px 18px; border-radius:50px;
    background:var(--page-white); border:1.5px solid var(--page-border);
    box-shadow:var(--shadow-sm); flex-shrink:0;
}
.stat-dot   { width:9px; height:9px; border-radius:50%; flex-shrink:0; }
.stat-label { font-size:11px; color:var(--page-muted); font-weight:500; }
.stat-num   { font-family:var(--font-serif); font-size:20px; font-weight:700; color:var(--page-text); line-height:1; }

/* ══ FLASH ══ */
.flash-wrap { max-width:1340px; margin:20px auto 0; padding:0 5%; }
.flash {
    display:flex; align-items:center; gap:12px;
    padding:14px 18px; border-radius:14px;
    font-size:13px; font-weight:600;
    animation: fadeUp .4s ease both;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.flash i { font-size:15px; flex-shrink:0; }
.flash.success { background:rgba(39,103,73,.1); border:1.5px solid rgba(39,103,73,.25); color:var(--success); }
.flash.warning { background:rgba(146,64,14,.09); border:1.5px solid rgba(146,64,14,.22); color:var(--warning); }
.flash.danger  { background:rgba(192,57,43,.09); border:1.5px solid rgba(192,57,43,.22); color:var(--danger); }
html.dark .flash.success { background:rgba(39,103,73,.18); }
html.dark .flash.warning { background:rgba(146,64,14,.18); }
html.dark .flash.danger  { background:rgba(192,57,43,.18); }

/* ══ TABLE WRAPPER ══ */
.table-wrap {
    max-width:1340px; margin:24px auto 60px; padding:0 5%;
    animation: fadeUp .5s .15s ease both;
}
.table-card {
    background:var(--page-white); border-radius:20px;
    border:1px solid var(--page-border); overflow:hidden; box-shadow:var(--shadow-md);
}
.table-scroll { overflow-x: auto; }

/* ══ TABLE ══ */
table { width:100%; border-collapse:collapse; }
thead tr {
    background:linear-gradient(135deg,rgba(196,164,107,.07) 0%,rgba(122,92,58,.05) 100%);
    border-bottom:1.5px solid var(--gold-border);
}
th {
    padding:14px 16px; font-family:var(--font-ui); font-size:10px; font-weight:700;
    letter-spacing:<?= $isRtl ? '0' : '2px' ?>; text-transform:uppercase;
    color:var(--gold-deep); text-align:<?= $isRtl ? 'right' : 'left' ?>; white-space:nowrap;
}
html.dark th { color:var(--gold); }
tbody tr {
    border-bottom:1px solid var(--page-border); transition:background var(--tr);
    animation:rowIn .35s ease both;
}
tbody tr:last-child { border-bottom:none; }
tbody tr:hover { background:var(--gold-faint); }
<?php for($i=1;$i<=10;$i++): ?>
tbody tr:nth-child(<?=$i?>) { animation-delay:<?=round(($i-1)*.04,2)?>s; }
<?php endfor; ?>
td {
    padding:16px; font-size:13px; color:var(--page-text); vertical-align:middle;
    text-align:<?= $isRtl ? 'right' : 'left' ?>;
}
.td-id { font-size:11px; color:var(--page-muted); font-weight:600; letter-spacing:.5px; }
.user-name { font-weight:700; font-size:14px; color:var(--page-text); margin-bottom:3px; }
.user-book {
    display:flex; align-items:center; gap:6px;
    font-size:11px; color:var(--page-muted); line-height:1.3;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.user-book i { color:var(--gold); font-size:10px; flex-shrink:0; }
.stock-ok   { display:inline-flex; align-items:center; gap:5px; color:#276749; font-size:11px; font-weight:700; flex-direction:<?= $isRtl?'row-reverse':'row' ?>; }
.stock-zero { display:inline-flex; align-items:center; gap:5px; color:var(--danger); font-size:11px; font-weight:700; flex-direction:<?= $isRtl?'row-reverse':'row' ?>; }
.stock-ok i, .stock-zero i { font-size:10px; }
.date-main { font-size:13px; font-weight:600; color:var(--page-text); }
.date-late {
    font-size:10px; color:var(--danger); font-weight:700; margin-top:2px; display:block;
    display:flex; align-items:center; gap:4px;
    flex-direction:<?= $isRtl?'row-reverse':'row' ?>;
}
.amende-badge {
    display:inline-flex; align-items:center; gap:5px;
    background:rgba(184,131,42,.12); border:1.5px solid rgba(184,131,42,.3);
    color:var(--amber); padding:4px 10px; border-radius:8px; font-size:11px; font-weight:700;
    flex-direction:<?= $isRtl?'row-reverse':'row' ?>;
}
.dash { color:var(--page-border); font-size:14px; }

/* ══ STATUS BADGES ══ */
.badge {
    display:inline-flex; align-items:center; gap:6px;
    padding:5px 12px; border-radius:20px;
    font-size:10px; font-weight:700; letter-spacing:.6px;
    text-transform:uppercase; white-space:nowrap;
    flex-direction:<?= $isRtl?'row-reverse':'row' ?>;
}
.badge::before { content:''; width:6px; height:6px; border-radius:50%; flex-shrink:0; }
.s-en-attente { background:rgba(234,179,8,.1); border:1.5px solid rgba(234,179,8,.3); color:#92400E; }
.s-en-attente::before { background:#F59E0B; }
.s-acceptee   { background:rgba(39,103,73,.1); border:1.5px solid rgba(39,103,73,.28); color:var(--success); }
.s-acceptee::before { background:#276749; }
.s-retard     { background:rgba(192,57,43,.1); border:1.5px solid rgba(192,57,43,.3); color:var(--danger); animation:pulse-ring 2s infinite; }
.s-retard::before { background:var(--danger); }
.s-rendu      { background:rgba(154,140,126,.1); border:1.5px solid rgba(154,140,126,.25); color:var(--page-muted); }
.s-rendu::before { background:var(--page-muted); }
.s-refusee    { background:rgba(136,14,79,.08); border:1.5px solid rgba(136,14,79,.2); color:#880E4F; }
.s-refusee::before { background:#880E4F; }

/* ══ ACTION BUTTONS ══ */
.actions-cell { display:flex; align-items:center; gap:7px; flex-wrap:wrap; flex-direction:<?= $isRtl?'row-reverse':'row' ?>; }
.btn-action {
    display:inline-flex; align-items:center; gap:6px;
    padding:8px 16px; border-radius:50px;
    font-family:var(--font-ui); font-size:11px; font-weight:700;
    text-decoration:none; border:none; cursor:pointer;
    transition:all var(--tr); letter-spacing:.2px; white-space:nowrap;
    flex-direction:<?= $isRtl?'row-reverse':'row' ?>;
}
.btn-action i { font-size:10px; }
.btn-approve {
    background:linear-gradient(135deg,#1A0E05 0%,#2E1D08 100%);
    color:var(--gold2); border:1.5px solid rgba(196,164,107,.3);
    box-shadow:0 4px 14px rgba(42,31,20,.25);
}
.btn-approve:hover { background:linear-gradient(135deg,#2E1D08 0%,#3E2A10 100%); border-color:rgba(196,164,107,.55); transform:translateY(-2px); box-shadow:0 8px 24px rgba(42,31,20,.35); }
.btn-approve.disabled { opacity:.38; pointer-events:none; cursor:not-allowed; }
.btn-refuse { background:rgba(136,14,79,.07); color:#880E4F; border:1.5px solid rgba(136,14,79,.22); }
.btn-refuse:hover { background:rgba(136,14,79,.15); transform:translateY(-2px); }
.btn-return {
    background:linear-gradient(135deg,var(--gold) 0%,var(--gold-deep) 100%);
    color:#1A0E05; border:1.5px solid transparent; box-shadow:var(--shadow-gold); font-weight:800;
}
.btn-return:hover { background:linear-gradient(135deg,var(--gold2) 0%,var(--gold) 100%); transform:translateY(-2px); box-shadow:0 10px 28px rgba(196,164,107,.4); }

/* ══ EMPTY STATE ══ */
.empty-row td { text-align:center; padding:70px 20px; }
.empty-icon { font-size:40px; color:var(--page-border); margin-bottom:14px; }
.empty-row h3 { font-family:var(--font-serif); font-size:22px; color:var(--page-muted); }
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<!-- HERO -->
<div class="page-hero">
    <div class="hero-inner">
        <div class="hero-left">
            <div class="hero-breadcrumb">
                <a href="/MEMOIR/admin/admin_dashboard.php">
                    <i class="fa-solid fa-gauge-high"></i> <?= $p['breadcrumb'] ?>
                </a>
                <i class="fa-solid fa-chevron-right"></i>
                <span><?= $p['hero_span'] ?></span>
            </div>
            <h1 class="hero-title"><?= $p['hero_title'] ?> <span><?= $p['hero_span'] ?></span></h1>
            <span class="hero-date"><?= $p['today'] ?> · <?= date('d F Y') ?></span>
        </div>
        <a href="/MEMOIR/admin/admin_dashboard.php" class="btn-back">
            <i class="fa-solid fa-arrow-<?= $isRtl ? 'right' : 'left' ?>" style="font-size:10px"></i>
            <?= $p['btn_back'] ?>
        </a>
    </div>
</div>

<!-- STATS BAR -->
<div class="stats-bar">
    <div class="stat-pill"><span class="stat-dot" style="background:#F59E0B"></span><span class="stat-label"><?= $p['stat_pending'] ?></span><span class="stat-num"><?= $stats['en attente'] ?></span></div>
    <div class="stat-pill"><span class="stat-dot" style="background:#276749"></span><span class="stat-label"><?= $p['stat_accepted'] ?></span><span class="stat-num"><?= $stats['acceptée'] ?></span></div>
    <div class="stat-pill"><span class="stat-dot" style="background:var(--danger)"></span><span class="stat-label"><?= $p['stat_late'] ?></span><span class="stat-num"><?= $stats['retard'] ?></span></div>
    <div class="stat-pill"><span class="stat-dot" style="background:var(--page-muted)"></span><span class="stat-label"><?= $p['stat_returned'] ?></span><span class="stat-num"><?= $stats['rendu'] ?></span></div>
    <div class="stat-pill"><span class="stat-dot" style="background:#880E4F"></span><span class="stat-label"><?= $p['stat_refused'] ?></span><span class="stat-num"><?= $stats['refusée'] ?></span></div>
</div>

<!-- FLASH -->
<?php if ($msg && isset($flash_messages[$msg])): $f = $flash_messages[$msg]; ?>
<div class="flash-wrap">
    <div class="flash <?= $f['type'] ?>">
        <?php if($f['type']==='success'): ?><i class="fa-solid fa-circle-check"></i>
        <?php elseif($f['type']==='warning'): ?><i class="fa-solid fa-triangle-exclamation"></i>
        <?php else: ?><i class="fa-solid fa-circle-xmark"></i><?php endif; ?>
        <?= $f['text'] ?>
    </div>
</div>
<?php endif; ?>

<!-- TABLE -->
<div class="table-wrap">
    <div class="table-card">
        <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th><?= $p['th_id'] ?></th>
                    <th><?= $p['th_reader'] ?></th>
                    <th><?= $p['th_stock'] ?></th>
                    <th><?= $p['th_request'] ?></th>
                    <th><?= $p['th_due'] ?></th>
                    <th><?= $p['th_fine'] ?></th>
                    <th><?= $p['th_status'] ?></th>
                    <th><?= $p['th_actions'] ?></th>
                </tr>
            </thead>
            <tbody>
            <?php
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            if (empty($rows)): ?>
            <tr class="empty-row">
                <td colspan="8">
                    <div class="empty-icon"><i class="fa-regular fa-folder-open"></i></div>
                    <h3><?= $p['empty_h'] ?></h3>
                </td>
            </tr>
            <?php else:
            foreach ($rows as $row):
                $statut_css = match($row['statut']) {
                    'en attente' => 's-en-attente',
                    'acceptée'   => 's-acceptee',
                    'retard'     => 's-retard',
                    'rendu'      => 's-rendu',
                    'refusée'    => 's-refusee',
                    default      => 's-rendu'
                };
                $statut_lbl = $status_labels[$row['statut']] ?? ucfirst($row['statut']);
                $jours_retard = 0;
                if ($row['statut'] === 'retard' && !empty($row['date_retour_prevue'])) {
                    $jours_retard = (int)(new DateTime())->diff(new DateTime($row['date_retour_prevue']))->days;
                }
                $url_accept = "action_emprunts.php?id={$row['id_emprunt']}&action=accepter";
                $url_refuse = "action_emprunts.php?id={$row['id_emprunt']}&action=refuser";
                $url_return = "action_emprunts.php?id={$row['id_emprunt']}&action=rendre";
                $no_stock   = (int)$row['exemplaires_disponibles'] <= 0;
            ?>
            <tr>
                <td class="td-id">#<?= str_pad($row['id_emprunt'], 3, '0', STR_PAD_LEFT) ?></td>

                <td>
                    <div class="user-name"><?= htmlspecialchars($row['firstname'].' '.$row['lastname']) ?></div>
                    <div class="user-book">
                        <i class="fa-solid fa-book"></i>
                        <?= htmlspecialchars($row['titre']) ?>
                    </div>
                </td>

                <td>
                    <?php if (!$no_stock): ?>
                        <span class="stock-ok"><i class="fa-solid fa-circle-check"></i><?= $row['exemplaires_disponibles'] ?> <?= $p['stock_avail'] ?></span>
                    <?php else: ?>
                        <span class="stock-zero"><i class="fa-solid fa-circle-xmark"></i><?= $p['stock_out'] ?></span>
                    <?php endif; ?>
                </td>

                <td><span class="date-main"><?= $row['date_debut'] ? date('d/m/Y', strtotime($row['date_debut'])) : '—' ?></span></td>

                <td>
                    <?php if ($row['date_retour_prevue']): ?>
                        <span class="date-main" style="<?= $row['statut']==='retard'?'color:var(--danger)':'' ?>"><?= date('d/m/Y', strtotime($row['date_retour_prevue'])) ?></span>
                        <?php if ($row['statut'] === 'retard'): ?>
                            <span class="date-late"><i class="fa-solid fa-clock" style="font-size:9px"></i><?= $jours_retard ?> <?= $p['days_late'] ?></span>
                        <?php endif; ?>
                    <?php else: ?><span class="dash">—</span><?php endif; ?>
                </td>

                <td>
                    <?php if ($row['amende'] > 0): ?>
                        <span class="amende-badge"><i class="fa-solid fa-coins" style="font-size:10px"></i><?= number_format($row['amende'], 0) ?> DA</span>
                    <?php else: ?><span class="dash">—</span><?php endif; ?>
                </td>

                <td><span class="badge <?= $statut_css ?>"><?= $statut_lbl ?></span></td>

                <td>
                    <div class="actions-cell">
                    <?php if ($row['statut'] === 'en attente'): ?>
                        <a href="<?= $url_accept ?>"
                           class="btn-action btn-approve <?= $no_stock ? 'disabled' : '' ?>"
                           title="<?= $no_stock ? $p['tip_no_stock'] : $p['tip_accept'] ?>">
                            <i class="fa-solid fa-check"></i> <?= $p['btn_accept'] ?>
                        </a>
                        <button class="btn-action btn-refuse"
                                onclick="confirmRefuse('<?= $url_refuse ?>')">
                            <i class="fa-solid fa-xmark"></i> <?= $p['btn_refuse'] ?>
                        </button>

                    <?php elseif (in_array($row['statut'], ['acceptée','retard'])): ?>
                        <button class="btn-action btn-return"
                                onclick="confirmReturn('<?= $url_return ?>')">
                            <i class="fa-solid fa-rotate-left"></i> <?= $p['btn_return'] ?>
                        </button>

                    <?php else: ?>
                        <span class="dash">—</span>
                    <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script>
// Labels from PHP
const SWAL = {
    returnTitle : <?= json_encode($p['swal_return_title']) ?>,
    returnText  : <?= json_encode($p['swal_return_text']) ?>,
    returnYes   : <?= json_encode($p['swal_return_yes']) ?>,
    refuseTitle : <?= json_encode($p['swal_refuse_title']) ?>,
    refuseText  : <?= json_encode($p['swal_refuse_text']) ?>,
    refuseYes   : <?= json_encode($p['swal_refuse_yes']) ?>,
    cancel      : <?= json_encode($p['swal_cancel']) ?>,
};

function confirmReturn(url) {
    Swal.fire({
        title: SWAL.returnTitle,
        text:  SWAL.returnText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: SWAL.returnYes,
        cancelButtonText:  SWAL.cancel,
        confirmButtonColor: '#C4A46B',
        cancelButtonColor:  '#9A8C7E',
        background: '#FFFDF9',
        color: '#2A1F14',
    }).then(result => {
        if (result.isConfirmed) window.location.href = url;
    });
}

function confirmRefuse(url) {
    Swal.fire({
        title: SWAL.refuseTitle,
        text:  SWAL.refuseText,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: SWAL.refuseYes,
        cancelButtonText:  SWAL.cancel,
        confirmButtonColor: '#880E4F',
        cancelButtonColor:  '#9A8C7E',
        background: '#FFFDF9',
        color: '#2A1F14',
    }).then(result => {
        if (result.isConfirmed) window.location.href = url;
    });
}
</script>
</body>
</html>