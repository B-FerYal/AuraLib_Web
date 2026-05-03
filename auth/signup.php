<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once  "../includes/db.php";
include_once  "../includes/languages.php";

$lang = $lang ?? 'fr';
$dir  = ($lang === 'ar') ? 'rtl' : 'ltr';
$font = ($lang === 'ar') ? "'Tajawal'" : "'Plus Jakarta Sans'";
$align       = ($lang === 'ar') ? 'right' : 'left';
$align_rev   = ($lang === 'ar') ? 'left'  : 'right';
$border_side = ($lang === 'ar') ? 'right' : 'left';
$pos_r       = ($lang === 'ar') ? 'left'  : 'right';
$pos_l       = ($lang === 'ar') ? 'right' : 'left';

$fname = $lname = $email = $Gender = $phone = "";
$err     = [];
$success = false;

if (isset($_POST['signup'])) {
    if (!isset($_POST['csrf_token'])  $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $err[] = "Requête invalide. Veuillez réessayer.";
    }

    $Gender = isset($_POST['Gender']) ? mysqli_real_escape_string($conn, $_POST['Gender']) : '';
    $fname  = isset($_POST['fname'])  ? trim(mysqli_real_escape_string($conn, $_POST['fname'])) : '';
    $lname  = isset($_POST['lname'])  ? trim(mysqli_real_escape_string($conn, $_POST['lname'])) : '';
    $email  = isset($_POST['email'])  ? trim(mysqli_real_escape_string($conn, $_POST['email'])) : '';
    $phone  = isset($_POST['phone'])  ? trim(mysqli_real_escape_string($conn, $_POST['phone'])) : '';
    $pass1  = $_POST['pass1'] ?? '';
    $pass2  = $_POST['pass2'] ?? '';

    if (empty($fname)||empty($lname)||empty($email)||empty($Gender)||empty($phone)||empty($pass1)||empty($pass2)) {
        $err[] = t('error_required');
    }
    if (empty($err) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err[] = t('error_email');
    }
    if (empty($err) && !preg_match('/^[0-9]{10}$/', $phone)) {
        $err[] = "Numéro de téléphone invalide (10 chiffres).";
    }
    if (empty($err) && $pass1 !== $pass2) {
        $err[] = t('error_match');
    }
    if (empty($err) && (strlen($pass1) < 8  !preg_match('/[A-Z]/', $pass1) || !preg_match('/[0-9]/', $pass1))) {
        $err[] = "Mot de passe : min 8 caractères, 1 majuscule, 1 chiffre.";
    }
    if (empty($err)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $err[] = t('error_email_taken');
        }
        $stmt->close();
    }
    if (empty($err)) {
        $hash = password_hash($pass1, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, Gender, email, phone, password, role) VALUES (?, ?, ?, ?, ?, ?, 'client')");
        $stmt->bind_param("ssssss", $fname, $lname, $Gender, $email, $phone, $hash);
        if ($stmt->execute()) {
            unset($_SESSION['csrf_token']);
            header("Location: login.php?signup=success");
            exit;
        } else {
            $err[] = "Échec de l'inscription : " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= t('signup_title') ?> — AuraLib</title>
<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script>(function(){if(localStorage.getItem('auralib_theme')==='dark')document.documentElement.classList.add('dark')})();</script>
<link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<style>
:root {
    --gold:   #C4A46B;
    --gold2:  #D4B47B;
    --taupe:  #2C1F0E;
    --cream:  #F5F0E8;
    --white:  #FFFDF9;
    --border: #EDE5D4;
    --muted:  #9A8C7E;
    --red:    #e74c3c;
    --green:  #2E7D52;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html, body { height: 100%; }
body {
    font-family: <?= $font ?>, sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    position: relative;
    overflow: hidden;
    background: #1a1208;
}

/* Background image — same as login, light blur */
body::before {
    content: '';
    position: fixed;
    inset: -20px;
    background-image: url('../assets/images/log.jpg');
    background-size: cover;
    background-position: center;
    filter: blur(4px) brightness(.58);
    transform: scale(1.05);
    z-index: 0;
}

/* Subtle dark overlay */
body::after {
    content: '';
    position: fixed;
    inset: 0;
    background: rgba(44, 31, 14, .20);
    z-index: 1;
}

/* Language bar */
.lang-bar {
    position: fixed;
    top: 16px;
    <?= $pos_r ?>: 20px;
    display: flex; gap: 5px;
    z-index: 100;
}
.lang-bar a {
    font-size: 11px; font-weight: 600;
    padding: 5px 11px; border-radius: 20px;
    border: 1px solid rgba(255,255,255,.2);
    background: rgba(255,255,255,.1);
    color: rgba(255,255,255,.85);
    text-decoration: none; backdrop-filter: blur(6px);
    transition: background .15s;
}
.lang-bar a.active,
.lang-bar a:hover { background: var(--gold); color: var(--taupe); border-color: var(--gold); }

/* Back button */
.btn-back {
    position: fixed;
    top: 16px;
    <?= $pos_l ?>: 20px;
    font-size: 12px; font-weight: 600;
    padding: 6px 14px; border-radius: 20px;
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.2);
    color: rgba(255,255,255,.85);
    text-decoration: none; backdrop-filter: blur(6px);
    z-index: 100; transition: background .15s;
}
.btn-back:hover { background: rgba(255,255,255,.22); }

/* Card — same as login */
.card {
    position: relative; z-index: 10;
    width: 100%; max-width: 480px;
    background: rgba(255, 253, 249, .97);
    border-radius: 20px;
    padding: 42px 42px;
    box-shadow: 0 24px 64px rgba(0,0,0,.35), 0 0 0 1px rgba(255,255,255,.2);
    animation: fadeUp .45s cubic-bezier(.16,1,.3,1);
}
html.dark .card {
    background: rgba(30, 22, 12, .97);
    box-shadow: 0 24px 64px rgba(0,0,0,.6), 0 0 0 1px rgba(196,164,107,.15);
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px) scale(.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* Logo */
.logo {
    text-align: center;
    font-family: 'EB Garamond', serif;
    font-size: 30px; font-weight: 700;
    color: var(--taupe); letter-spacing: -.3px;
    margin-bottom: 4px;
}
html.dark .logo { color: #F5EDD6; }
.logo em { color: var(--gold); font-style: normal; }
.logo-sub {
    text-align: center; font-size: 10px; font-weight: 600;
    letter-spacing: 2.5px; text-transform: uppercase;
    color: var(--muted); margin-bottom: 30px;
}

/* Gold divider */
.gold-line {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 22px;
}
.gold-line::before, .gold-line::after {
    content: ''; flex: 1; height: 1px; background: var(--border);
}
html.dark .gold-line::before,
html.dark .gold-line::after { background: rgba(196,164,107,.2); }
.gold-line span {
    font-size: 10px; letter-spacing: 2px; text-transform: uppercase;
    color: var(--gold); font-weight: 600;
}

/* Alerts */
.alert {
    padding: 12px 14px; border-radius: 10px;
    font-size: 13px; margin-bottom: 18px;
    display: flex; align-items: flex-start; gap: 9px;
    border-<?= $border_side ?>: 3px solid;
    animation: slideIn .3s ease;
}
@keyframes slideIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.alert-err     { background: #fff1f0; border-color: #e74c3c; color: #b03020; }
.alert-success { background: #f0fdf4; border-color: #4ade80; color: #15803d; }
html.dark .alert-err     { background: rgba(231,76,60,.12); color: #ff9a8e; }
html.dark .alert-success { background: rgba(74,222,128,.1); color: #86efac; }
.alert i { margin-top: 1px; flex-shrink: 0; }
/* Form rows */
.form-row   { display: flex; gap: 14px; }
.form-group { margin-bottom: 16px; flex: 1; }

.form-group label {
    display: block; font-size: 11px; font-weight: 700;
    letter-spacing: .8px; text-transform: uppercase;
    color: var(--muted); margin-bottom: 7px;
    text-align: <?= $align ?>;
}
html.dark .form-group label { color: rgba(196,164,107,.7); }
.required { color: var(--red); margin-<?= $align_rev ?>: 2px; }

.input-wrap { position: relative; }
.form-group input {
    width: 100%; padding: 12px 15px;
    border: 1.5px solid var(--border);
    border-radius: 10px; font-size: 13px;
    font-family: inherit;
    background: #FAFAF7;
    color: var(--taupe);
    transition: border-color .2s, box-shadow .2s, background .2s;
    outline: none;
    text-align: <?= $align ?>;
}
html.dark .form-group input {
    background: rgba(255,255,255,.06);
    border-color: rgba(196,164,107,.2);
    color: #F5EDD6;
}
.form-group input:focus {
    border-color: var(--gold);
    background: #fff;
    box-shadow: 0 0 0 4px rgba(196,164,107,.12);
}
html.dark .form-group input:focus {
    background: rgba(196,164,107,.06);
    box-shadow: 0 0 0 4px rgba(196,164,107,.1);
}
.form-group input::placeholder { color: #c0b8a8; font-size: 13px; }
html.dark .form-group input::placeholder { color: rgba(196,164,107,.3); }

.has-eye { padding-<?= $align_rev ?>: 44px !important; }
.eye-btn {
    position: absolute;
    top: 50%; transform: translateY(-50%);
    <?= $align_rev ?>: 13px;
    background: none; border: none;
    cursor: pointer; color: var(--muted);
    font-size: 14px; line-height: 1; padding: 4px;
    transition: color .15s; z-index: 5;
}
.eye-btn:hover { color: var(--gold); }

/* Gender radios */
.gender-row {
    display: flex; gap: 10px; margin-top: 7px;
}
.gender-option {
    display: flex; align-items: center; justify-content: center;
    gap: 7px; padding: 10px 16px;
    border: 1.5px solid var(--border);
    border-radius: 10px; cursor: pointer;
    transition: .2s; font-size: 13px;
    color: var(--taupe); flex: 1;
}
html.dark .gender-option { border-color: rgba(196,164,107,.2); color: #F5EDD6; }
.gender-option:has(input:checked) {
    border-color: var(--gold);
    background: rgba(196,164,107,.08);
    color: var(--gold);
}
.gender-option input { display: none; }

/* Security divider */
.sec-divider {
    display: flex; align-items: center; gap: 10px;
    margin: 4px 0 16px;
}
.sec-divider::before, .sec-divider::after {
    content: ''; flex: 1; height: 1px; background: var(--border);
}
html.dark .sec-divider::before,
html.dark .sec-divider::after { background: rgba(196,164,107,.2); }
.sec-divider span {
    font-size: 10px; letter-spacing: 2px; text-transform: uppercase;
    color: var(--gold); font-weight: 600;
}

/* Password strength */
.strength-bar {
    height: 3px; border-radius: 2px; margin-top: 6px;
    background: var(--border); overflow: hidden;
}
html.dark .strength-bar { background: rgba(196,164,107,.15); }
.strength-fill {
    height: 100%; border-radius: 2px; width: 0%;
    transition: width .3s, background .3s;
}
.strength-hint {
    font-size: 10px; margin-top: 4px;
    color: var(--muted); text-align: <?= $align ?>;
}

/* Submit */
.btn-submit {
    width: 100%; padding: 14px;
    background: var(--gold); color: var(--taupe);
    border: none; border-radius: 11px;
    font-family: inherit; font-size: 14px; font-weight: 700;
    cursor: pointer; letter-spacing: .3px;
    transition: background .2s, transform .15s;
    margin-top: 4px;
}
.btn-submit:hover { background: var(--gold2); transform: translateY(-2px); }
.btn-submit:active { transform: translateY(0); }

/* Bottom link */
.bottom-link {
    text-align: center; margin-top: 20px;
    font-size: 13px; color: var(--muted);
}
html.dark .bottom-link { color: rgba(255,255,255,.4); }
.bottom-link a { color: var(--gold); font-weight: 700; text-decoration: none; }
.bottom-link a:hover { text-decoration: underline; }
/* Responsive */
@media (max-width: 500px) {
    .card { padding: 32px 22px; }
    .form-row { flex-direction: column; gap: 0; }
}
</style>
</head>
<body>

<a href="../client/library.php" class="btn-back">
    <?= $lang === 'ar' ? '→ رجوع' : '← ' . (t('back') ?: 'Retour') ?>
</a>

<div class="lang-bar">
    <a href="?lang=fr" class="<?= $lang==='fr'?'active':'' ?>">🇫🇷 FR</a>
    <a href="?lang=en" class="<?= $lang==='en'?'active':'' ?>">🇬🇧 EN</a>
    <a href="?lang=ar" class="<?= $lang==='ar'?'active':'' ?>">🇩🇿 AR</a>
</div>

<div class="card">

    <div class="logo">Aura<em>Lib</em></div>
    <div class="logo-sub">Library</div>

    <div class="gold-line">
        <span><?= t('signup_title') ?: 'Inscription' ?></span>
    </div>

    <?php if (!empty($err)): ?>
    <div class="alert alert-err">
        <i class="fa-solid fa-circle-exclamation"></i>
        <?= htmlspecialchars($err[0]) ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['signup']) && $_GET['signup'] === 'success'): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-circle-check"></i>
        <?= $lang === 'ar' ? 'تم إنشاء حسابك بنجاح!' : ($lang === 'en' ? 'Account created successfully!' : 'Compte créé avec succès !') ?>
    </div>
    <?php endif; ?>

    <form id="signupForm" action="signup.php<?= $lang !== 'fr' ? '?lang='.$lang : '' ?>" method="POST" novalidate autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <!-- Name row -->
        <div class="form-row">
            <div class="form-group">
                <label><?= t('firstname') ?> <span class="required">*</span></label>
                <input type="text" name="fname"
                       placeholder="<?= $lang === 'ar' ? 'الاسم' : 'Jean' ?>"
                       value="<?= htmlspecialchars($fname) ?>">
            </div>
            <div class="form-group">
                <label><?= t('lastname') ?> <span class="required">*</span></label>
                <input type="text" name="lname"
                       placeholder="<?= $lang === 'ar' ? 'اللقب' : 'Dupont' ?>"
                       value="<?= htmlspecialchars($lname) ?>">
            </div>
        </div>

        <!-- Email -->
        <div class="form-group">
            <label><?= t('email') ?> <span class="required">*</span></label>
            <input type="email" name="email"
                   placeholder="nom@exemple.com"
                   value="<?= htmlspecialchars($email) ?>"
                   autocomplete="new-password">
        </div>

        <!-- Phone -->
        <div class="form-group">
            <label><?= t('phone') ?> <span class="required">*</span></label>
            <input type="text" name="phone"
                   placeholder="05XXXXXXXX"
                   value="<?= htmlspecialchars($phone) ?>">
        </div>

        <!-- Gender -->
        <div class="form-group">
            <label><?= t('gender') ?> <span class="required">*</span></label>
            <div class="gender-row">
                <label class="gender-option">
                    <input type="radio" name="Gender" value="Male" <?= $Gender==='Male'?'checked':'' ?>>
                    <i class="fa fa-mars"></i> <?= t('male') ?>
                </label>
                <label class="gender-option">
                    <input type="radio" name="Gender" value="Female" <?= $Gender==='Female'?'checked':'' ?>>
                    <i class="fa fa-venus"></i> <?= t('female') ?>
                </label>
            </div>
        </div>

        <!-- Security divider -->
        <div class="sec-divider">
            <span><?= $lang === 'ar' ? 'كلمة المرور' : ($lang === 'en' ? 'Security' : 'Sécurité') ?></span>
        </div>
<!-- Passwords row -->
        <div class="form-row">
            <div class="form-group">
                <label><?= t('password') ?> <span class="required">*</span></label>
                <div class="input-wrap">
                    <input type="password" name="pass1" id="pass1"
                           placeholder="••••••••"
                           class="has-eye"
                           autocomplete="new-password"
                           oninput="checkStrength(this.value)">
                    <button type="button" class="eye-btn"
                            onclick="toggleEye('pass1','eye1')" tabindex="-1">
                        <i class="fa-regular fa-eye" id="eye1"></i>
                    </button>
                </div>
                <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                <div class="strength-hint" id="strengthHint"></div>
            </div>
            <div class="form-group">
                <label><?= t('confirm_password') ?> <span class="required">*</span></label>
                <div class="input-wrap">
                    <input type="password" name="pass2" id="pass2"
                           placeholder="••••••••"
                           class="has-eye"
                           autocomplete="new-password">
                    <button type="button" class="eye-btn"
                            onclick="toggleEye('pass2','eye2')" tabindex="-1">
                        <i class="fa-regular fa-eye" id="eye2"></i>
                    </button>
                </div>
            </div>
        </div>

        <button type="submit" name="signup" class="btn-submit">
            <?= t('signup_title') ?: "S'inscrire" ?> &nbsp;→
        </button>

        <div class="bottom-link">
            <?= t('already_account') ?: 'Déjà un compte ?' ?>
            <a href="login.php<?= $lang !== 'fr' ? '?lang='.$lang : '' ?>">
                <?= t('login') ?: 'Se connecter' ?>
            </a>
        </div>
    </form>
</div>

<script>
window.onload = function () { document.getElementById('signupForm').reset(); };

function toggleEye(inputId, iconId) {
    var input = document.getElementById(inputId);
    var icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-regular fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa-regular fa-eye';
    }
}

function checkStrength(val) {
    var fill = document.getElementById('strengthFill');
    var hint = document.getElementById('strengthHint');
    var score = 0;
    if (val.length >= 8)              score++;
    if (/[A-Z]/.test(val))            score++;
    if (/[0-9]/.test(val))            score++;
    if (/[^A-Za-z0-9]/.test(val))     score++;

    var levels = [
        { w: '0%',   bg: 'transparent', txt: '' },
        { w: '25%',  bg: '#e74c3c',     txt: '<?= $lang==="ar"?"ضعيف":($lang==="en"?"Weak":"Faible") ?>' },
        { w: '50%',  bg: '#e67e22',     txt: '<?= $lang==="ar"?"مقبول":($lang==="en"?"Fair":"Acceptable") ?>' },
        { w: '75%',  bg: '#f1c40f',     txt: '<?= $lang==="ar"?"جيد":($lang==="en"?"Good":"Bon") ?>' },
        { w: '100%', bg: '#2E7D52',     txt: '<?= $lang==="ar"?"ممتاز":($lang==="en"?"Strong":"Fort") ?>' },
    ];

    if (val.length === 0) { fill.style.width = '0%'; hint.textContent = ''; return; }
    fill.style.width      = levels[score].w;
    fill.style.background = levels[score].bg;
    hint.textContent      = levels[score].txt;
    hint.style.color      = levels[score].bg;
}
</script>
</body>
</html>
