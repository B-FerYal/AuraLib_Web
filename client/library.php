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

$type_id = isset($_GET['type'])  ? (int)$_GET['type']  : 0;
$avail   = isset($_GET['avail']) ? $_GET['avail']       : 'all';

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

$cart_count = 0;
if ($is_logged_in && $user_role === 'client') {
    $rc = $conn->query("SELECT SUM(pi.quantite) as total FROM panier_item pi
                        JOIN panier p ON pi.id_panier = p.id_panier
                        WHERE p.id_user = $id_user");
    $cart_count = (int)($rc->fetch_assoc()['total'] ?? 0);
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

/* ══════════════════════════════════════════════
   HERO — 2-col café-style layout
══════════════════════════════════════════════ */
.hero {
    background: var(--hero-bg);
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 500px;
    position: relative;
    overflow: hidden;
}

/* subtle vertical divider */
.hero::after {
    content: '';
    position: absolute;
    top: 0; bottom: 0;
    left: 50%;
    width: 1px;
    background: linear-gradient(180deg,
        transparent,
        rgba(196,164,107,.16) 25%,
        rgba(196,164,107,.16) 75%,
        transparent);
    pointer-events: none;
}

/* ── LEFT ── */
.hero-left {
    padding: 68px 48px 68px 56px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    z-index: 2;
    animation: fadeUp .75s ease both;
}

@keyframes fadeUp {
    from { opacity:0; transform:translateY(20px); }
    to   { opacity:1; transform:translateY(0); }
}

.hero-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 4.5px;
    text-transform: uppercase;
    color: rgba(196,164,107,.45);
    margin-bottom: 22px;
}
.hero-eyebrow::before {
    content: '';
    width: 22px; height: 1px;
    background: rgba(196,164,107,.35);
    display: block;
    flex-shrink: 0;
}

.hero-title {
    font-family: var(--font-serif);
    font-size: clamp(40px, 4.5vw, 58px);
    font-weight: 700;
    font-style: italic;
    color: #FDFAF5;
    line-height: 1.06;
    margin-bottom: 22px;
    animation: fadeUp .75s .1s ease both;
}
.hero-title em {
    color: var(--gold);
    font-style: normal;
}

.hero-sub {
    font-size: 14px;
    font-weight: 300;
    color: rgba(237,229,212,.42);
    line-height: 1.8;
    max-width: 330px;
    margin-bottom: 36px;
    animation: fadeUp .75s .18s ease both;
}

/* search bar */
.hero-search {
    display: flex;
    align-items: center;
    background: rgba(253,250,245,.06);
    border: 1px solid rgba(196,164,107,.22);
    border-radius: 50px;
    padding: 6px 6px 6px 46px;
    position: relative;
    max-width: 420px;
    margin-bottom: 14px;
    transition: border-color var(--tr), box-shadow var(--tr);
    animation: fadeUp .75s .26s ease both;
}
.hero-search:focus-within {
    border-color: rgba(196,164,107,.5);
    box-shadow: 0 0 0 4px rgba(196,164,107,.09);
}
.hero-search i {
    position: absolute;
    left: 18px; top: 50%;
    transform: translateY(-50%);
    color: rgba(196,164,107,.4);
    font-size: 13px;
    pointer-events: none;
}
#search {
    flex: 1;
    background: transparent;
    border: none; outline: none;
    font-family: var(--font-ui);
    font-size: 13px;
    color: #FDFAF5;
    padding: 10px 0;
}
#search::placeholder { color: rgba(196,164,107,.32); }
.search-btn {
    background: var(--gold);
    border: none; cursor: pointer;
    border-radius: 50px;
    padding: 9px 22px;
    font-family: var(--font-ui);
    font-size: 11px;
    font-weight: 700;
    color: #2C1F0E;
    letter-spacing: .3px;
    transition: background var(--tr);
    white-space: nowrap;
    flex-shrink: 0;
}
.search-btn:hover { background: var(--gold2); }

/* hint tags */
.hero-hints {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    margin-bottom: 40px;
    animation: fadeUp .75s .34s ease both;
}
.hint-tag {
    font-size: 10px;
    color: rgba(196,164,107,.35);
    padding: 4px 12px;
    border: 1px solid rgba(196,164,107,.14);
    border-radius: 50px;
    cursor: pointer;
    transition: color var(--tr), border-color var(--tr), background var(--tr);
}
.hint-tag:hover {
    color: var(--gold);
    border-color: rgba(196,164,107,.38);
    background: rgba(196,164,107,.06);
}

/* stats row */
.hero-stats {
    display: flex;
    gap: 0;
    padding-top: 24px;
    border-top: 1px solid rgba(196,164,107,.1);
    animation: fadeUp .75s .42s ease both;
}
.hstat { flex: 1; }
.hstat:not(:first-child) {
    padding-left: 20px;
    border-left: 1px solid rgba(196,164,107,.1);
}
.hstat-n {
    font-family: var(--font-serif);
    font-size: 26px;
    font-weight: 600;
    color: var(--gold);
    display: block;
    line-height: 1;
    margin-bottom: 5px;
}
.hstat-l {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: rgba(196,164,107,.3);
}

/* decorative dots bottom-left like café template */
.hero-deco-dots {
    position: absolute;
    bottom: 40px; left: 56px;
    display: grid;
    grid-template-columns: repeat(7, 8px);
    gap: 6px;
    opacity: .3;
    pointer-events: none;
}
.hero-deco-dots span {
    width: 4px; height: 4px;
    border-radius: 50%;
    background: var(--gold);
    display: block;
}

/* ── RIGHT — SVG illustration ── */
.hero-right {
    position: relative;
    overflow: hidden;
    min-height: 460px;
}
.hero-right svg {
    position: absolute;
    inset: 0;
    width: 100%; height: 100%;
}

/* ══════════════════════════════════════════════
   CATALOGUE SECTION
══════════════════════════════════════════════ */
.page-wrap { max-width: 1380px; margin: 0 auto; padding: 52px 5% 80px; }

.section-head { margin-bottom: 28px; }
.section-head h2 {
    font-family: var(--font-serif);
    font-size: 32px;
    font-weight: 600;
}
.section-head p { font-size: 13px; color: var(--page-muted); margin-top: 5px; }

/* ── FILTER ROW ── */
.filter-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 32px;
    padding-bottom: 24px;
    border-bottom: 1px solid var(--page-border);
}
.filter-groups { display: flex; flex-direction: column; gap: 14px; }
.filter-col { display: flex; flex-direction: column; gap: 8px; }
.filter-label {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: var(--page-muted);
}
.pills { display: flex; flex-wrap: wrap; gap: 7px; }

.pill, .avail-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    border-radius: 50px;
    font-family: var(--font-ui);
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    border: 1.5px solid var(--page-border);
    background: var(--page-white);
    color: var(--page-muted);
    transition: all var(--tr);
    white-space: nowrap;
}
.pill:hover, .avail-pill:hover {
    border-color: var(--gold);
    color: var(--gold-deep);
    background: var(--gold-faint);
}
.pill.active {
    background: var(--gold);
    border-color: var(--gold);
    color: #2C1F0E;
    font-weight: 700;
    box-shadow: var(--shadow-gold);
}
.avail-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.ap-all  .avail-dot { background: var(--page-muted); }
.ap-all.active, .ap-buy.active {
    background: var(--gold);
    border-color: var(--gold);
    color: #2C1F0E;
    font-weight: 700;
    box-shadow: var(--shadow-gold);
}
.ap-buy  .avail-dot { background: var(--gold); }
.ap-borrow .avail-dot { background: var(--brown); }
.ap-borrow:hover  { border-color: var(--brown-border); color: var(--brown); background: var(--brown-faint); }
.ap-borrow.active { background: var(--brown); border-color: var(--brown); color: #fff; font-weight: 700; }
.ap-both .avail-dot { background: linear-gradient(135deg, var(--gold) 50%, var(--brown) 50%); }
.ap-both.active { background: linear-gradient(110deg, var(--gold) 0%, var(--brown) 100%); border-color: transparent; color: #fff; font-weight: 700; }

.results-meta {
    font-size: 13px;
    color: var(--page-muted);
    align-self: flex-end;
    padding-bottom: 2px;
}
.results-meta strong { color: var(--gold); font-size: 18px; font-family: var(--font-serif); }

/* ══════════════════════════════════════════════
   BOOK GRID & CARDS
══════════════════════════════════════════════ */
.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 24px;
    transition: opacity .25s;
}
.books-grid.loading { opacity: .45; pointer-events: none; }

.book-card {
    background: var(--page-white);
    border-radius: var(--radius);
    border: 1px solid var(--page-border);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    transition: transform var(--tr), box-shadow var(--tr), border-color var(--tr);
    animation: cardIn .4s ease both;
}
.book-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-lg);
    border-color: var(--gold-border);
}
@keyframes cardIn {
    from { opacity:0; transform:translateY(14px); }
    to   { opacity:1; transform:translateY(0); }
}
.book-card:nth-child(2) { animation-delay:.05s; }
.book-card:nth-child(3) { animation-delay:.10s; }
.book-card:nth-child(4) { animation-delay:.15s; }
.book-card:nth-child(5) { animation-delay:.20s; }
.book-card:nth-child(6) { animation-delay:.24s; }
.book-card:nth-child(n+7) { animation-delay:.28s; }

/* Cover */
.card-cover {
    position: relative;
    height: 255px;
    overflow: hidden;
    background: var(--page-bg2);
    display: block;
    text-decoration: none;
}
.card-cover img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .6s cubic-bezier(.4,0,.2,1);
}
.book-card:hover .card-cover img { transform: scale(1.06); }

.avail-ribbon {
    position: absolute;
    top: 10px; left: 10px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.avail-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: .8px;
    text-transform: uppercase;
    backdrop-filter: blur(10px);
    border: 1px solid transparent;
}
.tag-buy    { background: rgba(196,164,107,.9); color: #2C1F0E; border-color: rgba(196,164,107,.4); }
.tag-borrow { background: rgba(122,92,58,.88);  color: #F5EDD8; border-color: rgba(122,92,58,.4); }

.type-badge {
    position: absolute;
    top: 10px; right: 10px;
    background: rgba(44,31,14,.78);
    backdrop-filter: blur(8px);
    color: var(--gold);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: 20px;
    border: 1px solid rgba(196,164,107,.2);
}

.wish-btn {
    position: absolute;
    bottom: 10px; right: 10px;
    width: 32px; height: 32px;
    border-radius: 50%;
    background: rgba(44,31,14,.6);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(196,164,107,.18);
    color: rgba(196,164,107,.45);
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all var(--tr);
}
.wish-btn:hover    { color: var(--gold); border-color: var(--gold-border); background: rgba(44,31,14,.85); }
.wish-btn.wishlisted { color: #ef4444; border-color: #fca5a5; }

/* Card body */
.card-body {
    padding: 18px 18px 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.card-title {
    font-family: var(--font-serif);
    font-size: 18px;
    font-weight: 600;
    color: var(--page-text);
    line-height: 1.3;
    margin-bottom: 5px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.card-title a { text-decoration: none; color: inherit; }
.card-title a:hover { color: var(--gold); }

.card-author { font-size: 12px; color: var(--page-muted); margin-bottom: 14px; }
.card-author i { margin-right: 5px; font-size: 10px; }

.card-price-row { margin-bottom: 14px; }
.card-price {
    font-family: var(--font-serif);
    font-size: 22px;
    font-weight: 600;
    color: var(--amber);
}
html.dark .card-price { color: var(--gold2); }
.price-unit { font-family: var(--font-ui); font-size: 12px; font-weight: 400; margin-left: 3px; }
.card-free {
    font-size: 13px;
    color: var(--page-muted);
    display: flex;
    align-items: center;
    gap: 6px;
}
.card-free i { color: var(--gold); font-size: 12px; }

.card-divider {
    height: 1px;
    background: var(--page-border);
    margin-bottom: 14px;
    opacity: .6;
}

/* Buttons */
.card-actions { display: flex; gap: 7px; margin-top: auto; }
.btn-card {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 8px;
    border-radius: 9px;
    font-family: var(--font-ui);
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all var(--tr);
    line-height: 1;
    letter-spacing: .3px;
}
.btn-card i { font-size: 11px; }
.btn-borrow { background: var(--brown-faint); border: 1.5px solid var(--brown-border); color: var(--brown); }
.btn-borrow:hover { background: var(--brown); color: #F5EDD8; border-color: var(--brown); box-shadow: 0 4px 14px rgba(122,92,58,.3); }
.btn-buy    { background: var(--gold-faint); border: 1.5px solid var(--gold-border); color: var(--gold-deep); }
html.dark .btn-buy { color: var(--gold); }
.btn-buy:hover { background: var(--gold); color: #2C1F0E; border-color: var(--gold); box-shadow: var(--shadow-gold); }
.btn-card.full { flex: 1 1 100%; }

.btn-detail-link {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 8px;
    border-radius: 9px;
    margin-top: 7px;
    font-family: var(--font-ui);
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
    color: var(--page-muted);
    border: 1.5px solid var(--page-border);
    background: transparent;
    transition: all var(--tr);
}
.btn-detail-link:hover { border-color: var(--gold); color: var(--gold); }

.admin-actions { display: flex; gap: 7px; margin-top: auto; }
.btn-admin {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px;
    border-radius: 9px;
    font-family: var(--font-ui);
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    transition: all var(--tr);
}
.btn-edit   { background: var(--gold-faint); color: var(--gold); border: 1.5px solid var(--gold-border); }
.btn-edit:hover { background: rgba(196,164,107,.18); }
.btn-delete { background: rgba(192,57,43,.08); color: var(--danger); border: 1.5px solid rgba(192,57,43,.2); }
.btn-delete:hover { background: rgba(192,57,43,.15); }

/* Empty state */
.empty-state { grid-column: 1/-1; text-align: center; padding: 70px 20px; }
.empty-icon  { font-size: 44px; color: var(--page-border); margin-bottom: 16px; }
.empty-state h3 { font-family: var(--font-serif); font-size: 22px; color: var(--page-muted); margin-bottom: 6px; }
.empty-state p  { font-size: 13px; color: var(--page-muted); }

/* ══ RESPONSIVE ═════════════════════════════ */
@media (max-width: 900px) {
    .hero { grid-template-columns: 1fr; }
    .hero-right { min-height: 300px; }
    .hero::after { display: none; }
}
@media (max-width: 600px) {
    .hero-left { padding: 44px 24px 40px; }
    .books-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 14px; }
    .card-cover { height: 200px; }
    .filter-row { flex-direction: column; }
}
</style>
</head>
<body>

<!-- ══════════════════════════════════════════
     HERO — café-style 2-column layout
══════════════════════════════════════════ -->
<section class="hero">

    <!-- LEFT: text + search + stats -->
    <div class="hero-left">

        <div class="hero-eyebrow">Dépôt Numérique Académique</div>

        <h1 class="hero-title">
            Explorez la<br>
            <em>connaissance,</em><br>
            Chaque Jour
        </h1>

        <p class="hero-sub">
            Empruntez, achetez ou consultez des milliers de ressources universitaires — thèses, livres, articles et journaux.
        </p>

        <div class="hero-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="search" placeholder="Titre, auteur, ISBN, spécialité…">
            <button class="search-btn">Rechercher</button>
        </div>

        <div class="hero-hints">
            <span class="hint-tag" onclick="document.getElementById('search').value=this.textContent;document.getElementById('search').dispatchEvent(new Event('input'))">Thèse de doctorat</span>
            <span class="hint-tag" onclick="document.getElementById('search').value=this.textContent;document.getElementById('search').dispatchEvent(new Event('input'))">Droit algérien</span>
            <span class="hint-tag" onclick="document.getElementById('search').value=this.textContent;document.getElementById('search').dispatchEvent(new Event('input'))">Intelligence artificielle</span>
            <span class="hint-tag" onclick="document.getElementById('search').value=this.textContent;document.getElementById('search').dispatchEvent(new Event('input'))">Finance islamique</span>
        </div>

        <div class="hero-stats">
            <div class="hstat">
                <span class="hstat-n"><?= count($documents) ?>+</span>
                <span class="hstat-l">Documents</span>
            </div>
            <div class="hstat">
                <span class="hstat-n"><?= $q_types->num_rows ?></span>
                <span class="hstat-l">Types</span>
            </div>
            <div class="hstat">
                <span class="hstat-n">100%</span>
                <span class="hstat-l">Numérique</span>
            </div>
        </div>

        <!-- decorative dots like café template -->
        <div class="hero-deco-dots">
            <?php for($i=0;$i<21;$i++): ?><span></span><?php endfor; ?>
        </div>

    </div>

    <!-- RIGHT: SVG library illustration -->
    <div class="hero-right">
        <svg viewBox="0 0 420 500" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
            <defs>
                <linearGradient id="bg1" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#1A1208"/>
                    <stop offset="100%" stop-color="#2C1C0C"/>
                </linearGradient>
                <linearGradient id="floorG" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#3A2410"/>
                    <stop offset="100%" stop-color="#1A1008"/>
                </linearGradient>
                <linearGradient id="tableG" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#6B4A28"/>
                    <stop offset="100%" stop-color="#4A3018"/>
                </linearGradient>
                <linearGradient id="warmG" x1=".5" y1="0" x2=".5" y2="1">
                    <stop offset="0%" stop-color="#C4A46B" stop-opacity=".16"/>
                    <stop offset="100%" stop-color="#C4A46B" stop-opacity="0"/>
                </linearGradient>
                <clipPath id="cp"><rect width="420" height="500"/></clipPath>
            </defs>
            <g clip-path="url(#cp)">
                <!-- background -->
                <rect width="420" height="500" fill="url(#bg1)"/>
                <ellipse cx="210" cy="80" rx="180" ry="130" fill="url(#warmG)"/>
                <!-- wall grid -->
                <line x1="0" y1="28" x2="420" y2="28" stroke="rgba(196,164,107,.08)" stroke-width="1"/>
                <line x1="0" y1="295" x2="420" y2="295" stroke="rgba(196,164,107,.14)" stroke-width="1"/>
                <line x1="36" y1="28" x2="36" y2="295" stroke="rgba(196,164,107,.04)" stroke-width=".5"/>
                <line x1="384" y1="28" x2="384" y2="295" stroke="rgba(196,164,107,.04)" stroke-width=".5"/>
                <line x1="210" y1="28" x2="210" y2="295" stroke="rgba(196,164,107,.03)" stroke-width=".5"/>
                <!-- ═══ BOOKSHELF FRAME ═══ -->
                <rect x="24" y="34" width="372" height="258" rx="4" fill="#1C1208" stroke="rgba(196,164,107,.1)" stroke-width="1"/>
                <!-- shelf dividers -->
                <rect x="26" y="112" width="368" height="4" rx="1" fill="#2C1E10"/>
                <rect x="26" y="192" width="368" height="4" rx="1" fill="#2C1E10"/>
                <rect x="26" y="272" width="368" height="5" rx="1" fill="#3A2818"/>
                <!-- ROW 1 -->
                <rect x="34"  y="38"  width="20" height="72" rx="2" fill="#7A4A22"/><rect x="34"  y="38"  width="20" height="4" rx="1" fill="#C4A46B" opacity=".75"/>
                <rect x="55"  y="44"  width="14" height="66" rx="2" fill="#4A3020"/><rect x="55"  y="44"  width="14" height="3" rx="1" fill="#A88844" opacity=".5"/>
                <rect x="70"  y="36"  width="24" height="74" rx="2" fill="#8B5E32"/><rect x="70"  y="36"  width="24" height="5" rx="1" fill="#D4B47B" opacity=".85"/>
                <rect x="95"  y="42"  width="13" height="68" rx="2" fill="#2C1F0E"/><rect x="95"  y="42"  width="13" height="3" rx="1" fill="#C4A46B" opacity=".4"/>
                <rect x="109" y="37"  width="22" height="73" rx="2" fill="#6B4428"/><rect x="109" y="37"  width="22" height="4" rx="1" fill="#C4A46B" opacity=".7"/>
                <rect x="132" y="41"  width="16" height="69" rx="2" fill="#3A2814"/>
                <rect x="149" y="36"  width="25" height="74" rx="2" fill="#5C3A1E"/><rect x="149" y="36"  width="25" height="5" rx="1" fill="#C4A46B" opacity=".9"/>
                <rect x="178" y="40"  width="15" height="70" rx="2" fill="#4A3020"/><rect x="178" y="40"  width="15" height="3" rx="1" fill="#C4A46B" opacity=".55"/>
                <rect x="194" y="36"  width="20" height="74" rx="2" fill="#8B5E32"/><rect x="194" y="36"  width="20" height="5" rx="1" fill="#D4B47B" opacity=".9"/>
                <rect x="215" y="39"  width="22" height="71" rx="2" fill="#6B4428"/><rect x="215" y="39"  width="22" height="4" rx="1" fill="#C4A46B" opacity=".65"/>
                <rect x="238" y="37"  width="17" height="73" rx="2" fill="#3A2814"/>
                <rect x="256" y="37"  width="24" height="73" rx="2" fill="#7A4A22"/><rect x="256" y="37"  width="24" height="4" rx="1" fill="#D4B47B" opacity=".7"/>
                <rect x="281" y="36"  width="28" height="74" rx="2" fill="#8B5E32"/><rect x="281" y="36"  width="28" height="5" rx="1" fill="#C4A46B" opacity=".95"/>
                <rect x="310" y="40"  width="15" height="70" rx="2" fill="#4A3020"/><rect x="310" y="40"  width="15" height="3" rx="1" fill="#A88844" opacity=".5"/>
                <rect x="326" y="37"  width="21" height="73" rx="2" fill="#6B4428"/><rect x="326" y="37"  width="21" height="4" rx="1" fill="#C4A46B" opacity=".7"/>
                <rect x="348" y="39"  width="16" height="71" rx="2" fill="#5C3A1E"/><rect x="348" y="39"  width="16" height="3" rx="1" fill="#D4B47B" opacity=".6"/>
                <rect x="365" y="37"  width="13" height="73" rx="2" fill="#2C1F0E"/>
                <!-- ROW 2 -->
                <rect x="34"  y="118" width="22" height="70" rx="2" fill="#6B4428"/><rect x="34"  y="118" width="22" height="4" rx="1" fill="#C4A46B" opacity=".8"/>
                <rect x="57"  y="122" width="16" height="66" rx="2" fill="#8B5E32"/>
                <rect x="74"  y="116" width="24" height="72" rx="2" fill="#4A3020"/><rect x="74"  y="116" width="24" height="4" rx="1" fill="#D4B47B" opacity=".7"/>
                <rect x="99"  y="120" width="13" height="68" rx="2" fill="#7A4A22"/><rect x="99"  y="120" width="13" height="3" rx="1" fill="#C4A46B" opacity=".6"/>
                <rect x="113" y="116" width="26" height="72" rx="2" fill="#3A2814"/><rect x="113" y="116" width="26" height="5" rx="1" fill="#C4A46B" opacity=".85"/>
                <rect x="140" y="119" width="17" height="69" rx="2" fill="#5C3A1E"/>
                <rect x="158" y="115" width="22" height="73" rx="2" fill="#8B5E32"/><rect x="158" y="115" width="22" height="5" rx="1" fill="#D4B47B" opacity=".9"/>
                <rect x="181" y="124" width="11" height="64" rx="2" fill="#6B4428" transform="rotate(-5 186 156)"/>
                <rect x="195" y="117" width="20" height="71" rx="2" fill="#2C1F0E"/>
                <rect x="216" y="115" width="26" height="73" rx="2" fill="#7A4A22"/><rect x="216" y="115" width="26" height="4" rx="1" fill="#C4A46B" opacity=".75"/>
                <rect x="243" y="119" width="15" height="69" rx="2" fill="#4A3020"/>
                <rect x="259" y="115" width="24" height="73" rx="2" fill="#6B4428"/><rect x="259" y="115" width="24" height="5" rx="1" fill="#D4B47B" opacity=".8"/>
                <rect x="284" y="117" width="18" height="71" rx="2" fill="#3A2814"/>
                <rect x="303" y="115" width="28" height="73" rx="2" fill="#8B5E32"/><rect x="303" y="115" width="28" height="5" rx="1" fill="#C4A46B" opacity=".9"/>
                <rect x="332" y="119" width="15" height="69" rx="2" fill="#5C3A1E"/>
                <rect x="348" y="116" width="20" height="72" rx="2" fill="#7A4A22"/><rect x="348" y="116" width="20" height="4" rx="1" fill="#C4A46B" opacity=".65"/>
                <!-- ROW 3 -->
                <rect x="34"  y="198" width="18" height="70" rx="2" fill="#8B5E32"/><rect x="34"  y="198" width="18" height="4" rx="1" fill="#C4A46B" opacity=".7"/>
                <rect x="53"  y="202" width="24" height="66" rx="2" fill="#4A3020"/>
                <rect x="78"  y="196" width="20" height="72" rx="2" fill="#6B4428"/><rect x="78"  y="196" width="20" height="5" rx="1" fill="#D4B47B" opacity=".85"/>
                <rect x="99"  y="200" width="15" height="68" rx="2" fill="#2C1F0E"/>
                <rect x="115" y="196" width="26" height="72" rx="2" fill="#7A4A22"/><rect x="115" y="196" width="26" height="4" rx="1" fill="#C4A46B" opacity=".8"/>
                <rect x="142" y="199" width="17" height="69" rx="2" fill="#3A2814"/>
                <rect x="160" y="196" width="22" height="72" rx="2" fill="#5C3A1E"/><rect x="160" y="196" width="22" height="5" rx="1" fill="#C4A46B" opacity=".75"/>
                <rect x="183" y="198" width="28" height="70" rx="2" fill="#8B5E32"/><rect x="183" y="198" width="28" height="5" rx="1" fill="#D4B47B" opacity=".9"/>
                <rect x="212" y="200" width="14" height="68" rx="2" fill="#6B4428"/>
                <rect x="227" y="196" width="24" height="72" rx="2" fill="#4A3020"/><rect x="227" y="196" width="24" height="4" rx="1" fill="#C4A46B" opacity=".6"/>
                <rect x="252" y="198" width="20" height="70" rx="2" fill="#7A4A22"/>
                <rect x="273" y="198" width="17" height="70" rx="2" fill="#3A2814"/>
                <rect x="291" y="195" width="26" height="73" rx="2" fill="#6B4428"/><rect x="291" y="195" width="26" height="5" rx="1" fill="#C4A46B" opacity=".85"/>
                <rect x="318" y="199" width="15" height="69" rx="2" fill="#8B5E32"/>
                <rect x="334" y="196" width="22" height="72" rx="2" fill="#5C3A1E"/><rect x="334" y="196" width="22" height="4" rx="1" fill="#D4B47B" opacity=".7"/>
                <rect x="357" y="198" width="17" height="70" rx="2" fill="#2C1F0E"/>
                <!-- ═══ FLOOR ═══ -->
                <rect x="0" y="295" width="420" height="205" fill="url(#floorG)"/>
                <line x1="0" y1="295" x2="420" y2="295" stroke="rgba(196,164,107,.18)" stroke-width="1"/>
                <line x1="0" y1="325" x2="420" y2="325" stroke="rgba(196,164,107,.04)" stroke-width=".5"/>
                <line x1="0" y1="355" x2="420" y2="355" stroke="rgba(196,164,107,.04)" stroke-width=".5"/>
                <line x1="0" y1="385" x2="420" y2="385" stroke="rgba(196,164,107,.03)" stroke-width=".5"/>
                <line x1="105" y1="295" x2="105" y2="500" stroke="rgba(196,164,107,.03)" stroke-width=".5"/>
                <line x1="210" y1="295" x2="210" y2="500" stroke="rgba(196,164,107,.03)" stroke-width=".5"/>
                <line x1="315" y1="295" x2="315" y2="500" stroke="rgba(196,164,107,.03)" stroke-width=".5"/>
                <!-- ═══ TABLE ═══ -->
                <ellipse cx="210" cy="313" rx="115" ry="8" fill="rgba(0,0,0,.3)"/>
                <rect x="84"  y="313" width="12" height="80" rx="3" fill="#2C1A0A"/>
                <rect x="324" y="313" width="12" height="80" rx="3" fill="#2C1A0A"/>
                <rect x="80"  y="305" width="260" height="12" rx="3" fill="#4A3018"/>
                <rect x="82"  y="307" width="256" height="8"  rx="2" fill="#5C3C20"/>
                <rect x="82"  y="315" width="256" height="72" rx="3" fill="url(#tableG)"/>
                <rect x="84"  y="317" width="252" height="2"  rx="1" fill="rgba(196,164,107,.14)"/>
                <!-- open book -->
                <ellipse cx="185" cy="317" rx="55" ry="5" fill="rgba(0,0,0,.18)"/>
                <path d="M134 307 Q162 302 185 307 L185 358 Q162 352 134 358Z" fill="#EDE0C8"/>
                <path d="M136 310 Q162 305 183 310 L183 355 Q162 349 136 355Z" fill="#F5ECD8"/>
                <line x1="142" y1="316" x2="179" y2="316" stroke="rgba(100,70,40,.15)" stroke-width=".8"/>
                <line x1="142" y1="322" x2="179" y2="322" stroke="rgba(100,70,40,.12)" stroke-width=".8"/>
                <line x1="142" y1="328" x2="179" y2="328" stroke="rgba(100,70,40,.12)" stroke-width=".8"/>
                <line x1="142" y1="334" x2="179" y2="334" stroke="rgba(100,70,40,.1)" stroke-width=".8"/>
                <line x1="142" y1="340" x2="179" y2="340" stroke="rgba(100,70,40,.1)" stroke-width=".8"/>
                <line x1="142" y1="346" x2="174" y2="346" stroke="rgba(100,70,40,.08)" stroke-width=".8"/>
                <path d="M185 307 Q208 302 236 307 L236 358 Q208 352 185 358Z" fill="#EDE0C8"/>
                <path d="M187 310 Q208 305 234 310 L234 355 Q208 349 187 355Z" fill="#F0E5D0"/>
                <line x1="191" y1="316" x2="230" y2="316" stroke="rgba(100,70,40,.15)" stroke-width=".8"/>
                <line x1="191" y1="322" x2="230" y2="322" stroke="rgba(100,70,40,.12)" stroke-width=".8"/>
                <line x1="191" y1="328" x2="230" y2="328" stroke="rgba(100,70,40,.12)" stroke-width=".8"/>
                <line x1="191" y1="334" x2="228" y2="334" stroke="rgba(100,70,40,.1)" stroke-width=".8"/>
                <line x1="191" y1="340" x2="228" y2="340" stroke="rgba(100,70,40,.1)" stroke-width=".8"/>
                <line x1="191" y1="346" x2="222" y2="346" stroke="rgba(100,70,40,.08)" stroke-width=".8"/>
                <rect x="183" y="306" width="4" height="53" rx="1" fill="#8B6B50"/>
                <!-- desk lamp -->
                <ellipse cx="308" cy="318" rx="16" ry="4" fill="#2C1A0A"/>
                <rect x="305" y="284" width="6" height="35" rx="3" fill="#3A2810"/>
                <path d="M308 284 Q313 262 322 248" stroke="#3A2810" stroke-width="5" fill="none" stroke-linecap="round"/>
                <path d="M308 248 Q322 232 336 248" fill="#C4A46B" opacity=".8"/>
                <ellipse cx="322" cy="248" rx="14" ry="5" fill="#D4B47B" opacity=".7"/>
                <path d="M310 252 Q322 310 334 252" fill="rgba(196,164,107,.055)"/>
                <circle cx="322" cy="246" r="5" fill="#FFE8A0" opacity=".5"/>
                <circle cx="322" cy="246" r="2.5" fill="#FFFBE0" opacity=".8"/>
                <!-- stacked books -->
                <rect x="255" y="345" width="40" height="10" rx="2" fill="#7A4A22"/><rect x="255" y="345" width="40" height="3" rx="1" fill="#C4A46B" opacity=".7"/>
                <rect x="257" y="335" width="36" height="11" rx="2" fill="#4A3020"/><rect x="257" y="335" width="36" height="3" rx="1" fill="#D4B47B" opacity=".6"/>
                <rect x="260" y="325" width="32" height="11" rx="2" fill="#8B5E32"/><rect x="260" y="325" width="32" height="3" rx="1" fill="#C4A46B" opacity=".8"/>
                <!-- potted plant -->
                <path d="M354 355 Q362 348 370 355 L368 370 Q362 372 356 370Z" fill="#4A3020"/>
                <line x1="362" y1="350" x2="362" y2="334" stroke="#3D5020" stroke-width="2" stroke-linecap="round"/>
                <ellipse cx="354" cy="334" rx="10" ry="5" fill="#4A6828" transform="rotate(-30 354 334)"/>
                <ellipse cx="370" cy="332" rx="10" ry="5" fill="#3D5820" transform="rotate(25 370 332)"/>
                <ellipse cx="360" cy="326" rx="9" ry="4" fill="#567030" transform="rotate(-10 360 326)"/>
                <!-- deco dots right side -->
                <circle cx="386" cy="148" r="3.5" fill="rgba(196,164,107,.4)"/>
                <circle cx="397" cy="158" r="2.5" fill="rgba(196,164,107,.3)"/>
                <circle cx="388" cy="168" r="2" fill="rgba(196,164,107,.25)"/>
                <circle cx="399" cy="175" r="3" fill="rgba(196,164,107,.3)"/>
                <circle cx="392" cy="183" r="2" fill="rgba(196,164,107,.2)"/>
                <circle cx="401" cy="162" r="1.5" fill="rgba(196,164,107,.18)"/>
                <!-- bottom fade -->
                <rect x="0" y="450" width="420" height="50" fill="rgba(20,12,4,.65)"/>
            </g>
        </svg>
    </div>

</section>

<!-- ══════════════════════════════════════════
     CATALOGUE
══════════════════════════════════════════ -->
<div class="page-wrap">

    <div class="section-head">
        <h2>Catalogue</h2>
        <p>Toutes les ressources disponibles</p>
    </div>

    <!-- Filters — same logic as original -->
    <div class="filter-row">
        <div class="filter-groups">

            <div class="filter-col">
                <span class="filter-label">Type de document</span>
                <div class="pills">
                    <?php $avail_qs = $avail !== 'all' ? "avail=$avail&" : ''; ?>
                    <a href="library.php?<?= $avail_qs ?>type=0"
                       class="pill <?= $type_id == 0 ? 'active' : '' ?>">
                        <i class="fa-solid fa-layer-group" style="font-size:10px"></i> Tous
                    </a>
                    <?php $q_types->data_seek(0); while ($t = $q_types->fetch_assoc()): ?>
                    <a href="library.php?<?= $avail_qs ?>type=<?= $t['id_type'] ?>"
                       class="pill <?= $type_id == $t['id_type'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($t['libelle_type']) ?>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="filter-col">
                <span class="filter-label">Disponibilité</span>
                <div class="pills">
                    <?php
                    $type_qs = $type_id > 0 ? "type=$type_id&" : '';
                    $opts = [
                        'all'    => ['cls'=>'ap-all',    'icon'=>'fa-solid fa-infinity',          'label'=>'Tous'],
                        'buy'    => ['cls'=>'ap-buy',    'icon'=>'fa-solid fa-cart-shopping',     'label'=>'À acheter'],
                        'borrow' => ['cls'=>'ap-borrow', 'icon'=>'fa-regular fa-clock',           'label'=>'À emprunter'],
                        'both'   => ['cls'=>'ap-both',   'icon'=>'fa-solid fa-arrows-left-right', 'label'=>'Les deux'],
                    ];
                    foreach ($opts as $val => $o): ?>
                    <a href="library.php?<?= $type_qs ?>avail=<?= $val ?>"
                       class="avail-pill <?= $o['cls'] ?> <?= $avail === $val ? 'active' : '' ?>">
                        <span class="avail-dot"></span>
                        <i class="<?= $o['icon'] ?>" style="font-size:10px"></i>
                        <?= $o['label'] ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
        <p class="results-meta">
            <strong id="count"><?= count($documents) ?></strong> document(s) trouvé(s)
        </p>
    </div>

    <!-- Grid — same PHP logic as original -->
    <div class="books-grid" id="resultat">
        <?php if (empty($documents)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fa-regular fa-folder-open"></i></div>
            <h3>Aucun document trouvé</h3>
            <p>Essayez un autre filtre ou modifiez votre recherche.</p>
        </div>
        <?php else: ?>
        <?php foreach ($documents as $d):
            $dp         = $d['disponible_pour'] ?? 'both';
            $can_buy    = in_array($dp, ['achat', 'both']);
            $can_borrow = in_array($dp, ['emprunt', 'both']);

            $imgPath = "../uploads/" . (int)$d['id_doc'] . ".jpg";
            if (!file_exists($imgPath)) {
                $imgPath = !empty($d['image_doc'])
                    ? "../uploads/" . $d['image_doc']
                    : "../uploads/default.jpg";
            }
            $detail_url = "/MEMOIR/client/doc_details.php?id=" . (int)$d['id_doc'];
        ?>
        <div class="book-card">

            <a href="<?= $detail_url ?>" class="card-cover">
                <img src="<?= htmlspecialchars($imgPath) ?>"
                     alt="<?= htmlspecialchars($d['titre']) ?>"
                     loading="lazy"
                     onerror="this.src='../uploads/default.jpg'">

                <div class="avail-ribbon">
                    <?php if ($can_buy): ?>
                    <span class="avail-tag tag-buy">
                        <i class="fa-solid fa-cart-shopping" style="font-size:8px"></i> Achat
                    </span>
                    <?php endif; ?>
                    <?php if ($can_borrow): ?>
                    <span class="avail-tag tag-borrow">
                        <i class="fa-regular fa-clock" style="font-size:8px"></i> Emprunt
                    </span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($d['libelle_type'])): ?>
                <span class="type-badge"><?= htmlspecialchars($d['libelle_type']) ?></span>
                <?php endif; ?>

                <?php if ($is_logged_in && $user_role === 'client'): ?>
                <button class="wish-btn"
                        onclick="event.preventDefault(); toggleWishlist(this, <?= (int)$d['id_doc'] ?>)"
                        title="Favoris">
                    <i class="fa-regular fa-heart"></i>
                </button>
                <?php endif; ?>
            </a>

            <div class="card-body">

                <h3 class="card-title">
                    <a href="<?= $detail_url ?>"><?= htmlspecialchars($d['titre']) ?></a>
                </h3>

                <p class="card-author">
                    <i class="fa-solid fa-user-pen"></i>
                    <?= htmlspecialchars($d['auteur'] ?? '') ?>
                </p>

                <div class="card-price-row">
                    <?php if ($can_buy && (float)$d['prix'] > 0): ?>
                        <span class="card-price">
                            <?= number_format((float)$d['prix'], 0, ',', ' ') ?>
                            <span class="price-unit">DA</span>
                        </span>
                    <?php elseif ($can_borrow && !$can_buy): ?>
                        <span class="card-free">
                            <i class="fa-solid fa-book-open"></i> Emprunt gratuit
                        </span>
                    <?php else: ?>
                        <span class="card-free">
                            <i class="fa-solid fa-lock-open"></i> Gratuit
                        </span>
                    <?php endif; ?>
                </div>

                <div class="card-divider"></div>

                <?php if ($user_role === 'client'): ?>
                <div class="card-actions">
                    <?php if ($can_borrow): ?>
                    <a href="../emprunts/emprunt.php?id_doc=<?= (int)$d['id_doc'] ?>"
                       class="btn-card btn-borrow <?= !$can_buy ? 'full' : '' ?>">
                        <i class="fa-regular fa-clock"></i> Emprunter
                    </a>
                    <?php endif; ?>
                    <?php if ($can_buy): ?>
                    <form action="../cart/add_to_cart.php" method="POST" style="flex:1;display:flex">
                        <input type="hidden" name="id_doc" value="<?= (int)$d['id_doc'] ?>">
                        <button type="submit" class="btn-card btn-buy <?= !$can_borrow ? 'full' : '' ?>">
                            <i class="fa-solid fa-cart-plus"></i> Acheter
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <a href="<?= $detail_url ?>" class="btn-detail-link">
                    <i class="fa-solid fa-circle-info" style="font-size:10px"></i> Voir les détails
                </a>

                <?php elseif (!$is_logged_in): ?>
                <a href="/MEMOIR/auth/login.php" class="btn-card btn-borrow full">
                    <i class="fa-solid fa-right-to-bracket"></i> Connexion requise
                </a>

                <?php elseif ($user_role === 'admin'): ?>
                <div class="admin-actions">
                    <a href="/MEMOIR/admin/modifier_document.php?id=<?= (int)$d['id_doc'] ?>"
                       class="btn-admin btn-edit">
                        <i class="fa-solid fa-pen"></i> Modifier
                    </a>
                    <a href="/MEMOIR/admin/delete_doc.php?id=<?= (int)$d['id_doc'] ?>"
                       onclick="return confirm('Supprimer ce document ?')"
                       class="btn-admin btn-delete">
                        <i class="fa-solid fa-trash"></i> Supprimer
                    </a>
                </div>
                <?php endif; ?>

            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php include '../includes/footer.php'; ?>

<script>
/* ══ SEARCH — same logic as original, fixed params ══ */
const searchEl = document.getElementById('search');
const gridEl   = document.getElementById('resultat');
const countEl  = document.getElementById('count');
let debounceTimer;

searchEl.addEventListener('input', function () {
    clearTimeout(debounceTimer);
    const q = this.value;
    debounceTimer = setTimeout(function () {
        const params = new URLSearchParams(window.location.search);
        const avail  = params.get('avail') || 'all';
        const type   = params.get('type')  || '0';
        gridEl.classList.add('loading');
        fetch(
            'recherche_dcmnt.php'
            + '?search=' + encodeURIComponent(q)
            + '&avail='  + encodeURIComponent(avail)
            + '&type='   + encodeURIComponent(type)
        )
        .then(r => r.text())
        .then(html => {
            gridEl.innerHTML = html;
            gridEl.classList.remove('loading');
            if (countEl) countEl.textContent = gridEl.querySelectorAll('.book-card').length;
        })
        .catch(() => gridEl.classList.remove('loading'));
    }, 300);
});

/* ══ WISHLIST TOGGLE — same logic as original ══ */
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
