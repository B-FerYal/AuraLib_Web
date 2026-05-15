<?php
/* ════════════════════════════════════════════════════════
   get_sections.php — AJAX: catalogue sections filtrées
   Retourne le HTML des sections par catégorie
   selon le filtre avail (all|buy|borrow|both)
════════════════════════════════════════════════════════ */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

include_once '../includes/db.php';

if (!isset($conn)) {
    echo '<div class="empty-state"><p>Erreur DB</p></div>';
    exit;
}

$avail   = trim($_GET['avail'] ?? 'all');
$user_role = $_SESSION['role'] ?? 'client';
$id_user   = (int)($_SESSION['id_user'] ?? 0);
$is_logged_in = isset($_SESSION['id_user']);

/* ── build avail condition ── */
$avail_cond = '';
switch ($avail) {
    case 'buy':    $avail_cond = "AND d.disponible_pour IN ('achat','both')";   break;
    case 'borrow': $avail_cond = "AND d.disponible_pour IN ('emprunt','both')"; break;
    case 'both':   $avail_cond = "AND d.disponible_pour = 'both'";              break;
}

/* ── fetch all types ── */
$q_types = $conn->query("SELECT * FROM types_documents ORDER BY id_type ASC");
$all_types = $q_types ? $q_types->fetch_all(MYSQLI_ASSOC) : [];

$sections = [];
foreach ($all_types as $t) {
    $tid  = (int)$t['id_type'];
    $sql  = "SELECT d.*, t2.libelle_type FROM documents d
             LEFT JOIN types_documents t2 ON d.id_type = t2.id_type
             WHERE d.id_type = $tid $avail_cond
             ORDER BY d.id_doc DESC LIMIT 4";
    $r2   = $conn->query($sql);
    if (!$r2 || $r2->num_rows === 0) continue;
    $rows  = $r2->fetch_all(MYSQLI_ASSOC);
    $rc2   = $conn->query("SELECT COUNT(*) as n FROM documents d WHERE d.id_type = $tid $avail_cond");
    $total = (int)($rc2->fetch_assoc()['n'] ?? 0);
    $sections[] = ['id'=>$tid,'label'=>$t['libelle_type'],'docs'=>$rows,'total'=>$total];
}

function resolveImg($d) {
    $imgPath = "../uploads/" . (int)$d['id_doc'] . ".jpg";
    if (!file_exists($imgPath)) {
        $imgPath = !empty($d['image_doc']) ? "../uploads/".$d['image_doc'] : "../uploads/default.jpg";
    }
    return $imgPath;
}

if (empty($sections)) {
    echo '<div class="empty-state" style="padding:60px 0;text-align:center">';
    echo '<div class="empty-icon"><i class="fa-regular fa-folder-open"></i></div>';
    echo '<h3>Aucun document pour ce filtre</h3>';
    echo '</div>';
    exit;
}

foreach ($sections as $sec):
    $tid = $sec['id'];
?>
<div class="cat-section">
    <div class="cat-section-head">
        <div class="cat-section-title">
            <h2><?= htmlspecialchars($sec['label']) ?></h2>
            <span class="cat-section-badge"><?= $sec['total'] ?> doc<?= $sec['total']>1?'s':'' ?></span>
        </div>
        <?php if ($sec['total'] > 4): ?>
        <a class="cat-see-all" href="/MEMOIR/client/catalogue_type.php?type=<?= $tid ?>&label=<?= urlencode($sec['label']) ?>">
            Voir tout <i class="fa-solid fa-arrow-right"></i>
        </a>
        <?php endif; ?>
    </div>
    <div class="cat-row" id="row-<?= $tid ?>">
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
                            <button class="btn-card btn-both full" onclick="toggleBothMenu(this, <?= (int)$d['id_doc'] ?>)">
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
                                            <i class="fa-solid fa-cart-plus" style="color:var(--gold);font-size:12px;"></i><span>Acheter</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php if ($can_borrow): ?>
                        <a href="../emprunts/emprunt.php?id_doc=<?= (int)$d['id_doc'] ?>" class="btn-card btn-borrow <?= !$can_buy?'full':'' ?>">
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
                    <a href="/MEMOIR/admin/delete_doc.php?id=<?= (int)$d['id_doc'] ?>" onclick="return confirm('Supprimer ?')" class="btn-admin btn-delete">
                        <i class="fa-solid fa-trash"></i> Supprimer
                    </a>
                </div>
                <?php endif; ?>

            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach;