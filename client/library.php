<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();
include "../includes/db.php";
include_once '../includes/languages.php';

$is_logged_in = isset($_SESSION['id_user']);
$id_user      = $is_logged_in ? (int)$_SESSION['id_user'] : 0;
$user_role    = $_SESSION['role'] ?? 'client';
$base         = "/MEMOIR";

$q_types = $conn->query("SELECT * FROM types_documents");

/* ── URL params ── */
$type_id = isset($_GET['type'])  ? (int)$_GET['type']  : 0;
$avail   = isset($_GET['avail']) ? $_GET['avail']       : 'all';

/* ── Full catalogue query (used for filtered view) ── */
$where = [];
if ($type_id > 0) $where[] = "d.id_type = $type_id";
switch ($avail) {
    case 'buy':    $where[] = "d.disponible_pour = 'achat'";   break;
    case 'borrow': $where[] = "d.disponible_pour = 'emprunt'"; break;
    case 'both':   $where[] = "d.disponible_pour = 'both'";    break;
}
$sql = "SELECT d.*, t.libelle_type FROM documents d
        LEFT JOIN types_documents t ON d.id_type = t.id_type"
     . ($where ? " WHERE " . implode(" AND ", $where) : "")
     . " ORDER BY d.titre ASC";
$result = mysqli_query($conn, $sql);
if (!$result) die("SQL Error: " . mysqli_error($conn));
$documents = mysqli_fetch_all($result, MYSQLI_ASSOC);

/* ── Cart count ── */
$cart_count = 0;
if ($is_logged_in && $user_role === 'client') {
    $rc = $conn->query("SELECT SUM(pi.quantite) as total FROM panier_item pi
                        JOIN panier p ON pi.id_panier = p.id_panier
                        WHERE p.id_user = $id_user");
    $cart_count = (int)($rc->fetch_assoc()['total'] ?? 0);
}

/* ══ HERO SLIDER — 20 most recent docs ══ */
$rs_slider = $conn->query("
    SELECT d.id_doc, d.titre, d.auteur, d.description_longue, d.image_doc, d.disponible_pour, t.libelle_type
    FROM documents d
    LEFT JOIN types_documents t ON d.id_type = t.id_type
    ORDER BY d.id_doc DESC LIMIT 20
");
$slider_items = [];
if ($rs_slider) {
    while ($row = $rs_slider->fetch_assoc()) {
        $imgPath = "../uploads/" . (int)$row['id_doc'] . ".jpg";
        if (!file_exists($imgPath)) {
            $imgPath = !empty($row['image_doc']) ? "../uploads/" . $row['image_doc'] : "../uploads/default.jpg";
        }
        $row['_img'] = $imgPath;
        $slider_items[] = $row;
    }
}

/* ══ CATALOGUE BY CATEGORY — 6 docs per type ══ */
$q_types->data_seek(0);
$all_types = $q_types->fetch_all(MYSQLI_ASSOC);

$sections = [];
foreach ($all_types as $t) {
    $tid  = (int)$t['id_type'];
    $sql2 = "SELECT d.*, t2.libelle_type FROM documents d
             LEFT JOIN types_documents t2 ON d.id_type = t2.id_type
             WHERE d.id_type = $tid
             ORDER BY d.id_doc DESC LIMIT 6";
    $r2 = $conn->query($sql2);
    if (!$r2 || $r2->num_rows === 0) continue;
    $rows = $r2->fetch_all(MYSQLI_ASSOC);
    $rc2  = $conn->query("SELECT COUNT(*) as n FROM documents WHERE id_type = $tid");
    $total = (int)($rc2->fetch_assoc()['n'] ?? 0);
    $sections[] = ['id' => $tid, 'label' => $t['libelle_type'], 'docs' => $rows, 'total' => $total];
}

function resolveImg($d) {
    $imgPath = "../uploads/" . (int)$d['id_doc'] . ".jpg";
    if (!file_exists($imgPath)) {
        $imgPath = !empty($d['image_doc']) ? "../uploads/" . $d['image_doc'] : "../uploads/default.jpg";
    }
    return $imgPath;
}
?>
<?php include '../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="<?= $lang ?? 'fr' ?>" dir="<?= ($lang ?? 'fr') == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AuraLib · Catalogue</title>
<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600;1,700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<style>
/* ══════════════════════════════════════════════
   TOKENS
══════════════════════════════════════════════ */
:root {
    --gold:         #C4A46B;
    --gold2:        #D4B47B;
    --gold-deep:    #A8884E;
    --gold-faint:   rgba(196,164,107,.09);
    --gold-border:  rgba(196,164,107,.28);
    --brown:        #7A5C3A;
    --brown-faint:  rgba(122,92,58,.09);
    --brown-border: rgba(122,92,58,.28);
    --amber:        #B8832A;
    --nav-bg:       #1A1008;
    --hero-bg:      #241808;
    --page-bg:      #F2EDE3;
    --page-bg2:     #E8E0D0;
    --page-white:   #FDFAF5;
    --page-text:    #2A1F14;
    --page-muted:   #9A8C7E;
    --page-border:  #D8CFC0;
    --danger:       #C0392B;
    --font-serif:   'EB Garamond', Georgia, serif;
    --font-ui:      'Plus Jakarta Sans', sans-serif;
    --nav-h:        62px;
    --radius:       14px;
    --shadow-sm:    0 3px 10px rgba(42,31,20,.08);
    --shadow-md:    0 8px 28px rgba(42,31,20,.11);
    --shadow-lg:    0 20px 55px rgba(42,31,20,.15);
    --shadow-gold:  0 6px 20px rgba(196,164,107,.22);
    --tr:           .25s cubic-bezier(.4,0,.2,1);
}
html.dark {
    --page-bg:      #100C07;
    --page-bg2:     #1A1308;
    --page-white:   #1E1610;
    --page-text:    #EDE5D4;
    --page-muted:   #9A8C7E;
    --page-border:  #3A2E1E;
    --shadow-sm:    0 3px 10px rgba(0,0,0,.3);
    --shadow-md:    0 8px 28px rgba(0,0,0,.4);
    --shadow-lg:    0 20px 55px rgba(0,0,0,.55);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: var(--font-ui);
    background: var(--page-bg);
    color: var(--page-text);
    padding-top: var(--nav-h);
    transition: background .35s, color .35s;
}
@keyframes fadeUp {
    from { opacity:0; transform:translateY(18px); }
    to   { opacity:1; transform:translateY(0); }
}
@keyframes cardIn {
    from { opacity:0; transform:translateY(14px); }
    to   { opacity:1; transform:translateY(0); }
}

/* ══════════════════════════════════════════════
   HERO SLIDER
══════════════════════════════════════════════ */
.hero-slider-wrapper {
    position: relative; width: 100%; height: 600px;
    overflow: hidden; background: #0D0905;
}
@media (max-width: 900px) { .hero-slider-wrapper { height: 480px; } }
@media (max-width: 600px) { .hero-slider-wrapper { height: 380px; } }

.hs-slide { position: relative; width: 100%; height: 100%; overflow: hidden; }
.hs-slide-bg {
    position: absolute; inset: 0; width: 100%; height: 100%;
    object-fit: cover; object-position: center top;
    transform: scale(1.06);
    transition: transform 9s cubic-bezier(.25,0,.25,1);
    filter: brightness(.48) saturate(.75);
}
.swiper-slide-active .hs-slide-bg { transform: scale(1.0); }

.hs-slide::before {
    content: ''; position: absolute; inset: 0;
    background:
        linear-gradient(90deg, rgba(10,6,2,.96) 0%, rgba(10,6,2,.5) 45%, transparent 72%),
        linear-gradient(0deg, rgba(10,6,2,.98) 0%, rgba(10,6,2,.6) 28%, transparent 55%);
    z-index: 1; pointer-events: none;
}
.hs-slide::after {
    content: ''; position: absolute; bottom: 0; left: 0; right: 0;
    height: 20px;
    background: linear-gradient(0deg, var(--page-bg) 0%, transparent 100%);
    z-index: 2; pointer-events: none;
}
html.dark .hs-slide::after {
    background: linear-gradient(0deg, var(--page-bg) 0%, transparent 100%);
}

.hs-content {
    position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    padding: 52px 7% 80px;
    z-index: 3;
    display: flex; flex-direction: column; align-items: flex-start;
    justify-content: flex-start;
    max-width: 680px;
}
.hs-badge {
    display: inline-flex; align-items: center; gap: 7px;
    font-size: 9px; font-weight: 700; letter-spacing: 3.5px;
    text-transform: uppercase; color: var(--gold);
    background: rgba(196,164,107,.12); border: 1px solid rgba(196,164,107,.3);
    padding: 5px 13px; border-radius: 50px; margin-bottom: 18px;
    backdrop-filter: blur(10px);
    opacity: 0; transform: translateY(12px);
    transition: opacity .5s .1s ease, transform .5s .1s ease;
}
.swiper-slide-active .hs-badge,
.swiper-slide-active .hs-title,
.swiper-slide-active .hs-btn-row { opacity: 1; transform: translateY(0); }

.hs-title {
    font-family: var(--font-serif);
    font-size: clamp(32px, 5.5vw, 62px); font-weight: 700;
    color: #FDFAF5; line-height: 1.08; margin-bottom: 28px;
    text-shadow: 0 6px 32px rgba(0,0,0,.7);
    opacity: 0; transform: translateY(18px);
    transition: opacity .6s .22s ease, transform .6s .22s ease;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}
.hs-btn-row {
    display: flex; gap: 11px;
    opacity: 0; transform: translateY(12px);
    transition: opacity .5s .38s ease, transform .5s .38s ease;
}
.hs-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 13px 28px; border-radius: 50px;
    font-family: var(--font-ui); font-size: 12px; font-weight: 700;
    text-decoration: none; letter-spacing: .3px;
    transition: all var(--tr); cursor: pointer; border: none;
}
.hs-btn-details {
    background: rgba(253,250,245,.1);
    backdrop-filter: blur(16px) saturate(1.4);
    -webkit-backdrop-filter: blur(16px) saturate(1.4);
    border: 1.5px solid rgba(253,250,245,.22);
    color: rgba(253,250,245,.9);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.1), 0 8px 24px rgba(0,0,0,.25);
}
.hs-btn-details:hover {
    background: rgba(253,250,245,.18);
    border-color: rgba(196,164,107,.5);
    color: var(--gold2);
    transform: translateY(-2px);
}
.hs-btn-borrow {
    background: rgba(122,92,58,.25); backdrop-filter: blur(12px);
    border: 1.5px solid rgba(122,92,58,.4); color: #F5EDD8;
}
.hs-btn-borrow:hover { background: rgba(122,92,58,.45); transform: translateY(-2px); }

.hs-progress, .hs-counter { display: none !important; }

.swiper-pagination-bullets.hs-pagination {
    position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%);
    z-index: 20; display: flex; gap: 8px;
}
.hs-pagination .swiper-pagination-bullet {
    width: 8px; height: 8px;
    background: rgba(196,164,107,.3); opacity: 1;
    border-radius: 50%; transition: all 0.3s ease; cursor: pointer;
}
.hs-pagination .swiper-pagination-bullet-active {
    background: var(--gold); width: 24px; border-radius: 4px;
}

.hs-thumbs {
    position: absolute; right: 28px; top: 50%; transform: translateY(-50%);
    z-index: 10; display: flex; flex-direction: column; gap: 10px;
}
.hs-thumb {
    width: 52px; height: 70px; border-radius: 7px; overflow: hidden;
    cursor: pointer; opacity: .38; border: 1.5px solid transparent;
    transition: opacity .3s, border-color .3s, transform .3s; flex-shrink: 0;
}
.hs-thumb img { width: 100%; height: 100%; object-fit: cover; }
.hs-thumb.active-thumb { opacity:1; border-color:var(--gold); transform:scale(1.08); box-shadow:0 4px 14px rgba(196,164,107,.3); }
.hs-thumb:hover:not(.active-thumb) { opacity: .65; }
.hs-arrow {
    position: absolute; top: 50%; transform: translateY(-50%);
    z-index: 10; width: 40px; height: 40px; border-radius: 50%;
    background: rgba(196,164,107,.12); backdrop-filter: blur(10px);
    border: 1px solid rgba(196,164,107,.22); color: rgba(196,164,107,.7);
    font-size: 13px; display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all var(--tr);
}
.hs-arrow:hover { background: rgba(196,164,107,.25); color: var(--gold); border-color: var(--gold-border); }
.hs-arrow-prev { left: 24px; }
.hs-arrow-next { right: 72px; }
@media (max-width: 768px) {
    .hs-thumbs { display: none; }
    .hs-content { padding: 40px 5% 60px; }
    .hs-arrow-prev { left: 14px; }
    .hs-arrow-next { left: 60px; }
}

/* ══════════════════════════════════════════════
   STICKY SEARCH BAR
══════════════════════════════════════════════ */
.search-bar-sticky {
    position: sticky; top: var(--nav-h); z-index: 90;
    background: var(--page-bg);
    border-bottom: 1px solid var(--page-border);
    padding: 14px 5%;
    display: flex; flex-direction: column; gap: 10px;
    transition: background .35s, box-shadow .3s;
}
.search-bar-sticky.scrolled { box-shadow: 0 4px 20px rgba(42,31,20,.1); }
html.dark .search-bar-sticky.scrolled { box-shadow: 0 4px 20px rgba(0,0,0,.35); }

.search-top-row { display: flex; align-items: center; gap: 12px; }

.search-input-wrap {
    flex: 1; max-width: 600px;
    display: flex; align-items: center;
    background: var(--page-white);
    border: 1.5px solid var(--page-border);
    border-radius: 50px;
    padding: 0 6px 0 42px;
    position: relative;
    transition: border-color var(--tr), box-shadow var(--tr);
}
.search-input-wrap:focus-within {
    border-color: var(--gold-border);
    box-shadow: 0 0 0 3px rgba(196,164,107,.1);
}
.search-input-wrap i.search-icon {
    position: absolute; left: 16px; top: 50%;
    transform: translateY(-50%);
    color: var(--page-muted); font-size: 12px; pointer-events: none;
}
#search {
    flex: 1; background: transparent; border: none; outline: none;
    font-family: var(--font-ui); font-size: 13px; color: var(--page-text);
    padding: 11px 0; min-width: 0;
}
#search::placeholder { color: var(--page-muted); opacity: .65; }
.search-btn-clear {
    background: transparent; border: none; cursor: pointer;
    color: var(--page-muted); font-size: 13px; padding: 4px 8px;
    transition: color var(--tr); display: none;
}
.search-btn-clear.visible { display: flex; align-items: center; }
.search-btn-clear:hover { color: var(--danger); }

.search-btn {
    background: var(--gold); border: none; cursor: pointer;
    border-radius: 50px; padding: 8px 20px;
    font-family: var(--font-ui); font-size: 11px; font-weight: 700;
    color: #2C1F0E; letter-spacing: .3px;
    transition: background var(--tr); white-space: nowrap; flex-shrink: 0;
}
.search-btn:hover { background: var(--gold2); }

/* Live Suggestions */
.suggestions-wrap {
    position: absolute; top: calc(100% + 4px); left: 0; right: 0;
    background: var(--page-white);
    border: 1.5px solid var(--gold-border);
    border-radius: 16px;
    box-shadow: 0 16px 48px rgba(42,31,20,.16);
    overflow: hidden; z-index: 200;
    display: none; animation: fadeUp .18s ease both;
}
.suggestions-wrap.visible { display: block; }
.suggestion-item {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 16px; cursor: pointer;
    transition: background var(--tr);
    border-bottom: 1px solid var(--page-border);
    text-decoration: none; color: inherit;
}
.suggestion-item:last-child { border-bottom: none; }
.suggestion-item:hover { background: var(--gold-faint); }
.sug-icon {
    width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0;
    background: var(--page-bg2); display: flex; align-items: center;
    justify-content: center; font-size: 12px; color: var(--gold);
}
.sug-info { flex: 1; min-width: 0; }
.sug-title {
    font-family: var(--font-serif); font-size: 14px; font-weight: 600;
    color: var(--page-text); white-space: nowrap; overflow: hidden;
    text-overflow: ellipsis; display: block;
}
.sug-meta { font-size: 10px; color: var(--page-muted); margin-top: 1px; }
.sug-type {
    font-size: 9px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;
    color: var(--gold); background: var(--gold-faint);
    border: 1px solid var(--gold-border); border-radius: 20px; padding: 2px 8px;
    white-space: nowrap; flex-shrink: 0;
}
.sug-all-btn {
    display: flex; align-items: center; justify-content: center; gap: 7px;
    padding: 10px; font-size: 11px; font-weight: 700; color: var(--gold-deep);
    background: var(--gold-faint); cursor: pointer; border: none; width: 100%;
    font-family: var(--font-ui); letter-spacing: .2px;
    transition: background var(--tr);
}
.sug-all-btn:hover { background: rgba(196,164,107,.15); }

/* Avail Filter Pills */
.search-filters-row {
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
}
.filter-label-sm {
    font-size: 9px; font-weight: 700; letter-spacing: 2.5px;
    text-transform: uppercase; color: var(--page-muted);
    flex-shrink: 0; padding-right: 4px;
}
.avail-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 16px; border-radius: 50px;
    font-family: var(--font-ui); font-size: 11px; font-weight: 600;
    border: 1.5px solid var(--page-border);
    background: var(--page-white); color: var(--page-muted);
    cursor: pointer; transition: all var(--tr); white-space: nowrap;
    user-select: none;
}
.avail-pill:hover { border-color: var(--gold); color: var(--gold-deep); background: var(--gold-faint); }
.avail-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.ap-all  .avail-dot { background: var(--page-muted); }
.ap-all.active  { background: var(--gold); border-color: var(--gold); color: #2C1F0E; font-weight: 700; box-shadow: var(--shadow-gold); }
.ap-buy  .avail-dot { background: var(--gold); }
.ap-buy.active  { background: var(--gold); border-color: var(--gold); color: #2C1F0E; font-weight: 700; box-shadow: var(--shadow-gold); }
.ap-borrow .avail-dot { background: var(--brown); }
.ap-borrow:hover  { border-color: var(--brown-border); color: var(--brown); background: var(--brown-faint); }
.ap-borrow.active { background: var(--brown); border-color: var(--brown); color: #fff; font-weight: 700; }
.ap-both .avail-dot { background: linear-gradient(135deg, var(--gold) 50%, var(--brown) 50%); }
.ap-both.active { background: linear-gradient(110deg, var(--gold) 0%, var(--brown) 100%); border-color: transparent; color: #fff; font-weight: 700; }

.search-hint-tags { display: flex; gap: 6px; flex-wrap: wrap; }
.hint-tag {
    font-size: 10px; color: var(--page-muted);
    padding: 5px 12px; border: 1px solid var(--page-border);
    border-radius: 50px; cursor: pointer; white-space: nowrap;
    transition: color var(--tr), border-color var(--tr), background var(--tr);
}
.hint-tag:hover { color: var(--gold); border-color: var(--gold-border); background: var(--gold-faint); }
@media (max-width: 700px) { .search-hint-tags { display: none; } }

/* ══════════════════════════════════════════════
   SEARCH RESULTS OVERLAY
══════════════════════════════════════════════ */
#search-overlay { display: none; padding: 0 5% 60px; max-width: 1380px; margin: 0 auto; }
#search-overlay.visible { display: block; }
.search-overlay-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 32px 0 20px;
    border-bottom: 1px solid var(--page-border);
    margin-bottom: 28px;
}
.search-overlay-header h2 { font-family: var(--font-serif); font-size: 22px; font-weight: 600; }
#search-count-label { font-size: 12px; color: var(--page-muted); display: block; margin-top: 3px; }
#search-count-label strong { color: var(--gold); font-size: 18px; font-family: var(--font-serif); }
.search-clear-btn {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 11px; font-weight: 600; color: var(--page-muted);
    cursor: pointer; padding: 6px 14px; border-radius: 50px;
    border: 1px solid var(--page-border); background: transparent;
    transition: all var(--tr);
}
.search-clear-btn:hover { color: var(--danger); border-color: rgba(192,57,43,.3); }

/* ══════════════════════════════════════════════
   CATALOGUE SECTIONS
══════════════════════════════════════════════ */
#catalogue-sections { padding: 0 5% 80px; max-width: 1380px; margin: 0 auto; }

.cat-section {
    padding-top: 48px;
    border-top: 1px solid var(--page-border); margin-top: 0;
}
.cat-section:first-child { border-top: none; padding-top: 40px; }
.cat-section-head {
    display: flex; align-items: flex-end;
    justify-content: space-between; gap: 12px; margin-bottom: 24px;
}
.cat-section-title { display: flex; align-items: center; gap: 12px; }
.cat-section-title h2 { font-family: var(--font-serif); font-size: 26px; font-weight: 600; line-height: 1; }
.cat-section-badge {
    font-size: 10px; font-weight: 700; letter-spacing: 1.5px;
    text-transform: uppercase; color: var(--page-muted);
    background: var(--page-bg2); border: 1px solid var(--page-border);
    padding: 3px 10px; border-radius: 50px;
}

/* ── "Voir tout" button — AJAX version ── */
.cat-see-all {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 11px; font-weight: 700; letter-spacing: .3px;
    color: var(--gold-deep); text-decoration: none;
    padding: 8px 18px; border-radius: 50px;
    border: 1.5px solid var(--gold-border);
    background: var(--gold-faint);
    transition: all var(--tr); white-space: nowrap; flex-shrink: 0;
    cursor: pointer; font-family: var(--font-ui);
}
html.dark .cat-see-all { color: var(--gold); }
.cat-see-all:hover { background: var(--gold); color: #1A0E05; border-color: var(--gold); box-shadow: var(--shadow-gold); transform: translateY(-1px); }
.cat-see-all i { font-size: 9px; transition: transform var(--tr); }
.cat-see-all:hover i { transform: translateX(3px); }
.cat-see-all:disabled { opacity: .55; cursor: default; transform: none; box-shadow: none; }

/* Spinner dans le bouton */
@keyframes spin { to { transform: rotate(360deg); } }
.fa-spin { animation: spin .7s linear infinite; }

.cat-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}
@media (max-width: 900px) { .cat-row { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 14px; } }

/* ══════════════════════════════════════════════
   BOOK CARDS
══════════════════════════════════════════════ */
.book-card {
    background: var(--page-white);
    border-radius: var(--radius);
    border: 1px solid var(--page-border);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    display: flex; flex-direction: column;
    transition: transform var(--tr), box-shadow var(--tr), border-color var(--tr);
    animation: cardIn .4s ease both;
}
.book-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-lg); border-color: var(--gold-border); }
.book-card:nth-child(2) { animation-delay:.05s; }
.book-card:nth-child(3) { animation-delay:.10s; }
.book-card:nth-child(4) { animation-delay:.15s; }
.book-card:nth-child(5) { animation-delay:.20s; }
.book-card:nth-child(6) { animation-delay:.24s; }

.card-cover {
    position: relative;
    overflow: hidden;
    background: var(--page-bg2); display: block; text-decoration: none;
    flex-shrink: 0; height: 240px;
}
.card-cover img {
    position: absolute; inset: 0;
    width: 100%; height: 100%; object-fit: cover; display: block;
    transition: transform .6s cubic-bezier(.4,0,.2,1);
}
.book-card:hover .card-cover img { transform: scale(1.06); }

.avail-ribbon {
    position: absolute; top: 10px; left: 10px;
    display: flex; flex-direction: column; gap: 5px; z-index: 2;
}
.avail-tag {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px; border-radius: 20px;
    font-size: 9px; font-weight: 700; letter-spacing: .8px;
    text-transform: uppercase; backdrop-filter: blur(10px);
    border: 1px solid transparent;
}
.tag-buy    { background: rgba(196,164,107,.9); color: #2C1F0E; border-color: rgba(196,164,107,.4); }
.tag-borrow { background: rgba(122,92,58,.88);  color: #F5EDD8; border-color: rgba(122,92,58,.4); }
.wish-btn {
    position: absolute; bottom: 10px; right: 10px; z-index: 2;
    width: 32px; height: 32px; border-radius: 50%;
    background: rgba(44,31,14,.6); backdrop-filter: blur(8px);
    border: 1px solid rgba(196,164,107,.18); color: rgba(196,164,107,.45);
    font-size: 13px; display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all var(--tr);
}
.wish-btn:hover { color: var(--gold); border-color: var(--gold-border); background: rgba(44,31,14,.85); }
.wish-btn.wishlisted { color: #ef4444; border-color: #fca5a5; }

.card-body { padding: 12px 13px 14px; flex: 1; display: flex; flex-direction: column; }
.card-title {
    font-family: var(--font-serif); font-size: 15px; font-weight: 600;
    color: var(--page-text); line-height: 1.3; margin-bottom: 3px;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}
.card-title a { text-decoration: none; color: inherit; }
.card-title a:hover { color: var(--gold); }
.card-author { font-size: 10px; color: var(--page-muted); margin-bottom: 10px; }
.card-author i { margin-right: 3px; font-size: 9px; }
.card-price-row { margin-bottom: 10px; }
.card-price { font-family: var(--font-serif); font-size: 18px; font-weight: 600; color: var(--amber); }
html.dark .card-price { color: var(--gold2); }
.price-unit { font-family: var(--font-ui); font-size: 10px; font-weight: 400; margin-left: 3px; }
.card-free { font-size: 11px; color: var(--page-muted); display: flex; align-items: center; gap: 5px; }
.card-free i { color: var(--gold); font-size: 10px; }
.card-divider { height: 1px; background: var(--page-border); margin-bottom: 10px; opacity: .6; }
.card-actions { display: flex; gap: 6px; margin-top: auto; }
.btn-card {
    flex: 1; display: flex; align-items: center; justify-content: center;
    gap: 6px; padding: 8px 6px; border-radius: 9px;
    font-family: var(--font-ui); font-size: 10px; font-weight: 700;
    text-decoration: none; border: none; cursor: pointer;
    transition: all var(--tr); line-height: 1; letter-spacing: .3px;
}
.btn-card i { font-size: 10px; }
.btn-borrow { background: var(--brown-faint); border: 1.5px solid var(--brown-border); color: var(--brown); }
.btn-borrow:hover { background: var(--brown); color: #F5EDD8; border-color: var(--brown); }
.btn-buy    { background: var(--gold-faint); border: 1.5px solid var(--gold-border); color: var(--gold-deep); }
html.dark .btn-buy { color: var(--gold); }
.btn-buy:hover { background: var(--gold); color: #2C1F0E; border-color: var(--gold); box-shadow: var(--shadow-gold); }
.btn-card.full { flex: 1 1 100%; }

.btn-both {
    background: linear-gradient(110deg, var(--gold-faint) 0%, var(--brown-faint) 100%);
    border: 1.5px solid var(--gold-border); color: var(--gold-deep);
}
html.dark .btn-both { color: var(--gold); }
.btn-both:hover { background: linear-gradient(110deg, var(--gold) 0%, var(--brown) 100%); color: #fff; border-color: transparent; }

.btn-both-wrap { position: relative; flex: 1; }
.both-menu {
    position: absolute; bottom: calc(100% + 8px); left: 0; right: 0;
    background: var(--page-white);
    border: 1.5px solid var(--gold-border);
    border-radius: 12px;
    box-shadow: 0 12px 36px rgba(42,31,20,.2);
    overflow: hidden; z-index: 50;
    display: none; animation: fadeUp .18s ease both;
}
.both-menu.open { display: block; }
.both-opt {
    display: flex; align-items: center; gap: 10px; width: 100%;
    padding: 10px 14px; font-family: var(--font-ui); font-size: 11px; font-weight: 600;
    color: var(--page-text); text-decoration: none; background: transparent;
    border: none; cursor: pointer; transition: background var(--tr);
    border-bottom: 1px solid var(--page-border);
}
.both-opt:last-child { border-bottom: none; }
.both-opt:hover { background: var(--gold-faint); }
.both-opt i { color: var(--gold); font-size: 12px; }

.admin-actions { display: flex; gap: 7px; margin-top: auto; }
.btn-admin {
    flex: 1; display: flex; align-items: center; justify-content: center;
    gap: 6px; padding: 9px; border-radius: 9px;
    font-family: var(--font-ui); font-size: 11px; font-weight: 700;
    text-decoration: none; transition: all var(--tr);
}
.btn-edit   { background: var(--gold-faint); color: var(--gold); border: 1.5px solid var(--gold-border); }
.btn-edit:hover { background: rgba(196,164,107,.18); transform: translateY(-1px); }
.btn-delete { background: rgba(192,57,43,.08); color: var(--danger); border: 1.5px solid rgba(192,57,43,.2); }
.btn-delete:hover { background: rgba(192,57,43,.15); transform: translateY(-1px); }

.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px; transition: opacity .25s;
}
.books-grid.loading { opacity: .45; pointer-events: none; }

.empty-state { grid-column: 1/-1; text-align: center; padding: 70px 20px; }
.empty-icon  { font-size: 44px; color: var(--page-border); margin-bottom: 16px; }
.empty-state h3 { font-family: var(--font-serif); font-size: 22px; color: var(--page-muted); margin-bottom: 6px; }
.empty-state p  { font-size: 13px; color: var(--page-muted); }

@media (max-width: 600px) {
    .books-grid, .cat-row { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
    .card-cover { height: 190px; }
}
</style>
</head>
<body>

<!-- ══════════════════════════════════════════
     HERO SLIDER
══════════════════════════════════════════ -->
<?php if (!empty($slider_items)): ?>
<div class="hero-slider-wrapper">
    <div class="swiper hs-swiper" style="width:100%;height:100%;">
        <div class="swiper-wrapper">
            <?php foreach ($slider_items as $idx => $s):
                $detail_url  = "/MEMOIR/client/doc_details.php?id=" . (int)$s['id_doc'];
                $safe_title  = htmlspecialchars($s['titre']);
                $safe_type   = htmlspecialchars($s['libelle_type'] ?? 'Document');
                $safe_img    = htmlspecialchars($s['_img']);
                $dp          = $s['disponible_pour'] ?? 'both';
            ?>
            <div class="swiper-slide hs-slide">
                <img class="hs-slide-bg" src="<?= $safe_img ?>" alt="<?= $safe_title ?>"
                     onerror="this.src='../uploads/default.jpg'">
                <div class="hs-content">
                    <div class="hs-badge"><i class="fa-solid fa-bookmark"></i> <?= $safe_type ?></div>
                    <h2 class="hs-title"><?= $safe_title ?></h2>
                    <div class="hs-btn-row">
                        <a href="<?= $detail_url ?>" class="hs-btn hs-btn-details">
                            <i class="fa-solid fa-circle-info" style="font-size:10px"></i> Détails
                        </a>
                        <?php if ($is_logged_in && $user_role==='client' && in_array($dp,['emprunt','both'])): ?>
                        <a href="/MEMOIR/emprunts/emprunt.php?id_doc=<?= (int)$s['id_doc'] ?>" class="hs-btn hs-btn-borrow">
                            <i class="fa-regular fa-clock" style="font-size:10px"></i> Emprunter
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <button class="hs-arrow hs-arrow-prev" id="hsPrev"><i class="fa-solid fa-chevron-left"></i></button>
    <button class="hs-arrow hs-arrow-next" id="hsNext"><i class="fa-solid fa-chevron-right"></i></button>
    <div class="hs-thumbs" id="hsThumbs">
        <?php foreach ($slider_items as $idx => $s): ?>
        <div class="hs-thumb <?= $idx===0?'active-thumb':'' ?>" onclick="hsGoTo(<?= $idx ?>)">
            <img src="<?= htmlspecialchars($s['_img']) ?>" alt="" onerror="this.src='../uploads/default.jpg'">
        </div>
        <?php endforeach; ?>
    </div>
    <div class="swiper-pagination hs-pagination"></div>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════
     STICKY SEARCH BAR
══════════════════════════════════════════ -->
<div class="search-bar-sticky" id="searchBar">
    <div class="search-top-row">
        <div class="search-input-wrap" id="searchWrap" style="position:relative;">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="search"
                   placeholder="Titre, auteur, ISBN, prix, année, éditeur…"
                   autocomplete="off" spellcheck="false">
            <button class="search-btn-clear" id="clearBtn" onclick="clearSearch()" title="Effacer">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="suggestions-wrap" id="suggestionsBox"></div>
        </div>
        <button class="search-btn" onclick="triggerSearch()">
            <i class="fa-solid fa-magnifying-glass" style="font-size:10px"></i> Rechercher
        </button>
        <div class="search-hint-tags">
            <span class="hint-tag" onclick="setSearch('Thèse')">Thèse</span>
            <span class="hint-tag" onclick="setSearch('Droit')">Droit</span>
            <span class="hint-tag" onclick="setSearch('Intelligence artificielle')">IA</span>
            <span class="hint-tag" onclick="setSearch('Finance')">Finance</span>
            <span class="hint-tag" onclick="setSearch('Revue')">Revue</span>
        </div>
    </div>
    <div class="search-filters-row">
        <span class="filter-label-sm">Filtrer :</span>
        <button class="avail-pill ap-all active" data-avail="all" onclick="setAvail('all', this)">
            <span class="avail-dot"></span> Tout
        </button>
        <button class="avail-pill ap-buy" data-avail="buy" onclick="setAvail('buy', this)">
            <span class="avail-dot"></span> Achat
        </button>
        <button class="avail-pill ap-borrow" data-avail="borrow" onclick="setAvail('borrow', this)">
            <span class="avail-dot"></span> Emprunt
        </button>
        <button class="avail-pill ap-both" data-avail="both" onclick="setAvail('both', this)">
            <span class="avail-dot"></span> Achat &amp; Emprunt
        </button>
    </div>
</div>

<!-- ══════════════════════════════════════════
     SEARCH RESULTS OVERLAY
══════════════════════════════════════════ -->
<div id="search-overlay">
    <div class="search-overlay-header">
        <div>
            <h2>Résultats de recherche</h2>
            <span id="search-count-label">0 document(s) trouvé(s)</span>
        </div>
        <button class="search-clear-btn" onclick="clearSearch()">
            <i class="fa-solid fa-xmark"></i> Effacer
        </button>
    </div>
    <div class="books-grid" id="resultat"></div>
</div>

<!-- ══════════════════════════════════════════
     CATALOGUE PAR CATÉGORIE
══════════════════════════════════════════ -->
<div id="catalogue-sections">

<?php if (!empty($sections)): ?>
<?php foreach ($sections as $sec): ?>
<div class="cat-section">
    <div class="cat-section-head">
        <div class="cat-section-title">
            <h2><?= htmlspecialchars($sec['label']) ?></h2>
            <span class="cat-section-badge"><?= $sec['total'] ?> doc<?= $sec['total']>1?'s':'' ?></span>
        </div>
        <?php if ($sec['total'] > 6): ?>
        <button
            class="cat-see-all"
            id="btn-voir-<?= $sec['id'] ?>"
            onclick="loadMore(this, <?= $sec['id'] ?>, <?= $sec['total'] ?>)"
            data-loaded="6"
            data-type="<?= $sec['id'] ?>">
            Voir tout <i class="fa-solid fa-arrow-right"></i>
        </button>
        <?php endif; ?>
    </div>
    <div class="cat-row" id="row-<?= $sec['id'] ?>">
        <?php foreach ($sec['docs'] as $d):
            $dp         = $d['disponible_pour'] ?? 'both';
            $can_buy    = in_array($dp, ['achat','both']);
            $can_borrow = in_array($dp, ['emprunt','both']);
            $is_both    = ($dp === 'both');
            $imgPath    = resolveImg($d);
            $detail_url = "/MEMOIR/client/doc_details.php?id=" . (int)$d['id_doc'];
        ?>
        <div class="book-card">
            <a href="<?= $detail_url ?>" class="card-cover">
                <img src="<?= htmlspecialchars($imgPath) ?>"
                     alt="<?= htmlspecialchars($d['titre']) ?>"
                     loading="lazy"
                     onerror="this.src='../uploads/default.jpg'">
                <div class="avail-ribbon">
                    <?php if ($can_buy): ?><span class="avail-tag tag-buy"><i class="fa-solid fa-cart-shopping" style="font-size:8px"></i> Achat</span><?php endif; ?>
                    <?php if ($can_borrow): ?><span class="avail-tag tag-borrow"><i class="fa-regular fa-clock" style="font-size:8px"></i> Emprunt</span><?php endif; ?>
                </div>
                <?php if ($is_logged_in && $user_role==='client'): ?>
                <button class="wish-btn" onclick="event.preventDefault();toggleWishlist(this,<?= (int)$d['id_doc'] ?>)" title="Favoris">
                    <i class="fa-regular fa-heart"></i>
                </button>
                <?php endif; ?>
            </a>
            <div class="card-body">
                <h3 class="card-title"><a href="<?= $detail_url ?>"><?= htmlspecialchars($d['titre']) ?></a></h3>
                <p class="card-author"><i class="fa-solid fa-user-pen"></i> <?= htmlspecialchars($d['auteur'] ?? '') ?></p>
                <div class="card-price-row">
                    <?php if ($can_buy && (float)$d['prix'] > 0): ?>
                        <span class="card-price"><?= number_format((float)$d['prix'],0,',',' ') ?><span class="price-unit">DA</span></span>
                    <?php elseif ($can_borrow && !$can_buy): ?>
                        <span class="card-free"><i class="fa-solid fa-book-open"></i> Emprunt gratuit</span>
                    <?php else: ?>
                        <span class="card-free"><i class="fa-solid fa-lock-open"></i> Gratuit</span>
                    <?php endif; ?>
                </div>
                <div class="card-divider"></div>

                <?php if ($user_role==='client'): ?>
                <div class="card-actions">
                    <?php if ($is_both): ?>
                        <div class="btn-both-wrap">
                            <button class="btn-card btn-both full"
                                    onclick="toggleBothMenu(this, <?= (int)$d['id_doc'] ?>)">
                                <i class="fa-solid fa-plus"></i> Choisir
                            </button>
                            <div class="both-menu" id="both-menu-<?= (int)$d['id_doc'] ?>">
                                <a href="../emprunts/emprunt.php?id_doc=<?= (int)$d['id_doc'] ?>" class="both-opt">
                                    <i class="fa-regular fa-clock"></i><span>Emprunter</span>
                                </a>
                                <div class="both-opt" style="padding:0;">
                                    <form action="../cart/add_to_cart.php" method="POST" style="width:100%;">
                                        <input type="hidden" name="id_doc" value="<?= (int)$d['id_doc'] ?>">
                                        <button type="submit" style="all:unset;display:flex;align-items:center;gap:10px;width:100%;padding:10px 14px;font-family:var(--font-ui);font-size:11px;font-weight:600;color:var(--page-text);cursor:pointer;">
                                            <i class="fa-solid fa-cart-plus" style="color:var(--gold);font-size:12px;"></i><span>Acheter</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php if ($can_borrow): ?>
                        <a href="../emprunts/emprunt.php?id_doc=<?= (int)$d['id_doc'] ?>"
                           class="btn-card btn-borrow <?= !$can_buy?'full':'' ?>">
                            <i class="fa-regular fa-clock"></i> Emprunter
                        </a>
                        <?php endif; ?>
                        <?php if ($can_buy): ?>
                        <form action="../cart/add_to_cart.php" method="POST" style="flex:1;display:flex">
                            <input type="hidden" name="id_doc" value="<?= (int)$d['id_doc'] ?>">
                            <button type="submit" class="btn-card btn-buy <?= !$can_borrow?'full':'' ?>">
                                <i class="fa-solid fa-cart-plus"></i> Acheter
                            </button>
                        </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php elseif (!$is_logged_in): ?>
                <a href="/MEMOIR/auth/login.php" class="btn-card btn-borrow full">
                    <i class="fa-solid fa-right-to-bracket"></i> Connexion requise
                </a>
                <?php elseif ($user_role==='admin'): ?>
                <div class="admin-actions">
                    <a href="/MEMOIR/admin/modifier_document.php?id=<?= (int)$d['id_doc'] ?>"
                       class="btn-admin btn-edit" title="Modifier">
                        <i class="fa-solid fa-pen"></i> Modifier
                    </a>
                    <a href="/MEMOIR/admin/delete_doc.php?id=<?= (int)$d['id_doc'] ?>"
                       onclick="return confirm('Supprimer ce document ?')"
                       class="btn-admin btn-delete" title="Supprimer">
                        <i class="fa-solid fa-trash"></i> Supprimer
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="empty-state" style="padding:80px 0">
    <div class="empty-icon"><i class="fa-regular fa-folder-open"></i></div>
    <h3>Aucun document dans le catalogue</h3>
    <p>Ajoutez des documents pour les voir apparaître ici.</p>
</div>
<?php endif; ?>

</div><!-- /catalogue-sections -->

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
/* ══ HERO SLIDER ══ */
(function () {
    const DELAY  = 3000;
    const thumbs = document.querySelectorAll('.hs-thumb');

    function updateThumbs(idx) {
        thumbs.forEach((t,i) => t.classList.toggle('active-thumb', i===idx));
    }

    const swiper = new Swiper('.hs-swiper', {
        loop: true,
        speed: 1000,
        effect: 'fade',
        fadeEffect: { crossFade: true },
        autoplay: { delay: DELAY, disableOnInteraction: false, pauseOnMouseEnter: true },
        navigation: { nextEl: '#hsNext', prevEl: '#hsPrev' },
        pagination: { el: '.hs-pagination', clickable: true },
        on: {
            slideChangeTransitionEnd() { updateThumbs(this.realIndex); }
        }
    });

    window.hsGoTo = function(idx) {
        swiper.slideToLoop(idx, 900);
        updateThumbs(idx);
    };
})();

/* ══ STICKY SEARCH SHADOW ══ */
const searchBarEl = document.getElementById('searchBar');
window.addEventListener('scroll', () => {
    searchBarEl.classList.toggle('scrolled', window.scrollY > 30);
}, { passive: true });

/* ══════════════════════════════════════════
   SMART GLOBAL SEARCH ENGINE
══════════════════════════════════════════ */
const searchEl   = document.getElementById('search');
const overlayEl  = document.getElementById('search-overlay');
const gridEl     = document.getElementById('resultat');
const countLbl   = document.getElementById('search-count-label');
const sectionsEl = document.getElementById('catalogue-sections');
const suggestBox = document.getElementById('suggestionsBox');
const clearBtnEl = document.getElementById('clearBtn');

let debounceTimer, suggestTimer;
let currentAvail = 'all';

function setAvail(val, btn) {
    currentAvail = val;
    document.querySelectorAll('.avail-pill').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    const q = searchEl.value.trim();
    if (q) fetchCards(q);
}

function setSearch(val) {
    searchEl.value = val;
    clearBtnEl.classList.add('visible');
    hideSuggestions();
    fetchCards(val);
    searchEl.focus();
}

function clearSearch() {
    searchEl.value = '';
    clearBtnEl.classList.remove('visible');
    hideSuggestions();
    overlayEl.classList.remove('visible');
    sectionsEl.style.display = '';
    searchEl.focus();
}

function triggerSearch() {
    const q = searchEl.value.trim();
    if (!q) { clearSearch(); return; }
    hideSuggestions();
    fetchCards(q);
}

function buildUrl(q, extra='') {
    return 'recherche_dcmnt.php?search=' + encodeURIComponent(q)
         + '&avail=' + encodeURIComponent(currentAvail)
         + '&type=0' + extra;
}

function fetchCards(q) {
    overlayEl.classList.add('visible');
    sectionsEl.style.display = 'none';
    gridEl.classList.add('loading');
    countLbl.textContent = 'Recherche en cours…';

    fetch(buildUrl(q))
        .then(r => r.text())
        .then(html => {
            gridEl.innerHTML = html;
            gridEl.classList.remove('loading');
            const n = gridEl.querySelectorAll('.book-card').length;
            countLbl.innerHTML = '<strong>' + n + '</strong> document' + (n!==1?'s':'') + ' trouvé' + (n!==1?'s':'');
        })
        .catch(() => {
            gridEl.classList.remove('loading');
            countLbl.textContent = 'Erreur de chargement';
        });
}

function fetchSuggestions(q) {
    fetch(buildUrl(q, '&mode=suggest'))
        .then(r => r.json())
        .then(items => {
            if (!items.length) { hideSuggestions(); return; }
            let html = '';
            items.forEach(item => {
                const meta = [item.auteur, item.annee].filter(Boolean).join(' · ');
                html += `<a class="suggestion-item" href="/MEMOIR/client/doc_details.php?id=${item.id}">
                    <div class="sug-icon"><i class="fa-solid fa-book"></i></div>
                    <div class="sug-info">
                        <span class="sug-title">${escHtml(item.titre)}</span>
                        ${meta ? `<span class="sug-meta">${escHtml(meta)}</span>` : ''}
                    </div>
                    ${item.type ? `<span class="sug-type">${escHtml(item.type)}</span>` : ''}
                </a>`;
            });
            html += `<button class="sug-all-btn" onclick="hideSuggestions();fetchCards('${escAttr(q)}')">
                <i class="fa-solid fa-magnifying-glass" style="font-size:10px"></i>
                Voir tous les résultats
            </button>`;
            suggestBox.innerHTML = html;
            suggestBox.classList.add('visible');
        })
        .catch(() => hideSuggestions());
}

function hideSuggestions() {
    suggestBox.classList.remove('visible');
    suggestBox.innerHTML = '';
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escAttr(s) { return String(s).replace(/'/g,"\\'"); }

searchEl.addEventListener('input', function () {
    clearTimeout(debounceTimer);
    clearTimeout(suggestTimer);
    const q = this.value.trim();
    clearBtnEl.classList.toggle('visible', q.length > 0);
    if (!q) { clearSearch(); return; }
    if (q.length >= 2) {
        suggestTimer = setTimeout(() => fetchSuggestions(q), 180);
    }
    debounceTimer = setTimeout(() => {
        hideSuggestions();
        fetchCards(q);
    }, 350);
});

searchEl.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        clearTimeout(debounceTimer);
        clearTimeout(suggestTimer);
        hideSuggestions();
        fetchCards(this.value.trim());
    }
    if (e.key === 'Escape') { clearSearch(); }
});

document.addEventListener('click', function(e) {
    if (!searchEl.contains(e.target) && !suggestBox.contains(e.target)) {
        hideSuggestions();
    }
});

/* ══════════════════════════════════════════
   LOAD MORE — AJAX "Voir tout / Voir moins"
══════════════════════════════════════════ */
async function loadMore(btn, typeId, total) {
    const row = document.getElementById('row-' + typeId);
    if (!row) return;

    /* ── Collapse : rجوع للـ 5 الأوائل ── */
    if (btn.dataset.mode === 'collapse') {
        const allCards = row.querySelectorAll('.book-card');
        allCards.forEach((card, i) => { if (i >= 5) card.remove(); });
        btn.dataset.loaded = '5';
        btn.dataset.mode   = '';
        btn.innerHTML = 'Voir tout <i class="fa-solid fa-arrow-right"></i>';
        row.scrollIntoView({ behavior: 'smooth', block: 'start' });
        return;
    }

    /* ── Load more ── */
    const loaded = parseInt(btn.dataset.loaded) || 5;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Chargement…';

    try {
        const res = await fetch(
            `get_more_docs.php?type=${typeId}&offset=${loaded}&avail=${encodeURIComponent(currentAvail)}`
        );
        if (!res.ok) throw new Error('Erreur réseau');
        const html = await res.text();

        const tmp = document.createElement('div');
        tmp.innerHTML = html;
        const newCards = tmp.querySelectorAll('.book-card');

        newCards.forEach((card, i) => {
            card.style.animationDelay = (i * 0.05) + 's';
            row.appendChild(card);
        });

        const newLoaded = loaded + newCards.length;
        btn.dataset.loaded = newLoaded;
        btn.disabled = false;

        if (newLoaded >= total || newCards.length === 0) {
            btn.dataset.mode = 'collapse';
            btn.innerHTML = 'Voir moins <i class="fa-solid fa-arrow-up"></i>';
        } else {
            const remaining = total - newLoaded;
            btn.innerHTML = `Voir plus (${remaining}) <i class="fa-solid fa-arrow-right"></i>`;
        }
    } catch (err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Réessayer';
        console.error('loadMore error:', err);
    }
}

/* ══ BOTH MENU TOGGLE ══ */
function toggleBothMenu(btn, id) {
    const menu = document.getElementById('both-menu-' + id);
    if (!menu) return;
    document.querySelectorAll('.both-menu.open').forEach(m => {
        if (m !== menu) m.classList.remove('open');
    });
    menu.classList.toggle('open');
    if (menu.classList.contains('open')) {
        setTimeout(() => {
            document.addEventListener('click', function closeMenu(e) {
                if (!menu.contains(e.target) && e.target !== btn) {
                    menu.classList.remove('open');
                    document.removeEventListener('click', closeMenu);
                }
            });
        }, 10);
    }
}

/* ══ WISHLIST TOGGLE ══ */
function toggleWishlist(btn, id_doc) {
    fetch('/MEMOIR/client/toggle_wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_doc=' + id_doc
    })
    .then(r => r.json())
    .then(r => {
        const icon = btn.querySelector('i');
        if (r.status === 'added') {
            btn.classList.add('wishlisted');
            if (icon) icon.className = 'fa-solid fa-heart';
        } else {
            btn.classList.remove('wishlisted');
            if (icon) icon.className = 'fa-regular fa-heart';
        }
    });
}
</script>
</body>
</html>