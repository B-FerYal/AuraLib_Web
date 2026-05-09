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

/* ── URL params (kept for filter compatibility) ── */
$type_id = isset($_GET['type'])  ? (int)$_GET['type']  : 0;
$avail   = isset($_GET['avail']) ? $_GET['avail']       : 'all';

/* ── Full catalogue (used only when a filter is active) ── */
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

/* ══ HERO SLIDER — 5 most recent documents ══ */
$rs_slider = $conn->query("
    SELECT d.id_doc, d.titre, d.auteur, d.description_longue, d.image_doc, d.disponible_pour, t.libelle_type
    FROM documents d
    LEFT JOIN types_documents t ON d.id_type = t.id_type
    ORDER BY d.id_doc DESC LIMIT 5
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

/* ══ CATALOGUE BY CATEGORY — 6 docs per type, only types that have docs ══ */
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
    // total count for this type
    $rc2 = $conn->query("SELECT COUNT(*) as n FROM documents WHERE id_type = $tid");
    $total = (int)($rc2->fetch_assoc()['n'] ?? 0);
    $sections[] = [
        'id'    => $tid,
        'label' => $t['libelle_type'],
        'docs'  => $rows,
        'total' => $total,
    ];
}

/* ── helper: resolve image path ── */
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
    position: relative; width: 100%; height: 540px;
    overflow: hidden; background: #0D0905;
}
@media (max-width: 900px) { .hero-slider-wrapper { height: 460px; } }
@media (max-width: 600px) { .hero-slider-wrapper { height: 360px; } }

.hs-slide { position: relative; width: 100%; height: 100%; overflow: hidden; }
.hs-slide-bg {
    position: absolute; inset: 0; width: 100%; height: 100%;
    object-fit: cover; object-position: center top;
    transform: scale(1.05);
    transition: transform 8s cubic-bezier(.25,0,.25,1);
    filter: brightness(.52) saturate(.8);
}
.swiper-slide-active .hs-slide-bg { transform: scale(1.0); }
.hs-slide::before {
    content: ''; position: absolute; inset: 0;
    background:
        linear-gradient(90deg, rgba(10,6,2,.94) 0%, rgba(10,6,2,.52) 42%, transparent 68%),
        linear-gradient(0deg, rgba(10,6,2,.82) 0%, transparent 55%);
    z-index: 1; pointer-events: none;
}

.hs-content {
    position: absolute; bottom: 0; left: 0; right: 0;
    padding: 0 6% 64px; z-index: 2;
    display: flex; flex-direction: column; align-items: flex-start;
    max-width: 700px;
}
.hs-badge {
    display: inline-flex; align-items: center; gap: 7px;
    font-size: 9px; font-weight: 700; letter-spacing: 3.5px;
    text-transform: uppercase; color: var(--gold);
    background: rgba(196,164,107,.12); border: 1px solid rgba(196,164,107,.3);
    padding: 5px 13px; border-radius: 50px; margin-bottom: 16px;
    backdrop-filter: blur(10px);
    opacity: 0; transform: translateY(12px);
    transition: opacity .5s .1s ease, transform .5s .1s ease;
}
.swiper-slide-active .hs-badge,
.swiper-slide-active .hs-title,
.swiper-slide-active .hs-meta,
.swiper-slide-active .hs-desc,
.swiper-slide-active .hs-btn-row { opacity: 1; transform: translateY(0); }
.hs-title {
    font-family: var(--font-serif);
    font-size: clamp(28px, 4.5vw, 52px); font-weight: 700;
    color: #FDFAF5; line-height: 1.1; margin-bottom: 12px;
    text-shadow: 0 4px 24px rgba(0,0,0,.6);
    opacity: 0; transform: translateY(16px);
    transition: opacity .55s .2s ease, transform .55s .2s ease;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}
.hs-meta {
    display: flex; align-items: center; gap: 14px;
    font-size: 11px; font-weight: 500; color: rgba(237,229,212,.5);
    margin-bottom: 14px; opacity: 0; transform: translateY(14px);
    transition: opacity .5s .3s ease, transform .5s .3s ease;
}
.hs-meta span { display: flex; align-items: center; gap: 5px; }
.hs-meta i { font-size: 9px; color: var(--gold); opacity: .7; }
.hs-meta .hs-dot { width: 3px; height: 3px; border-radius: 50%; background: rgba(196,164,107,.3); }
.hs-desc {
    font-size: 13px; font-weight: 300; color: rgba(237,229,212,.48);
    line-height: 1.7; margin-bottom: 28px;
    opacity: 0; transform: translateY(12px);
    transition: opacity .5s .38s ease, transform .5s .38s ease;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden; max-width: 480px;
}
.hs-btn-row {
    display: flex; gap: 11px;
    opacity: 0; transform: translateY(10px);
    transition: opacity .5s .46s ease, transform .5s .46s ease;
}
.hs-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 26px; border-radius: 50px;
    font-family: var(--font-ui); font-size: 12px; font-weight: 700;
    text-decoration: none; letter-spacing: .3px;
    transition: all var(--tr); cursor: pointer; border: none;
}
.hs-btn-primary { background: var(--gold); color: #1A0E05; box-shadow: 0 6px 22px rgba(196,164,107,.35); }
.hs-btn-primary:hover { background: var(--gold2); transform: translateY(-2px); }
.hs-btn-ghost {
    background: rgba(253,250,245,.08); backdrop-filter: blur(10px);
    border: 1px solid rgba(253,250,245,.2); color: rgba(253,250,245,.85);
}
.hs-btn-ghost:hover { background: rgba(253,250,245,.14); border-color: rgba(253,250,245,.35); }
.hs-progress {
    position: absolute; bottom: 0; left: 0; right: 0;
    height: 2px; background: rgba(196,164,107,.12); z-index: 10; overflow: hidden;
}
.hs-progress-bar {
    height: 100%; background: linear-gradient(90deg, var(--gold-deep), var(--gold));
    width: 0%; transition: none;
}
.hs-thumbs {
    position: absolute; right: 32px; top: 50%; transform: translateY(-50%);
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
    background: rgba(196,164,107,.13); backdrop-filter: blur(10px);
    border: 1px solid rgba(196,164,107,.22); color: rgba(196,164,107,.7);
    font-size: 13px; display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all var(--tr);
}
.hs-arrow:hover { background: rgba(196,164,107,.25); color: var(--gold); border-color: var(--gold-border); }
.hs-arrow-prev { left: 24px; }
.hs-arrow-next { left: 72px; }
.hs-counter {
    position: absolute; bottom: 20px; right: 100px;
    font-size: 11px; font-family: var(--font-serif);
    color: rgba(196,164,107,.45); z-index: 10; letter-spacing: 1px;
}
.hs-counter span { color: var(--gold); font-size: 16px; font-weight: 600; }
@media (max-width: 768px) {
    .hs-thumbs { display: none; }
    .hs-counter { right: 20px; }
    .hs-content { padding: 0 5% 48px; }
    .hs-desc { display: none; }
    .hs-arrow-prev { left: 14px; }
    .hs-arrow-next { left: 60px; }
}

/* ══════════════════════════════════════════════
   STICKY SEARCH BAR
══════════════════════════════════════════════ */
.search-bar-sticky {
    position: sticky;
    top: var(--nav-h);
    z-index: 90;
    background: var(--page-bg);
    border-bottom: 1px solid var(--page-border);
    padding: 12px 5%;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: background .35s, box-shadow .3s;
}
.search-bar-sticky.scrolled {
    box-shadow: 0 4px 20px rgba(42,31,20,.1);
}
html.dark .search-bar-sticky.scrolled { box-shadow: 0 4px 20px rgba(0,0,0,.35); }
.search-input-wrap {
    flex: 1; max-width: 580px;
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
.search-input-wrap i {
    position: absolute; left: 16px; top: 50%;
    transform: translateY(-50%);
    color: var(--page-muted); font-size: 12px; pointer-events: none;
}
#search {
    flex: 1; background: transparent; border: none; outline: none;
    font-family: var(--font-ui); font-size: 13px; color: var(--page-text);
    padding: 10px 0; min-width: 0;
}
#search::placeholder { color: var(--page-muted); opacity: .65; }
.search-btn {
    background: var(--gold); border: none; cursor: pointer;
    border-radius: 50px; padding: 8px 18px;
    font-family: var(--font-ui); font-size: 11px; font-weight: 700;
    color: #2C1F0E; letter-spacing: .3px;
    transition: background var(--tr); white-space: nowrap; flex-shrink: 0;
}
.search-btn:hover { background: var(--gold2); }
.search-hint-tags {
    display: flex; gap: 6px; flex-wrap: wrap; flex-shrink: 0;
}
.hint-tag {
    font-size: 10px; color: var(--page-muted);
    padding: 5px 12px; border: 1px solid var(--page-border);
    border-radius: 50px; cursor: pointer; white-space: nowrap;
    transition: color var(--tr), border-color var(--tr), background var(--tr);
}
.hint-tag:hover { color: var(--gold); border-color: var(--gold-border); background: var(--gold-faint); }
@media (max-width: 700px) { .search-hint-tags { display: none; } }

/* ══════════════════════════════════════════════
   SEARCH RESULTS OVERLAY (replaces sections)
══════════════════════════════════════════════ */
#search-overlay {
    display: none;
    padding: 0 5% 60px;
    max-width: 1380px;
    margin: 0 auto;
}
#search-overlay.visible { display: block; }
.search-overlay-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 32px 0 20px;
    border-bottom: 1px solid var(--page-border);
    margin-bottom: 28px;
}
.search-overlay-header h2 {
    font-family: var(--font-serif); font-size: 22px; font-weight: 600;
}
.search-overlay-header span {
    font-size: 12px; color: var(--page-muted);
}
.search-clear {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 11px; font-weight: 600; color: var(--page-muted);
    cursor: pointer; padding: 6px 14px; border-radius: 50px;
    border: 1px solid var(--page-border); background: transparent;
    transition: all var(--tr);
}
.search-clear:hover { color: var(--danger); border-color: rgba(192,57,43,.3); }

/* ══════════════════════════════════════════════
   CATALOGUE BY SECTION
══════════════════════════════════════════════ */
#catalogue-sections { padding: 0 5% 80px; max-width: 1380px; margin: 0 auto; }

.cat-section {
    padding-top: 48px;
    border-top: 1px solid var(--page-border);
    margin-top: 0;
}
.cat-section:first-child { border-top: none; padding-top: 40px; }

.cat-section-head {
    display: flex; align-items: flex-end;
    justify-content: space-between; gap: 12px;
    margin-bottom: 24px;
}
.cat-section-title {
    display: flex; align-items: center; gap: 12px;
}
.cat-section-title h2 {
    font-family: var(--font-serif);
    font-size: 26px; font-weight: 600; line-height: 1;
}
.cat-section-badge {
    font-size: 10px; font-weight: 700; letter-spacing: 1.5px;
    text-transform: uppercase; color: var(--page-muted);
    background: var(--page-bg2); border: 1px solid var(--page-border);
    padding: 3px 10px; border-radius: 50px;
}
.cat-see-all {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 11px; font-weight: 700; letter-spacing: .3px;
    color: var(--gold-deep); text-decoration: none;
    padding: 8px 18px; border-radius: 50px;
    border: 1.5px solid var(--gold-border);
    background: var(--gold-faint);
    transition: all var(--tr); white-space: nowrap; flex-shrink: 0;
}
html.dark .cat-see-all { color: var(--gold); }
.cat-see-all:hover {
    background: var(--gold); color: #1A0E05;
    border-color: var(--gold); box-shadow: var(--shadow-gold);
    transform: translateY(-1px);
}
.cat-see-all i { font-size: 9px; transition: transform var(--tr); }
.cat-see-all:hover i { transform: translateX(3px); }

/* horizontal scroll row */
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
    position: relative; height: 230px; overflow: hidden;
    background: var(--page-bg2); display: block; text-decoration: none;
}
.card-cover img {
    width: 100%; height: 100%; object-fit: cover; display: block;
    transition: transform .6s cubic-bezier(.4,0,.2,1);
}
.book-card:hover .card-cover img { transform: scale(1.06); }
.avail-ribbon {
    position: absolute; top: 10px; left: 10px;
    display: flex; flex-direction: column; gap: 5px;
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
.type-badge {
    position: absolute; top: 10px; right: 10px;
    background: rgba(44,31,14,.78); backdrop-filter: blur(8px);
    color: var(--gold); font-size: 9px; font-weight: 700;
    letter-spacing: 1.8px; text-transform: uppercase;
    padding: 4px 10px; border-radius: 20px;
    border: 1px solid rgba(196,164,107,.2);
}
.wish-btn {
    position: absolute; bottom: 10px; right: 10px;
    width: 32px; height: 32px; border-radius: 50%;
    background: rgba(44,31,14,.6); backdrop-filter: blur(8px);
    border: 1px solid rgba(196,164,107,.18); color: rgba(196,164,107,.45);
    font-size: 13px; display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all var(--tr);
}
.wish-btn:hover { color: var(--gold); border-color: var(--gold-border); background: rgba(44,31,14,.85); }
.wish-btn.wishlisted { color: #ef4444; border-color: #fca5a5; }
.card-body { padding: 14px 14px 16px; flex: 1; display: flex; flex-direction: column; }
.card-title {
    font-family: var(--font-serif); font-size: 16px; font-weight: 600;
    color: var(--page-text); line-height: 1.3; margin-bottom: 4px;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}
.card-title a { text-decoration: none; color: inherit; }
.card-title a:hover { color: var(--gold); }
.card-author { font-size: 11px; color: var(--page-muted); margin-bottom: 12px; }
.card-author i { margin-right: 4px; font-size: 9px; }
.card-price-row { margin-bottom: 12px; }
.card-price { font-family: var(--font-serif); font-size: 20px; font-weight: 600; color: var(--amber); }
html.dark .card-price { color: var(--gold2); }
.price-unit { font-family: var(--font-ui); font-size: 11px; font-weight: 400; margin-left: 3px; }
.card-free { font-size: 12px; color: var(--page-muted); display: flex; align-items: center; gap: 6px; }
.card-free i { color: var(--gold); font-size: 11px; }
.card-divider { height: 1px; background: var(--page-border); margin-bottom: 12px; opacity: .6; }
.card-actions { display: flex; gap: 7px; margin-top: auto; }
.btn-card {
    flex: 1; display: flex; align-items: center; justify-content: center;
    gap: 6px; padding: 9px 8px; border-radius: 9px;
    font-family: var(--font-ui); font-size: 11px; font-weight: 700;
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
.btn-detail-link {
    display: flex; align-items: center; justify-content: center;
    gap: 6px; padding: 9px 8px; border-radius: 9px; margin-top: 6px;
    font-family: var(--font-ui); font-size: 10px; font-weight: 600;
    text-decoration: none; color: var(--page-muted);
    border: 1.5px solid var(--page-border); background: transparent;
    transition: all var(--tr);
}
.btn-detail-link:hover { border-color: var(--gold); color: var(--gold); }
.admin-actions { display: flex; gap: 7px; margin-top: auto; }
.btn-admin {
    flex: 1; display: flex; align-items: center; justify-content: center;
    gap: 6px; padding: 9px; border-radius: 9px;
    font-family: var(--font-ui); font-size: 11px; font-weight: 700;
    text-decoration: none; transition: all var(--tr);
}
.btn-edit   { background: var(--gold-faint); color: var(--gold); border: 1.5px solid var(--gold-border); }
.btn-edit:hover { background: rgba(196,164,107,.18); }
.btn-delete { background: rgba(192,57,43,.08); color: var(--danger); border: 1.5px solid rgba(192,57,43,.2); }
.btn-delete:hover { background: rgba(192,57,43,.15); }

/* ══ Books grid (used in search results & filtered view) ══ */
.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px; transition: opacity .25s;
}
.books-grid.loading { opacity: .45; pointer-events: none; }

/* Empty state */
.empty-state { grid-column: 1/-1; text-align: center; padding: 70px 20px; }
.empty-icon  { font-size: 44px; color: var(--page-border); margin-bottom: 16px; }
.empty-state h3 { font-family: var(--font-serif); font-size: 22px; color: var(--page-muted); margin-bottom: 6px; }
.empty-state p  { font-size: 13px; color: var(--page-muted); }

/* Filter bar (shown when type/avail filter active) */
.filter-row {
    display: flex; align-items: flex-start;
    justify-content: space-between; flex-wrap: wrap;
    gap: 16px; margin-bottom: 28px;
    padding-bottom: 20px; border-bottom: 1px solid var(--page-border);
}
.filter-groups { display: flex; flex-direction: column; gap: 14px; }
.filter-col { display: flex; flex-direction: column; gap: 8px; }
.filter-label { font-size: 9px; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; color: var(--page-muted); }
.pills { display: flex; flex-wrap: wrap; gap: 7px; }
.pill, .avail-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 16px; border-radius: 50px;
    font-family: var(--font-ui); font-size: 12px; font-weight: 500;
    text-decoration: none; border: 1.5px solid var(--page-border);
    background: var(--page-white); color: var(--page-muted);
    transition: all var(--tr); white-space: nowrap;
}
.pill:hover, .avail-pill:hover { border-color: var(--gold); color: var(--gold-deep); background: var(--gold-faint); }
.pill.active { background: var(--gold); border-color: var(--gold); color: #2C1F0E; font-weight: 700; box-shadow: var(--shadow-gold); }
.avail-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.ap-all .avail-dot  { background: var(--page-muted); }
.ap-all.active, .ap-buy.active { background: var(--gold); border-color: var(--gold); color: #2C1F0E; font-weight: 700; box-shadow: var(--shadow-gold); }
.ap-buy  .avail-dot { background: var(--gold); }
.ap-borrow .avail-dot { background: var(--brown); }
.ap-borrow:hover  { border-color: var(--brown-border); color: var(--brown); background: var(--brown-faint); }
.ap-borrow.active { background: var(--brown); border-color: var(--brown); color: #fff; font-weight: 700; }
.ap-both .avail-dot { background: linear-gradient(135deg, var(--gold) 50%, var(--brown) 50%); }
.ap-both.active { background: linear-gradient(110deg, var(--gold) 0%, var(--brown) 100%); border-color: transparent; color: #fff; font-weight: 700; }
.results-meta { font-size: 13px; color: var(--page-muted); align-self: flex-end; padding-bottom: 2px; }
.results-meta strong { color: var(--gold); font-size: 18px; font-family: var(--font-serif); }

/* Page wrap for filtered view */
.page-wrap { max-width: 1380px; margin: 0 auto; padding: 36px 5% 80px; }
.section-head { margin-bottom: 24px; }
.section-head h2 { font-family: var(--font-serif); font-size: 28px; font-weight: 600; }
.section-head p { font-size: 13px; color: var(--page-muted); margin-top: 5px; }

@media (max-width: 600px) {
    .books-grid, .cat-row { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
    .card-cover { height: 190px; }
    .filter-row { flex-direction: column; }
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
                $safe_author = htmlspecialchars($s['auteur'] ?? '');
                $safe_type   = htmlspecialchars($s['libelle_type'] ?? 'Document');
                $safe_desc   = htmlspecialchars(strip_tags($s['description_longue'] ?? ''));
                $safe_img    = htmlspecialchars($s['_img']);
                $dp          = $s['disponible_pour'] ?? 'both';
            ?>
            <div class="swiper-slide hs-slide">
                <img class="hs-slide-bg" src="<?= $safe_img ?>" alt="<?= $safe_title ?>"
                     onerror="this.src='../uploads/default.jpg'">
                <div class="hs-content">
                    <div class="hs-badge"><i class="fa-solid fa-bookmark"></i> <?= $safe_type ?></div>
                    <h2 class="hs-title"><?= $safe_title ?></h2>
                    <div class="hs-meta">
                        <?php if (!empty($s['auteur'])): ?>
                        <span><i class="fa-solid fa-user-pen"></i> <?= $safe_author ?></span>
                        <span class="hs-dot"></span>
                        <?php endif; ?>
                        <span><i class="fa-solid fa-circle-dot"></i>
                            <?php
                            if ($dp==='achat') echo 'À l\'achat';
                            elseif ($dp==='emprunt') echo 'À l\'emprunt';
                            else echo 'Achat &amp; Emprunt';
                            ?>
                        </span>
                        <span class="hs-dot"></span>
                        <span><i class="fa-regular fa-star"></i> Nouveau</span>
                    </div>
                    <?php if (!empty($s['description_longue'])): ?>
                    <p class="hs-desc"><?= $safe_desc ?></p>
                    <?php endif; ?>
                    <div class="hs-btn-row">
                        <a href="<?= $detail_url ?>" class="hs-btn hs-btn-primary">
                            <i class="fa-solid fa-play" style="font-size:10px"></i> Voir les détails
                        </a>
                        <?php if ($is_logged_in && $user_role==='client' && in_array($dp,['emprunt','both'])): ?>
                        <a href="/MEMOIR/emprunts/emprunt.php?id_doc=<?= (int)$s['id_doc'] ?>" class="hs-btn hs-btn-ghost">
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
        <div class="hs-thumb <?= $idx===0?'active-thumb':'' ?>" data-index="<?= $idx ?>" onclick="hsGoTo(<?= $idx ?>)">
            <img src="<?= htmlspecialchars($s['_img']) ?>" alt="" onerror="this.src='../uploads/default.jpg'">
        </div>
        <?php endforeach; ?>
    </div>
    <div class="hs-counter"><span id="hsCurrentNum">1</span> / <?= count($slider_items) ?></div>
    <div class="hs-progress"><div class="hs-progress-bar" id="hsProgress"></div></div>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════
     STICKY SEARCH BAR
══════════════════════════════════════════ -->
<div class="search-bar-sticky" id="searchBar">
    <div class="search-input-wrap">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="search" placeholder="Titre, auteur, ISBN, spécialité…" autocomplete="off">
        <button class="search-btn" onclick="document.getElementById('search').dispatchEvent(new Event('input'))">Rechercher</button>
    </div>
    <div class="search-hint-tags">
        <span class="hint-tag" onclick="setSearch('Thèse de doctorat')">Thèse</span>
        <span class="hint-tag" onclick="setSearch('Droit algérien')">Droit</span>
        <span class="hint-tag" onclick="setSearch('Intelligence artificielle')">IA</span>
        <span class="hint-tag" onclick="setSearch('Finance islamique')">Finance</span>
        <span class="hint-tag" onclick="setSearch('Revue scientifique')">Revue</span>
    </div>
</div>

<!-- ══════════════════════════════════════════
     SEARCH RESULTS (hidden until user types)
══════════════════════════════════════════ -->
<div id="search-overlay">
    <div class="search-overlay-header">
        <div>
            <h2>Résultats de recherche</h2>
            <span id="search-count-label">0 document(s) trouvé(s)</span>
        </div>
        <button class="search-clear" onclick="clearSearch()">
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
<?php foreach ($sections as $sec):
    $sec_url = "library.php?type=" . $sec['id'];
?>
<div class="cat-section">
    <div class="cat-section-head">
        <div class="cat-section-title">
            <h2><?= htmlspecialchars($sec['label']) ?></h2>
            <span class="cat-section-badge"><?= $sec['total'] ?> doc<?= $sec['total']>1?'s':'' ?></span>
        </div>
        <?php if ($sec['total'] > 6): ?>
        <a href="<?= $sec_url ?>" class="cat-see-all">
            Voir tout <i class="fa-solid fa-arrow-right"></i>
        </a>
        <?php endif; ?>
    </div>
    <div class="cat-row">
        <?php foreach ($sec['docs'] as $d):
            $dp         = $d['disponible_pour'] ?? 'both';
            $can_buy    = in_array($dp, ['achat','both']);
            $can_borrow = in_array($dp, ['emprunt','both']);
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
                    <?php if ($can_borrow): ?>
                    <a href="../emprunts/emprunt.php?id_doc=<?= (int)$d['id_doc'] ?>" class="btn-card btn-borrow <?= !$can_buy?'full':'' ?>">
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
                </div>
                <a href="<?= $detail_url ?>" class="btn-detail-link">
                    <i class="fa-solid fa-circle-info" style="font-size:9px"></i> Voir les détails
                </a>
                <?php elseif (!$is_logged_in): ?>
                <a href="/MEMOIR/auth/login.php" class="btn-card btn-borrow full">
                    <i class="fa-solid fa-right-to-bracket"></i> Connexion requise
                </a>
                <?php elseif ($user_role==='admin'): ?>
                <div class="admin-actions">
                    <a href="/MEMOIR/admin/modifier_document.php?id=<?= (int)$d['id_doc'] ?>" class="btn-admin btn-edit">
                        <i class="fa-solid fa-pen"></i> Modifier
                    </a>
                    <a href="/MEMOIR/admin/delete_doc.php?id=<?= (int)$d['id_doc'] ?>"
                       onclick="return confirm('Supprimer ce document ?')" class="btn-admin btn-delete">
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
    const DELAY = 5000;
    const prog  = document.getElementById('hsProgress');
    const ctr   = document.getElementById('hsCurrentNum');
    const thumbs= document.querySelectorAll('.hs-thumb');

    function resetProg() {
        if (!prog) return;
        prog.style.transition = 'none';
        prog.style.width = '0%';
        void prog.offsetWidth;
    }
    function startProg() {
        if (!prog) return;
        prog.style.transition = 'width ' + DELAY + 'ms linear';
        prog.style.width = '100%';
    }
    function updateThumbs(idx) {
        thumbs.forEach((t,i) => t.classList.toggle('active-thumb', i===idx));
        if (ctr) ctr.textContent = idx + 1;
    }
    const swiper = new Swiper('.hs-swiper', {
        loop: true, speed: 900, effect: 'fade',
        fadeEffect: { crossFade: true },
        autoplay: { delay: DELAY, disableOnInteraction: false, pauseOnMouseEnter: true },
        navigation: { nextEl: '#hsNext', prevEl: '#hsPrev' },
        on: {
            slideChangeTransitionStart() { resetProg(); },
            slideChangeTransitionEnd()   { updateThumbs(this.realIndex); startProg(); },
            autoplayStart: startProg, autoplayStop: resetProg,
        }
    });
    startProg();
    window.hsGoTo = function(idx) {
        swiper.slideToLoop(idx, 800);
        resetProg(); setTimeout(startProg, 820);
        updateThumbs(idx);
    };
})();

/* ══ STICKY SEARCH — shadow on scroll ══ */
const searchBarEl = document.getElementById('searchBar');
window.addEventListener('scroll', () => {
    searchBarEl.classList.toggle('scrolled', window.scrollY > 30);
}, { passive: true });

/* ══ SEARCH LOGIC ══ */
const searchEl   = document.getElementById('search');
const overlayEl  = document.getElementById('search-overlay');
const gridEl     = document.getElementById('resultat');
const countLbl   = document.getElementById('search-count-label');
const sectionsEl = document.getElementById('catalogue-sections');
let debounceTimer;

function setSearch(val) {
    searchEl.value = val;
    searchEl.dispatchEvent(new Event('input'));
    searchEl.focus();
}

function clearSearch() {
    searchEl.value = '';
    overlayEl.classList.remove('visible');
    sectionsEl.style.display = '';
    searchEl.focus();
}

searchEl.addEventListener('input', function () {
    clearTimeout(debounceTimer);
    const q = this.value.trim();
    if (!q) { clearSearch(); return; }
    debounceTimer = setTimeout(() => {
        overlayEl.classList.add('visible');
        sectionsEl.style.display = 'none';
        gridEl.classList.add('loading');
        fetch('recherche_dcmnt.php?search=' + encodeURIComponent(q) + '&avail=all&type=0')
            .then(r => r.text())
            .then(html => {
                gridEl.innerHTML = html;
                gridEl.classList.remove('loading');
                const n = gridEl.querySelectorAll('.book-card').length;
                countLbl.textContent = n + ' document' + (n !== 1 ? 's' : '') + ' trouvé' + (n !== 1 ? 's' : '');
            })
            .catch(() => gridEl.classList.remove('loading'));
    }, 280);
});

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
        if (r.status === 'added') { btn.classList.add('wishlisted'); if (icon) icon.className='fa-solid fa-heart'; }
        else                      { btn.classList.remove('wishlisted'); if (icon) icon.className='fa-regular fa-heart'; }
    });
}
</script>
</body>
</html>
