<?php
/* ════════════════════════════════════════════════════════
   recherche_dcmnt.php — AJAX endpoint: Smart Global Search
   Fix : filtre avail (buy/borrow/both) ne s'appliquait pas
         car buildSearchWhere recevait $avail_resolved mais
         comparait avec les valeurs 'buy'/'borrow'/'both'
════════════════════════════════════════════════════════ */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

include_once '../includes/db.php';
include_once '../includes/languages.php';

if (!isset($conn)) {
    if (($_GET['mode'] ?? '') === 'suggest') {
        header('Content-Type: application/json');
        echo json_encode([]);
    } else {
        echo '<div class="empty-state"><h3>Erreur de connexion base de données</h3></div>';
    }
    exit;
}

$is_logged_in = isset($_SESSION['id_user']);
$id_user      = $is_logged_in ? (int)$_SESSION['id_user'] : 0;
$user_role    = $_SESSION['role'] ?? 'client';
$lang         = $_SESSION['lang'] ?? 'fr';

$mode    = trim($_GET['mode']   ?? '');
$search  = trim($_GET['search'] ?? '');
$avail   = trim($_GET['avail']  ?? 'all');   // valeurs possibles : all | buy | borrow | both
$type_id = isset($_GET['type']) ? (int)$_GET['type'] : 0;

$s = $conn->real_escape_string($search);

/* ══════════════════════════════════════════════
   BUILD SEARCH WHERE CLAUSE
   ──────────────────────────────────────────────
   FIX : La fonction reçoit maintenant $avail tel quel
         ('all','buy','borrow','both') et fait la
         correspondance elle-même vers les valeurs SQL
         ('achat','emprunt','both').
   L'ancien code passait $avail_resolved déjà mappé
   mais testait ensuite with 'buy'/'borrow'/'both'
   → jamais de correspondance → filtre ignoré.
══════════════════════════════════════════════ */
function buildSearchWhere($conn, $s, $avail, $type_id) {
    $where = "WHERE 1=1";

    // ── Filtre disponible_pour ──────────────────
    // $avail vient du GET : all | buy | borrow | both
    if ($avail === 'buy') {
        // Achat uniquement → achat OU both
        $where .= " AND (d.disponible_pour = 'achat' OR d.disponible_pour = 'both')";

    } elseif ($avail === 'borrow') {
        // Emprunt uniquement → emprunt OU both
        $where .= " AND (d.disponible_pour = 'emprunt' OR d.disponible_pour = 'both')";

    } elseif ($avail === 'both') {
        // Achat ET Emprunt disponibles → both uniquement
        $where .= " AND d.disponible_pour = 'both'";

    }
    // $avail === 'all' → pas de filtre, tout afficher

    // ── Filtre type de document ─────────────────
    if ($type_id > 0) {
        $where .= " AND d.id_type = " . (int)$type_id;
    }

    // ── Filtre recherche texte ──────────────────
    if ($s !== '') {
        $where .= " AND (
            d.titre          LIKE '%$s%'
            OR d.auteur      LIKE '%$s%'
            OR d.annee_edition LIKE '%$s%'
            OR d.isbn        LIKE '%$s%'
            OR d.categorie   LIKE '%$s%'
        )";
    }

    return $where;
}

/* ══════════════════════════════════════════════
   MODE: SUGGESTIONS (live dropdown)
══════════════════════════════════════════════ */
if ($mode === 'suggest') {
    header('Content-Type: application/json');
    if ($s === '' || mb_strlen($s) < 2) { echo json_encode([]); exit; }

    // FIX : on passe $avail directement (pas $avail_resolved)
    $where = buildSearchWhere($conn, $s, $avail, $type_id);

    $q = "
        SELECT d.id_doc, d.titre, d.auteur, d.annee_edition, t.libelle_type
        FROM documents d
        LEFT JOIN types_documents t ON d.id_type = t.id_type
        $where
        ORDER BY
            CASE WHEN d.titre LIKE '$s%' THEN 0 ELSE 1 END,
            d.titre ASC
        LIMIT 8
    ";
    $res = mysqli_query($conn, $q);
    $suggestions = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $suggestions[] = [
                'id'    => (int)$row['id_doc'],
                'titre' => $row['titre'],
                'auteur'=> $row['auteur'] ?? '',
                'annee' => $row['annee_edition'] ?? '',
                'type'  => $row['libelle_type'] ?? '',
            ];
        }
    }
    echo json_encode($suggestions);
    exit;
}

/* ══════════════════════════════════════════════
   MODE: FULL CARDS HTML (default)
   FIX : on passe $avail directement (pas $avail_resolved)
══════════════════════════════════════════════ */
$where = buildSearchWhere($conn, $s, $avail, $type_id);

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

/* ── Empty state ── */
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

/* ── Render cards ── */
foreach ($documents as $d):
    $dp         = $d['disponible_pour'] ?? 'both';
    $can_buy    = in_array($dp, ['achat', 'both']);
    $can_borrow = in_array($dp, ['emprunt', 'both']);
    $is_both    = ($dp === 'both');

    $is_wishlisted = ($d['is_wishlisted'] ?? 0) > 0;
    $heart_class   = $is_wishlisted ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
    $wish_active   = $is_wishlisted ? 'wishlisted' : '';

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
        <a href="<?= $detail_url ?>">
            <img src="<?= htmlspecialchars($imgPath) ?>"
                 alt="<?= htmlspecialchars($d['titre']) ?>"
                 loading="lazy"
                 onerror="this.src='../uploads/default.jpg'">
        </a>

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
        <button class="wish-btn <?= $wish_active ?>"
                onclick="toggleWishlist(this, <?= (int)$d['id_doc'] ?>)"
                title="Favoris">
            <i class="<?= $heart_class ?>"></i>
        </button>
        <?php endif; ?>
    </div>

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
                <span class="card-free"><i class="fa-solid fa-book-open"></i> Emprunt gratuit</span>
            <?php else: ?>
                <span class="card-free"><i class="fa-solid fa-lock-open"></i> Gratuit</span>
            <?php endif; ?>
        </div>

        <div class="card-divider"></div>

        <?php if ($user_role === 'client'): ?>
        <div class="card-actions">
            <?php if ($is_both): ?>
                <div class="btn-both-wrap" style="flex:1;position:relative;">
                    <button class="btn-card btn-both full"
                           onclick="toggleBothMenu( this, <?= (int)$d['id_doc'] ?>)">
                        <i class="fa-solid fa-plus"></i> Choisir
                    </button>
                    <div class="both-menu" id="both-menu-<?= (int)$d['id_doc'] ?>">
                        <a href="../emprunts/emprunt.php?id_doc=<?= (int)$d['id_doc'] ?>" class="both-opt">
                            <i class="fa-regular fa-clock"></i>
                            <span>Emprunter</span>
                        </a>
                      <form action="../cart/add_to_cart.php"
      method="POST"
      style="margin:0;width:100%;">
      
    <input type="hidden"
           name="id_doc"
           value="<?= (int)$d['id_doc'] ?>">

    <button type="submit"
            class="both-opt"
            style="width:100%;border:none;background:none;">








                                <i class="fa-solid fa-cart-plus"></i>
                                <span>Acheter</span>
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <?php if ($can_borrow): ?>
                <a href="../emprunts/emprunt.php?id_doc=<?= (int)$d['id_doc'] ?>"
                   class="btn-card btn-borrow full">
                    <i class="fa-regular fa-clock"></i> Emprunter
                </a>
                <?php endif; ?>
                <?php if ($can_buy): ?>
                <form action="../cart/add_to_cart.php" method="POST" style="flex:1;display:flex">
                    <input type="hidden" name="id_doc" value="<?= (int)$d['id_doc'] ?>">
                    <button type="submit" class="btn-card btn-buy full">
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