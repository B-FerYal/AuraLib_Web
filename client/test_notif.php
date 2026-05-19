<?php
/**
 * ══════════════════════════════════════════════════════════════════
 *  AuraLib · test_notif.php
 *  Script de TEST — à placer dans /MEMOIR/client/
 *  Ouvre dans le navigateur : http://localhost/MEMOIR/client/test_notif.php
 *
 *  Ce script :
 *  1. Vérifie les emprunts en retard dans la DB
 *  2. Insère les notifications manquantes SANS email
 *  3. Affiche un rapport complet
 *
 *  ⚠️  SUPPRIMER CE FICHIER APRÈS LES TESTS EN PRODUCTION
 * ══════════════════════════════════════════════════════════════════
 */

// ── Pas de session nécessaire pour ce script de test ──
require_once "../includes/db.php";

echo "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'>
<title>AuraLib · Test Notifications</title>
<style>
  body { font-family: monospace; background:#1A0E05; color:#EDE5D4; padding:30px; }
  h1   { color:#C4A46B; border-bottom:1px solid #3A2E1E; padding-bottom:10px; }
  h2   { color:#D4B47B; margin-top:30px; }
  .ok  { color:#4ade80; }
  .err { color:#f87171; }
  .warn{ color:#fbbf24; }
  .box { background:#2E1D08; border:1px solid #3A2E1E; border-radius:8px;
         padding:16px 20px; margin:12px 0; }
  table{ border-collapse:collapse; width:100%; margin-top:10px; }
  th   { color:#C4A46B; text-align:left; padding:8px 12px;
         border-bottom:1px solid #3A2E1E; font-size:11px; letter-spacing:2px; text-transform:uppercase; }
  td   { padding:8px 12px; border-bottom:1px solid #1A0E05; font-size:13px; }
  tr:hover td { background:rgba(196,164,107,.05); }
  .badge { padding:3px 10px; border-radius:20px; font-size:11px; font-weight:bold; }
  .b-retard  { background:rgba(192,57,43,.2);  color:#f87171; border:1px solid rgba(192,57,43,.4); }
  .b-warning { background:rgba(251,191,36,.15); color:#fbbf24; border:1px solid rgba(251,191,36,.3); }
  .b-info    { background:rgba(3,105,161,.15);  color:#7dd3fc; border:1px solid rgba(3,105,161,.3); }
</style></head><body>";

echo "<h1>🔔 AuraLib · Diagnostic Notifications</h1>";
echo "<p style='color:#9A8C7E;'>Date du serveur : <strong style='color:#C4A46B;'>" . date('d/m/Y H:i:s') . "</strong></p>";

// ════════════════════════════════════════════════════════════════
// ÉTAPE 1 : Vérifier la table notifications
// ════════════════════════════════════════════════════════════════
echo "<h2>① Table notifications</h2>";
$check = $conn->query("SELECT COUNT(*) as n FROM notifications");
if ($check) {
    $total_notifs = $check->fetch_assoc()['n'];
    echo "<div class='box'><span class='ok'>✓</span> Table accessible — <strong>{$total_notifs}</strong> notifications en base</div>";
} else {
    echo "<div class='box'><span class='err'>✗ Erreur : " . $conn->error . "</span></div>";
    exit;
}

// ════════════════════════════════════════════════════════════════
// ÉTAPE 2 : Lister tous les emprunts actifs
// ════════════════════════════════════════════════════════════════
echo "<h2>② Emprunts actifs (acceptée + retard)</h2>";
$emprunts = $conn->query("
    SELECT e.id_emprunt, e.id_user, e.statut,
           e.date_debut, e.date_retour_prevue, e.date_fin, e.amende,
           u.firstname, u.email,
           d.titre,
           DATEDIFF(CURDATE(), e.date_retour_prevue) AS jours_diff
    FROM   emprunt e
    JOIN   users u     ON u.id = e.id_user
    JOIN   documents d ON d.id_doc = e.id_doc
    WHERE  e.statut IN ('acceptée', 'retard')
      AND  e.date_retour_prevue IS NOT NULL
    ORDER BY e.id_emprunt
");

if (!$emprunts || $emprunts->num_rows === 0) {
    echo "<div class='box'><span class='warn'>⚠ Aucun emprunt actif trouvé avec date_retour_prevue</span></div>";
} else {
    echo "<div class='box'><table>
    <tr>
        <th>#</th><th>Utilisateur</th><th>Document</th>
        <th>Retour prévu</th><th>Jours diff</th><th>Statut</th><th>Alerte</th>
    </tr>";
    
    $rows = $emprunts->fetch_all(MYSQLI_ASSOC);
    foreach ($rows as $r) {
        $diff  = (int)$r['jours_diff']; // positif = retard, négatif = dans le futur
        $alerte = '';
        $badge  = '';
        if ($diff > 0) {
            $alerte = "RETARD ({$diff}j)";
            $badge  = "b-retard";
        } elseif ($diff >= -2) {
            $alerte = "J-" . abs($diff) . " (urgent)";
            $badge  = "b-warning";
        } elseif ($diff >= -7) {
            $alerte = "J-" . abs($diff) . " (rappel)";
            $badge  = "b-info";
        } else {
            $alerte = "OK (" . abs($diff) . "j restants)";
            $badge  = "";
        }
        echo "<tr>
            <td>#{$r['id_emprunt']}</td>
            <td>{$r['firstname']}<br><small style='color:#9A8C7E'>{$r['email']}</small></td>
            <td>" . mb_substr($r['titre'], 0, 30) . "…</td>
            <td style='color:" . ($diff > 0 ? '#f87171' : '#4ade80') . "'>{$r['date_retour_prevue']}</td>
            <td style='color:" . ($diff > 0 ? '#f87171' : '#EDE5D4') . "'><strong>{$diff}</strong></td>
            <td><span class='badge b-" . ($r['statut']==='retard'?'retard':'info') . "'>{$r['statut']}</span></td>
            <td>" . ($badge ? "<span class='badge {$badge}'>{$alerte}</span>" : "<span style='color:#9A8C7E'>{$alerte}</span>") . "</td>
        </tr>";
    }
    echo "</table></div>";
}

// ════════════════════════════════════════════════════════════════
// ÉTAPE 3 : INSÉRER LES NOTIFICATIONS MANQUANTES (sans email)
// ════════════════════════════════════════════════════════════════
echo "<h2>③ Insertion des notifications manquantes</h2>";

$inserted = 0;
$skipped  = 0;

if (!empty($rows)) {
    foreach ($rows as $r) {
        $id_user    = (int)$r['id_user'];
        $id_emprunt = (int)$r['id_emprunt'];
        $prenom     = $r['firstname'];
        $titre      = $r['titre'];
        $diff       = (int)$r['jours_diff'];

        if ($diff > 0) {
            // ── RETARD ──
            // Mettre à jour le statut si nécessaire
            if ($r['statut'] === 'acceptée') {
                $conn->query("UPDATE emprunt SET statut='retard' WHERE id_emprunt={$id_emprunt}");
                echo "<div class='box'><span class='ok'>✓</span> Emprunt #{$id_emprunt} → statut mis à jour : <strong>retard</strong></div>";
            }
            // Calculer amende
            $amende = $diff * 10;
            $conn->query("UPDATE emprunt SET amende={$amende} WHERE id_emprunt={$id_emprunt}");

            $titre_notif = "⚠️ Retard · Emprunt #{$id_emprunt}";
            $msg = "Vous avez dépassé la date de retour du document « {$titre} ».\nRetard actuel : {$diff} jour(s) — Amende : {$amende} DA.\nVeuillez retourner le document dès que possible.";
            $type = 'danger';

        } elseif ($diff >= -2) {
            // ── J-2 ──
            $titre_notif = "🔔 Rappel urgent · Emprunt #{$id_emprunt}";
            $msg = "Votre emprunt du document « {$titre} » expire dans " . abs($diff) . " jour(s) (le {$r['date_retour_prevue']}). Pensez à le retourner pour éviter toute amende.";
            $type = 'warning';

        } elseif ($diff >= -7) {
            // ── J-7 ──
            $titre_notif = "📚 Rappel · Emprunt #{$id_emprunt}";
            $msg = "Votre emprunt du document « {$titre} » arrive à échéance dans " . abs($diff) . " jours (le {$r['date_retour_prevue']}).";
            $type = 'info';

        } else {
            $skipped++;
            continue; // pas d'alerte à envoyer
        }

        // Vérifier doublon du jour
        $chk = $conn->prepare("SELECT id FROM notifications WHERE id_user=? AND titre=? AND DATE(created_at)=CURDATE() LIMIT 1");
        $chk->bind_param('is', $id_user, $titre_notif);
        $chk->execute();

        if ($chk->get_result()->num_rows > 0) {
            echo "<div class='box'><span class='warn'>↷</span> Doublon ignoré pour <strong>{$prenom}</strong> — « {$titre_notif} »</div>";
            $skipped++;
            continue;
        }

        // Insérer
        $lien = '/MEMOIR/client/mes_emprunts.php';
        $ins  = $conn->prepare("INSERT INTO notifications (id_user, type, titre, message, lu, lien) VALUES (?, ?, ?, ?, 0, ?)");
        $ins->bind_param('issss', $id_user, $type, $titre_notif, $msg, $lien);
        
        if ($ins->execute()) {
            echo "<div class='box'><span class='ok'>✓</span> Notification insérée pour <strong style='color:#C4A46B'>{$prenom}</strong> 
                  — <span class='badge b-" . ($type==='danger'?'retard':($type==='warning'?'warning':'info')) . "'>{$titre_notif}</span></div>";
            $inserted++;
        } else {
            echo "<div class='box'><span class='err'>✗ Erreur INSERT : " . $conn->error . "</span></div>";
        }
    }
}

// ════════════════════════════════════════════════════════════════
// RAPPORT FINAL
// ════════════════════════════════════════════════════════════════
echo "<h2>④ Rapport</h2>";
echo "<div class='box'>
    <p><span class='ok'>✓</span> Notifications insérées : <strong style='color:#4ade80'>{$inserted}</strong></p>
    <p><span class='warn'>↷</span> Ignorées (ok ou doublons) : <strong style='color:#fbbf24'>{$skipped}</strong></p>
    <br>
    <p style='color:#9A8C7E; font-size:12px;'>
        → Maintenant ouvre la page notifications.php côté client pour vérifier l'affichage.<br>
        → Supprime ce fichier test_notif.php après validation.
    </p>
</div>

<div style='margin-top:24px;'>
    <a href='/MEMOIR/client/notifications.php' 
       style='display:inline-flex;align-items:center;gap:8px;padding:12px 24px;
              background:linear-gradient(135deg,#C4A46B,#A8884E);color:#1A0E05;
              border-radius:30px;text-decoration:none;font-weight:800;font-size:13px;'>
       🔔 Voir les notifications d'amin
    </a>
</div>
</body></html>";