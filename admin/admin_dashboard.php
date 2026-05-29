<?php
include "../includes/header.php";
include '../includes/dark_init.php'; 

$pg = [
    'fr' => [
        'page_title'     => 'Tableau de Bord',
        'page_sub'       => "Bienvenue dans l'espace de gestion AuraLib",
        'stat_docs'      => 'Total Documents',
        'stat_docs_sub'  => 'dans le catalogue',
        'stat_users'     => 'Lecteurs Inscrits',
        'stat_users_sub' => 'comptes actifs',
        'stat_loans'     => 'Emprunts en cours',
        'stat_loans_sub' => 'en circulation',
        'stat_rev'       => 'Recettes (Ventes)',
        'stat_rev_sub'   => 'DA encaissés',
        'promo_h4'       => 'Analyses &amp; Rapports 2026',
        'promo_p'        => 'Visualisez l\'évolution de vos ventes et les préférences de vos lecteurs.',
        'promo_btn'      => 'Voir les Graphiques →',
        'qa_epuise'      => 'Stock Épuisé',
        'qa_docs'        => 'Documents',
        'qa_analyses'    => 'Analyses',
        'qa_ventes'      => 'Ventes',
        'qa_users'       => 'Utilisateurs',
        'qa_prets'       => 'Prêts',
        'qa_inbox'       => 'Messages',
        'tb_sales'       => 'Dernières Ventes',
        'tb_sales_link'  => 'Voir tout →',
        'tb_loans'       => 'Flux d\'Emprunts',
        'tb_loans_link'  => 'Voir tout →',
        'th_user'        => 'Utilisateur',
        'th_date'        => 'Date',
        'th_amount'      => 'Montant',
        'th_status'      => 'Statut',
        'th_book'        => 'Livre',
        'th_reader'      => 'Lecteur',
    ],
    'en' => [
        'page_title'     => 'Dashboard',
        'page_sub'       => 'Welcome to the AuraLib management space',
        'stat_docs'      => 'Total Documents',
        'stat_docs_sub'  => 'in the catalogue',
        'stat_users'     => 'Registered Readers',
        'stat_users_sub' => 'active accounts',
        'stat_loans'     => 'Active Loans',
        'stat_loans_sub' => 'in circulation',
        'stat_rev'       => 'Revenue (Sales)',
        'stat_rev_sub'   => 'DA collected',
        'promo_h4'       => 'Analytics &amp; Reports 2026',
        'promo_p'        => 'Visualise your sales trends and reader preferences.',
        'promo_btn'      => 'View Charts →',
        'qa_epuise'      => 'Out of Stock',
        'qa_docs'        => 'Documents',
        'qa_analyses'    => 'Analytics',
        'qa_ventes'      => 'Sales',
        'qa_users'       => 'Users',
        'qa_prets'       => 'Loans',
        'qa_inbox'       => 'Messages',
        'tb_sales'       => 'Recent Sales',
        'tb_sales_link'  => 'See all →',
        'tb_loans'       => 'Loan Activity',
        'tb_loans_link'  => 'See all →',
        'th_user'        => 'User',
        'th_date'        => 'Date',
        'th_amount'      => 'Amount',
        'th_status'      => 'Status',
        'th_book'        => 'Book',
        'th_reader'      => 'Reader',
    ],
    'ar' => [
        'page_title'     => 'لوحة التحكم',
        'page_sub'       => 'مرحباً بك في فضاء إدارة AuraLib',
        'stat_docs'      => 'إجمالي الوثائق',
        'stat_docs_sub'  => 'في الكتالوج',
        'stat_users'     => 'القراء المسجلون',
        'stat_users_sub' => 'حسابات نشطة',
        'stat_loans'     => 'الاستعارات الجارية',
        'stat_loans_sub' => 'في التداول',
        'stat_rev'       => 'الإيرادات',
        'stat_rev_sub'   => 'دج محصّلة',
        'promo_h4'       => 'التحليلات &amp; التقارير 2026',
        'promo_p'        => 'تابع تطور مبيعاتك وتفضيلات قرائك.',
        'promo_btn'      => 'عرض الرسوم ←',
        'qa_epuise'      => 'نفد المخزون',
        'qa_docs'        => 'الوثائق',
        'qa_analyses'    => 'التحليلات',
        'qa_ventes'      => 'المبيعات',
        'qa_users'       => 'المستخدمون',
        'qa_prets'       => 'الاستعارات',
        'qa_inbox'       => 'الرسائل',
        'tb_sales'       => 'آخر المبيعات',
        'tb_sales_link'  => 'عرض الكل ←',
        'tb_loans'       => 'نشاط الاستعارات',
        'tb_loans_link'  => 'عرض الكل ←',
        'th_user'        => 'المستخدم',
        'th_date'        => 'التاريخ',
        'th_amount'      => 'المبلغ',
        'th_status'      => 'الحالة',
        'th_book'        => 'الكتاب',
        'th_reader'      => 'القارئ',
    ],
];
$p     = $pg[$lang] ?? $pg['fr'];
$isRtl = ($lang === 'ar');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location:../client/library.php"); exit;
}

$total_docs  = $conn->query("SELECT COUNT(*) c FROM documents")->fetch_assoc()['c'] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) c FROM users WHERE role='utilisateur'")->fetch_assoc()['c'] ?? 0;
$total_loans = $conn->query("SELECT COUNT(*) c FROM emprunt WHERE statut='en_cours'")->fetch_assoc()['c'] ?? 0;
$rev_q       = $conn->query("SELECT SUM(total) s FROM commande WHERE statut IN ('payee','payée','Terminé')");
$revenue     = $rev_q ? (float)$rev_q->fetch_assoc()['s'] : 0;
$nb_messages = 0;
$r = $conn->query("SELECT COUNT(*) c FROM contact_messages WHERE lu=0");
if ($r) $nb_messages = (int)$r->fetch_assoc()['c'];
$nb_epuises = (int)($conn->query("SELECT COUNT(*) c FROM documents WHERE exemplaires_disponibles <= 0")->fetch_assoc()['c'] ?? 0);
$recent_orders   = $conn->query("SELECT c.*, u.firstname, u.lastname FROM commande c JOIN users u ON c.id_user = u.id ORDER BY c.id_commande DESC LIMIT 5");
$recent_emprunts = $conn->query("SELECT e.*, u.firstname, u.lastname, d.titre FROM emprunt e JOIN users u ON e.id_user = u.id JOIN documents d ON e.id_doc = d.id_doc ORDER BY e.id_emprunt DESC LIMIT 5");
?>

<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════════════════════════
   AURALIB ADMIN DASHBOARD — Premium Luxury CSS
   ═══════════════════════════════════════════════════════════════ */
:root {
    --gold:          #C4A46B;
    --gold2:         #D4B47B;
    --gold-deep:     #A8884E;
    --gold-faint:    rgba(196,164,107,.08);
    --gold-border:   rgba(196,164,107,.22);
    --gold-shadow:   0 8px 32px rgba(196,164,107,.16);
    --ink:           #1A0E05;
    --ink2:          #2C1F0E;
    --ink3:          #3A2A14;
    --page-bg:       #F2EDE3;
    --page-bg2:      #EDE5D4;
    --page-white:    #FDFAF5;
    --page-text:     #2A1F14;
    --page-muted:    #9A8C7E;
    --page-border:   #D8CFC0;
    --success:       #276749;
    --success-bg:    rgba(39,103,73,.09);
    --danger:        #C0392B;
    --danger-bg:     rgba(192,57,43,.09);
    --warning:       #92400E;
    --warning-bg:    rgba(146,64,14,.09);
    --info:          #1B4F8A;
    --info-bg:       rgba(27,79,138,.09);
    --font-serif:    'EB Garamond', Georgia, serif;
    --font-ui:       'Plus Jakarta Sans', sans-serif;
    --nav-h:         68px;
    --radius:        16px;
    --radius-sm:     10px;
    --shadow-sm:     0 2px 12px rgba(42,31,20,.06);
    --shadow-md:     0 8px 30px rgba(42,31,20,.10);
    --ease:          cubic-bezier(.4,0,.2,1);
    --tr:            .22s var(--ease);
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

@keyframes fadeUp  { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }
@keyframes fadeIn  { from{opacity:0} to{opacity:1} }
@keyframes countUp { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

/* ── WRAPPER ─────────────────────────────────────────────── */
.adm-wrap {
    background: var(--page-bg);
    padding-top: var(--nav-h);
    min-height: 100vh;
    direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
    transition: background .35s;
}
.adm-main {
    max-width: 1380px;
    margin: 0 auto;
    padding: 40px 5% 80px;
}

/* ── PAGE HERO ───────────────────────────────────────────── */
.dash-hero {
    background:
        radial-gradient(ellipse 60% 140% at 5% 60%, rgba(196,164,107,.08) 0%, transparent 55%),
        linear-gradient(155deg, #0D0805 0%, #1C1208 50%, #0D0805 100%);
    border-radius: var(--radius);
    padding: 32px 36px;
    margin-bottom: 28px;
    display: flex; align-items: flex-end;
    justify-content: space-between; gap: 20px; flex-wrap: wrap;
    position: relative; overflow: hidden;
    border: 1px solid rgba(196,164,107,.12);
    box-shadow: 0 8px 40px rgba(0,0,0,.25);
    animation: fadeUp .5s var(--ease) both;
    direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
}
.dash-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23C4A46B' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}
.dash-hero::after {
    content: '';
    position: absolute; bottom: 0; left: 0; right: 0; height: 1px;
    background: linear-gradient(90deg, transparent, rgba(196,164,107,.3), transparent);
}
.hero-eyebrow {
    font-size: 9px; color: rgba(196,164,107,.45);
    letter-spacing: 3.5px; text-transform: uppercase;
    margin-bottom: 6px; display: flex; align-items: center; gap: 7px;
}
.hero-eyebrow i { font-size: 8px; }
.dash-title {
    font-family: var(--font-serif);
    font-size: clamp(28px, 4vw, 46px);
    font-weight: 700; color: #FDFAF5; line-height: 1;
    font-style: normal;
    letter-spacing: -.3px; position: relative;
}
.dash-title span {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold2) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
}
.dash-sub {
    font-size: 12px; color: rgba(255,255,255,.35);
    margin-top: 6px; font-family: var(--font-ui);
}
.dash-date-pill {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(196,164,107,.1); border: 1.5px solid rgba(196,164,107,.2);
    color: rgba(196,164,107,.7); font-family: var(--font-ui);
    font-size: 11px; font-weight: 600; padding: 8px 16px;
    border-radius: 50px; letter-spacing: .2px; white-space: nowrap;
}
.dash-date-pill i { font-size: 10px; }

/* ── STOCK ALERT ─────────────────────────────────────────── */
.stock-alert {
    display: flex; align-items: center; gap: 16px;
    background: linear-gradient(135deg, rgba(192,57,43,.10) 0%, rgba(192,57,43,.06) 100%);
    border: 1.5px solid rgba(192,57,43,.22);
    border-radius: var(--radius-sm);
    padding: 14px 20px; margin-bottom: 28px;
    text-decoration: none;
    transition: all var(--tr);
    animation: fadeUp .45s .05s var(--ease) both;
    position: relative; overflow: hidden;
}
.stock-alert::before {
    content: '';
    position: absolute; top: 0; left: 0; width: 3px; height: 100%;
    background: var(--danger);
    border-radius: 3px 0 0 3px;
}
.stock-alert:hover {
    background: linear-gradient(135deg, rgba(192,57,43,.15) 0%, rgba(192,57,43,.09) 100%);
    border-color: rgba(192,57,43,.38);
    transform: translateX(4px);
}
.stock-alert-icon {
    width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
    background: rgba(192,57,43,.12); border: 1.5px solid rgba(192,57,43,.25);
    display: flex; align-items: center; justify-content: center;
}
.stock-alert-icon svg { width: 18px; height: 18px; stroke: var(--danger); }
.stock-alert-body { flex: 1; min-width: 0; }
.stock-alert-title { font-size: 13px; font-weight: 700; color: var(--danger); }
.stock-alert-sub   { font-size: 11px; color: rgba(192,57,43,.65); margin-top: 2px; }
.stock-alert-badge {
    background: var(--danger); color: #fff;
    font-size: 12px; font-weight: 800;
    padding: 3px 12px; border-radius: 20px;
    flex-shrink: 0;
}
.stock-alert-arrow { color: rgba(192,57,43,.5); font-size: 14px; flex-shrink: 0; }

/* ── STATS GRID ──────────────────────────────────────────── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px; margin-bottom: 28px;
}
.stat-card {
    background: var(--page-white);
    border: 1px solid var(--page-border);
    border-radius: var(--radius);
    padding: 24px 22px;
    position: relative; overflow: hidden;
    border-top: 3px solid var(--gold);
    box-shadow: var(--shadow-sm);
    transition: transform var(--tr), box-shadow var(--tr);
    animation: fadeUp .5s var(--ease) both;
    direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
}
.stat-card:nth-child(1) { animation-delay: .07s; }
.stat-card:nth-child(2) { animation-delay: .12s; border-top-color: var(--success); }
.stat-card:nth-child(3) { animation-delay: .17s; border-top-color: var(--info); }
.stat-card:nth-child(4) { animation-delay: .22s; border-top-color: var(--warning); }
.stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
.stat-card::after {
    content: '';
    position: absolute; right: -12px; bottom: -12px;
    width: 70px; height: 70px; border-radius: 50%;
    background: var(--gold-faint);
    pointer-events: none;
}
.stat-icon {
    width: 36px; height: 36px; border-radius: 10px; margin-bottom: 14px;
    background: var(--gold-faint); border: 1px solid var(--gold-border);
    display: flex; align-items: center; justify-content: center;
}
.stat-icon svg { width: 18px; height: 18px; stroke: var(--gold); }
.stat-card:nth-child(2) .stat-icon { background: var(--success-bg); border-color: rgba(39,103,73,.2); }
.stat-card:nth-child(2) .stat-icon svg { stroke: var(--success); }
.stat-card:nth-child(3) .stat-icon { background: var(--info-bg); border-color: rgba(27,79,138,.2); }
.stat-card:nth-child(3) .stat-icon svg { stroke: var(--info); }
.stat-card:nth-child(4) .stat-icon { background: var(--warning-bg); border-color: rgba(146,64,14,.2); }
.stat-card:nth-child(4) .stat-icon svg { stroke: var(--warning); }
.stat-lbl {
    font-size: 9px; font-weight: 700; letter-spacing: 1.5px;
    text-transform: uppercase; color: var(--page-muted); margin-bottom: 6px;
}
.stat-val {
    font-family: var(--font-ui);
    font-size: 38px; font-weight: 700;
    color: var(--page-text); line-height: 1;
    animation: countUp .6s var(--ease) both;
    letter-spacing: -1px;
}
.stat-sub {
    font-size: 10px; color: var(--page-muted); margin-top: 5px;
    display: flex; align-items: center; gap: 5px;
}
.stat-sub i { font-size: 9px; }

/* ── PROMO BANNER ────────────────────────────────────────── */
.promo-banner {
    background: linear-gradient(135deg, var(--ink) 0%, #241806 50%, var(--ink) 100%);
    border: 1px solid rgba(196,164,107,.18);
    border-radius: var(--radius);
    padding: 24px 30px;
    margin-bottom: 28px;
    display: flex; align-items: center;
    justify-content: space-between; gap: 20px; flex-wrap: wrap;
    box-shadow: 0 8px 32px rgba(0,0,0,.2);
    animation: fadeUp .5s .25s var(--ease) both;
    position: relative; overflow: hidden;
    direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
}
.promo-banner::before {
    content: '';
    position: absolute; top: -30px; right: -30px;
    width: 180px; height: 180px; border-radius: 50%;
    background: radial-gradient(circle, rgba(196,164,107,.08) 0%, transparent 70%);
    pointer-events: none;
}
.promo-eyebrow {
    font-size: 9px; color: rgba(196,164,107,.45);
    letter-spacing: 3px; text-transform: uppercase;
    margin-bottom: 4px;
}
.promo-title {
    font-family: var(--font-serif);
    font-size: 22px; font-weight: 700; color: var(--gold);
    line-height: 1.2; margin-bottom: 5px;
}
.promo-desc {
    font-size: 12px; color: rgba(237,229,212,.45); line-height: 1.6;
}
.promo-btn {
    display: inline-flex; align-items: center; gap: 9px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-deep) 100%);
    color: var(--ink2);
    padding: 12px 26px; border-radius: 50px;
    font-family: var(--font-ui); font-size: 12px; font-weight: 800;
    text-decoration: none; white-space: nowrap; letter-spacing: .3px;
    transition: all var(--tr);
    box-shadow: 0 4px 18px rgba(196,164,107,.35);
}
.promo-btn:hover {
    background: linear-gradient(135deg, var(--gold2) 0%, var(--gold) 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(196,164,107,.45);
}
.promo-btn i { font-size: 11px; }

/* ── QUICK ACTIONS ───────────────────────────────────────── */
.qa-section-lbl {
    font-size: 9px; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; color: var(--page-muted);
    margin-bottom: 14px; padding-bottom: 10px;
    border-bottom: 1px solid var(--page-border);
    display: flex; align-items: center; gap: 8px;
}
.qa-section-lbl i { color: var(--gold); font-size: 11px; }
.qa-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 14px; margin-bottom: 32px;
    animation: fadeUp .5s .3s var(--ease) both;
}
.qa-card {
    background: var(--page-white);
    border: 1px solid var(--page-border);
    border-radius: var(--radius);
    padding: 22px 12px 18px;
    text-align: center; text-decoration: none;
    color: var(--page-text);
    transition: all var(--tr);
    position: relative; display: flex;
    flex-direction: column; align-items: center; gap: 10px;
    box-shadow: var(--shadow-sm);
}
.qa-card:hover {
    transform: translateY(-4px);
    border-color: var(--gold-border);
    box-shadow: var(--gold-shadow);
    background: var(--page-white);
}
.qa-icon-wrap {
    width: 48px; height: 48px; border-radius: 14px;
    background: var(--gold-faint);
    border: 1px solid var(--gold-border);
    display: flex; align-items: center; justify-content: center;
    transition: all var(--tr);
}
.qa-card:hover .qa-icon-wrap {
    background: rgba(196,164,107,.16);
    border-color: rgba(196,164,107,.4);
}
.qa-icon-wrap svg {
    width: 22px; height: 22px;
    stroke: var(--gold-deep);
}
.qa-label {
    font-size: 11px; font-weight: 700;
    color: var(--page-text); letter-spacing: .2px;
    line-height: 1.3;
}
.qa-badge {
    position: absolute; top: -6px; right: -6px;
    background: #ef4444; color: #fff;
    font-size: 9px; font-weight: 800;
    padding: 2px 7px; border-radius: 10px;
    border: 2px solid var(--page-white);
    box-shadow: 0 2px 8px rgba(239,68,68,.4);
}
/* Danger card */
.qa-card.qa-danger {
    border-color: rgba(192,57,43,.22);
    background: linear-gradient(135deg, rgba(192,57,43,.06) 0%, var(--page-white) 100%);
}
.qa-card.qa-danger .qa-icon-wrap {
    background: rgba(192,57,43,.09);
    border-color: rgba(192,57,43,.2);
}
.qa-card.qa-danger .qa-icon-wrap svg { stroke: var(--danger); }
.qa-card.qa-danger .qa-label { color: var(--danger); }
.qa-card.qa-danger:hover {
    border-color: rgba(192,57,43,.42);
    box-shadow: 0 8px 28px rgba(192,57,43,.14);
}
/* Featured card */
.qa-card.qa-featured {
    border-color: rgba(39,103,73,.22);
    background: linear-gradient(135deg, rgba(39,103,73,.06) 0%, var(--page-white) 100%);
}
.qa-card.qa-featured .qa-icon-wrap {
    background: rgba(39,103,73,.09);
    border-color: rgba(39,103,73,.2);
}
.qa-card.qa-featured .qa-icon-wrap svg { stroke: var(--success); }
.qa-card.qa-featured .qa-label { color: var(--success); }
.qa-card.qa-featured:hover {
    border-color: rgba(39,103,73,.4);
    box-shadow: 0 8px 28px rgba(39,103,73,.12);
}

/* ── TABLES GRID ─────────────────────────────────────────── */
.tables-grid {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 20px;
    animation: fadeUp .5s .35s var(--ease) both;
}
.table-box {
    background: var(--page-white);
    border: 1px solid var(--page-border);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.tb-head {
    background: linear-gradient(135deg, var(--ink) 0%, var(--ink3) 100%);
    padding: 14px 20px;
    display: flex; align-items: center;
    justify-content: space-between; gap: 12px;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.tb-title {
    font-family: var(--font-serif);
    font-size: 17px; font-weight: 700; color: #FDFAF5;
    display: flex; align-items: center; gap: 9px;
}
.tb-title i { color: var(--gold); font-size: 13px; }
.tb-link {
    font-size: 11px; color: var(--gold);
    text-decoration: none; font-weight: 700;
    display: flex; align-items: center; gap: 4px;
    opacity: .8; transition: opacity var(--tr);
}
.tb-link:hover { opacity: 1; }
.data-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.data-table th {
    padding: 10px 18px;
    text-align: <?= $isRtl ? 'right' : 'left' ?>;
    font-size: 9px; font-weight: 700; color: var(--page-muted);
    text-transform: uppercase; letter-spacing: 1px;
    background: var(--page-bg2); border-bottom: 1px solid var(--page-border);
}
.data-table td {
    padding: 12px 18px;
    border-bottom: 1px solid var(--page-border);
    color: var(--page-text);
    text-align: <?= $isRtl ? 'right' : 'left' ?>;
    transition: background var(--tr);
}
.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr:hover td { background: var(--gold-faint); }
.data-table .td-muted { color: var(--page-muted); font-size: 11px; }
.data-table .td-bold  { font-weight: 700; }
/* Status badges */
.status-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 20px;
    font-size: 10px; font-weight: 700; border: 1px solid;
}
.sb-green  { background: var(--success-bg); color: var(--success); border-color: rgba(39,103,73,.2); }
.sb-amber  { background: var(--warning-bg); color: var(--warning); border-color: rgba(146,64,14,.2); }
.sb-blue   { background: var(--info-bg);    color: var(--info);    border-color: rgba(27,79,138,.2); }
.sb-gray   { background: var(--page-bg2);   color: var(--page-muted); border-color: var(--page-border); }
.sb-red    { background: var(--danger-bg);  color: var(--danger);  border-color: rgba(192,57,43,.2); }
.status-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

/* ── RESPONSIVE ──────────────────────────────────────────── */
@media (max-width: 1100px) {
    .qa-grid    { grid-template-columns: repeat(3, 1fr); }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 800px) {
    .tables-grid { grid-template-columns: 1fr; }
    .qa-grid     { grid-template-columns: repeat(2, 1fr); }
    .adm-main    { padding: 24px 4% 60px; }
    .dash-hero   { padding: 24px 20px; }
}
@media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr 1fr; }
}
</style>

<div class="adm-wrap">
<div class="adm-main">

    <!-- ── HERO ───────────────────────────────────────────── -->
    <div class="dash-hero">
        <div>
            <div class="hero-eyebrow">
                <i class="fa-solid fa-shield-halved"></i>
                Administration · AuraLib
            </div>
            <div class="dash-title">
                <?php
                $parts = explode(' ', $p['page_title'], 2);
                echo htmlspecialchars($parts[0]);
                if (isset($parts[1])) echo ' <span>' . htmlspecialchars($parts[1]) . '</span>';
                ?>
            </div>
            <div class="dash-sub"><?= $p['page_sub'] ?></div>
        </div>
        <div class="dash-date-pill">
            <i class="fa-regular fa-calendar"></i>
            <?php
            $days   = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
            $months = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'];
            echo $days[date('w')] . ' ' . date('d') . ' ' . $months[date('n')-1] . ' ' . date('Y');
            ?>
        </div>
    </div>

    <!-- ── STOCK ALERT ────────────────────────────────────── -->
    <?php if ($nb_epuises > 0): ?>
    <a href="stock_epuise.php" class="stock-alert">
        <div class="stock-alert-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <div class="stock-alert-body">
            <div class="stock-alert-title"><?= $p['qa_epuise'] ?></div>
            <div class="stock-alert-sub">
                <?= $nb_epuises ?> document<?= $nb_epuises > 1 ? 's' : '' ?> nécessitent un réapprovisionnement
            </div>
        </div>
        <span class="stock-alert-badge"><?= $nb_epuises ?></span>
        <i class="fa-solid fa-arrow-right stock-alert-arrow"></i>
    </a>
    <?php endif; ?>

    <!-- ── STATS ──────────────────────────────────────────── -->
    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>
            </div>
            <div class="stat-lbl"><?= $p['stat_docs'] ?></div>
            <div class="stat-val"><?= number_format($total_docs) ?></div>
            <div class="stat-sub">
                <i class="fa-solid fa-layer-group"></i>
                <?= $p['stat_docs_sub'] ?>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="stat-lbl"><?= $p['stat_users'] ?></div>
            <div class="stat-val"><?= number_format($total_users) ?></div>
            <div class="stat-sub">
                <i class="fa-solid fa-circle-check" style="color:var(--success)"></i>
                <?= $p['stat_users_sub'] ?>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="stat-lbl"><?= $p['stat_loans'] ?></div>
            <div class="stat-val"><?= number_format($total_loans) ?></div>
            <div class="stat-sub">
                <i class="fa-solid fa-rotate" style="color:var(--info)"></i>
                <?= $p['stat_loans_sub'] ?>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="stat-lbl"><?= $p['stat_rev'] ?></div>
            <div class="stat-val"><?= number_format($revenue, 0, ',', ' ') ?></div>
            <div class="stat-sub">
                <i class="fa-solid fa-coins" style="color:var(--warning)"></i>
                <?= $p['stat_rev_sub'] ?>
            </div>
        </div>

    </div>

    <!-- ── PROMO BANNER ───────────────────────────────────── -->
    <div class="promo-banner">
        <div>
            <div class="promo-eyebrow">Rapports · Statistiques</div>
            <div class="promo-title"><?= $p['promo_h4'] ?></div>
            <div class="promo-desc"><?= $p['promo_p'] ?></div>
        </div>
        <a href="stats.php" class="promo-btn">
            <i class="fa-solid fa-chart-mixed"></i>
            <?= $p['promo_btn'] ?>
        </a>
    </div>

    <!-- ── QUICK ACTIONS ──────────────────────────────────── -->
    <div class="qa-section-lbl">
        <i class="fa-solid fa-bolt"></i>
        Actions rapides
    </div>
    <div class="qa-grid">

        <?php if ($nb_epuises > 0): ?>
        <a href="stock_epuise.php" class="qa-card qa-danger">
            <?php if ($nb_epuises > 0): ?>
            <span class="qa-badge"><?= $nb_epuises ?></span>
            <?php endif; ?>
            <div class="qa-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <span class="qa-label"><?= $p['qa_epuise'] ?></span>
        </a>
        <?php endif; ?>

        <a href="gerer_documents.php" class="qa-card">
            <div class="qa-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    <line x1="12" y1="6" x2="16" y2="6"/><line x1="12" y1="10" x2="16" y2="10"/><line x1="12" y1="14" x2="14" y2="14"/>
                </svg>
            </div>
            <span class="qa-label"><?= $p['qa_docs'] ?></span>
        </a>

        <a href="stats.php" class="qa-card qa-featured">
            <div class="qa-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/>
                </svg>
            </div>
            <span class="qa-label"><?= $p['qa_analyses'] ?></span>
        </a>

        <a href="all_orders.php" class="qa-card">
            <div class="qa-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
            </div>
            <span class="qa-label"><?= $p['qa_ventes'] ?></span>
        </a>

        <a href="users.php" class="qa-card">
            <div class="qa-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <span class="qa-label"><?= $p['qa_users'] ?></span>
        </a>

        <a href="gerer_emprunts.php" class="qa-card">
            <div class="qa-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <span class="qa-label"><?= $p['qa_prets'] ?></span>
        </a>

        <a href="messages.php" class="qa-card">
            <?php if ($nb_messages > 0): ?>
            <span class="qa-badge"><?= $nb_messages ?></span>
            <?php endif; ?>
            <div class="qa-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <span class="qa-label"><?= $p['qa_inbox'] ?></span>
        </a>

    </div>

    <!-- ── TABLES ─────────────────────────────────────────── -->
    <div class="tables-grid">

        <div class="table-box">
            <div class="tb-head">
                <span class="tb-title">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <?= $p['tb_sales'] ?>
                </span>
                <a href="all_orders.php" class="tb-link">
                    <?= $p['tb_sales_link'] ?>
                    <i class="fa-solid fa-arrow-right" style="font-size:9px"></i>
                </a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= $p['th_user'] ?></th>
                        <th><?= $p['th_date'] ?></th>
                        <th><?= $p['th_amount'] ?></th>
                        <th><?= $p['th_status'] ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $recent_orders->fetch_assoc()):
                        $s = strtolower($row['statut'] ?? '');
                        $bc = in_array($s, ['payee','payée','terminé','termine']) ? 'sb-green'
                           : (in_array($s, ['annulee','annulée','refusée']) ? 'sb-red' : 'sb-amber');
                    ?>
                    <tr>
                        <td class="td-bold"><?= htmlspecialchars($row['firstname'].' '.$row['lastname']) ?></td>
                        <td class="td-muted"><?= date('d/m/Y', strtotime($row['date_commande'])) ?></td>
                        <td class="td-bold"><?= number_format($row['total'], 0) ?> <span style="font-size:10px;font-weight:400;color:var(--page-muted)">DA</span></td>
                        <td>
                            <span class="status-badge <?= $bc ?>">
                                <span class="status-dot"></span>
                                <?= htmlspecialchars($row['statut']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="table-box">
            <div class="tb-head">
                <span class="tb-title">
                    <i class="fa-solid fa-book-open-reader"></i>
                    <?= $p['tb_loans'] ?>
                </span>
                <a href="gerer_emprunts.php" class="tb-link">
                    <?= $p['tb_loans_link'] ?>
                    <i class="fa-solid fa-arrow-right" style="font-size:9px"></i>
                </a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= $p['th_book'] ?></th>
                        <th><?= $p['th_reader'] ?></th>
                        <th><?= $p['th_status'] ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($emp = $recent_emprunts->fetch_assoc()):
                        $s  = strtolower($emp['statut'] ?? '');
                        $bc = $s==='rendu' ? 'sb-green' : ($s==='retard' ? 'sb-red' : ($s==='en_cours' ? 'sb-blue' : 'sb-gray'));
                    ?>
                    <tr>
                        <td class="td-bold" style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                            <?= htmlspecialchars($emp['titre']) ?>
                        </td>
                        <td class="td-muted"><?= htmlspecialchars($emp['firstname']) ?></td>
                        <td>
                            <span class="status-badge <?= $bc ?>">
                                <span class="status-dot"></span>
                                <?= htmlspecialchars($emp['statut']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>
</div>

<?php include "../includes/footer.php"; ?>