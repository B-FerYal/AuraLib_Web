<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once "../includes/db.php";
include_once "../includes/languages.php";

ini_set('display_errors', 0);
error_reporting(0);

$email       = "";
$err         = [];
$success_msg = "";

if (isset($_GET['signup']) && $_GET['signup'] === 'success') {
    $success_msg = $lang === 'ar'
        ? 'تم إنشاء حسابك بنجاح! يمكنك الآن تسجيل الدخول.'
        : ($lang === 'en' ? 'Account created! You can now log in.' : 'Compte créé ! Vous pouvez maintenant vous connecter.');
}

if (isset($_POST['login'])) {
    $email    = trim(mysqli_real_escape_string($conn, $_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $err[] = t('error_required');
    } else {
        $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' LIMIT 1");
        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                if (isset($user['status']) && $user['status'] === 'suspended') {
                    $err[] = $lang === 'ar'
                        ? 'حسابك موقوف. تواصل مع الإدارة.'
                        : ($lang === 'en' ? 'Your account is suspended.' : 'Votre compte est suspendu. Contactez l\'administration.');
                    $email = "";
                } else {
                    $_SESSION['id_user']   = $user['id'];
                    $_SESSION['role']      = $user['role'];
                    $_SESSION['firstname'] = $user['firstname'];
                    header("Location: ../auth/welcome.php");
                    exit;
                }
            } else {
                $err[] = t('error_login');
                $email = "";
            }
        } else {
            $err[] = t('error_login');
            $email = "";
        }
    }
}

$dir   = $lang === 'ar' ? 'rtl' : 'ltr';
$font  = $lang === 'ar' ? "'Tajawal'" : "'Plus Jakarta Sans'";
$align = $lang === 'ar' ? 'right' : 'left';
$pos_r = $lang === 'ar' ? 'left'  : 'right';
$pos_l = $lang === 'ar' ? 'right' : 'left';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= t('login_title') ?> — AuraLib</title>
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

/* Background image — light blur ~20% */
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

/* Card */
.card {
    position: relative; z-index: 10;
    width: 100%; max-width: 440px;
    background: rgba(255, 253, 249, .97);
    border-radius: 20px;
    padding: 46px 42px;
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
    border-<?= $lang === 'ar' ? 'right' : 'left' ?>: 3px solid;
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

/* Form */
.form-group { margin-bottom: 16px; }
.form-group label {
    display: block; font-size: 11px; font-weight: 700;
    letter-spacing: .8px; text-transform: uppercase;
    color: var(--muted); margin-bottom: 7px;
    text-align: <?= $align ?>;
}
html.dark .form-group label { color: rgba(196,164,107,.7); }
.input-wrap { position: relative; }
.form-group input {
    width: 100%; padding: 12px 15px;
    border: 1.5px solid var(--border);
    border-radius: 10px; font-size: 14px;
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

.input-has-eye { padding-<?= $pos_r ?>: 44px !important; }

.eye-btn {
    position: absolute;
    top: 50%; transform: translateY(-50%);
    <?= $pos_r ?>: 13px;
    background: none; border: none;
    cursor: pointer; color: var(--muted);
    font-size: 14px; line-height: 1; padding: 4px;
    transition: color .15s; z-index: 5;
}
.eye-btn:hover { color: var(--gold); }

.forgot {
    text-align: <?= $pos_r ?>;
    margin-top: 5px;
}
.forgot a {
    font-size: 11px; color: var(--muted);
    text-decoration: none; transition: color .15s;
}
.forgot a:hover { color: var(--gold); }

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

.bottom-link {
    text-align: center; margin-top: 20px;
    font-size: 13px; color: var(--muted);
}
html.dark .bottom-link { color: rgba(255,255,255,.4); }
.bottom-link a { color: var(--gold); font-weight: 700; text-decoration: none; }
.bottom-link a:hover { text-decoration: underline; }
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

    <div class="gold-line"><span><?= t('login_title') ?: 'Connexion' ?></span></div>

    <?php if ($success_msg): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-circle-check"></i>
        <?= htmlspecialchars($success_msg) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($err)): ?>
    <div class="alert alert-err">
        <i class="fa-solid fa-circle-exclamation"></i>
        <?= htmlspecialchars($err[0]) ?>
    </div>
    <?php endif; ?>

    <form action="login.php<?= $lang !== 'fr' ? '?lang='.$lang : '' ?>"
          method="POST" id="loginForm" autocomplete="off">

        <!-- Email -->
        <div class="form-group">
            <label><?= t('email') ?: 'Email' ?></label>
            <input type="email" name="email"
                   value="<?= htmlspecialchars($email) ?>"
                   placeholder="nom@exemple.com"
                   autocomplete="new-password" required>
        </div>
<!-- Password + eye toggle -->
        <div class="form-group">
            <label><?= t('password') ?: 'Mot de passe' ?></label>
            <div class="input-wrap">
                <input type="password" name="password" id="passLogin"
                       placeholder="••••••••"
                       class="input-has-eye"
                       autocomplete="new-password" required>
                <button type="button" class="eye-btn"
                        onclick="toggleEye('passLogin','eyeLogin')"
                        tabindex="-1" title="Afficher/masquer">
                    <i class="fa-regular fa-eye" id="eyeLogin"></i>
                </button>
            </div>
            <div class="forgot">
                <a href="forgot_password.php"><?= t('forgot_password') ?: 'Mot de passe oublié ?' ?></a>
            </div>
        </div>

        <button type="submit" name="login" class="btn-submit">
            <?= t('login_title') ?: 'Connexion' ?>
        </button>

        <div class="bottom-link">
            <?= t('no_account') ?: 'Pas de compte ?' ?>
            <a href="signup.php<?= $lang !== 'fr' ? '?lang='.$lang : '' ?>">
                <?= t('signup') ?: 'S\'inscrire' ?>
            </a>
        </div>
    </form>
</div>

<script>
window.onload = function () { document.getElementById('loginForm').reset(); };

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
</script>
</body>
</html>
