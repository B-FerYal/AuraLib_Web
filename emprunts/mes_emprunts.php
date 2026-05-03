<?php
include_once __DIR__ . "/../includes/header.php"; 

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
?>

<style>
    :root {
        --premium-gold: #C4A46B;
        --soft-bg: #F9F7F2;
        --text-dark: #2C1F0E;
        --accent-red: #D32F2F;
    }

    .main-content { background: var(--soft-bg); min-height: 100vh; padding: 60px 0; font-family: 'Inter', sans-serif; }
    .emprunts-container { max-width: 1000px; margin: 0 auto; padding: 0 20px; }

    .page-header h1 { font-family: 'Playfair Display', serif; font-size: 42px; margin-bottom: 10px; color: var(--text-dark); }
    .page-header p { color: #9A8C7E; font-size: 16px; margin-bottom: 50px; }

    .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 50px; }
    .stat-box {
        background: white; padding: 30px; border-radius: 20px; text-align: center;
        border: 1px solid rgba(196,164,107,0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        transition: 0.3s;
    }
    .stat-box h3 { font-size: 32px; margin: 0; color: var(--text-dark); font-weight: 800; }
    .stat-box p { color: #9A8C7E; font-size: 12px; margin: 8px 0 0; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600; }
    .stat-box.highlight { border-bottom: 4px solid var(--premium-gold); }

    .emprunt-card {
        display: flex; background: white; border-radius: 24px; margin-bottom: 30px;
        overflow: hidden; border: 1px solid rgba(0,0,0,0.05); position: relative;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }
    .emprunt-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(196,164,107,0.15); }

    .book-side { width: 180px; position: relative; overflow: hidden; background: var(--text-dark); display: flex; align-items: center; justify-content: center; }
    .book-side img { width: 110px; height: 160px; object-fit: cover; border-radius: 6px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); z-index: 2; }

    .emprunt-details { padding: 35px; flex-grow: 1; display: flex; flex-direction: column; justify-content: center; }
    .emprunt-details h3 { font-family: 'Playfair Display', serif; font-size: 24px; margin: 0 0 10px; color: var(--text-dark); }
    .author { color: var(--premium-gold); font-weight: 600; font-size: 15px; margin-bottom: 25px; display: flex; align-items: center; gap: 8px; }

    .status-badge { 
        position: absolute; top: 35px; right: 35px; padding: 8px 20px; border-radius: 50px; 
        font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; 
    }
    .status-en-cours { background: #F0F7FF; color: #007AFF; border: 1px solid #D0E7FF; }
    .status-rendu    { background: #F2FAF3; color: #34C759; border: 1px solid #D7F0DB; }
    .status-retard   { background: #FFF2F2; color: var(--accent-red); border: 1px solid #FFD6D6; animation: softPulse 2s infinite; }
    .status-attente  { background: #FFF9E6; color: #D4A942; border: 1px solid #FFECB3; }
    .status-refuse   { background: #FEE2E2; color: #880E4F; border: 1px solid #F48FB1; }

    @keyframes softPulse { 
        0%   { box-shadow: 0 0 0 0 rgba(211,47,47,0.2); } 
        70%  { box-shadow: 0 0 0 10px rgba(211,47,47,0); } 
        100% { box-shadow: 0 0 0 0 rgba(211,47,47,0); } 
    }

    .info-dates { display: flex; gap: 50px; margin-top: 10px; }
    .date-item { display: flex; flex-direction: column; }
    .date-label { font-size: 11px; color: #9A8C7E; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; font-weight: 700; }
    .date-value { font-size: 16px; font-weight: 700; color: var(--text-dark); }

    .btn-prolong {
        margin-top: 25px; align-self: flex-start;
        background: white; border: 2px solid var(--premium-gold); color: var(--premium-gold);
        padding: 10px 22px; border-radius: 12px; font-size: 13px; font-weight: 700;
        cursor: pointer; display: flex; align-items: center; gap: 10px; transition: all 0.3s ease;
        text-decoration: none;
    }
    .btn-prolong:hover { background: var(--premium-gold); color: white; transform: scale(1.05); }

    /* كارت مرفوضة تبان أفتح شوية */
    .emprunt-card.refused { opacity: 0.7; }
</style>

<div class="main-content">
    <div class="emprunts-container">
        
        <div class="page-header" style="text-align: center;">
            <h1>Mes <em style="color:var(--premium-gold)">Emprunts</em></h1>
            <p>Gérez vos lectures et suivez vos délais en toute simplicité.</p>
        </div>

        <div class="stats-row">
            <div class="stat-box">
                <h3><?= $total ?></h3>
                <p>Total</p>
            </div>
            <div class="stat-box highlight">
                <h3><?= $active ?></h3>
                <p>Lectures Actives</p>
            </div>
            <div class="stat-box" style="<?= $late > 0 ? 'border-bottom:4px solid var(--accent-red)' : '' ?>">
                <h3 style="<?= $late > 0 ? 'color:var(--accent-red)' : '' ?>"><?= $late ?></h3>
                <p>En Retard</p>
            </div>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($e = $result->fetch_assoc()): 
                $date_prevue = $e['date_retour_prevue'];
                $statut_brut = strtolower(trim($e['statut']));

                // ════ تحديد الحالة البصرية ════
                if ($statut_brut === 'rendu' || $statut_brut === 'retourné') {
                    $status_class = "status-rendu";
                    $status_text  = "Rendu";

                } elseif ($statut_brut === 'refusée' || $statut_brut === 'refusee') {
                    $status_class = "status-refuse";
                    $status_text  = "Refusée";

                } elseif ($statut_brut === 'en attente') {
                    $status_class = "status-attente";
                    $status_text  = "En attente";

                } elseif ($statut_brut === 'retard' || ($date_prevue && $today > $date_prevue)) {
                    $status_class = "status-retard";
                    $status_text  = "En retard";

                } else {
                    // acceptée + dans les délais
                    $status_class = "status-en-cours";
                    $status_text  = "En cours";
                }

                $imgPath     = "../uploads/" . $e['id_doc'] . ".jpg";
                $is_refused  = ($statut_brut === 'refusée' || $statut_brut === 'refusee');
            ?>

            <div class="emprunt-card <?= $is_refused ? 'refused' : '' ?>">
                <div class="book-side">
                    <img src="<?= $imgPath ?>" onerror="this.src='../uploads/default.jpg';">
                </div>

                <div class="emprunt-details">
                    <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>

                    <h3><?= htmlspecialchars($e['titre']) ?></h3>
                    <div class="author">
                        <i class="fas fa-feather-alt"></i> 
                        <?= htmlspecialchars($e['auteur']) ?>
                    </div>

                    <div class="info-dates">
                        <div class="date-item">
                            <span class="date-label">Emprunté le</span>
                            <span class="date-value">
                                <?= date('d M Y', strtotime($e['date_debut'])) ?>
                            </span>
                        </div>
                        <div class="date-item">
                            <span class="date-label">Date limite</span>
                            <span class="date-value" style="<?= ($status_text === 'En retard') ? 'color:var(--accent-red)' : '' ?>">
                                <?php if ($is_refused): ?>
                                    <span style="color:#880E4F;">—</span>
                                <?php elseif ($date_prevue && $date_prevue !== '0000-00-00'): ?>
                                    <?= date('d M Y', strtotime($date_prevue)) ?>
                                <?php else: ?>
                                    À confirmer
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($is_refused): ?>
                        <!-- رسالة للمستخدم عند الرفض -->
                        <p style="margin-top:16px; font-size:12px; color:#880E4F; background:#FEE2E2; padding:8px 14px; border-radius:8px; display:inline-block;">
                            ✗ Votre demande a été refusée par l'administrateur.
                        </p>

                    <?php elseif ($statut_brut === 'acceptée' && (!$date_prevue || $today <= $date_prevue)): ?>
                        <form method="POST" action="prolonger_action.php">
                            <input type="hidden" name="id_emprunt" value="<?= $e['id_emprunt'] ?>">
                            <button type="submit" class="btn-prolong">
                                <i class="fas fa-history"></i> Prolonger de 7 jours
                            </button>
                        </form>
                    <?php endif; ?>

                </div>
            </div>

            <?php endwhile; ?>

        <?php else: ?>
            <div style="text-align:center; padding:100px 40px; background:white; border-radius:30px; border:2px dashed #E6E1D8;">
                <i class="fas fa-book-open fa-3x" style="color:var(--premium-gold); margin-bottom:20px;"></i>
                <h3 style="color:var(--text-dark)">Aucun emprunt trouvé</h3>
                <p style="color:#9A8C7E; margin-bottom:25px;">Commencez votre aventure littéraire dès maintenant.</p>
                <a href="../client/library.php" class="btn-prolong" style="display:inline-flex;">
                    Explorer le catalogue
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>