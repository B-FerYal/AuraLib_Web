<?php
/**
 * ══════════════════════════════════════════════════════
 *  SNIPPET — Coller dans header.php  (avant l'icône Panier)
 *  Wishlist heart icon + dropdown  —  Option C
 * ══════════════════════════════════════════════════════
 *
 *  Variables attendues depuis header.php (déjà présentes) :
 *    $is_logged_in  (bool)
 *    $id_user       (int)
 *    $user_role     (string)
 *    $conn          (mysqli)
 *    $lang          (string)
 */

/* ── Fetch wishlist preview (top 4) ── */
$wl_items  = [];
$wl_count  = 0;
if ($is_logged_in && $user_role !== 'admin') {
    // Correction ici : changement de date_ajout par created_at
    $wl_res = $conn->query(
        "SELECT w.id_wishlist, d.id_doc, d.titre, d.auteur, d.prix,
                d.image_doc, d.disponible_pour
         FROM wishlist w
         JOIN documents d ON w.id_doc = d.id_doc
         WHERE w.id_user = $id_user
         ORDER BY w.created_at DESC
         LIMIT 4"
    );
    if ($wl_res) $wl_items = $wl_res->fetch_all(MYSQLI_ASSOC);

    $wl_count_res = $conn->query(
        "SELECT COUNT(*) AS n FROM wishlist WHERE id_user = $id_user"
    );
    $wl_count = $wl_count_res ? (int)$wl_count_res->fetch_assoc()['n'] : 0;
}

$wl_labels = [
    'fr' => ['title'=>'Mes Favoris','empty'=>'Aucun favori pour l\'instant','see_all'=>'Voir tout','buy'=>'Acheter','borrow'=>'Emprunter','choose'=>'Choisir','free'=>'Gratuit'],
    'en' => ['title'=>'My Wishlist','empty'=>'No favourites yet','see_all'=>'See all','buy'=>'Buy','borrow'=>'Borrow','choose'=>'Choose','free'=>'Free'],
    'ar' => ['title'=>'المفضلة','empty'=>'لا توجد مفضلة بعد','see_all'=>'عرض الكل','buy'=>'شراء','borrow'=>'استعارة','choose'=>'اختر','free'=>'مجاني'],
];
$wl_t = $wl_labels[$lang] ?? $wl_labels['fr'];
?>

<?php if ($is_logged_in && $user_role !== 'admin'): ?>
<!-- ══ WISHLIST HEART ICON + DROPDOWN ══ -->
<div class="wl-nav-wrap" id="wlNavWrap">

    <!-- Heart trigger button -->
    <button class="wl-nav-btn" id="wlNavBtn"
            onclick="toggleWlDropdown()"
            aria-label="<?= $wl_t['title'] ?>"
            title="<?= $wl_t['title'] ?>">
        <i class="fa-solid fa-heart"></i>
        <?php if ($wl_count > 0): ?>
        <span class="wl-nav-badge" id="wlNavBadge"><?= $wl_count ?></span>
        <?php else: ?>
        <span class="wl-nav-badge wl-badge-hidden" id="wlNavBadge">0</span>
        <?php endif; ?>
    </button>

    <!-- Dropdown panel -->
    <div class="wl-dropdown" id="wlDropdown" aria-hidden="true">
        <div class="wl-drop-header">
            <span class="wl-drop-title">
                <i class="fa-solid fa-heart"></i> <?= $wl_t['title'] ?>
            </span>
            <?php if ($wl_count > 0): ?>
            <span class="wl-drop-count"><?= $wl_count ?></span>
            <?php endif; ?>
        </div>

        <?php if (empty($wl_items)): ?>
        <!-- Empty -->
        <div class="wl-drop-empty">
            <i class="fa-regular fa-heart"></i>
            <p><?= $wl_t['empty'] ?></p>
        </div>
        <?php else: ?>
        <!-- Items list -->
        <div class="wl-drop-list">
            <?php foreach ($wl_items as $wi):
                $dp         = $wi['disponible_pour'] ?? 'both';
                $can_buy    = in_array($dp, ['achat','both']);
                $can_borrow = in_array($dp, ['emprunt','both']);
                $img_path   = '../uploads/' . (int)$wi['id_doc'] . '.jpg';
                if (!file_exists($img_path))
                    $img_path = !empty($wi['image_doc']) ? '../uploads/' . $wi['image_doc'] : '../uploads/default.jpg';
            ?>
            <div class="wl-drop-item" id="wl-item-<?= (int)$wi['id_doc'] ?>">
                <!-- Cover -->
                <a href="/MEMOIR/client/doc_details.php?id=<?= (int)$wi['id_doc'] ?>"
                   class="wl-drop-cover" onclick="closeWlDropdown()">
                    <img src="<?= htmlspecialchars($img_path) ?>"
                         alt="<?= htmlspecialchars($wi['titre']) ?>"
                         onerror="this.src='../uploads/default.jpg'">
                </a>
                <!-- Info -->
                <div class="wl-drop-info">
                    <a href="/MEMOIR/client/doc_details.php?id=<?= (int)$wi['id_doc'] ?>"
                       class="wl-drop-name" onclick="closeWlDropdown()">
                        <?= htmlspecialchars($wi['titre']) ?>
                    </a>
                    <span class="wl-drop-author"><?= htmlspecialchars($wi['auteur'] ?? '') ?></span>
                    <span class="wl-drop-price">
                        <?php if ($can_buy && (float)$wi['prix'] > 0): ?>
                            <?= number_format((float)$wi['prix'], 0, ',', ' ') ?> DA
                        <?php else: ?>
                            <?= $wl_t['free'] ?>
                        <?php endif; ?>
                    </span>
                </div>
                <!-- Remove -->
                <button class="wl-drop-rm"
                        onclick="wlDropRemove(<?= (int)$wi['id_doc'] ?>)"
                        title="Retirer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer CTA -->
        <div class="wl-drop-footer">
            <a href="/MEMOIR/client/wishlist.php" class="wl-drop-see-all" onclick="closeWlDropdown()">
                <i class="fa-solid fa-heart"></i>
                <?= $wl_t['see_all'] ?>
                <?php if ($wl_count > 4): ?>
                <span class="wl-see-all-count">(<?= $wl_count ?>)</span>
                <?php endif; ?>
                <i class="fa-solid fa-arrow-right" style="margin-left:auto;font-size:9px;opacity:.6"></i>
            </a>
        </div>
        <?php endif; ?>
    </div><!-- /wl-dropdown -->
</div><!-- /wl-nav-wrap -->

<!-- ══ STYLES ══ -->
<style>
/* ── Trigger button ── */
.wl-nav-wrap { position:relative; display:flex; align-items:center; }

.wl-nav-btn {
    position:relative;
    width:38px; height:38px; border-radius:50%;
    background:rgba(196,164,107,.08);
    border:1px solid rgba(196,164,107,.18);
    color:rgba(196,164,107,.65);
    font-size:15px; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:all .22s cubic-bezier(.4,0,.2,1);
}
.wl-nav-btn:hover,
.wl-nav-btn.active {
    background:rgba(196,164,107,.16);
    border-color:rgba(196,164,107,.4);
    color:#C4A46B;
    transform:scale(1.06);
}
.wl-nav-btn .fa-heart { transition:transform .3s; }
.wl-nav-btn.active .fa-heart { transform:scale(1.15); }

/* badge */
.wl-nav-badge {
    position:absolute; top:-5px; right:-5px;
    min-width:16px; height:16px; border-radius:50px;
    background:#ef4444; color:#fff;
    font-size:9px; font-weight:700; line-height:16px;
    padding:0 4px; text-align:center;
    border:2px solid var(--nav-bg, #1A1008);
    transition:transform .3s cubic-bezier(.34,1.56,.64,1), opacity .2s;
}
.wl-nav-badge.wl-badge-hidden { opacity:0; transform:scale(0); }
.wl-nav-badge.wl-badge-pop { animation:badgePop .35s cubic-bezier(.34,1.56,.64,1); }
@keyframes badgePop { 0%{transform:scale(0)} 60%{transform:scale(1.3)} 100%{transform:scale(1)} }

/* ── Dropdown panel ── */
.wl-dropdown {
    position:absolute;
    top:calc(100% + 12px); right:-8px;
    width:310px;
    background:var(--page-white, #FDFAF5);
    border:1px solid rgba(196,164,107,.22);
    border-radius:16px;
    box-shadow:0 20px 55px rgba(42,31,20,.2), 0 0 0 1px rgba(196,164,107,.08);
    z-index:999; overflow:hidden;
    opacity:0; pointer-events:none;
    transform:translateY(-8px) scale(.97);
    transform-origin:top right;
    transition:opacity .22s, transform .22s cubic-bezier(.4,0,.2,1);
}
.wl-dropdown.open {
    opacity:1; pointer-events:all;
    transform:translateY(0) scale(1);
}
html.dark .wl-dropdown {
    background:#1E1610;
    border-color:rgba(196,164,107,.18);
    box-shadow:0 20px 55px rgba(0,0,0,.5);
}

/* arrow tip */
.wl-dropdown::before {
    content:'';
    position:absolute; top:-6px; right:18px;
    width:12px; height:12px;
    background:var(--page-white, #FDFAF5);
    border:1px solid rgba(196,164,107,.22);
    border-bottom:none; border-right:none;
    transform:rotate(45deg);
}
html.dark .wl-dropdown::before { background:#1E1610; }

/* header */
.wl-drop-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:14px 16px 10px;
    border-bottom:1px solid var(--page-border, #D8CFC0);
}
html.dark .wl-drop-header { border-color:#3A2E1E; }
.wl-drop-title {
    font-size:11px; font-weight:700; letter-spacing:1.5px;
    text-transform:uppercase; color:var(--page-text, #2A1F14);
    display:flex; align-items:center; gap:7px;
}
.wl-drop-title i { color:#ef4444; font-size:10px; }
.wl-drop-count {
    font-size:10px; font-weight:700;
    background:rgba(196,164,107,.12);
    border:1px solid rgba(196,164,107,.22);
    color:#C4A46B; border-radius:50px;
    padding:2px 9px;
}

/* empty */
.wl-drop-empty {
    padding:36px 20px; text-align:center;
}
.wl-drop-empty i { font-size:28px; color:rgba(196,164,107,.35); display:block; margin-bottom:10px; }
.wl-drop-empty p { font-size:12px; color:var(--page-muted, #9A8C7E); line-height:1.6; }

/* list */
.wl-drop-list { padding:6px 0; }

.wl-drop-item {
    display:flex; align-items:center; gap:10px;
    padding:8px 14px;
    transition:background .18s;
    position:relative;
}
.wl-drop-item:hover { background:rgba(196,164,107,.06); }

.wl-drop-cover {
    width:36px; height:48px; border-radius:5px; overflow:hidden;
    flex-shrink:0; display:block;
    box-shadow:0 2px 8px rgba(42,31,20,.15);
}
.wl-drop-cover img {
    width:100%; height:100%; object-fit:cover; display:block;
}

.wl-drop-info { flex:1; min-width:0; }
.wl-drop-name {
    display:block; font-size:12px; font-weight:600;
    color:var(--page-text, #2A1F14); text-decoration:none;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    margin-bottom:2px; transition:color .18s;
}
.wl-drop-name:hover { color:#C4A46B; }
.wl-drop-author {
    display:block; font-size:10px; color:var(--page-muted, #9A8C7E);
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    margin-bottom:4px;
}
.wl-drop-price {
    display:block; font-size:11px; font-weight:700; color:#B8832A;
}
html.dark .wl-drop-price { color:#D4B47B; }

.wl-drop-rm {
    flex-shrink:0; width:22px; height:22px; border-radius:50%;
    background:transparent; border:none; cursor:pointer;
    color:var(--page-muted, #9A8C7E); font-size:10px;
    display:flex; align-items:center; justify-content:center;
    opacity:0; transition:all .18s;
}
.wl-drop-item:hover .wl-drop-rm { opacity:1; }
.wl-drop-rm:hover { background:rgba(192,57,43,.12); color:#C0392B; }

/* footer CTA */
.wl-drop-footer {
    padding:8px 12px 12px;
    border-top:1px solid var(--page-border, #D8CFC0);
}
html.dark .wl-drop-footer { border-color:#3A2E1E; }

.wl-drop-see-all {
    display:flex; align-items:center; gap:9px; width:100%;
    padding:10px 14px; border-radius:10px;
    background:rgba(196,164,107,.08);
    border:1px solid rgba(196,164,107,.2);
    color:#C4A46B; font-size:11px; font-weight:700;
    text-decoration:none; letter-spacing:.3px;
    transition:all .2s;
}
.wl-drop-see-all:hover {
    background:rgba(196,164,107,.15);
    border-color:rgba(196,164,107,.35);
    transform:translateX(2px);
}
.wl-drop-see-all i:first-child { font-size:11px; }
.wl-see-all-count { font-size:10px; opacity:.7; }

/* ── Removing animation ── */
.wl-drop-item.removing {
    opacity:0; max-height:0; padding:0 14px;
    transition:opacity .25s, max-height .3s .05s, padding .3s .05s;
    overflow:hidden;
}
</style>

<!-- ══ JAVASCRIPT ══ -->
<script>
/* open/close */
function toggleWlDropdown() {
    const wrap = document.getElementById('wlNavWrap');
    const btn  = document.getElementById('wlNavBtn');
    const drop = document.getElementById('wlDropdown');
    const open = drop.classList.contains('open');
    if (open) {
        closeWlDropdown();
    } else {
        drop.classList.add('open');
        btn.classList.add('active');
        drop.setAttribute('aria-hidden','false');
        setTimeout(() => {
            document.addEventListener('click', wlOutsideClick);
        }, 10);
    }
}
function closeWlDropdown() {
    const btn  = document.getElementById('wlNavBtn');
    const drop = document.getElementById('wlDropdown');
    drop.classList.remove('open');
    btn?.classList.remove('active');
    drop.setAttribute('aria-hidden','true');
    document.removeEventListener('click', wlOutsideClick);
}
function wlOutsideClick(e) {
    const wrap = document.getElementById('wlNavWrap');
    if (wrap && !wrap.contains(e.target)) closeWlDropdown();
}

/* remove from dropdown */
function wlDropRemove(id_doc) {
    const item  = document.getElementById('wl-item-' + id_doc);
    const badge = document.getElementById('wlNavBadge');
    if (!item) return;

    item.classList.add('removing');

    fetch('/MEMOIR/client/toggle_wishlist.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'id_doc=' + id_doc
    })
    .then(r => r.json())
    .then(() => {
        setTimeout(() => {
            item.remove();
            /* update badge */
            if (badge) {
                const n = Math.max(0, parseInt(badge.textContent) - 1);
                badge.textContent = n;
                if (n === 0) badge.classList.add('wl-badge-hidden');
            }
            /* update header count */
            const cnt = document.querySelector('.wl-drop-count');
            if (cnt) {
                const n = Math.max(0, parseInt(cnt.textContent) - 1);
                cnt.textContent = n;
                if (n === 0) cnt.remove();
            }
            /* if list empty, show empty state */
            const list = document.querySelector('.wl-drop-list');
            if (list && list.children.length === 0) {
                list.outerHTML = `<div class="wl-drop-empty">
                    <i class="fa-regular fa-heart"></i>
                    <p>Aucun favori pour l'instant</p>
                </div>`;
                document.querySelector('.wl-drop-footer')?.remove();
            }
        }, 320);
    });
}
</script>
<?php endif; ?>