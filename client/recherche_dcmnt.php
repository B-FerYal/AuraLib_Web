<?php
/* ════════════════════════════════════════════════════════
   recherche_dcmnt.php — AJAX endpoint for library search
   Returns book cards HTML
════════════════════════════════════════════════════════ */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

include_once '../includes/db.php';
include_once '../includes/languages.php';

// Return JSON-safe error if DB fails
if (!isset($conn)) {
    echo '<div class="empty-state"><h3>Erreur de connexion base de données</h3></div>';
    exit;
}

$is_logged_in = isset($_SESSION['id_user']);
$id_user      = $is_logged_in ? (int)$_SESSION['id_user'] : 0;
$user_role    = $_SESSION['role'] ?? 'client';
$lang         = $_SESSION['lang'] ?? 'fr';

$search  = trim($_GET['search']  ?? '');
$avail   = trim($_GET['avail']   ?? 'all');   // matches library.php param name
$filter  = trim($_GET['filter']  ?? 'all');   // fallback alias
$type_id = isset($_GET['type'])  ? (int)$_GET['type'] : 0;

// Normalise avail → disponible_pour values
$avail_map = [
    'buy'    => 'achat',
    'borrow' => 'emprunt',
    'both'   => 'both',
];
$avail_resolved = $avail_map[$avail] ?? ($avail_map[$filter] ?? null);

$s = $conn->real_escape_string($search);

// ── Build WHERE ─────────────────────────────────────────
$where = "WHERE 1=1";

if ($avail_resolved) {
    $ar = $conn->real_escape_string($avail_resolved);
    $where .= " AND d.disponible_pour = '$ar'";
}

if ($type_id > 0) {
    $where .= " AND d.id_type = $type_id";
}

if ($s !== '') {
    $where .= " AND (
        d.titre               LIKE '%$s%'
        OR d.auteur           LIKE '%$s%'
        OR d.sous_titre       LIKE '%$s%'
        OR d.isbn             LIKE '%$s%'
        OR d.issn             LIKE '%$s%'
        OR d.editeur          LIKE '%$s%'
        OR d.lieu_edition     LIKE '%$s%'
        OR d.encadrant        LIKE '%$s%'
        OR d.universite       LIKE '%$s%'
        OR d.specialite       LIKE '%$s%'
        OR d.nom_revue        LIKE '%$s%'
        OR d.description_longue LIKE '%$s%'
        OR t.libelle_type     LIKE '%$s%'
        OR d.annee_edition    LIKE '%$s%'
    )";
}

$query = "
    SELECT d.*,
           t.libelle_type,
           (SELECT COUNT(*) FROM wishlist w
            WHERE w.id_doc = d.id_doc AND w.id_user = $id_user) AS is_wishlisted
    FROM documents d
    LEFT JOIN types_documents t ON d.id_type = t.id_type
    $where
    ORDER BY d.titre ASC
";

$result    = mysqli_query($conn, $query);
$documents = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];

$text = (isset($translations) && is_array($translations))
    ? ($translations[$lang] ?? $translations['fr'] ?? [])
    : [];

// ── Empty state ─────────────────────────────────────────
if (empty($documents)) {
    echo '<div class="empty-state">';
    echo '<div class="empty-icon"><i class="fa-regular fa-folder-open"></i></div>';
    echo '<h3>' . htmlspecialchars($text['no_results'] ?? 'Aucun résultat trouvé') . '</h3>';
    if ($s !== '') {
        echo '<p>Essayez avec un autre mot-clé ou modifiez les filtres.</p>';
    }
    echo '</div>';
    exit;
}

// ── Render cards ────────────────────────────────────────
foreach ($documents as $d):
    $dp         = $d['disponible_pour'] ?? 'both';
    $can_buy    = in_array($dp, ['achat', 'both']);
    $can_borrow = in_array($dp, ['emprunt', 'both']);

    $is_wishlisted = ($d['is_wishlisted'] ?? 0) > 0;
    $heart_class   = $is_wishlisted ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
    $wish_active   = $is_wishlisted ? 'wishlisted' : '';

    // Cover image — try id_doc.jpg first
    $imgPath = "../uploads/" . (int)$d['id_doc'] . ".jpg";
    if (!file_exists($imgPath)) {
        $imgPath = !empty($d['image_doc'])
            ? "../uploads/" . $d['image_doc']
            : "../uploads/default.jpg";
    }
$detail_url = "/MEMOIR/client/doc_details.php?id=" . (int)$d['id_doc'];
?>
<div class="book-card">
    <div class="card-cover">
        <!-- Clicking cover → detail page -->
        <a href="<?= $detail_url ?>">
            <img src="<?= htmlspecialchars($imgPath) ?>"
                 alt="<?= htmlspecialchars($d['titre']) ?>"
                 loading="lazy"
                 onerror="this.src='../uploads/default.jpg'">
        </a>

        <!-- Availability ribbon -->
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

        <!-- Type badge -->
        <?php if (!empty($d['libelle_type'])): ?>
        <span class="type-badge"><?= htmlspecialchars($d['libelle_type']) ?></span>
        <?php endif; ?>

        <!-- Wishlist -->
        <?php if ($is_logged_in && $user_role === 'client'): ?>
        <button class="wish-btn <?= $wish_active ?>"
                onclick="toggleWishlist(this, <?= (int)$d['id_doc'] ?>)"
                title="Favoris">
            <i class="<?= $heart_class ?>"></i>
        </button>
        <?php endif; ?>
    </div>

    <div class="card-body">
        <!-- Title → detail page -->
        <h3 class="card-title">
            <a href="<?= $detail_url ?>" style="text-decoration:none;color:inherit">
                <?= htmlspecialchars($d['titre']) ?>
            </a>
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
        <?php elseif (!$is_logged_in): ?>
        <a href="/MEMOIR/auth/login.php" class="btn-card btn-borrow full">
            <i class="fa-solid fa-right-to-bracket"></i>
            Connexion requise
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
<?php endforeach;
