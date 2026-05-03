<?php
include "../includes/header.php";

$success = '';
$error   = '';

// ── Pré-remplir si connecté ──────────────────────────────
$pre_name  = '';
$pre_email = '';
if (isset($is_logged_in) && $is_logged_in && !empty($user)) {
    $pre_name  = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
    $pre_email = $user['email'] ?? '';
}

// ── Traitement du formulaire ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $subject = trim($_POST['subject'] ?? 'Question générale');
    $message = trim($_POST['message'] ?? '');

    // Correction de la syntaxe : Ajout des opérateurs ||
    if (empty($name) || empty($email) || empty($message)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } else {
        $uid         = (isset($is_logged_in) && $is_logged_in) ? ($id_user ?? null) : null;
        $name_esc    = $conn->real_escape_string($name);
        $email_esc   = $conn->real_escape_string($email);
        $subject_esc = $conn->real_escape_string($subject);
        $message_esc = $conn->real_escape_string($message);
        $uid_sql     = $uid ? $uid : 'NULL';

        $ok = $conn->query("
            INSERT INTO contact_messages (id_user, name, email, subject, message)
            VALUES ($uid_sql, '$name_esc', '$email_esc', '$subject_esc', '$message_esc')
        ");

        if ($ok) {
            $success = "Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.";
            // Réinitialiser les champs après succès
            $pre_name = $pre_email = $subject = $message = '';
        } else {
            $error = "Une erreur est survenue lors de l'envoi. Veuillez réessayer.";
        }
    }
}
?>
<title>Contact — AuraLibre</title>
<style>
.contact-page { max-width: 1000px; margin: 0 auto; padding: 60px 20px 80px; }

.contact-hero { text-align: center; margin-bottom: 52px; }
.badge-pill {
    display: inline-block; background: rgba(196,164,107,.12);
    border: 1px solid rgba(196,164,107,.3); color: #C4A46B;
    font-size: 11px; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; padding: 6px 18px; border-radius: 30px; margin-bottom: 20px;
}
.contact-hero h1 {
    font-family: 'Playfair Display', serif; font-size: 40px;
    color: var(--page-text, #2C1F0E); margin-bottom: 14px; line-height: 1.2;
}
.contact-hero h1 em { color: #C4A46B; font-style: normal; }
.contact-hero p { font-size: 15px; color: var(--page-muted, #9A8C7E); max-width: 520px; margin: 0 auto; line-height: 1.75; }

.contact-layout { display: grid; grid-template-columns: 1fr 1.6fr; gap: 28px; align-items: start; }

/* Info cards */
.info-panel { display: flex; flex-direction: column; gap: 12px; }
.info-card {
    background: var(--page-white, #FFFDF9); border: 1px solid var(--page-border, #DDD5C8);
    border-radius: 12px; padding: 18px; display: flex; align-items: flex-start; gap: 13px;
    transition: border-color .2s;
}
.info-card:hover { border-color: #C4A46B; }
.info-icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: rgba(196,164,107,.1); color: #C4A46B;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.info-body h4 { font-size: 13px; font-weight: 700; color: var(--page-text, #2C1F0E); margin-bottom: 4px; }
.info-body p  { font-size: 12px; color: var(--page-muted, #9A8C7E); line-height: 1.6; }
.info-body a  { color: #C4A46B; text-decoration: none; font-weight: 600; }

/* FAQ */
.faq-card {
    background: var(--page-white, #FFFDF9); border: 1px solid var(--page-border, #DDD5C8);
    border-radius: 12px; padding: 18px;
}
.faq-card h4 { font-size: 13px; font-weight: 700; color: var(--page-text, #2C1F0E); margin-bottom: 10px; }
.faq-item { padding: 7px 0; border-bottom: 0.5px solid var(--page-border, #DDD5C8); }
.faq-item:last-child { border-bottom: none; }
.faq-q { font-size: 12px; font-weight: 600; color: var(--page-text, #2C1F0E); margin-bottom: 3px; }
.faq-a { font-size: 11px; color: var(--page-muted, #9A8C7E); line-height: 1.5; }

/* Form */
.form-card {
    background: var(--page-white, #FFFDF9); border: 1px solid var(--page-border, #DDD5C8);
    border-radius: 16px; padding: 32px;
}
.form-card h2 {
    font-family: 'Playfair Display', serif; font-size: 21px;
    color: var(--page-text, #2C1F0E); margin-bottom: 22px;
    padding-bottom: 14px; border-bottom: 1px solid var(--page-border, #DDD5C8);
}
.form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 13px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 12px; font-weight: 700; color: var(--page-text, #2C1F0E); margin-bottom: 6px; }
.req { color: #e74c3c; margin-left: 2px; }
.form-group input,
.form-group select,
.form-group textarea {
    width: 100%; padding: 10px 13px;
    border: 1.5px solid var(--page-border, #DDD5C8); border-radius: 8px;
    font-size: 13px; font-family: inherit;
    background: var(--page-bg, #F5F0E8); color: var(--page-text, #2C1F0E);
    transition: border-color .15s, background .15s; outline: none;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { border-color: #C4A46B; background: white; box-shadow: 0 4px 14px rgba(196,164,107,.12); }
.form-group textarea { resize: vertical; min-height: 130px; line-height: 1.6; }
.char-count { font-size: 10px; color: var(--page-muted, #9A8C7E); text-align: right; margin-top: 3px; }

.btn-send {
    width: 100%; padding: 13px; background: #C4A46B; color: #2C1F0E;
    border: none; border-radius: 10px; font-size: 14px; font-weight: 700;
    cursor: pointer; font-family: inherit; transition: background .2s, transform .15s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.btn-send:hover { background: #D4B47B; transform: translateY(-2px); }
.btn-send svg { width: 15px; height: 15px; }

.alert { padding: 12px 15px; border-radius: 9px; font-size: 13px; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; border-left: 3px solid; }
.alert svg { width: 14px; height: 14px; flex-shrink: 0; }
.alert-success { background: #f0fdf4; border-color: #4ade80; color: #15803d; }
.alert-error   { background: #fef2f2; border-color: #f87171; color: #dc2626; }

.response-note { display: flex; align-items: center; gap: 7px; font-size: 11px; color: var(--page-muted, #9A8C7E); margin-top: 12px; }
.response-note::before { content:''; width:8px; height:8px; border-radius:50%; background:#4ade80; flex-shrink:0; animation: pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

@media (max-width:768px) {
    .contact-layout { grid-template-columns: 1fr; }
    .form-row       { grid-template-columns: 1fr; }
    .contact-hero h1 { font-size: 28px; }
}
</style>

<div class="contact-page">
    <div class="contact-hero">
        <div class="badge-pill">✦ Contactez-nous</div>
        <h1>Une question ?<br><em>Nous sommes là.</em></h1>
        <p>Notre équipe est disponible pour répondre à toutes vos questions concernant les emprunts, les commandes ou votre compte.</p>
    </div>

    <div class="contact-layout">
        <div class="info-panel">
            <div class="info-card">
                <div class="info-icon">📧</div>
                <div class="info-body">
                    <h4>Email</h4>
                    <p><a href="mailto:contact@auralib.dz">contact@auralib.dz</a></p>
                    <p style="margin-top:3px">Réponse sous 24h ouvrables</p>
                </div>
            </div>
            <div class="faq-card">
                <h4>Questions fréquentes</h4>
                <div class="faq-item">
                    <div class="faq-q">Combien de livres puis-je emprunter ?</div>
                    <div class="faq-a">Jusqu'à 3 documents simultanément pendant 14 jours.</div>
                </div>
                </div>
        </div>

        <div class="form-card">
            <h2>Envoyer un message</h2>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="contactForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nom complet <span class="req">*</span></label>
                        <input type="text" name="name" value="<?= htmlspecialchars($pre_name) ?>" placeholder="Votre nom" required>
                    </div>
                    <div class="form-group">
                        <label>Adresse email <span class="req">*</span></label>
                        <input type="email" name="email" value="<?= htmlspecialchars($pre_email) ?>" placeholder="votre@email.com" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Sujet</label>
                    <select name="subject">
                        <option value="Question générale">Question générale</option>
                        <option value="Problème emprunt">Problème avec un emprunt</option>
                        <option value="Problème commande">Problème avec une commande</option>
                        <option value="Problème compte">Problème de compte</option>
                        <option value="Signaler un bug">Signaler un bug</option>
                        <option value="Suggestion">Suggestion / Amélioration</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Message <span class="req">*</span></label>
                    <textarea name="message" id="msgArea" placeholder="Décrivez votre demande en détail..." required maxlength="500"></textarea>
                    <div class="char-count"><span id="charCount">0</span> / 500 caractères</div>
                </div>
                <button type="submit" name="send" class="btn-send">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Envoyer le message
                </button>
                <div class="response-note">Nous répondons généralement dans les 24 heures ouvrables.</div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var area = document.getElementById('msgArea');
    var cnt  = document.getElementById('charCount');
    if (area && cnt) {
        area.addEventListener('input', function() {
            var n = this.value.length;
            cnt.textContent = n;
            cnt.style.color = n > 450 ? '#f87171' : '';
        });
    }
});
</script>

<?php include "../includes/footer.php"; ?>