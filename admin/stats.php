<?php
require_once "../includes/db.php";
include_once '../includes/languages.php';

// ── نصوص الصفحة ─────────────────────────────────────────
$pg = [
    'fr' => [
        'page_title'  => 'Analyses & Statistiques — AuraLib',
        'dash_title'  => 'Analyses & Statistiques',
        'dash_sub'    => 'Suivi des performances de AuraLib',
        'chart_rev'   => '📈 Évolution des Revenus (DA)',
        'chart_cat'   => '📊 Répartition par Catégorie',
        'dataset_lbl' => 'Revenu Mensuel',
        'click_hint'  => 'Cliquer sur une tranche',
        'cat_prefix'  => '📈 Revenus — ',
        'btn_all'     => '↩ Tout',
    ],
    'en' => [
        'page_title'  => 'Analytics & Statistics — AuraLib',
        'dash_title'  => 'Analytics & Statistics',
        'dash_sub'    => 'AuraLib performance overview',
        'chart_rev'   => '📈 Revenue Trend (DA)',
        'chart_cat'   => '📊 Distribution by Category',
        'dataset_lbl' => 'Monthly Revenue',
        'click_hint'  => 'Click a slice',
        'cat_prefix'  => '📈 Revenue — ',
        'btn_all'     => '↩ All',
    ],
    'ar' => [
        'page_title'  => 'التحليلات والإحصائيات — AuraLib',
        'dash_title'  => 'التحليلات والإحصائيات',
        'dash_sub'    => 'متابعة أداء AuraLib',
        'chart_rev'   => '📈 تطور الإيرادات (دج)',
        'chart_cat'   => '📊 التوزيع حسب الفئة',
        'dataset_lbl' => 'الإيراد الشهري',
        'click_hint'  => 'انقر على فئة',
        'cat_prefix'  => '📈 إيرادات — ',
        'btn_all'     => 'الكل ↩',
    ],
];
$p     = $pg[$lang] ?? $pg['fr'];
$isRtl = ($lang === 'ar');

// جلب الإحصائيات من قاعدة البيانات
$res_active = $conn->query("SELECT COUNT(*) as total FROM emprunt WHERE statut = 'acceptée' AND date_fin IS NULL");
$row_active = $res_active->fetch_assoc();
$nb_en_cours = $row_active['total'];

$res_retard = $conn->query("SELECT COUNT(*) as total FROM emprunt WHERE (statut = 'retard') OR (statut = 'acceptée' AND date_retour_prevue < CURDATE() AND date_fin IS NULL)");
$row_retard = $res_retard->fetch_assoc();
$nb_retards = $row_retard['total'];

$res_pending = $conn->query("SELECT COUNT(*) as total FROM emprunt WHERE statut = 'en attente'");
$row_pending = $res_pending->fetch_assoc();
$nb_demandes = $row_pending['total'];

$res_rendu = $conn->query("SELECT COUNT(*) as total FROM emprunt WHERE statut = 'rendu'");
$row_rendu = $res_rendu->fetch_assoc();
$nb_rendu = $row_rendu['total'];

include "../includes/header.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location:../client/library.php");
    exit;
}

// --- 1. جلب بيانات المبيعات ---
$months = []; $revenues = [];
$sales_query = $conn->query("SELECT MONTHNAME(date_commande) as m, SUM(total) as s FROM commande WHERE statut IN ('payee', 'payée', 'Terminé') GROUP BY MONTH(date_commande) ORDER BY MONTH(date_commande)");
while($row = $sales_query->fetch_assoc()) { $months[] = $row['m']; $revenues[] = (float)$row['s']; }

// --- 2. جلب توزيع الأصناف ---
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
    /* 1. إصلاح التداخل مع الهيدر */
    body {
        margin-top: 0 !important;
        display: block;
        direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
    }

    /* 2. الحاوية الكبرى التي تبدأ تحت الناف بار */
    .adm-wrap { 
        display: flex; 
        min-height: calc(100vh - 66px);
        margin-top: 66px;
        background: #F9F7F2; 
    }

    /* 3. القسم الرئيسي للمحتوى - يتقلص ويتوسع بمرونة */
    .adm-main { 
        flex: 1; 
        min-width: 0;
        padding: 40px; 
        transition: all 0.3s ease;
    }

    /* تنسيقات الرسوم البيانية */
    .dash-title { font-family:'Playfair Display',serif; font-size:28px; font-weight:700; color:#2C1F0E; margin-bottom:5px; text-align:<?= $isRtl?'right':'left' ?>; }
    .dash-sub   { font-size:13px; color:#9A8C7E; margin-bottom:35px; text-align:<?= $isRtl?'right':'left' ?>; }

    .charts-grid { 
        display: grid; 
        grid-template-columns: 1.6fr 1fr; 
        gap: 25px; 
    }

    .chart-card {
        background: #FFFDF9;
        border: 1px solid #DDD5C8;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }

    /* ── label row ── */
    .chart-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        gap: 8px;
        flex-direction: <?= $isRtl?'row-reverse':'row' ?>;
    }
    .chart-lbl  { font-weight:700; color:#2C1F0E; font-size:14px; }
    .chart-hint { font-size:10px; color:#9A8C7E; font-style:italic; }

    .btn-reset-rev {
        display: none;
        background: transparent;
        border: 1.5px solid #C4A46B;
        color: #B8924A;
        padding: 3px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        transition: background .2s, color .2s;
        white-space: nowrap;
    }
    .btn-reset-rev:hover { background: #C4A46B; color: #2C1F0E; }
    #genreChart { cursor: pointer; }

    .chart-container { 
        position: relative; 
        height: 380px; 
        width: 100%; 
    }

    @media (max-width: 1200px) {
        .charts-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="adm-wrap">
    <div class="adm-main">
        <div class="dash-title"><?= $p['dash_title'] ?></div>
        <div class="dash-sub"><?= $p['dash_sub'] ?></div>

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
// إعدادات Chart.js
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#9A8C7E';

// ── Données PHP → JS ─────────────────────────────────────
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

// ── 1. Line Chart ─────────────────────────────────────────
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

// ── Mise à jour selon la catégorie cliquée ───────────────
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

// ── 2. Doughnut Chart ─────────────────────────────────────
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