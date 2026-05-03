<?php
include "../includes/header.php";

// 1. استعلام لأكثر الكتب طلباً (Top 5)
$top_books = $conn->query("
    SELECT d.titre, COUNT(e.id_emprunt) as total_demandes 
    FROM documents d 
    JOIN emprunt e ON d.id_doc = e.id_doc 
    GROUP BY d.id_doc 
    ORDER BY total_demandes DESC LIMIT 5
");

// 2. استعلام للمستخدمين الأكثر استعارة
$top_users = $conn->query("
    SELECT u.firstname, u.lastname, COUNT(e.id_emprunt) as nb_emprunts 
    FROM users u 
    JOIN emprunt e ON u.id = e.id_user 
    GROUP BY u.id 
    ORDER BY nb_emprunts DESC LIMIT 5
");

// 3. إجمالي المبيعات حسب الشهر (مثال بسيط)
$monthly_sales = $conn->query("
    SELECT MONTHNAME(date_commande) as month, SUM(total) as revenue 
    FROM commande 
    WHERE statut IN ('payee', 'Terminé')
    GROUP BY MONTH(date_commande)
");
?>

<div class="adm-main">
    <div class="dash-title">Rapports & Statistiques</div>
    <div class="dash-sub">Analyse détaillée de l'activité de la bibliothèque</div>

    <div class="tables-grid" style="grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 30px;">
        
        <div class="table-box">
            <div class="tb-head"><span class="tb-title">📚 Livres les plus populaires</span></div>
            <table>
                <thead><tr><th>Titre du Livre</th><th>Nb. d'emprunts</th></tr></thead>
                <tbody>
                    <?php while($b = $top_books->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['titre']) ?></td>
                        <td><strong style="color:#C4A46B;"><?= $b['total_demandes'] ?></strong> fois</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="table-box">
            <div class="tb-head"><span class="tb-title">🏆 Top Lecteurs</span></div>
            <table>
                <thead><tr><th>Nom du Lecteur</th><th>Livres Empruntés</th></tr></thead>
                <tbody>
                    <?php while($u = $top_users->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['firstname'].' '.$u['lastname']) ?></td>
                        <td><span class="badge" style="background:#dcfce7; color:#15803d;"><?= $u['nb_emprunts'] ?> livres</span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

    <div style="margin-top: 30px; text-align: right;">
        <a href="generate_pdf.php" class="qa-card" style="display: inline-block; padding: 10px 20px;">
            📄 Exporter le rapport complet (PDF)
        </a>
    </div>
</div>