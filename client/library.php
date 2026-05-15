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

// ── نصوص الصفحة حسب اللغة — AJOUT UNIQUEMENT ──────────
$pg = [
    'fr' => [
        'search_ph'      => 'Titre, auteur, ISBN, prix, année, éditeur…',
        'search_btn'     => 'Rechercher',
        'clear_title'    => 'Effacer',
        'filter_lbl'     => 'Filtrer :',
        'f_all'          => 'Tout',
        'f_buy'          => 'Achat',
        'f_borrow'       => 'Emprunt',
        'f_both'         => 'Achat &amp; Emprunt',
        'details'        => 'Détails',
        'borrow'         => 'Emprunter',
        'buy'            => 'Acheter',
        'choose'         => 'Choisir',
        'add_cart'       => 'Acheter',
        'login_req'      => 'Connexion requise',
        'edit'           => 'Modifier',
        'delete'         => 'Supprimer',
        'delete_confirm' => 'Supprimer ce document ?',
        'free_loan'      => 'Emprunt gratuit',
        'free'           => 'Gratuit',
        'tag_buy'        => 'Achat',
        'tag_borrow'     => 'Emprunt',
        'see_all'        => 'Voir tout',
        'no_docs'        => 'Aucun document dans le catalogue',
        'no_docs_sub'    => 'Ajoutez des documents pour les voir apparaître ici.',
        'sd_loading'     => 'Recherche…',
        'sd_empty'       => 'Aucun résultat pour',
        'sd_error'       => 'Erreur de chargement',
        'sd_close'       => '✕ Fermer',
        'sd_result'      => 'résultat',
        'sd_results'     => 'résultats',
        'sd_buy'         => 'Achat',
        'sd_borrow'      => 'Emprunt',
        'sd_both'        => 'Achat &amp; Emprunt',
        'doc_s'          => 's',
    ],
    'en' => [
        'search_ph'      => 'Title, author, ISBN, price, year, publisher…',
        'search_btn'     => 'Search',
        'clear_title'    => 'Clear',
        'filter_lbl'     => 'Filter:',
        'f_all'          => 'All',
        'f_buy'          => 'Purchase',
        'f_borrow'       => 'Borrow',
        'f_both'         => 'Purchase &amp; Borrow',
        'details'        => 'Details',
        'borrow'         => 'Borrow',
        'buy'            => 'Buy',
        'choose'         => 'Choose',
        'add_cart'       => 'Buy',
        'login_req'      => 'Login required',
        'edit'           => 'Edit',
        'delete'         => 'Delete',
        'delete_confirm' => 'Delete this document?',
        'free_loan'      => 'Free loan',
        'free'           => 'Free',
        'tag_buy'        => 'Purchase',
        'tag_borrow'     => 'Borrow',
        'see_all'        => 'See all',
        'no_docs'        => 'No documents in the catalogue',
        'no_docs_sub'    => 'Add documents to see them appear here.',
        'sd_loading'     => 'Searching…',
        'sd_empty'       => 'No results for',
        'sd_error'       => 'Loading error',
        'sd_close'       => '✕ Close',
        'sd_result'      => 'result',
        'sd_results'     => 'results',
        'sd_buy'         => 'Purchase',
        'sd_borrow'      => 'Borrow',
        'sd_both'        => 'Purchase &amp; Borrow',
        'doc_s'          => 's',
    ],
    'ar' => [
        'search_ph'      => 'العنوان، المؤلف، ISBN، السعر، السنة…',
        'search_btn'     => 'بحث',
        'clear_title'    => 'مسح',
        'filter_lbl'     => 'تصفية:',
        'f_all'          => 'الكل',
        'f_buy'          => 'شراء',
        'f_borrow'       => 'استعارة',
        'f_both'         => 'شراء &amp; استعارة',
        'details'        => 'تفاصيل',
        'borrow'         => 'استعارة',
        'buy'            => 'شراء',
        'choose'         => 'اختر',
        'add_cart'       => 'شراء',
        'login_req'      => 'تسجيل الدخول مطلوب',
        'edit'           => 'تعديل',
        'delete'         => 'حذف',
        'delete_confirm' => 'حذف هذا الكتاب؟',
        'free_loan'      => 'استعارة مجانية',
        'free'           => 'مجاني',
        'tag_buy'        => 'شراء',
        'tag_borrow'     => 'استعارة',
        'see_all'        => 'عرض الكل',
        'no_docs'        => 'لا توجد وثائق في الكتالوج',
        'no_docs_sub'    => 'أضف وثائق لتظهر هنا.',
        'sd_loading'     => 'جارٍ البحث…',
        'sd_empty'       => 'لا توجد نتائج لـ',
        'sd_error'       => 'خطأ في التحميل',
        'sd_close'       => '✕ إغلاق',
        'sd_result'      => 'نتيجة',
        'sd_results'     => 'نتائج',
        'sd_buy'         => 'شراء',
        'sd_borrow'      => 'استعارة',
        'sd_both'        => 'شراء &amp; استعارة',
        'doc_s'          => '',
    ],
];
$p = $pg[$lang] ?? $pg['fr'];
// ── FIN AJOUT ───────────────────────────────────────────

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

/* ══ CATALOGUE BY CATEGORY — 4 docs per type ══ */
$q_types->data_seek(0);
$all_types = $q_types->fetch_all(MYSQLI_ASSOC);

$sections = [];
foreach ($all_types as $t) {
    $tid  = (int)$t['id_type'];
    $avail_cond2 = '';
    switch ($avail) {
        case 'buy':    $avail_cond2 = "AND d.disponible_pour IN ('achat','both')";   break;
        case 'borrow': $avail_cond2 = "AND d.disponible_pour IN ('emprunt','both')"; break;
        case 'both':   $avail_cond2 = "AND d.disponible_pour = 'both'";              break;
    }
    $sql2 = "SELECT d.*, t2.libelle_type FROM documents d
             LEFT JOIN types_documents t2 ON d.id_type = t2.id_type
             WHERE d.id_type = $tid $avail_cond2
             ORDER BY d.id_doc DESC LIMIT 4";
    $r2 = $conn->query($sql2);
    if (!$r2 || $r2->num_rows === 0) continue;
    $rows  = $r2->fetch_all(MYSQLI_ASSOC);
    $rc2   = $conn->query("SELECT COUNT(*) as n FROM documents d WHERE d.id_type = $tid $avail_cond2");
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
<?php include '../includes/dark_init.php'; ?>
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
html.dark .hs-slide::after { background: linear-gradient(0deg, var(--page-bg) 0%, transparent 100%); }
.hs-content {
    position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    padding: 52px 7% 80px; z-index: 3;
    display: flex; flex-direction: column; align-items: flex-start;
    justify-content: flex-start; max-width: 680px;
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
    background: rgba(253,250,245,.1); backdrop-filter: blur(16px) saturate(1.4);
    border: 1.5px solid rgba(253,250,245,.22); color: rgba(253,250,245,.9);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.1), 0 8px 24px rgba(0,0,0,.25);
}
.hs-btn-details:hover { background: rgba(253,250,245,.18); border-color: rgba(196,164,107,.5); color: var(--gold2); transform: translateY(-2px); }
.hs-btn-borrow { background: rgba(122,92,58,.25); backdrop-filter: blur(12px); border: 1.5px solid rgba(122,92,58,.4); color: #F5EDD8; }
.hs-btn-borrow:hover { background: rgba(122,92,58,.45); transform: translateY(-2px); }

.swiper-pagination-bullets.hs-pagination {
    position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%);
    z-index: 20; display: flex; gap: 8px;
}
.hs-pagination .swiper-pagination-bullet { width: 8px; height: 8px; background: rgba(196,164,107,.3); opacity: 1; border-radius: 50%; transition: all 0.3s ease; cursor: pointer; }
.hs-pagination .swiper-pagination-bullet-active { background: var(--gold); width: 24px; border-radius: 4px; }

.hs-thumbs {
    position: absolute; right: 28px; top: 50%; transform: translateY(-50%);
    z-index: 10; display: flex; flex-direction: column; gap: 10px;
}
.hs-thumb { width: 52px; height: 70px; border-radius: 7px; overflow: hidden; cursor: pointer; opacity: .38; border: 1.5px solid transparent; transition: opacity .3s, border-color .3s, transform .3s; flex-shrink: 0; }
.hs-thumb img { width: 100%; height: 100%; object-fit: cover; }
.hs-thumb.active-thumb { opacity:1; border-color:var(--gold); transform:scale(1.08); box-shadow:0 4px 14px rgba(196,164,107,.3); }
.hs-thumb:hover:not(.active-thumb) { opacity: .65; }
.hs-arrow {
    position: absolute; top: 50%; transform: translateY(-50%); z-index: 10;
    width: 40px; height: 40px; border-radius: 50%;
    background: rgba(196,164,107,.12); backdrop-filter: blur(10px);
    border: 1px solid rgba(196,164,107,.22); color: rgba(196,164,107,.7);
    font-size: 13px; display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all var(--tr);
}
.hs-arrow:hover { background: rgba(196,164,107,.25); color: var(--gold); border-color: var(--gold-border); }
.hs-arrow-prev { left: 24px; }
.hs-arrow-next { right: 72px; }
@media (max-width: 768px) { .hs-thumbs { display: none; } .hs-content { padding: 40px 5% 60px; } .hs-arrow-prev { left: 14px; } .hs-arrow-next { left: 60px; } }

/* ══════════════════════════════════════════════
   STICKY SEARCH BAR — hidden for admin
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
    border-radius: 50px; padding: 0 6px 0 42px;
    position: relative; /* dropdown anchor */
    transition: border-color var(--tr), box-shadow var(--tr);
}
.search-input-wrap:focus-within { border-color: var(--gold-border); box-shadow: 0 0 0 3px rgba(196,164,107,.1); }
.search-input-wrap i.search-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--page-muted); font-size: 12px; pointer-events: none; }
#search { flex: 1; background: transparent; border: none; outline: none; font-family: var(--font-ui); font-size: 13px; color: var(--page-text); padding: 11px 0; min-width: 0; }
#search::placeholder { color: var(--page-muted); opacity: .65; }
.search-btn-clear { background: transparent; border: none; cursor: pointer; color: var(--page-muted); font-size: 13px; padding: 4px 8px; transition: color var(--tr); display: none; }
.search-btn-clear.visible { display: flex; align-items: center; }
.search-btn-clear:hover { color: var(--danger); }
.search-btn { background: var(--gold); border: none; cursor: pointer; border-radius: 50px; padding: 8px 20px; font-family: var(--font-ui); font-size: 11px; font-weight: 700; color: #2C1F0E; letter-spacing: .3px; transition: background var(--tr); white-space: nowrap; flex-shrink: 0; }
.search-btn:hover { background: var(--gold2); }

/* suggestions replaced by search-drop */

/* Avail pills */
.search-filters-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.filter-label-sm { font-size: 9px; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; color: var(--page-muted); flex-shrink: 0; padding-right: 4px; }
.avail-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; border-radius: 50px; font-family: var(--font-ui); font-size: 11px; font-weight: 600; border: 1.5px solid var(--page-border); background: var(--page-white); color: var(--page-muted); cursor: pointer; transition: all var(--tr); white-space: nowrap; user-select: none; }
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

/* ══════════════════════════════════════════════
   SEARCH DROPDOWN — Option B (thumbnail + info)
══════════════════════════════════════════════ */
.search-drop {
    display: none;
    position: absolute;
    top: calc(100% + 8px);
    left: 0; right: 0;
    z-index: 400;
    background: var(--page-white);
    border: 1.5px solid var(--gold-border);
    border-radius: 16px;
    box-shadow: 0 16px 48px rgba(42,31,20,.16), 0 2px 8px rgba(42,31,20,.06);
    overflow: hidden;
    animation: dropIn .18s cubic-bezier(.4,0,.2,1) both;
    max-height: 420px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(196,164,107,.25) transparent;
}
.search-drop::-webkit-scrollbar { width: 3px; }
.search-drop::-webkit-scrollbar-thumb { background: rgba(196,164,107,.3); border-radius: 3px; }
html.dark .search-drop {
    background: #1C1409;
    border-color: rgba(196,164,107,.2);
    box-shadow: 0 16px 48px rgba(0,0,0,.55);
}
.search-drop.open { display: block; }

@keyframes dropIn {
    from { opacity:0; transform:translateY(-6px) scale(.99); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}

/* ── item row ── */
.sd-item {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 14px;
    border-bottom: 1px solid var(--page-border);
    text-decoration: none; color: inherit;
    transition: background var(--tr); cursor: pointer;
}
.sd-item:last-of-type { border-bottom: none; }
.sd-item:hover { background: var(--gold-faint); }
html.dark .sd-item:hover { background: rgba(196,164,107,.06); }

.sd-thumb {
    width: 34px; height: 46px; border-radius: 4px;
    object-fit: cover; flex-shrink: 0;
    background: var(--page-bg2);
    border: 1px solid var(--page-border);
}
.sd-info { flex: 1; min-width: 0; }
.sd-title {
    font-family: var(--font-serif); font-size: 14px; font-weight: 600;
    color: var(--page-text); white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis; display: block;
    line-height: 1.2;
}
.sd-meta {
    font-size: 10px; color: var(--page-muted); margin-top: 2px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sd-badge {
    font-size: 9px; font-weight: 700; letter-spacing: .5px;
    text-transform: uppercase; padding: 3px 8px;
    border-radius: 20px; flex-shrink: 0; white-space: nowrap;
}
.sd-badge.buy    { background: rgba(196,164,107,.14); color: var(--gold-deep); border: 1px solid var(--gold-border); }
.sd-badge.borrow { background: rgba(122,92,58,.1);    color: var(--brown);     border: 1px solid var(--brown-border); }
.sd-badge.both   { background: rgba(196,164,107,.1);  color: var(--gold-deep); border: 1px solid var(--gold-border); }

/* ── footer row ── */
.sd-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 9px 14px;
    background: var(--gold-faint);
    border-top: 1px solid var(--gold-border);
    cursor: pointer; transition: background var(--tr);
}
.sd-footer:hover { background: rgba(196,164,107,.13); }
.sd-footer-label { font-size: 11px; font-weight: 700; color: var(--gold-deep); display: flex; align-items: center; gap: 6px; }
html.dark .sd-footer-label { color: var(--gold); }
.sd-footer-count { font-size: 11px; color: var(--page-muted); }

/* ── empty / spinner ── */
.sd-empty {
    padding: 20px 14px; text-align: center;
    font-size: 13px; color: var(--page-muted);
}
.sd-empty i { font-size: 20px; display: block; margin-bottom: 6px; opacity: .4; color: var(--gold); }
.sd-loading {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 18px; font-size: 12px; color: var(--page-muted);
}
.sd-spin {
    width: 14px; height: 14px;
    border: 2px solid rgba(196,164,107,.2);
    border-top-color: var(--gold); border-radius: 50%;
    animation: sdSpin .6s linear infinite; flex-shrink: 0;
}
@keyframes sdSpin { to { transform: rotate(360deg); } }

/* ══════════════════════════════════════════════
   CATALOGUE SECTIONS
══════════════════════════════════════════════ */
#catalogue-sections { padding: 0 5% 80px; max-width: 1380px; margin: 0 auto; }
.cat-section { padding-top: 48px; border-top: 1px solid var(--page-border); }
.cat-section:first-child { border-top: none; padding-top: 40px; }
.cat-section-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; margin-bottom: 24px; }
.cat-section-title { display: flex; align-items: center; gap: 12px; }
.cat-section-title h2 { font-family: var(--font-serif); font-size: 26px; font-weight: 600; line-height: 1; }
.cat-section-badge { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: var(--page-muted); background: var(--page-bg2); border: 1px solid var(--page-border); padding: 3px 10px; border-radius: 50px; }
.cat-see-all { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; letter-spacing: .3px; color: var(--gold-deep); text-decoration: none; padding: 8px 18px; border-radius: 50px; border: 1.5px solid var(--gold-border); background: var(--gold-faint); transition: all var(--tr); white-space: nowrap; flex-shrink: 0; cursor: pointer; font-family: var(--font-ui); }
html.dark .cat-see-all { color: var(--gold); }
.cat-see-all:hover { background: var(--gold); color: #1A0E05; border-color: var(--gold); box-shadow: var(--shadow-gold); transform: translateY(-1px); }
.cat-see-all i { font-size: 9px; transition: transform var(--tr); }
.cat-see-all:hover i { transform: translateX(3px); }
@keyframes spin { to { transform: rotate(360deg); } }
.fa-spin { animation: spin .7s linear infinite; }

.cat-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; }
@media (max-width: 900px) { .cat-row { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 14px; } }

/* ══════════════════════════════════════════════
   BOOK CARDS
══════════════════════════════════════════════ */
.book-card { background: var(--page-white); border-radius: var(--radius); border: 1px solid var(--page-border); overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; transition: transform var(--tr), box-shadow var(--tr), border-color var(--tr); animation: cardIn .4s ease both; }
.book-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-lg); border-color: var(--gold-border); }
.book-card:nth-child(2) { animation-delay:.05s; }
.book-card:nth-child(3) { animation-delay:.10s; }
.book-card:nth-child(4) { animation-delay:.15s; }
.book-card:nth-child(5) { animation-delay:.20s; }

.card-cover { position: relative; overflow: hidden; background: var(--page-bg2); display: block; text-decoration: none; flex-shrink: 0; height: 340px; }
.card-cover img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .6s cubic-bezier(.4,0,.2,1); }
.book-card:hover .card-cover img { transform: scale(1.06); }

.avail-ribbon { position: absolute; top: 10px; left: 10px; display: flex; flex-direction: column; gap: 5px; z-index: 2; }
.avail-tag { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 9px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; backdrop-filter: blur(10px); border: 1px solid transparent; }
.tag-buy    { background: rgba(196,164,107,.9); color: #2C1F0E; border-color: rgba(196,164,107,.4); }
.tag-borrow { background: rgba(122,92,58,.88);  color: #F5EDD8; border-color: rgba(122,92,58,.4); }
.wish-btn { position: absolute; bottom: 10px; right: 10px; z-index: 2; width: 32px; height: 32px; border-radius: 50%; background: rgba(44,31,14,.6); backdrop-filter: blur(8px); border: 1px solid rgba(196,164,107,.18); color: rgba(196,164,107,.45); font-size: 13px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all var(--tr); }
.wish-btn:hover { color: var(--gold); border-color: var(--gold-border); background: rgba(44,31,14,.85); }
.wish-btn.wishlisted { color: #ef4444; border-color: #fca5a5; }

.card-body { padding: 12px 13px 14px; flex: 1; display: flex; flex-direction: column; }
.card-title { font-family: var(--font-serif); font-size: 15px; font-weight: 600; color: var(--page-text); line-height: 1.3; margin-bottom: 3px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
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
.btn-card { flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 6px; border-radius: 9px; font-family: var(--font-ui); font-size: 10px; font-weight: 700; text-decoration: none; border: none; cursor: pointer; transition: all var(--tr); line-height: 1; letter-spacing: .3px; }
.btn-card i { font-size: 10px; }
.btn-borrow { background: var(--brown-faint); border: 1.5px solid var(--brown-border); color: var(--brown); }
.btn-borrow:hover { background: var(--brown); color: #F5EDD8; border-color: var(--brown); }
.btn-buy    { background: var(--gold-faint); border: 1.5px solid var(--gold-border); color: var(--gold-deep); }
html.dark .btn-buy { color: var(--gold); }
.btn-buy:hover { background: var(--gold); color: #2C1F0E; border-color: var(--gold); box-shadow: var(--shadow-gold); }
.btn-card.full { flex: 1 1 100%; }
.btn-both { background: linear-gradient(110deg, var(--gold-faint) 0%, var(--brown-faint) 100%); border: 1.5px solid var(--gold-border); color: var(--gold-deep); }
html.dark .btn-both { color: var(--gold); }
.btn-both:hover { background: linear-gradient(110deg, var(--gold) 0%, var(--brown) 100%); color: #fff; border-color: transparent; }
.btn-both-wrap { position: relative; flex: 1; }
.both-menu { position: absolute; bottom: calc(100% + 8px); left: 0; right: 0; background: var(--page-white); border: 1.5px solid var(--gold-border); border-radius: 12px; box-shadow: 0 12px 36px rgba(42,31,20,.2); overflow: hidden; z-index: 50; display: none; animation: fadeUp .18s ease both; }
.both-menu.open { display: block; }
.both-opt { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 14px; font-family: var(--font-ui); font-size: 11px; font-weight: 600; color: var(--page-text); text-decoration: none; background: transparent; border: none; cursor: pointer; transition: background var(--tr); border-bottom: 1px solid var(--page-border); }
.both-opt:last-child { border-bottom: none; }
.both-opt:hover { background: var(--gold-faint); }
.both-opt i { color: var(--gold); font-size: 12px; }
.admin-actions { display: flex; gap: 7px; margin-top: auto; }
.btn-admin { flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 9px; border-radius: 9px; font-family: var(--font-ui); font-size: 11px; font-weight: 700; text-decoration: none; transition: all var(--tr); }
.btn-edit   { background: var(--gold-faint); color: var(--gold); border: 1.5px solid var(--gold-border); }
.btn-edit:hover { background: rgba(196,164,107,.18); transform: translateY(-1px); }
.btn-delete { background: rgba(192,57,43,.08); color: var(--danger); border: 1.5px solid rgba(192,57,43,.2); }
.btn-delete:hover { background: rgba(192,57,43,.15); transform: translateY(-1px); }


.empty-state { grid-column: 1/-1; text-align: center; padding: 70px 20px; }
.empty-icon  { font-size: 44px; color: var(--page-border); margin-bottom: 16px; }
.empty-state h3 { font-family: var(--font-serif); font-size: 22px; color: var(--page-muted); margin-bottom: 6px; }
.empty-state p  { font-size: 13px; color: var(--page-muted); }

/* ══ type-badge (used in search_engine.php card output) ══ */
.type-badge { position: absolute; bottom: 10px; left: 10px; font-size: 9px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; background: rgba(44,31,14,.7); color: var(--gold); border: 1px solid rgba(196,164,107,.3); padding: 3px 9px; border-radius: 20px; backdrop-filter: blur(8px); z-index: 2; }

@media (max-width: 600px) {
    .books-grid, .cat-row { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
    .card-cover { height: 220px; }
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
                $detail_url = "/MEMOIR/client/doc_details.php?id=" . (int)$s['id_doc'];
                $safe_title = htmlspecialchars($s['titre']);
                $safe_type  = htmlspecialchars($s['libelle_type'] ?? 'Document');
                $safe_img   = htmlspecialchars($s['_img']);
                $dp         = $s['disponible_pour'] ?? 'both';
            ?>
            <div class="swiper-slide hs-slide">
                <img class="hs-slide-bg" src="<?= $safe_img ?>" alt="<?= $safe_title ?>" onerror="this.src='../uploads/default.jpg'">
                <div class="hs-content">
                    <div class="hs-badge"><i class="fa-solid fa-bookmark"></i> <?= $safe_type ?></div>
                    <h2 class="hs-title"><?= $safe_title ?></h2>
                    <div class="hs-btn-row">
                        <a href="<?= $detail_url ?>" class="hs-btn hs-btn-details">
                            <i class="fa-solid fa-circle-info" style="font-size:10px"></i> <?= $p['details'] ?>
                        </a>
                        <?php if ($is_logged_in && $user_role === 'client' && in_array($dp, ['emprunt','both'])): ?>
                        <a href="/MEMOIR/emprunts/emprunt.php?id_doc=<?= (int)$s['id_doc'] ?>" class="hs-btn hs-btn-borrow">
                            <i class="fa-regular fa-clock" style="font-size:10px"></i> <?= $p['borrow'] ?>
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
     STICKY SEARCH BAR — client & guest only
     Admin uses the navbar search bar instead
══════════════════════════════════════════ -->
<!-- search bar: client & guest only -->
<?php if ($user_role !== 'admin'): ?>
<div class="search-bar-sticky" id="searchBar">
    <div class="search-top-row">
        <div class="search-input-wrap" id="searchWrap" style="position:relative;">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="search"
                   placeholder="<?= htmlspecialchars($p['search_ph']) ?>"
                   autocomplete="off" spellcheck="false">
            <button class="search-btn-clear" id="clearBtn" onclick="clearSearch()" title="<?= htmlspecialchars($p['clear_title']) ?>">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="search-drop" id="searchDrop"></div>
        </div>
        <button class="search-btn" onclick="triggerSearch()">
            <i class="fa-solid fa-magnifying-glass" style="font-size:10px"></i> <?= $p['search_btn'] ?>
        </button>
    </div>
</div><!-- /search-bar-sticky -->
<?php endif; ?>

<!-- filter pills: tout le monde (admin + client + guest) -->
<div class="search-bar-sticky" id="filterBar" style="<?= $user_role==='admin' ? 'top:var(--nav-h)' : 'top:calc(var(--nav-h) + 60px)' ?>">
    <div class="search-filters-row">
        <span class="filter-label-sm"><?= $p['filter_lbl'] ?></span>
        <button class="avail-pill ap-all <?= $avail==='all'?'active':'' ?>" onclick="setAvail('all', this)">
            <span class="avail-dot"></span> <?= $p['f_all'] ?>
        </button>
        <button class="avail-pill ap-buy <?= $avail==='buy'?'active':'' ?>" onclick="setAvail('buy', this)">
            <span class="avail-dot"></span> <?= $p['f_buy'] ?>
        </button>
        <button class="avail-pill ap-borrow <?= $avail==='borrow'?'active':'' ?>" onclick="setAvail('borrow', this)">
            <span class="avail-dot"></span> <?= $p['f_borrow'] ?>
        </button>
        <button class="avail-pill ap-both <?= $avail==='both'?'active':'' ?>" onclick="setAvail('both', this)">
            <span class="avail-dot"></span> <?= $p['f_both'] ?>
        </button>
    </div>
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
            <span class="cat-section-badge"><?= $sec['total'] ?> doc<?= $sec['total']>1 ? $p['doc_s'] : '' ?></span>
        </div>
        <?php if ($sec['total'] > 4): ?>
        <a class="cat-see-all" href="/MEMOIR/client/catalogue_type.php?type=<?= $sec['id'] ?>&label=<?= urlencode($sec['label']) ?>">
            <?= $p['see_all'] ?> <i class="fa-solid fa-arrow-right"></i>
        </a>
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
                    <?php if ($can_buy): ?><span class="avail-tag tag-buy"><i class="fa-solid fa-cart-shopping" style="font-size:8px"></i> <?= $p['tag_buy'] ?></span><?php endif; ?>
                    <?php if ($can_borrow): ?><span class="avail-tag tag-borrow"><i class="fa-regular fa-clock" style="font-size:8px"></i> <?= $p['tag_borrow'] ?></span><?php endif; ?>
                </div>
                <?php if ($is_logged_in && $user_role === 'client'): ?>
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
                        <span class="card-free"><i class="fa-solid fa-book-open"></i> <?= $p['free_loan'] ?></span>
                    <?php else: ?>
                        <span class="card-free"><i class="fa-solid fa-lock-open"></i> <?= $p['free'] ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-divider"></div>

                <?php if ($user_role === 'client'): ?>
                <div class="card-actions">
                    <?php if ($is_both): ?>
                        <div class="btn-both-wrap">
                            <button class="btn-card btn-both full" onclick="toggleBothMenu(this, <?= (int)$d['id_doc'] ?>)">
                                <i class="fa-solid fa-plus"></i> <?= $p['choose'] ?>
                            </button>
                            <div class="both-menu" id="both-menu-<?= (int)$d['id_doc'] ?>">
                                <a href="../emprunts/emprunt.php?id_doc=<?= (int)$d['id_doc'] ?>" class="both-opt">
                                    <i class="fa-regular fa-clock"></i><span><?= $p['borrow'] ?></span>
                                </a>
                                <div class="both-opt" style="padding:0;">
                                    <form action="../cart/add_to_cart.php" method="POST" style="width:100%;">
                                        <input type="hidden" name="id_doc" value="<?= (int)$d['id_doc'] ?>">
                                        <button type="submit" style="all:unset;display:flex;align-items:center;gap:10px;width:100%;padding:10px 14px;font-family:var(--font-ui);font-size:11px;font-weight:600;color:var(--page-text);cursor:pointer;">
                                            <i class="fa-solid fa-cart-plus" style="color:var(--gold);font-size:12px;"></i><span><?= $p['add_cart'] ?></span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php if ($can_borrow): ?>
                        <a href="../emprunts/emprunt.php?id_doc=<?= (int)$d['id_doc'] ?>" class="btn-card btn-borrow <?= !$can_buy?'full':'' ?>">
                            <i class="fa-regular fa-clock"></i> <?= $p['borrow'] ?>
                        </a>
                        <?php endif; ?>
                        <?php if ($can_buy): ?>
                        <form action="../cart/add_to_cart.php" method="POST" style="flex:1;display:flex">
                            <input type="hidden" name="id_doc" value="<?= (int)$d['id_doc'] ?>">
                            <button type="submit" class="btn-card btn-buy <?= !$can_borrow?'full':'' ?>">
                                <i class="fa-solid fa-cart-plus"></i> <?= $p['add_cart'] ?>
                            </button>
                        </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php elseif (!$is_logged_in): ?>
                <a href="/MEMOIR/auth/login.php" class="btn-card btn-borrow full">
                    <i class="fa-solid fa-right-to-bracket"></i> <?= $p['login_req'] ?>
                </a>

                <?php elseif ($user_role === 'admin'): ?>
                <div class="admin-actions">
                    <a href="/MEMOIR/admin/modifier_document.php?id=<?= (int)$d['id_doc'] ?>" class="btn-admin btn-edit">
                        <i class="fa-solid fa-pen"></i> <?= $p['edit'] ?>
                    </a>
                    <a href="/MEMOIR/admin/delete_doc.php?id=<?= (int)$d['id_doc'] ?>" onclick="return confirm('<?= addslashes($p['delete_confirm']) ?>')" class="btn-admin btn-delete">
                        <i class="fa-solid fa-trash"></i> <?= $p['delete'] ?>
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
    <h3><?= $p['no_docs'] ?></h3>
    <p><?= $p['no_docs_sub'] ?></p>
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
        loop: true, speed: 1000, effect: 'fade',
        fadeEffect: { crossFade: true },
        autoplay: { delay: DELAY, disableOnInteraction: false, pauseOnMouseEnter: true },
        navigation: { nextEl: '#hsNext', prevEl: '#hsPrev' },
        pagination: { el: '.hs-pagination', clickable: true },
        on: { slideChangeTransitionEnd() { updateThumbs(this.realIndex); } }
    });
    window.hsGoTo = function(idx) { swiper.slideToLoop(idx, 900); updateThumbs(idx); };
})();

<?php if ($user_role !== 'admin'): ?>
/* ══ STICKY SEARCH SHADOW ══ */
const searchBarEl = document.getElementById('searchBar');
if (searchBarEl) {
    window.addEventListener('scroll', () => {
        searchBarEl.classList.toggle('scrolled', window.scrollY > 30);
    }, { passive: true });
}

/* ══════════════════════════════════════════════════
   CLIENT SEARCH — Option B dropdown
   thumbnail + titre + auteur + badge
   mode=suggest → JSON léger depuis recherche_dcmnt.php
══════════════════════════════════════════════════ */
const searchEl  = document.getElementById('search');
const dropEl    = document.getElementById('searchDrop');
const clearBtn  = document.getElementById('clearBtn');
const sectionsEl= document.getElementById('catalogue-sections');

let dropTimer = null;
let currentAvail = '<?= htmlspecialchars($avail) ?>';

/* ── translated strings passed from PHP ── */
const SD = {
    loading : <?= json_encode($p['sd_loading']) ?>,
    empty   : <?= json_encode($p['sd_empty']) ?>,
    error   : <?= json_encode($p['sd_error']) ?>,
    close   : <?= json_encode($p['sd_close']) ?>,
    result  : <?= json_encode($p['sd_result']) ?>,
    results : <?= json_encode($p['sd_results']) ?>,
    buy     : <?= json_encode($p['sd_buy']) ?>,
    borrow  : <?= json_encode($p['sd_borrow']) ?>,
    both    : <?= json_encode($p['sd_both']) ?>,
};

/* ── helpers ── */
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function badgeCls(dp) {
    if (dp==='achat')   return 'buy';
    if (dp==='emprunt') return 'borrow';
    return 'both';
}
function badgeLbl(dp) {
    if (dp==='achat')   return SD.buy;
    if (dp==='emprunt') return SD.borrow;
    return SD.both;
}
function buildUrl(q) {
    return 'recherche_dcmnt.php?mode=suggest'
         + '&search=' + encodeURIComponent(q)
         + '&avail='  + encodeURIComponent(currentAvail)
         + '&type=0';
}

/* ── open / close ── */
function openDrop()  { dropEl.classList.add('open'); }
function closeDrop() { dropEl.classList.remove('open'); dropEl.innerHTML = ''; }

function clearSearch() {
    searchEl.value = '';
    clearBtn.classList.remove('visible');
    closeDrop();
    searchEl.focus();
}

/* ── filter pills ── */
function setAvail(val, btn) {
    currentAvail = val;
    document.querySelectorAll('.avail-pill').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');

    /* si search active → relancer la recherche */
    const searchEl = document.getElementById('search');
    if (searchEl) {
        const q = searchEl.value.trim();
        if (q.length >= 2) { doSearch(q); return; }
    }

    /* AJAX filter sur les sections catalogue */
    const sectionsEl = document.getElementById('catalogue-sections');
    if (!sectionsEl) return;
    sectionsEl.style.opacity = '.4';
    sectionsEl.style.pointerEvents = 'none';

    fetch('get_sections.php?avail=' + encodeURIComponent(val))
        .then(r => r.text())
        .then(html => {
            sectionsEl.innerHTML = html;
            sectionsEl.style.opacity = '';
            sectionsEl.style.pointerEvents = '';
            const url = new URL(window.location.href);
            url.searchParams.set('avail', val);
            history.replaceState({}, '', url.toString());
        })
        .catch(() => {
            sectionsEl.style.opacity = '';
            sectionsEl.style.pointerEvents = '';
        });
}

/* ── main search ── */
function doSearch(q, autoGo) {
    dropEl.innerHTML = '<div class="sd-loading"><div class="sd-spin"></div> ' + SD.loading + '</div>';
    openDrop();

    fetch(buildUrl(q))
        .then(r => r.json())
        .then(items => {
            if (!items.length) {
                dropEl.innerHTML = '<div class="sd-empty"><i class="fa-regular fa-folder-open"></i>' + SD.empty + ' «\u00a0' + esc(q) + '\u00a0»</div>';
                openDrop();
                return;
            }

            /* autoGo = true → Enter/Rechercher → روح لأول نتيجة مباشرة */
            if (autoGo) {
                window.location.href = '/MEMOIR/client/doc_details.php?id=' + items[0].id;
                return;
            }

            let html = '';
            items.slice(0, 8).forEach(item => {
                const img  = item.image ? '/MEMOIR/uploads/' + item.image : '/MEMOIR/uploads/' + item.id + '.jpg';
                const meta = [item.auteur, item.annee, item.type].filter(Boolean).join(' · ');
                html += '<a class="sd-item" href="/MEMOIR/client/doc_details.php?id=' + item.id + '">'
                      + '<img class="sd-thumb" src="' + esc(img) + '" '
                      + 'onerror="this.onerror=null;this.src=\'/MEMOIR/uploads/default.jpg\'" alt="">'
                      + '<div class="sd-info">'
                      + '<span class="sd-title">' + esc(item.titre) + '</span>'
                      + '<span class="sd-meta">'  + esc(meta)       + '</span>'
                      + '</div>'
                      + '<span class="sd-badge ' + badgeCls(item.dispo||'both') + '">' + badgeLbl(item.dispo||'both') + '</span>'
                      + '</a>';
            });

            /* footer */
            const count = items.length;
            html += '<div class="sd-footer" onclick="closeDrop()">'
                  + '<span class="sd-footer-label"><i class="fa-solid fa-magnifying-glass" style="font-size:10px"></i> ' + count + ' ' + (count > 1 ? SD.results : SD.result) + '</span>'
                  + '<span class="sd-footer-count">' + SD.close + '</span>'
                  + '</div>';

            dropEl.innerHTML = html;
            openDrop();
        })
        .catch(() => {
            dropEl.innerHTML = '<div class="sd-empty"><i class="fa-solid fa-triangle-exclamation"></i>' + SD.error + '</div>';
            openDrop();
        });
}

function triggerSearch() {
    const q = searchEl.value.trim();
    if (!q) { clearSearch(); return; }
    clearTimeout(dropTimer);

    /* إذا الـ dropdown فيه نتائج → روح لأول نتيجة */
    const items = dropEl.querySelectorAll('.sd-item');
    if (items.length >= 1) {
        window.location.href = items[0].href;
        return;
    }
    /* ما في شيء → دور مع autoGo */
    doSearch(q, true);
}

/* ── events ── */
searchEl.addEventListener('input', function() {
    clearTimeout(dropTimer);
    const q = this.value.trim();
    clearBtn.classList.toggle('visible', q.length > 0);
    if (!q) { closeDrop(); return; }
    if (q.length >= 2) {
        dropTimer = setTimeout(() => doSearch(q), 250);
    }
});

searchEl.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        e.stopPropagation();
        clearTimeout(dropTimer);
        const q = searchEl.value.trim();
        if (!q) return;

        /* إذا الـ dropdown فيه نتائج → روح لأول نتيجة مباشرة */
        const items = dropEl.querySelectorAll('.sd-item');
        if (items.length >= 1) {
            window.location.href = items[0].href;
            return;
        }
        /* ما في شيء بعد → دور أولاً ثم روح */
        doSearch(q);
    }
    if (e.key === 'Escape') clearSearch();
});

/* close on outside click — ignore clicks inside the wrap */
document.addEventListener('click', function(e) {
    const wrap = document.querySelector('.search-input-wrap');
    const btn  = document.querySelector('.search-btn');
    if (wrap && wrap.contains(e.target)) return;
    if (btn  && btn.contains(e.target))  return;
    closeDrop();
});

<?php endif; /* end non-admin search JS */ ?>

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