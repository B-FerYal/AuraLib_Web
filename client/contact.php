<?php
include "../includes/header.php";
// $lang متاح تلقائياً من header.php

// ── نصوص الصفحة حسب اللغة ──────────────────────────────
$pg = [
    'fr' => [
        'page_title'     => 'Contact — AuraLib',
        'badge'          => '✦ Contactez-nous',
        'hero_h1'        => 'Une question ?<br><em>Nous sommes là.</em>',
        'hero_p'         => 'Notre équipe est disponible pour répondre à toutes vos questions concernant les emprunts, les commandes ou votre compte.',
        'email_label'    => 'Email',
        'email_reply'    => 'Réponse sous 24h ouvrables',
        'faq_title'      => 'Questions fréquentes',
        'faq1_q'         => 'Combien de livres puis-je emprunter ?',
        'faq1_a'         => 'Jusqu\'à 3 documents simultanément pendant 14 jours.',
        'form_title'     => 'Envoyer un message',
        'lbl_name'       => 'Nom complet',
        'lbl_email'      => 'Adresse email',
        'ph_name'        => 'Votre nom',
        'ph_email'       => 'votre@email.com',
        'lbl_subject'    => 'Sujet',
        'lbl_message'    => 'Message',
        'ph_message'     => 'Décrivez votre demande en détail...',
        'chars'          => 'caractères',
        'btn_send'       => 'Envoyer le message',
        'response_note'  => 'Nous répondons généralement dans les 24 heures ouvrables.',
        'subjects' => [
            'Question générale'   => 'Question générale',
            'Problème emprunt'    => 'Problème avec un emprunt',
            'Problème commande'   => 'Problème avec une commande',
            'Problème compte'     => 'Problème de compte',
            'Signaler un bug'     => 'Signaler un bug',
            'Suggestion'          => 'Suggestion / Amélioration',
            'Autre'               => 'Autre',
        ],
        'err_required'   => 'Veuillez remplir tous les champs obligatoires.',
        'err_email'      => 'L\'adresse email n\'est pas valide.',
        'err_send'       => 'Une erreur est survenue lors de l\'envoi. Veuillez réessayer.',
        'success'        => 'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.',
    ],
    'en' => [
        'page_title'     => 'Contact — AuraLib',
        'badge'          => '✦ Contact us',
        'hero_h1'        => 'Have a question?<br><em>We\'re here.</em>',
        'hero_p'         => 'Our team is available to answer all your questions about loans, orders or your account.',
        'email_label'    => 'Email',
        'email_reply'    => 'Response within 24 working hours',
        'faq_title'      => 'Frequently asked questions',
        'faq1_q'         => 'How many books can I borrow?',
        'faq1_a'         => 'Up to 3 documents simultaneously for 14 days.',
        'form_title'     => 'Send a message',
        'lbl_name'       => 'Full name',
        'lbl_email'      => 'Email address',
        'ph_name'        => 'Your name',
        'ph_email'       => 'your@email.com',
        'lbl_subject'    => 'Subject',
        'lbl_message'    => 'Message',
        'ph_message'     => 'Describe your request in detail...',
        'chars'          => 'characters',
        'btn_send'       => 'Send message',
        'response_note'  => 'We usually reply within 24 working hours.',
        'subjects' => [
            'Question générale'   => 'General question',
            'Problème emprunt'    => 'Problem with a loan',
            'Problème commande'   => 'Problem with an order',
            'Problème compte'     => 'Account issue',
            'Signaler un bug'     => 'Report a bug',
            'Suggestion'          => 'Suggestion / Improvement',
            'Autre'               => 'Other',
        ],
        'err_required'   => 'Please fill in all required fields.',
        'err_email'      => 'The email address is not valid.',
        'err_send'       => 'An error occurred while sending. Please try again.',
        'success'        => 'Your message has been sent. We will get back to you as soon as possible.',
    ],
    'ar' => [
        'page_title'     => 'اتصل بنا — AuraLib',
        'badge'          => '✦ اتصل بنا',
        'hero_h1'        => 'لديك سؤال؟<br><em>نحن هنا.</em>',
        'hero_p'         => 'فريقنا متاح للإجابة على جميع أسئلتك المتعلقة بالاستعارات أو الطلبات أو حسابك.',
        'email_label'    => 'البريد الإلكتروني',
        'email_reply'    => 'رد خلال 24 ساعة عمل',
        'faq_title'      => 'أسئلة شائعة',
        'faq1_q'         => 'كم كتاباً يمكنني استعارته؟',
        'faq1_a'         => 'حتى 3 وثائق في نفس الوقت لمدة 14 يوماً.',
        'form_title'     => 'أرسل رسالة',
        'lbl_name'       => 'الاسم الكامل',
        'lbl_email'      => 'البريد الإلكتروني',
        'ph_name'        => 'اسمك',
        'ph_email'       => 'بريدك@example.com',
        'lbl_subject'    => 'الموضوع',
        'lbl_message'    => 'الرسالة',
        'ph_message'     => 'اشرح طلبك بالتفصيل...',
        'chars'          => 'حرف',
        'btn_send'       => 'إرسال الرسالة',
        'response_note'  => 'نرد عادةً خلال 24 ساعة عمل.',
        'subjects' => [
            'Question générale'   => 'سؤال عام',
            'Problème emprunt'    => 'مشكلة في استعارة',
            'Problème commande'   => 'مشكلة في طلب',
            'Problème compte'     => 'مشكلة في الحساب',
            'Signaler un bug'     => 'الإبلاغ عن خطأ',
            'Suggestion'          => 'اقتراح / تحسين',
            'Autre'               => 'أخرى',
        ],
        'err_required'   => 'يرجى ملء جميع الحقول المطلوبة.',
        'err_email'      => 'عنوان البريد الإلكتروني غير صالح.',
        'err_send'       => 'حدث خطأ أثناء الإرسال. يرجى المحاولة مجدداً.',
        'success'        => 'تم إرسال رسالتك بنجاح. سنرد عليك في أقرب وقت ممكن.',
    ],
];

$p     = $pg[$lang] ?? $pg['fr'];
$isRtl = ($lang === 'ar');

// ── الإعداد المسبق إذا كان مسجلاً ────────────────────────
$pre_name  = '';
$pre_email = '';
if (isset($is_logged_in) && $is_logged_in && !empty($user)) {
    $pre_name  = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
    $pre_email = $user['email'] ?? '';
}

// ── معالجة الفورم ─────────────────────────────────────────
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $subject = trim($_POST['subject'] ?? 'Question générale');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = $p['err_required'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = $p['err_email'];
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
            $success  = $p['success'];
            $pre_name = $pre_email = $subject = $message = '';
        } else {
            $error = $p['err_send'];
        }
    }
}
?>
<title><?= $p['page_title'] ?></title>

<style>
.contact-page {
    max-width: 1000px;
    margin: 0 auto;
    padding: 60px 20px 80px;
    direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
    font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "inherit" ?>;
}

.contact-hero { text-align: center; margin-bottom: 52px; }
.badge-pill {
    display: inline-block;
    background: rgba(196,164,107,.12); border: 1px solid rgba(196,164,107,.3);
    color: #C4A46B; font-size: 11px; font-weight: 700;
    letter-spacing: <?= $isRtl ? '0' : '2px' ?>;
    text-transform: uppercase; padding: 6px 18px;
    border-radius: 30px; margin-bottom: 20px;
    font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "inherit" ?>;
}
.contact-hero h1 {
    font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "'Cormorant Garamond', serif" ?>;
    font-size: <?= $isRtl ? '32px' : '40px' ?>;
    color: var(--page-text, #2C1F0E);
    margin-bottom: 14px; line-height: 1.3;
}
.contact-hero h1 em { color: #C4A46B; font-style: <?= $isRtl ? 'normal' : 'normal' ?>; }
.contact-hero p {
    font-size: 15px; color: var(--page-muted, #9A8C7E);
    max-width: 520px; margin: 0 auto; line-height: 1.75;
}

.contact-layout { display: grid; grid-template-columns: 1fr 1.6fr; gap: 28px; align-items: start; }

/* Info cards */
.info-panel { display: flex; flex-direction: column; gap: 12px; }
.info-card {
    background: var(--page-white, #FFFDF9); border: 1px solid var(--page-border, #DDD5C8);
    border-radius: 12px; padding: 18px;
    display: flex; align-items: flex-start; gap: 13px;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
    text-align: <?= $isRtl ? 'right' : 'left' ?>;
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
    text-align: <?= $isRtl ? 'right' : 'left' ?>;
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
    text-align: <?= $isRtl ? 'right' : 'left' ?>;
}
.form-card h2 {
    font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "'Cormorant Garamond', serif" ?>;
    font-size: <?= $isRtl ? '22px' : '21px' ?>;
    color: var(--page-text, #2C1F0E); margin-bottom: 22px;
    padding-bottom: 14px; border-bottom: 1px solid var(--page-border, #DDD5C8);
}
.form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 13px; }
.form-group { margin-bottom: 16px; }
.form-group label {
    display: block; font-size: 12px; font-weight: 700;
    color: var(--page-text, #2C1F0E); margin-bottom: 6px;
}
.req { color: #e74c3c; margin-<?= $isRtl ? 'right' : 'left' ?>: 2px; }
.form-group input,
.form-group select,
.form-group textarea {
    width: 100%; padding: 10px 13px;
    border: 1.5px solid var(--page-border, #DDD5C8); border-radius: 8px;
    font-size: 13px; font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "inherit" ?>;
    background: var(--page-bg, #F5F0E8); color: var(--page-text, #2C1F0E);
    transition: border-color .15s, background .15s; outline: none;
    text-align: <?= $isRtl ? 'right' : 'left' ?>;
    direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #C4A46B; background: white;
    box-shadow: 0 4px 14px rgba(196,164,107,.12);
}
.form-group textarea { resize: vertical; min-height: 130px; line-height: 1.6; }
.char-count {
    font-size: 10px; color: var(--page-muted, #9A8C7E);
    text-align: <?= $isRtl ? 'left' : 'right' ?>;
    margin-top: 3px;
}

.btn-send {
    width: 100%; padding: 13px; background: #C4A46B; color: #2C1F0E;
    border: none; border-radius: 10px; font-size: 14px; font-weight: 700;
    cursor: pointer;
    font-family: <?= $isRtl ? "'Tajawal', sans-serif" : "inherit" ?>;
    transition: background .2s, transform .15s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.btn-send:hover { background: #D4B47B; transform: translateY(-2px); }
.btn-send svg { width: 15px; height: 15px; }

.alert {
    padding: 12px 15px; border-radius: 9px; font-size: 13px;
    margin-bottom: 18px;
    display: flex; align-items: center; gap: 8px;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
    border-<?= $isRtl ? 'right' : 'left' ?>: 3px solid;
    text-align: <?= $isRtl ? 'right' : 'left' ?>;
}
.alert svg { width: 14px; height: 14px; flex-shrink: 0; }
.alert-success { background: #f0fdf4; border-color: #4ade80; color: #15803d; }
.alert-error   { background: #fef2f2; border-color: #f87171; color: #dc2626; }

.response-note {
    display: flex; align-items: center; gap: 7px;
    font-size: 11px; color: var(--page-muted, #9A8C7E); margin-top: 12px;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
    justify-content: <?= $isRtl ? 'flex-end' : 'flex-start' ?>;
}
.response-note::before {
    content:''; width:8px; height:8px; border-radius:50%;
    background:#4ade80; flex-shrink:0; animation: rpulse 2s infinite;
}
@keyframes rpulse { 0%,100%{opacity:1} 50%{opacity:.4} }

@media (max-width:768px) {
    .contact-layout { grid-template-columns: 1fr; }
    .form-row       { grid-template-columns: 1fr; }
    .contact-hero h1 { font-size: 26px; }
}
</style>

<div class="contact-page">

    <!-- Hero -->
    <div class="contact-hero">
        <div class="badge-pill"><?= $p['badge'] ?></div>
        <h1><?= $p['hero_h1'] ?></h1>
        <p><?= $p['hero_p'] ?></p>
    </div>

    <div class="contact-layout">

        <!-- Info panel -->
        <div class="info-panel">
            <div class="info-card">
                <div class="info-icon">📧</div>
                <div class="info-body">
                    <h4><?= $p['email_label'] ?></h4>
                    <p><a href="mailto:contact@auralib.dz">contact@auralib.dz</a></p>
                    <p style="margin-top:3px"><?= $p['email_reply'] ?></p>
                </div>
            </div>

            <div class="faq-card">
                <h4><?= $p['faq_title'] ?></h4>
                <div class="faq-item">
                    <div class="faq-q"><?= $p['faq1_q'] ?></div>
                    <div class="faq-a"><?= $p['faq1_a'] ?></div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="form-card">
            <h2><?= $p['form_title'] ?></h2>

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
                        <label><?= $p['lbl_name'] ?> <span class="req">*</span></label>
                        <input type="text" name="name"
                               value="<?= htmlspecialchars($pre_name) ?>"
                               placeholder="<?= $p['ph_name'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label><?= $p['lbl_email'] ?> <span class="req">*</span></label>
                        <input type="email" name="email"
                               value="<?= htmlspecialchars($pre_email) ?>"
                               placeholder="<?= $p['ph_email'] ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><?= $p['lbl_subject'] ?></label>
                    <select name="subject">
                        <?php foreach ($p['subjects'] as $val => $label): ?>
                        <option value="<?= htmlspecialchars($val) ?>"
                            <?= (isset($subject) && $subject === $val) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= $p['lbl_message'] ?> <span class="req">*</span></label>
                    <textarea name="message" id="msgArea"
                              placeholder="<?= $p['ph_message'] ?>"
                              required maxlength="500"><?= htmlspecialchars($message ?? '') ?></textarea>
                    <div class="char-count">
                        <span id="charCount">0</span> / 500 <?= $p['chars'] ?>
                    </div>
                </div>

                <button type="submit" name="send" class="btn-send">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                    <?= $p['btn_send'] ?>
                </button>

                <div class="response-note"><?= $p['response_note'] ?></div>
            </form>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var area = document.getElementById('msgArea');
    var cnt  = document.getElementById('charCount');
    if (area && cnt) {
        // تحديث العداد عند التحميل إذا كان فيه نص
        cnt.textContent = area.value.length;
        area.addEventListener('input', function () {
            var n = this.value.length;
            cnt.textContent = n;
            cnt.style.color = n > 450 ? '#f87171' : '';
        });
    }
});
</script>

<?php include "../includes/footer.php"; ?>