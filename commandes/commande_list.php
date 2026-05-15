<?php
include_once __DIR__ . "/../includes/header.php";
// $text و t() و $lang متاحة تلقائياً من header.php ← languages.php

if (!$is_logged_in) {
    header("Location: " . $base . "/auth/login.php");
    exit;
}

// ── Fetch orders ────────────────────────────────────────
$query = "SELECT * FROM commande WHERE id_user = $id_user ORDER BY id_commande DESC";
$commandes = $conn->query($query);

// ── Pre-fetch items per order (one query) ───────────────
$items_query = "
    SELECT ci.id_commande, d.titre, d.auteur, ci.quantite
    FROM commande_item ci
    JOIN documents d ON ci.id_doc = d.id_doc
    WHERE ci.id_commande IN (
        SELECT id_commande FROM commande WHERE id_user = $id_user
    )
    ORDER BY ci.id_commande DESC, d.titre ASC
";
$items_result = $conn->query($items_query);
$items_by_order = [];
if ($items_result) {
    while ($row = $items_result->fetch_assoc()) {
        $items_by_order[$row['id_commande']][] = $row;
    }
}

// ── نصوص الصفحة حسب اللغة ──────────────────────────────
$pg = [
    'fr' => [
        'eyebrow'         => 'Mon compte',
        'title'           => 'Mes <em>Achats</em>',
        'subtitle'        => 'Retrouvez l\'historique de toutes vos commandes.',
        'order_num'       => 'Commande n°',
        'lbl_total'       => 'Montant total',
        'btn_pay'         => 'Régler la commande',
        'btn_cancel'      => 'Annuler',
        'confirm_cancel'  => 'Voulez-vous vraiment annuler cette commande ?',
        'msg_paid'        => 'Paiement confirmé',
        'msg_cancelled'   => 'Commande annulée',
        'more_items'      => '+ %d autre%s article%s',
        'st_paid'         => 'Payée',
        'st_waiting'      => 'En attente de paiement',
        'st_cancelled'    => 'Annulée',
        'st_pending'      => 'En attente',
        'empty_title'     => 'Aucun achat trouvé',
        'empty_sub'       => 'Vous n\'avez encore passé aucune commande.',
        'btn_explore'     => 'Explorer le catalogue',
    ],
    'en' => [
        'eyebrow'         => 'My Account',
        'title'           => 'My <em>Purchases</em>',
        'subtitle'        => 'View the history of all your orders.',
        'order_num'       => 'Order #',
        'lbl_total'       => 'Total amount',
        'btn_pay'         => 'Pay now',
        'btn_cancel'      => 'Cancel',
        'confirm_cancel'  => 'Are you sure you want to cancel this order?',
        'msg_paid'        => 'Payment confirmed',
        'msg_cancelled'   => 'Order cancelled',
        'more_items'      => '+ %d more item%s',
        'st_paid'         => 'Paid',
        'st_waiting'      => 'Awaiting payment',
        'st_cancelled'    => 'Cancelled',
        'st_pending'      => 'Pending',
        'empty_title'     => 'No purchases found',
        'empty_sub'       => 'You haven\'t placed any orders yet.',
        'btn_explore'     => 'Explore the catalogue',
    ],
    'ar' => [
        'eyebrow'         => 'حسابي',
        'title'           => '<em>مشترياتي</em>',
        'subtitle'        => 'تصفح سجل جميع طلباتك.',
        'order_num'       => 'طلب رقم ',
        'lbl_total'       => 'المبلغ الإجمالي',
        'btn_pay'         => 'إتمام الدفع',
        'btn_cancel'      => 'إلغاء',
        'confirm_cancel'  => 'هل أنت متأكد من إلغاء هذا الطلب؟',
        'msg_paid'        => 'تم تأكيد الدفع',
        'msg_cancelled'   => 'تم إلغاء الطلب',
        'more_items'      => '+ %d عناصر أخرى',
        'st_paid'         => 'مدفوع',
        'st_waiting'      => 'في انتظار الدفع',
        'st_cancelled'    => 'ملغى',
        'st_pending'      => 'قيد الانتظار',
        'empty_title'     => 'لا توجد مشتريات',
        'empty_sub'       => 'لم تقم بأي طلب حتى الآن.',
        'btn_explore'     => 'استكشف الكتالوج',
    ],
];

$p     = $pg[$lang] ?? $pg['fr'];
$isRtl = ($lang === 'ar');

// ── دالة التاريخ متعدد اللغات ───────────────────────────
function fmt_date_cmd(string $date_str, string $lang): string {
    if (!$date_str || $date_str === '0000-00-00') return '';
    $ts = strtotime($date_str);
    if (!$ts) return '';
    $day = date('d', $ts);
    $m   = (int)date('n', $ts);
    $yr  = date('Y', $ts);
    $months = [
        'fr' => ['','Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'],
        'en' => ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        'ar' => ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'],
    ];
    $month = ($months[$lang] ?? $months['fr'])[$m];
    return "$day $month $yr";
}

// ── دالة "X عناصر إضافية" ──────────────────────────────
function fmt_more(int $n, string $lang): string {
    if ($lang === 'ar') return "+ $n عناصر أخرى";
    if ($lang === 'en') return "+ $n more item" . ($n > 1 ? 's' : '');
    return "+ $n autre" . ($n > 1 ? 's' : '') . " article" . ($n > 1 ? 's' : '');
}
?>

<style>
:root {
    --gold:       #C4A46B;
    --gold-deep:  #B8924A;
    --gold-light: #E8D5AA;
    --cream:      #F5F0E8;
    --cream-dark: #EDE5D4;
    --ink:        #2C1F0E;
    --ink-muted:  #9A8C7E;
    --white:      #FFFDF9;
    --red:        #C0392B;
    --green:      #2E7D52;
    --amber:      #C9870A;
}

.achats-page {
    background: var(--cream);
    min-height: 100vh;
    padding: 0 0 80px;
    font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "'Lato', sans-serif" ?>;
    direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
}

/* ═══ HERO ══════════════════════════════════════════════ */
.ach-hero {
    background: var(--ink);
    padding: 90px 24px 60px;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.ach-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 55% 80% at 10% 60%, rgba(196,164,107,0.09) 0%, transparent 65%),
        radial-gradient(ellipse 45% 60% at 90% 20%, rgba(196,164,107,0.07) 0%, transparent 65%);
    pointer-events: none;
}
.ach-hero::after {
    content: '';
    position: absolute;
    bottom: 0; left: 12%; right: 12%;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(196,164,107,0.45), transparent);
}
.ach-hero-inner { position: relative; z-index: 1; }

.ach-hero-eyebrow {
    font-size: 9px; font-weight: 700;
    letter-spacing: <?= $isRtl ? '1px' : '5px' ?>;
    text-transform: uppercase; color: var(--gold);
    margin-bottom: 18px;
    display: flex; align-items: center; justify-content: center; gap: 14px;
}
.ach-hero-eyebrow::before,
.ach-hero-eyebrow::after {
    content: ''; width: 30px; height: 1px;
    background: var(--gold); opacity: 0.45;
}
.ach-hero h1 {
    font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "'Cormorant Garamond', serif" ?>;
    font-size: <?= $isRtl ? '42px' : '54px' ?>;
    font-weight: 700; color: #fff;
    margin: 0 0 12px;
    letter-spacing: <?= $isRtl ? '0' : '-1px' ?>;
    line-height: 1.2;
}
.ach-hero h1 em { font-style: italic; color: var(--gold); }
.ach-hero p { font-size: 13px; color: rgba(255,255,255,0.38); letter-spacing: 0.4px; }

/* ═══ CARDS LIST ════════════════════════════════════════ */
.ach-list {
    width: 95%;
    max-width: 1400px;
    margin: 44px auto 0;
    padding: 0 24px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.ach-card {
    background: var(--white);
    border: 1px solid var(--cream-dark);
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    box-shadow: 0 4px 20px rgba(44,31,14,0.06);
    transition: box-shadow 0.3s, transform 0.3s;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.ach-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 44px rgba(196,164,107,0.14);
}

/* Accent bar */
.ach-bar { width: 4px; flex-shrink: 0; background: var(--cream-dark); }
.ach-bar.paid    { background: var(--green); }
.ach-bar.pending { background: var(--amber); }
.ach-bar.waiting { background: #3B82F6; }
.ach-bar.cancel  { background: var(--red); }

/* Dark icon panel */
.ach-icon-panel {
    width: 80px; flex-shrink: 0;
    background: var(--ink);
    display: flex; align-items: center; justify-content: center;
}
.ach-icon-panel svg {
    width: 28px; height: 28px;
    stroke: var(--gold); fill: none;
    stroke-width: 1.4; stroke-linecap: round; stroke-linejoin: round;
    opacity: 0.75;
}

/* Content */
.ach-content {
    flex: 1;
    padding: 20px 24px 18px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    min-width: 0;
    text-align: <?= $isRtl ? 'right' : 'left' ?>;
}

.ach-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.ach-order-num {
    font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "'Cormorant Garamond', serif" ?>;
    font-size: 20px; font-weight: 700; color: var(--ink);
    margin: 0 0 3px;
}
.ach-order-date {
    font-size: 11px; color: var(--ink-muted); font-weight: 600;
    display: flex; align-items: center; gap: 5px;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}

/* Documents list */
.ach-docs {
    background: var(--cream);
    border: 1px solid var(--cream-dark);
    border-radius: 12px;
    overflow: hidden;
}
.ach-doc-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-bottom: 1px dashed var(--cream-dark);
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.ach-doc-row:last-child { border-bottom: none; }

.ach-doc-bullet {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--gold);
    flex-shrink: 0; opacity: 0.7;
}
.ach-doc-title {
    font-size: 13px; font-weight: 700; color: var(--ink);
    flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.ach-doc-author {
    font-size: 11px; color: var(--gold-deep);
    font-weight: 600; white-space: nowrap; flex-shrink: 0;
}
.ach-doc-qty {
    font-size: 10px; font-weight: 700;
    color: var(--ink-muted); background: var(--cream-dark);
    padding: 2px 8px; border-radius: 20px; flex-shrink: 0;
}
.ach-doc-more {
    padding: 7px 14px; font-size: 11px;
    color: var(--ink-muted); font-weight: 600; font-style: italic;
    text-align: <?= $isRtl ? 'right' : 'left' ?>;
}

/* Badge */
.ach-badge {
    flex-shrink: 0;
    padding: 5px 12px; border-radius: 50px;
    font-size: 9px; font-weight: 800;
    letter-spacing: <?= $isRtl ? '0' : '1px' ?>;
    text-transform: uppercase; white-space: nowrap;
}
.bdg-paid    { background: #F0FDF4; color: #15803D; border: 1px solid #BBF7D0; }
.bdg-pending { background: #FFFBEB; color: #92400E; border: 1px solid #FDE68A; }
.bdg-waiting { background: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE; }
.bdg-cancel  { background: #FEF2F2; color: var(--red); border: 1px solid #FECACA; }

/* Bottom row */
.ach-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    padding-top: 4px;
    border-top: 1px solid var(--cream-dark);
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.ach-total-lbl {
    font-size: 9px; font-weight: 700;
    letter-spacing: <?= $isRtl ? '0' : '2px' ?>;
    text-transform: uppercase; color: var(--ink-muted); margin-bottom: 3px;
}
.ach-total-val {
    font-family: 'Cormorant Garamond', serif;
    font-size: 28px; font-weight: 700; color: var(--gold-deep); line-height: 1;
}
.ach-total-val span { font-size: 13px; font-weight: 400; color: var(--ink-muted); margin-<?= $isRtl ? 'right' : 'left' ?>: 2px; }

.ach-actions {
    display: flex; align-items: center; gap: 10px;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}

.btn-pay {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 9px 18px;
    background: var(--gold); border: 1.5px solid var(--gold); color: var(--ink);
    border-radius: 10px; font-size: 12px; font-weight: 700;
    text-decoration: none; transition: background 0.2s, transform 0.15s;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.btn-pay:hover { background: var(--gold-deep); border-color: var(--gold-deep); transform: scale(1.02); }

.btn-cancel {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 14px;
    background: transparent; border: 1.5px solid var(--cream-dark); color: var(--ink-muted);
    border-radius: 10px; font-size: 12px; font-weight: 600;
    text-decoration: none; transition: border-color 0.2s, color 0.2s;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.btn-cancel:hover { border-color: var(--red); color: var(--red); }

.ach-status-msg {
    font-size: 12px; font-weight: 700;
    display: flex; align-items: center; gap: 6px;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.ach-status-msg.green { color: var(--green); }
.ach-status-msg.muted { color: var(--ink-muted); }

/* Empty state */
.ach-empty {
    text-align: center; padding: 80px 40px;
    background: var(--white);
    border: 1.5px dashed var(--cream-dark); border-radius: 24px;
}
.ach-empty h3 {
    font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "'Cormorant Garamond', serif" ?>;
    font-size: 26px; color: var(--ink); margin-bottom: 8px; font-weight: 700;
}
.ach-empty p { font-size: 14px; color: var(--ink-muted); margin-bottom: 24px; }

.btn-browse {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 9px 18px; background: transparent;
    border: 1.5px solid var(--gold); color: var(--gold-deep);
    border-radius: 10px; font-size: 12px; font-weight: 700;
    text-decoration: none; transition: background 0.2s, color 0.2s;
}
.btn-browse:hover { background: var(--gold); color: var(--ink); }

@media (max-width: 600px) {
    .ach-hero    { padding: 80px 16px 44px; }
    .ach-hero h1 { font-size: 38px; }
    .ach-list    { padding: 0 14px; }
    .ach-icon-panel { width: 54px; }
    .ach-content { padding: 14px 14px; }
    .ach-total-val { font-size: 22px; }
    .ach-bottom  { flex-direction: column; align-items: flex-start; }
    .ach-doc-author { display: none; }
}
</style>

<div class="achats-page">

    <!-- HERO -->
    <div class="ach-hero">
        <div class="ach-hero-inner">
            <div class="ach-hero-eyebrow"><?= $p['eyebrow'] ?></div>
            <h1><?= $p['title'] ?></h1>
            <p><?= $p['subtitle'] ?></p>
        </div>
    </div>

    <!-- CARDS -->
    <div class="ach-list">

    <?php if ($commandes && $commandes->num_rows > 0):
        $i = 1;
        while ($commande = $commandes->fetch_assoc()):
            $id_cmd    = $commande['id_commande'];
            $statut    = strtolower(trim($commande['statut']));
            $total_val = $commande['total'];

            // ── تاريخ الطلب مترجم ──────────────────────
            $date_cmd = isset($commande['date_commande']) && $commande['date_commande']
                        ? fmt_date_cmd($commande['date_commande'], $lang) : '';

            // ── الحالة → شريط + badge + نص ─────────────
            if (in_array($statut, ['payée','payee'])) {
                $bar='paid';    $bdg='bdg-paid';    $lbl=$p['st_paid'];
            } elseif ($statut === 'en attente de paiement') {
                $bar='waiting'; $bdg='bdg-waiting'; $lbl=$p['st_waiting'];
            } elseif (in_array($statut, ['annulée','annulee'])) {
                $bar='cancel';  $bdg='bdg-cancel';  $lbl=$p['st_cancelled'];
            } else {
                $bar='pending'; $bdg='bdg-pending'; $lbl=$p['st_pending'];
            }

            $is_paid      = ($bar === 'paid');
            $is_cancelled = ($bar === 'cancel');

            $order_items = $items_by_order[$id_cmd] ?? [];
            $show_items  = array_slice($order_items, 0, 3);
            $extra       = count($order_items) - count($show_items);
    ?>

        <div class="ach-card">
            <div class="ach-bar <?= $bar ?>"></div>

            <div class="ach-icon-panel">
                <svg viewBox="0 0 24 24">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
            </div>

            <div class="ach-content">

                <!-- رقم الطلب + التاريخ + Badge -->
                <div class="ach-top">
                    <div>
                        <div class="ach-order-num"><?= $p['order_num'] . $i++ ?></div>
                        <?php if ($date_cmd): ?>
                        <div class="ach-order-date">
                            <i class="fas fa-calendar-alt" style="font-size:9px;"></i>
                            <?= $date_cmd ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <span class="ach-badge <?= $bdg ?>"><?= $lbl ?></span>
                </div>

                <!-- عناوين الكتب -->
                <?php if (!empty($show_items)): ?>
                <div class="ach-docs">
                    <?php foreach ($show_items as $item): ?>
                    <div class="ach-doc-row">
                        <div class="ach-doc-bullet"></div>
                        <div class="ach-doc-title"><?= htmlspecialchars($item['titre']) ?></div>
                        <div class="ach-doc-author"><?= htmlspecialchars($item['auteur']) ?></div>
                        <?php if ($item['quantite'] > 1): ?>
                        <div class="ach-doc-qty">×<?= $item['quantite'] ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php if ($extra > 0): ?>
                    <div class="ach-doc-more"><?= fmt_more($extra, $lang) ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- المجموع + الأزرار -->
                <div class="ach-bottom">
                    <div>
                        <div class="ach-total-lbl"><?= $p['lbl_total'] ?></div>
                        <div class="ach-total-val">
                            <?= number_format($total_val, 0, ',', ' ') ?><span>DA</span>
                        </div>
                    </div>

                    <?php if (!$is_paid && !$is_cancelled): ?>
                    <div class="ach-actions">
                        <a href="annuler_commande.php?id=<?= $id_cmd ?>"
                           class="btn-cancel"
                           onclick="return confirm('<?= addslashes($p['confirm_cancel']) ?>')">
                            <i class="fas fa-times"></i> <?= $p['btn_cancel'] ?>
                        </a>
                        <a href="paiement.php?id=<?= $id_cmd ?>&total=<?= $total_val ?>" class="btn-pay">
                            <i class="fas fa-credit-card"></i> <?= $p['btn_pay'] ?>
                        </a>
                    </div>

                    <?php elseif ($is_paid): ?>
                    <div class="ach-status-msg green">
                        <i class="fas fa-check-circle"></i> <?= $p['msg_paid'] ?>
                    </div>

                    <?php else: ?>
                    <div class="ach-status-msg muted">
                        <i class="fas fa-ban"></i> <?= $p['msg_cancelled'] ?>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    <?php endwhile; ?>

    <?php else: ?>
        <div class="ach-empty">
            <svg style="width:38px;height:38px;stroke:var(--gold);fill:none;stroke-width:1.3;stroke-linecap:round;stroke-linejoin:round;margin:0 auto 16px;display:block;" viewBox="0 0 24 24">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
            <h3><?= $p['empty_title'] ?></h3>
            <p><?= $p['empty_sub'] ?></p>
            <a href="../client/library.php" class="btn-browse">
                <i class="fas fa-compass"></i> <?= $p['btn_explore'] ?>
            </a>
        </div>
    <?php endif; ?>

    </div>
</div>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>