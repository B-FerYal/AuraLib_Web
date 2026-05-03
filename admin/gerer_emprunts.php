<?php
session_start();
require_once "../includes/db.php";
require_once '../includes/head.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Auto-update retards
$conn->query("UPDATE emprunt SET statut = 'retard' 
              WHERE date_retour_prevue < CURDATE() 
              AND statut = 'acceptée' 
              AND date_fin IS NULL");

// Messages flash
$msg = $_GET['msg'] ?? '';
$amende = (int)($_GET['amende'] ?? 0);

$flash_messages = [
    'accepted'       => ['type' => 'success', 'text' => '✅ Emprunt accepté — stock mis à jour.'],
    'refused'        => ['type' => 'warning', 'text' => '🚫 Emprunt refusé.'],
    'returned'       => ['type' => 'success', 'text' => '📦 Livre retourné — stock restauré.' . ($amende > 0 ? " Amende : <strong>{$amende} DA</strong>" : '')],
    'no_stock'       => ['type' => 'danger',  'text' => '❌ Impossible d\'accepter : stock épuisé pour ce document.'],
    'invalid_status' => ['type' => 'warning', 'text' => '⚠️ Action non autorisée pour ce statut.'],
    'error'          => ['type' => 'danger',  'text' => '❌ Une erreur est survenue.'],
];

// Récupérer emprunts avec toutes les infos
$result = $conn->query("
    SELECT e.*, u.firstname, u.lastname, d.titre, d.exemplaires_disponibles
    FROM emprunt e 
    JOIN users u ON e.id_user = u.id 
    JOIN documents d ON e.id_doc = d.id_doc 
    ORDER BY 
        CASE 
            WHEN e.statut = 'en attente' THEN 1 
            WHEN e.statut = 'retard'     THEN 2 
            WHEN e.statut = 'acceptée'   THEN 3 
            ELSE 4 
        END, 
        e.id_emprunt DESC
");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AuraLib | Gestion des Emprunts</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --taupe:#2C1F0E; --gold:#D4A942; --cream:#F9F5EE; --white:#fff; --sidebar-w:260px; }
        body { font-family:'Inter',sans-serif; background:var(--cream); display:flex; margin:0; }
        .main-wrapper { flex:1; margin-left:var(--sidebar-w); padding:40px; }
        .container { background:var(--white); border-radius:20px; padding:30px; box-shadow:0 4px 20px rgba(0,0,0,.04); }
        h2 { font-family:'Playfair Display',serif; color:var(--taupe); font-size:26px; margin-bottom:20px; }

        /* Flash */
        .flash { padding:12px 16px; border-radius:10px; margin-bottom:20px; font-size:13px; font-weight:500; }
        .flash.success { background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; }
        .flash.warning { background:#fffbeb; border:1px solid #fde68a; color:#92400e; }
        .flash.danger  { background:#fef2f2; border:1px solid #fecaca; color:#dc2626; }

        table { width:100%; border-collapse:collapse; }
        th { padding:12px 14px; text-align:left; font-size:11px; text-transform:uppercase; color:#999; border-bottom:2px solid #f0ebe0; }
        td { padding:16px 14px; font-size:13px; color:var(--taupe); border-bottom:1px solid #f9f5ee; }
        tr:hover td { background:#fdfaf4; }

        /* Badges statuts */
        .badge { padding:5px 11px; border-radius:8px; font-size:11px; font-weight:700; display:inline-block; }
        .s-en-attente { background:#fffde7; color:#f57f17; border:1px solid #fff176; }
        .s-acceptee   { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; }
        .s-retard     { background:#ffebee; color:#c62828; border:1px solid #ef9a9a; animation:pulse 2s infinite; }
        .s-rendu      { background:#f5f5f5; color:#757575; border:1px solid #e0e0e0; }
        .s-refusee    { background:#fce4ec; color:#880e4f; border:1px solid #f48fb1; }

        @keyframes pulse { 
            0%   { box-shadow:0 0 0 0 rgba(198,40,40,.4); } 
            70%  { box-shadow:0 0 0 8px rgba(198,40,40,0); } 
            100% { box-shadow:0 0 0 0 rgba(198,40,40,0); } 
        }

        /* Boutons actions */
        .btn { padding:7px 14px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:600; transition:.2s; display:inline-flex; align-items:center; gap:5px; margin-right:4px; }
        .btn-approve { background:var(--taupe); color:#fff; }
        .btn-approve:hover { background:#45321c; }
        .btn-refuse  { background:#fce4ec; color:#880e4f; border:1px solid #f48fb1; }
        .btn-refuse:hover { background:#f8bbd0; }
        .btn-return  { background:var(--gold); color:#fff; }
        .btn-return:hover { background:#b8892e; }

        /* Stock badge */
        .stock-ok   { color:#15803d; font-size:11px; font-weight:600; }
        .stock-zero { color:#dc2626; font-size:11px; font-weight:600; }

        /* Info user */
        .user-name  { font-weight:600; color:var(--taupe); }
        .user-book  { font-size:12px; color:#888; margin-top:2px; }

        /* Amende */
        .amende-badge { background:#fef9c3; color:#854d0e; padding:3px 8px; border-radius:6px; font-size:11px; font-weight:600; }
    </style>
</head>
<body>

<div class="main-wrapper">
<div class="container">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2>📚 Gestion des Emprunts</h2>
        <span style="font-size:12px; color:#888;">Aujourd'hui : <strong><?= date('d/m/Y') ?></strong></span>
    </div>

    <?php if ($msg && isset($flash_messages[$msg])): 
        $f = $flash_messages[$msg]; ?>
        <div class="flash <?= $f['type'] ?>"><?= $f['text'] ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Lecteur & Document</th>
                <th>Stock</th>
                <th>Date demande</th>
                <th>Retour prévu</th>
                <th>Amende</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): 
            // Classe CSS du statut
            $statut_css = match($row['statut']) {
                'en attente' => 's-en-attente',
                'acceptée'   => 's-acceptee',
                'retard'     => 's-retard',
                'rendu'      => 's-rendu',
                'refusée'    => 's-refusee',
                default      => 's-rendu'
            };

            // Calcul jours retard si applicable
            $jours_retard = 0;
            if ($row['statut'] === 'retard' && !empty($row['date_retour_prevue'])) {
                $jours_retard = (int)(new DateTime())->diff(new DateTime($row['date_retour_prevue']))->days;
            }
        ?>
        <tr>
            <td style="color:#aaa; font-size:12px;">#<?= $row['id_emprunt'] ?></td>
            <td>
                <div class="user-name"><?= htmlspecialchars($row['firstname'].' '.$row['lastname']) ?></div>
                <div class="user-book">📖 <?= htmlspecialchars($row['titre']) ?></div>
            </td>
            <td>
                <?php if ((int)$row['exemplaires_disponibles'] > 0): ?>
                    <span class="stock-ok">✓ <?= $row['exemplaires_disponibles'] ?> dispo</span>
                <?php else: ?>
                    <span class="stock-zero">✗ Épuisé</span>
                <?php endif; ?>
            </td>
            <td style="font-size:12px; color:#888;">
                <?= $row['date_debut'] ? date('d/m/Y', strtotime($row['date_debut'])) : '—' ?>
            </td>
            <td style="font-weight:600; color:<?= $row['statut']==='retard' ? '#c62828' : '#555' ?>;">
                <?php if ($row['date_retour_prevue']): ?>
                    <?= date('d/m/Y', strtotime($row['date_retour_prevue'])) ?>
                    <?php if ($row['statut'] === 'retard'): ?>
                        <br><small style="color:#c62828;"><?= $jours_retard ?> j de retard</small>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color:#ccc;">—</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($row['amende'] > 0): ?>
                    <span class="amende-badge"><?= number_format($row['amende'], 0) ?> DA</span>
                <?php else: ?>
                    <span style="color:#ccc; font-size:12px;">—</span>
                <?php endif; ?>
            </td>
            <td>
                <span class="badge <?= $statut_css ?>">
                    <?= ucfirst($row['statut']) ?>
                </span>
            </td>
            <td>
                <?php if ($row['statut'] === 'en attente'): ?>
                    <a href="action_emprunts.php?id=<?= $row['id_emprunt'] ?>&action=accepter" 
                       class="btn btn-approve"
                       <?= (int)$row['exemplaires_disponibles'] <= 0 ? 'style="opacity:.5;pointer-events:none;" title="Stock épuisé"' : '' ?>>
                        ✓ Accepter
                    </a>
                    <a href="action_emprunts.php?id=<?= $row['id_emprunt'] ?>&action=refuser"
                       class="btn btn-refuse"
                       onclick="return confirm('Refuser cet emprunt ?')">
                        ✗ Refuser
                    </a>

                <?php elseif (in_array($row['statut'], ['acceptée', 'retard'])): ?>
                    <a href="action_emprunts.php?id=<?= $row['id_emprunt'] ?>&action=rendre"
                       class="btn btn-return"
                       onclick="return confirm('Confirmer le retour de ce livre ?')">
                        ↩ Marquer Rendu
                    </a>

                <?php else: ?>
                    <small style="color:#bbb;">—</small>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</div>
</body>
</html>