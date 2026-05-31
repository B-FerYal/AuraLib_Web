<?php
/**
 * ══════════════════════════════════════════════════════════════════
 *  AuraLib · check_notifications.php
 *  Script de rappels automatiques pour les emprunts
 *
 *  UTILISATION :
 *    → Manuellement   : php check_notifications.php
 *    → Via CRON       : 0 8 * * * php /var/www/html/MEMOIR/client/check_notifications.php
 *    → Via navigateur : http://localhost/MEMOIR/client/check_notifications.php
 *      (protéger avec un token secret en production)
 *
 *  DÉPENDANCES :
 *    composer require phpmailer/phpmailer
 * ══════════════════════════════════════════════════════════════════
 */

// ── Sécurité basique (token en prod) ──────────────────────────────
// if (($_GET['token'] ?? '') !== 'MON_TOKEN_SECRET') { http_response_code(403); exit; }

// ── Bootstrap ─────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__)); // remonte d'un niveau depuis /client
require_once BASE_PATH . '/includes/db.php';          // $conn : objet MySQLi
require_once BASE_PATH . '/vendor/autoload.php';      // Composer PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── 1. CONFIGURATION SMTP ─────────────────────────────────────────
// ▶▶ MODIFIE CES VALEURS AVEC TON VRAI COMPTE SMTP ◀◀
define('SMTP_HOST',     'smtp.gmail.com');          // ex: smtp.gmail.com, smtp.mailtrap.io
define('SMTP_PORT',     587);                       // 587 = TLS  |  465 = SSL
define('SMTP_USER',     'ton.email@gmail.com');     // ← TON EMAIL
define('SMTP_PASS',     'ton_mot_de_passe_app');    // ← MOT DE PASSE APPLICATION (Gmail)
define('SMTP_FROM',     'ton.email@gmail.com');     // adresse expéditrice
define('SMTP_FROM_NAME','AuraLib · Bibliothèque');  // nom affiché

// ── 2. AMENDE JOURNALIÈRE ─────────────────────────────────────────
define('AMENDE_PAR_JOUR', 10); // DA par jour de retard

// ── Helper : insérer une notification interne ─────────────────────
function insertNotif(
    mysqli $conn,
    int    $id_user,
    string $type,     // 'info' | 'warning' | 'danger' | 'success'
    string $titre,
    string $message,
    string $lien = ''
): bool {
    $stmt = $conn->prepare(
        "INSERT INTO notifications (id_user, type, titre, message, lu, lien)
         VALUES (?, ?, ?, ?, 0, ?)"
    );
    $stmt->bind_param('issss', $id_user, $type, $titre, $message, $lien);
    return $stmt->execute();
}

// ── Helper : vérifier doublon (même emprunt + même type aujourd'hui) ──
function dejaEnvoye(mysqli $conn, int $id_user, string $titre): bool {
    $stmt = $conn->prepare(
        "SELECT id FROM notifications
         WHERE id_user = ?
           AND titre = ?
           AND DATE(created_at) = CURDATE()
         LIMIT 1"
    );
    $stmt->bind_param('is', $id_user, $titre);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// ── Helper : envoi email PHPMailer ────────────────────────────────
function sendEmail(
    string $to_email,
    string $to_name,
    string $subject,
    string $body_html
): bool {
    $mail = new PHPMailer(true);
    try {
        // ▶ Serveur SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // changer en SMTPS si port 465
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // ▶ Expéditeur & destinataire
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $to_name);

        // ▶ Contenu HTML
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body_html;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>'], "\n", $body_html));

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('[AuraLib Email] Erreur : ' . $mail->ErrorInfo);
        return false;
    }
}

// ── Helper : gabarit HTML email ───────────────────────────────────
function emailTemplate(
    string $prenom,
    string $titre_livre,
    string $message_corps,
    string $couleur_accent = '#C4A46B'
): string {
    return "
    <!DOCTYPE html>
    <html lang='fr'>
    <head><meta charset='UTF-8'><meta name='viewport' content='width=device-width'></head>
    <body style='margin:0;padding:0;background:#F2EDE3;font-family:Georgia,serif;'>
      <table width='100%' cellpadding='0' cellspacing='0'>
        <tr><td align='center' style='padding:40px 20px;'>
          <table width='600' cellpadding='0' cellspacing='0'
                 style='background:#1A0E05;border-radius:16px;overflow:hidden;'>
            <!-- Header doré -->
            <tr>
              <td style='background:linear-gradient(135deg,#1A0E05,#2E1D08);
                          padding:32px 40px;border-bottom:2px solid {$couleur_accent};'>
                <h1 style='margin:0;font-size:28px;color:{$couleur_accent};letter-spacing:2px;'>
                  ✦ AuraLib
                </h1>
                <p style='margin:4px 0 0;font-size:11px;color:rgba(196,164,107,.5);
                           letter-spacing:4px;text-transform:uppercase;'>
                  Bibliothèque Premium
                </p>
              </td>
            </tr>
            <!-- Corps -->
            <tr>
              <td style='padding:36px 40px;'>
                <p style='color:#EDE5D4;font-size:16px;margin:0 0 8px;'>
                  Cher(e) <strong style='color:{$couleur_accent};'>{$prenom}</strong>,
                </p>
                <div style='background:rgba(196,164,107,.08);border-left:3px solid {$couleur_accent};
                             border-radius:0 8px 8px 0;padding:18px 20px;margin:20px 0;'>
                  <p style='color:#C4A46B;font-size:13px;margin:0 0 6px;
                              letter-spacing:1px;text-transform:uppercase;font-family:sans-serif;'>
                    Document emprunté
                  </p>
                  <p style='color:#FDFAF5;font-size:17px;margin:0;font-weight:bold;'>
                    📖 {$titre_livre}
                  </p>
                </div>
                <p style='color:#9A8C7E;font-size:15px;line-height:1.7;margin:0;'>
                  {$message_corps}
                </p>
              </td>
            </tr>
            <!-- Footer -->
            <tr>
              <td style='padding:20px 40px;border-top:1px solid rgba(196,164,107,.15);'>
                <p style='margin:0;font-size:11px;color:rgba(154,140,126,.5);text-align:center;'>
                  © AuraLib · Ce message est automatique, merci de ne pas y répondre.
                </p>
              </td>
            </tr>
          </table>
        </td></tr>
      </table>
    </body>
    </html>";
}

// ══════════════════════════════════════════════════════════════════
//  3. LOGIQUE PRINCIPALE
// ══════════════════════════════════════════════════════════════════

$today    = new DateTime('today');
$counters = ['j7' => 0, 'j2' => 0, 'retard' => 0, 'amende' => 0, 'skipped' => 0];

// ── Récupérer tous les emprunts actifs (acceptée + retard) ────────
$emprunts = $conn->query("
    SELECT e.id_emprunt, e.id_user, e.id_doc,
           e.date_debut, e.date_retour_prevue, e.statut, e.amende,
           u.firstname, u.lastname, u.email,
           d.titre
    FROM   emprunt e
    JOIN   users u     ON u.id     = e.id_user
    JOIN   documents d ON d.id_doc = e.id_doc
    WHERE  e.statut IN ('acceptée', 'retard')
      AND  e.date_retour_prevue IS NOT NULL
");

if (!$emprunts) {
    die('[AuraLib] Erreur SQL : ' . $conn->error . "\n");
}

while ($row = $emprunts->fetch_assoc()) {

    $id_user      = (int)$row['id_user'];
    $id_emprunt   = (int)$row['id_emprunt'];
    $prenom       = htmlspecialchars($row['firstname']);
    $email        = $row['email'];
    $titre_livre  = htmlspecialchars($row['titre']);
    $statut       = $row['statut'];

    $date_retour  = new DateTime($row['date_retour_prevue']);
    $diff         = (int)$today->diff($date_retour)->days; // nb de jours entre aujourd'hui et retour
    $est_passe    = $today > $date_retour;                 // true = dépassée

    // ─── CAS A : Retard ───────────────────────────────────────────
    if ($est_passe) {

        // A1. Passer le statut en 'retard' si pas encore fait
        if ($statut === 'acceptée') {
            $upd = $conn->prepare("UPDATE emprunt SET statut='retard' WHERE id_emprunt=?");
            $upd->bind_param('i', $id_emprunt);
            $upd->execute();
        }

        // A2. Calculer & mettre à jour l'amende
        $jours_retard = (int)$today->diff($date_retour)->days;
        $amende_due   = $jours_retard * AMENDE_PAR_JOUR;
        $upd2 = $conn->prepare("UPDATE emprunt SET amende=? WHERE id_emprunt=?");
        $upd2->bind_param('di', $amende_due, $id_emprunt);
        $upd2->execute();
        $counters['amende']++;

        // A3. Notification interne "RETARD" (1 fois par jour)
        $titre_notif = "⚠️ Retard · Emprunt #{$id_emprunt}";
        if (!dejaEnvoye($conn, $id_user, $titre_notif)) {
            $msg = "Vous avez dépassé la date de retour du document « {$titre_livre} ».\n"
                 . "Retard actuel : {$jours_retard} jour(s) — Amende : {$amende_due} DA.\n"
                 . "Veuillez retourner le document dès que possible.";
            insertNotif($conn, $id_user, 'danger', $titre_notif, $msg, '/MEMOIR/emprunts/mes_emprunts.php');

            // A4. Email retard
            $body = emailTemplate(
                $prenom,
                $titre_livre,
                "Vous avez dépassé la date de retour prévue.<br><br>
                 <strong style='color:#ef4444;'>Retard : {$jours_retard} jour(s)</strong><br>
                 <strong style='color:#ef4444;'>Amende cumulée : {$amende_due} DA</strong><br><br>
                 Merci de retourner le document à la bibliothèque dès que possible afin d'éviter l'augmentation de votre amende.",
                '#ef4444' // rouge pour les retards
            );
            sendEmail($email, $prenom, "⚠️ AuraLib · Retard de retour - {$titre_livre}", $body);
            $counters['retard']++;
        } else {
            $counters['skipped']++;
        }

    // ─── CAS B : Rappel J-2 ───────────────────────────────────────
    } elseif ($diff === 2) {

        $titre_notif = "🔔 Rappel urgent · Emprunt #{$id_emprunt}";
        if (!dejaEnvoye($conn, $id_user, $titre_notif)) {
            $msg = "Votre emprunt du document « {$titre_livre} » expire dans 2 jours "
                 . "(le " . $date_retour->format('d/m/Y') . "). "
                 . "Pensez à le retourner pour éviter toute amende.";
            insertNotif($conn, $id_user, 'warning', $titre_notif, $msg, '/MEMOIR/emprunts/mes_emprunts.php');

            $body = emailTemplate(
                $prenom,
                $titre_livre,
                "Votre emprunt expire dans <strong style='color:#F59E0B;'>2 jours</strong> 
                 (le <strong>" . $date_retour->format('d/m/Y') . "</strong>).<br><br>
                 Pensez à retourner votre document avant cette date pour éviter une amende de 
                 <strong>" . AMENDE_PAR_JOUR . " DA/jour</strong>.",
                '#F59E0B'
            );
            sendEmail($email, $prenom, "🔔 AuraLib · Rappel urgent - {$titre_livre}", $body);
            $counters['j2']++;
        } else {
            $counters['skipped']++;
        }

    // ─── CAS C : Rappel J-7 ───────────────────────────────────────
    } elseif ($diff === 7) {

        $titre_notif = "📚 Rappel · Emprunt #{$id_emprunt}";
        if (!dejaEnvoye($conn, $id_user, $titre_notif)) {
            $msg = "Votre emprunt du document « {$titre_livre} » arrive à échéance "
                 . "dans 7 jours (le " . $date_retour->format('d/m/Y') . "). "
                 . "Ce rappel est envoyé par précaution.";
            insertNotif($conn, $id_user, 'info', $titre_notif, $msg, '/MEMOIR/emprunts/mes_emprunts.php');

            $body = emailTemplate(
                $prenom,
                $titre_livre,
                "Votre emprunt arrive à échéance dans <strong style='color:#C4A46B;'>7 jours</strong>
                 (le <strong>" . $date_retour->format('d/m/Y') . "</strong>).<br><br>
                 Ce message est un rappel amical. Vous avez encore le temps, mais n'oubliez pas !",
                '#C4A46B'
            );
            sendEmail($email, $prenom, "📚 AuraLib · Rappel emprunt - {$titre_livre}", $body);
            $counters['j7']++;
        } else {
            $counters['skipped']++;
        }
    }
}

// ── Rapport final ─────────────────────────────────────────────────
echo "[AuraLib] Rapport du " . $today->format('d/m/Y H:i') . "\n";
echo "  ✓ Rappels J-7   : {$counters['j7']}\n";
echo "  ✓ Rappels J-2   : {$counters['j2']}\n";
echo "  ✓ Alertes retard: {$counters['retard']}\n";
echo "  ✓ Amendes MAJ   : {$counters['amende']}\n";
echo "  ↷ Doublons évités: {$counters['skipped']}\n";