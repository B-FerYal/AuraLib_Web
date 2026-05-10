<?php
/**
 * get_more_docs.php
 * AJAX endpoint — retourne les cartes suivantes d'une catégorie
 * Params GET : type (int), offset (int), avail (string)
 */
session_start();
include "../includes/db.php";

$is_logged_in = isset($_SESSION['id_user']);
$id_user      = $is_logged_in ? (int)$_SESSION['id_user'] : 0;
$user_role    = $_SESSION['role'] ?? 'client';

$type_id = isset($_GET['type'])   ? (int)$_GET['type']   : 0;
$offset  = isset($_GET['offset']) ? (int)$_GET['offset'] : 5;
$avail   = $_GET['avail'] ?? 'all';

/* ── Sécurité ── */
if ($type_id <= 0) { http_response_code(400); exit; }
if ($offset  < 0)  $offset = 0;

/* ── Build WHERE ── */
$where = ["d.id_type = $type_id"];
switch ($avail) {
    case 'buy':    $where[] = "d.disponible_pour = 'achat'";   break;
    case 'borrow': $where[] = "d.disponible_pour = 'emprunt'"; break;
    case 'both':   $where[] = "d.disponible_pour = 'both'";    break;
}

$sql = "SELECT d.*, t.libelle_type
        FROM documents d
        LEFT JOIN types_documents t ON d.id_type = t.id_type
        WHERE " . implode(" AND ", $where) . "
        ORDER BY d.id_doc DESC
        LIMIT 5 OFFSET $offset";

$result = $conn->query($sql);
if (!$result) { http_response_code(500); exit; }

/* ── Image helper ── */
function resolveImg($d) {
    $p = "../uploads/" . (int)$d['id_doc'] . ".jpg";
    if (file_exists($p)) return $p;
    return !empty($d['image_doc'])
        ? "../uploads/" . $d['image_doc']
        : "../uploads/default.jpg";
}

/* ── Render cards ── */
while ($d = $result->fetch_assoc()):
    $dp         = $d['disponible_pour'] ?? 'both';
    $can_buy    = in_array($dp, ['achat', 'both']);
    $can_borrow = in_array($dp, ['emprunt', 'both']);
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
            <?php if ($is_both): ?>
                <div class="btn-both-wrap">
                    <button class="btn-card btn-both full"
                            onclick="toggleBothMenu(this, <?= (int)$d['id_doc'] ?>)">
                        <i class="fa-solid fa-plus"></i> Choisir
                    </button>
                    <div class="both-menu" id="both-menu-<?= (int)$d['id_doc'] ?>">
                        <a href="../emprunts/emprunt.php?id_doc=<?= (int)$d['id_doc'] ?>" class="both-opt">
                            <i class="fa-regular fa-clock"></i><span>Emprunter</span>
                        </a>
                        <div class="both-opt" style="padding:0;">
                            <form action="../cart/add_to_cart.php" method="POST" style="width:100%;">
                                <input type="hidden" name="id_doc" value="<?= (int)$d['id_doc'] ?>">
                                <button type="submit" style="all:unset;display:flex;align-items:center;gap:10px;width:100%;padding:10px 14px;font-family:var(--font-ui);font-size:11px;font-weight:600;color:var(--page-text);cursor:pointer;">
                                    <i class="fa-solid fa-cart-plus" style="color:var(--gold);font-size:12px;"></i>
                                    <span>Acheter</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php else: ?>
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
<?php endwhile; ?>