<?php
session_start();
include "../includes/db.php";
include_once '../includes/languages.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: /MEMOIR/auth/login.php");
    exit;
}
$id_user   = (int)$_SESSION['id_user'];
$user_role = strtolower(trim($_SESSION['role'] ?? 'client'));

$pg = [
    'fr' => [
        'page_title'  => 'Mes Favoris',
        'subtitle'    => 'Les documents que vous avez sauvegardés',
        'empty_title' => 'Aucun favori pour l\'instant',
        'empty_sub'   => 'Parcourez le catalogue et cliquez sur ♥ pour sauvegarder vos coups de cœur.',
        'browse'      => 'Parcourir le catalogue',
        'remove'      => 'Retirer des favoris',
        'buy'         => 'Acheter',
        'borrow'      => 'Emprunter',
        'choose'      => 'Choisir',
        'free_loan'   => 'Emprunt gratuit',
        'tag_buy'     => 'Achat',
        'tag_borrow'  => 'Emprunt',
        'count'       => 'favori',
        'counts'      => 'favoris',
        'add_cart'    => 'Acheter',
        'home'        => 'Accueil',
        'removed_ok'  => 'Retiré des favoris',
        'sort_recent' => 'Plus récents',
        'sort_price'  => 'Prix',
        'filter'      => 'Filtrer',
    ],
    'en' => [
        'page_title'  => 'My Wishlist',
        'subtitle'    => 'Documents you have saved',
        'empty_title' => 'No favourites yet',
        'empty_sub'   => 'Browse the catalogue and click ♥ to save your favourites.',
        'browse'      => 'Browse catalogue',
        'remove'      => 'Remove',
        'buy'         => 'Buy',
        'borrow'      => 'Borrow',
        'choose'      => 'Choose',
        'free_loan'   => 'Free loan',
        'tag_buy'     => 'Purchase',
        'tag_borrow'  => 'Borrow',
        'count'       => 'favourite',
        'counts'      => 'favourites',
        'add_cart'    => 'Buy',
        'home'        => 'Home',
        'removed_ok'  => 'Removed from wishlist',
        'sort_recent' => 'Most recent',
        'sort_price'  => 'Price',
        'filter'      => 'Filter',
    ],
    'ar' => [
        'page_title'  => 'المفضلة',
        'subtitle'    => 'الوثائق التي حفظتها',
        'empty_title' => 'لا توجد مفضلة بعد',
        'empty_sub'   => 'تصفح الكتالوج واضغط ♥ لحفظ ما يعجبك.',
        'browse'      => 'تصفح الكتالوج',
        'remove'      => 'إزالة من المفضلة',
        'buy'         => 'شراء',
        'borrow'      => 'استعارة',
        'choose'      => 'اختر',
        'free_loan'   => 'استعارة مجانية',
        'tag_buy'     => 'شراء',
        'tag_borrow'  => 'استعارة',
        'count'       => 'مفضلة',
        'counts'      => 'مفضلة',
        'add_cart'    => 'شراء',
        'home'        => 'الرئيسية',
        'removed_ok'  => 'تمت الإزالة',
        'sort_recent' => 'الأحدث',
        'sort_price'  => 'السعر',
        'filter'      => 'تصفية',
    ],
];
$p = $pg[$lang] ?? $pg['fr'];

/* ── Fetch wishlist ── */
/* ── Fetch wishlist ── */
// On trie par id_wishlist DESC au lieu de date_ajout
$sql = "SELECT w.id_wishlist, d.*, t.libelle_type
        FROM wishlist w
        JOIN documents d ON w.id_doc = d.id_doc
        LEFT JOIN types_documents t ON d.id_type = t.id_type
        WHERE w.id_user = $id_user
        ORDER BY w.id_wishlist DESC"; 
$result   = $conn->query($sql);
$wishlist = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Vérifie que cette ligne est bien présente :
$total    = count($wishlist);
function resolveImg($d) {
    $path = "../uploads/" . (int)$d['id_doc'] . ".jpg";
    if (!file_exists($path))
        $path = !empty($d['image_doc']) ? "../uploads/" . $d['image_doc'] : "../uploads/default.jpg";
    return $path;
}
?>
<?php include '../includes/header.php'; ?>
<style>
:root {
    --gold:#C4A46B; --gold2:#D4B47B; --gold-deep:#A8884E;
    --gold-faint:rgba(196,164,107,.09); --gold-border:rgba(196,164,107,.28);
    --brown:#7A5C3A; --brown-faint:rgba(122,92,58,.09); --brown-border:rgba(122,92,58,.28);
    --amber:#B8832A; --nav-bg:#1A1008;
    --page-bg:#F2EDE3; --page-bg2:#E8E0D0; --page-white:#FDFAF5;
    --page-text:#2A1F14; --page-muted:#9A8C7E; --page-border:#D8CFC0;
    --danger:#C0392B;
    --font-serif:'EB Garamond',Georgia,serif;
    --font-ui:'Plus Jakarta Sans',sans-serif;
    --nav-h:62px; --radius:14px;
    --shadow-sm:0 3px 10px rgba(42,31,20,.08);
    --shadow-md:0 8px 28px rgba(42,31,20,.11);
    --shadow-lg:0 20px 55px rgba(42,31,20,.15);
    --shadow-gold:0 6px 20px rgba(196,164,107,.22);
    --tr:.25s cubic-bezier(.4,0,.2,1);
}
html.dark {
    --page-bg:#100C07; --page-bg2:#1A1308; --page-white:#1E1610;
    --page-text:#EDE5D4; --page-muted:#9A8C7E; --page-border:#3A2E1E;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:var(--font-ui);background:var(--page-bg);color:var(--page-text);padding-top:var(--nav-h);}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
@keyframes cardIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
@keyframes hpop{0%{transform:scale(1)}45%{transform:scale(1.5)}100%{transform:scale(1)}}

/* ══ HERO ══ */
.wl-hero{
    background:var(--nav-bg);
    padding:48px 6% 54px;
    position:relative;overflow:hidden;
}
.wl-hero::before{
    content:'';position:absolute;inset:0;pointer-events:none;
    background:
        radial-gradient(ellipse at 10% 70%,rgba(196,164,107,.07) 0%,transparent 50%),
        radial-gradient(ellipse at 90% 20%,rgba(122,92,58,.05) 0%,transparent 50%);
}
/* decorative lines */
.wl-hero::after{
    content:'';position:absolute;inset:0;pointer-events:none;
    background:repeating-linear-gradient(
        90deg,transparent,transparent 80px,
        rgba(196,164,107,.025) 80px,rgba(196,164,107,.025) 81px
    );
}
.wl-hero-inner{position:relative;z-index:1;max-width:1400px;margin:0 auto;}

.wl-breadcrumb{
    display:flex;align-items:center;gap:8px;
    font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;
    color:rgba(196,164,107,.38);margin-bottom:26px;
}
.wl-breadcrumb a{color:rgba(196,164,107,.38);text-decoration:none;transition:color var(--tr);}
.wl-breadcrumb a:hover{color:var(--gold);}

.wl-top-row{display:flex;align-items:flex-end;gap:20px;flex-wrap:wrap;}
.wl-left{display:flex;align-items:center;gap:18px;flex:1;min-width:0;}

.wl-icon-wrap{
    width:58px;height:58px;border-radius:50%;flex-shrink:0;
    background:rgba(196,164,107,.1);
    border:1.5px solid rgba(196,164,107,.2);
    display:flex;align-items:center;justify-content:center;
}
.wl-icon-wrap i{
    font-size:22px;color:#ef4444;
    animation:hpop .7s ease .2s both;
}
.wl-texts{}
.wl-h1{
    font-family:var(--font-serif);
    font-size:clamp(26px,4vw,44px);font-weight:700;
    color:#FDFAF5;line-height:1.1;margin-bottom:5px;
}
.wl-subtitle{font-size:13px;color:rgba(253,250,245,.38);}

.wl-meta{
    display:flex;align-items:center;gap:10px;flex-shrink:0;
}
.wl-pill{
    background:rgba(196,164,107,.1);
    border:1px solid rgba(196,164,107,.2);
    border-radius:50px;padding:8px 18px;
    display:flex;align-items:center;gap:8px;
    color:var(--gold);font-size:12px;font-weight:700;
}
.wl-pill-num{font-family:var(--font-serif);font-size:22px;line-height:1;}

/* ══ TOOLBAR ══ */
.wl-toolbar{
    max-width:1400px;margin:0 auto;
    padding:18px 6% 0;
    display:flex;align-items:center;justify-content:space-between;
    flex-wrap:wrap;gap:10px;
}
.wl-filter-tabs{display:flex;gap:6px;flex-wrap:wrap;}
.wl-filter-tab{
    padding:6px 14px;border-radius:50px;
    font-size:11px;font-weight:700;letter-spacing:.3px;
    border:1.5px solid var(--page-border);
    background:transparent;color:var(--page-muted);
    cursor:pointer;transition:all var(--tr);
}
.wl-filter-tab.active,
.wl-filter-tab:hover{
    background:var(--gold);color:#2C1F0E;
    border-color:var(--gold);
}

/* ══ MAIN ══ */
.wl-main{max-width:1400px;margin:0 auto;padding:24px 6% 80px;}

/* ══ GRID ══ */
.wl-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
    gap:20px;
}
@media(max-width:900px){.wl-grid{grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;}}
@media(max-width:500px){.wl-grid{grid-template-columns:repeat(2,1fr);gap:12px;}}

.card-wrap{display:flex;flex-direction:column;}
.card-wrap[data-hidden="true"]{display:none;}

/* ══ BOOK CARD ══ */
.book-card{
    background:var(--page-white);border-radius:var(--radius);
    border:1px solid var(--page-border);overflow:hidden;
    box-shadow:var(--shadow-sm);display:flex;flex-direction:column;
    transition:transform var(--tr),box-shadow var(--tr),border-color var(--tr);
    animation:cardIn .4s ease both;
}
.book-card:hover{transform:translateY(-6px);box-shadow:var(--shadow-lg);border-color:var(--gold-border);}
.book-card:nth-child(2){animation-delay:.04s}
.book-card:nth-child(3){animation-delay:.08s}
.book-card:nth-child(4){animation-delay:.12s}
.book-card:nth-child(5){animation-delay:.16s}
.book-card:nth-child(6){animation-delay:.20s}

.card-cover{
    position:relative;overflow:hidden;background:var(--page-bg2);
    display:block;text-decoration:none;flex-shrink:0;
    height:290px;
}
.card-cover img{
    position:absolute;inset:0;width:100%;height:100%;
    object-fit:cover;display:block;
    transition:transform .55s cubic-bezier(.4,0,.2,1);
}
.book-card:hover .card-cover img{transform:scale(1.07);}

/* overlay on hover */
.card-cover::after{
    content:'';position:absolute;inset:0;
    background:linear-gradient(to top,rgba(26,16,8,.55) 0%,transparent 55%);
    opacity:0;transition:opacity var(--tr);pointer-events:none;
}
.book-card:hover .card-cover::after{opacity:1;}

.avail-ribbon{position:absolute;top:10px;left:10px;display:flex;flex-direction:column;gap:4px;z-index:2;}
.avail-tag{
    display:inline-flex;align-items:center;gap:5px;
    padding:4px 10px;border-radius:20px;
    font-size:9px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;
    backdrop-filter:blur(10px);border:1px solid transparent;
}
.tag-buy   {background:rgba(196,164,107,.9);color:#2C1F0E;border-color:rgba(196,164,107,.4);}
.tag-borrow{background:rgba(122,92,58,.88); color:#F5EDD8;border-color:rgba(122,92,58,.4);}

/* X button top-right cover */
.cover-rm{
    position:absolute;top:10px;right:10px;z-index:3;
    width:28px;height:28px;border-radius:50%;
    background:rgba(192,57,43,.82);backdrop-filter:blur(6px);
    border:1px solid rgba(255,255,255,.12);color:#fff;
    font-size:10px;display:flex;align-items:center;justify-content:center;
    cursor:pointer;transition:all var(--tr);
    opacity:0;transform:scale(.75);
}
.book-card:hover .cover-rm{opacity:1;transform:scale(1);}
.cover-rm:hover{background:var(--danger);transform:scale(1.12)!important;}

/* heart badge bottom-right */
.wish-heart{
    position:absolute;bottom:10px;right:10px;z-index:2;
    width:28px;height:28px;border-radius:50%;
    background:rgba(239,68,68,.18);border:1px solid rgba(239,68,68,.35);
    color:#ef4444;font-size:11px;
    display:flex;align-items:center;justify-content:center;
}

/* body */
.card-body{padding:12px 13px 14px;flex:1;display:flex;flex-direction:column;}
.card-title{
    font-family:var(--font-serif);font-size:15px;font-weight:600;
    color:var(--page-text);line-height:1.3;margin-bottom:3px;
    display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}
.card-title a{text-decoration:none;color:inherit;}
.card-title a:hover{color:var(--gold);}
.card-author{font-size:10px;color:var(--page-muted);margin-bottom:10px;}
.card-author i{margin-right:3px;font-size:9px;}
.card-price-row{margin-bottom:10px;}
.card-price{font-family:var(--font-serif);font-size:18px;font-weight:600;color:var(--amber);}
html.dark .card-price{color:var(--gold2);}
.price-unit{font-family:var(--font-ui);font-size:10px;font-weight:400;margin-left:3px;}
.card-free{font-size:11px;color:var(--page-muted);display:flex;align-items:center;gap:5px;}
.card-free i{color:var(--gold);font-size:10px;}
.card-divider{height:1px;background:var(--page-border);margin-bottom:10px;opacity:.6;}

/* action buttons */
.card-actions{display:flex;gap:6px;margin-top:auto;}
.btn-card{
    flex:1;display:flex;align-items:center;justify-content:center;gap:6px;
    padding:8px 6px;border-radius:9px;
    font-family:var(--font-ui);font-size:10px;font-weight:700;
    text-decoration:none;border:none;cursor:pointer;
    transition:all var(--tr);line-height:1;letter-spacing:.3px;
}
.btn-card i{font-size:10px;}
.btn-card.full{flex:1 1 100%;}
.btn-borrow{background:var(--brown-faint);border:1.5px solid var(--brown-border);color:var(--brown);}
.btn-borrow:hover{background:var(--brown);color:#F5EDD8;border-color:var(--brown);}
.btn-buy{background:var(--gold-faint);border:1.5px solid var(--gold-border);color:var(--gold-deep);}
html.dark .btn-buy{color:var(--gold);}
.btn-buy:hover{background:var(--gold);color:#2C1F0E;border-color:var(--gold);box-shadow:var(--shadow-gold);}
.btn-both{background:linear-gradient(110deg,var(--gold-faint),var(--brown-faint));border:1.5px solid var(--gold-border);color:var(--gold-deep);}
html.dark .btn-both{color:var(--gold);}
.btn-both:hover{background:linear-gradient(110deg,var(--gold),var(--brown));color:#fff;border-color:transparent;}

.btn-both-wrap{position:relative;flex:1;}
.both-menu{
    position:absolute;bottom:calc(100% + 8px);left:0;right:0;
    background:var(--page-white);border:1.5px solid var(--gold-border);
    border-radius:12px;box-shadow:0 12px 36px rgba(42,31,20,.2);
    overflow:hidden;z-index:50;display:none;
}
.both-menu.open{display:block;}
.both-opt{
    display:flex;align-items:center;gap:10px;width:100%;
    padding:10px 14px;font-family:var(--font-ui);font-size:11px;font-weight:600;
    color:var(--page-text);text-decoration:none;background:transparent;
    border:none;cursor:pointer;transition:background var(--tr);
    border-bottom:1px solid var(--page-border);
}
.both-opt:last-child{border-bottom:none;}
.both-opt:hover{background:var(--gold-faint);}
.both-opt i{color:var(--gold);font-size:12px;}

/* remove row */
.remove-row{display:flex;justify-content:center;margin-top:7px;}
.remove-link{
    font-size:10px;font-weight:600;color:var(--page-muted);
    display:flex;align-items:center;gap:5px;
    background:none;border:none;cursor:pointer;
    padding:5px 10px;border-radius:8px;
    transition:color var(--tr),background var(--tr);
}
.remove-link:hover{color:var(--danger);background:rgba(192,57,43,.07);}
.remove-link i{font-size:9px;}

/* ══ EMPTY STATE ══ */
.wl-empty{
    text-align:center;padding:90px 20px;
    animation:fadeUp .5s ease both;
}
.wl-empty-icon{
    width:84px;height:84px;border-radius:50%;
    background:var(--gold-faint);border:1.5px solid var(--gold-border);
    display:flex;align-items:center;justify-content:center;
    font-size:32px;color:var(--gold);margin:0 auto 24px;
}
.wl-empty h2{font-family:var(--font-serif);font-size:26px;font-weight:600;color:var(--page-muted);margin-bottom:10px;}
.wl-empty p{font-size:13px;color:var(--page-muted);max-width:360px;margin:0 auto 28px;line-height:1.7;}
.btn-browse{
    display:inline-flex;align-items:center;gap:9px;
    padding:13px 30px;border-radius:50px;
    background:var(--gold);color:#2C1F0E;
    font-family:var(--font-ui);font-size:12px;font-weight:700;
    text-decoration:none;letter-spacing:.3px;
    transition:all var(--tr);box-shadow:var(--shadow-gold);
}
.btn-browse:hover{background:var(--gold2);transform:translateY(-2px);}

/* ══ TOAST ══ */
.wl-toast{
    position:fixed;bottom:28px;left:50%;
    transform:translateX(-50%) translateY(14px);
    background:var(--nav-bg);color:#FDFAF5;
    border:1px solid rgba(196,164,107,.22);border-radius:50px;
    padding:11px 24px;font-size:12px;font-weight:600;
    display:flex;align-items:center;gap:9px;
    opacity:0;pointer-events:none;z-index:9999;
    transition:opacity .25s,transform .25s;
    box-shadow:var(--shadow-md);
}
.wl-toast.show{opacity:1;transform:translateX(-50%) translateY(0);}
.wl-toast i{color:#ef4444;}
</style>

<!-- ══ HERO ══ -->
<div class="wl-hero">
    <div class="wl-hero-inner">
        <div class="wl-breadcrumb">
            <a href="/MEMOIR/client/library.php"><i class="fa-solid fa-house"></i> <?= $p['home'] ?></a>
            <i class="fa-solid fa-chevron-right" style="font-size:7px"></i>
            <span><?= $p['page_title'] ?></span>
        </div>
        <div class="wl-top-row" style="animation:fadeUp .45s ease both;">
            <div class="wl-left">
                <div class="wl-icon-wrap">
                    <i class="fa-solid fa-heart"></i>
                </div>
                <div class="wl-texts">
                    <h1 class="wl-h1"><?= htmlspecialchars($p['page_title']) ?></h1>
                    <p class="wl-subtitle"><?= htmlspecialchars($p['subtitle']) ?></p>
                </div>
            </div>
            <?php if ($total > 0): ?>
            <div class="wl-meta">
                <div class="wl-pill">
                    <i class="fa-solid fa-heart" style="font-size:11px;color:#ef4444"></i>
                    <span class="wl-pill-num" id="wlCount"><?= $total ?></span>
                    <span><?= $total > 1 ? $p['counts'] : $p['count'] ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (isset($total) && $total > 0): ?>
<div class="wl-toolbar">
    <div class="wl-filter-tabs">
        <button class="wl-filter-tab active" data-filter="all"><?= $p['filter'] ?> : Tout</button>
        <button class="wl-filter-tab" data-filter="achat">
            <i class="fa-solid fa-cart-shopping" style="font-size:9px"></i> <?= $p['tag_buy'] ?>
        </button>
        <button class="wl-filter-tab" data-filter="emprunt">
            <i class="fa-regular fa-clock" style="font-size:9px"></i> <?= $p['tag_borrow'] ?>
        </button>
        <button class="wl-filter-tab" data-filter="both">Les deux</button>
    </div>
</div>
<?php endif; ?>

<!-- ══ GRID ══ -->
<div class="wl-main">
<?php if (!empty($wishlist)): ?>
<div class="wl-grid" id="wlGrid">
<?php foreach ($wishlist as $i => $d):
    $dp         = $d['disponible_pour'] ?? 'both';
    $can_buy    = in_array($dp, ['achat','both']);
    $can_borrow = in_array($dp, ['emprunt','both']);
    $is_both    = ($dp === 'both');
    $imgPath    = resolveImg($d);
    $detail_url = "/MEMOIR/client/doc_details.php?id=" . (int)$d['id_doc'];
    $id_doc     = (int)$d['id_doc'];
    $id_w       = (int)$d['id_wishlist'];
?>
<div class="card-wrap" id="cwrap-<?= $id_doc ?>" data-dp="<?= htmlspecialchars($dp) ?>">
    <div class="book-card">

        <a href="<?= $detail_url ?>" class="card-cover">
            <img src="<?= htmlspecialchars($imgPath) ?>"
                 alt="<?= htmlspecialchars($d['titre']) ?>"
                 loading="lazy"
                 onerror="this.src='../uploads/default.jpg'">

            <div class="avail-ribbon">
                <?php if ($can_buy): ?>
                <span class="avail-tag tag-buy">
                    <i class="fa-solid fa-cart-shopping" style="font-size:8px"></i> <?= $p['tag_buy'] ?>
                </span>
                <?php endif; ?>
                <?php if ($can_borrow): ?>
                <span class="avail-tag tag-borrow">
                    <i class="fa-regular fa-clock" style="font-size:8px"></i> <?= $p['tag_borrow'] ?>
                </span>
                <?php endif; ?>
            </div>

            <button class="cover-rm"
                    onclick="event.preventDefault(); doRemove(<?= $id_doc ?>)"
                    title="<?= htmlspecialchars($p['remove']) ?>">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="wish-heart"><i class="fa-solid fa-heart"></i></div>
        </a>

        <div class="card-body">
            <h3 class="card-title">
                <a href="<?= $detail_url ?>"><?= htmlspecialchars($d['titre']) ?></a>
            </h3>
            <p class="card-author">
                <i class="fa-solid fa-user-pen"></i> <?= htmlspecialchars($d['auteur'] ?? '') ?>
            </p>
            <div class="card-price-row">
                <?php if ($can_buy && (float)$d['prix'] > 0): ?>
                    <span class="card-price">
                        <?= number_format((float)$d['prix'], 0, ',', ' ') ?>
                        <span class="price-unit">DA</span>
                    </span>
                <?php elseif ($can_borrow && !$can_buy): ?>
                    <span class="card-free">
                        <i class="fa-solid fa-book-open"></i> <?= $p['free_loan'] ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-divider"></div>
            <div class="card-actions">
                <?php if ($is_both): ?>
                <div class="btn-both-wrap">
                    <button class="btn-card btn-both full"
                            onclick="toggleBothMenu(this, <?= $id_doc ?>)">
                        <i class="fa-solid fa-plus"></i> <?= $p['choose'] ?>
                    </button>
                    <div class="both-menu" id="both-menu-<?= $id_doc ?>">
                        <a href="../emprunts/emprunt.php?id_doc=<?= $id_doc ?>" class="both-opt">
                            <i class="fa-regular fa-clock"></i> <?= $p['borrow'] ?>
                        </a>
                        <div class="both-opt" style="padding:0;">
                            <form action="../cart/add_to_cart.php" method="POST" style="width:100%;">
                                <input type="hidden" name="id_doc" value="<?= $id_doc ?>">
                                <button type="submit" style="all:unset;display:flex;align-items:center;gap:10px;width:100%;padding:10px 14px;font-family:var(--font-ui);font-size:11px;font-weight:600;color:var(--page-text);cursor:pointer;">
                                    <i class="fa-solid fa-cart-plus" style="color:var(--gold);font-size:12px;"></i>
                                    <?= $p['add_cart'] ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                    <?php if ($can_borrow): ?>
                    <a href="../emprunts/emprunt.php?id_doc=<?= $id_doc ?>"
                       class="btn-card btn-borrow <?= !$can_buy ? 'full' : '' ?>">
                        <i class="fa-regular fa-clock"></i> <?= $p['borrow'] ?>
                    </a>
                    <?php endif; ?>
                    <?php if ($can_buy): ?>
                    <form action="../cart/add_to_cart.php" method="POST"
                          style="flex:<?= !$can_borrow ? '1 1 100%' : '1' ?>;display:flex;">
                        <input type="hidden" name="id_doc" value="<?= $id_doc ?>">
                        <button type="submit" class="btn-card btn-buy <?= !$can_borrow ? 'full' : '' ?>">
                            <i class="fa-solid fa-cart-plus"></i> <?= $p['add_cart'] ?>
                        </button>
                    </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="remove-row">
        <button class="remove-link" onclick="doRemove(<?= $id_doc ?>)">
            <i class="fa-regular fa-heart-crack"></i> <?= $p['remove'] ?>
        </button>
    </div>
</div>
<?php endforeach; ?>
</div>

<?php else: ?>
<div class="wl-empty">
    <div class="wl-empty-icon"><i class="fa-regular fa-heart"></i></div>
    <h2><?= htmlspecialchars($p['empty_title']) ?></h2>
    <p><?= htmlspecialchars($p['empty_sub']) ?></p>
    <a href="/MEMOIR/client/library.php" class="btn-browse">
        <i class="fa-solid fa-book-open-reader"></i> <?= $p['browse'] ?>
    </a>
</div>
<?php endif; ?>
</div>

<!-- TOAST -->
<div class="wl-toast" id="wlToast">
    <i class="fa-solid fa-heart-crack"></i>
    <span id="wlToastMsg"></span>
</div>

<?php include '../includes/footer.php'; ?>
<script>
const MSG_REMOVED   = <?= json_encode($p['removed_ok']) ?>;
const MSG_EMPTY_T   = <?= json_encode($p['empty_title']) ?>;
const MSG_EMPTY_S   = <?= json_encode($p['empty_sub']) ?>;
const MSG_BROWSE    = <?= json_encode($p['browse']) ?>;

/* ── Remove ── */
function doRemove(id_doc) {
    const wrap = document.getElementById('cwrap-' + id_doc);
    if (!wrap) return;
    wrap.style.transition = 'opacity .28s, transform .28s';
    wrap.style.opacity    = '0';
    wrap.style.transform  = 'scale(.92)';

    fetch('/MEMOIR/client/toggle_wishlist.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'id_doc=' + id_doc
    })
    .then(r => r.json())
    .then(() => {
        setTimeout(() => {
            wrap.remove();
            const c = document.getElementById('wlCount');
            if (c) {
                const n = parseInt(c.textContent) - 1;
                c.textContent = n;
                if (n <= 0) showEmptyState();
            } else {
                showEmptyState();
            }
            showToast(MSG_REMOVED);
        }, 290);
    })
    .catch(() => {
        wrap.style.opacity   = '1';
        wrap.style.transform = '';
    });
}

function showEmptyState() {
    const grid = document.getElementById('wlGrid');
    if (grid) {
        grid.outerHTML = `<div class="wl-empty">
            <div class="wl-empty-icon"><i class="fa-regular fa-heart"></i></div>
            <h2>${MSG_EMPTY_T}</h2><p>${MSG_EMPTY_S}</p>
            <a href="/MEMOIR/client/library.php" class="btn-browse">
                <i class="fa-solid fa-book-open-reader"></i> ${MSG_BROWSE}
            </a>
        </div>`;
    }
    document.querySelector('.wl-pill')?.remove();
    document.querySelector('.wl-toolbar')?.remove();
}

/* ── Filter tabs ── */
document.querySelectorAll('.wl-filter-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.wl-filter-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        const f = tab.dataset.filter;
        document.querySelectorAll('.card-wrap').forEach(w => {
            const dp = w.dataset.dp;
            const show = f === 'all' || dp === f || (f === 'both' && dp === 'both');
            w.dataset.hidden = show ? 'false' : 'true';
            w.style.display  = show ? '' : 'none';
        });
    });
});

/* ── Both-menu toggle ── */
function toggleBothMenu(btn, id) {
    const menu = document.getElementById('both-menu-' + id);
    if (!menu) return;
    document.querySelectorAll('.both-menu.open').forEach(m => { if(m!==menu) m.classList.remove('open'); });
    menu.classList.toggle('open');
    if (menu.classList.contains('open')) {
        setTimeout(() => {
            document.addEventListener('click', function cl(e) {
                if (!menu.contains(e.target) && e.target !== btn) {
                    menu.classList.remove('open');
                    document.removeEventListener('click', cl);
                }
            });
        }, 10);
    }
}

/* ── Toast ── */
function showToast(msg) {
    const t = document.getElementById('wlToast');
    document.getElementById('wlToastMsg').textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2800);
}
</script>