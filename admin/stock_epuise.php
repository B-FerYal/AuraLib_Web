<?php
include "../includes/header.php";
include '../includes/dark_init.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location:../client/library.php"); exit;
}

// ── Multilingue ───────────────────────────────────────────
$pg = [
    'fr' => [
        'title'       => 'Stocks Épuisés',
        'sub'         => 'Documents en rupture de stock nécessitant un réapprovisionnement',
        'breadcrumb'  => 'Tableau de bord',
        'back'        => 'Dashboard',
        'stat_total'  => 'Total épuisés',
        'stat_vente'  => 'Vente',
        'stat_pret'   => 'Prêt',
        'stat_both'   => 'Les deux',
        'search_ph'   => 'Rechercher par titre, auteur, catégorie…',
        'all_types'   => 'Tous types',
        'all_cats'    => 'Toutes catégories',
        'results'     => 'résultat',
        'results_pl'  => 'résultats',
        'epuise'      => 'ÉPUISÉ',
        'vente'       => 'Vente',
        'pret'        => 'Prêt',
        'both'        => 'Vente & Prêt',
        'edition'     => 'Édition',
        'editeur'     => 'Éditeur',
        'pages'       => 'Pages',
        'stock_zero'  => '0 exemplaire disponible',
        'stock_total' => 'Total',
        'reappro'     => 'Réapprovisionner',
        'no_results'  => 'Aucun document ne correspond à votre recherche.',
        'empty_title' => 'Aucun stock épuisé !',
        'empty_sub'   => 'Tous vos documents sont disponibles en stock.',
        'flash_ok'    => '',
        'ex'          => 'ex.',
    ],
    'en' => [
        'title'       => 'Out of Stock',
        'sub'         => 'Documents requiring restocking',
        'breadcrumb'  => 'Dashboard',
        'back'        => 'Dashboard',
        'stat_total'  => 'Total out of stock',
        'stat_vente'  => 'For sale',
        'stat_pret'   => 'For loan',
        'stat_both'   => 'Both',
        'search_ph'   => 'Search by title, author, category…',
        'all_types'   => 'All types',
        'all_cats'    => 'All categories',
        'results'     => 'result',
        'results_pl'  => 'results',
        'epuise'      => 'OUT OF STOCK',
        'vente'       => 'Sale',
        'pret'        => 'Loan',
        'both'        => 'Sale & Loan',
        'edition'     => 'Edition',
        'editeur'     => 'Publisher',
        'pages'       => 'Pages',
        'stock_zero'  => '0 copy available',
        'stock_total' => 'Total',
        'reappro'     => 'Restock',
        'no_results'  => 'No documents match your search.',
        'empty_title' => 'No out-of-stock items!',
        'empty_sub'   => 'All your documents are in stock.',
        'ex'          => 'copies',
    ],
    'ar' => [
        'title'       => 'المخزون النافد',
        'sub'         => 'وثائق تحتاج إلى إعادة تخزين',
        'breadcrumb'  => 'لوحة التحكم',
        'back'        => 'لوحة التحكم',
        'stat_total'  => 'إجمالي النافد',
        'stat_vente'  => 'للبيع',
        'stat_pret'   => 'للاستعارة',
        'stat_both'   => 'كلاهما',
        'search_ph'   => 'ابحث بالعنوان أو المؤلف…',
        'all_types'   => 'كل الأنواع',
        'all_cats'    => 'كل الفئات',
        'results'     => 'نتيجة',
        'results_pl'  => 'نتائج',
        'epuise'      => 'نافد',
        'vente'       => 'بيع',
        'pret'        => 'استعارة',
        'both'        => 'بيع واستعارة',
        'edition'     => 'الطبعة',
        'editeur'     => 'الناشر',
        'pages'       => 'الصفحات',
        'stock_zero'  => '0 نسخة متاحة',
        'stock_total' => 'المجموع',
        'reappro'     => 'إعادة التخزين',
        'no_results'  => 'لا توجد وثائق مطابقة.',
        'empty_title' => 'لا يوجد مخزون نافد!',
        'empty_sub'   => 'جميع وثائقك متوفرة في المخزون.',
        'ex'          => 'نسخ',
    ],
];
$l     = $pg[$lang] ?? $pg['fr'];
$isRtl = ($lang === 'ar');

// ── DB queries ────────────────────────────────────────────
$result = $conn->query("
    SELECT d.*, t.libelle_type AS nom_type
    FROM documents d
    LEFT JOIN types_documents t ON d.id_type = t.id_type
    WHERE d.exemplaires_disponibles <= 0
    ORDER BY d.titre ASC
");
$total_epuise  = $result ? $result->num_rows : 0;
$achat_count   = (int)($conn->query("SELECT COUNT(*) c FROM documents WHERE exemplaires_disponibles<=0 AND disponible_pour='achat'")->fetch_assoc()['c'] ?? 0);
$emprunt_count = (int)($conn->query("SELECT COUNT(*) c FROM documents WHERE exemplaires_disponibles<=0 AND disponible_pour='emprunt'")->fetch_assoc()['c'] ?? 0);
$both_count    = (int)($conn->query("SELECT COUNT(*) c FROM documents WHERE exemplaires_disponibles<=0 AND disponible_pour='both'")->fetch_assoc()['c'] ?? 0);

$flash_type = $_SESSION['flash_type'] ?? '';
$flash_msg  = $_SESSION['flash_msg']  ?? '';
unset($_SESSION['flash_type'], $_SESSION['flash_msg']);
?>

<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════════════════════
   AURALIB · stock_epuise — Premium Luxury CSS
   ═══════════════════════════════════════════════════════════ */
:root {
    --gold:         #C4A46B;
    --gold2:        #D4B47B;
    --gold-deep:    #A8884E;
    --gold-faint:   rgba(196,164,107,.08);
    --gold-border:  rgba(196,164,107,.22);
    --ink:          #1A0E05;
    --ink2:         #2C1F0E;
    --ink3:         #3A2A14;
    --page-bg:      #F2EDE3;
    --page-bg2:     #EDE5D4;
    --page-white:   #FDFAF5;
    --page-text:    #2A1F14;
    --page-muted:   #9A8C7E;
    --page-border:  #D8CFC0;
    --danger:       #C0392B;
    --danger-bg:    rgba(192,57,43,.08);
    --danger-border:rgba(192,57,43,.22);
    --success:      #276749;
    --success-bg:   rgba(39,103,73,.08);
    --warning:      #92400E;
    --info:         #1B4F8A;
    --font-serif:   'EB Garamond', Georgia, serif;
    --font-ui:      'Plus Jakarta Sans', sans-serif;
    --nav-h:        68px;
    --radius:       16px;
    --radius-sm:    10px;
    --shadow-sm:    0 2px 12px rgba(42,31,20,.06);
    --shadow-md:    0 8px 30px rgba(42,31,20,.10);
    --shadow-gold:  0 6px 24px rgba(196,164,107,.16);
    --ease:         cubic-bezier(.4,0,.2,1);
    --tr:           .22s var(--ease);
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
@keyframes fadeUp  { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
@keyframes slideIn { from{opacity:0;transform:translateX(-10px)} to{opacity:1;transform:translateX(0)} }

/* ── WRAPPER ─────────────────────────────────────────────── */
.se-wrap {
    background: var(--page-bg);
    padding-top: var(--nav-h);
    min-height: 100vh;
    direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
    transition: background .35s;
}
.se-main { max-width: 1380px; margin: 0 auto; padding: 40px 5% 80px; }

/* ── FLASH ───────────────────────────────────────────────── */
.se-flash {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 18px; border-radius: var(--radius-sm);
    font-size: 13px; font-weight: 600; margin-bottom: 24px;
    animation: slideIn .3s var(--ease) both;
}
.se-flash.success {
    background: var(--success-bg); border: 1px solid rgba(39,103,73,.22); color: var(--success);
}
.se-flash.error {
    background: var(--danger-bg); border: 1px solid var(--danger-border); color: var(--danger);
}

/* ── PAGE HERO ───────────────────────────────────────────── */
.se-hero {
    background:
        radial-gradient(ellipse 60% 130% at 8% 55%, rgba(192,57,43,.07) 0%, transparent 55%),
        linear-gradient(155deg, #0D0805 0%, #1C1208 50%, #0D0805 100%);
    border-radius: var(--radius);
    padding: 30px 36px;
    margin-bottom: 28px;
    display: flex; align-items: flex-end;
    justify-content: space-between; gap: 20px; flex-wrap: wrap;
    position: relative; overflow: hidden;
    border: 1px solid rgba(192,57,43,.15);
    box-shadow: 0 8px 40px rgba(0,0,0,.25);
    animation: fadeUp .5s var(--ease) both;
}
.se-hero::after {
    content: '';
    position: absolute; bottom: 0; left: 0; right: 0; height: 1px;
    background: linear-gradient(90deg, transparent, rgba(192,57,43,.3), transparent);
}
.se-breadcrumb {
    font-size: 10px; color: rgba(196,164,107,.4);
    letter-spacing: 1px; margin-bottom: 6px;
    display: flex; align-items: center; gap: 7px;
}
.se-breadcrumb a { color: rgba(196,164,107,.55); text-decoration: none; transition: color var(--tr); }
.se-breadcrumb a:hover { color: var(--gold); }
.se-title {
    font-family: var(--font-serif);
    font-size: clamp(24px, 4vw, 40px);
    font-weight: 700; color: #FDFAF5; line-height: 1;
    display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
}
.se-title-badge {
    background: var(--danger); color: #fff;
    font-family: var(--font-ui); font-size: 11px; font-weight: 800;
    padding: 3px 12px; border-radius: 20px; letter-spacing: .3px;
    box-shadow: 0 3px 10px rgba(192,57,43,.4);
}
.se-sub {
    font-size: 12px; color: rgba(255,255,255,.32);
    margin-top: 6px; font-family: var(--font-ui);
}
.se-back-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 22px; border-radius: 50px;
    background: var(--gold-faint); border: 1.5px solid var(--gold-border);
    color: rgba(196,164,107,.75); font-family: var(--font-ui);
    font-size: 12px; font-weight: 700; text-decoration: none;
    transition: all var(--tr);
}
.se-back-btn:hover { background: rgba(196,164,107,.16); color: var(--gold); transform: translateX(-2px); }

/* ── STATS BAR ───────────────────────────────────────────── */
.se-stats-bar {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 16px; margin-bottom: 28px;
    animation: fadeUp .5s .08s var(--ease) both;
}
.se-stat {
    background: var(--page-white); border: 1px solid var(--page-border);
    border-radius: var(--radius); padding: 20px 22px;
    display: flex; align-items: center; gap: 16px;
    box-shadow: var(--shadow-sm); transition: all var(--tr);
}
.se-stat:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.se-stat:first-child { border-top: 3px solid var(--danger); }
.se-stat:nth-child(2){ border-top: 3px solid var(--warning); }
.se-stat:nth-child(3){ border-top: 3px solid var(--info); }
.se-stat:last-child  { border-top: 3px solid var(--success); }
.se-stat-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.se-stat-icon.red   { background: var(--danger-bg); border: 1px solid var(--danger-border); }
.se-stat-icon.amber { background: rgba(146,64,14,.08); border: 1px solid rgba(146,64,14,.2); }
.se-stat-icon.blue  { background: rgba(27,79,138,.08); border: 1px solid rgba(27,79,138,.2); }
.se-stat-icon.green { background: var(--success-bg); border: 1px solid rgba(39,103,73,.2); }
.se-stat-icon svg   { width: 20px; height: 20px; }
.se-stat-val {
    font-family: var(--font-ui); font-size: 30px;
    font-weight: 700; color: var(--page-text); line-height: 1;
}
.se-stat-lbl {
    font-size: 9px; font-weight: 700; letter-spacing: 1.2px;
    text-transform: uppercase; color: var(--page-muted); margin-top: 3px;
}

/* ── TOOLBAR ─────────────────────────────────────────────── */
.se-toolbar {
    background: var(--page-white); border: 1px solid var(--page-border);
    border-radius: var(--radius-sm); padding: 14px 18px;
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 24px; flex-wrap: wrap;
    box-shadow: var(--shadow-sm);
    animation: fadeUp .5s .12s var(--ease) both;
}
.se-search {
    flex: 1; min-width: 220px;
    display: flex; align-items: center; gap: 9px;
    background: var(--page-bg); border: 1.5px solid var(--page-border);
    border-radius: var(--radius-sm); padding: 9px 14px;
    transition: border-color var(--tr), box-shadow var(--tr);
}
.se-search:focus-within {
    border-color: var(--gold-border);
    box-shadow: 0 0 0 3px rgba(196,164,107,.1);
}
.se-search svg { width: 14px; height: 14px; stroke: var(--page-muted); flex-shrink: 0; }
.se-search input {
    border: none; background: transparent; outline: none;
    font-size: 12px; font-family: var(--font-ui);
    color: var(--page-text); width: 100%;
}
.se-search input::placeholder { color: var(--page-muted); }
.se-filter-select {
    background: var(--page-bg); border: 1.5px solid var(--page-border);
    border-radius: var(--radius-sm); padding: 9px 14px;
    font-size: 12px; font-family: var(--font-ui);
    color: var(--page-text); outline: none; cursor: pointer;
    transition: border-color var(--tr);
}
.se-filter-select:focus { border-color: var(--gold-border); }
.se-count-label {
    font-size: 11px; color: var(--page-muted);
    font-family: var(--font-ui); margin-left: auto; white-space: nowrap;
}
#visible-count { color: var(--gold); font-weight: 700; }

/* ── CARDS GRID ──────────────────────────────────────────── */
.se-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 22px;
    animation: fadeUp .5s .16s var(--ease) both;
}

/* ── BOOK CARD ───────────────────────────────────────────── */
.se-card {
    background: var(--page-white); border: 1px solid var(--page-border);
    border-radius: var(--radius); overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: transform var(--tr), box-shadow var(--tr), border-color var(--tr);
}
.se-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); border-color: var(--gold-border); }

/* COVER with real book image */
.sc-cover {
    height: 200px; position: relative; overflow: hidden;
    background: linear-gradient(135deg, var(--ink) 0%, var(--ink3) 100%);
}
.sc-cover-img {
    width: 100%; height: 100%; object-fit: cover; display: block;
    transition: transform .5s var(--ease);
}
.se-card:hover .sc-cover-img { transform: scale(1.05); }
.sc-cover-gradient {
    position: absolute; inset: 0;
    background: linear-gradient(135deg, var(--ink) 0%, var(--ink3) 100%);
    display: flex; align-items: center; justify-content: center;
}
.sc-spine {
    width: 110px; padding: 16px 12px;
    border-radius: 6px; text-align: center;
    box-shadow: 0 6px 24px rgba(0,0,0,.4);
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.sc-spine-title {
    font-family: var(--font-serif); font-size: 11px;
    color: rgba(255,255,255,.85); line-height: 1.4; word-break: break-word;
}
.sc-spine-line { width: 40px; height: 1px; background: rgba(255,255,255,.25); }
/* ÉPUISÉ ribbon */
.sc-cover::before {
    content: '<?= addslashes($l['epuise']) ?>';
    position: absolute; top: 18px; right: -30px;
    background: var(--danger); color: #fff;
    font-family: var(--font-ui); font-size: 9px; font-weight: 800;
    letter-spacing: 1.5px; padding: 5px 42px;
    transform: rotate(45deg); z-index: 10;
    box-shadow: 0 2px 8px rgba(0,0,0,.25);
}
/* Overlay on hover */
.sc-cover-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,.6) 0%, transparent 50%);
    pointer-events: none;
}

/* BODY */
.sc-body { padding: 18px 20px 16px; }
.sc-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 6px; }
.sc-title {
    font-family: var(--font-serif); font-size: 17px; font-weight: 700;
    color: var(--page-text); line-height: 1.2;
}
.sc-price {
    font-size: 14px; font-weight: 700; color: var(--gold);
    white-space: nowrap; flex-shrink: 0; font-family: var(--font-ui);
}
.sc-author {
    font-size: 11px; color: var(--page-muted);
    margin-bottom: 12px; display: flex; align-items: center; gap: 5px;
}
.sc-author i { font-size: 10px; }
.sc-meta { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }
.sc-tag {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10px; font-family: var(--font-ui); font-weight: 600;
    padding: 3px 10px; border-radius: 20px;
    background: var(--page-bg2); border: 1px solid var(--page-border); color: var(--page-muted);
}
.sc-tag i { font-size: 9px; }
.dispo-achat   { background: rgba(146,64,14,.08); border-color: rgba(146,64,14,.2); color: var(--warning); }
.dispo-emprunt { background: rgba(27,79,138,.08); border-color: rgba(27,79,138,.2); color: var(--info); }
.dispo-both    { background: var(--success-bg); border-color: rgba(39,103,73,.2); color: var(--success); }

/* Info grid */
.sc-info-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 8px; margin-bottom: 14px;
}
.sc-info-item { display: flex; flex-direction: column; gap: 2px; }
.sc-info-lbl  { font-size: 8px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--page-muted); }
.sc-info-val  { font-size: 12px; color: var(--page-text); font-weight: 600; }

/* Stock warning */
.sc-stock-row {
    display: flex; align-items: center; justify-content: space-between;
    background: var(--danger-bg); border: 1px solid var(--danger-border);
    border-radius: var(--radius-sm); padding: 9px 14px; margin-bottom: 14px;
}
.sc-stock-warn {
    display: flex; align-items: center; gap: 7px;
    font-size: 11px; font-weight: 700; color: var(--danger);
}
.sc-stock-warn i { font-size: 12px; }
.sc-stock-total { font-size: 11px; color: var(--page-muted); }

/* RESTOCK BUTTON */
.sc-restock-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 12px 16px; border-radius: var(--radius-sm);
    font-family: var(--font-ui); font-size: 12px; font-weight: 700;
    text-decoration: none; cursor: pointer; border: 1.5px solid;
    transition: all var(--tr); letter-spacing: .2px;
}
.sc-restock-btn i { font-size: 12px; }
.btn-pret {
    background: var(--ink2); border-color: rgba(196,164,107,.2); color: rgba(237,229,212,.85);
}
.btn-pret:hover { background: var(--ink3); border-color: var(--gold-border); color: var(--gold); transform: translateY(-1px); }
.btn-vente {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-deep) 100%);
    border-color: var(--gold); color: var(--ink2);
    box-shadow: 0 4px 14px rgba(196,164,107,.3);
}
.btn-vente:hover { background: linear-gradient(135deg, var(--gold2) 0%, var(--gold) 100%); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(196,164,107,.4); }
.btn-both {
    background: linear-gradient(135deg, var(--ink2) 0%, var(--ink3) 50%, rgba(196,164,107,.2) 100%);
    border-color: var(--gold-border); color: rgba(237,229,212,.85);
}
.btn-both:hover { border-color: var(--gold); color: var(--gold); transform: translateY(-1px); }

/* ── EMPTY ───────────────────────────────────────────────── */
.se-empty {
    grid-column: 1 / -1; text-align: center; padding: 70px 20px;
}
.se-empty-icon {
    width: 72px; height: 72px; border-radius: 50%; margin: 0 auto 20px;
    background: var(--success-bg); border: 1.5px solid rgba(39,103,73,.22);
    display: flex; align-items: center; justify-content: center;
}
.se-empty-icon i { font-size: 28px; color: var(--success); }
.se-empty-title {
    font-family: var(--font-serif); font-size: 24px; font-weight: 700;
    color: var(--page-text); margin-bottom: 8px;
}
.se-empty-sub { font-size: 13px; color: var(--page-muted); }
.se-no-results {
    display: none; grid-column: 1 / -1; text-align: center;
    padding: 40px 20px; color: var(--page-muted); font-size: 13px;
}

/* ── RESPONSIVE ──────────────────────────────────────────── */
@media (max-width: 900px) {
    .se-stats-bar { grid-template-columns: 1fr 1fr; }
    .se-hero      { padding: 22px 20px; }
}
@media (max-width: 600px) {
    .se-stats-bar { grid-template-columns: 1fr 1fr; }
    .se-grid      { grid-template-columns: 1fr; }
}
</style>

<div class="se-wrap">
<div class="se-main">

    <?php if ($flash_msg): ?>
    <div class="se-flash <?= htmlspecialchars($flash_type) ?>">
        <i class="fa-solid fa-<?= $flash_type==='success' ? 'circle-check' : 'circle-exclamation' ?>"></i>
        <?= htmlspecialchars($flash_msg) ?>
    </div>
    <?php endif; ?>

    <!-- HERO -->
    <div class="se-hero">
        <div>
            <div class="se-breadcrumb">
                <a href="admin_dashboard.php">
                    <i class="fa-solid fa-house" style="font-size:9px"></i>
                    <?= $l['breadcrumb'] ?>
                </a>
                <i class="fa-solid fa-chevron-right" style="font-size:8px;opacity:.4"></i>
                <span><?= $l['title'] ?></span>
            </div>
            <div class="se-title">
                <?= $l['title'] ?>
                <?php if ($total_epuise > 0): ?>
                <span class="se-title-badge"><?= $total_epuise ?></span>
                <?php endif; ?>
            </div>
            <div class="se-sub"><?= $l['sub'] ?></div>
        </div>
        <a href="admin_dashboard.php" class="se-back-btn">
            <i class="fa-solid fa-arrow-<?= $isRtl ? 'right' : 'left' ?>" style="font-size:10px"></i>
            <?= $l['back'] ?>
        </a>
    </div>

    <!-- STATS -->
    <div class="se-stats-bar">
        <div class="se-stat">
            <div class="se-stat-icon red">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div><div class="se-stat-val"><?= $total_epuise ?></div><div class="se-stat-lbl"><?= $l['stat_total'] ?></div></div>
        </div>
        <div class="se-stat">
            <div class="se-stat-icon amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--warning)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
            </div>
            <div><div class="se-stat-val"><?= $achat_count ?></div><div class="se-stat-lbl"><?= $l['stat_vente'] ?></div></div>
        </div>
        <div class="se-stat">
            <div class="se-stat-icon blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--info)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>
            </div>
            <div><div class="se-stat-val"><?= $emprunt_count ?></div><div class="se-stat-lbl"><?= $l['stat_pret'] ?></div></div>
        </div>
        <div class="se-stat">
            <div class="se-stat-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
            </div>
            <div><div class="se-stat-val"><?= $both_count ?></div><div class="se-stat-lbl"><?= $l['stat_both'] ?></div></div>
        </div>
    </div>

    <!-- TOOLBAR -->
    <div class="se-toolbar">
        <div class="se-search">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="search-input" placeholder="<?= htmlspecialchars($l['search_ph']) ?>">
        </div>
        <select class="se-filter-select" id="filter-dispo">
            <option value=""><?= $l['all_types'] ?></option>
            <option value="achat"><?= $l['stat_vente'] ?></option>
            <option value="emprunt"><?= $l['stat_pret'] ?></option>
            <option value="both"><?= $l['stat_both'] ?></option>
        </select>
        <select class="se-filter-select" id="filter-cat">
            <option value=""><?= $l['all_cats'] ?></option>
            <?php
            $cats = $conn->query("SELECT DISTINCT categorie FROM documents WHERE exemplaires_disponibles<=0 AND categorie IS NOT NULL ORDER BY categorie");
            while ($cat = $cats->fetch_assoc()):
            ?>
            <option value="<?= htmlspecialchars($cat['categorie']) ?>"><?= htmlspecialchars($cat['categorie']) ?></option>
            <?php endwhile; ?>
        </select>
        <span class="se-count-label">
            <span id="visible-count"><?= $total_epuise ?></span>
            <?= $total_epuise != 1 ? $l['results_pl'] : $l['results'] ?>
        </span>
    </div>

    <!-- CARDS GRID -->
    <div class="se-grid" id="cards-grid">

    <?php if ($total_epuise === 0): ?>
        <div class="se-empty">
            <div class="se-empty-icon"><i class="fa-solid fa-check"></i></div>
            <div class="se-empty-title"><?= $l['empty_title'] ?></div>
            <div class="se-empty-sub"><?= $l['empty_sub'] ?></div>
        </div>
    <?php else:
        $gradients = [
            'linear-gradient(145deg,#1a0a2e,#3d1054)',
            'linear-gradient(145deg,#0a1628,#1e3a5f)',
            'linear-gradient(145deg,#1a0a0a,#5c1a1a)',
            'linear-gradient(145deg,#0a1a0a,#1a4a1a)',
            'linear-gradient(145deg,#1a150a,#4a3010)',
            'linear-gradient(145deg,#0a1a1a,#0a3a3a)',
            'linear-gradient(145deg,#1a0a1a,#3d103d)',
            'linear-gradient(145deg,#1a1a0a,#3d3d10)',
        ];
        $i = 0;
        while ($doc = $result->fetch_assoc()):
            $grad  = $doc['cover_color'] ?: $gradients[$i % count($gradients)];
            $i++;
            $dispo = $doc['disponible_pour'];
            $dispo_cls   = $dispo==='achat'?'dispo-achat':($dispo==='emprunt'?'dispo-emprunt':'dispo-both');
            $dispo_label = $dispo==='achat' ? $l['vente'] : ($dispo==='emprunt' ? $l['pret'] : $l['both']);
            $dispo_icon  = $dispo==='achat' ? 'fa-cart-shopping' : ($dispo==='emprunt' ? 'fa-book-open' : 'fa-layer-group');
            $btn_class   = $dispo==='achat' ? 'btn-vente' : ($dispo==='both' ? 'btn-both' : 'btn-pret');

            // Image path
            $img = '/MEMOIR/uploads/' . (int)$doc['id_doc'] . '.jpg';
            $img_exists = file_exists($_SERVER['DOCUMENT_ROOT'] . $img);
            if (!$img_exists && !empty($doc['image_doc'])) {
                $img = '/MEMOIR/uploads/' . $doc['image_doc'];
                $img_exists = file_exists($_SERVER['DOCUMENT_ROOT'] . $img);
            }
            if (!$img_exists) $img = '';
    ?>
    <div class="se-card"
         data-titre="<?= strtolower(htmlspecialchars($doc['titre'])) ?>"
         data-auteur="<?= strtolower(htmlspecialchars($doc['auteur'] ?? '')) ?>"
         data-cat="<?= strtolower(htmlspecialchars($doc['categorie'] ?? '')) ?>"
         data-dispo="<?= htmlspecialchars($dispo) ?>">

        <!-- COVER -->
        <div class="sc-cover">
            <?php if ($img_exists || $img): ?>
            <img class="sc-cover-img"
                 src="<?= htmlspecialchars($img) ?>"
                 alt="<?= htmlspecialchars($doc['titre']) ?>"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="sc-cover-gradient" style="display:none">
            <?php else: ?>
            <div class="sc-cover-gradient">
            <?php endif; ?>
                <div class="sc-spine" style="background:<?= htmlspecialchars($grad) ?>">
                    <div class="sc-spine-title"><?= htmlspecialchars(mb_substr($doc['titre'],0,38)) ?></div>
                    <div class="sc-spine-line"></div>
                    <?php if (!empty($doc['auteur'])): ?>
                    <div style="font-size:9px;color:rgba(255,255,255,.5);text-align:center"><?= htmlspecialchars(mb_substr($doc['auteur'],0,20)) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="sc-cover-overlay"></div>
        </div>

        <!-- BODY -->
        <div class="sc-body">
            <div class="sc-header">
                <div class="sc-title"><?= htmlspecialchars($doc['titre']) ?></div>
                <?php if ($doc['prix'] > 0): ?>
                <div class="sc-price"><?= number_format($doc['prix'], 0) ?> <small style="font-size:10px;font-weight:400;opacity:.7">DA</small></div>
                <?php endif; ?>
            </div>

            <?php if (!empty($doc['auteur'])): ?>
            <div class="sc-author">
                <i class="fa-solid fa-user-pen"></i>
                <?= htmlspecialchars($doc['auteur']) ?>
            </div>
            <?php endif; ?>

            <div class="sc-meta">
                <?php if (!empty($doc['categorie'])): ?>
                <span class="sc-tag"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($doc['categorie']) ?></span>
                <?php endif; ?>
                <?php if (!empty($doc['langue'])): ?>
                <span class="sc-tag"><i class="fa-solid fa-globe"></i> <?= htmlspecialchars($doc['langue']) ?></span>
                <?php endif; ?>
                <span class="sc-tag <?= $dispo_cls ?>">
                    <i class="fa-solid <?= $dispo_icon ?>"></i> <?= $dispo_label ?>
                </span>
            </div>

            <div class="sc-info-grid">
                <?php if (!empty($doc['annee_edition'])): ?>
                <div class="sc-info-item">
                    <span class="sc-info-lbl"><?= $l['edition'] ?></span>
                    <span class="sc-info-val"><?= $doc['annee_edition'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($doc['editeur'])): ?>
                <div class="sc-info-item">
                    <span class="sc-info-lbl"><?= $l['editeur'] ?></span>
                    <span class="sc-info-val"><?= htmlspecialchars(mb_substr($doc['editeur'],0,20)) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($doc['nb_pages'])): ?>
                <div class="sc-info-item">
                    <span class="sc-info-lbl"><?= $l['pages'] ?></span>
                    <span class="sc-info-val"><?= $doc['nb_pages'] ?> p.</span>
                </div>
                <?php endif; ?>
                <?php if (!empty($doc['isbn'])): ?>
                <div class="sc-info-item">
                    <span class="sc-info-lbl">ISBN</span>
                    <span class="sc-info-val" style="font-size:10px"><?= htmlspecialchars($doc['isbn']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="sc-stock-row">
                <div class="sc-stock-warn">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= $l['stock_zero'] ?>
                </div>
                <div class="sc-stock-total">
                    <?= $l['stock_total'] ?>: <?= (int)$doc['exemplaires'] ?> <?= $l['ex'] ?>
                </div>
            </div>

            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="restock.php?id=<?= $doc['id_doc'] ?>" class="sc-restock-btn <?= $btn_class ?>">
                <i class="fa-solid fa-rotate-right"></i>
                <?= $l['reappro'] ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile; endif; ?>

    <div class="se-no-results" id="no-results">
        <p><?= $l['no_results'] ?></p>
    </div>
    </div>

</div>
</div>

<script>
(function() {
    const searchInput = document.getElementById('search-input');
    const filterDispo = document.getElementById('filter-dispo');
    const filterCat   = document.getElementById('filter-cat');
    const cards       = document.querySelectorAll('.se-card');
    const countEl     = document.getElementById('visible-count');
    const noResults   = document.getElementById('no-results');

    function filterCards() {
        const q    = searchInput.value.toLowerCase().trim();
        const disp = filterDispo.value;
        const cat  = filterCat.value.toLowerCase();
        let visible = 0;
        cards.forEach(card => {
            const matchQ    = !q    || card.dataset.titre.includes(q) || card.dataset.auteur.includes(q) || card.dataset.cat.includes(q);
            const matchDisp = !disp || card.dataset.dispo === disp;
            const matchCat  = !cat  || card.dataset.cat === cat;
            const show = matchQ && matchDisp && matchCat;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        countEl.textContent = visible;
        noResults.style.display = visible === 0 && cards.length > 0 ? 'block' : 'none';
    }
    searchInput.addEventListener('input', filterCards);
    filterDispo.addEventListener('change', filterCards);
    filterCat.addEventListener('change', filterCards);
})();
</script>

<?php include "../includes/footer.php"; ?>