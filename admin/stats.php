<?php
require_once "../includes/db.php";
// جلب الإحصائيات من قاعدة البيانات
// 1. عدد الكتب الإجمالي المعار حالياً (Active)
$res_active = $conn->query("SELECT COUNT(*) as total FROM emprunt WHERE statut = 'acceptée' AND date_fin IS NULL");
$row_active = $res_active->fetch_assoc();
$nb_en_cours = $row_active['total']; // هذا ينحي خطأ $nb_en_cours

// 2. عدد الكتب المتأخرة (Retard)
$res_retard = $conn->query("SELECT COUNT(*) as total FROM emprunt WHERE (statut = 'retard') OR (statut = 'acceptée' AND date_retour_prevue < CURDATE() AND date_fin IS NULL)");
$row_retard = $res_retard->fetch_assoc();
$nb_retards = $row_retard['total']; // هذا ينحي خطأ $nb_retards

// 3. عدد طلبات الإعارة الجديدة (En attente)
$res_pending = $conn->query("SELECT COUNT(*) as total FROM emprunt WHERE statut = 'en attente'");
$row_pending = $res_pending->fetch_assoc();
$nb_demandes = $row_pending['total']; 

// 4. إجمالي الكتب المرجعة (Rendu)
$res_rendu = $conn->query("SELECT COUNT(*) as total FROM emprunt WHERE statut = 'rendu'");
$row_rendu = $res_rendu->fetch_assoc();
$nb_rendu = $row_rendu['total'];

include "../includes/header.php";

// التحقق من صلاحيات الأدمن
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
?>


<title>Analyses & Statistiques — AuraLib</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* 1. إصلاح التداخل مع الهيدر */
    body {
        margin-top: 0 !important; /* نلغي الهامش الافتراضي للهيدر هنا */
        display: block; /* نضمن أن الجسم طبيعي */
    }

    /* 2. الحاوية الكبرى التي تبدأ تحت الناف بار */
    .adm-wrap { 
        display: flex; 
        min-height: calc(100vh - 66px); /* 66px هو ارتفاع الناف بار */
        margin-top: 66px; /* ندفع المحتوى تحت الناف بار */
        background: #F9F7F2; 
    }

    /* 3. القسم الرئيسي للمحتوى - يتقلص ويتوسع بمرونة */
    .adm-main { 
        flex: 1; 
        min-width: 0; /* ضروري جداً لمنع الـ Charts من الخروج عن الإطار */
        padding: 40px; 
        transition: all 0.3s ease;
    }

    /* تنسيقات الرسوم البيانية */
    .dash-title { font-family:'Playfair Display',serif; font-size:28px; font-weight:700; color:#2C1F0E; margin-bottom: 5px; }
    .dash-sub { font-size:13px; color:#9A8C7E; margin-bottom:35px; }

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
        <div class="dash-title">Analyses & Statistiques</div>
        <div class="dash-sub">Suivi des performances de AuraLib</div>

        <div class="charts-grid">
            <div class="chart-card">
                <div style="margin-bottom:20px; font-weight:700; color:#2C1F0E;">📈 Évolution des Revenus (DA)</div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div style="margin-bottom:20px; font-weight:700; color:#2C1F0E;">📊 Répartition par Catégorie</div>
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

// 1. Line Chart
const ctxRev = document.getElementById('revenueChart').getContext('2d');
const grad = ctxRev.createLinearGradient(0, 0, 0, 400);
grad.addColorStop(0, 'rgba(196, 164, 107, 0.2)');
grad.addColorStop(1, 'rgba(196, 164, 107, 0)');

new Chart(ctxRev, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Revenu Mensuel',
            data: <?php echo json_encode($revenues); ?>,
            borderColor: '#C4A46B',
            backgroundColor: grad,
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#fff',
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

// 2. Doughnut Chart
const ctxGen = document.getElementById('genreChart').getContext('2d');
new Chart(ctxGen, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($categories); ?>,
        datasets: [{
            data: <?php echo json_encode($counts); ?>,
            backgroundColor: ['#2C1F0E', '#C4A46B', '#9A8C7E', '#EDE5D4', '#D4C5B0'],
            borderWidth: 5,
            borderColor: '#FFFDF9'
        }]
    },
    options: {
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
        }
    }
});
</script>

<?php include "../includes/footer.php"; ?>