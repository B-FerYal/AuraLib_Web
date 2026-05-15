<?php
/* ═══════════════════════════════════════════════════════════════════
   search_engine.php — AuraLib Universal Search Backend
   Uses mysqli via the project's existing db.php (no PDO).
   ─────────────────────────────────────────────────────────────────
   ROUTES:
     ?scope=admin&q=...         → JSON: documents + users + loans
     ?scope=client&q=...        → JSON: available books (suggestions)
     ?scope=client&q=...&full=1 → HTML book cards
═══════════════════════════════════════════════════════════════════ */

if (session_status() === PHP_SESSION_NONE) { session_start(); }

include_once '../includes/db.php';  // gives $conn (mysqli)

if (!isset($conn)) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(503);
    echo json_encode(['error' => 'db_unavailable']);
    exit;
}

/* ── Input ─────────────────────────────────────────────────────── */
$scope   = in_array($_GET['scope'] ?? '', ['admin','client']) ? $_GET['scope'] : 'client';
$q       = trim($_GET['q'] ?? '');
$full    = !empty($_GET['full']);
$avail   = trim($_GET['avail'] ?? 'all');   // all | buy | borrow | both
$type_id = isset($_GET['type']) ? (int)$_GET['type'] : 0;
$limit   = 50;

/* ── Auth ──────────────────────────────────────────────────────── */
$user_role = $_SESSION['role']    ?? 'client';
$id_user   = (int)($_SESSION['id_user'] ?? 0);

if ($scope === 'admin' && $user_role !== 'admin') {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

/* ── Min length guard (JSON only — full/HTML shows catalogue) ──── */
if (mb_strlen($q) < 2 && !$full) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['results' => [], 'query' => $q, 'total' => 0]);
    exit;
}

/* ── Safe LIKE value ───────────────────────────────────────────── */
$s    = $conn->real_escape_string($q);
$like = "%$s%";

/* ═══════════════════════════════════════════════════════════════
   ADMIN SCOPE — documents + users + loans
═══════════════════════════════════════════════════════════════ */
if ($scope === 'admin') {
    header('Content-Type: application/json; charset=utf-8');
    $results = ['documents' => [], 'users' => [], 'loans' => []];

    /* ── Documents ── */
    $res = mysqli_query($conn, "
        SELECT d.id_doc, d.titre, d.auteur, d.annee_edition, d.isbn,
               d.disponible_pour, d.exemplaires_disponibles,
               d.prix, d.image_doc, t.libelle_type
        FROM   documents d
        LEFT JOIN types_documents t ON t.id_type = d.id_type
        WHERE  d.titre     LIKE '$like'
            OR d.auteur    LIKE '$like'
            OR d.isbn      LIKE '$like'
            OR d.categorie LIKE '$like'
        ORDER BY d.titre ASC
        LIMIT $limit
    ");
    if ($res) while ($r = $res->fetch_assoc()) {
        $results['documents'][] = [
            'id'         => (int)$r['id_doc'],
            'titre'      => $r['titre'],
            'auteur'     => $r['auteur'] ?? '',
            'annee'      => $r['annee_edition'] ?? '',
            'isbn'       => $r['isbn'] ?? '',
            'type'       => $r['libelle_type'] ?? '',
            'dispo'      => $r['disponible_pour'],
            'stock'      => (int)$r['exemplaires_disponibles'],
            'prix'       => (float)$r['prix'],
            'image'      => $r['image_doc'] ?? '',
            'edit_url'   => '/MEMOIR/admin/modifier_document.php?id=' . (int)$r['id_doc'],
            'detail_url' => '/MEMOIR/client/doc_details.php?id='      . (int)$r['id_doc'],
        ];
    }

    /* ── Users ── */
    $exact        = preg_replace('/[^0-9]/', '', $q);
    $exact_clause = $exact !== '' ? "OR CAST(id AS CHAR) = '$exact'" : '';
    $res = mysqli_query($conn, "
        SELECT id, firstname, lastname, email, phone, status, role, created_at
        FROM   users
        WHERE  firstname LIKE '$like'
            OR lastname  LIKE '$like'
            OR email     LIKE '$like'
            $exact_clause
        ORDER BY firstname ASC
        LIMIT $limit
    ");
    if ($res) while ($r = $res->fetch_assoc()) {
        $results['users'][] = [
            'id'          => (int)$r['id'],
            'name'        => trim($r['firstname'] . ' ' . $r['lastname']),
            'email'       => $r['email'],
            'phone'       => $r['phone'] ?? '',
            'status'      => $r['status'],
            'role'        => $r['role'],
            'created_at'  => $r['created_at'],
            'profile_url' => '/MEMOIR/admin/users.php?highlight=' . (int)$r['id'],
        ];
    }

    /* ── Loans ── */
    $res = mysqli_query($conn, "
        SELECT e.id_emprunt, e.statut, e.date_debut, e.date_retour_prevue,
               u.firstname, u.lastname, u.email,
               d.titre, d.auteur, d.id_doc
        FROM   emprunt e
        JOIN   users u     ON u.id     = e.id_user
        JOIN   documents d ON d.id_doc = e.id_doc
        WHERE  u.firstname LIKE '$like'
            OR u.lastname  LIKE '$like'
            OR u.email     LIKE '$like'
            OR d.titre     LIKE '$like'
        ORDER BY e.id_emprunt DESC
        LIMIT $limit
    ");
    if ($res) while ($r = $res->fetch_assoc()) {
        $results['loans'][] = [
            'id'          => (int)$r['id_emprunt'],
            'user_name'   => trim($r['firstname'] . ' ' . $r['lastname']),
            'user_email'  => $r['email'],
            'book_title'  => $r['titre'],
            'book_author' => $r['auteur'] ?? '',
            'doc_id'      => (int)$r['id_doc'],
            'statut'      => $r['statut'],
            'date_debut'  => $r['date_debut'],
            'date_retour' => $r['date_retour_prevue'],
            'manage_url'  => '/MEMOIR/admin/gerer_emprunts.php?highlight=' . (int)$r['id_emprunt'],
        ];
    }

    $total = count($results['documents']) + count($results['users']) + count($results['loans']);
    echo json_encode(['results' => $results, 'query' => $q, 'total' => $total]);
    exit;
}

/* ═══════════════════════════════════════════════════════════════
   CLIENT SCOPE — available books
   Availability logic matches library.php exactly:
     all    → no extra filter
     buy    → disponible_pour IN ('achat','both')
     borrow → disponible_pour IN ('emprunt','both')
     both   → disponible_pour = 'both'
═══════════════════════════════════════════════════════════════ */

/* ── Availability WHERE (same logic as library.php) ─────────── */
$avail_where = '';
switch ($avail) {
    case 'buy':    $avail_where = " AND d.disponible_pour IN ('achat','both')";   break;
    case 'borrow': $avail_where = " AND d.disponible_pour IN ('emprunt','both')"; break;
    case 'both':   $avail_where = " AND d.disponible_pour = 'both'";              break;
    // 'all' → no filter
}

/* ── Type WHERE ─────────────────────────────────────────────── */
$type_where = $type_id > 0 ? " AND d.id_type = $type_id" : '';

/* ── Text search (empty = show full catalogue) ──────────────── */
$text_where = '';
if ($s !== '') {
    $text_where = "
        AND (
            d.titre            LIKE '$like'
            OR d.auteur        LIKE '$like'
            OR d.categorie     LIKE '$like'
            OR d.isbn          LIKE '$like'
            OR d.annee_edition LIKE '$like'
        )";
}

/* ── NOTE: no d.actif filter — column does not exist in memoir_db ── */
$sql = "
    SELECT d.*,
           t.libelle_type,
           (SELECT COUNT(*) FROM wishlist w
            WHERE w.id_doc = d.id_doc AND w.id_user = $id_user) AS is_wishlisted
    FROM   documents d
    LEFT JOIN types_documents t ON t.id_type = d.id_type
    WHERE  1=1
      $text_where
      $avail_where
      $type_where
    ORDER BY d.titre ASC
    LIMIT $limit
";

$result = mysqli_query($conn, $sql);
if (!$result) {
    /* Surface SQL errors during development */
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => mysqli_error($conn)]);
    exit;
}
$docs = mysqli_fetch_all($result, MYSQLI_ASSOC);

/* ── JSON suggestions mode ──────────────────────────────────── */
if (!$full) {
    header('Content-Type: application/json; charset=utf-8');
    $out = [];
    foreach ($docs as $r) {
        $out[] = [
            'id'     => (int)$r['id_doc'],
            'titre'  => $r['titre'],
            'auteur' => $r['auteur'] ?? '',
            'annee'  => $r['annee_edition'] ?? '',
            'type'   => $r['libelle_type'] ?? '',
            'dispo'  => $r['disponible_pour'],
            'prix'   => (float)$r['prix'],
            'image'  => $r['image_doc'] ?? '',
        ];
    }
    echo json_encode(['results' => $out, 'query' => $q, 'total' => count($out)]);
    exit;
}

/* ── Full HTML card mode ────────────────────────────────────── */
header('Content-Type: text/html; charset=utf-8');

if (empty($docs)) {
    echo '<div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:var(--page-muted,#9A8C7E)">'
       . '<div style="font-size:40px;margin-bottom:14px;opacity:.35"><i class="fa-regular fa-folder-open"></i></div>'
       . '<h3 style="font-size:18px;font-weight:600;margin-bottom:8px">Aucun résultat'
       . ($q ? ' pour « ' . htmlspecialchars($q) . ' »' : '') . '</h3>'
       . '<p style="font-size:14px">Essayez un autre mot-clé ou modifiez les filtres.</p>'
       . '</div>';
    exit;
}

foreach ($docs as $d):
    $dp         = $d['disponible_pour'] ?? 'both';
    $can_buy    = in_array($dp, ['achat',  'both']);
    $can_borrow = in_array($dp, ['emprunt','both']);
    $is_both    = ($dp === 'both');
    $wishlisted = (int)($d['is_wishlisted'] ?? 0) > 0;
    $heart_cls  = $wishlisted ? 'fa-solid fa-heart'   : 'fa-regular fa-heart';
    $wish_cls   = $wishlisted ? 'wishlisted' : '';

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

        <?php if ($id_user > 0 && $user_role === 'client'): ?>
        <button class="wish-btn <?= $wish_cls ?>"
                onclick="toggleWishlist(this, <?= (int)$d['id_doc'] ?>)"
                title="Favoris">
            <i class="<?= $heart_cls ?>"></i>
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
                            onclick="toggleBothMenu(this, <?= (int)$d['id_doc'] ?>)">
                        <i class="fa-solid fa-plus"></i> Choisir
                    </button>
                    <div class="both-menu" id="both-menu-<?= (int)$d['id_doc'] ?>">
                        <a href="../emprunts/emprunt.php?id_doc=<?= (int)$d['id_doc'] ?>" class="both-opt">
                            <i class="fa-regular fa-clock"></i> <span>Emprunter</span>
                        </a>
                        <form action="../cart/add_to_cart.php" method="POST" style="margin:0;width:100%">
                            <input type="hidden" name="id_doc" value="<?= (int)$d['id_doc'] ?>">
                            <button type="submit" class="both-opt"
                                    style="width:100%;border:none;background:none;cursor:pointer;">
                                <i class="fa-solid fa-cart-plus"></i> <span>Acheter</span>
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

        <?php elseif (!$id_user): ?>
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
