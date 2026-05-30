<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$host = "localhost"; $user = "root"; $pass = ""; $dbname = "memoir_db";
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

require_once '../includes/head.php';
include_once '../includes/languages.php';

function fmtOrderId($id, $date) {
    return 'ARL-' . str_pad($id, 4, '0', STR_PAD_LEFT) . '-' . date('Y', strtotime($date));
}

$pg = [
    'fr' => [
        'page_title'   => 'AuraLib · Gestion des Commandes',
        'breadcrumb'   => 'Dashboard',
        'hero_title'   => 'Gestion des',
        'hero_span'    => 'Commandes',
        'today'        => "Aujourd'hui",
        'btn_back'     => 'Retour au Dashboard',
        'stat_revenue' => 'Revenus Totaux',
        'stat_profit'  => 'Bénéfice Net',
        'stat_sold'    => 'Livres Vendus',
        'stat_copies'  => 'exemplaires',
        'table_title'  => 'Liste des Commandes',
        'th_num'       => 'Référence',
        'th_client'    => 'Lecteur &amp; Détails',
        'th_total'     => 'Total',
        'th_date'      => 'Date &amp; Heure',
        'th_status'    => 'Statut',
        'th_action'    => 'Action',
        'status_paid'  => 'Payée',
        'status_wait'  => 'En attente',
        'confirmed'    => '✓ Confirmé Auto',
        'invoice'      => 'Facture PDF',
        'stock_left'   => 'Reste :',
        'stock_unit'   => 'en stock',
        'inv_title'    => 'FACTURE',
        'inv_ref'      => 'Réf. Commande',
        'inv_client'   => 'Client',
        'inv_date'     => 'Date',
        'inv_status'   => 'Statut',
        'inv_col_doc'  => 'Document',
        'inv_col_qty'  => 'Qté',
        'inv_col_pu'   => 'Prix unitaire',
        'inv_col_total'=> 'Total',
        'inv_total'    => 'TOTAL À PAYER',
        'inv_footer'   => 'Merci pour votre confiance — AuraLib',
        'inv_paid_lbl' => 'PAYÉE',
        'inv_wait_lbl' => 'EN ATTENTE',
        'btn_print'    => '🖨 Imprimer',
        'btn_close'    => 'Fermer',
        'empty'        => 'Aucune commande enregistrée',
        'da'           => 'DA',
    ],
    'en' => [
        'page_title'   => 'AuraLib · Order Management',
        'breadcrumb'   => 'Dashboard',
        'hero_title'   => 'Order',
        'hero_span'    => 'Management',
        'today'        => 'Today',
        'btn_back'     => 'Back to Dashboard',
        'stat_revenue' => 'Total Revenue',
        'stat_profit'  => 'Net Profit',
        'stat_sold'    => 'Books Sold',
        'stat_copies'  => 'copies',
        'table_title'  => 'Order List',
        'th_num'       => 'Reference',
        'th_client'    => 'Reader &amp; Details',
        'th_total'     => 'Total',
        'th_date'      => 'Date &amp; Time',
        'th_status'    => 'Status',
        'th_action'    => 'Action',
        'status_paid'  => 'Paid',
        'status_wait'  => 'Pending',
        'confirmed'    => '✓ Auto Confirmed',
        'invoice'      => 'PDF Invoice',
        'stock_left'   => 'Left:',
        'stock_unit'   => 'in stock',
        'inv_title'    => 'INVOICE',
        'inv_ref'      => 'Order Ref.',
        'inv_client'   => 'Client',
        'inv_date'     => 'Date',
        'inv_status'   => 'Status',
        'inv_col_doc'  => 'Document',
        'inv_col_qty'  => 'Qty',
        'inv_col_pu'   => 'Unit Price',
        'inv_col_total'=> 'Total',
        'inv_total'    => 'TOTAL DUE',
        'inv_footer'   => 'Thank you for your trust — AuraLib',
        'inv_paid_lbl' => 'PAID',
        'inv_wait_lbl' => 'PENDING',
        'btn_print'    => '🖨 Print',
        'btn_close'    => 'Close',
        'empty'        => 'No orders recorded',
        'da'           => 'DA',
    ],
    'ar' => [
        'page_title'   => 'AuraLib · إدارة الطلبات',
        'breadcrumb'   => 'لوحة التحكم',
        'hero_title'   => 'إدارة',
        'hero_span'    => 'الطلبات',
        'today'        => 'اليوم',
        'btn_back'     => 'العودة للوحة التحكم',
        'stat_revenue' => 'إجمالي الإيرادات',
        'stat_profit'  => 'صافي الربح',
        'stat_sold'    => 'الكتب المباعة',
        'stat_copies'  => 'نسخة',
        'table_title'  => 'قائمة الطلبات',
        'th_num'       => 'المرجع',
        'th_client'    => 'القارئ والتفاصيل',
        'th_total'     => 'الإجمالي',
        'th_date'      => 'التاريخ والوقت',
        'th_status'    => 'الحالة',
        'th_action'    => 'الإجراء',
        'status_paid'  => 'مدفوعة',
        'status_wait'  => 'في الانتظار',
        'confirmed'    => '✓ تأكيد تلقائي',
        'invoice'      => 'فاتورة PDF',
        'stock_left'   => 'متبقي:',
        'stock_unit'   => 'في المخزن',
        'inv_title'    => 'فاتورة',
        'inv_ref'      => 'رقم الطلب',
        'inv_client'   => 'العميل',
        'inv_date'     => 'التاريخ',
        'inv_status'   => 'الحالة',
        'inv_col_doc'  => 'الوثيقة',
        'inv_col_qty'  => 'الكمية',
        'inv_col_pu'   => 'السعر الوحدوي',
        'inv_col_total'=> 'المجموع',
        'inv_total'    => 'المبلغ الإجمالي',
        'inv_footer'   => 'شكراً لثقتكم — AuraLib',
        'inv_paid_lbl' => 'مدفوع',
        'inv_wait_lbl' => 'في الانتظار',
        'btn_print'    => '🖨 طباعة',
        'btn_close'    => 'إغلاق',
        'empty'        => 'لا توجد طلبات مسجلة',
        'da'           => 'دج',
    ],
];
$p     = $pg[$lang] ?? $pg['fr'];
$isRtl = ($lang === 'ar');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php"); exit;
}

// ── Stats ──────────────────────────────────────────────
$rev_res = $conn->query("
    SELECT SUM(total) as tr FROM commande
    WHERE statut IN ('payée', 'en attente de paiement')
")->fetch_assoc();
$total_revenue = $rev_res['tr'] ?? 0;

$profit_res = $conn->query("
    SELECT SUM((ci.prix - IFNULL(d.prix_achat, 0)) * ci.quantite) as net_profit
    FROM commande_item ci
    JOIN documents d ON ci.id_doc = d.id_doc
    JOIN commande c ON ci.id_commande = c.id_commande
    WHERE c.statut IN ('payée', 'en attente de paiement')
")->fetch_assoc();
$total_profit = $profit_res['net_profit'] ?? 0;

$sales_res = $conn->query("
    SELECT SUM(quantite) as tb FROM commande_item ci
    JOIN commande c ON ci.id_commande = c.id_commande
    WHERE c.statut IN ('payée', 'en attente de paiement')
")->fetch_assoc();
$total_sold = $sales_res['tb'] ?? 0;

// ── Main query ─────────────────────────────────────────
$query = "
    SELECT c.id_commande, c.total, c.date_commande, c.statut,
           u.firstname, u.lastname,
           GROUP_CONCAT(
               CONCAT('<b>', d.titre, '</b> (x', ci.quantite, ')
               <br><small>" . addslashes($p['stock_left']) . " ', d.exemplaires_disponibles, ' " . addslashes($p['stock_unit']) . "</small>')
               SEPARATOR '<hr style=\"margin:5px 0;border:0;border-top:1px solid rgba(196,164,107,.15);\">') as order_details
    FROM commande c
    JOIN users u ON c.id_user = u.id
    JOIN commande_item ci ON c.id_commande = ci.id_commande
    JOIN documents d ON ci.id_doc = d.id_doc
    GROUP BY c.id_commande
    ORDER BY c.date_commande DESC";
$result = $conn->query($query);

// ── Invoices data ──────────────────────────────────────
$invoices = [];
$inv_q = $conn->query("
    SELECT c.id_commande, c.total, c.date_commande, c.statut,
           u.firstname, u.lastname, u.email,
           d.titre, ci.quantite, ci.prix
    FROM commande c
    JOIN users u ON c.id_user = u.id
    JOIN commande_item ci ON c.id_commande = ci.id_commande
    JOIN documents d ON ci.id_doc = d.id_doc
    WHERE c.statut IN ('payée', 'en attente de paiement')
    ORDER BY c.id_commande DESC, d.titre ASC
");
if ($inv_q) {
    while ($r = $inv_q->fetch_assoc()) {
        $id = $r['id_commande'];
        if (!isset($invoices[$id])) {
            $invoices[$id] = [
                'id'        => $id,
                'ref'       => fmtOrderId($id, $r['date_commande']),
                'date'      => $r['date_commande'],
                'total'     => $r['total'],
                'statut'    => $r['statut'],
                'firstname' => $r['firstname'],
                'lastname'  => $r['lastname'],
                'email'     => $r['email'],
                'items'     => [],
            ];
        }
        $invoices[$id]['items'][] = [
            'titre'    => $r['titre'],
            'quantite' => $r['quantite'],
            'prix'     => $r['prix'],
        ];
    }
}
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
    --ink:         #1A0E05;
    --ink2:        #2E1D08;
    --page-bg:     #F2EDE3;
    --page-bg2:    #E8E0D0;
    --page-white:  #FDFAF5;
    --page-text:   #2A1F14;
    --page-muted:  #9A8C7E;
    --page-border: #D8CFC0;
    --success:     #276749;
    --success-bg:  rgba(39,103,73,.09);
    --pending-clr: #B8832A;
    --pending-bg:  rgba(184,131,42,.09);
    --pending-brd: rgba(184,131,42,.25);
    --font-serif:  'EB Garamond', Georgia, serif;
    --font-ui:     <?= $isRtl ? "'Tajawal', sans-serif" : "'Plus Jakarta Sans', sans-serif" ?>;
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
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: var(--font-ui);
    background: var(--page-bg);
    color: var(--page-text);
    min-height: 100vh;
    padding-top: var(--nav-h);
    transition: background .35s, color .35s;
    direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
}
<?php if ($isRtl): ?>
th, td { text-align: right !important; }
.hero-inner, .stat-pill, .actions-cell { flex-direction: row-reverse; }
<?php endif; ?>

@keyframes fadeUp {
    from { opacity:0; transform:translateY(16px); }
    to   { opacity:1; transform:translateY(0); }
}
@keyframes rowIn {
    from { opacity:0; transform:translateX(-10px); }
    to   { opacity:1; transform:translateX(0); }
}

/* ══ PAGE HERO ══ */
.page-hero {
    background: linear-gradient(135deg, var(--ink) 0%, var(--ink2) 55%, var(--ink) 100%);
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
    position: absolute; bottom: 0; left: 0; right: 0; height: 1px;
    background: linear-gradient(90deg, transparent, rgba(196,164,107,.3), transparent);
}
.hero-inner {
    max-width: 1340px; margin: 0 auto;
    display: flex; align-items: center; justify-content: space-between;
    gap: 20px; flex-wrap: wrap;
    animation: fadeUp .5s ease both;
}
.hero-left { display: flex; flex-direction: column; gap: 6px; }
.hero-breadcrumb {
    display: flex; align-items: center; gap: 8px;
    font-size: 11px; color: rgba(196,164,107,.5); letter-spacing: .4px;
}
.hero-breadcrumb a { color: rgba(196,164,107,.5); text-decoration: none; transition: color var(--tr); }
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
.hero-date { font-size: 11px; color: rgba(253,250,245,.4); letter-spacing: .5px; }

.btn-back {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px; border-radius: 50px;
    font-family: var(--font-ui); font-size: 12px; font-weight: 700;
    color: rgba(196,164,107,.8); letter-spacing: .3px;
    background: rgba(196,164,107,.1); backdrop-filter: blur(12px);
    border: 1.5px solid rgba(196,164,107,.25);
    text-decoration: none; transition: all var(--tr); flex-shrink: 0;
}
.btn-back:hover {
    background: rgba(196,164,107,.2); color: var(--gold2);
    border-color: rgba(196,164,107,.5); transform: translateY(-1px);
}

/* ══ STATS BAR ══ */
.stats-bar {
    max-width: 1340px; margin: 28px auto 0; padding: 0 5%;
    display: flex; gap: 14px; flex-wrap: wrap;
    animation: fadeUp .5s .1s ease both;
}
.stat-pill {
    display: flex; align-items: center; gap: 9px;
    padding: 10px 18px; border-radius: 50px;
    background: var(--page-white); border: 1.5px solid var(--page-border);
    box-shadow: var(--shadow-sm); flex-shrink: 0;
}
.stat-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.stat-label { font-size: 11px; color: var(--page-muted); font-weight: 500; }
.stat-val {
    font-family: var(--font-serif); font-size: 20px; font-weight: 700;
    color: var(--page-text); line-height: 1;
}
.stat-unit { font-size: 10px; color: var(--page-muted); margin-left: 2px; }

/* ══ MAIN WRAPPER ══ */
.table-wrap {
    max-width: 1340px; margin: 24px auto 60px; padding: 0 5%;
    animation: fadeUp .5s .15s ease both;
}

/* ══ SECTION HEADER ══ */
.section-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 22px 28px;
    background: linear-gradient(135deg, var(--ink) 0%, var(--ink2) 100%);
    border-radius: 20px 20px 0 0;
    border-bottom: 1px solid var(--gold-border);
}
.section-title {
    font-family: var(--font-serif); font-size: 22px; font-weight: 700;
    color: var(--gold); letter-spacing: -.2px;
}
.section-badge {
    font-size: 10px; font-weight: 700; letter-spacing: 1px;
    text-transform: uppercase; color: rgba(196,164,107,.6);
    background: rgba(196,164,107,.08);
    border: 1px solid rgba(196,164,107,.18);
    padding: 4px 12px; border-radius: 20px;
}

/* ══ TABLE CARD ══ */
.table-card {
    background: var(--page-white);
    border-radius: 20px;
    border: 1px solid var(--page-border);
    overflow: hidden;
    box-shadow: var(--shadow-md);
}
.table-scroll { overflow-x: auto; }

table { width: 100%; border-collapse: collapse; }
thead tr {
    background: linear-gradient(135deg, rgba(196,164,107,.07) 0%, rgba(122,92,58,.05) 100%);
    border-bottom: 1.5px solid var(--gold-border);
}
th {
    padding: 14px 18px;
    font-size: 10px; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; color: var(--gold-deep);
    text-align: left; white-space: nowrap;
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
td {
    padding: 18px 18px; font-size: 13px;
    color: var(--page-text); vertical-align: middle;
}

/* ── Order ID ── */
.order-id {
    font-family: var(--font-serif); font-size: 15px; font-weight: 700;
    color: var(--gold-deep); letter-spacing: .5px; white-space: nowrap;
}
html.dark .order-id { color: var(--gold); }

/* ── Client + details ── */
.client-name {
    font-weight: 700; font-size: 14px;
    color: var(--page-text); margin-bottom: 6px; text-transform: capitalize;
}
.details-box {
    font-size: 12px; color: var(--page-muted);
    line-height: 1.7; max-width: 320px;
}
.details-box b { color: var(--page-text); }
.details-box small { color: var(--amber); font-weight: 600; }

/* ── Price ── */
.prix-tag {
    font-family: var(--font-serif); font-size: 20px; font-weight: 700;
    color: var(--success); letter-spacing: -.3px; white-space: nowrap;
}
.prix-unit { font-family: var(--font-ui); font-size: 12px; color: var(--page-muted); font-weight: 400; margin-left: 3px; }

/* ── Date ── */
.date-cell { font-size: 12px; color: var(--page-muted); line-height: 1.6; white-space: nowrap; }
.date-cell strong { color: var(--page-text); font-size: 13px; display: block; }

/* ── Status badges ── */
.status-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 13px; border-radius: 20px;
    font-size: 10px; font-weight: 700; letter-spacing: .6px;
    text-transform: uppercase; white-space: nowrap;
}
.status-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.status-pill.paid {
    background: var(--success-bg); color: var(--success);
    border: 1.5px solid rgba(39,103,73,.22);
}
.status-pill.paid::before { background: var(--success); }
.status-pill.pending {
    background: var(--pending-bg); color: var(--pending-clr);
    border: 1.5px solid var(--pending-brd);
}
.status-pill.pending::before { background: var(--pending-clr); }

/* ── Action cell ── */
.action-cell { display: flex; flex-direction: column; gap: 6px; }
.confirmed-text {
    font-size: 11px; font-weight: 700; color: var(--success);
    display: flex; align-items: center; gap: 5px;
}
.btn-invoice {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 14px; border-radius: 50px;
    font-family: var(--font-ui); font-size: 11px; font-weight: 700;
    color: var(--gold-deep); border: 1.5px solid var(--gold-border);
    background: var(--gold-faint); cursor: pointer;
    transition: all var(--tr); white-space: nowrap;
}
html.dark .btn-invoice { color: var(--gold); }
.btn-invoice:hover {
    background: var(--gold); color: var(--ink);
    border-color: var(--gold); box-shadow: var(--shadow-gold);
    transform: translateY(-1px);
}
.btn-invoice i { font-size: 10px; }

/* ── Empty ── */
.empty-row td { text-align: center; padding: 70px 20px; }
.empty-icon { font-size: 40px; color: var(--page-border); margin-bottom: 14px; }
.empty-row h3 { font-family: var(--font-serif); font-size: 22px; color: var(--page-muted); }

@media (max-width: 768px) {
    .stats-bar { gap: 10px; }
    .stat-pill { padding: 8px 14px; }
    th, td { padding: 12px 12px; }
}

/* ══════════════════════════════════════════════
   INVOICE MODAL — luxury restyle
══════════════════════════════════════════════ */
.inv-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(10,6,2,.78); backdrop-filter: blur(8px);
    z-index: 2000; align-items: center; justify-content: center; padding: 20px;
}
.inv-overlay.open { display: flex; }

.inv-modal {
    background: var(--page-white);
    border-radius: 20px;
    box-shadow: 0 40px 100px rgba(10,6,2,.6), 0 0 0 1px var(--gold-border);
    width: 100%; max-width: 660px; max-height: 90vh; overflow-y: auto;
    animation: invIn .28s cubic-bezier(.4,0,.2,1) both;
    scrollbar-width: thin; scrollbar-color: rgba(196,164,107,.25) transparent;
}
.inv-modal::-webkit-scrollbar { width: 3px; }
.inv-modal::-webkit-scrollbar-thumb { background: rgba(196,164,107,.3); border-radius: 3px; }

@keyframes invIn {
    from { opacity:0; transform:translateY(24px) scale(.97); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}

/* Modal top bar */
.inv-topbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 24px;
    background: linear-gradient(135deg, var(--ink) 0%, var(--ink2) 100%);
    border-radius: 20px 20px 0 0;
    border-bottom: 1px solid rgba(196,164,107,.2);
    position: sticky; top: 0; z-index: 10;
}
.inv-topbar-title {
    font-family: var(--font-serif); font-size: 16px; font-weight: 600;
    color: var(--gold); letter-spacing: .3px;
}
.inv-topbar-actions { display: flex; gap: 10px; align-items: center; }
.btn-inv-print {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--gold); color: var(--ink);
    border: none; padding: 8px 18px; border-radius: 50px;
    font-size: 11px; font-weight: 800; cursor: pointer;
    font-family: var(--font-ui); transition: background var(--tr);
    letter-spacing: .3px;
}
.btn-inv-print:hover { background: var(--gold2); }
.btn-inv-close {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(253,250,245,.07);
    border: 1.5px solid rgba(253,250,245,.15);
    color: rgba(253,250,245,.65);
    padding: 8px 16px; border-radius: 50px;
    font-size: 11px; font-weight: 700; cursor: pointer;
    font-family: var(--font-ui); transition: all var(--tr);
}
.btn-inv-close:hover { background: rgba(253,250,245,.14); color: #FDFAF5; }

/* Invoice content */
.inv-body { padding: 36px 40px; }

.inv-head {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 30px; flex-wrap: wrap; gap: 16px;
}
.inv-brand { font-family: var(--font-serif); font-size: 32px; font-weight: 700; color: var(--page-text); line-height: 1; }
.inv-brand em { color: var(--gold); font-style: normal; }
.inv-brand-sub { font-size: 9px; letter-spacing: 3.5px; text-transform: uppercase; color: var(--page-muted); margin-top: 4px; }
.inv-title-block { text-align: right; }
.inv-title-word {
    font-family: var(--font-serif); font-size: 30px; font-weight: 700;
    color: var(--gold); line-height: 1;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold2) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
}
.inv-ref { font-size: 11px; color: var(--page-muted); margin-top: 5px; font-weight: 600; letter-spacing: .5px; }
.inv-stamp {
    display: inline-block; margin-top: 8px;
    font-size: 9px; font-weight: 800; letter-spacing: 2px;
    padding: 4px 12px; border-radius: 4px;
    transform: rotate(-3deg);
}
.inv-stamp.paid { border: 2px solid var(--success); color: var(--success); }
.inv-stamp.pending { border: 2px solid var(--pending-clr); color: var(--pending-clr); }

/* Separator line */
.inv-divider {
    height: 1px; margin: 0 0 24px;
    background: linear-gradient(90deg, transparent, var(--gold-border), transparent);
}

.inv-meta {
    display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
    background: var(--page-bg);
    border: 1px solid var(--page-border);
    border-radius: 12px; padding: 18px 20px; margin-bottom: 26px;
    font-size: 12px;
}
html.dark .inv-meta { background: var(--page-bg2); }
.inv-meta-lbl {
    font-size: 9px; font-weight: 700; letter-spacing: 1.5px;
    text-transform: uppercase; color: var(--page-muted); margin-bottom: 3px;
}
.inv-meta-val { color: var(--page-text); font-weight: 600; font-size: 13px; }

/* Invoice table */
.inv-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px; border-radius: 12px; overflow: hidden; }
.inv-table thead tr { background: linear-gradient(135deg, var(--ink) 0%, var(--ink2) 100%); }
.inv-table th {
    padding: 11px 15px; text-align: left;
    font-size: 9px; letter-spacing: 1.5px; text-transform: uppercase;
    font-weight: 700; color: rgba(196,164,107,.7); white-space: nowrap;
}
.inv-table th:last-child { text-align: right; }
.inv-table td {
    padding: 12px 15px;
    border-bottom: 1px solid var(--page-border);
    color: var(--page-text); font-size: 13px;
}
.inv-table td:last-child { text-align: right; font-weight: 700; color: var(--amber); }
.inv-table tbody tr:last-child td { border-bottom: none; }
.inv-table tbody tr:hover td { background: var(--gold-faint); }

/* Total row */
.inv-total-row {
    display: flex; justify-content: flex-end; align-items: center; gap: 20px;
    padding: 16px 20px;
    background: linear-gradient(135deg, var(--ink) 0%, var(--ink2) 100%);
    border-radius: 12px; margin-bottom: 26px;
    border: 1px solid rgba(196,164,107,.2);
}
.inv-total-lbl {
    font-size: 9px; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; color: rgba(196,164,107,.55);
}
.inv-total-val {
    font-family: var(--font-serif); font-size: 28px; font-weight: 700;
    color: var(--gold); line-height: 1;
}
.inv-total-val span { font-size: 14px; color: rgba(196,164,107,.55); margin-left: 5px; }

/* Footer */
.inv-footer-line {
    text-align: center; font-size: 11px; color: var(--page-muted);
    padding-top: 18px;
    border-top: 1px solid var(--page-border);
    letter-spacing: .3px;
}
.inv-footer-line::before { content: '✦ '; color: var(--gold); }
.inv-footer-line::after  { content: ' ✦'; color: var(--gold); }

@media (max-width: 600px) {
    .inv-body { padding: 24px 20px; }
    .inv-meta { grid-template-columns: 1fr; }
}

@media print {
    body > *:not(.inv-overlay) { display: none !important; }
    .inv-overlay { display: flex !important; position: static !important; background: none !important; backdrop-filter: none !important; padding: 0 !important; }
    .inv-modal { box-shadow: none !important; max-height: none !important; overflow: visible !important; border-radius: 0 !important; }
    .inv-topbar { display: none !important; }
}
</style>
</head>
<body>
<?php include "../includes/header.php"; ?>

<!-- ══ HERO ══ -->
<div class="page-hero">
    <div class="hero-inner">
        <div class="hero-left">
            <div class="hero-breadcrumb">
                <a href="admin_dashboard.php">
                    <i class="fa-solid fa-gauge-high"></i> <?= $p['breadcrumb'] ?>
                </a>
                <i class="fa-solid fa-chevron-right"></i>
                <span><?= $p['hero_span'] ?></span>
            </div>
            <h1 class="hero-title"><?= $p['hero_title'] ?> <span><?= $p['hero_span'] ?></span></h1>
            <span class="hero-date"><?= $p['today'] ?> · <?= date('d F Y') ?></span>
        </div>
        <a href="admin_dashboard.php" class="btn-back">
            <i class="fa-solid fa-arrow-left" style="font-size:10px"></i>
            <?= $p['btn_back'] ?>
        </a>
    </div>
</div>

<!-- ══ STATS BAR ══ -->
<div class="stats-bar">
    <div class="stat-pill">
        <span class="stat-dot" style="background:var(--gold)"></span>
        <span class="stat-label"><?= $p['stat_revenue'] ?></span>
        <span class="stat-val"><?= number_format($total_revenue, 0, ',', ' ') ?></span>
        <span class="stat-unit"><?= $p['da'] ?></span>
    </div>
    <div class="stat-pill">
        <span class="stat-dot" style="background:var(--success)"></span>
        <span class="stat-label"><?= $p['stat_profit'] ?></span>
        <span class="stat-val"><?= number_format($total_profit, 0, ',', ' ') ?></span>
        <span class="stat-unit"><?= $p['da'] ?></span>
    </div>
    <div class="stat-pill">
        <span class="stat-dot" style="background:#60a5fa"></span>
        <span class="stat-label"><?= $p['stat_sold'] ?></span>
        <span class="stat-val"><?= $total_sold ?></span>
        <span class="stat-unit"><?= $p['stat_copies'] ?></span>
    </div>
</div>

<!-- ══ TABLE ══ -->
<div class="table-wrap">
    <div class="table-card">
        <!-- dark header bar -->
        <div class="section-head">
            <span class="section-title"><?= $p['table_title'] ?></span>
            <span class="section-badge">AuraLib</span>
        </div>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th><?= $p['th_num'] ?></th>
                        <th><?= $p['th_client'] ?></th>
                        <th><?= $p['th_total'] ?></th>
                        <th><?= $p['th_date'] ?></th>
                        <th><?= $p['th_status'] ?></th>
                        <th><?= $p['th_action'] ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><span class="order-id"><?= fmtOrderId($row['id_commande'], $row['date_commande']) ?></span></td>
                    <td>
                        <div class="client-name"><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></div>
                        <div class="details-box"><?= $row['order_details'] ?></div>
                    </td>
                    <td>
                        <span class="prix-tag">
                            <?= number_format($row['total'], 2) ?>
                            <span class="prix-unit"><?= $p['da'] ?></span>
                        </span>
                    </td>
                    <td class="date-cell">
                        <strong><?= date('d M Y', strtotime($row['date_commande'])) ?></strong>
                        <?= date('H:i', strtotime($row['date_commande'])) ?>
                    </td>
                    <td>
                        <?php if ($row['statut'] === 'payée'): ?>
                            <span class="status-pill paid"><?= $p['status_paid'] ?></span>
                        <?php else: ?>
                            <span class="status-pill pending"><?= $p['status_wait'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-cell">
                            <span class="confirmed-text">
                                <i class="fa-solid fa-circle-check" style="font-size:10px"></i>
                                <?= $p['confirmed'] ?>
                            </span>
                            <button onclick="showInvoice(<?= $row['id_commande'] ?>)" class="btn-invoice">
                                <i class="fa-regular fa-file-lines"></i>
                                <?= $p['invoice'] ?>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile;
                else: ?>
                <tr class="empty-row">
                    <td colspan="6">
                        <div class="empty-icon"><i class="fa-regular fa-folder-open"></i></div>
                        <h3><?= $p['empty'] ?></h3>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

<!-- ══ INVOICE MODAL ══ -->
<div class="inv-overlay" id="invOverlay" onclick="closeOnOverlay(event)">
    <div class="inv-modal" id="invModal">
        <div class="inv-topbar">
            <span class="inv-topbar-title" id="invTopTitle"></span>
            <div class="inv-topbar-actions">
                <button class="btn-inv-print" onclick="window.print()">
                    <i class="fa-solid fa-print" style="font-size:10px"></i> <?= $p['btn_print'] ?>
                </button>
                <button class="btn-inv-close" onclick="closeInvoice()">
                    <i class="fa-solid fa-xmark" style="font-size:10px"></i> <?= $p['btn_close'] ?>
                </button>
            </div>
        </div>
        <div class="inv-body" id="invBody"></div>
    </div>
</div>

<script>
const INVOICES = <?= json_encode($invoices) ?>;
const INV = {
    title    : <?= json_encode($p['inv_title']) ?>,
    ref      : <?= json_encode($p['inv_ref']) ?>,
    client   : <?= json_encode($p['inv_client']) ?>,
    date     : <?= json_encode($p['inv_date']) ?>,
    status   : <?= json_encode($p['inv_status']) ?>,
    colDoc   : <?= json_encode($p['inv_col_doc']) ?>,
    colQty   : <?= json_encode($p['inv_col_qty']) ?>,
    colPu    : <?= json_encode($p['inv_col_pu']) ?>,
    colTotal : <?= json_encode($p['inv_col_total']) ?>,
    total    : <?= json_encode($p['inv_total']) ?>,
    footer   : <?= json_encode($p['inv_footer']) ?>,
    paid     : <?= json_encode($p['inv_paid_lbl']) ?>,
    wait     : <?= json_encode($p['inv_wait_lbl']) ?>,
    da       : <?= json_encode($p['da']) ?>,
};

function fmtDate(str) {
    if (!str) return '—';
    return new Date(str).toLocaleDateString(
        '<?= $lang === 'ar' ? 'ar-DZ' : ($lang === 'en' ? 'en-GB' : 'fr-FR') ?>',
        { day: '2-digit', month: 'long', year: 'numeric' }
    );
}
function fmtNum(n) {
    return parseFloat(n).toLocaleString('fr-DZ', { minimumFractionDigits: 2 });
}

function showInvoice(id) {
    const inv = INVOICES[id];
    if (!inv) return;

    const isPaid  = inv.statut === 'payée';
    const stamp   = `<span class="inv-stamp ${isPaid ? 'paid' : 'pending'}">${isPaid ? INV.paid : INV.wait}</span>`;
    const sColor  = isPaid ? 'var(--success)' : 'var(--pending-clr)';
    const sLabel  = isPaid ? INV.paid : INV.wait;

    document.getElementById('invTopTitle').textContent = INV.title + ' · ' + inv.ref;

    let rows = '';
    inv.items.forEach(it => {
        const line = parseFloat(it.prix) * parseInt(it.quantite);
        rows += `<tr>
            <td>${it.titre}</td>
            <td style="text-align:center">${it.quantite}</td>
            <td style="text-align:right">${fmtNum(it.prix)} ${INV.da}</td>
            <td>${fmtNum(line)} ${INV.da}</td>
        </tr>`;
    });

    document.getElementById('invBody').innerHTML = `
        <div class="inv-head">
            <div>
                <div class="inv-brand">Aura<em>Lib</em></div>
                <div class="inv-brand-sub">LIBRARY</div>
            </div>
            <div class="inv-title-block">
                <div class="inv-title-word">${INV.title}</div>
                <div class="inv-ref">${INV.ref} · ${inv.ref}</div>
                ${stamp}
            </div>
        </div>
        <div class="inv-divider"></div>
        <div class="inv-meta">
            <div>
                <div class="inv-meta-lbl">${INV.client}</div>
                <div class="inv-meta-val">${inv.firstname} ${inv.lastname}</div>
                <div style="font-size:11px;color:var(--page-muted);margin-top:3px">${inv.email || ''}</div>
            </div>
            <div>
                <div class="inv-meta-lbl">${INV.date}</div>
                <div class="inv-meta-val">${fmtDate(inv.date)}</div>
                <div class="inv-meta-lbl" style="margin-top:12px">${INV.status}</div>
                <div class="inv-meta-val" style="color:${sColor}">${sLabel}</div>
            </div>
        </div>
        <table class="inv-table">
            <thead>
                <tr>
                    <th>${INV.colDoc}</th>
                    <th style="text-align:center">${INV.colQty}</th>
                    <th style="text-align:right">${INV.colPu}</th>
                    <th style="text-align:right">${INV.colTotal}</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
        <div class="inv-total-row">
            <span class="inv-total-lbl">${INV.total}</span>
            <span class="inv-total-val">${fmtNum(inv.total)}<span>${INV.da}</span></span>
        </div>
        <div class="inv-footer-line">${INV.footer}</div>
    `;

    document.getElementById('invOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeInvoice() {
    document.getElementById('invOverlay').classList.remove('open');
    document.body.style.overflow = '';
}
function closeOnOverlay(e) {
    if (e.target === document.getElementById('invOverlay')) closeInvoice();
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeInvoice(); });
</script>
</body>
</html>