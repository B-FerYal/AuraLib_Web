<?php
include "../includes/header.php";
include '../includes/dark_init.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location:../client/library.php");
    exit;
}

// ── Stats ──────────────────────────────────────────────────
$total_docs  = $conn->query("SELECT COUNT(*) c FROM documents")->fetch_assoc()['c'] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) c FROM users WHERE role='client'")->fetch_assoc()['c'] ?? 0;
$total_loans = $conn->query("SELECT COUNT(*) c FROM emprunt WHERE statut='en_cours'")->fetch_assoc()['c'] ?? 0;
$rev_q       = $conn->query("SELECT SUM(total) s FROM commande WHERE statut IN ('payee','payée','Terminé')");
$revenue     = $rev_q ? (float)$rev_q->fetch_assoc()['s'] : 0;

$nb_messages = 0;
$r = $conn->query("SELECT COUNT(*) c FROM contact_messages WHERE lu=0");
if ($r) $nb_messages = (int)$r->fetch_assoc()['c'];

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

$alerts_result = $conn->query("
    SELECT titre, disponible_pour FROM documents WHERE exemplaires_disponibles <= 0
");
?>
<style>
/* ════════════════════════════════════════════════════════
   ADMIN DASHBOARD — AuraLib
   Fix : header overlap + full screen + icônes luxury SVG
════════════════════════════════════════════════════════ */

/* ── Layout principal ── */
.adm-wrap {
    display: flex;
    min-height: 100vh;
    background: #F5F0E8;
    /* nav height = 66px */
    padding-top: 66px;
}

.adm-main {
    flex: 1;
    /* sidebar width = 255px (variable --sidebar-width dans sidebar.php) */
    margin-left:0;
    padding: 36px 36px 60px;
    min-width: 0;
    transition: margin-left .28s cubic-bezier(.4,0,.2,1);
}

/* Quand sidebar collapsed */
.sidebar-is-collapsed .adm-main {
    margin-left: 66px;
}

/* ── En-tête de page ── */
.dash-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 12px;
}
.dash-title {
    font-family: 'Playfair Display', serif;
    font-size: 26px;
    font-weight: 700;
    color: #2C1F0E;
    line-height: 1.1;
}
.dash-sub {
    font-size: 12px;
    color: #9A8C7E;
    margin-top: 3px;
}
.dash-date {
    font-size: 11px;
    color: #9A8C7E;
    background: #FFFDF9;
    border: 1px solid #DDD5C8;
    border-radius: 7px;
    padding: 6px 12px;
    white-space: nowrap;
}
/* Card Stock Épuisé */
.qa-card.qa-danger {
    border-color: rgba(239,68,68,.3);
    background: #fff5f5;
    margin-bottom: 23px;
}
.qa-card.qa-danger .qa-icon-wrap {
    background: rgba(239,68,68,.12);
    border-color: rgba(239,68,68,.25);
}
.qa-card.qa-danger .qa-icon-wrap svg { stroke: #dc2626; color: #dc2626; }
.qa-card.qa-danger .qa-label { color: #dc2626; font-weight: 700; }
.qa-card.qa-danger:hover { border-color: #EF4444; box-shadow: 0 6px 20px rgba(239,68,68,.15); }
 
html.dark .qa-card.qa-danger { background: #220E0E; border-color: rgba(239,68,68,.25); }

/* ── Stats grid ── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}
.stat-card {
    background: #FFFDF9;
    border: 1px solid #DDD5C8;
    border-radius: 12px;
    padding: 20px 18px;
    border-top: 3px solid #C4A46B;
    position: relative;
    overflow: hidden;
}
.stat-card.g { border-top-color: #4ade80; }
.stat-card.b { border-top-color: #60a5fa; }
.stat-card.r { border-top-color: #f87171; }
.stat-card::after {
    content: '';
    position: absolute;
    right: -10px; bottom: -10px;
    width: 60px; height: 60px;
    border-radius: 50%;
    background: rgba(196,164,107,.06);
}
.stat-lbl {
    font-size: 10px;
    color: #9A8C7E;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.stat-val {
    font-family: 'Playfair Display', serif;
    font-size: 30px;
    color: #2C1F0E;
    line-height: 1;
    font-weight: 600;
}
.stat-sub {
    font-size: 10px;
    color: #9A8C7E;
    margin-top: 4px;
}

/* ── Promo banner ── */
.promo-banner {
    background: #2C1F0E;
    color: #EDE5D4;
    padding: 18px 24px;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    border: 1px solid rgba(196,164,107,.25);
    gap: 16px;
    flex-wrap: wrap;
}
.promo-banner h4 {
    font-family: 'Playfair Display', serif;
    font-size: 17px;
    margin: 0 0 3px;
    color: #C4A46B;
}
.promo-banner p {
    font-size: 12px;
    opacity: .7;
    margin: 0;
}
.promo-btn {
    background: #C4A46B;
    color: #2C1F0E;
    padding: 9px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    transition: background .15s;
}
.promo-btn:hover { background: #D4B47B; }

/* ══════════════════════════════════════════════════════
   QUICK ACTIONS — icônes SVG luxury AuraLib
══════════════════════════════════════════════════════ */
.qa-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}
.qa-card {
    background: #FFFDF9;
    border: 1px solid #DDD5C8;
    border-radius: 10px;
    padding: 18px 10px 14px;
    text-align: center;
    text-decoration: none;
    color: #2C1F0E;
    transition: transform .15s, border-color .15s, box-shadow .15s;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.qa-card:hover {
    transform: translateY(-3px);
    border-color: #C4A46B;
    box-shadow: 0 6px 20px rgba(196,164,107,.12);
}
.qa-icon-wrap {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: rgba(196,164,107,.1);
    border: 1px solid rgba(196,164,107,.25);
    display: flex; align-items: center; justify-content: center;
    transition: background .15s;
}
.qa-card:hover .qa-icon-wrap {
    background: rgba(196,164,107,.2);
}
.qa-icon-wrap svg {
    width: 20px; height: 20px;
    color: #C4A46B;
    stroke: #C4A46B;
}
.qa-label {
    font-size: 11px;
    font-weight: 600;
    color: #2C1F0E;
    letter-spacing: .2px;
}
.qa-badge {
    position: absolute;
    top: -5px; right: -5px;
    background: #EF4444;
    color: #fff;
    font-size: 9px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 10px;
    border: 1.5px solid #FFFDF9;
}
/* Card spéciale Analyses */
.qa-card.qa-featured {
    border-color: rgba(74,222,128,.4);
    background: #f0fdf4;
}
.qa-card.qa-featured .qa-icon-wrap {
    background: rgba(74,222,128,.15);
    border-color: rgba(74,222,128,.3);
}
.qa-card.qa-featured .qa-icon-wrap svg { stroke: #15803d; color: #15803d; }
.qa-card.qa-featured .qa-label { color: #15803d; }

/* ── Tables ── */
.tables-grid {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 18px;
}
.table-box {
    background: #FFFDF9;
    border: 1px solid #DDD5C8;
    border-radius: 12px;
    overflow: hidden;
}
.tb-head {
    padding: 13px 18px;
    border-bottom: 1px solid #EDE5D4;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.tb-title {
    font-family: 'Playfair Display', serif;
    font-size: 15px;
    color: #2C1F0E;
    font-weight: 600;
}
.tb-link {
    font-size: 11px;
    color: #C4A46B;
    text-decoration: none;
    font-weight: 600;
}
.tb-link:hover { text-decoration: underline; }

table { width: 100%; border-collapse: collapse; }
th {
    padding: 10px 14px;
    text-align: left;
    font-size: 10px;
    color: #9A8C7E;
    text-transform: uppercase;
    letter-spacing: .5px;
    background: #FAF8F4;
    border-bottom: 1px solid #EDE5D4;
}
td {
    padding: 12px 14px;
    font-size: 12px;
    color: #2C1F0E;
    border-bottom: 1px solid #F5F0E8;
}
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: #FDF8F0; }

.badge { padding: 3px 9px; border-radius: 20px; font-size: 10px; font-weight: 600; }
.status-payee   { background: #dcfce7; color: #15803d; }
.status-pending { background: #fef9c3; color: #854d0e; }
.status-gray    { background: #f3f4f6; color: #6b7280; }

/* ── Dark mode ── */
html.dark .adm-wrap      { background: #1A1610; }
html.dark .adm-main      { background: #1A1610; }
html.dark .dash-title    { color: #F0E8D8; }
html.dark .stat-card     { background: #2C2418; border-color: #3E3228; }
html.dark .stat-val      { color: #F0E8D8; }
html.dark .table-box     { background: #2C2418; border-color: #3E3228; }
html.dark .tb-head       { border-bottom-color: #3E3228; background: #241C12; }
html.dark .tb-title      { color: #F0E8D8; }
html.dark th             { background: #241C12; color: #A89880; border-bottom-color: #3E3228; }
html.dark td             { color: #F0E8D8; border-bottom-color: #342C20; }
html.dark tbody tr:hover td { background: #32281C; }
html.dark .qa-card       { background: #2C2418; border-color: #3E3228; color: #F0E8D8; }
html.dark .qa-card:hover { border-color: #C4A46B; }
html.dark .qa-label      { color: #F0E8D8; }
html.dark .promo-banner  { background: #140E08; }
html.dark .dash-date     { background: #2C2418; border-color: #3E3228; color: #A89880; }
html.dark .alert-stock   { background: #220E0E; border-color: #401818; }
</style>

<div class="adm-wrap">

    <div class="adm-main">

        <!-- ── En-tête ── -->
        <div class="dash-header">
            <div>
                <div class="dash-title">Tableau de Bord</div>
                <div class="dash-sub">Bienvenue dans l'espace de gestion AuraLib</div>
            </div>
            <span class="dash-date">
                <?= ucfirst(strftime('%A %d %B %Y')) ?>
            </span>
        </div>
<a href="stock_epuise.php" class="qa-card qa-danger">
    <?php
    $nb_epuises = $conn->query("SELECT COUNT(*) c FROM documents WHERE exemplaires_disponibles <= 0")->fetch_assoc()['c'] ?? 0;
    if ($nb_epuises > 0):
    ?>
        <span class="qa-badge"><?= $nb_epuises ?></span>
    <?php endif; ?>
    <div class="qa-icon-wrap">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
    </div>
    <span class="qa-label">Épuisé</span>
</a>
        
        <!-- ── Stats ── -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-lbl">Total Documents</div>
                <div class="stat-val"><?= $total_docs ?></div>
                <div class="stat-sub">dans le catalogue</div>
            </div>
            <div class="stat-card g">
                <div class="stat-lbl">Lecteurs Inscrits</div>
                <div class="stat-val"><?= $total_users ?></div>
                <div class="stat-sub">comptes actifs</div>
            </div>
            <div class="stat-card b">
                <div class="stat-lbl">Emprunts en cours</div>
                <div class="stat-val"><?= $total_loans ?></div>
                <div class="stat-sub">en circulation</div>
            </div>
            <div class="stat-card r">
                <div class="stat-lbl">Recettes (Ventes)</div>
                <div class="stat-val"><?= number_format($revenue, 0, ',', ' ') ?></div>
                <div class="stat-sub">DA encaissés</div>
            </div>
        </div>

        <!-- ── Promo banner ── -->
        <div class="promo-banner">
            <div>
                <h4>Analyses &amp; Rapports 2026</h4>
                <p>Visualisez l'évolution de vos ventes et les préférences de vos lecteurs.</p>
            </div>
            <a href="stats.php" class="promo-btn">Voir les Graphiques →</a>
        </div>

        <!-- ══════════════════════════════════════
             QUICK ACTIONS — icônes SVG luxury
        ══════════════════════════════════════ -->
        <div class="qa-grid">

            <!-- Livre -->
            <a href="gerer_documents.php" class="qa-card">
                <div class="qa-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                        <line x1="12" y1="6" x2="16" y2="6"/>
                        <line x1="12" y1="10" x2="16" y2="10"/>
                        <line x1="12" y1="14" x2="14" y2="14"/>
                    </svg>
                </div>
                <span class="qa-label">documents</span>
            </a>

            <!-- Analyses -->
            <a href="stats.php" class="qa-card qa-featured">
                <div class="qa-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"/>
                        <line x1="12" y1="20" x2="12" y2="4"/>
                        <line x1="6"  y1="20" x2="6"  y2="14"/>
                        <line x1="2"  y1="20" x2="22" y2="20"/>
                    </svg>
                </div>
                <span class="qa-label">Analyses</span>
            </a>

            <!-- Ventes -->
            <a href="all_orders.php" class="qa-card">
                <div class="qa-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                </div>
                <span class="qa-label">Ventes</span>
            </a>

            <!-- Utilisateurs -->
            <a href="users.php" class="qa-card">
                <div class="qa-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <span class="qa-label">Utilisateurs</span>
            </a>

            <!-- Prêts -->
            <a href="gerer_emprunts.php" class="qa-card">
                <div class="qa-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                        <polyline points="12 8 12 12 14 14"/>
                        <circle cx="12" cy="12" r="3" stroke-dasharray="2"/>
                    </svg>
                </div>
                <span class="qa-label">Prêts</span>
            </a>

            <!-- Messages -->
            <a href="messages.php" class="qa-card">
                <?php if ($nb_messages > 0): ?>
                    <span class="qa-badge"><?= $nb_messages ?></span>
                <?php endif; ?>
                <div class="qa-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        <line x1="9" y1="10" x2="15" y2="10"/>
                        <line x1="9" y1="14" x2="13" y2="14"/>
                    </svg>
                </div>
                <span class="qa-label">Messages</span>
            </a>

        </div>

        <!-- ── Tables ── -->
        <div class="tables-grid">

            <div class="table-box">
                <div class="tb-head">
                    <span class="tb-title">Dernières Ventes</span>
                    <a href="all_orders.php" class="tb-link">Voir tout →</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Date</th>
                            <th>Montant</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recent_orders->fetch_assoc()):
                            $s = strtolower($row['statut'] ?? '');
                            $bc = ($s === 'payee' || $s === 'payée') ? 'status-payee' : 'status-pending';
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['firstname'].' '.$row['lastname']) ?></strong></td>
                            <td style="color:#9A8C7E"><?= date('d/m/Y', strtotime($row['date_commande'])) ?></td>
                            <td><strong><?= number_format($row['total'], 0) ?> DA</strong></td>
                            <td><span class="badge <?= $bc ?>"><?= htmlspecialchars($row['statut']) ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-box">
                <div class="tb-head">
                    <span class="tb-title">Flux d'Emprunts</span>
                    <a href="gerer_emprunts.php" class="tb-link">Voir tout →</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Livre</th>
                            <th>Lecteur</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($emp = $recent_emprunts->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($emp['titre']) ?></td>
                            <td style="color:#9A8C7E"><?= htmlspecialchars($emp['firstname']) ?></td>
                            <td><span class="badge status-gray"><?= htmlspecialchars($emp['statut']) ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </div><!-- .adm-main -->
</div><!-- .adm-wrap -->

<?php include "../includes/footer.php"; ?>