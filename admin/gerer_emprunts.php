<?php
session_start();
require_once "../includes/db.php";
require_once '../includes/head.php';
include_once '../includes/languages.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// ── نصوص الصفحة حسب اللغة ──────────────────────────────
$pg = [
    'fr' => [
        'page_title'      => 'AuraLib · Gestion des Emprunts',
        'breadcrumb_dash' => 'Dashboard',
        'breadcrumb_page' => 'Emprunts',
        'hero_title'      => 'Gestion des',
        'hero_title_span' => 'Emprunts',
        'btn_back'        => 'Retour au Dashboard',
        'today'           => "Aujourd'hui",
        // Stats
        'stat_pending'    => 'En attente',
        'stat_accepted'   => 'Acceptés',
        'stat_late'       => 'En retard',
        'stat_returned'   => 'Rendus',
        'stat_refused'    => 'Refusés',
        // Flash messages
        'flash_accepted'  => 'Emprunt accepté — stock mis à jour.',
        'flash_refused'   => 'Emprunt refusé.',
        'flash_returned'  => 'Livre retourné — stock restauré.',
        'flash_fine'      => 'Amende :',
        'flash_no_stock'  => "Impossible d'accepter : stock épuisé pour ce document.",
        'flash_invalid'   => 'Action non autorisée pour ce statut.',
        'flash_error'     => 'Une erreur est survenue.',
        // Table headers
        'th_id'           => '#',
        'th_reader'       => 'Lecteur &amp; Document',
        'th_period'       => "Période d'Emprunt",
        'th_status'       => 'Statut',
        'th_actions'      => 'Actions',
        'th_fine'         => 'Amende',
        // Table content
        'lbl_start'       => 'Début',
        'lbl_return'      => 'Retour',
        'lbl_returned'    => 'Rendu',
        'days_late'       => 'j de retard',
        // Status labels
        'st_pending'      => 'En attente',
        'st_accepted'     => 'Acceptée',
        'st_late'         => 'Retard',
        'st_returned'     => 'Rendu',
        'st_refused'      => 'Refusée',
        // Action buttons
        'btn_accept'      => 'Accepter',
        'btn_refuse'      => 'Refuser',
        'btn_mark_return' => 'Marquer Rendu',
        'confirm_refuse'  => 'Refuser cet emprunt ?',
        'confirm_return'  => 'Confirmer le retour de ce livre ?',
        'tooltip_no_stock'=> 'Stock épuisé',
        'tooltip_accept'  => 'Accepter cet emprunt',
        // Empty state
        'empty'           => 'Aucun emprunt enregistré',
        'da'              => 'DA',
    ],
    'en' => [
        'page_title'      => 'AuraLib · Loan Management',
        'breadcrumb_dash' => 'Dashboard',
        'breadcrumb_page' => 'Loans',
        'hero_title'      => 'Loan',
        'hero_title_span' => 'Management',
        'btn_back'        => 'Back to Dashboard',
        'today'           => 'Today',
        // Stats
        'stat_pending'    => 'Pending',
        'stat_accepted'   => 'Accepted',
        'stat_late'       => 'Late',
        'stat_returned'   => 'Returned',
        'stat_refused'    => 'Refused',
        // Flash messages
        'flash_accepted'  => 'Loan accepted — stock updated.',
        'flash_refused'   => 'Loan refused.',
        'flash_returned'  => 'Book returned — stock restored.',
        'flash_fine'      => 'Fine:',
        'flash_no_stock'  => 'Cannot accept: no stock available for this document.',
        'flash_invalid'   => 'Action not allowed for this status.',
        'flash_error'     => 'An error occurred.',
        // Table headers
        'th_id'           => '#',
        'th_reader'       => 'Reader &amp; Document',
        'th_period'       => 'Loan Period',
        'th_status'       => 'Status',
        'th_actions'      => 'Actions',
        'th_fine'         => 'Fine',
        // Table content
        'lbl_start'       => 'Start',
        'lbl_return'      => 'Return',
        'lbl_returned'    => 'Returned',
        'days_late'       => 'd late',
        // Status labels
        'st_pending'      => 'Pending',
        'st_accepted'     => 'Accepted',
        'st_late'         => 'Late',
        'st_returned'     => 'Returned',
        'st_refused'      => 'Refused',
        // Action buttons
        'btn_accept'      => 'Accept',
        'btn_refuse'      => 'Refuse',
        'btn_mark_return' => 'Mark Returned',
        'confirm_refuse'  => 'Refuse this loan?',
        'confirm_return'  => 'Confirm book return?',
        'tooltip_no_stock'=> 'Out of stock',
        'tooltip_accept'  => 'Accept this loan',
        // Empty state
        'empty'           => 'No loans recorded',
        'da'              => 'DA',
    ],
    'ar' => [
        'page_title'      => 'AuraLib · إدارة الاستعارات',
        'breadcrumb_dash' => 'لوحة التحكم',
        'breadcrumb_page' => 'الاستعارات',
        'hero_title'      => 'إدارة',
        'hero_title_span' => 'الاستعارات',
        'btn_back'        => 'العودة للوحة التحكم',
        'today'           => 'اليوم',
        // Stats
        'stat_pending'    => 'في الانتظار',
        'stat_accepted'   => 'مقبولة',
        'stat_late'       => 'متأخرة',
        'stat_returned'   => 'مُعادة',
        'stat_refused'    => 'مرفوضة',
        // Flash messages
        'flash_accepted'  => 'تم قبول الاستعارة — تم تحديث المخزون.',
        'flash_refused'   => 'تم رفض الاستعارة.',
        'flash_returned'  => 'تم إعادة الكتاب — تم استعادة المخزون.',
        'flash_fine'      => 'الغرامة:',
        'flash_no_stock'  => 'لا يمكن القبول: المخزون نافد لهذا المستند.',
        'flash_invalid'   => 'الإجراء غير مسموح لهذا الوضع.',
        'flash_error'     => 'حدث خطأ.',
        // Table headers
        'th_id'           => '#',
        'th_reader'       => 'القارئ والمستند',
        'th_period'       => 'فترة الاستعارة',
        'th_status'       => 'الحالة',
        'th_actions'      => 'الإجراءات',
        'th_fine'         => 'الغرامة',
        // Table content
        'lbl_start'       => 'البداية',
        'lbl_return'      => 'الإعادة',
        'lbl_returned'    => 'أُعيد',
        'days_late'       => 'يوم تأخير',
        // Status labels
        'st_pending'      => 'في الانتظار',
        'st_accepted'     => 'مقبولة',
        'st_late'         => 'متأخرة',
        'st_returned'     => 'مُعادة',
        'st_refused'      => 'مرفوضة',
        // Action buttons
        'btn_accept'      => 'قبول',
        'btn_refuse'      => 'رفض',
        'btn_mark_return' => 'تسجيل الإعادة',
        'confirm_refuse'  => 'رفض هذه الاستعارة؟',
        'confirm_return'  => 'تأكيد إعادة هذا الكتاب؟',
        'tooltip_no_stock'=> 'المخزون نافد',
        'tooltip_accept'  => 'قبول هذه الاستعارة',
        // Empty state
        'empty'           => 'لا توجد استعارات مسجلة',
        'da'              => 'دج',
    ],
];
$p = $pg[$lang] ?? $pg['fr'];

// ── Auto-update retards ──────────────────────────────────
$conn->query("
    UPDATE emprunt 
    SET statut = 'retard' 
    WHERE date_retour_prevue < CURDATE() 
      AND statut = 'acceptée' 
      AND (date_fin IS NULL OR date_fin = '')
");

// ── Flash messages ───────────────────────────────────────
$msg    = $_GET['msg'] ?? '';
$amende = (int)($_GET['amende'] ?? 0);

$flash_messages = [
    'accepted'       => ['type' => 'success', 'text' => $p['flash_accepted']],
    'refused'        => ['type' => 'warning', 'text' => $p['flash_refused']],
    'returned'       => ['type' => 'success', 'text' => $p['flash_returned'] . ($amende > 0 ? " {$p['flash_fine']} <strong>{$amende} {$p['da']}</strong>" : '')],
    'no_stock'       => ['type' => 'danger',  'text' => $p['flash_no_stock']],
    'invalid_status' => ['type' => 'warning', 'text' => $p['flash_invalid']],
    'error'          => ['type' => 'danger',  'text' => $p['flash_error']],
];

// ── Stats rapides ────────────────────────────────────────
$stats = [];
foreach (['en attente','acceptée','retard','rendu','refusée'] as $s) {
    $r = $conn->query("SELECT COUNT(*) as n FROM emprunt WHERE statut = '$s'");
    $stats[$s] = (int)($r->fetch_assoc()['n'] ?? 0);
}

// ── Query ────────────────────────────────────────────────
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

$dir = $lang === 'ar' ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<?php include '../includes/dark_init.php'; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $p['page_title'] ?></title>
<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<style>
/* ══ TOKENS ══ */
:root {
    --gold:        #C4A46B;
    --gold2:       #D4B47B;
    --gold-deep:   #A8884E;
    --gold-faint:  rgba(196,164,107,.09);
    --gold-border: rgba(196,164,107,.28);
    --amber:       #B8832A;
    --brown:       #7A5C3A;
    --page-bg:     #F2EDE3;
    --page-bg2:    #E8E0D0;
    --page-white:  #FDFAF5;
    --page-text:   #2A1F14;
    --page-muted:  #9A8C7E;
    --page-border: #D8CFC0;
    --danger:      #C0392B;
    --success:     #276749;
    --warning:     #92400E;
    --font-serif:  'EB Garamond', Georgia, serif;
    --font-ui:     'Plus Jakarta Sans', sans-serif;
    --nav-h:       62px;
    --radius:      16px;
    --shadow-sm:   0 3px 10px rgba(42,31,20,.08);
    --shadow-md:   0 8px 28px rgba(42,31,20,.12);
    --shadow-lg:   0 20px 55px rgba(42,31,20,.16);
    --shadow-gold: 0 6px 20px rgba(196,164,107,.25);
    --tr:          .25s cubic-bezier(.4,0,.2,1);
}
html.dark {
    --page-bg:     #100C07;
    --page-bg2:    #1A1308;
    --page-white:  #1E1610;
    --page-text:   #EDE5D4;
    --page-muted:  #9A8C7E;
    --page-border: #3A2E1E;
    --shadow-sm:   0 3px 10px rgba(0,0,0,.3);
    --shadow-md:   0 8px 28px rgba(0,0,0,.4);
    --shadow-lg:   0 20px 55px rgba(0,0,0,.55);
}
*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
body {
    font-family: var(--font-ui);
    background: var(--page-bg);
    color: var(--page-text);
    min-height: 100vh;
    padding-top: var(--nav-h);
    transition: background .35s, color .35s;
}
<?php if ($lang === 'ar'): ?>
body { font-family: 'Noto Sans Arabic', var(--font-ui); }
th, td { text-align: right; }
.hero-inner, .actions-cell, .periode-row, .stat-pill { flex-direction: row-reverse; }
<?php endif; ?>

@keyframes fadeUp {
    from { opacity:0; transform:translateY(16px); }
    to   { opacity:1; transform:translateY(0); }
}
@keyframes rowIn {
    from { opacity:0; transform:translateX(-10px); }
    to   { opacity:1; transform:translateX(0); }
}
@keyframes pulse-ring {
    0%   { box-shadow: 0 0 0 0 rgba(192,57,43,.45); }
    70%  { box-shadow: 0 0 0 7px rgba(192,57,43,0); }
    100% { box-shadow: 0 0 0 0 rgba(192,57,43,0); }
}

/* ══ PAGE HERO ══ */
.page-hero {
    background: linear-gradient(135deg, #1A0E05 0%, #2E1D08 55%, #1A0E05 100%);
    padding: 36px 5% 32px;
    position: relative; overflow: hidden;
}
.page-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse 60% 90% at 10% 50%, rgba(196,164,107,.11) 0%, transparent 65%);
    pointer-events: none;
}
.page-hero::after {
    content: '';
    position: absolute; bottom:0; left:0; right:0; height:1px;
    background: linear-gradient(90deg, transparent, rgba(196,164,107,.3), transparent);
}
.hero-inner {
    max-width: 1340px; margin: 0 auto;
    display: flex; align-items: center; justify-content: space-between; gap: 20px;
    flex-wrap: wrap;
    animation: fadeUp .5s ease both;
}
.hero-left { display: flex; flex-direction: column; gap: 6px; }
.hero-breadcrumb {
    display: flex; align-items: center; gap: 8px;
    font-size: 11px; color: rgba(196,164,107,.5); letter-spacing: .4px;
}
.hero-breadcrumb a {
    color: rgba(196,164,107,.5); text-decoration: none; transition: color var(--tr);
}
.hero-breadcrumb a:hover { color: var(--gold); }
.hero-breadcrumb i { font-size: 8px; }
.hero-title {
    font-family: var(--font-serif);
    font-size: clamp(26px, 4vw, 44px); font-weight: 700;
    color: #FDFAF5; line-height: 1.05; letter-spacing: -.3px;
}
.hero-title span {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold2) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
}
.hero-date {
    font-size: 11px; color: rgba(253,250,245,.4); letter-spacing: .5px;
}

.btn-back {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px; border-radius: 50px;
    font-family: var(--font-ui); font-size: 12px; font-weight: 700;
    color: rgba(196,164,107,.8); letter-spacing: .3px;
    background: rgba(196,164,107,.1);
    backdrop-filter: blur(12px);
    border: 1.5px solid rgba(196,164,107,.25);
    text-decoration: none;
    transition: all var(--tr); flex-shrink: 0;
}
.btn-back:hover {
    background: rgba(196,164,107,.2);
    color: var(--gold2);
    border-color: rgba(196,164,107,.5);
    transform: translateY(-1px);
}

/* ══ STATS BAR ══ */
.stats-bar {
    max-width: 1340px; margin: 28px auto 0;
    padding: 0 5%;
    display: flex; gap: 14px; flex-wrap: wrap;
    animation: fadeUp .5s .1s ease both;
}
.stat-pill {
    display: flex; align-items: center; gap: 9px;
    padding: 10px 18px; border-radius: 50px;
    background: var(--page-white);
    border: 1.5px solid var(--page-border);
    box-shadow: var(--shadow-sm);
    flex-shrink: 0;
}
.stat-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.stat-label { font-size: 11px; color: var(--page-muted); font-weight: 500; }
.stat-num { font-family: var(--font-serif); font-size: 20px; font-weight: 700; color: var(--page-text); line-height: 1; }

/* ══ FLASH ══ */
.flash-wrap { max-width: 1340px; margin: 20px auto 0; padding: 0 5%; }
.flash {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 18px; border-radius: 14px;
    font-size: 13px; font-weight: 600;
    animation: fadeUp .4s ease both;
}
.flash i { font-size: 15px; flex-shrink: 0; }
.flash.success { background: rgba(39,103,73,.1); border: 1.5px solid rgba(39,103,73,.25); color: var(--success); }
.flash.warning { background: rgba(146,64,14,.09); border: 1.5px solid rgba(146,64,14,.22); color: var(--warning); }
.flash.danger  { background: rgba(192,57,43,.09); border: 1.5px solid rgba(192,57,43,.22); color: var(--danger); }
html.dark .flash.success { background: rgba(39,103,73,.18); }
html.dark .flash.warning { background: rgba(146,64,14,.18); }
html.dark .flash.danger  { background: rgba(192,57,43,.18); }

/* ══ TABLE WRAPPER ══ */
.table-wrap {
    max-width: 1340px; margin: 24px auto 60px;
    padding: 0 5%;
    animation: fadeUp .5s .15s ease both;
}
.table-card {
    background: var(--page-white);
    border-radius: 20px;
    border: 1px solid var(--page-border);
    overflow: hidden;
    box-shadow: var(--shadow-md);
}

/* ══ TABLE ══ */
table { width: 100%; border-collapse: collapse; }
thead tr {
    background: linear-gradient(135deg, rgba(196,164,107,.07) 0%, rgba(122,92,58,.05) 100%);
    border-bottom: 1.5px solid var(--gold-border);
}
th {
    padding: 14px 16px;
    font-family: var(--font-ui); font-size: 10px; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    color: var(--gold-deep); text-align: left; white-space: nowrap;
}
html.dark th { color: var(--gold); }

tbody tr {
    border-bottom: 1px solid var(--page-border);
    transition: background var(--tr);
    animation: rowIn .35s ease both;
}
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: var(--gold-faint); }
tbody tr:nth-child(1) { animation-delay: .04s; }
tbody tr:nth-child(2) { animation-delay: .08s; }
tbody tr:nth-child(3) { animation-delay: .12s; }
tbody tr:nth-child(4) { animation-delay: .16s; }
tbody tr:nth-child(5) { animation-delay: .20s; }
tbody tr:nth-child(6) { animation-delay: .24s; }

td {
    padding: 16px 16px;
    font-size: 13px; color: var(--page-text);
    vertical-align: middle;
}
.td-id { font-size: 11px; color: var(--page-muted); font-weight: 600; letter-spacing: .5px; }

.user-name { font-weight: 700; font-size: 14px; color: var(--page-text); margin-bottom: 3px; }
.user-book {
    display: flex; align-items: center; gap: 6px;
    font-size: 11px; color: var(--page-muted); line-height: 1.3;
}
.user-book i { color: var(--gold); font-size: 10px; flex-shrink: 0; }

.periode-cell { display: flex; flex-direction: column; gap: 4px; }
.periode-row  { display: flex; align-items: center; gap: 7px; font-size: 12px; }
.periode-label {
    font-size: 9px; font-weight: 700; letter-spacing: 1px;
    text-transform: uppercase; color: var(--page-muted);
    width: 42px; flex-shrink: 0;
}
.periode-date { font-weight: 600; color: var(--page-text); }
.periode-date.danger { color: var(--danger); }
.date-late {
    font-size: 10px; color: var(--danger); font-weight: 700;
    margin-top: 2px; display: block;
}
.date-reelle-badge {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(39,103,73,.09);
    border: 1px solid rgba(39,103,73,.22);
    color: var(--success);
    padding: 2px 8px; border-radius: 6px;
    font-size: 10px; font-weight: 700;
}

.amende-badge {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(184,131,42,.12);
    border: 1.5px solid rgba(184,131,42,.3);
    color: var(--amber); padding: 4px 10px; border-radius: 8px;
    font-size: 11px; font-weight: 700;
}
.dash { color: var(--page-border); font-size: 14px; }

/* ══ STATUS BADGES ══ */
.badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 12px; border-radius: 20px;
    font-size: 10px; font-weight: 700; letter-spacing: .6px;
    text-transform: uppercase; white-space: nowrap;
}
.badge::before { content:''; width:6px; height:6px; border-radius:50%; flex-shrink:0; }

.s-en-attente {
    background: rgba(234,179,8,.1); border: 1.5px solid rgba(234,179,8,.3); color: #92400E;
}
.s-en-attente::before { background:#F59E0B; }

.s-acceptee {
    background: rgba(39,103,73,.1); border: 1.5px solid rgba(39,103,73,.28); color: var(--success);
}
.s-acceptee::before { background: #276749; }

.s-retard {
    background: rgba(192,57,43,.1); border: 1.5px solid rgba(192,57,43,.3); color: var(--danger);
    animation: pulse-ring 2s infinite;
}
.s-retard::before { background: var(--danger); }

.s-rendu {
    background: rgba(154,140,126,.1); border: 1.5px solid rgba(154,140,126,.25); color: var(--page-muted);
}
.s-rendu::before { background: var(--page-muted); }

.s-refusee {
    background: rgba(136,14,79,.08); border: 1.5px solid rgba(136,14,79,.2); color: #880E4F;
}
.s-refusee::before { background: #880E4F; }

/* ══ ACTION BUTTONS ══ */
.actions-cell { display:flex; align-items:center; gap:7px; flex-wrap:wrap; }

.btn-action {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 50px;
    font-family: var(--font-ui); font-size: 11px; font-weight: 700;
    text-decoration: none; border: none; cursor: pointer;
    transition: all var(--tr); letter-spacing: .2px; white-space: nowrap;
}
.btn-action i { font-size: 10px; }

.btn-approve {
    background: linear-gradient(135deg, #1A0E05 0%, #2E1D08 100%);
    color: var(--gold2);
    border: 1.5px solid rgba(196,164,107,.3);
    box-shadow: 0 4px 14px rgba(42,31,20,.25);
}
.btn-approve:hover {
    background: linear-gradient(135deg, #2E1D08 0%, #3E2A10 100%);
    border-color: rgba(196,164,107,.55);
    color: var(--gold2);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(42,31,20,.35);
}
.btn-approve.disabled {
    opacity: .38; pointer-events: none; cursor: not-allowed;
}

.btn-refuse {
    background: rgba(136,14,79,.07);
    color: #880E4F;
    border: 1.5px solid rgba(136,14,79,.22);
}
.btn-refuse:hover {
    background: rgba(136,14,79,.15);
    transform: translateY(-2px);
}

.btn-return {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-deep) 100%);
    color: #1A0E05;
    border: 1.5px solid transparent;
    box-shadow: var(--shadow-gold);
    font-weight: 800;
}
.btn-return:hover {
    background: linear-gradient(135deg, var(--gold2) 0%, var(--gold) 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(196,164,107,.4);
}

/* ══ EMPTY STATE ══ */
.empty-row td { text-align: center; padding: 70px 20px; }
.empty-icon { font-size: 40px; color: var(--page-border); margin-bottom: 14px; }
.empty-row h3 { font-family: var(--font-serif); font-size:22px; color:var(--page-muted); }
</style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<!-- ══ HERO ══ -->
<div class="page-hero">
    <div class="hero-inner">
        <div class="hero-left">
            <div class="hero-breadcrumb">
                <a href="/MEMOIR/admin/admin_dashboard.php">
                    <i class="fa-solid fa-gauge-high"></i> <?= $p['breadcrumb_dash'] ?>
                </a>
                <i class="fa-solid fa-chevron-right"></i>
                <span><?= $p['breadcrumb_page'] ?></span>
            </div>
            <h1 class="hero-title"><?= $p['hero_title'] ?> <span><?= $p['hero_title_span'] ?></span></h1>
            <span class="hero-date"><?= $p['today'] ?> · <?= date('d F Y') ?></span>
        </div>
        <a href="/MEMOIR/admin/admin_dashboard.php" class="btn-back">
            <i class="fa-solid fa-arrow-left" style="font-size:10px"></i>
            <?= $p['btn_back'] ?>
        </a>
    </div>
</div>

<!-- ══ STATS BAR ══ -->
<div class="stats-bar">
    <div class="stat-pill">
        <span class="stat-dot" style="background:#F59E0B"></span>
        <span class="stat-label"><?= $p['stat_pending'] ?></span>
        <span class="stat-num"><?= $stats['en attente'] ?></span>
    </div>
    <div class="stat-pill">
        <span class="stat-dot" style="background:#276749"></span>
        <span class="stat-label"><?= $p['stat_accepted'] ?></span>
        <span class="stat-num"><?= $stats['acceptée'] ?></span>
    </div>
    <div class="stat-pill">
        <span class="stat-dot" style="background:var(--danger)"></span>
        <span class="stat-label"><?= $p['stat_late'] ?></span>
        <span class="stat-num"><?= $stats['retard'] ?></span>
    </div>
    <div class="stat-pill">
        <span class="stat-dot" style="background:var(--page-muted)"></span>
        <span class="stat-label"><?= $p['stat_returned'] ?></span>
        <span class="stat-num"><?= $stats['rendu'] ?></span>
    </div>
    <div class="stat-pill">
        <span class="stat-dot" style="background:#880E4F"></span>
        <span class="stat-label"><?= $p['stat_refused'] ?></span>
        <span class="stat-num"><?= $stats['refusée'] ?></span>
    </div>
</div>

<!-- ══ FLASH ══ -->
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

<!-- ══ TABLE ══ -->
<div class="table-wrap">
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th><?= $p['th_id'] ?></th>
                    <th><?= $p['th_reader'] ?></th>
                    <th><?= $p['th_period'] ?></th>
                    <th><?= $p['th_status'] ?></th>
                    <th><?= $p['th_actions'] ?></th>
                    <th><?= $p['th_fine'] ?></th>
                </tr>
            </thead>
            <tbody>
            <?php
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            if (empty($rows)): ?>
            <tr class="empty-row">
                <td colspan="6">
                    <div class="empty-icon"><i class="fa-regular fa-folder-open"></i></div>
                    <h3><?= $p['empty'] ?></h3>
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
                $statut_lbl = match($row['statut']) {
                    'en attente' => $p['st_pending'],
                    'acceptée'   => $p['st_accepted'],
                    'retard'     => $p['st_late'],
                    'rendu'      => $p['st_returned'],
                    'refusée'    => $p['st_refused'],
                    default      => $row['statut']
                };
                $jours_retard = 0;
                if ($row['statut'] === 'retard' && !empty($row['date_retour_prevue'])) {
                    $jours_retard = (int)(new DateTime())->diff(new DateTime($row['date_retour_prevue']))->days;
                }
                $est_rendu = $row['statut'] === 'rendu';
                $date_fin_affichee = $est_rendu && !empty($row['date_fin'])
                    ? $row['date_fin']
                    : $row['date_retour_prevue'];
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
                    <div class="periode-cell">
                        <div class="periode-row">
                            <span class="periode-label"><?= $p['lbl_start'] ?></span>
                            <span class="periode-date">
                                <?= $row['date_debut'] ? date('d/m/Y', strtotime($row['date_debut'])) : '—' ?>
                            </span>
                        </div>

                        <?php if ($date_fin_affichee): ?>
                        <div class="periode-row">
                            <span class="periode-label" style="color:<?= $est_rendu ? 'var(--success)' : ($row['statut']==='retard' ? 'var(--danger)' : 'var(--page-muted)') ?>">
                                <?= $est_rendu ? $p['lbl_returned'] : $p['lbl_return'] ?>
                            </span>
                            <?php if ($est_rendu): ?>
                                <span class="date-reelle-badge">
                                    <i class="fa-solid fa-circle-check" style="font-size:9px"></i>
                                    <?= date('d/m/Y', strtotime($date_fin_affichee)) ?>
                                </span>
                            <?php else: ?>
                                <span class="periode-date <?= $row['statut']==='retard' ? 'danger' : '' ?>">
                                    <?= date('d/m/Y', strtotime($date_fin_affichee)) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($row['statut'] === 'retard'): ?>
                            <span class="date-late">
                                <i class="fa-solid fa-clock" style="font-size:9px"></i>
                                <?= $jours_retard ?> <?= $p['days_late'] ?>
                            </span>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="periode-row">
                            <span class="periode-label"><?= $p['lbl_return'] ?></span>
                            <span class="dash">—</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </td>

                <td>
                    <span class="badge <?= $statut_css ?>">
                        <?= $statut_lbl ?>
                    </span>
                </td>

                <td>
                    <div class="actions-cell">
                    <?php if ($row['statut'] === 'en attente'): ?>
                        <a href="action_emprunts.php?id=<?= $row['id_emprunt'] ?>&action=accepter"
                           class="btn-action btn-approve <?= (int)$row['exemplaires_disponibles'] <= 0 ? 'disabled' : '' ?>"
                           title="<?= (int)$row['exemplaires_disponibles'] <= 0 ? $p['tooltip_no_stock'] : $p['tooltip_accept'] ?>">
                            <i class="fa-solid fa-check"></i> <?= $p['btn_accept'] ?>
                        </a>
                        <a href="action_emprunts.php?id=<?= $row['id_emprunt'] ?>&action=refuser"
                           class="btn-action btn-refuse"
                           onclick="return confirm('<?= addslashes($p['confirm_refuse']) ?>')">
                            <i class="fa-solid fa-xmark"></i> <?= $p['btn_refuse'] ?>
                        </a>

                    <?php elseif (in_array($row['statut'], ['acceptée','retard'])): ?>
                        <a href="action_emprunts.php?id=<?= $row['id_emprunt'] ?>&action=rendre"
                           class="btn-action btn-return"
                           onclick="return confirm('<?= addslashes($p['confirm_return']) ?>')">
                            <i class="fa-solid fa-rotate-left"></i> <?= $p['btn_mark_return'] ?>
                        </a>

                    <?php else: ?>
                        <span class="dash">—</span>
                    <?php endif; ?>
                    </div>
                </td>

                <td>
                    <?php if ($row['amende'] > 0): ?>
                        <span class="amende-badge">
                            <i class="fa-solid fa-coins" style="font-size:10px"></i>
                            <?= number_format($row['amende'], 0) ?> <?= $p['da'] ?>
                        </span>
                    <?php else: ?>
                        <span class="dash">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>