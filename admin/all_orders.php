<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// 1. الاتصال بقاعدة البيانات (memoir_db)
$host = "localhost"; $user = "root"; $pass = ""; $dbname = "memoir_db"; 
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

require_once '../includes/head.php'; 

// حماية الصفحة للأدمن فقط
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php"); exit;
}

// --- 2. الإحصائيات العلوية ---
// المداخيل الإجمالية
$rev_res = $conn->query("SELECT SUM(total) as tr FROM commande WHERE statut = 'payée'")->fetch_assoc();
$total_revenue = $rev_res['tr'] ?? 0;

// الربح الصافي (سعر البيع - سعر الشراء)
$profit_query = "
    SELECT SUM((ci.prix - IFNULL(d.prix_achat, 0)) * ci.quantite) as net_profit
    FROM commande_item ci
    JOIN documents d ON ci.id_doc = d.id_doc
    JOIN commande c ON ci.id_commande = c.id_commande
    WHERE c.statut = 'payée'";
$profit_res = $conn->query($profit_query)->fetch_assoc();
$total_profit = $profit_res['net_profit'] ?? 0;

// إجمالي القطع المباعة
$sales_res = $conn->query("SELECT SUM(quantite) as tb FROM commande_item ci JOIN commande c ON ci.id_commande = c.id_commande WHERE c.statut = 'payée'")->fetch_assoc();
$total_sold = $sales_res['tb'] ?? 0;

// --- 3. الاستعلام الرئيسي (احترافي لجلب تفاصيل المخزون) ---
$query = "
    SELECT c.id_commande, c.total, c.date_commande, c.statut, 
           u.firstname, u.lastname,
           GROUP_CONCAT(CONCAT('<b>', d.titre, '</b> (x', ci.quantite, ') <br><small>Reste: ', d.exemplaires_disponibles, ' en stock</small>') SEPARATOR '<hr style=\"margin:5px 0; border:0; border-top:1px solid #eee;\">') as order_details
    FROM commande c 
    JOIN users u ON c.id_user = u.id 
    JOIN commande_item ci ON c.id_commande = ci.id_commande
    JOIN documents d ON ci.id_doc = d.id_doc
    GROUP BY c.id_commande
    ORDER BY c.date_commande DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <?php include '../includes/dark_init.php'; ?>
    <title>AuraLib | Gestion des Commandes</title>
    <link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<script>
(function(){
    if(localStorage.getItem('auralib_theme')==='dark')
        document.documentElement.classList.add('dark');
})();
</script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --taupe: #2C1F0E; --gold: #D4A942; --cream: #F9F5EE; --white: #ffffff; --sidebar-w: 260px; --success: #27ae60; }
<<<<<<< HEAD
       body {
    display: block; /* ❌ نحيو flex كامل */
}
=======
        body { font-family: 'Inter', sans-serif; background-color: var(--cream); margin: 0; display: flex; }
        .main-content { flex: 1; margin-left: 0; padding: 40px; min-height:100vh; }
>>>>>>> 5ff8029 (Mise à jour admin_dashboard pour ma collègue)

.main-content {
    flex: 1;
    margin-left: var(--sidebar-w); /* إذا عندك sidebar */
    
    max-width: 1200px;   /* ✔️ يحدد العرض */
    margin-right: auto;
    margin-left: auto;   /* ✔️ يخليه في الوسط */

    padding: 40px 20px;  /* ✔️ espace من الجهتين */
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}       .stat-card { background: var(--white);width: 100%; padding: 25px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.02); border-left: 5px solid var(--gold); }
        .stat-card.profit { border-left-color: var(--success); }
        .stat-label { font-size: 12px; text-transform: uppercase; color: #999; letter-spacing: 1px; font-weight: 600; }
        .stat-value { font-family: 'Playfair Display', serif; font-size: 26px; color: var(--taupe); margin-top: 10px; }

        /* حاوية الجدول */
        .table-container { background: var(--white); border-radius: 30px; padding: 40px; box-shadow: 0 20px 60px rgba(44,31,14,0.05); }
        .table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        h2 { font-family: 'Playfair Display', serif; font-size: 32px; color: var(--taupe); }
        
        .btn-back { border: 2px solid var(--taupe); padding: 8px 20px; border-radius: 12px; text-decoration: none; color: var(--taupe); font-weight: 600; transition: 0.3s; font-size: 14px; }
        .btn-back:hover { background: var(--taupe); color: white; }

        /* الجدول */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; font-size: 12px; color: #bbb; text-transform: uppercase; border-bottom: 2px solid #f9f9f9; }
        td { padding: 20px 15px; border-bottom: 1px solid #fcfcfc; vertical-align: middle; }

        .order-id { font-weight: 700; color: var(--gold); font-size: 16px; }
        .client-info { display: flex; flex-direction: column; }
        .client-name { font-weight: 700; color: var(--taupe); text-transform: capitalize; margin-bottom: 5px; }
        .details-box { font-size: 13px; color: #666; line-height: 1.5; }
        .details-box small { color: #e67e22; font-weight: 500; } /* تلوين المخزون المتبقي */

        .prix-tag { font-weight: 700; color: var(--success); font-size: 18px; }
        .status-pill { background: #ebf9f1; color: var(--success); padding: 8px 15px; border-radius: 10px; font-size: 12px; font-weight: 600; }
        
        .action-cell { font-size: 13px; }
        .confirmed-text { color: var(--success); font-weight: 700; display: flex; align-items: center; gap: 5px; margin-bottom: 5px; }
        .btn-facture { color: var(--taupe); text-decoration: underline; opacity: 0.7; transition: 0.3s; }
        .btn-facture:hover { opacity: 1; color: var(--gold); }
    </style>
</head>
<body>


    <div class="main-content">
        <!-- قسم الإحصائيات -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Revenus Totaux</div>
                <div class="stat-value"><?= number_format($total_revenue, 2) ?> DA</div>
            </div>
            <div class="stat-card profit">
                <div class="stat-label">Bénéfice Net (الربح الصافي)</div>
                <div class="stat-value"><?= number_format($total_profit, 2) ?> DA</div>
            </div>
            <div class="stat-card" style="border-left-color: #3498db;">
                <div class="stat-label">Livres Vendus</div>
                <div class="stat-value"><?= $total_sold ?> Exemplaires</div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h2>Liste des Commandes</h2>
                <a href="admin_dashboard.php" class="btn-back">⬅ Retour</a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Lecteur & Détails (Stock Restant)</th>
                        <th>Total</th>
                        <th>Date & Heure</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="order-id">#<?= $row['id_commande'] ?></td>
                        <td>
                            <div class="client-info">
                                <span class="client-name"><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></span>
                                <div class="details-box">
                                    <?= $row['order_details'] ?>
                                </div>
                            </div>
                        </td>
                        <td class="prix-tag"><?= number_format($row['total'], 2) ?> DA</td>
                        <td style="color: #888; font-size: 13px;">
                            <?= date('d M Y', strtotime($row['date_commande'])) ?><br>
                            <?= date('H:i', strtotime($row['date_commande'])) ?>
                        </td>
                        <td><span class="status-pill">Payée</span></td>
                        <td class="action-cell">
                            <div class="confirmed-text">✓ Confirmé Auto</div>
                            <a href="print_invoice.php?id=<?= $row['id_commande'] ?>" target="_blank" class="btn-facture">Facture PDF</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>