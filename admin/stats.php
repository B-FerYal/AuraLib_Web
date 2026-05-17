<?php
require_once "../includes/db.php";
include_once '../includes/languages.php';

// ── نصوص الصفحة ─────────────────────────────────────────
$pg = [
    'fr' => [
        'page_title'   => 'Analyses & Statistiques — AuraLib',
        'dash_title'   => 'Analyses & Statistiques',
        'dash_sub'     => 'Suivi des performances de AuraLib',
        'chart_rev'    => '📈 Évolution des Revenus (DA)',
        'chart_cat'    => '📊 Répartition par Catégorie',
        'dataset_lbl'  => 'Revenu Mensuel',
        'click_hint'   => 'Cliquer sur une tranche',
        'cat_prefix'   => '📈 Revenus — ',
        'btn_all'      => '↩ Tout',
        'stat_active'  => 'Emprunts Actifs',
        'stat_late'    => 'En Retard',
        'stat_pending' => 'En Attente',
        'stat_returned'=> 'Rendus',
    ],
    'en' => [
        'page_title'   => 'Analytics & Statistics — AuraLib',
        'dash_title'   => 'Analytics & Statistics',
        'dash_sub'     => 'AuraLib performance overview',
        'chart_rev'    => '📈 Revenue Trend (DA)',
        'chart_cat'    => '📊 Distribution by Category',
        'dataset_lbl'  => 'Monthly Revenue',
        'click_hint'   => 'Click a slice',
        'cat_prefix'   => '📈 Revenue — ',
        'btn_all'      => '↩ All',
        'stat_active'  => 'Active Loans',
        'stat_late'    => 'Overdue',
        'stat_pending' => 'Pending',
        'stat_returned'=> 'Returned',
    ],
    'ar' => [
        'page_title'   => 'التحليلات والإحصائيات — AuraLib',
        'dash_title'   => 'التحليلات والإحصائيات',
        'dash_sub'     => 'متابعة أداء AuraLib',
        'chart_rev'    => '📈 تطور الإيرادات (دج)',
        'chart_cat'    => '📊 التوزيع حسب الفئة',
        'dataset_lbl'  => 'الإيراد الشهري',
        'click_hint'   => 'انقر على فئة',
        'cat_prefix'   => '📈 إيرادات — ',
        'btn_all'      => 'الكل ↩',
        'stat_active'  => 'استعارات نشطة',
        'stat_late'    => 'متأخرة',
        'stat_pending' => 'قيد الانتظار',
        'stat_returned'=> 'مُعادة',
    ],
];
$p     = $pg[$lang] ?? $pg['fr'];
$isRtl = ($lang === 'ar');

// جلب الإحصائيات
$res_active  = $conn->query("SELECT COUNT(*) as total FROM emprunt WHERE statut = 'acceptée' AND date_fin IS NULL");
$nb_en_cours = (int)$res_active->fetch_assoc()['total'];

$res_retard  = $conn->query("SELECT COUNT(*) as total FROM emprunt WHERE (statut = 'retard') OR (statut = 'acceptée' AND date_retour_prevue < CURDATE() AND date_fin IS NULL)");
$nb_retards  = (int)$res_retard->fetch_assoc()['total'];

$res_pending = $conn->query("SELECT COUNT(*) as total FROM emprunt WHERE statut = 'en attente'");
$nb_demandes = (int)$res_pending->fetch_assoc()['total'];

$res_rendu   = $conn->query("SELECT COUNT(*) as total FROM emprunt WHERE statut = 'rendu'");
$nb_rendu    = (int)$res_rendu->fetch_assoc()['total'];

include "../includes/header.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location:../client/library.php");
    exit;
}

// --- 1. بيانات المبيعات ---
$months = []; $revenues = [];
$sales_query = $conn->query("SELECT MONTHNAME(date_commande) as m, SUM(total) as s FROM commande WHERE statut IN ('payee', 'payée', 'Terminé') GROUP BY MONTH(date_commande) ORDER BY MONTH(date_commande)");
while($row = $sales_query->fetch_assoc()) { $months[] = $row['m']; $revenues[] = (float)$row['s']; }

// --- 2. توزيع الأصناف ---
$categories = []; $counts = [];
$cat_query = $conn->query("SELECT categorie, COUNT(*) as total FROM documents GROUP BY categorie");
while($row = $cat_query->fetch_assoc()) { $categories[] = $row['categorie']; $counts[] = (int)$row['total']; }

// --- 3. إيرادات كل فئة شهرياً ---
$cat_revenues = [];
foreach ($categories as $cat) {
    $cat_esc = $conn->real_escape_string($cat);
    $q = $conn->query("
        SELECT MONTHNAME(c.date_commande) as m, SUM(c.total) as s
        FROM commande c
        JOIN commande_item ci ON ci.id_commande = c.id_commande
        JOIN documents d ON d.id_doc = ci.id_doc
        WHERE c.statut IN ('payee','payée','Terminé')
          AND d.categorie = '$cat_esc'
        GROUP BY MONTH(c.date_commande)
        ORDER BY MONTH(c.date_commande)
    ");
    $cm = []; $cs = [];
    if ($q) { while($r = $q->fetch_assoc()) { $cm[] = $r['m']; $cs[] = (float)$r['s']; } }
    $cat_revenues[$cat] = ['months' => $cm, 'revenues' => $cs];
}
?>

<title><?= $p['page_title'] ?></title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ══ TOKENS AuraLib ══ */
:root {
    --gold:        #C4A46B;
    --gold2:       #D4B47B;
    --gold-deep:   #A8884E;
    --gold-faint:  rgba(196,164,107,.08);
    --gold-border: rgba(196,164,107,.25);
    --ink:         #2C1F0E;
    --page-bg:     #F5F0E8;
    --page-bg2:    #EDE5D4;
    --page-white:  #FFFDF9;
    --page-text:   #2C1F0E;
    --page-muted:  #9A8C7E;
    --page-border: #DDD5C8;
    --success:     #2E7D52;
    --danger:      #C0392B;
    --warning:     #B8832A;
    --font-serif:  'Playfair Display', serif;
    --font-ui:     <?= $isRtl ? "'Tajawal', sans-serif" : "'Plus Jakarta Sans', sans-serif" ?>;
    --nav-h:       66px;
    --shadow-sm:   0 3px 12px rgba(44,31,14,.07);
    --shadow-md:   0 8px 30px rgba(44,31,14,.10);
    --shadow-gold: 0 6px 20px rgba(196,164,107,.22);
    --tr:          .22s cubic-bezier(.4,0,.2,1);
}
html.dark {
    --page-bg:    #100C07; --page-bg2:   #1A1308;
    --page-white: #1E1610; --page-text:  #EDE5D4;
    --page-muted: #9A8C7E; --page-border:#3A2E1E;
}

body {
    margin-top: 0 !important;
    display: block;
    direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
    font-family: var(--font-ui);
    background: var(--page-bg);
    color: var(--page-text);
    transition: background .35s, color .35s;
}

/* ══ HERO ══ */
.stats-hero {
    background: linear-gradient(135deg, #1A0E05 0%, #2E1D08 55%, #1A0E05 100%);
    padding: 28px 5%;
    border-bottom: 1px solid rgba(196,164,107,.15);
    margin-top: var(--nav-h);
}
.stats-hero-title {
    font-family: var(--font-serif);
    font-size: clamp(22px, 3vw, 32px);
    font-weight: 700;
    color: #FDFAF5;
    line-height: 1;
    margin-bottom: 5px;
    text-align: <?= $isRtl ? 'right' : 'left' ?>;
}
.stats-hero-sub {
    font-size: 12px;
    color: rgba(253,250,245,.4);
    letter-spacing: .3px;
    text-align: <?= $isRtl ? 'right' : 'left' ?>;
}

/* ══ WRAP ══ */
.adm-wrap { display: flex; min-height: calc(100vh - var(--nav-h)); background: var(--page-bg); }
.adm-main { flex: 1; min-width: 0; padding: 32px 40px 60px; transition: all 0.3s ease; }

/* ══ STATS CARDS ══ */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 28px;
}
@media (max-width: 900px) { .stats-cards { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px) { .stats-cards { grid-template-columns: 1fr 1fr; } }

.stat-card {
    background: var(--page-white);
    border: 1px solid var(--page-border);
    border-radius: 14px;
    padding: 20px 18px;
    border-top: 3px solid var(--gold);
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    text-align: <?= $isRtl ? 'right' : 'left' ?>;
    transition: transform var(--tr), box-shadow var(--tr);
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.stat-card.green  { border-top-color: var(--success); }
.stat-card.red    { border-top-color: var(--danger); }
.stat-card.amber  { border-top-color: var(--warning); }
.stat-card::after {
    content: '';
    position: absolute; right: -10px; bottom: -10px;
    width: 60px; height: 60px; border-radius: 50%;
    background: rgba(196,164,107,.05);
    pointer-events: none;
}
.stat-lbl {
    font-size: 10px; font-weight: 700;
    letter-spacing: <?= $isRtl ? '0' : '1px' ?>;
    text-transform: uppercase;
    color: var(--page-muted);
    margin-bottom: 8px;
}
.stat-val {
    font-family: var(--font-serif);
    font-size: 32px; font-weight: 700;
    color: var(--page-text); line-height: 1;
}

/* ══ CHART CARDS ══ */
.charts-grid {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 20px;
}
@media (max-width: 1100px) { .charts-grid { grid-template-columns: 1fr; } }

.chart-card {
    background: var(--page-white);
    border: 1px solid var(--page-border);
    border-radius: 16px;
    padding: 24px;
    box-shadow: var(--shadow-sm);
    transition: box-shadow var(--tr);
}
.chart-card:hover { box-shadow: var(--shadow-md); }

.chart-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    gap: 8px;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.chart-lbl  {
    font-weight: 700;
    color: var(--page-text);
    font-size: 14px;
}
.chart-hint {
    font-size: 10px;
    color: var(--page-muted);
    font-style: italic;
    background: var(--gold-faint);
    border: 1px solid var(--gold-border);
    padding: 3px 10px;
    border-radius: 20px;
}

.btn-reset-rev {
    display: none;
    background: transparent;
    border: 1.5px solid var(--gold);
    color: var(--gold-deep);
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    font-family: var(--font-ui);
    transition: background var(--tr), color var(--tr);
    white-space: nowrap;
}
.btn-reset-rev:hover { background: var(--gold); color: var(--ink); }
html.dark .btn-reset-rev { color: var(--gold); }

#genreChart { cursor: pointer; }

.chart-container {
    position: relative;
    height: 360px;
    width: 100%;
}

/* dark mode chart cards */
html.dark .chart-card { background: var(--page-white); border-color: var(--page-border); }
html.dark .stat-card  { background: var(--page-white); border-color: var(--page-border); }
html.dark .stat-val   { color: var(--page-text); }
</style>

<!-- HERO -->
<div class="stats-hero">
    <div class="stats-hero-title"><?= $p['dash_title'] ?></div>
    <div class="stats-hero-sub"><?= $p['dash_sub'] ?></div>
</div>

<div class="adm-wrap">
    <div class="adm-main">

        <!-- ══ STATS CARDS ══ -->
        <div class="stats-cards">
            <div class="stat-card green">
                <div class="stat-lbl"><?= $p['stat_active'] ?></div>
                <div class="stat-val"><?= $nb_en_cours ?></div>
            </div>
            <div class="stat-card red">
                <div class="stat-lbl"><?= $p['stat_late'] ?></div>
                <div class="stat-val"><?= $nb_retards ?></div>
            </div>
            <div class="stat-card amber">
                <div class="stat-lbl"><?= $p['stat_pending'] ?></div>
                <div class="stat-val"><?= $nb_demandes ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-lbl"><?= $p['stat_returned'] ?></div>
                <div class="stat-val"><?= $nb_rendu ?></div>
            </div>
        </div>

        <!-- ══ CHARTS ══ -->
        <div class="charts-grid">

            <div class="chart-card">
                <div class="chart-top">
                    <div class="chart-lbl" id="revLabel"><?= $p['chart_rev'] ?></div>
                    <button class="btn-reset-rev" id="btnReset" onclick="resetChart()"><?= $p['btn_all'] ?></button>
                </div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-top">
                    <div class="chart-lbl"><?= $p['chart_cat'] ?></div>
                    <span class="chart-hint"><?= $p['click_hint'] ?></span>
                </div>
                <div class="chart-container">
                    <canvas id="genreChart"></canvas>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#9A8C7E';

const GLOBAL_MONTHS   = <?php echo json_encode($months); ?>;
const GLOBAL_REVENUES = <?php echo json_encode($revenues); ?>;
const CAT_REVENUES    = <?php echo json_encode($cat_revenues); ?>;
const LBL_GLOBAL      = <?= json_encode($p['dataset_lbl']) ?>;
const LBL_REV_TITLE   = <?= json_encode($p['chart_rev']) ?>;
const CAT_PREFIX      = <?= json_encode($p['cat_prefix']) ?>;

const BG_COLORS = [
    '#2C1F0E','#C4A46B','#9A8C7E','#EDE5D4','#D4C5B0',
    '#B8832A','#7A5C3A','#4a6fa5','#6b7280','#16a34a',
    '#dc2626','#7c3aed','#0891b2','#b45309','#be185d',
    '#475569','#059669','#d97706','#6366f1','#e11d48'
];

// ── 1. Line Chart ──
const ctxRev = document.getElementById('revenueChart').getContext('2d');

function makeGrad(ctx, color) {
    const g = ctx.createLinearGradient(0, 0, 0, 400);
    g.addColorStop(0, color + '44');
    g.addColorStop(1, color + '00');
    return g;
}

const revenueChart = new Chart(ctxRev, {
    type: 'line',
    data: {
        labels: GLOBAL_MONTHS,
        datasets: [{
            label: LBL_GLOBAL,
            data: GLOBAL_REVENUES,
            borderColor: '#C4A46B',
            backgroundColor: makeGrad(ctxRev, '#C4A46B'),
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#C4A46B',
            pointRadius: 5
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#F0EBE3' } },
            x: { grid: { display: false } }
        }
    }
});

function updateRevenueChart(cat, color) {
    const data = CAT_REVENUES[cat] || { months: [], revenues: [] };
    const ds   = revenueChart.data.datasets[0];
    revenueChart.data.labels = data.months;
    ds.data             = data.revenues;
    ds.borderColor      = color;
    ds.pointBorderColor = color;
    ds.backgroundColor  = makeGrad(ctxRev, color);
    ds.label            = cat;
    document.getElementById('revLabel').innerHTML =
        CAT_PREFIX + '<em style="color:' + color + ';font-style:normal">' + cat + '</em>';
    document.getElementById('btnReset').style.display = 'inline-block';
    revenueChart.update('active');
}

function resetChart() {
    const ds = revenueChart.data.datasets[0];
    revenueChart.data.labels = GLOBAL_MONTHS;
    ds.data             = GLOBAL_REVENUES;
    ds.borderColor      = '#C4A46B';
    ds.pointBorderColor = '#C4A46B';
    ds.backgroundColor  = makeGrad(ctxRev, '#C4A46B');
    ds.label            = LBL_GLOBAL;
    document.getElementById('revLabel').textContent   = LBL_REV_TITLE;
    document.getElementById('btnReset').style.display = 'none';
    revenueChart.update('active');
}

// ── 2. Doughnut Chart ──
const ctxGen = document.getElementById('genreChart').getContext('2d');
new Chart(ctxGen, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($categories); ?>,
        datasets: [{
            data: <?php echo json_encode($counts); ?>,
            backgroundColor: BG_COLORS,
            borderWidth: 5,
            borderColor: '#FFFDF9'
        }]
    },
    options: {
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
        },
        onClick(evt, elements) {
            if (!elements.length) { resetChart(); return; }
            const idx   = elements[0].index;
            const cat   = this.data.labels[idx];
            const color = BG_COLORS[idx % BG_COLORS.length];
            updateRevenueChart(cat, color);
        }
    }
});
</script>

<?php include "../includes/footer.php"; ?>