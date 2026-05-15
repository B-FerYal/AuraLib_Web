<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once "../includes/db.php";
include "../includes/header.php";

$is_logged_in = isset($_SESSION['id_user']);
$id_user      = $is_logged_in ? (int)$_SESSION['id_user'] : 0;
$user_role    = $_SESSION['role'] ?? 'client';
$lang         = $lang ?? 'fr';

// ── Guard ────────────────────────────────────────────────
$id_doc = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_doc <= 0) { header("Location: library.php"); exit; }

// ── Fetch document ───────────────────────────────────────
$stmt = $conn->prepare("
    SELECT d.*, t.libelle_type,
        (SELECT COUNT(*) FROM wishlist w
         WHERE w.id_doc = d.id_doc AND w.id_user = ?) AS is_wishlisted,
        (SELECT COUNT(*) FROM emprunt e
         WHERE e.id_doc = d.id_doc AND e.statut = 'en_cours') AS nb_emprunts_actifs
    FROM documents d
    LEFT JOIN types_documents t ON d.id_type = t.id_type
    WHERE d.id_doc = ?
");
$stmt->bind_param("ii", $id_user, $id_doc);
$stmt->execute();
$d = $stmt->get_result()->fetch_assoc();

if (!$d) { header("Location: library.php"); exit; }

// ── Derived vars ─────────────────────────────────────────
$dp            = $d['disponible_pour'] ?? 'both';
$can_buy       = in_array($dp, ['achat', 'both']);
$can_borrow    = in_array($dp, ['emprunt', 'both']);
$is_wishlisted = ($d['is_wishlisted'] ?? 0) > 0;
$heart_icon    = $is_wishlisted ? 'fa-solid fa-heart'   : 'fa-regular fa-heart';
$wish_class    = $is_wishlisted ? 'wishlisted'           : '';
$disponible    = ($d['exemplaires_disponibles'] ?? 1) > 0;

// Cover image
$imgPath = "../uploads/" . $id_doc . ".jpg";
if (!file_exists($imgPath)) {
    $imgPath = !empty($d['image_doc'])
        ? "../uploads/" . $d['image_doc']
        : "../uploads/default.jpg";
}

// Similar docs
$similaires = [];
if (!empty($d['id_type'])) {
    $rs = $conn->query("
        SELECT id_doc, titre, auteur, prix, disponible_pour, image_doc
        FROM documents
        WHERE id_type = {$d['id_type']} AND id_doc != $id_doc
        ORDER BY RAND() LIMIT 4
    ");
    if ($rs) $similaires = $rs->fetch_all(MYSQLI_ASSOC);
}
?>
<title><?= htmlspecialchars($d['titre']) ?> — AuraLib</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<link rel="stylesheet" href="/MEMOIR/css/aura-base.css">

<style>
/* ══════════════════════════════════════════════════════
   DETAIL PAGE — AuraLib luxury theme
══════════════════════════════════════════════════════ */
:root {
    --gold:       #C4A46B;
    --gold2:      #D4B47B;
    --taupe:      #2C1F0E;
    --cream:      #F5F0E8;
    --white:      #FFFDF9;
    --border:     #DDD5C8;
    --muted:      #9A8C7E;
    --brown:      #7A5C3A;
    --green:      #2E7D52;
    --blue:       #1A5FA5;
    --blue-bg:    #E6F1FB;
    --amber:      #A06000;
    --amber-bg:   #FEF3DC;
}

.detail-wrap {
    max-width: 1060px;
    margin: 40px auto 80px;
    padding: 0 24px;
}

/* ── Breadcrumb ── */
.breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--page-muted, var(--muted));
    margin-bottom: 32px;
    flex-wrap: wrap;
    font-family: 'Lato', sans-serif;
}
.breadcrumb a {
    color: var(--gold);
    text-decoration: none;
    font-weight: 600;
}
.breadcrumb a:hover { text-decoration: underline; }
.breadcrumb .sep { opacity: .4; }

/* ══ MAIN LAYOUT ══════════════════════════════════════ */
.detail-main {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 48px;
    align-items: start;
    margin-bottom: 56px;
}

/* ── LEFT — Cover ── */
.cover-col { position: sticky; top: 90px; }
.cover-img-wrap {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(44,31,14,.22);
    aspect-ratio: 3/4;
    background: var(--page-bg, var(--cream));
    position: relative;
}
.cover-img-wrap img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform .5s ease;
    display: block;
}
.cover-img-wrap:hover img { transform: scale(1.04); }

/* Availability badge under cover */
.cover-badges { margin-top: 14px; display: flex; flex-direction: column; gap: 8px; }

.avail-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    font-family: 'Lato', sans-serif;
}
.ab-buy    { background: var(--amber-bg); color: var(--amber); border: 1px solid rgba(160,96,0,.2); }
.ab-borrow { background: var(--blue-bg);  color: var(--blue);  border: 1px solid rgba(26,95,165,.2); }
.ab-dot    { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.dot-amber { background: var(--amber); }
.dot-blue  { background: var(--blue); }

/* Disponibility line */
.dispo-line {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 12px;
    color: var(--page-muted, var(--muted));
    font-family: 'Lato', sans-serif;
    margin-top: 10px;
}
.dispo-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.dot-green { background: #4ade80; }
.dot-red   { background: #f87171; }

/* Wishlist button */
.wish-btn-detail {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    width: 100%;
    padding: 11px 16px;
    margin-top: 12px;
    border-radius: 10px;
    border: 1.5px solid var(--page-border, var(--border));
    background: var(--page-white, var(--white));
    color: var(--page-muted, var(--muted));
    font-size: 13px;
    font-weight: 600;
    font-family: 'Lato', sans-serif;
    cursor: pointer;
    transition: all .2s;
}
.wish-btn-detail:hover   { border-color: #fca5a5; color: #ef4444; background: #fff5f5; }
.wish-btn-detail.wishlisted { color: #ef4444; border-color: #fecaca; background: #fff5f5; }

/* ── RIGHT — Info ── */
.info-col {}

.doc-category {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: var(--gold);
    margin-bottom: 12px;
    font-family: 'Lato', sans-serif;
}

.doc-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 42px;
    font-weight: 700;
    line-height: 1.12;
    color: var(--page-text, var(--taupe));
    margin-bottom: 8px;
    letter-spacing: -0.5px;
}

.doc-subtitle {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
    font-weight: 400;
    font-style: italic;
    color: var(--page-muted, var(--muted));
    margin-bottom: 16px;
}

.doc-author {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 15px;
    color: var(--page-muted, var(--muted));
    font-family: 'Lato', sans-serif;
    margin-bottom: 24px;
}
.doc-author strong {
    color: var(--page-text, var(--taupe));
    font-weight: 700;
}
.author-dot { width: 4px; height: 4px; border-radius: 50%; background: var(--page-border, var(--border)); }

/* Price */
.price-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 28px;
    flex-wrap: wrap;
}
.price-main {
    font-family: 'Cormorant Garamond', serif;
    font-size: 40px;
    font-weight: 700;
    color: var(--page-text, var(--taupe));
    line-height: 1;
    letter-spacing: -1px;
}
.price-unit-lbl {
    font-size: 16px;
    font-weight: 400;
    color: var(--page-muted, var(--muted));
    margin-left: 3px;
}
.badge-free {
    background: #dcfce7;
    color: #15803d;
    font-size: 12px;
    font-weight: 700;
    padding: 5px 14px;
    border-radius: 20px;
    font-family: 'Lato', sans-serif;
}

/* ── ACTION BUTTONS ── */
.action-zone {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 32px;
}
.btn-detail {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 15px 24px;
    border-radius: 12px;
    border: none;
    font-size: 14px;
    font-weight: 700;
    font-family: 'Lato', sans-serif;
    letter-spacing: .5px;
    cursor: pointer;
    text-decoration: none;
    transition: all .2s;
}
.btn-detail i { font-size: 15px; }

.btn-emprunter {
    background: var(--taupe);
    color: var(--gold);
}
.btn-emprunter:hover { background: #1a1208; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(44,31,14,.25); }

.btn-acheter {
    background: var(--gold);
    color: var(--taupe);
}
.btn-acheter:hover { background: #D4B47B; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(196,164,107,.35); }

.btn-disabled {
    background: var(--page-bg, var(--cream));
    color: var(--page-muted, var(--muted));
    cursor: not-allowed;
    opacity: .6;
}

.btn-login {
    background: transparent;
    border: 1.5px solid var(--border);
    color: var(--muted);
}
.btn-login:hover { border-color: var(--gold); color: var(--gold); }

/* ── Divider ── */
.h-divider {
    height: 1px;
    background: var(--page-border, var(--border));
    margin: 28px 0;
    opacity: .6;
}

/* ── Description ── */
.section-lbl {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--gold);
    margin-bottom: 10px;
    font-family: 'Lato', sans-serif;
}
.doc-desc {
    font-family: 'Cormorant Garamond', serif;
    font-size: 18px;
    font-weight: 400;
    line-height: 1.85;
    color: var(--page-muted, var(--muted));
}

/* ── Metadata grid ── */
.meta-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    border: 1px solid var(--page-border, var(--border));
    border-radius: 14px;
    overflow: hidden;
    margin-top: 24px;
    background: var(--page-white, var(--white));
}
.meta-item {
    padding: 14px 18px;
    border-bottom: 1px solid var(--page-border, var(--border));
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.meta-item:nth-child(odd)      { border-right: 1px solid var(--page-border, var(--border)); }
.meta-item:nth-last-child(-n+2) { border-bottom: none; }

.meta-key {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--page-muted, var(--muted));
    font-family: 'Lato', sans-serif;
}
.meta-val {
    font-size: 14px;
    color: var(--page-text, var(--taupe));
    font-family: 'Lato', sans-serif;
    font-weight: 600;
}

/* ══ SIMILAR DOCS ═════════════════════════════════════ */
.sim-section { margin-top: 56px; }

.sim-section-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 30px;
    font-weight: 700;
    color: var(--page-text, var(--taupe));
    margin-bottom: 22px;
    letter-spacing: -0.3px;
}

.sim-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
}
.sim-card {
    background: var(--page-white, var(--white));
    border: 1px solid var(--page-border, var(--border));
    border-radius: 12px;
    overflow: hidden;
    text-decoration: none;
    transition: transform .2s, box-shadow .2s, border-color .2s;
    display: block;
}
.sim-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(44,31,14,.12);
    border-color: rgba(196,164,107,.4);
}
.sim-cover {
    height: 170px;
    overflow: hidden;
    background: var(--cream);
}
.sim-cover img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform .4s;
    display: block;
}
.sim-card:hover .sim-cover img { transform: scale(1.06); }
.sim-info { padding: 14px; }
.sim-title-txt {
    font-family: 'Cormorant Garamond', serif;
    font-size: 16px;
    font-weight: 700;
    color: var(--page-text, var(--taupe));
    margin-bottom: 4px;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.sim-author {
    font-size: 11px;
    color: var(--page-muted, var(--muted));
    font-family: 'Lato', sans-serif;
    margin-bottom: 6px;
}
.sim-price {
    font-size: 13px;
    font-weight: 700;
    color: var(--gold);
    font-family: 'Lato', sans-serif;
}

/* ── Responsive ── */
@media (max-width: 900px) {
    .detail-main { grid-template-columns: 1fr; }
    .cover-col   { position: static; }
    .cover-img-wrap { max-width: 280px; margin: 0 auto; }
    .doc-title   { font-size: 30px; }
    .sim-grid    { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 500px) {
    .meta-grid   { grid-template-columns: 1fr; }
    .meta-item:nth-child(odd) { border-right: none; }
    .meta-item:nth-last-child(-n+2) { border-bottom: 1px solid var(--page-border, var(--border)); }
    .meta-item:last-child { border-bottom: none; }
    .sim-grid    { grid-template-columns: 1fr 1fr; }
}
</style>

<div class="detail-wrap">

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="/MEMOIR/client/library.php">
            <i class="fa-solid fa-book-open" style="font-size:11px"></i> Catalogue
        </a>
        <span class="sep">›</span>
        <span><?= htmlspecialchars($d['libelle_type'] ?? 'Document') ?></span>
        <span class="sep">›</span>
        <span><?= htmlspecialchars(mb_substr($d['titre'], 0, 45)) ?><?= mb_strlen($d['titre']) > 45 ? '…' : '' ?></span>
    </div>

    <!-- ══ MAIN ══ -->
    <div class="detail-main">

        <!-- LEFT — Cover -->
        <div class="cover-col">
            <div class="cover-img-wrap">
                <img src="<?= htmlspecialchars($imgPath) ?>"
                     alt="<?= htmlspecialchars($d['titre']) ?>"
                     onerror="this.src='../uploads/default.jpg'">
            </div>

            <!-- Availability badges -->
            <div class="cover-badges">
                <?php if ($can_buy): ?>
                <div class="avail-badge ab-buy">
                    <div class="ab-dot dot-amber"></div>
                    <i class="fa-solid fa-cart-shopping" style="font-size:11px"></i>
                    Disponible à l'achat
                </div>
                <?php endif; ?>
                <?php if ($can_borrow): ?>
                <div class="avail-badge ab-borrow">
                    <div class="ab-dot dot-blue"></div>
                    <i class="fa-regular fa-clock" style="font-size:11px"></i>
                    Disponible à l'emprunt
                </div>
                <?php endif; ?>
            </div>

            <!-- Stock line -->
            <div class="dispo-line">
                <div class="dispo-dot <?= $disponible ? 'dot-green' : 'dot-red' ?>"></div>
                <?php if ($disponible): ?>
                    <?= ($d['exemplaires_disponibles'] ?? 1) ?> exemplaire(s) disponible(s)
                <?php else: ?>
                    Indisponible actuellement
                <?php endif; ?>
            </div>
<!-- Wishlist -->
            <?php if ($is_logged_in && $user_role === 'client'): ?>
            <button class="wish-btn-detail <?= $wish_class ?>" id="wishBtn"
                    onclick="toggleWishlistDetail(<?= $id_doc ?>)">
                <i class="<?= $heart_icon ?>" id="wishIcon"></i>
                <span id="wishTxt">
                    <?= $is_wishlisted ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>
                </span>
            </button>
            <?php endif; ?>
        </div>

        <!-- RIGHT — Info -->
        <div class="info-col">

            <?php if (!empty($d['libelle_type'])): ?>
            <div class="doc-category"><?= htmlspecialchars($d['libelle_type']) ?></div>
            <?php endif; ?>

            <h1 class="doc-title"><?= htmlspecialchars($d['titre']) ?></h1>

            <?php if (!empty($d['sous_titre'])): ?>
            <div class="doc-subtitle"><?= htmlspecialchars($d['sous_titre']) ?></div>
            <?php endif; ?>

            <div class="doc-author">
                <i class="fa-solid fa-user-pen" style="color:var(--gold);font-size:13px"></i>
                <strong><?= htmlspecialchars($d['auteur'] ?? '—') ?></strong>
                <?php if (!empty($d['encadrant'])): ?>
                    <div class="author-dot"></div>
                    <span>Encadrant : <strong><?= htmlspecialchars($d['encadrant']) ?></strong></span>
                <?php endif; ?>
            </div>

            <!-- Price -->
            <div class="price-row">
                <?php if ($dp === 'emprunt'): ?>
                    <span class="badge-free"><i class="fa-solid fa-lock-open"></i> Gratuit — Emprunt uniquement</span>
                <?php else: ?>
                    <span class="price-main">
                        <?= number_format((float)$d['prix'], 0, ',', ' ') ?>
                        <span class="price-unit-lbl">DA</span>
                    </span>
                    <?php if ($dp === 'both'): ?>
                        <span class="badge-free"><i class="fa-solid fa-book-open"></i> Emprunt gratuit aussi</span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <div class="action-zone">
                <?php if ($user_role === 'client'): ?>

                    <?php if ($can_borrow): ?>
                    <a href="../emprunts/emprunt.php?id_doc=<?= $id_doc ?>"
                       class="btn-detail <?= $disponible ? 'btn-emprunter' : 'btn-disabled' ?>"
                       <?= !$disponible ? 'onclick="return false"' : '' ?>>
                        <i class="fa-solid fa-book-open"></i>
                        <?= $disponible ? 'Emprunter gratuitement' : 'Indisponible' ?>
                    </a>
                    <?php endif; ?>

                    <?php if ($can_buy): ?>
                    <form action="../cart/add_to_cart.php" method="POST" style="margin:0">
                        <input type="hidden" name="id_doc" value="<?= $id_doc ?>">
                        <button type="submit" class="btn-detail btn-acheter" style="width:100%">
                            <i class="fa-solid fa-cart-plus"></i>
                            Ajouter au panier
                        </button>
                    </form>
                    <?php endif; ?>

                <?php elseif (!$is_logged_in): ?>
                    <a href="/MEMOIR/auth/login.php" class="btn-detail btn-login">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Connectez-vous pour emprunter ou acheter
                    </a>
                <?php endif; ?>
            </div>

            <div class="h-divider"></div>

            <!-- Description -->
            <?php if (!empty($d['description_longue'])): ?>
            <div class="section-lbl">À propos de ce document</div>
            <div class="doc-desc"><?= nl2br(htmlspecialchars($d['description_longue'])) ?></div>
            <div class="h-divider"></div>
            <?php endif; ?>
<!-- Metadata -->
            <?php
            $meta = array_filter([
                'Auteur'           => $d['auteur']           ?? '',
                'Éditeur'          => $d['editeur']          ?? '',
                'Année'            => $d['annee_edition']    ?? '',
                'Lieu d\'édition'  => $d['lieu_edition']     ?? '',
                'Pages'            => !empty($d['nb_pages'])  ? $d['nb_pages'] . ' pages' : '',
                'ISBN'             => $d['isbn']             ?? '',
                'ISSN'             => $d['issn']             ?? '',
                'Université'       => $d['universite']       ?? '',
                'Spécialité'       => $d['specialite']       ?? '',
                'Revue'            => $d['nom_revue']        ?? '',
                'Numéro'           => $d['numero_issue']     ?? '',
                'Type'             => $d['libelle_type']     ?? '',
                'Exemplaires'      => $d['exemplaires']      ?? '',
            ]);
            // Pad to even number
            if (count($meta) % 2 !== 0) $meta[''] = '';
            ?>
            <?php if (!empty($meta)): ?>
            <div class="section-lbl" style="margin-top:0">Informations bibliographiques</div>
            <div class="meta-grid">
                <?php foreach ($meta as $k => $v):
                    if ($k === '' && $v === '') { echo '<div class="meta-item"></div>'; continue; }
                ?>
                <div class="meta-item">
                    <span class="meta-key"><?= htmlspecialchars($k) ?></span>
                    <span class="meta-val"><?= htmlspecialchars($v) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div><!-- .info-col -->
    </div><!-- .detail-main -->

    <!-- ══ SIMILAR DOCS ══ -->
    <?php if (!empty($similaires)): ?>
    <div class="sim-section">
        <div class="sim-section-title">Documents similaires</div>
        <div class="sim-grid">
            <?php foreach ($similaires as $s):
                $sImg = "../uploads/" . (int)$s['id_doc'] . ".jpg";
                if (!file_exists($sImg)) {
                    $sImg = !empty($s['image_doc'])
                        ? "../uploads/" . $s['image_doc']
                        : "../uploads/default.jpg";
                }
                $sDispo = $s['disponible_pour'] ?? 'both';
            ?>
            <a href="/MEMOIR/client/doc_details.php?id=<?= (int)$s['id_doc'] ?>" class="sim-card">
                <div class="sim-cover">
                    <img src="<?= htmlspecialchars($sImg) ?>"
                         alt="<?= htmlspecialchars($s['titre']) ?>"
                         onerror="this.src='../uploads/default.jpg'">
                </div>
                <div class="sim-info">
                    <div class="sim-title-txt"><?= htmlspecialchars($s['titre']) ?></div>
                    <div class="sim-author"><?= htmlspecialchars($s['auteur'] ?? '') ?></div>
                    <div class="sim-price">
                        <?= ($sDispo === 'emprunt')
                            ? '<i class="fa-solid fa-lock-open" style="font-size:10px"></i> Gratuit'
                            : number_format((float)$s['prix'], 0, ',', ' ') . ' DA' ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div><!-- .detail-wrap -->

<?php include "../includes/footer.php"; ?>

<script>
function toggleWishlistDetail(id_doc) {
    const btn  = document.getElementById('wishBtn');
    const icon = document.getElementById('wishIcon');
    const txt  = document.getElementById('wishTxt');
    if (!btn) return;
    btn.disabled = true;
fetch('/MEMOIR/client/toggle_wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_doc=' + id_doc
    })
    .then(r => r.json())
    .then(r => {
        if (r.status === 'added') {
            btn.classList.add('wishlisted');
            icon.className = 'fa-solid fa-heart';
            txt.textContent = 'Retirer des favoris';
        } else {
            btn.classList.remove('wishlisted');
            icon.className = 'fa-regular fa-heart';
            txt.textContent = 'Ajouter aux favoris';
        }
        btn.disabled = false;
    })
    .catch(() => { btn.disabled = false; });
}
</script>