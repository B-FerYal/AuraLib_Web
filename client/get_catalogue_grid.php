<?php
/* ════════════════════════════════════════════════════════
   get_catalogue_grid.php — AJAX endpoint pour catalogue_type
   Retourne JSON: {grid_html, pagination_html, result_count_html, ...}
════════════════════════════════════════════════════════ */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($conn)) { echo json_encode(['error'=>'db']); exit; }

$is_logged_in = isset($_SESSION['id_user']);
$id_user      = (int)($_SESSION['id_user'] ?? 0);
$user_role    = $_SESSION['role'] ?? 'client';

$type_id = isset($_GET['type']) ? (int)$_GET['type'] : 0;
$avail   = trim($_GET['avail'] ?? 'all');
$sort    = trim($_GET['sort']  ?? 'newest');
$page    = max(1, (int)($_GET['page'] ?? 1));
$per     = 20;
$offset  = ($page - 1) * $per;

if (!$type_id) { echo json_encode(['error'=>'no_type']); exit; }

/* ── avail filter ── */
$avail_cond = '';
switch ($avail) {
    case 'buy':    $avail_cond = "AND d.disponible_pour IN ('achat','both')";   break;
    case 'borrow': $avail_cond = "AND d.disponible_pour IN ('emprunt','both')"; break;
    case 'both':   $avail_cond = "AND d.disponible_pour = 'both'";              break;
}

/* ── sort ── */
$order = match($sort) {
    'title'      => 'd.titre ASC',
    'price_asc'  => 'd.prix ASC',
    'price_desc' => 'd.prix DESC',
    'oldest'     => 'd.id_doc ASC',
    default      => 'd.id_doc DESC',
};

/* ── count + docs ── */
$rc    = $conn->query("SELECT COUNT(*) as n FROM documents d WHERE d.id_type = $type_id $avail_cond");
$total = (int)($rc->fetch_assoc()['n'] ?? 0);
$total_pages = max(1, ceil($total / $per));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per;

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

/* ══ BUILD GRID HTML ══ */
ob_start();
if (empty($documents)) {
    echo '<div class="empty-state" style="grid-column:1/-1;text-align:center;padding:90px 20px">';
    echo '<div class="empty-icon"><i class="fa-regular fa-folder-open"></i></div>';
    echo '<h3>Aucun document trouvé</h3>';
    echo '<p>Essayez de changer les filtres.</p></div>';
} else {
    foreach ($documents as $d):
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
                    <span class="card-free"><i class="fa-solid fa-book-open"></i> Emprunt gratuit</span>
                <?php else: ?>
                    <span class="card-free"><i class="fa-solid fa-lock-open"></i> Gratuit</span>
                <?php endif; ?>
            </div>
            <div class="card-divider"></div>

            <?php if ($user_role === 'client'): ?>
            <div class="card-actions">
                <?php if ($is_both): ?>
                    <div class="btn-both-wrap">
                        <button class="btn-card btn-both full" onclick="toggleBothMenu(this,<?= (int)$d['id_doc'] ?>)">
                            <i class="fa-solid fa-plus"></i> Choisir
                        </button>
                        <div class="both-menu" id="both-menu-<?= (int)$d['id_doc'] ?>">
                            <a href="../emprunts/emprunt.php?id_doc=<?= (int)$d['id_doc'] ?>" class="both-opt">
                                <i class="fa-regular fa-clock"></i><span>Emprunter</span>
                            </a>
                            <div class="both-opt" style="padding:0;">
                                <form action="../cart/add_to_cart.php" method="POST" style="width:100%">
                                    <input type="hidden" name="id_doc" value="<?= (int)$d['id_doc'] ?>">
                                    <button type="submit" style="all:unset;display:flex;align-items:center;gap:10px;width:100%;padding:10px 14px;font-family:inherit;font-size:11px;font-weight:600;color:inherit;cursor:pointer">
                                        <i class="fa-solid fa-cart-plus" style="color:var(--gold)"></i><span>Acheter</span>
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

            <?php elseif ($user_role === 'admin'): ?>
            <div class="admin-actions">
                <a href="/MEMOIR/admin/modifier_document.php?id=<?= (int)$d['id_doc'] ?>" class="btn-admin btn-edit">
                    <i class="fa-solid fa-pen"></i> Modifier
                </a>
                <a href="/MEMOIR/admin/delete_doc.php?id=<?= (int)$d['id_doc'] ?>"
                   onclick="return confirm('Supprimer ?')" class="btn-admin btn-delete">
                    <i class="fa-solid fa-trash"></i> Supprimer
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach;
}
$grid_html = ob_get_clean();

/* ══ BUILD PAGINATION HTML ══ */
ob_start();
if ($total_pages > 1):
    // prev
    if ($page > 1):
        echo '<button class="pg-btn" onclick="goPage(' . ($page-1) . ')"><i class="fa-solid fa-chevron-left" style="font-size:10px"></i></button>';
    else:
        echo '<span class="pg-btn disabled"><i class="fa-solid fa-chevron-left" style="font-size:10px"></i></span>';
    endif;

    $window = 2; $shown = []; $prev = null;
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i === 1 || $i === $total_pages || ($i >= $page - $window && $i <= $page + $window)) {
            $shown[] = $i;
        }
    }
    foreach ($shown as $p) {
        if ($prev !== null && $p - $prev > 1) echo '<span class="pg-ellipsis">…</span>';
        $active = $p === $page ? 'active' : '';
        echo '<button class="pg-btn ' . $active . '" onclick="goPage(' . $p . ')">' . $p . '</button>';
        $prev = $p;
    }

    // next
    if ($page < $total_pages):
        echo '<button class="pg-btn" onclick="goPage(' . ($page+1) . ')"><i class="fa-solid fa-chevron-right" style="font-size:10px"></i></button>';
    else:
        echo '<span class="pg-btn disabled"><i class="fa-solid fa-chevron-right" style="font-size:10px"></i></span>';
    endif;
endif;
$pagination_html = ob_get_clean();

/* ══ INFO STRINGS ══ */
$from = $total > 0 ? $offset + 1 : 0;
$to   = min($offset + $per, $total);
$result_count_html = $total > 0
    ? 'Affichage <strong>' . $from . '–' . $to . '</strong> sur <strong>' . $total . '</strong>'
    : '';
$page_info_html  = 'Page ' . $page . ' / ' . $total_pages;
$hero_count_html = '<strong>' . $total . '</strong> document' . ($total > 1 ? 's' : '') . ' disponible' . ($total > 1 ? 's' : '');

echo json_encode([
    'grid_html'        => $grid_html,
    'pagination_html'  => $pagination_html,
    'result_count_html'=> $result_count_html,
    'page_info_html'   => $page_info_html,
    'hero_count_html'  => $hero_count_html,
    'total'            => $total,
    'page'             => $page,
    'total_pages'      => $total_pages,
]);