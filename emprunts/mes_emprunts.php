<?php
include_once __DIR__ . "/../includes/header.php"; 
// $text و t() و $lang متاحة تلقائياً من header.php ← languages.php

if (!$is_logged_in) {
    header("Location: " . $base . "/auth/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$today = date('Y-m-d');

$countQuery = "SELECT 
    COUNT(*) as total, 
    SUM(CASE WHEN statut = 'acceptée' AND ('$today' <= date_retour_prevue OR date_retour_prevue IS NULL) THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN statut = 'retard' OR (statut = 'acceptée' AND '$today' > date_retour_prevue AND date_retour_prevue IS NOT NULL) THEN 1 ELSE 0 END) as late
    FROM emprunt WHERE id_user = $id_user";
$counts = $conn->query($countQuery)->fetch_assoc();

$total  = $counts['total']  ?? 0;
$active = $counts['active'] ?? 0;
$late   = $counts['late']   ?? 0;

$query = "SELECT e.*, d.titre, d.auteur, d.id_doc
          FROM emprunt e 
          JOIN documents d ON e.id_doc = d.id_doc 
          WHERE e.id_user = $id_user 
          ORDER BY e.date_debut DESC";
$result = $conn->query($query);

// ── نصوص الصفحة حسب اللغة ──────────────────────────────
$pg = [
    'fr' => [
        'eyebrow'        => 'Ma bibliothèque',
        'title'          => 'Mes <em>Emprunts</em>',
        'subtitle'       => 'Gérez vos lectures et suivez vos délais en toute simplicité.',
        'lbl_total'      => 'Total',
        'lbl_active'     => 'Lectures actives',
        'lbl_late'       => 'En retard',
        'lbl_borrowed'   => 'Emprunté le',
        'lbl_deadline'   => 'Date limite',
        'lbl_confirm'    => 'À confirmer',
        'lbl_unavail'    => 'Document actuellement indisponible.',
        'lbl_prolong'    => 'Prolonger de 7 jours',
        'lbl_explore'    => 'Explorer le catalogue',
        'empty_title'    => 'Aucun emprunt trouvé',
        'empty_sub'      => 'Commencez votre aventure littéraire dès maintenant.',
        'st_active'      => 'En cours',
        'st_returned'    => 'Rendu',
        'st_late'        => 'En retard',
        'st_pending'     => 'En attente',
        'st_refused'     => 'Indisponible',
    ],
    'en' => [
        'eyebrow'        => 'My Library',
        'title'          => 'My <em>Loans</em>',
        'subtitle'       => 'Manage your readings and track your deadlines easily.',
        'lbl_total'      => 'Total',
        'lbl_active'     => 'Active loans',
        'lbl_late'       => 'Overdue',
        'lbl_borrowed'   => 'Borrowed on',
        'lbl_deadline'   => 'Due date',
        'lbl_confirm'    => 'To be confirmed',
        'lbl_unavail'    => 'Document currently unavailable.',
        'lbl_prolong'    => 'Extend by 7 days',
        'lbl_explore'    => 'Explore the catalogue',
        'empty_title'    => 'No loans found',
        'empty_sub'      => 'Start your literary adventure now.',
        'st_active'      => 'Active',
        'st_returned'    => 'Returned',
        'st_late'        => 'Overdue',
        'st_pending'     => 'Pending',
        'st_refused'     => 'Unavailable',
    ],
    'ar' => [
        'eyebrow'        => 'مكتبتي',
        'title'          => '<em>استعاراتي</em>',
        'subtitle'       => 'تابع قراءاتك ومواعيد الإعادة بكل سهولة.',
        'lbl_total'      => 'المجموع',
        'lbl_active'     => 'استعارات نشطة',
        'lbl_late'       => 'متأخرة',
        'lbl_borrowed'   => 'تاريخ الاستعارة',
        'lbl_deadline'   => 'تاريخ الإعادة',
        'lbl_confirm'    => 'في انتظار التأكيد',
        'lbl_unavail'    => 'الكتاب غير متاح حالياً.',
        'lbl_prolong'    => 'تمديد 7 أيام',
        'lbl_explore'    => 'استكشف الكتالوج',
        'empty_title'    => 'لا توجد استعارات',
        'empty_sub'      => 'ابدأ مغامرتك الأدبية الآن.',
        'st_active'      => 'جارية',
        'st_returned'    => 'مُعادة',
        'st_late'        => 'متأخرة',
        'st_pending'     => 'في الانتظار',
        'st_refused'     => 'غير متاح',
    ],
];

// اختيار اللغة الحالية مع fallback للفرنسية
$p = $pg[$lang] ?? $pg['fr'];
$isRtl = ($lang === 'ar');

// ── دالة تنسيق التاريخ حسب اللغة ──────────────────────
function fmt_date(string $date_str, string $lang): string {
    if (!$date_str || $date_str === '0000-00-00') return '—';
    $ts = strtotime($date_str);
    if (!$ts) return '—';
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

.emprunts-page {
    background: var(--cream);
    min-height: 100vh;
    padding: 0 0 80px;
    font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "'Lato', sans-serif" ?>;
    direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
}

.emp-hero {
    background: var(--ink);
    padding: 90px 24px 60px;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.emp-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 55% 80% at 10% 60%, rgba(196,164,107,0.09) 0%, transparent 65%),
        radial-gradient(ellipse 45% 60% at 90% 20%, rgba(196,164,107,0.07) 0%, transparent 65%);
    pointer-events: none;
}
.emp-hero::after {
    content: '';
    position: absolute;
    bottom: 0; left: 12%; right: 12%;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(196,164,107,0.45), transparent);
}
.emp-hero-inner { position: relative; z-index: 1; }

.emp-hero-eyebrow {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: <?= $isRtl ? '1px' : '5px' ?>;
    text-transform: uppercase;
    color: var(--gold);
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
}
.emp-hero-eyebrow::before,
.emp-hero-eyebrow::after {
    content: '';
    width: 30px; height: 1px;
    background: var(--gold);
    opacity: 0.45;
}
.emp-hero h1 {
    font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "'Cormorant Garamond', serif" ?>;
    font-size: <?= $isRtl ? '42px' : '54px' ?>;
    font-weight: 700;
    color: #fff;
    margin: 0 0 12px;
    letter-spacing: <?= $isRtl ? '0' : '-1px' ?>;
    line-height: 1.2;
}
.emp-hero h1 em { font-style: italic; color: var(--gold); }
.emp-hero p {
    font-size: 13px;
    color: rgba(255,255,255,0.38);
    letter-spacing: 0.4px;
}

.stats-strip {
    width: 95%;
    max-width: 1400px;
    margin: 44px auto 44px;
    padding: 0 24px;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}
.stat-tile {
    background: var(--white);
    border: 1px solid var(--cream-dark);
    border-radius: 18px;
    padding: 28px 12px 22px;
    text-align: center;
    box-shadow: 0 6px 28px rgba(44,31,14,0.07);
    position: relative;
    overflow: hidden;
    transition: transform 0.25s, box-shadow 0.25s;
}
.stat-tile:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 40px rgba(196,164,107,0.15);
}
.stat-tile::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 18px 18px 0 0;
    background: var(--cream-dark);
}
.stat-tile.t-gold::before  { background: linear-gradient(90deg, transparent, var(--gold), transparent); }
.stat-tile.t-green::before { background: linear-gradient(90deg, transparent, var(--green), transparent); }
.stat-tile.t-red::before   { background: linear-gradient(90deg, transparent, var(--red), transparent); }

.stat-svg {
    display: block;
    width: 24px; height: 24px;
    margin: 0 auto 14px;
    stroke: var(--gold);
    fill: none;
    stroke-width: 1.5;
    stroke-linecap: round;
    stroke-linejoin: round;
    opacity: 0.85;
}
.stat-tile.t-green .stat-svg { stroke: var(--green); }
.stat-tile.t-red   .stat-svg { stroke: var(--red); }

.stat-num {
    font-family: 'Cormorant Garamond', serif;
    font-size: 52px;
    font-weight: 700;
    color: var(--ink);
    line-height: 1;
    display: block;
}
.stat-tile.t-green .stat-num { color: var(--green); }
.stat-tile.t-red   .stat-num { color: var(--red); }

.stat-lbl {
    display: block;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: <?= $isRtl ? '0' : '2.5px' ?>;
    text-transform: uppercase;
    color: var(--ink-muted);
    margin-top: 10px;
}

.emp-list {
    width: 95%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.emp-card {
    background: var(--white);
    border: 1px solid var(--cream-dark);
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    box-shadow: 0 4px 20px rgba(44,31,14,0.06);
    transition: box-shadow 0.3s, transform 0.3s;
}
.emp-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 44px rgba(196,164,107,0.14);
}
.emp-card.refused { opacity: 0.65; }

.emp-bar {
    width: 4px;
    flex-shrink: 0;
    background: var(--cream-dark);
}
.emp-bar.b-cours   { background: #3B82F6; }
.emp-bar.b-rendu   { background: var(--green); }
.emp-bar.b-retard  { background: var(--red); }
.emp-bar.b-attente { background: var(--amber); }
.emp-bar.b-refuse  { background: #9B1C1C; }

.emp-cover {
    width: 106px;
    flex-shrink: 0;
    background: var(--ink);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 18px 12px;
}
.emp-cover img {
    width: 74px; height: 108px;
    object-fit: cover;
    border-radius: 3px;
    box-shadow: 4px 6px 18px rgba(0,0,0,0.5);
}

.emp-content {
    flex: 1;
    padding: 20px 22px 18px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-width: 0;
    text-align: <?= $isRtl ? 'right' : 'left' ?>;
}
.emp-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.emp-title {
    font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "'Cormorant Garamond', serif" ?>;
    font-size: 19px;
    font-weight: 700;
    color: var(--ink);
    margin: 0 0 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.emp-author {
    font-size: 11px;
    font-weight: 600;
    color: var(--gold-deep);
    display: flex;
    align-items: center;
    gap: 5px;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}

.emp-badge {
    flex-shrink: 0;
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: <?= $isRtl ? '0' : '1px' ?>;
    text-transform: uppercase;
    white-space: nowrap;
}
.bdg-cours   { background: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE; }
.bdg-rendu   { background: #F0FDF4; color: #15803D; border: 1px solid #BBF7D0; }
.bdg-retard  { background: #FEF2F2; color: var(--red); border: 1px solid #FECACA; animation: pulse-r 2s infinite; }
.bdg-attente { background: #FFFBEB; color: #92400E; border: 1px solid #FDE68A; }
.bdg-refuse  { background: #FFF1F2; color: #9B1C1C; border: 1px solid #FECDD3; }

@keyframes pulse-r {
    0%,100% { box-shadow: 0 0 0 0 rgba(192,57,43,0); }
    50%      { box-shadow: 0 0 0 6px rgba(192,57,43,0.12); }
}

.emp-dates {
    display: flex;
    gap: 28px;
    margin-bottom: 14px;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.emp-date-lbl {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: <?= $isRtl ? '0' : '1.5px' ?>;
    text-transform: uppercase;
    color: var(--ink-muted);
    margin-bottom: 3px;
}
.emp-date-val {
    font-size: 13px;
    font-weight: 700;
    color: var(--ink);
}
.emp-date-val.late { color: var(--red); }

.refused-msg {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 11px;
    color: #9B1C1C;
    background: #FFF1F2;
    border: 1px solid #FECDD3;
    padding: 6px 12px;
    border-radius: 8px;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}

.btn-prolong {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: transparent;
    border: 1.5px solid var(--gold);
    color: var(--gold-deep);
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s, color 0.2s, transform 0.15s;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.btn-prolong:hover { background: var(--gold); color: var(--ink); transform: scale(1.03); }

.emp-empty {
    text-align: center;
    padding: 80px 40px;
    background: var(--white);
    border: 1.5px dashed var(--cream-dark);
    border-radius: 24px;
}
.emp-empty h3 {
    font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "'Cormorant Garamond', serif" ?>;
    font-size: 26px;
    color: var(--ink);
    margin-bottom: 8px;
    font-weight: 700;
}
.emp-empty p { font-size: 14px; color: var(--ink-muted); margin-bottom: 24px; }

@media (max-width: 600px) {
    .emp-hero   { padding: 80px 16px 44px; }
    .emp-hero h1 { font-size: 38px; }
    .stats-strip, .emp-list { padding: 0 14px; }
    .stat-num   { font-size: 38px; }
    .emp-cover  { width: 76px; padding: 14px 8px; }
    .emp-cover img { width: 56px; height: 82px; }
    .emp-content { padding: 14px 12px; }
    .emp-title  { font-size: 15px; }
    .emp-dates  { gap: 16px; flex-wrap: wrap; }
}
</style>

<div class="emprunts-page">

    <!-- HERO -->
    <div class="emp-hero">
        <div class="emp-hero-inner">
            <div class="emp-hero-eyebrow"><?= $p['eyebrow'] ?></div>
            <h1><?= $p['title'] ?></h1>
            <p><?= $p['subtitle'] ?></p>
        </div>
    </div>

    <!-- STATS -->
    <div class="stats-strip">

        <div class="stat-tile t-gold">
            <svg class="stat-svg" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            <span class="stat-num"><?= $total ?></span>
            <span class="stat-lbl"><?= $p['lbl_total'] ?></span>
        </div>

        <div class="stat-tile t-green">
            <svg class="stat-svg" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            <span class="stat-num"><?= $active ?></span>
            <span class="stat-lbl"><?= $p['lbl_active'] ?></span>
        </div>

        <div class="stat-tile <?= $late > 0 ? 't-red' : '' ?>">
            <svg class="stat-svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span class="stat-num"><?= $late ?></span>
            <span class="stat-lbl"><?= $p['lbl_late'] ?></span>
        </div>

    </div>

    <!-- CARDS -->
    <div class="emp-list">

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($e = $result->fetch_assoc()):
            $date_prevue = $e['date_retour_prevue'];
            $statut_brut = strtolower(trim($e['statut']));

            if (in_array($statut_brut, ['rendu','retourné'])) {
                $bar='b-rendu';  $bdg='bdg-rendu';  $lbl=$p['st_returned'];
            } elseif (in_array($statut_brut, ['refusée','refusee'])) {
                $bar='b-refuse'; $bdg='bdg-refuse'; $lbl=$p['st_refused'];
            } elseif ($statut_brut === 'en attente') {
                $bar='b-attente';$bdg='bdg-attente';$lbl=$p['st_pending'];
            } elseif ($statut_brut === 'retard' || ($date_prevue && $today > $date_prevue)) {
                $bar='b-retard'; $bdg='bdg-retard'; $lbl=$p['st_late'];
            } else {
                $bar='b-cours';  $bdg='bdg-cours';  $lbl=$p['st_active'];
            }

            $is_refused = in_array($statut_brut, ['refusée','refusee']);
            $is_late    = ($statut_brut === 'retard' || ($date_prevue && $today > $date_prevue));
        ?>

        <div class="emp-card <?= $is_refused ? 'refused' : '' ?>">
            <div class="emp-bar <?= $bar ?>"></div>

            <div class="emp-cover">
                <img src="../uploads/<?= $e['id_doc'] ?>.jpg"
                     onerror="this.src='../uploads/default.jpg'" alt="">
            </div>

            <div class="emp-content">
                <div class="emp-top">
                    <div style="min-width:0;">
                        <div class="emp-title"><?= htmlspecialchars($e['titre']) ?></div>
                        <div class="emp-author">
                            <i class="fas fa-feather-alt"></i>
                            <?= htmlspecialchars($e['auteur']) ?>
                        </div>
                    </div>
                    <span class="emp-badge <?= $bdg ?>"><?= $lbl ?></span>
                </div>

                <div class="emp-dates">
                    <div>
                        <div class="emp-date-lbl"><?= $p['lbl_borrowed'] ?></div>
                        <div class="emp-date-val"><?= fmt_date($e['date_debut'], $lang) ?></div>
                    </div>
                    <div>
                        <div class="emp-date-lbl"><?= $p['lbl_deadline'] ?></div>
                        <div class="emp-date-val <?= $is_late ? 'late' : '' ?>">
                            <?php if ($is_refused): ?>
                                <span style="color:var(--ink-muted)">—</span>
                            <?php elseif ($date_prevue && $date_prevue !== '0000-00-00'): ?>
                                <?= fmt_date($date_prevue, $lang) ?>
                            <?php else: ?>
                                <?= $p['lbl_confirm'] ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if ($is_refused): ?>
                    <div class="refused-msg">
                        <i class="fas fa-times-circle"></i>
                        <?= $p['lbl_unavail'] ?>
                    </div>
                <?php elseif ($statut_brut === 'acceptée' && (!$date_prevue || $today <= $date_prevue)): ?>
                    <form method="POST" action="prolonger_action.php" style="margin:0;">
                        <input type="hidden" name="id_emprunt" value="<?= $e['id_emprunt'] ?>">
                        <button type="submit" class="btn-prolong">
                            <i class="fas fa-history"></i>
                            <?= $p['lbl_prolong'] ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php endwhile; ?>

    <?php else: ?>
        <div class="emp-empty">
            <svg style="width:38px;height:38px;stroke:var(--gold);fill:none;stroke-width:1.3;stroke-linecap:round;stroke-linejoin:round;margin:0 auto 16px;display:block;" viewBox="0 0 24 24">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
            <h3><?= $p['empty_title'] ?></h3>
            <p><?= $p['empty_sub'] ?></p>
            <a href="../client/library.php" class="btn-prolong">
                <i class="fas fa-compass"></i>
                <?= $p['lbl_explore'] ?>
            </a>
        </div>
    <?php endif; ?>

    </div>
</div>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>