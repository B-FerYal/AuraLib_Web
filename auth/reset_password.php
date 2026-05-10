<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

ini_set('display_errors', 0);
error_reporting(0);

$errors = [];
$globalError = "";

$token = $_GET['token'] ?? '';
if (empty($token)) {
    $globalError = "Lien de réinitialisation invalide ou expiré.";
}

// التحقق من التوكن في قاعدة البيانات
$stmt = $conn->prepare("
    SELECT user_id, expires_at 
    FROM password_resets 
    WHERE token = ? 
    LIMIT 1
");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$reset = $result->fetch_assoc();

if (!$reset && empty($globalError)) {
    $globalError = "Lien invalide ou déjà utilisé.";
} elseif ($reset && strtotime($reset['expires_at']) < time()) {
    $globalError = "Le lien de réinitialisation a expiré.";
}

/* معالجة الفورم */
if (isset($_POST['reset']) && empty($globalError)) {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (empty($password) || empty($confirm)) {
        $errors[] = "Tous les champs sont obligatoires.";
    } elseif ($password !== $confirm) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // تحديث كلمة المرور (تأكدي أن اسم الحقل id كما في كودك)
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed, $reset['user_id']);
        $stmt->execute();

        // حذف التوكن بعد الاستخدام
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE token=?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        header("Location:../auth/login.php?reset=success");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe - BiblioUHBC</title>
    <link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<script>
    (function(){
        if(localStorage.getItem('auralib_theme')==='dark')
            document.documentElement.classList.add('dark');
    })();
</script>
    <style>
        body { background-color: #f4f1ea; margin: 0; padding: 0; font-family: 'Lato', sans-serif; }
        .box { max-width: 400px; margin: 80px auto; padding: 35px; background: #FFFDF9; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        .box h2 { color: #3D2E25; text-align: center; margin-bottom: 25px; text-transform: uppercase; font-size: 18px; letter-spacing: 1px; }
        
        .err { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; border: 1px solid #f5c6cb; text-align: center; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; color: #5C4A3A; font-weight: 600; font-size: 14px; }

        input[type="password"] { 
            width: 100%; padding: 12px; border: 1px solid #d1c7bc; border-radius: 6px; 
            box-sizing: border-box; background: #fff; font-size: 14px; margin-bottom: 10px;
        }
        input:focus { border-color: #C4A46B; outline: none; box-shadow: 0 0 5px rgba(196, 164, 107, 0.2); }

        button { 
            width: 100%; padding: 14px; background: #C4A46B; border: none; color: #FFF; 
            font-weight: 700; cursor: pointer; border-radius: 6px; font-size: 14px; 
            text-transform: uppercase; transition: 0.3s; margin-top: 10px;
        }
        button:hover { background: #3D2E25; }
        
        .back-to-login { text-align: center; margin-top: 20px; font-size: 13px; }
        .back-to-login a { color: #C4A46B; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="box">
    <h2>Nouveau mot de passe</h2>

    <?php if (!empty($globalError)): ?>
        <div class="err"><?= htmlspecialchars($globalError) ?></div>
        <div class="back-to-login"><a href="login.php">Retour à la connexion</a></div>
    <?php else: ?>

        <?php if (!empty($errors)): ?>
            <div class="err"><?= htmlspecialchars($errors[0]) ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" name="password" placeholder="••••••••" required autocomplete="new-password">
            </div>

            <div class="form-group">
                <label>Confirmer le mot de passe</label>
                <input type="password" name="confirm" placeholder="••••••••" required autocomplete="new-password">
            </div>

            <button type="submit" name="reset">Enregistrer</button>
        </form>
    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>