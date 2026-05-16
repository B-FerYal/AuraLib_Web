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
        'page_title'   => 'AuraLib | Gestion des Commandes',
        'stat_revenue' => 'Revenus Totaux',
        'stat_profit'  => 'Bénéfice Net',
        'stat_sold'    => 'Livres Vendus',
        'stat_copies'  => 'Exemplaires',
        'table_title'  => 'Liste des Commandes',
        'btn_back'     => '⬅ Retour',
        'th_num'       => 'Référence',
        'th_client'    => 'Lecteur & Détails',
        'th_total'     => 'Total',
        'th_date'      => 'Date & Heure',
        'th_status'    => 'Statut',
        'th_action'    => 'Action',
        'status_paid'  => 'Payée',
        'status_wait'  => 'En attente',
        'confirmed'    => '✓ Confirmé Auto',
        'invoice'      => 'Facture PDF',
        'stock_left'   => 'Reste:',
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
    ],
    'en' => [
        'page_title'   => 'AuraLib | Order Management',
        'stat_revenue' => 'Total Revenue',
        'stat_profit'  => 'Net Profit',
        'stat_sold'    => 'Books Sold',
        'stat_copies'  => 'Copies',
        'table_title'  => 'Order List',
        'btn_back'     => '⬅ Back',
        'th_num'       => 'Reference',
        'th_client'    => 'Reader & Details',
        'th_total'     => 'Total',
        'th_date'      => 'Date & Time',
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
    ],
    'ar' => [
        'page_title'   => 'AuraLib | إدارة الطلبات',
        'stat_revenue' => 'إجمالي الإيرادات',
        'stat_profit'  => 'صافي الربح',
        'stat_sold'    => 'الكتب المباعة',
        'stat_copies'  => 'نسخة',
        'table_title'  => 'قائمة الطلبات',
        'btn_back'     => 'رجوع ←',
        'th_num'       => 'المرجع',
        'th_client'    => 'القارئ والتفاصيل',
        'th_total'     => 'الإجمالي',
        'th_date'      => 'التاريخ والوقت',
        'th_status'    => 'الحالة',
        'th_action'    => 'الإجراء',
        'status_paid'  => 'مدفوع',
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
    ],
];
$p     = $pg[$lang] ?? $pg['fr'];
$isRtl = ($lang === 'ar');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php"); exit;
}

// ── Stats ──────────────────────────────────────────────
$rev_res = $conn->query("
    SELECT SUM(total) as tr FROM commande 
    WHERE statut IN ('payée', 'en attente de paiement')
")->fetch_assoc();
$total_revenue = $rev_res['tr'] ?? 0;

$profit_query = "
    SELECT SUM((ci.prix - IFNULL(d.prix_achat, 0)) * ci.quantite) as net_profit
    FROM commande_item ci
    JOIN documents d ON ci.id_doc = d.id_doc
    JOIN commande c ON ci.id_commande = c.id_commande
    WHERE c.statut IN ('payée', 'en attente de paiement')";
$profit_res = $conn->query($profit_query)->fetch_assoc();
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
           GROUP_CONCAT(CONCAT('<b>', d.titre, '</b> (x', ci.quantite, ') <br><small>" . addslashes($p['stock_left']) . " ', d.exemplaires_disponibles, ' " . addslashes($p['stock_unit']) . "</small>') SEPARATOR '<hr style=\"margin:5px 0; border:0; border-top:1px solid rgba(196,164,107,.15);\">') as order_details
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
    <title><?= $p['page_title'] ?></title>
    <link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
    <script>
    (function(){
        if(localStorage.getItem('auralib_theme')==='dark')
            document.documentElement.classList.add('dark');
    })();
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
    :root {
        --gold:        #C4A46B;
        --gold2:       #D4B47B;
        --gold-deep:   #A8884E;
        --gold-faint:  rgba(196,164,107,.08);
        --gold-border: rgba(196,164,107,.25);
        --amber:       #B8832A;
        --ink:         #2C1F0E;
        --ink2:        #3A2A14;
        --page-bg:     #F5F0E8;
        --page-bg2:    #EDE5D4;
        --page-white:  #FFFDF9;
        --page-text:   #2C1F0E;
        --page-muted:  #9A8C7E;
        --page-border: #DDD5C8;
        --success:     #2E7D52;
        --success-bg:  rgba(46,125,82,.08);
        --pending-bg:  rgba(184,131,42,.10);
        --pending-clr: #B8832A;
        --pending-brd: rgba(184,131,42,.25);
        --font-serif:  'Cormorant Garamond', Georgia, serif;
        --font-ui:     <?= $isRtl ? "'Tajawal', sans-serif" : "'Plus Jakarta Sans', sans-serif" ?>;
        --nav-h:       66px;
        --radius:      14px;
        --shadow-sm:   0 3px 12px rgba(44,31,14,.07);
        --shadow-md:   0 8px 30px rgba(44,31,14,.10);
        --shadow-lg:   0 20px 55px rgba(44,31,14,.13);
        --tr:          .22s cubic-bezier(.4,0,.2,1);
    }
    html.dark {
        --page-bg:    #100C07;
        --page-bg2:   #1A1308;
        --page-white: #1E1610;
        --page-text:  #EDE5D4;
        --page-muted: #9A8C7E;
        --page-border:#3A2E1E;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: var(--font-ui);
        background: var(--page-bg);
        color: var(--page-text);
        padding-top: var(--nav-h);
        min-height: 100vh;
        transition: background .35s, color .35s;
        direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
    }

    .ao-wrap { max-width: 1240px; margin: 0 auto; padding: 40px 28px 80px; }

    .ao-page-header {
        display: flex; align-items: flex-end; justify-content: space-between;
        margin-bottom: 32px; gap: 16px; flex-wrap: wrap;
        flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
    }
    .ao-page-title { font-family: var(--font-serif); font-size: 34px; font-weight: 700; color: var(--page-text); line-height: 1; letter-spacing: -.3px; }
    .ao-page-sub   { font-size: 12px; color: var(--page-muted); margin-top: 5px; letter-spacing: .3px; }

    .btn-back {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 20px; border: 1.5px solid var(--gold-border);
        border-radius: 50px; background: var(--gold-faint);
        color: var(--gold-deep); font-size: 12px; font-weight: 700;
        text-decoration: none; font-family: var(--font-ui);
        transition: all var(--tr); white-space: nowrap; flex-shrink: 0;
    }
    html.dark .btn-back { color: var(--gold); }
    .btn-back:hover { background: var(--gold); color: var(--ink); border-color: var(--gold); box-shadow: 0 4px 16px rgba(196,164,107,.25); }

    .ao-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 30px; }
    .ao-stat {
        background: var(--page-white); border: 1px solid var(--page-border);
        border-radius: var(--radius); padding: 22px 20px;
        border-top: 3px solid var(--gold); position: relative;
        overflow: hidden; box-shadow: var(--shadow-sm);
        text-align: <?= $isRtl ? 'right' : 'left' ?>;
    }
    .ao-stat.profit { border-top-color: var(--success); }
    .ao-stat.blue   { border-top-color: #60a5fa; }
    .ao-stat-lbl { font-size: 10px; font-weight: 700; letter-spacing: <?= $isRtl ? '0' : '1.2px' ?>; text-transform: uppercase; color: var(--page-muted); margin-bottom: 8px; }
    .ao-stat-val { font-family: var(--font-serif); font-size: 30px; font-weight: 700; color: var(--page-text); line-height: 1; letter-spacing: -.5px; }
    .ao-stat-unit { font-family: var(--font-ui); font-size: 13px; color: var(--page-muted); margin-top: 4px; }

    .ao-card { background: var(--page-white); border: 1px solid var(--page-border); border-radius: 18px; box-shadow: var(--shadow-md); overflow: hidden; }
    .ao-card-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 22px 28px; border-bottom: 1px solid var(--page-border);
        background: linear-gradient(135deg, var(--ink) 0%, var(--ink2) 100%);
        flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>; gap: 12px;
    }
    .ao-card-title { font-family: var(--font-serif); font-size: 22px; font-weight: 700; color: var(--gold); letter-spacing: -.2px; }
    .ao-count-badge { font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: rgba(196,164,107,.6); background: rgba(196,164,107,.08); border: 1px solid rgba(196,164,107,.18); padding: 4px 12px; border-radius: 20px; }

    .ao-table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    thead tr { background: var(--page-bg2); }
    html.dark thead tr { background: var(--page-bg); }
    th { padding: 13px 18px; text-align: <?= $isRtl ? 'right' : 'left' ?>; font-size: 10px; font-weight: 700; letter-spacing: <?= $isRtl ? '0' : '1px' ?>; text-transform: uppercase; color: var(--page-muted); border-bottom: 1px solid var(--page-border); white-space: nowrap; }
    td { padding: 18px 18px; border-bottom: 1px solid var(--page-border); color: var(--page-text); vertical-align: middle; text-align: <?= $isRtl ? 'right' : 'left' ?>; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr { transition: background var(--tr); }
    tbody tr:hover td { background: var(--gold-faint); }

    .order-id {
        font-family: var(--font-serif);
        font-size: 15px;
        font-weight: 700;
        color: var(--gold-deep);
        letter-spacing: .5px;
        white-space: nowrap;
    }
    html.dark .order-id { color: var(--gold); }

    .client-name { font-weight: 700; color: var(--page-text); font-size: 13px; margin-bottom: 6px; text-transform: capitalize; }
    .details-box { font-size: 12px; color: var(--page-muted); line-height: 1.7; max-width: 340px; }
    .details-box b { color: var(--page-text); }
    .details-box small { color: var(--amber); font-weight: 600; }
    .prix-tag { font-family: var(--font-serif); font-size: 20px; font-weight: 700; color: var(--success); letter-spacing: -.3px; white-space: nowrap; }
    .date-cell { font-size: 12px; color: var(--page-muted); line-height: 1.6; white-space: nowrap; }
    .date-cell strong { color: var(--page-text); font-size: 13px; }

    .status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 13px; border-radius: 20px;
        font-size: 11px; font-weight: 700; letter-spacing: .3px; white-space: nowrap;
    }
    .status-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
    .status-pill.paid { background: var(--success-bg); color: var(--success); border: 1px solid rgba(46,125,82,.2); }
    .status-pill.paid::before { background: var(--success); }
    .status-pill.pending { background: var(--pending-bg); color: var(--pending-clr); border: 1px solid var(--pending-brd); }
    .status-pill.pending::before { background: var(--pending-clr); }

    .confirmed-text { font-size: 12px; font-weight: 700; color: var(--success); margin-bottom: 6px; display: flex; align-items: center; gap: 4px; flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>; }
    .btn-facture {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 11px; color: var(--gold-deep); text-decoration: none;
        font-weight: 700; border: 1px solid var(--gold-border);
        padding: 4px 12px; border-radius: 20px; background: var(--gold-faint);
        transition: all var(--tr); cursor: pointer; font-family: var(--font-ui);
    }
    html.dark .btn-facture { color: var(--gold); }
    .btn-facture:hover { background: var(--gold); color: var(--ink); border-color: var(--gold); }

    @media (max-width: 900px) { .ao-stats { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 600px) { .ao-stats { grid-template-columns: 1fr; } .ao-wrap { padding: 24px 16px 60px; } .ao-card-head { padding: 16px 18px; } th, td { padding: 12px 12px; } }

    /* ── Invoice Modal ── */
    .inv-overlay { display: none; position: fixed; inset: 0; background: rgba(20,12,4,.72); backdrop-filter: blur(6px); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
    .inv-overlay.open { display: flex; }
    .inv-modal { background: #FFFDF9; border-radius: 16px; box-shadow: 0 30px 80px rgba(20,12,4,.4); width: 100%; max-width: 640px; max-height: 90vh; overflow-y: auto; animation: invSlideIn .25s cubic-bezier(.4,0,.2,1) both; }
    @keyframes invSlideIn { from { opacity:0; transform:translateY(20px) scale(.97); } to { opacity:1; transform:translateY(0) scale(1); } }
    .inv-modal-bar { display: flex; align-items: center; justify-content: space-between; padding: 16px 22px; border-bottom: 1px solid #EDE5D4; background: #2C1F0E; border-radius: 16px 16px 0 0; }
    .inv-modal-bar-title { font-size: 13px; font-weight: 700; color: #C4A46B; letter-spacing: .5px; }
    .inv-modal-actions { display: flex; gap: 10px; align-items: center; }
    .btn-inv-print { background: #C4A46B; color: #2C1F0E; border: none; padding: 7px 18px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: inherit; transition: background .2s; }
    .btn-inv-print:hover { background: #D4B47B; }
    .btn-inv-close { background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15); color: rgba(255,255,255,.7); padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; font-family: inherit; transition: background .2s; }
    .btn-inv-close:hover { background: rgba(255,255,255,.15); color: #fff; }
    .inv-body { padding: 32px 36px; }
    .inv-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; flex-wrap: wrap; gap: 16px; }
    .inv-brand { font-family: 'Cormorant Garamond', serif; font-size: 30px; font-weight: 700; color: #2C1F0E; line-height: 1; }
    .inv-brand em { color: #C4A46B; font-style: normal; }
    .inv-brand-sub { font-size: 9px; letter-spacing: 3px; text-transform: uppercase; color: #9A8C7E; margin-top: 3px; }
    .inv-title-block { text-align: right; }
    .inv-title-word { font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 700; color: #C4A46B; line-height: 1; }
    .inv-ref { font-size: 11px; color: #9A8C7E; margin-top: 4px; font-weight: 600; letter-spacing: .5px; }
    .inv-paid-stamp { display: inline-block; border: 2.5px solid #2E7D52; color: #2E7D52; font-size: 10px; font-weight: 700; letter-spacing: 2px; padding: 3px 10px; border-radius: 4px; margin-top: 6px; transform: rotate(-4deg); }
    .inv-wait-stamp { display: inline-block; border: 2.5px solid #B8832A; color: #B8832A; font-size: 10px; font-weight: 700; letter-spacing: 2px; padding: 3px 10px; border-radius: 4px; margin-top: 6px; transform: rotate(-4deg); }
    .inv-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; background: #FAF6EF; border: 1px solid #EDE5D4; border-radius: 10px; padding: 16px 18px; margin-bottom: 24px; font-size: 12px; }
    .inv-meta-lbl { font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #9A8C7E; margin-bottom: 3px; }
    .inv-meta-val { color: #2C1F0E; font-weight: 600; }
    .inv-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12px; }
    .inv-table th { background: #2C1F0E; color: #C4A46B; padding: 10px 13px; text-align: left; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; font-weight: 700; }
    .inv-table th:last-child { text-align: right; }
    .inv-table td { padding: 11px 13px; border-bottom: 1px solid #EDE5D4; color: #2C1F0E; font-size: 13px; }
    .inv-table td:last-child { text-align: right; font-weight: 700; color: #B8832A; }
    .inv-table tbody tr:last-child td { border-bottom: none; }
    .inv-total-row { display: flex; justify-content: flex-end; align-items: center; gap: 20px; padding: 14px 18px; background: #2C1F0E; border-radius: 10px; margin-bottom: 24px; }
    .inv-total-lbl { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(196,164,107,.6); }
    .inv-total-val { font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 700; color: #C4A46B; line-height: 1; }
    .inv-total-val span { font-family: inherit; font-size: 14px; color: rgba(196,164,107,.6); margin-left: 4px; }
    .inv-footer-line { text-align: center; font-size: 11px; color: #9A8C7E; padding-top: 16px; border-top: 1px solid #EDE5D4; }

    @media print {
        body > *:not(.inv-overlay) { display: none !important; }
        .inv-overlay { display: flex !important; position: static !important; background: none !important; backdrop-filter: none !important; padding: 0 !important; }
        .inv-modal { box-shadow: none !important; max-height: none !important; overflow: visible !important; border-radius: 0 !important; }
        .inv-modal-bar { display: none !important; }
    }
    </style>
</head>
<body>
<?php include "../includes/header.php"; ?>

<div class="ao-wrap">

    <div class="ao-page-header">
        <div>
            <div class="ao-page-title"><?= $p['table_title'] ?></div>
            <div class="ao-page-sub">AuraLib — Administration</div>
        </div>
        <a href="admin_dashboard.php" class="btn-back"><?= $p['btn_back'] ?></a>
    </div>

    <div class="ao-stats">
        <div class="ao-stat">
            <div class="ao-stat-lbl"><?= $p['stat_revenue'] ?></div>
            <div class="ao-stat-val"><?= number_format($total_revenue, 2) ?></div>
            <div class="ao-stat-unit">DA</div>
        </div>
        <div class="ao-stat profit">
            <div class="ao-stat-lbl"><?= $p['stat_profit'] ?></div>
            <div class="ao-stat-val"><?= number_format($total_profit, 2) ?></div>
            <div class="ao-stat-unit">DA</div>
        </div>
        <div class="ao-stat blue">
            <div class="ao-stat-lbl"><?= $p['stat_sold'] ?></div>
            <div class="ao-stat-val"><?= $total_sold ?></div>
            <div class="ao-stat-unit"><?= $p['stat_copies'] ?></div>
        </div>
    </div>

    <div class="ao-card">
        <div class="ao-card-head">
            <div class="ao-card-title"><?= $p['table_title'] ?></div>
            <span class="ao-count-badge">AuraLib</span>
        </div>
        <div class="ao-table-wrap">
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
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="order-id"><?= fmtOrderId($row['id_commande'], $row['date_commande']) ?></td>
                        <td>
                            <div class="client-name"><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></div>
                            <div class="details-box"><?= $row['order_details'] ?></div>
                        </td>
                        <td class="prix-tag">
                            <?= number_format($row['total'], 2) ?>
                            <span style="font-size:13px;font-family:var(--font-ui);color:var(--page-muted);font-weight:400">DA</span>
                        </td>
                        <td class="date-cell">
                            <strong><?= date('d M Y', strtotime($row['date_commande'])) ?></strong><br>
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
                            <div class="confirmed-text"><?= $p['confirmed'] ?></div>
                            <button onclick="showInvoice(<?= $row['id_commande'] ?>)" class="btn-facture">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                <?= $p['invoice'] ?>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include "../includes/footer.php"; ?>

<div class="inv-overlay" id="invOverlay" onclick="closeInvoiceOnOverlay(event)">
    <div class="inv-modal" id="invModal">
        <div class="inv-modal-bar">
            <span class="inv-modal-bar-title" id="invModalTitle"></span>
            <div class="inv-modal-actions">
                <button class="btn-inv-print" onclick="printInvoice()"><?= $p['btn_print'] ?></button>
                <button class="btn-inv-close" onclick="closeInvoice()"><?= $p['btn_close'] ?></button>
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
};

function fmtDate(str) {
    if (!str) return '—';
    const d = new Date(str);
    return d.toLocaleDateString('<?= $lang === 'ar' ? 'ar-DZ' : ($lang === 'en' ? 'en-GB' : 'fr-FR') ?>', { day:'2-digit', month:'long', year:'numeric' });
}

function fmtNum(n) {
    return parseFloat(n).toLocaleString('fr-DZ', { minimumFractionDigits: 2 });
}

function showInvoice(id) {
    const inv = INVOICES[id];
    if (!inv) return;

    const isPaid      = inv.statut === 'payée';
    const stamp       = isPaid
        ? `<div class="inv-paid-stamp">${INV.paid}</div>`
        : `<div class="inv-wait-stamp">${INV.wait}</div>`;
    const statusColor = isPaid ? '#2E7D52' : '#B8832A';
    const statusLabel = isPaid ? INV.paid  : INV.wait;

    document.getElementById('invModalTitle').textContent = INV.title + ' ' + inv.ref;

    let itemsHtml = '';
    inv.items.forEach(it => {
        const line = parseFloat(it.prix) * parseInt(it.quantite);
        itemsHtml += `<tr>
            <td>${it.titre}</td>
            <td style="text-align:center">${it.quantite}</td>
            <td style="text-align:right">${fmtNum(it.prix)} DA</td>
            <td>${fmtNum(line)} DA</td>
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
                <div class="inv-ref">${INV.ref} ${inv.ref}</div>
                ${stamp}
            </div>
        </div>
        <div class="inv-meta">
            <div>
                <div class="inv-meta-lbl">${INV.client}</div>
                <div class="inv-meta-val">${inv.firstname} ${inv.lastname}</div>
                <div style="font-size:11px;color:#9A8C7E;margin-top:2px">${inv.email || ''}</div>
            </div>
            <div>
                <div class="inv-meta-lbl">${INV.date}</div>
                <div class="inv-meta-val">${fmtDate(inv.date)}</div>
                <div class="inv-meta-lbl" style="margin-top:10px">${INV.status}</div>
                <div class="inv-meta-val" style="color:${statusColor}">${statusLabel}</div>
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
            <tbody>${itemsHtml}</tbody>
        </table>
        <div class="inv-total-row">
            <span class="inv-total-lbl">${INV.total}</span>
            <span class="inv-total-val">${fmtNum(inv.total)}<span>DA</span></span>
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

function closeInvoiceOnOverlay(e) {
    if (e.target === document.getElementById('invOverlay')) closeInvoice();
}

function printInvoice() { window.print(); }

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeInvoice(); });
</script>
</body>
</html>