<?php
session_start();
include "../includes/db.php";
include_once '../includes/languages.php';

$is_logged_in = isset($_SESSION['id_user']);
$id_user      = $is_logged_in ? (int)$_SESSION['id_user'] : 0;
$user_role    = $_SESSION['role'] ?? 'client';
$base         = "/MEMOIR";

// ── 12 documents for homepage (8 shown + 4 hidden) ──
$sql_home = "SELECT d.*, t.libelle_type
             FROM documents d
             LEFT JOIN types_documents t ON d.id_type = t.id_type
             WHERE d.status = 1
             ORDER BY d.created_at DESC
             LIMIT 12";
$res_home  = mysqli_query($conn, $sql_home);
$docs_home = mysqli_fetch_all($res_home, MYSQLI_ASSOC);
$docs_first = array_slice($docs_home, 0, 8);
$docs_more  = array_slice($docs_home, 8);

// ── Categories with count ───────────────────────────
$res_cats = $conn->query("SELECT t.id_type, t.libelle_type, COUNT(d.id_doc) as nb
                          FROM types_documents t
                          LEFT JOIN documents d ON d.id_type = t.id_type AND d.status = 1
                          GROUP BY t.id_type ORDER BY nb DESC");
$categories = $res_cats->fetch_all(MYSQLI_ASSOC);

// ── Stats ───────────────────────────────────────────
$total_docs  = $conn->query("SELECT COUNT(*) FROM documents WHERE status=1")->fetch_row()[0];
$total_users = $conn->query("SELECT COUNT(*) FROM users WHERE role='client'")->fetch_row()[0];
$total_types = count($categories);

// ── Cart count ──────────────────────────────────────
$cart_count = 0;
if ($is_logged_in && $user_role === 'client') {
    $rc = $conn->query("SELECT SUM(pi.quantite) as total FROM panier_item pi JOIN panier p ON pi.id_panier=p.id_panier WHERE p.id_user=$id_user");
    $cart_count = (int)($rc->fetch_assoc()['total'] ?? 0);
}
?>
<?php include '../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="<?= $lang ?? 'fr' ?>" dir="<?= ($lang ?? 'fr') == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AuraLib · Accueil</title>
<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<style>
/* ══ TOKENS ══════════════════════════════════════════ */
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
    --nav-bg:       #2C1F0E;
    --page-bg:      #F2EDE3;
    --page-bg2:     #E8E0D0;
    --page-white:   #FDFAF5;
    --page-text:    #2A1F14;
    --page-muted:   #9A8C7E;
    --page-border:  #D8CFC0;
    --danger:       #C0392B;
    --font-serif:   'EB Garamond', Georgia, serif;
    --font-ui:      'Plus Jakarta Sans', sans-serif;
    --nav-h:        66px;
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
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:var(--font-ui);background:var(--page-bg);color:var(--page-text);padding-top:var(--nav-h);transition:background .35s,color .35s}

/* ══ HERO ════════════════════════════════════════════ */
.hero{
    background:var(--nav-bg);
    position:relative;overflow:hidden;
    padding:72px 6% 88px;text-align:center;
    border-bottom:1px solid rgba(196,164,107,.18);
}
/* warm radial glow */
.hero::before{
    content:'';position:absolute;inset:0;
    background:
        radial-gradient(ellipse 80% 50% at 20% 50%, rgba(196,164,107,.06) 0%,transparent 60%),
        radial-gradient(ellipse 60% 40% at 80% 60%, rgba(122,92,58,.05) 0%,transparent 60%);
    pointer-events:none;
}
/* bottom gold line */
.hero::after{
    content:'';position:absolute;left:10%;right:10%;bottom:0;height:1px;
    background:linear-gradient(90deg,transparent,rgba(196,164,107,.45),transparent);
}
.hero-eyebrow{
    font-size:10px;font-weight:700;letter-spacing:5px;text-transform:uppercase;
    color:rgba(196,164,107,.45);margin-bottom:16px;
}
.hero-title{
    font-family:var(--font-serif);
    font-size:clamp(36px,5.5vw,62px);font-weight:600;
    color:#FDFAF5;line-height:1.12;margin-bottom:14px;
}
.hero-title em{color:var(--gold);font-style:italic}
.hero-sub{
    color:rgba(237,229,212,.38);font-size:15px;font-weight:300;
    max-width:440px;margin:0 auto 36px;line-height:1.7;
}
/* Search */
.search-wrap{max-width:540px;margin:0 auto 42px;position:relative}
.search-icon{
    position:absolute;left:20px;top:50%;transform:translateY(-50%);
    color:rgba(156,140,126,.6);font-size:15px;pointer-events:none;
}
#search{
    width:100%;background:#FDFAF5;
    border:1.5px solid rgba(196,164,107,.2);border-radius:50px;
    padding:15px 24px 15px 50px;
    font-family:var(--font-ui);font-size:14px;color:var(--page-text);
    outline:none;box-shadow:var(--shadow-md);
    transition:border-color var(--tr),box-shadow var(--tr);
}
#search::placeholder{color:#9A8C7E}
#search:focus{
    border-color:var(--gold);
    box-shadow:0 0 0 4px rgba(196,164,107,.13),var(--shadow-md);
}
html.dark #search{background:#1E1610;color:#EDE5D4}
/* Stats */
.stats-row{display:flex;justify-content:center;gap:48px;flex-wrap:wrap}
.stat-item{text-align:center}
.stat-n{font-family:var(--font-serif);font-size:30px;font-weight:600;color:var(--gold);line-height:1}
.stat-l{font-size:10px;font-weight:600;letter-spacing:2.5px;text-transform:uppercase;color:rgba(237,229,212,.3);margin-top:5px}

/* ══ PAGE WRAP ═══════════════════════════════════════ */
.page-wrap{max-width:1380px;margin:0 auto;padding:52px 5% 80px}

/* ══ SECTION HEADER ══════════════════════════════════ */
.sec-head{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px}
.sec-head-left h2{font-family:var(--font-serif);font-size:30px;font-weight:600}
.sec-head-left p{font-size:13px;color:var(--page-muted);margin-top:4px}
.voir-tout{
    font-size:13px;font-weight:600;color:var(--gold);
    text-decoration:none;display:inline-flex;align-items:center;gap:5px;
    padding:7px 16px;border-radius:8px;border:1.5px solid var(--gold-border);
    transition:all var(--tr);
}
.voir-tout:hover{background:var(--gold-faint);border-color:var(--gold)}

/* ══ FILTER PILLS ════════════════════════════════════ */
.filter-row{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:30px}
.filter-groups{display:flex;flex-direction:column;gap:12px}
.filter-col{display:flex;flex-direction:column;gap:7px}
.filter-label{font-size:9px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--page-muted)}
.pills{display:flex;flex-wrap:wrap;gap:7px}

.pill,.avail-pill{
    display:inline-flex;align-items:center;gap:6px;
    padding:6px 15px;border-radius:50px;
    font-family:var(--font-ui);font-size:12px;font-weight:500;
    text-decoration:none;border:1.5px solid var(--page-border);
    background:var(--page-white);color:var(--page-muted);
    transition:all var(--tr);white-space:nowrap;cursor:pointer;
}
.pill:hover,.avail-pill:hover{border-color:var(--gold);color:var(--gold-deep);background:var(--gold-faint)}
.pill.active{background:var(--gold);border-color:var(--gold);color:#2C1F0E;font-weight:700;box-shadow:var(--shadow-gold)}

/* Availability dots */
.avail-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.ap-all  .avail-dot{background:var(--page-muted)}
.ap-all.active,.ap-buy.active{background:var(--gold);border-color:var(--gold);color:#2C1F0E;font-weight:700;box-shadow:var(--shadow-gold)}
.ap-buy  .avail-dot{background:var(--gold)}
.ap-borrow .avail-dot{background:var(--brown)}
.ap-borrow:hover{border-color:var(--brown-border);color:var(--brown);background:var(--brown-faint)}
.ap-borrow.active{background:var(--brown);border-color:var(--brown);color:#fff;font-weight:700;box-shadow:0 6px 20px rgba(122,92,58,.22)}
.ap-both .avail-dot{background:linear-gradient(135deg,var(--gold) 50%,var(--brown) 50%)}
.ap-both.active{background:linear-gradient(110deg,var(--gold) 0%,var(--brown) 100%);border-color:transparent;color:#fff;font-weight:700}

.results-meta{font-size:12px;color:var(--page-muted);align-self:flex-end;padding-bottom:2px}
.results-meta strong{color:var(--gold);font-size:16px}

/* ══ BOOKS GRID ══════════════════════════════════════ */
.books-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(235px,1fr));gap:24px}

/* ══ BOOK CARD ═══════════════════════════════════════ */
.book-card{
    background:var(--page-white);
    border-radius:var(--radius);border:1px solid var(--page-border);
    overflow:hidden;box-shadow:var(--shadow-sm);
    display:flex;flex-direction:column;
    transition:transform var(--tr),box-shadow var(--tr),border-color var(--tr);
    animation:cardIn .4s ease both;
}
.book-card:hover{transform:translateY(-6px);box-shadow:var(--shadow-lg);border-color:var(--gold-border)}
@keyframes cardIn{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.book-card:nth-child(2){animation-delay:.06s}
.book-card:nth-child(3){animation-delay:.12s}
.book-card:nth-child(4){animation-delay:.18s}
.book-card:nth-child(5){animation-delay:.22s}
.book-card:nth-child(6){animation-delay:.26s}
.book-card:nth-child(7){animation-delay:.30s}
.book-card:nth-child(8){animation-delay:.34s}

/* Cover */
.card-cover{position:relative;height:250px;overflow:hidden;background:var(--page-bg2)}
.card-cover img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .6s cubic-bezier(.4,0,.2,1)}
.book-card:hover .card-cover img{transform:scale(1.05)}

/* Availability ribbon */
.avail-ribbon{position:absolute;top:10px;left:10px;display:flex;flex-direction:column;gap:5px}
.avail-tag{
    display:inline-flex;align-items:center;gap:5px;
    padding:4px 9px;border-radius:20px;
    font-size:9px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;
    backdrop-filter:blur(10px);border:1px solid transparent;
}
.tag-buy   {background:rgba(196,164,107,.9);color:#2C1F0E;border-color:rgba(196,164,107,.4)}
.tag-borrow{background:rgba(122,92,58,.88);color:#F5EDD8;border-color:rgba(122,92,58,.4)}

/* Type badge */
.type-badge{
    position:absolute;top:10px;right:10px;
    background:rgba(44,31,14,.78);backdrop-filter:blur(8px);
    color:var(--gold);font-size:9px;font-weight:700;
    letter-spacing:1.8px;text-transform:uppercase;
    padding:4px 10px;border-radius:20px;border:1px solid rgba(196,164,107,.2);
}

/* Wishlist */
.wish-btn{
    position:absolute;bottom:10px;right:10px;
    width:32px;height:32px;border-radius:50%;
    background:rgba(44,31,14,.6);backdrop-filter:blur(8px);
    border:1px solid rgba(196,164,107,.18);color:rgba(196,164,107,.45);
    font-size:13px;display:flex;align-items:center;justify-content:center;
    text-decoration:none;transition:all var(--tr);
}
.wish-btn:hover{color:var(--gold);border-color:var(--gold-border);background:rgba(44,31,14,.85)}

/* Card body */
.card-body{padding:18px 18px 20px;flex:1;display:flex;flex-direction:column}
.card-title{
    font-family:var(--font-serif);font-size:18px;font-weight:600;
    color:var(--page-text);line-height:1.3;margin-bottom:5px;
    display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}
.card-author{font-size:12px;color:var(--page-muted);margin-bottom:14px}
.card-author i{margin-right:5px;font-size:10px}

.card-price-row{margin-bottom:14px}
.card-price{font-family:var(--font-serif);font-size:22px;font-weight:600;color:var(--amber)}
html.dark .card-price{color:var(--gold2)}
.price-unit{font-family:var(--font-ui);font-size:12px;font-weight:400;margin-left:3px}
.card-free{font-size:13px;color:var(--page-muted);display:flex;align-items:center;gap:6px}
.card-free i{color:var(--gold);font-size:12px}

.card-divider{height:1px;background:var(--page-border);margin-bottom:14px;opacity:.6}

/* Action buttons */
.card-actions{display:flex;gap:7px;margin-top:auto}
.btn-card{
    flex:1;display:flex;align-items:center;justify-content:center;gap:6px;
    padding:10px 8px;border-radius:9px;
    font-family:var(--font-ui);font-size:11px;font-weight:700;
    text-decoration:none;border:none;cursor:pointer;
    transition:all var(--tr);line-height:1;letter-spacing:.3px;
}
.btn-card i{font-size:11px}
/* Borrow — warm brown */
.btn-borrow{background:var(--brown-faint);border:1.5px solid var(--brown-border);color:var(--brown)}
.btn-borrow:hover{background:var(--brown);color:#F5EDD8;border-color:var(--brown);box-shadow:0 4px 14px rgba(122,92,58,.3)}
/* Buy — gold */
.btn-buy{background:var(--gold-faint);border:1.5px solid var(--gold-border);color:var(--gold-deep)}
html.dark .btn-buy{color:var(--gold)}
.btn-buy:hover{background:var(--gold);color:#2C1F0E;border-color:var(--gold);box-shadow:var(--shadow-gold)}
.btn-card.full{flex:1 1 100%}

/* Admin */
.admin-actions{display:flex;gap:7px;margin-top:auto}
.btn-admin{flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;border-radius:9px;font-family:var(--font-ui);font-size:11px;font-weight:700;text-decoration:none;transition:all var(--tr)}
.btn-edit{background:var(--gold-faint);color:var(--gold);border:1.5px solid var(--gold-border)}
.btn-edit:hover{background:rgba(196,164,107,.18)}
.btn-delete{background:rgba(192,57,43,.08);color:var(--danger);border:1.5px solid rgba(192,57,43,.2)}
.btn-delete:hover{background:rgba(192,57,43,.15)}

/* ══ VOIR PLUS BUTTON ════════════════════════════════ */
.more-wrap{text-align:center;margin-top:36px}
.btn-more{
    font-family:var(--font-ui);font-size:14px;font-weight:600;
    padding:13px 38px;border-radius:10px;
    background:var(--nav-bg);color:#EDE5D4;
    border:1px solid rgba(196,164,107,.2);cursor:pointer;
    transition:all var(--tr);display:inline-flex;align-items:center;gap:8px;
}
.btn-more:hover{background:#3D2E1A;border-color:rgba(196,164,107,.4);box-shadow:var(--shadow-md)}
.btn-more i{transition:transform var(--tr)}
.btn-more.open i{transform:rotate(180deg)}

/* Extra cards hidden by default */
.extra-grid{
    display:grid;grid-template-columns:repeat(auto-fill,minmax(235px,1fr));gap:24px;
    margin-top:24px;
    overflow:hidden;max-height:0;
    transition:max-height .55s cubic-bezier(.4,0,.2,1),opacity .4s ease;
    opacity:0;
}
.extra-grid.open{max-height:1000px;opacity:1}

/* ══ CATEGORIES ══════════════════════════════════════ */
.cats-section{margin-top:64px}
.cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-top:28px}
.cat-card{
    background:var(--page-white);border:1px solid var(--page-border);
    border-radius:var(--radius);padding:24px 20px;
    display:flex;align-items:center;gap:16px;
    text-decoration:none;color:var(--page-text);
    transition:all var(--tr);box-shadow:var(--shadow-sm);
    border-left:3px solid transparent;
}
.cat-card:hover{transform:translateY(-3px);box-shadow:var(--shadow-md);border-left-color:var(--gold);background:var(--gold-faint)}
.cat-icon{
    width:44px;height:44px;border-radius:10px;flex-shrink:0;
    background:rgba(196,164,107,.12);
    display:flex;align-items:center;justify-content:center;
    font-size:20px;transition:background var(--tr);
}
.cat-card:hover .cat-icon{background:rgba(196,164,107,.22)}
.cat-name{font-family:var(--font-serif);font-size:16px;font-weight:600}
.cat-count{font-size:11px;color:var(--page-muted);margin-top:2px}

/* ══ CTA BANNER ══════════════════════════════════════ */
.cta-banner{
    margin-top:64px;
    background:var(--nav-bg);
    border-radius:16px;
    padding:48px 52px;
    display:flex;align-items:center;justify-content:space-between;
    gap:32px;flex-wrap:wrap;
    border:1px solid rgba(196,164,107,.18);
    position:relative;overflow:hidden;
}
.cta-banner::before{
    content:'';position:absolute;right:-60px;top:-60px;
    width:280px;height:280px;border-radius:50%;
    background:radial-gradient(circle,rgba(196,164,107,.06),transparent 70%);
    pointer-events:none;
}
.cta-text h2{font-family:var(--font-serif);font-size:28px;font-weight:600;color:#FDFAF5;margin-bottom:8px}
.cta-text p{font-size:14px;color:rgba(237,229,212,.4);max-width:380px;line-height:1.6}
.btn-cta{
    font-family:var(--font-ui);font-size:14px;font-weight:700;
    padding:14px 32px;border-radius:10px;
    background:var(--gold);color:#2C1F0E;border:none;cursor:pointer;
    text-decoration:none;white-space:nowrap;
    transition:all var(--tr);box-shadow:var(--shadow-gold);
    display:inline-flex;align-items:center;gap:8px;
}
.btn-cta:hover{background:var(--gold2);transform:translateY(-2px);box-shadow:0 10px 30px rgba(196,164,107,.35)}

/* ══ EMPTY STATE ══════════════════════════════════════ */
.empty-state{grid-column:1/-1;text-align:center;padding:70px 20px}
.empty-icon{font-size:44px;color:var(--page-border);margin-bottom:16px}
.empty-state h3{font-family:var(--font-serif);font-size:22px;color:var(--page-muted);margin-bottom:6px}
.empty-state p{font-size:13px;color:var(--page-muted)}

@media(max-width:768px){
    .hero{padding:52px 5% 64px}
    .books-grid,.extra-grid{grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px}
    .card-cover{height:200px}
    .cta-banner{padding:32px 24px;flex-direction:column}
    .filter-row{flex-direction:column}
}
</style>
</head>
<body>

<!-- ══ HERO ═══════════════════════════════════════════ -->
<section class="hero">
    <p class="hero-eyebrow">Dépôt Numérique Académique · UHBC Chlef</p>
    <h1 class="hero-title">Explorez la <em>connaissance</em></h1>
    <p class="hero-sub">Empruntez, achetez ou consultez des milliers de ressources universitaires en ligne</p>

    <div class="search-wrap">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" id="search" placeholder="Rechercher par titre, auteur ou spécialité…">
    </div>

    <div class="stats-row">
        <div class="stat-item">
            <div class="stat-n"><?= number_format($total_docs) ?>+</div>
            <div class="stat-l">Documents</div>
        </div>
        <div class="stat-item">
            <div class="stat-n"><?= $total_types ?></div>
            <div class="stat-l">Catégories</div>
        </div>
        <div class="stat-item">
            <div class="stat-n"><?= number_format($total_users) ?>+</div>
            <div class="stat-l">Étudiants</div>
        </div>
    </div>
</section>

<!-- ══ MAIN ════════════════════════════════════════════ -->
<div class="page-wrap">

    <!-- ── Section: Catalogue preview ── -->
    <div class="sec-head">
        <div class="sec-head-left">
            <h2>Catalogue</h2>
            <p>Découvrez nos dernières ressources disponibles</p>
        </div>
        <a href="library.php" class="voir-tout">
            Voir tout <i class="fa-solid fa-arrow-right" style="font-size:11px"></i>
        </a>
    </div>

    <!-- Filter row (links to library.php with filter params) -->
    <div class="filter-row">
        <div class="filter-groups">
            <div class="filter-col">
                <span class="filter-label">Disponibilité</span>
                <div class="pills">
                    <?php
                    $avail_opts = [
                        'all'    => ['cls'=>'ap-all',    'icon'=>'fa-solid fa-infinity',          'label'=>'Tous'],
                        'buy'    => ['cls'=>'ap-buy',    'icon'=>'fa-solid fa-cart-shopping',     'label'=>'À acheter'],
                        'borrow' => ['cls'=>'ap-borrow', 'icon'=>'fa-regular fa-clock',           'label'=>'À emprunter'],
                        'both'   => ['cls'=>'ap-both',   'icon'=>'fa-solid fa-arrows-left-right', 'label'=>'Les deux'],
                    ];
                    foreach ($avail_opts as $val => $o): ?>
                        <a href="library.php?avail=<?= $val ?>"
                           class="avail-pill <?= $o['cls'] ?> <?= $val === 'all' ? 'active' : '' ?>">
                            <span class="avail-dot"></span>
                            <i class="<?= $o['icon'] ?>" style="font-size:10px"></i>
                            <?= $o['label'] ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- First 8 cards -->
    <div class="books-grid">
        <?php if (empty($docs_first)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="fa-regular fa-folder-open"></i></div>
                <h3>Aucun document disponible</h3>
                <p>Revenez bientôt !</p>
            </div>
        <?php else: ?>
            <?php foreach ($docs_first as $d): ?>
                <?php
                $dp         = $d['disponible_pour'] ?? 'both';
                $can_buy    = in_array($dp, ['achat', 'both']);
                $can_borrow = in_array($dp, ['emprunt', 'both']);
                $imgPath    = !empty($d['image_doc']) ? "../uploads/".$d['image_doc'] : "../uploads/default.png";
                ?>
                <div class="book-card">
                    <div class="card-cover">
                        <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($d['titre']) ?>" loading="lazy">
                        <div class="avail-ribbon">
                            <?php if ($can_buy): ?>
                                <span class="avail-tag tag-buy"><i class="fa-solid fa-cart-shopping" style="font-size:8px"></i> Achat</span>
                            <?php endif; ?>
                            <?php if ($can_borrow): ?>
                                <span class="avail-tag tag-borrow"><i class="fa-regular fa-clock" style="font-size:8px"></i> Emprunt</span>
                            <?php endif; ?>
                        </div>
                        <span class="type-badge"><?= htmlspecialchars($d['libelle_type'] ?? '') ?></span>
                        <?php if ($user_role === 'client'): ?>
                        <a href="toggle_wishlist.php?id_doc=<?= $d['id_doc'] ?>" class="wish-btn" title="Favoris">
                            <i class="fa-regular fa-heart"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?= htmlspecialchars($d['titre']) ?></h3>
                        <p class="card-author"><i class="fa-solid fa-user-pen"></i><?= htmlspecialchars($d['auteur'] ?? '') ?></p>
                        <div class="card-price-row">
                            <?php if ($can_buy && (float)$d['prix'] > 0): ?>
                                <span class="card-price"><?= number_format($d['prix'], 2) ?><span class="price-unit">DA</span></span>
                            <?php elseif ($can_borrow && !$can_buy): ?>
                                <span class="card-free"><i class="fa-solid fa-book-open"></i> Emprunt uniquement</span>
                            <?php else: ?>
                                <span class="card-free"><i class="fa-solid fa-lock-open"></i> Gratuit</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-divider"></div>
                        <?php if ($user_role === 'client'): ?>
                        <div class="card-actions">
                            <?php if ($can_borrow): ?>
                            <form action="../emprunts/emprunt.php" method="POST" style="flex:1;display:flex">
                                <input type="hidden" name="id_doc" value="<?= $d['id_doc'] ?>">
                                <button type="submit" name="emprunter" class="btn-card btn-borrow <?= !$can_buy ? 'full' : '' ?>">
                                    <i class="fa-regular fa-clock"></i> Emprunter
                                </button>
                            </form>
                            <?php endif; ?>
                            <?php if ($can_buy): ?>
                            <form action="../cart/add_to_cart.php" method="POST" style="flex:1;display:flex">
                                <input type="hidden" name="id_doc" value="<?= $d['id_doc'] ?>">
                                <button type="submit" class="btn-card btn-buy <?= !$can_borrow ? 'full' : '' ?>">
                                    <i class="fa-solid fa-cart-plus"></i> Acheter
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                        <?php elseif ($user_role === 'admin'): ?>
                        <div class="admin-actions">
                            <a href="../admin/modifier_document.php?id=<?= $d['id_doc'] ?>" class="btn-admin btn-edit"><i class="fa-solid fa-pen"></i> Modifier</a>
                            <a href="../admin/delete_doc.php?id=<?= $d['id_doc'] ?>" onclick="return confirm('Confirmer ?')" class="btn-admin btn-delete"><i class="fa-solid fa-trash"></i> Supprimer</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Extra 4 cards (hidden, smooth reveal) -->
    <?php if (!empty($docs_more)): ?>
    <div class="extra-grid" id="extraGrid">
        <?php foreach ($docs_more as $d):
            $dp         = $d['disponible_pour'] ?? 'both';
            $can_buy    = in_array($dp, ['achat', 'both']);
            $can_borrow = in_array($dp, ['emprunt', 'both']);
            $imgPath    = !empty($d['image_doc']) ? "../uploads/".$d['image_doc'] : "../uploads/default.png";
        ?>
        <div class="book-card">
            <div class="card-cover">
                <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($d['titre']) ?>" loading="lazy">
                <div class="avail-ribbon">
                    <?php if ($can_buy): ?>
                        <span class="avail-tag tag-buy"><i class="fa-solid fa-cart-shopping" style="font-size:8px"></i> Achat</span>
                    <?php endif; ?>
                    <?php if ($can_borrow): ?>
                        <span class="avail-tag tag-borrow"><i class="fa-regular fa-clock" style="font-size:8px"></i> Emprunt</span>
                    <?php endif; ?>
                </div>
                <span class="type-badge"><?= htmlspecialchars($d['libelle_type'] ?? '') ?></span>
                <?php if ($user_role === 'client'): ?>
                <a href="toggle_wishlist.php?id_doc=<?= $d['id_doc'] ?>" class="wish-btn" title="Favoris">
                    <i class="fa-regular fa-heart"></i>
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <h3 class="card-title"><?= htmlspecialchars($d['titre']) ?></h3>
                <p class="card-author"><i class="fa-solid fa-user-pen"></i><?= htmlspecialchars($d['auteur'] ?? '') ?></p>
                <div class="card-price-row">
                    <?php if ($can_buy && (float)$d['prix'] > 0): ?>
                        <span class="card-price"><?= number_format($d['prix'], 2) ?><span class="price-unit">DA</span></span>
                    <?php elseif ($can_borrow && !$can_buy): ?>
                        <span class="card-free"><i class="fa-solid fa-book-open"></i> Emprunt uniquement</span>
                    <?php else: ?>
                        <span class="card-free"><i class="fa-solid fa-lock-open"></i> Gratuit</span>
                    <?php endif; ?>
                </div>
                <div class="card-divider"></div>
                <?php if ($user_role === 'client'): ?>
                <div class="card-actions">
                    <?php if ($can_borrow): ?>
                    <form action="../emprunts/emprunt.php" method="POST" style="flex:1;display:flex">
                        <input type="hidden" name="id_doc" value="<?= $d['id_doc'] ?>">
                        <button type="submit" name="emprunter" class="btn-card btn-borrow <?= !$can_buy ? 'full' : '' ?>">
                            <i class="fa-regular fa-clock"></i> Emprunter
                        </button>
                    </form>
                    <?php endif; ?>
                    <?php if ($can_buy): ?>
                    <form action="../cart/add_to_cart.php" method="POST" style="flex:1;display:flex">
                        <input type="hidden" name="id_doc" value="<?= $d['id_doc'] ?>">
                        <button type="submit" class="btn-card btn-buy <?= !$can_borrow ? 'full' : '' ?>">
                            <i class="fa-solid fa-cart-plus"></i> Acheter
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php elseif ($user_role === 'admin'): ?>
                <div class="admin-actions">
                    <a href="../admin/modifier_document.php?id=<?= $d['id_doc'] ?>" class="btn-admin btn-edit"><i class="fa-solid fa-pen"></i> Modifier</a>
                    <a href="../admin/delete_doc.php?id=<?= $d['id_doc'] ?>" onclick="return confirm('Confirmer ?')" class="btn-admin btn-delete"><i class="fa-solid fa-trash"></i> Supprimer</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="more-wrap">
        <button class="btn-more" id="moreBtn" onclick="toggleMore()">
            <i class="fa-solid fa-chevron-down" id="moreIcon"></i>
            <span id="moreTxt">Voir plus de documents</span>
        </button>
    </div>
    <?php endif; ?>

    <!-- ── Categories ── -->
    <?php if (!empty($categories)): ?>
    <div class="cats-section">
        <div class="sec-head">
            <div class="sec-head-left">
                <h2>Parcourir par catégorie</h2>
                <p>Trouvez rapidement ce que vous cherchez</p>
            </div>
        </div>
        <div class="cat-grid">
            <?php
            $cat_icons = ['Livre'=>'📚','Thèse'=>'🎓','Article'=>'📄','Journal'=>'📰','Mémoire'=>'📝','Rapport'=>'📋'];
            foreach ($categories as $cat):
                $icon = $cat_icons[$cat['libelle_type']] ?? '📁';
            ?>
            <a href="library.php?type=<?= $cat['id_type'] ?>" class="cat-card">
                <div class="cat-icon"><?= $icon ?></div>
                <div>
                    <div class="cat-name"><?= htmlspecialchars($cat['libelle_type']) ?></div>
                    <div class="cat-count"><?= $cat['nb'] ?> document<?= $cat['nb'] != 1 ? 's' : '' ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── CTA banner (guests only) ── -->
    <?php if (!$is_logged_in): ?>
    <div class="cta-banner">
        <div class="cta-text">
            <h2>Accédez à toute la bibliothèque</h2>
            <p>Créez un compte gratuit et commencez à emprunter ou acheter vos ressources universitaires dès aujourd'hui.</p>
        </div>
        <a href="<?= $base ?>/auth/signup.php" class="btn-cta">
            Créer un compte <i class="fa-solid fa-arrow-right" style="font-size:12px"></i>
        </a>
    </div>
    <?php endif; ?>

</div><!-- /.page-wrap -->

<?php include '../includes/footer.php'; ?>

<script>
/* ── Voir plus toggle ──────────────────────────── */
function toggleMore() {
    const grid = document.getElementById('extraGrid');
    const btn  = document.getElementById('moreBtn');
    const txt  = document.getElementById('moreTxt');
    const icon = document.getElementById('moreIcon');
    const open = grid.classList.toggle('open');
    btn.classList.toggle('open', open);
    txt.textContent = open ? 'Voir moins' : 'Voir plus de documents';
}

/* ── Live search → redirect to library.php ────── */
let searchTimer;
document.getElementById('search').addEventListener('input', function () {
    clearTimeout(searchTimer);
    const val = this.value.trim();
    if (val.length >= 2) {
        searchTimer = setTimeout(() => {
            window.location.href = 'library.php?search=' + encodeURIComponent(val);
        }, 600);
    }
});
</script>

</body>
</html>
