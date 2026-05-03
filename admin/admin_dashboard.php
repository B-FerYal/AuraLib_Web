<?php
// تأكد من أن session_start موجودة في header.php أو أضفها هنا
include "../includes/header.php";

// التحقق من صلاحيات الأدمن
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location:../client/library.php");
    exit;
}

// --- 1. جلب الإحصائيات (Stats) ---
$total_docs  = $conn->query("SELECT COUNT(*) c FROM documents")->fetch_assoc()['c'] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) c FROM users WHERE role='client'")->fetch_assoc()['c'] ?? 0;
$total_loans = $conn->query("SELECT COUNT(*) c FROM emprunt WHERE statut='en_cours'")->fetch_assoc()['c'] ?? 0;

// حساب الدخل من الطلبات المدفوعة فقط
$rev_q   = $conn->query("SELECT SUM(total) s FROM commande WHERE statut IN ('payee','payée','Terminé')");
$revenue = $rev_q ? (float)$rev_q->fetch_assoc()['s'] : 0;

// --- 2. جلب الرسائل غير المقروءة ---
$nb_messages = 0;
$r = $conn->query("SELECT COUNT(*) c FROM contact_messages WHERE lu=0");
if ($r) $nb_messages = (int)$r->fetch_assoc()['c'];

// --- 3. جلب البيانات للجداول ---
$recent_orders = $conn->query("
    SELECT c.*, u.firstname, u.lastname
    FROM commande c JOIN users u ON c.id_user = u.id
    ORDER BY c.id_commande DESC LIMIT 5
");

$recent_emprunts = $conn->query("
    SELECT e.*, u.firstname, u.lastname, d.titre
    FROM emprunt e
    JOIN users u ON e.id_user = u.id
    JOIN documents d ON e.id_doc = d.id_doc
    ORDER BY e.id_emprunt DESC LIMIT 5
");

// --- 4. نظام تنبيهات المخزون (الكتب النافدة) ---
$low_stock_query = "SELECT titre, disponible_pour FROM documents WHERE exemplaires_disponibles <= 0";
$alerts_result = $conn->query($low_stock_query);
?>

<title>Dashboard — AuraLib</title>

<style>
/* التنسيقات الأساسية */
.adm-wrap { display:flex; min-height:100vh; background: #F9F7F2; }
.adm-main { flex:1; margin-left:260px; padding:36px 32px 60px; }
.dash-title { font-family:'Playfair Display',serif; font-size:28px; font-weight:700; color:#2C1F0E; margin-bottom:4px; }
.dash-sub   { font-size:13px; color:#9A8C7E; margin-bottom:32px; }

/* تنسيق بطاقة التنبيهات */
.alert-container {
    background: #FFF5F5; 
    border: 1px solid #FECACA; 
    border-left: 5px solid #EF4444; 
    border-radius: 12px; 
    padding: 18px; 
    margin-bottom: 28px; 
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.alert-tag {
    background: #FEE2E2; 
    color: #B91C1C; 
    padding: 4px 12px; 
    border-radius: 6px; 
    font-size: 11px; 
    font-weight: 700; 
    border: 1px solid #FCA5A5;
    display: inline-block;
    margin: 4px;
}

/* شبكة الإحصائيات */
.stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:28px; }
.stat-card  { background:#FFFDF9; border:1px solid #DDD5C8; border-radius:12px; padding:20px; border-top:3px solid #C4A46B; }
.stat-card.g{ border-top-color:#4ade80; }
.stat-card.b{ border-top-color:#60a5fa; }
.stat-card.r{ border-top-color:#f87171; }
.stat-lbl { font-size:10px; color:#9A8C7E; letter-spacing:1px; text-transform:uppercase; margin-bottom:8px; }
.stat-val { font-family:'Playfair Display',serif; font-size:28px; color:#2C1F0E; line-height:1; }

/* بطاقة الترويج للإحصائيات */
.promo-banner {
    background: #2C1F0E; 
    color: #EDE5D4; 
    padding: 20px 24px; 
    border-radius: 12px; 
    display: flex; 
    justify-content: space-between; 
    align-items: center;
    margin-bottom: 28px;
}
.promo-btn {
    background: #C4A46B; 
    color: #fff; 
    padding: 10px 20px; 
    border-radius: 8px; 
    text-decoration: none; 
    font-size: 13px; 
    font-weight: 600;
    transition: 0.3s;
}
.promo-btn:hover { background: #A38558; }

/* الجداول والروابط السريعة */
.qa-grid  { display:grid; grid-template-columns:repeat(6,1fr); gap:12px; margin-bottom:28px; }
.qa-card  { background:#FFFDF9; border:1px solid #DDD5C8; border-radius:10px; padding:16px; text-align:center; text-decoration:none; color:#2C1F0E; transition: 0.3s; position: relative; }
.qa-card:hover { transform: translateY(-3px); border-color:#C4A46B; background:rgba(196,164,107,.04); }
.qa-badge { position:absolute; top:-5px; right:-5px; background:#e74c3c; color:#fff; font-size:10px; padding:2px 6px; border-radius:10px; }

.tables-grid { display:grid; grid-template-columns:1.6fr 1fr; gap:20px; }
.table-box   { background:#FFFDF9; border:1px solid #DDD5C8; border-radius:12px; overflow:hidden; }
.tb-head     { padding:14px 18px; border-bottom:1px solid #DDD5C8; background: #FDFBFA; }
.tb-title    { font-family:'Playfair Display',serif; font-size:16px; color:#2C1F0E; font-weight: 600; }

table { width:100%; border-collapse:collapse; }
th { padding:12px 14px; text-align:left; font-size:10px; color:#9A8C7E; text-transform:uppercase; background:#F9F6F0; }
td { padding:14px; font-size:13px; color:#2C1F0E; border-bottom:1px solid #F5F0E8; }

.badge { padding:4px 10px; border-radius:20px; font-size:10px; font-weight:600; }
.status-payee { background:#dcfce7; color:#15803d; }
.status-pending { background:#fef9c3; color:#854d0e; }
</style>

<div class="adm-wrap">
   

    <div class="adm-main">
        <div class="dash-title">Tableau de Bord</div>
        <div class="dash-sub">Bienvenue dans l'espace de gestion AuraLib</div>

        <?php if ($alerts_result && $alerts_result->num_rows > 0): ?>
            <div class="alert-container">
                <h4 style="color:#991B1B; margin:0 0 12px 0; font-size:15px; display:flex; align-items:center;">
                    <span style="margin-right:10px;">⚠️</span> Attention : Stock Critique
                </h4>
                <div style="margin-bottom: 10px;">
                    <?php while($alert = $alerts_result->fetch_assoc()): ?>
                        <span class="alert-tag">
                            <?= htmlspecialchars($alert['titre']) ?> (<?= $alert['disponible_pour'] ?>)
                        </span>
                    <?php endwhile; ?>
                </div>
                <small style="color: #B91C1C; font-style: italic;">* Ces documents ne sont plus disponibles pour de nouvelles transactions.</small>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-lbl">Total Documents</div>
                <div class="stat-val"><?= $total_docs ?></div>
            </div>
            <div class="stat-card g">
                <div class="stat-lbl">Lecteurs Inscrits</div>
                <div class="stat-val"><?= $total_users ?></div>
            </div>
            <div class="stat-card b">
                <div class="stat-lbl">Emprunts en cours</div>
                <div class="stat-val"><?= $total_loans ?></div>
            </div>
            <div class="stat-card r">
                <div class="stat-lbl">Recettes (Ventes)</div>
                <div class="stat-val"><?= number_format($revenue, 0, ',', ' ') ?> DA</div>
            </div>
        </div>

        <div class="promo-banner">
            <div>
                <h4 style="margin: 0; font-family: 'Playfair Display', serif; font-size: 18px;">Analyses & Rapports 2026</h4>
                <p style="margin: 5px 0 0 0; font-size: 12px; opacity: 0.8;">Visualisez l'évolution de vos ventes et les préférences de vos lecteurs.</p>
            </div>
            <a href="stats.php" class="promo-btn">Voir les Graphiques →</a>
        </div>

        <div class="qa-grid">
            <a href="gerer_documents.php" class="qa-card"><div style="font-size:24px;">➕</div><div class="qa-label">Livre</div></a>
            
            <a href="stats.php" class="qa-card" style="border-color: #4ade80;">
                <div style="font-size:24px;">📊</div>
                <div class="qa-label" style="color: #15803d; font-weight: bold;">Analyses</div>
            </a>

            <a href="all_orders.php" class="qa-card"><div style="font-size:24px;">💰</div><div class="qa-label">Ventes</div></a>
            <a href="users.php" class="qa-card"><div style="font-size:24px;">👥</div><div class="qa-label">Users</div></a>
            <a href="gerer_emprunts.php" class="qa-card"><div style="font-size:24px;">📑</div><div class="qa-label">Prêts</div></a>
            
            <a href="messages.php" class="qa-card">
                <div style="font-size:24px;">📩</div><div class="qa-label">Messages</div>
                <?php if ($nb_messages > 0): ?><span class="qa-badge"><?= $nb_messages ?></span><?php endif; ?>
            </a>
        </div>

        <div class="tables-grid">
            <div class="table-box">
                <div class="tb-head"><span class="tb-title">Dernières Ventes</span></div>
                <table>
                    <thead><tr><th>Client</th><th>Date</th><th>Montant</th><th>Statut</th></tr></thead>
                    <tbody>
                        <?php while ($row = $recent_orders->fetch_assoc()): 
                            $status_class = ($row['statut'] == 'payée' || $row['statut'] == 'payee') ? 'status-payee' : 'status-pending';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['firstname'].' '.$row['lastname']) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['date_commande'])) ?></td>
                            <td><strong><?= number_format($row['total'], 0) ?> DA</strong></td>
                            <td><span class="badge <?= $status_class ?>"><?= $row['statut'] ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-box">
                <div class="tb-head"><span class="tb-title">Flux d'Emprunts</span></div>
                <table>
                    <thead><tr><th>Livre</th><th>Lecteur</th><th>Statut</th></tr></thead>
                    <tbody>
                        <?php while ($emp = $recent_emprunts->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($emp['titre']) ?></td>
                            <td><?= htmlspecialchars($emp['firstname']) ?></td>
                            <td><span class="badge" style="background:#E5E7EB; color:#374151;"><?= $emp['statut'] ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>