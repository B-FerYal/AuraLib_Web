<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../includes/db.php";
include_once '../includes/languages.php';

$is_logged_in = isset($_SESSION['id_user']);
$id_user      = $is_logged_in ? (int)$_SESSION['id_user'] : 0;
$user_role    = strtolower(trim($_SESSION['role'] ?? 'client'));
$is_admin     = ($user_role === 'admin');
$is_client    = $is_logged_in && !$is_admin;

/* ── params ── */
$type_id = isset($_GET['type']) ? (int)$_GET['type'] : 0;
$avail   = $_GET['avail'] ?? 'all';
$sort    = $_GET['sort']  ?? 'newest';
$page    = max(1, (int)($_GET['page'] ?? 1));
$per     = 20;
$offset  = ($page - 1) * $per;

if (!$type_id) { header('Location: library.php'); exit; }

/* ── type info ── */
$rt = $conn->query("SELECT * FROM types_documents WHERE id_type = $type_id");
$type_row = $rt ? $rt->fetch_assoc() : null;
if (!$type_row) { header('Location: library.php'); exit; }
$type_label = htmlspecialchars($type_row['libelle_type']);

/* ── availability filter ── */
$avail_cond = '';
switch ($avail) {
    case 'buy':    $avail_cond = "AND d.disponible_pour IN ('achat','both')";   break;
    case 'borrow': $avail_cond = "AND d.disponible_pour IN ('emprunt','both')"; break;
    case 'both':   $avail_cond = "AND d.disponible_pour = 'both'";              break;
    default:       $avail_cond = ''; break;
}

/* ── sort ── */
$order = match($sort) {
    'title'      => 'd.titre ASC',
    'price_asc'  => 'd.prix ASC',
    'price_desc' => 'd.prix DESC',
    'oldest'     => 'd.id_doc ASC',
    default      => 'd.id_doc DESC',
};

/* ── total count ── */
$rc    = $conn->query("SELECT COUNT(*) as n FROM documents d WHERE d.id_type = $type_id $avail_cond");
$total = (int)($rc->fetch_assoc()['n'] ?? 0);
$total_pages = max(1, ceil($total / $per));

/* ── documents ── */
$sql = "SELECT d.*, t.libelle_type
        FROM documents d
        LEFT JOIN types_documents t ON d.id_type = t.id_type
        WHERE d.id_type = $type_id $avail_cond
        ORDER BY $order
        LIMIT $per OFFSET $offset";
$result    = $conn->query($sql);
$documents = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

function resolveImg($d) {
    $p = "../uploads/" . (int)$d['id_doc'] . ".jpg";
    if (!file_exists($p)) $p = !empty($d['image_doc']) ? "../uploads/".$d['image_doc'] : "../uploads/default.jpg";
    return $p;
}

function buildUrl($params) {
    $current = [
        'type'  => $_GET['type']  ?? '',
        'avail' => $_GET['avail'] ?? 'all',
        'sort'  => $_GET['sort']  ?? 'newest',
        'page'  => $_GET['page']  ?? 1,
    ];
    return $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($current, $params));
}
?>
<?php include '../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="<?= $lang ?? 'fr' ?>" dir="<?= ($lang ?? 'fr') == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
<meta charset="UTF-8">
<?php include '../includes/dark_init.php'; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AuraLib · <?= $type_label ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<link rel="stylesheet" href="/MEMOIR/css/aura-base.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.cat-hero { background: linear-gradient(135deg,#1A0E05 0%,#2E1D08 50%,#1A0E05 100%); padding:60px 5% 50px; position:relative; overflow:hidden; }
.cat-hero::before { content:''; position:absolute; inset:0; background:radial-gradient(ellipse 70% 80% at 15% 50%,rgba(196,164,107,.12) 0%,transparent 65%); pointer-events:none; }
.cat-hero::after  { content:''; position:absolute; bottom:0; left:0; right:0; height:1px; background:linear-gradient(90deg,transparent,rgba(196,164,107,.3),transparent); }
.cat-hero-inner { max-width:1380px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:24px; flex-wrap:wrap; animation:fadeUp .5s ease both; }
.cat-breadcrumb { display:flex; align-items:center; gap:8px; font-size:11px; color:rgba(196,164,107,.55); }
.cat-breadcrumb a { color:rgba(196,164,107,.55); text-decoration:none; transition:color .2s; }
.cat-breadcrumb a:hover { color:var(--gold); }
.cat-hero-title { font-family:var(--font-serif); font-size:clamp(32px,5vw,58px); font-weight:700; color:#FDFAF5; line-height:1.05; margin:10px 0 14px; }
.cat-hero-title span { background:linear-gradient(135deg,var(--gold) 0%,var(--gold2) 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
.cat-hero-count { font-family:var(--font-serif); font-size:16px; color:rgba(253,250,245,.55); }
.cat-hero-count strong { color:var(--gold); font-size:22px; }
.cat-hero-deco { display:flex; flex-direction:column; gap:6px; opacity:.18; flex-shrink:0; }
.cat-hero-deco span { display:block; height:2px; background:var(--gold); border-radius:2px; }

.cat-toolbar { position:sticky; top:var(--nav-h); z-index:90; background:var(--page-bg); border-bottom:1px solid var(--page-border); padding:12px 5%; display:flex; align-items:center; gap:12px; flex-wrap:wrap; transition:background .35s,box-shadow .3s; }
.cat-toolbar.scrolled { box-shadow:0 4px 20px rgba(42,31,20,.1); }
html.dark .cat-toolbar.scrolled { box-shadow:0 4px 20px rgba(0,0,0,.35); }
.toolbar-left  { display:flex; align-items:center; gap:8px; flex-wrap:wrap; flex:1; }
.toolbar-right { display:flex; align-items:center; gap:8px; flex-shrink:0; }
.sort-select-wrap { position:relative; flex-shrink:0; }
.sort-select-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--page-muted); font-size:11px; pointer-events:none; }
.sort-select { appearance:none; background:var(--page-white); border:1.5px solid var(--page-border); border-radius:50px; padding:8px 32px 8px 30px; font-family:var(--font-ui); font-size:11px; font-weight:600; color:var(--page-text); cursor:pointer; transition:border-color .2s; }
.sort-select:focus { outline:none; border-color:var(--gold-border); }
.sort-chevron { position:absolute; right:12px; top:50%; transform:translateY(-50%); color:var(--page-muted); font-size:9px; pointer-events:none; }
.back-btn { display:inline-flex; align-items:center; gap:7px; font-size:11px; font-weight:600; color:var(--page-muted); text-decoration:none; padding:7px 14px; border-radius:50px; border:1.5px solid var(--page-border); background:var(--page-white); transition:all .2s; flex-shrink:0; }
.back-btn:hover { color:var(--gold); border-color:var(--gold-border); background:var(--gold-faint); }

.cat-main { max-width:1380px; margin:0 auto; padding:36px 5% 80px; }

/* ── Pagination ── */
.pagination { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:52px; flex-wrap:wrap; }
.pg-btn { width:42px; height:42px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-family:var(--font-ui); font-size:13px; font-weight:700; text-decoration:none; cursor:pointer; transition:all .22s cubic-bezier(.4,0,.2,1); background:#2C1F0E; color:#EDE5D4; border:1.5px solid rgba(196,164,107,.18); box-shadow:0 2px 10px rgba(44,31,14,.25); line-height:1; }
html.dark .pg-btn { background:#1E1610; color:#C4A46B; border-color:rgba(196,164,107,.15); box-shadow:0 2px 10px rgba(0,0,0,.4); }
.pg-btn:hover:not(.active):not(.disabled) { background:#3A2A14; border-color:rgba(196,164,107,.4); color:var(--gold); transform:translateY(-2px); box-shadow:0 5px 16px rgba(44,31,14,.35); }
.pg-btn.active { background:var(--gold); border-color:var(--gold); color:#1A0E05; box-shadow:0 4px 18px rgba(196,164,107,.45); transform:scale(1.1); pointer-events:none; }
.pg-btn.disabled { opacity:.3; cursor:not-allowed; pointer-events:none; }

/* ── Result info ── */
.result-info { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; font-size:12px; color:var(--page-muted); }
.result-info strong { color:var(--page-text); }

/* ── Grid ── */
.cat-grid { display:grid !important; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:18px; }
@media(max-width:900px){ .cat-grid{ grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:14px; } }
@media(max-width:600px){ .cat-grid{ grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:12px; } }

/* ── Avail pills ── */
.avail-pill { display:inline-flex; align-items:center; gap:6px; padding:6px 16px; border-radius:50px; font-family:var(--font-ui); font-size:11px; font-weight:600; border:1.5px solid var(--page-border); background:var(--page-white); color:var(--page-muted); cursor:pointer; text-decoration:none; transition:all .25s; white-space:nowrap; user-select:none; }
.avail-pill:hover { border-color:var(--gold); color:var(--gold-deep); background:var(--gold-faint); }
.avail-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
.ap-all  .avail-dot { background:var(--page-muted); }
.ap-all.active  { background:var(--gold); border-color:var(--gold); color:#2C1F0E; font-weight:700; box-shadow:var(--shadow-gold); }
.ap-buy  .avail-dot { background:var(--gold); }
.ap-buy.active  { background:var(--gold); border-color:var(--gold); color:#2C1F0E; font-weight:700; box-shadow:var(--shadow-gold); }
.ap-borrow .avail-dot { background:var(--brown); }
.ap-borrow:hover  { border-color:var(--brown-border); color:var(--brown); background:var(--brown-faint); }
.ap-borrow.active { background:var(--brown); border-color:var(--brown); color:#fff; font-weight:700; }
.ap-both .avail-dot { background:linear-gradient(135deg,var(--gold) 50%,var(--brown) 50%); }
.ap-both.active { background:linear-gradient(110deg,var(--gold) 0%,var(--brown) 100%); border-color:transparent; color:#fff; font-weight:700; }

/* ── Book cards ── */
@keyframes cardIn { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
@keyframes fadeUp { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }
.book-card { background:var(--page-white); border-radius:var(--radius); border:1px solid var(--page-border); overflow:hidden; box-shadow:var(--shadow-sm); display:flex !important; flex-direction:column !important; transition:transform var(--tr),box-shadow var(--tr),border-color var(--tr); animation:cardIn .4s ease both; }
.book-card:hover { transform:translateY(-6px); box-shadow:var(--shadow-lg); border-color:var(--gold-border); }
.card-cover { position:relative; overflow:hidden; background:var(--page-bg2); display:block; text-decoration:none; flex-shrink:0; height:280px; }
.card-cover img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; display:block; transition:transform .6s cubic-bezier(.4,0,.2,1); }
.book-card:hover .card-cover img { transform:scale(1.06); }
@media(max-width:600px){ .card-cover { height:210px; } }

.avail-ribbon { position:absolute; top:10px; left:10px; display:flex; flex-direction:column; gap:5px; z-index:2; }
.avail-tag { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:9px; font-weight:700; letter-spacing:.8px; text-transform:uppercase; backdrop-filter:blur(10px); border:1px solid transparent; }
.tag-buy    { background:rgba(196,164,107,.9); color:#2C1F0E; border-color:rgba(196,164,107,.4); }
.tag-borrow { background:rgba(122,92,58,.88);  color:#F5EDD8; border-color:rgba(122,92,58,.4); }
.wish-btn { position:absolute; bottom:10px; right:10px; z-index:2; width:32px; height:32px; border-radius:50%; background:rgba(44,31,14,.6); backdrop-filter:blur(8px); border:1px solid rgba(196,164,107,.18); color:rgba(196,164,107,.45); font-size:13px; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all var(--tr); }
.wish-btn:hover { color:var(--gold); border-color:var(--gold-border); background:rgba(44,31,14,.85); }
.wish-btn.wishlisted { color:#ef4444; border-color:#fca5a5; }

.card-body { padding:12px 13px 14px; flex:1; display:flex !important; flex-direction:column !important; }
.card-title { font-family:var(--font-serif); font-size:15px; font-weight:600; color:var(--page-text); line-height:1.3; margin-bottom:3px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.card-title a { text-decoration:none; color:inherit; }
.card-title a:hover { color:var(--gold); }
.card-author { font-size:10px; color:var(--page-muted); margin-bottom:10px; }
.card-author i { margin-right:3px; font-size:9px; }
.card-price-row { margin-bottom:10px; }
.card-price { font-family:var(--font-serif); font-size:18px; font-weight:600; color:var(--amber); }
html.dark .card-price { color:var(--gold2); }
.price-unit { font-family:var(--font-ui); font-size:10px; font-weight:400; margin-left:3px; }
.card-free { font-size:11px; color:var(--page-muted); display:flex; align-items:center; gap:5px; }
.card-free i { color:var(--gold); font-size:10px; }
.card-divider { height:1px; background:var(--page-border); margin-bottom:10px; opacity:.6; }

.card-actions { display:flex !important; flex-direction:row !important; gap:6px; margin-top:auto; }
.btn-card { flex:1; display:flex; align-items:center; justify-content:center; gap:6px; padding:8px 6px; border-radius:9px; font-family:var(--font-ui); font-size:10px; font-weight:700; text-decoration:none; border:none; cursor:pointer; transition:all var(--tr); line-height:1; letter-spacing:.3px; }
.btn-card i { font-size:10px; }
.btn-borrow { background:var(--brown-faint); border:1.5px solid var(--brown-border); color:var(--brown); }
.btn-borrow:hover { background:var(--brown); color:#F5EDD8; border-color:var(--brown); }
.btn-buy { background:var(--gold-faint); border:1.5px solid var(--gold-border); color:var(--gold-deep); }
html.dark .btn-buy { color:var(--gold); }
.btn-buy:hover { background:var(--gold); color:#2C1F0E; border-color:var(--gold); box-shadow:var(--shadow-gold); }
.btn-card.full { flex:1 1 100%; }

.admin-actions { display:flex !important; flex-direction:row !important; gap:7px; margin-top:auto; }
.btn-admin { flex:1; display:flex; align-items:center; justify-content:center; gap:6px; padding:9px; border-radius:9px; font-family:var(--font-ui); font-size:11px; font-weight:700; text-decoration:none; transition:all var(--tr); border:1.5px solid transparent; }
.btn-edit   { background:var(--gold-faint); color:var(--gold); border-color:var(--gold-border); }
.btn-edit:hover { background:rgba(196,164,107,.18); transform:translateY(-1px); }
.btn-delete { background:rgba(192,57,43,.08); color:var(--danger); border-color:rgba(192,57,43,.2); cursor:pointer; }
.btn-delete:hover { background:rgba(192,57,43,.15); transform:translateY(-1px); }

.empty-state { grid-column:1/-1; text-align:center; padding:70px 20px; }
.empty-icon { font-size:44px; color:var(--page-border); margin-bottom:16px; }
.empty-state h3 { font-family:var(--font-serif); font-size:22px; color:var(--page-muted); margin-bottom:6px; }
.empty-state p  { font-size:13px; color:var(--page-muted); }
</style>
</head>
<body>

<div class="cat-hero">
    <div class="cat-hero-inner">
        <div class="cat-hero-left">
            <div class="cat-breadcrumb">
                <a href="/MEMOIR/client/library.php"><i class="fa-solid fa-house"></i> Accueil</a>
                <i class="fa-solid fa-chevron-right"></i>
                <span><?= $type_label ?></span>
            </div>
            <h1 class="cat-hero-title">Collection <span><?= $type_label ?></span></h1>
            <div class="cat-hero-count">
                <strong><?= $total ?></strong> document<?= $total > 1 ? 's' : '' ?> disponible<?= $total > 1 ? 's' : '' ?>
            </div>
        </div>
        <div class="cat-hero-deco">
            <span style="width:120px"></span><span style="width:80px"></span>
            <span style="width:100px"></span><span style="width:60px"></span>
        </div>
    </div>
</div>

<!-- ══ TOOLBAR ══ -->
<div class="cat-toolbar" id="toolbar">
    <div class="toolbar-left">
        <span style="font-size:9px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--page-muted);padding-right:4px;">Filtrer :</span>
        <?php
        $pills = [
            'all'    => 'Tout',
            'buy'    => 'Achat',
            'borrow' => 'Emprunt',
            'both'   => 'Achat &amp; Emprunt',
        ];
        foreach ($pills as $val => $label):
            $active = $avail === $val ? 'active' : '';
            $url    = buildUrl(['avail' => $val, 'page' => 1]);
        ?>
        <a href="<?= $url ?>" class="avail-pill ap-<?= $val ?> <?= $active ?>">
            <span class="avail-dot"></span> <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>
    <div class="toolbar-right">
        <div class="sort-select-wrap">
            <i class="fa-solid fa-arrow-up-wide-short"></i>
            <select class="sort-select" onchange="window.location.href=buildSortUrl(this.value)">
                <option value="newest"     <?= $sort==='newest'    ?'selected':'' ?>>Plus récents</option>
                <option value="oldest"     <?= $sort==='oldest'    ?'selected':'' ?>>Plus anciens</option>
                <option value="title"      <?= $sort==='title'     ?'selected':'' ?>>Titre A→Z</option>
                <option value="price_asc"  <?= $sort==='price_asc' ?'selected':'' ?>>Prix croissant</option>
                <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Prix décroissant</option>
            </select>
            <i class="fa-solid fa-chevron-down sort-chevron"></i>
        </div>
        <a href="/MEMOIR/client/library.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> Catalogue
        </a>
    </div>
</div>

<!-- ══ MAIN ══ -->
<div class="cat-main">
    <div class="result-info">
        <div class="result-count">
            <?php if ($total > 0): ?>
                Affichage <strong><?= $offset+1 ?>–<?= min($offset+$per,$total) ?></strong> sur <strong><?= $total ?></strong>
            <?php endif; ?>
        </div>
        <div style="font-size:11px;color:var(--page-muted)">Page <?= $page ?> / <?= $total_pages ?></div>
    </div>

    <div class="cat-grid">
        <?php if (empty($documents)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fa-regular fa-folder-open"></i></div>
            <h3>Aucun document trouvé</h3>
            <p>Essayez de changer les filtres ou revenez plus tard.</p>
        </div>
        <?php else: ?>
        <?php foreach ($documents as $d):
            $dp         = $d['disponible_pour'] ?? 'both';
            $can_buy    = in_array($dp, ['achat','both']);
            $can_borrow = in_array($dp, ['emprunt','both']);
            $is_both    = ($dp === 'both');
            $imgPath    = resolveImg($d);
            $detail_url = "/MEMOIR/client/doc_details.php?id=" . (int)$d['id_doc'];
        ?>
        <div class="book-card">
            <a href="<?= $detail_url ?>" class="card-cover">
                <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($d['titre']) ?>" loading="lazy" onerror="this.src='../uploads/default.jpg'">
                <div class="avail-ribbon">
                    <?php if ($can_buy): ?><span class="avail-tag tag-buy"><i class="fa-solid fa-cart-shopping" style="font-size:8px"></i> Achat</span><?php endif; ?>
                    <?php if ($can_borrow): ?><span class="avail-tag tag-borrow"><i class="fa-regular fa-clock" style="font-size:8px"></i> Emprunt</span><?php endif; ?>
                </div>
                <?php if ($is_client): ?>
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
                <?php if ($is_client): ?>
                <div class="card-actions">
                    <?php if ($is_both): ?>
                        <a href="../emprunts/emprunt.php?id_doc=<?= (int)$d['id_doc'] ?>" class="btn-card btn-borrow"><i class="fa-regular fa-clock"></i> Emprunter</a>
                        <form action="../cart/add_to_cart.php" method="POST" style="flex:1;display:flex">
                            <input type="hidden" name="id_doc" value="<?= (int)$d['id_doc'] ?>">
                            <button type="submit" class="btn-card btn-buy"><i class="fa-solid fa-cart-plus"></i> Acheter</button>
                        </form>
                    <?php else: ?>
                        <?php if ($can_borrow): ?>
                        <a href="../emprunts/emprunt.php?id_doc=<?= (int)$d['id_doc'] ?>" class="btn-card btn-borrow <?= !$can_buy?'full':'' ?>"><i class="fa-regular fa-clock"></i> Emprunter</a>
                        <?php endif; ?>
                        <?php if ($can_buy): ?>
                        <form action="../cart/add_to_cart.php" method="POST" style="flex:1;display:flex">
                            <input type="hidden" name="id_doc" value="<?= (int)$d['id_doc'] ?>">
                            <button type="submit" class="btn-card btn-buy <?= !$can_borrow?'full':'' ?>"><i class="fa-solid fa-cart-plus"></i> Acheter</button>
                        </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php elseif (!$is_logged_in): ?>
                <?php
                    $login_borrow = '/MEMOIR/auth/login.php?redirect=' . urlencode('/MEMOIR/emprunts/emprunt.php?id_doc=' . (int)$d['id_doc']);
                    $login_buy    = '/MEMOIR/auth/login.php?redirect=' . urlencode('/MEMOIR/cart/add_to_cart.php?id_doc=' . (int)$d['id_doc']);
                ?>
                <div class="card-actions">
                    <?php if ($is_both): ?>
                        <a href="<?= $login_borrow ?>" class="btn-card btn-borrow"><i class="fa-regular fa-clock"></i> Emprunter</a>
                        <a href="<?= $login_buy ?>" class="btn-card btn-buy"><i class="fa-solid fa-cart-plus"></i> Acheter</a>
                    <?php else: ?>
                        <?php if ($can_borrow): ?>
                        <a href="<?= $login_borrow ?>" class="btn-card btn-borrow <?= !$can_buy ? 'full' : '' ?>"><i class="fa-regular fa-clock"></i> Emprunter</a>
                        <?php endif; ?>
                        <?php if ($can_buy): ?>
                        <a href="<?= $login_buy ?>" class="btn-card btn-buy <?= !$can_borrow ? 'full' : '' ?>"><i class="fa-solid fa-cart-plus"></i> Acheter</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php elseif ($is_admin): ?>
                <div class="admin-actions">
                    <a href="/MEMOIR/admin/modifier_document.php?id=<?= (int)$d['id_doc'] ?>" class="btn-admin btn-edit"><i class="fa-solid fa-pen"></i> Modifier</a>
                    <button onclick="confirmDeleteDoc(<?= (int)$d['id_doc'] ?>)" class="btn-admin btn-delete"><i class="fa-solid fa-trash"></i> Supprimer</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="<?= buildUrl(['page'=>$page-1]) ?>" class="pg-btn"><i class="fa-solid fa-chevron-left" style="font-size:11px"></i></a>
        <?php else: ?>
        <span class="pg-btn disabled"><i class="fa-solid fa-chevron-left" style="font-size:11px"></i></span>
        <?php endif; ?>

        <?php
        $shown = []; $window = 2;
        for ($i=1; $i<=$total_pages; $i++) {
            if ($i===1 || $i===$total_pages || ($i>=$page-$window && $i<=$page+$window)) $shown[]=$i;
        }
        $prev = null;
        foreach ($shown as $pg_num):
            if ($prev !== null && $pg_num-$prev > 1) echo '<span class="pg-ellipsis">…</span>';
        ?>
        <a href="<?= buildUrl(['page'=>$pg_num]) ?>" class="pg-btn <?= $pg_num===$page?'active':'' ?>"><?= $pg_num ?></a>
        <?php $prev=$pg_num; endforeach; ?>

        <?php if ($page < $total_pages): ?>
        <a href="<?= buildUrl(['page'=>$page+1]) ?>" class="pg-btn"><i class="fa-solid fa-chevron-right" style="font-size:11px"></i></a>
        <?php else: ?>
        <span class="pg-btn disabled"><i class="fa-solid fa-chevron-right" style="font-size:11px"></i></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>

<script>
const toolbar = document.getElementById('toolbar');
window.addEventListener('scroll', () => {
    toolbar.classList.toggle('scrolled', window.scrollY > 30);
}, { passive: true });

function buildSortUrl(val) {
    const u = new URL(window.location.href);
    u.searchParams.set('sort', val);
    u.searchParams.set('page', 1);
    return u.toString();
}

function toggleWishlist(btn, id_doc) {
    fetch('/MEMOIR/client/toggle_wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_doc=' + id_doc
    })
    .then(r => r.json())
    .then(r => {
        const icon = btn.querySelector('i');
        if (r.status === 'added') { btn.classList.add('wishlisted'); if(icon) icon.className='fa-solid fa-heart'; }
        else { btn.classList.remove('wishlisted'); if(icon) icon.className='fa-regular fa-heart'; }
    });
}

function confirmDeleteDoc(id) {
    Swal.fire({
        title: 'Supprimer ce document ?', text: 'Cette action est irréversible.',
        icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Oui, supprimer', cancelButtonText: 'Annuler',
        confirmButtonColor: '#C0392B', cancelButtonColor: '#C4A46B',
        background: '#FFFDF9', color: '#2A1F14',
    }).then(result => {
        if (result.isConfirmed) window.location.href = '/MEMOIR/admin/delete_doc.php?id=' + id;
    });
}
</script>
</body>
</html>