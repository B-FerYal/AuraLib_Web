<?php
/**
 * stock_epuise.php — AuraLib (updated)
 * Changes vs original:
 *  - Flash message banner at top (success/error from restock.php POST)
 *  - Réapprovisionner button now → restock.php?id=XX  (smart routing)
 *  - Button hidden if not admin (extra guard, already implied by page auth)
 */
include "../includes/header.php";
include '../includes/dark_init.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location:../client/library.php");
    exit;
}

// Fetch all out-of-stock documents with full info
$result = $conn->query("
    SELECT d.*, t.libelle_type AS nom_type
    FROM documents d
    LEFT JOIN types_documents t ON d.id_type = t.id_type
    WHERE d.exemplaires_disponibles <= 0
    ORDER BY d.titre ASC
");

$total_epuise = $result ? $result->num_rows : 0;

// Stats breakdown
$achat_count   = $conn->query("SELECT COUNT(*) c FROM documents WHERE exemplaires_disponibles<=0 AND disponible_pour='achat'")->fetch_assoc()['c'] ?? 0;
$emprunt_count = $conn->query("SELECT COUNT(*) c FROM documents WHERE exemplaires_disponibles<=0 AND disponible_pour='emprunt'")->fetch_assoc()['c'] ?? 0;
$both_count    = $conn->query("SELECT COUNT(*) c FROM documents WHERE exemplaires_disponibles<=0 AND disponible_pour='both'")->fetch_assoc()['c'] ?? 0;

// Flash message from restock.php
$flash_type = $_SESSION['flash_type'] ?? '';
$flash_msg  = $_SESSION['flash_msg']  ?? '';
unset($_SESSION['flash_type'], $_SESSION['flash_msg']);
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,700;1,500&family=DM+Sans:wght@300;400;500;600&display=swap');

/* ── Layout ── */
.se-wrap {
    display: flex;
    min-height: 100vh;
    background: #F5F0E8;
    padding-top: 66px;
}
.se-main {
    flex: 1;
    padding: 38px 38px 70px;
    min-width: 0;
}

/* ── Flash ── */
.se-flash {
    padding: 13px 18px; border-radius: 10px; font-size: 13px; font-weight: 500;
    margin-bottom: 22px; display: flex; align-items: center; gap: 10px;
    font-family: 'DM Sans', sans-serif;
}
.se-flash.success { background: #f0fdf4; border: 1px solid #86efac; color: #16a34a; }
.se-flash.error   { background: #fef2f2; border: 1px solid #fca5a5; color: #EF4444; }

/* ── Page header ── */
.se-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 14px;
}
.se-header-left {}
.se-breadcrumb {
    font-size: 11px;
    color: #B8A898;
    letter-spacing: .5px;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.se-breadcrumb a { color: #C4A46B; text-decoration: none; }
.se-breadcrumb a:hover { text-decoration: underline; }
.se-title {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    font-weight: 700;
    color: #2C1F0E;
    display: flex;
    align-items: center;
    gap: 12px;
    line-height: 1.1;
}
.se-title-badge {
    background: #EF4444;
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    letter-spacing: .3px;
}
.se-sub {
    font-size: 12px;
    color: #9A8C7E;
    margin-top: 5px;
    font-family: 'DM Sans', sans-serif;
}
.se-back-btn {
    display: flex;
    align-items: center;
    gap: 7px;
    background: #FFFDF9;
    border: 1px solid #DDD5C8;
    color: #2C1F0E;
    padding: 9px 18px;
    border-radius: 9px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    transition: border-color .15s, box-shadow .15s;
}
.se-back-btn:hover {
    border-color: #C4A46B;
    box-shadow: 0 2px 8px rgba(196,164,107,.15);
}

/* ── Mini stats bar ── */
.se-stats-bar {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 28px;
}
.se-stat {
    background: #FFFDF9;
    border: 1px solid #DDD5C8;
    border-radius: 11px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.se-stat-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.se-stat-icon.red   { background: #FEE2E2; }
.se-stat-icon.amber { background: #FEF3C7; }
.se-stat-icon.blue  { background: #DBEAFE; }
.se-stat-icon.green { background: #DCFCE7; }
.se-stat-icon svg { width: 20px; height: 20px; }
.se-stat-val {
    font-family: 'Playfair Display', serif;
    font-size: 26px;
    font-weight: 700;
    color: #2C1F0E;
    line-height: 1;
}
.se-stat-lbl {
    font-size: 10px;
    color: #9A8C7E;
    letter-spacing: .5px;
    text-transform: uppercase;
    margin-top: 3px;
    font-family: 'DM Sans', sans-serif;
}

/* ── Search + filter bar ── */
.se-toolbar {
    background: #FFFDF9;
    border: 1px solid #DDD5C8;
    border-radius: 11px;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 22px;
    flex-wrap: wrap;
}
.se-search {
    flex: 1;
    min-width: 200px;
    display: flex;
    align-items: center;
    gap: 8px;
    background: #F5F0E8;
    border: 1px solid #DDD5C8;
    border-radius: 8px;
    padding: 8px 14px;
}
.se-search svg { width: 14px; height: 14px; color: #9A8C7E; stroke: #9A8C7E; flex-shrink: 0; }
.se-search input {
    border: none;
    background: transparent;
    outline: none;
    font-size: 12px;
    font-family: 'DM Sans', sans-serif;
    color: #2C1F0E;
    width: 100%;
}
.se-search input::placeholder { color: #B8A898; }
.se-filter-select {
    background: #F5F0E8;
    border: 1px solid #DDD5C8;
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 12px;
    font-family: 'DM Sans', sans-serif;
    color: #2C1F0E;
    outline: none;
    cursor: pointer;
}
.se-count-label {
    font-size: 11px;
    color: #9A8C7E;
    font-family: 'DM Sans', sans-serif;
    margin-left: auto;
    white-space: nowrap;
}
#visible-count { color: #C4A46B; font-weight: 700; }

/* ── Cards grid ── */
.se-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

/* ── Book card ── */
.se-card {
    background: #FFFDF9;
    border: 1px solid #DDD5C8;
    border-radius: 14px;
    overflow: hidden;
    transition: box-shadow .18s, transform .18s;
}
.se-card:hover {
    box-shadow: 0 8px 28px rgba(44,31,14,.10);
    transform: translateY(-2px);
}

/* Cover */
.sc-cover {
    height: 160px;
    background: #E8E0D4;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
.sc-cover-bg {
    position: absolute; inset: 0; opacity: .75;
}
/* Épuisé ribbon */
.sc-cover::before {
    content: 'ÉPUISÉ';
    position: absolute;
    top: 18px; right: -28px;
    background: #EF4444;
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.2px;
    padding: 5px 40px;
    transform: rotate(45deg);
    z-index: 10;
    box-shadow: 0 2px 6px rgba(0,0,0,.2);
}
.sc-cover-spine {
    position: relative;
    z-index: 2;
    width: 100px; min-height: 130px;
    border-radius: 5px;
    padding: 16px 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,.3);
}
.sc-spine-title {
    font-family: 'Playfair Display', serif;
    font-size: 10px;
    color: rgba(255,255,255,.85);
    text-align: center;
    line-height: 1.4;
    word-break: break-word;
}
.sc-spine-line { width: 40px; height: 1px; background: rgba(255,255,255,.3); }
.sc-type-dot   { width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,.4); }

/* Body */
.sc-body { padding: 18px 18px 16px; }
.sc-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 8px;
}
.sc-title {
    font-family: 'Playfair Display', serif;
    font-size: 15px;
    font-weight: 700;
    color: #2C1F0E;
    line-height: 1.25;
}
.sc-price {
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: #C4A46B;
    white-space: nowrap;
    flex-shrink: 0;
}
.sc-author {
    font-size: 11px;
    color: #9A8C7E;
    margin-bottom: 10px;
    font-family: 'DM Sans', sans-serif;
}
.sc-meta { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 12px; }
.sc-tag {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10px; font-family: 'DM Sans', sans-serif;
    background: #F5F0E8; border: 1px solid #DDD5C8; color: #6B5E52;
    padding: 3px 9px; border-radius: 20px;
}
.sc-tag svg { width: 9px; height: 9px; stroke: #9A8C7E; flex-shrink: 0; }
.dispo-achat  { background: #FEF9C3; border-color: #FDE047; color: #854D0E; }
.dispo-emprunt{ background: #DBEAFE; border-color: #93C5FD; color: #1E40AF; }
.dispo-both   { background: #F0FDF4; border-color: #86EFAC; color: #14532D; }

/* Info grid */
.sc-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    margin-bottom: 12px;
}
.sc-info-item { display: flex; flex-direction: column; gap: 1px; }
.sc-info-lbl { font-size: 9px; color: #B8A898; letter-spacing: .5px; text-transform: uppercase; font-family: 'DM Sans', sans-serif; }
.sc-info-val { font-size: 11px; color: #2C1F0E; font-weight: 500; font-family: 'DM Sans', sans-serif; }

/* Stock warning */
.sc-stock-section {
    background: #FEF2F2;
    border: 1px solid rgba(239,68,68,.2);
    border-radius: 8px;
    padding: 9px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.sc-stock-text {
    display: flex; align-items: center; gap: 6px;
    font-size: 11px; font-weight: 600; color: #EF4444;
    font-family: 'DM Sans', sans-serif;
}
.sc-stock-text svg { width: 13px; height: 13px; stroke: #EF4444; flex-shrink: 0; }
.sc-stock-total { font-size: 11px; color: #9A8C7E; font-family: 'DM Sans', sans-serif; }

/* ── RESTOCK BUTTON — smart routing ── */
.sc-action-btn {
    display: flex; align-items: center; justify-content: center; gap: 7px;
    width: 100%; padding: 11px;
    background: #2C1F0E; color: #FFFDF9;
    border-radius: 9px; text-decoration: none;
    font-size: 12px; font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    transition: background .15s, transform .1s;
}
.sc-action-btn:hover { background: #3d2f1a; }
.sc-action-btn:active { transform: scale(.99); }
.sc-action-btn svg { width: 13px; height: 13px; stroke: currentColor; }

/* Vente variant: golden button */
.sc-action-btn.btn-vente {
    background: linear-gradient(135deg, #C4A46B, #D4B47B);
    color: #2C1F0E;
}
.sc-action-btn.btn-vente:hover { background: linear-gradient(135deg, #D4B47B, #E4C48B); }

/* Both variant: split style */
.sc-action-btn.btn-both {
    background: linear-gradient(135deg, #2C1F0E 50%, #C4A46B 50%);
    color: #FFFDF9;
}

/* ── Empty state ── */
.se-empty {
    grid-column: 1/-1;
    text-align: center;
    padding: 60px 20px;
}
.se-empty-icon {
    width: 64px; height: 64px;
    background: #F0FDF4; border: 1px solid #86EFAC;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
}
.se-empty-icon svg { width: 28px; height: 28px; stroke: #16A34A; }
.se-empty-title {
    font-family: 'Playfair Display', serif;
    font-size: 20px; font-weight: 700; color: #2C1F0E; margin-bottom: 6px;
}
.se-empty-sub { font-size: 12px; color: #9A8C7E; font-family: 'DM Sans', sans-serif; }

.se-no-results {
    display: none; text-align: center; padding: 40px 20px;
    color: #9A8C7E; font-family: 'DM Sans', sans-serif; font-size: 13px;
    grid-column: 1/-1;
}

/* ── Dark ── */
html.dark .se-wrap     { background: #110C06; }
html.dark .se-stat     { background: #1C1410; border-color: #3E3228; }
html.dark .se-stat-val { color: #F0E8D8; }
html.dark .se-toolbar  { background: #1C1410; border-color: #3E3228; }
html.dark .se-search   { background: #2C2418; border-color: #3E3228; }
html.dark .se-search input { color: #F0E8D8; }
html.dark .se-filter-select { background: #2C2418; border-color: #3E3228; color: #F0E8D8; }
html.dark .se-card      { background: #1C1410; border-color: #3E3228; }
html.dark .sc-title     { color: #F0E8D8; }
html.dark .sc-info-val  { color: #F0E8D8; }
html.dark .se-title     { color: #F0E8D8; }
html.dark .se-back-btn  { background: #1C1410; border-color: #3E3228; color: #F0E8D8; }
html.dark .se-flash.success { background: #052e16; border-color: #166534; }
html.dark .se-flash.error   { background: #450a0a; border-color: #991b1b; }
</style>

<div class="se-wrap">
<div class="se-main">

    <?php if ($flash_msg): ?>
    <div class="se-flash <?= htmlspecialchars($flash_type) ?>">
      <?php if ($flash_type === 'success'): ?>
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" width="16" height="16" style="stroke:#16a34a;flex-shrink:0"><polyline points="20 6 9 17 4 12"/></svg>
      <?php else: ?>
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" width="16" height="16" style="stroke:#EF4444;flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?php endif; ?>
      <?= htmlspecialchars($flash_msg) ?>
    </div>
    <?php endif; ?>

    <!-- ── Page header ── -->
    <div class="se-header">
        <div class="se-header-left">
            <div class="se-breadcrumb">
                <a href="admin_dashboard.php">Tableau de bord</a>
                <span>›</span> Stocks épuisés
            </div>
            <div class="se-title">
                Stocks Épuisés
                <?php if ($total_epuise > 0): ?>
                <span class="se-title-badge"><?= $total_epuise ?></span>
                <?php endif; ?>
            </div>
            <div class="se-sub">Documents en rupture de stock nécessitant un réapprovisionnement</div>
        </div>
        <a href="admin_dashboard.php" class="se-back-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" width="12" height="12" style="stroke:currentColor"><polyline points="15 18 9 12 15 6"/></svg>
            Tableau de bord
        </a>
    </div>

    <!-- ── Stats bar ── -->
    <div class="se-stats-bar">
        <div class="se-stat">
            <div class="se-stat-icon red">
                <svg viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2" stroke-linecap="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div>
                <div class="se-stat-val"><?= $total_epuise ?></div>
                <div class="se-stat-lbl">Total épuisés</div>
            </div>
        </div>
        <div class="se-stat">
            <div class="se-stat-icon amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
            <div>
                <div class="se-stat-val"><?= $achat_count ?></div>
                <div class="se-stat-lbl">Vente</div>
            </div>
        </div>
        <div class="se-stat">
            <div class="se-stat-icon blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            </div>
            <div>
                <div class="se-stat-val"><?= $emprunt_count ?></div>
                <div class="se-stat-lbl">Prêt</div>
            </div>
        </div>
        <div class="se-stat">
            <div class="se-stat-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </div>
            <div>
                <div class="se-stat-val"><?= $both_count ?></div>
                <div class="se-stat-lbl">Les deux</div>
            </div>
        </div>
    </div>

    <!-- ── Toolbar ── -->
    <div class="se-toolbar">
        <div class="se-search">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="search-input" placeholder="Rechercher par titre, auteur, catégorie…">
        </div>
        <select class="se-filter-select" id="filter-dispo">
            <option value="">Tous types</option>
            <option value="achat">Vente</option>
            <option value="emprunt">Prêt</option>
            <option value="both">Les deux</option>
        </select>
        <select class="se-filter-select" id="filter-cat">
            <option value="">Toutes catégories</option>
            <?php
            $cats = $conn->query("SELECT DISTINCT categorie FROM documents WHERE exemplaires_disponibles<=0 AND categorie IS NOT NULL ORDER BY categorie");
            while ($cat = $cats->fetch_assoc()):
            ?>
            <option value="<?= htmlspecialchars($cat['categorie']) ?>"><?= htmlspecialchars($cat['categorie']) ?></option>
            <?php endwhile; ?>
        </select>
        <span class="se-count-label"><span id="visible-count"><?= $total_epuise ?></span> résultat<?= $total_epuise != 1 ? 's' : '' ?></span>
    </div>

    <!-- ── Cards grid ── -->
    <div class="se-grid" id="cards-grid">

    <?php if ($total_epuise === 0): ?>
        <div class="se-empty">
            <div class="se-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="se-empty-title">Aucun stock épuisé !</div>
            <div class="se-empty-sub">Tous vos documents sont disponibles en stock.</div>
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
            $grad = $doc['cover_color'] ?: $gradients[$i % count($gradients)];
            $i++;
            $dispo = $doc['disponible_pour'];
            $dispo_class = $dispo === 'achat' ? 'dispo-achat' : ($dispo === 'emprunt' ? 'dispo-emprunt' : 'dispo-both');
            $dispo_label = $dispo === 'achat' ? 'Vente' : ($dispo === 'emprunt' ? 'Prêt' : 'Vente & Prêt');

            // Smart button style based on type
            $btn_class = 'sc-action-btn';
            if ($dispo === 'achat')   $btn_class .= ' btn-vente';
            elseif ($dispo === 'both') $btn_class .= ' btn-both';
    ?>
    <div class="se-card"
         data-titre="<?= strtolower(htmlspecialchars($doc['titre'])) ?>"
         data-auteur="<?= strtolower(htmlspecialchars($doc['auteur'] ?? '')) ?>"
         data-cat="<?= strtolower(htmlspecialchars($doc['categorie'] ?? '')) ?>"
         data-dispo="<?= htmlspecialchars($dispo) ?>">

        <!-- Cover -->
        <div class="sc-cover">
            <div class="sc-cover-bg" style="background:<?= htmlspecialchars($grad) ?>"></div>
            <div class="sc-cover-spine" style="background:<?= htmlspecialchars($grad) ?>">
                <div class="sc-spine-title"><?= htmlspecialchars(mb_substr($doc['titre'],0,40)) ?></div>
                <div class="sc-spine-line"></div>
                <div class="sc-type-dot"></div>
            </div>
        </div>

        <!-- Body -->
        <div class="sc-body">
            <div class="sc-header">
                <div class="sc-title"><?= htmlspecialchars($doc['titre']) ?></div>
                <?php if ($doc['prix'] > 0): ?>
                <div class="sc-price"><?= number_format($doc['prix'], 0) ?> DA</div>
                <?php endif; ?>
            </div>

            <?php if ($doc['auteur']): ?>
            <div class="sc-author">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#9A8C7E" stroke-width="2" stroke-linecap="round" style="display:inline;vertical-align:middle;margin-right:3px"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <?= htmlspecialchars($doc['auteur']) ?>
            </div>
            <?php endif; ?>

            <div class="sc-meta">
                <?php if ($doc['categorie']): ?>
                <span class="sc-tag">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                    <?= htmlspecialchars($doc['categorie']) ?>
                </span>
                <?php endif; ?>
                <?php if ($doc['langue']): ?>
                <span class="sc-tag">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    <?= htmlspecialchars($doc['langue']) ?>
                </span>
                <?php endif; ?>
                <span class="sc-tag <?= $dispo_class ?>"><?= $dispo_label ?></span>
            </div>

            <!-- Info grid -->
            <div class="sc-info-grid">
                <?php if ($doc['annee_edition']): ?>
                <div class="sc-info-item">
                    <span class="sc-info-lbl">Édition</span>
                    <span class="sc-info-val"><?= $doc['annee_edition'] ?></span>
                </div>
                <?php endif; ?>
                <?php if ($doc['editeur']): ?>
                <div class="sc-info-item">
                    <span class="sc-info-lbl">Éditeur</span>
                    <span class="sc-info-val"><?= htmlspecialchars(mb_substr($doc['editeur'],0,22)) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($doc['nb_pages']): ?>
                <div class="sc-info-item">
                    <span class="sc-info-lbl">Pages</span>
                    <span class="sc-info-val"><?= $doc['nb_pages'] ?> p.</span>
                </div>
                <?php endif; ?>
                <?php if ($doc['isbn']): ?>
                <div class="sc-info-item">
                    <span class="sc-info-lbl">ISBN</span>
                    <span class="sc-info-val" style="font-size:10px"><?= htmlspecialchars($doc['isbn']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Stock warning -->
            <div class="sc-stock-section">
                <div class="sc-stock-text">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    0 exemplaire disponible
                </div>
                <div class="sc-stock-total">Total: <?= (int)$doc['exemplaires'] ?> ex.</div>
            </div>

            <!-- ══ SMART RESTOCK BUTTON ══
                 Routes to restock.php?id=XX
                 Button color adapts to document type:
                   Prêt   → dark ink button
                   Vente  → golden button
                   Both   → split gradient button
            -->
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="restock.php?id=<?= $doc['id_doc'] ?>" class="<?= $btn_class ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Réapprovisionner
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile; endif; ?>

    <div class="se-no-results" id="no-results">
        <p>Aucun document ne correspond à votre recherche.</p>
    </div>
    </div>

</div>
</div>

<script>
(function() {
    const searchInput  = document.getElementById('search-input');
    const filterDispo  = document.getElementById('filter-dispo');
    const filterCat    = document.getElementById('filter-cat');
    const cards        = document.querySelectorAll('.se-card');
    const countEl      = document.getElementById('visible-count');
    const noResults    = document.getElementById('no-results');

    function filterCards() {
        const q    = searchInput.value.toLowerCase().trim();
        const disp = filterDispo.value;
        const cat  = filterCat.value.toLowerCase();
        let visible = 0;

        cards.forEach(card => {
            const matchQ    = !q    || card.dataset.titre.includes(q) || card.dataset.auteur.includes(q) || card.dataset.cat.includes(q);
            const matchDisp = !disp || card.dataset.dispo === disp;
            const matchCat  = !cat  || card.dataset.cat === cat;

            if (matchQ && matchDisp && matchCat) {
                card.style.display = '';
                visible++;
            } else {
                card.style.display = 'none';
            }
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
