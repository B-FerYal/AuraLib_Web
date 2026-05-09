<?php
session_start();
require_once "../includes/db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

ini_set('display_errors', 0);
error_reporting(0);

$err = [];
$success = "";

if (isset($_POST['submit'])) {

    $email = trim($_POST['email'] ?? '');

    // Validation des champs
    if (empty($email)) {
        $err[] = "L'adresse email est requise.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err[] = "Format d'email invalide.";
    }

    if (empty($err)) {
        try {
            // ملاحظة: تأكد أن اسم الحقل في جدولك هو 'id' كما في كودك الأصلي
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            if (!$stmt) {
                throw new Exception("Erreur DB");
            }

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($user = $result->fetch_assoc()) {

                $token = bin2hex(random_bytes(32));
                $expires = date("Y-m-d H:i:s", time() + 1800);

                $stmt = $conn->prepare("
                    INSERT INTO password_resets (user_id, token, expires_at)
                    VALUES (?, ?, ?)
                ");
                if (!$stmt) {
                    throw new Exception("Erreur DB");
                }

                $stmt->bind_param("iss", $user['id'], $token, $expires);
                $stmt->execute();

                $resetLink = "http://localhost/memoir/auth/reset_password.php?token=$token";

                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'no.reply.biblioo@gmail.com';
                    $mail->Password = 'vvtstiybjfxnoamk';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('no.reply.biblioo@gmail.com', 'BiblioUHBC');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'Reinitialisation de mot de passe';
                    $mail->Body = "
                        <p>Cliquez sur le lien ci-dessous pour réinitialiser votre mot de passe :</p>
                        <a href='$resetLink'>$resetLink</a>
                    ";

                    $mail->send();

                } catch (Exception $e) {
                    // Erreur silencieuse pour l'utilisateur
                }
            }

            // الرسالة تظهر دائماً للأمان
            $success = "Si l'email existe, un lien de réinitialisation a été envoyé.";

        } catch (Exception $e) {
            $err[] = "Quelque chose s'est mal passé. Veuillez réessayer plus tard.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - BiblioUHBC</title>
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
        .box h2 { color: #3D2E25; text-align: center; margin-bottom: 25px; text-transform: uppercase; font-size: 20px; }
        
        .err { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; border: 1px solid #f5c6cb; text-align: center; }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; border: 1px solid #c3e6cb; text-align: center; }
        
        input[type="email"] { 
            width: 100%; padding: 12px; border: 1px solid #d1c7bc; border-radius: 6px; 
            box-sizing: border-box; background: #fff; font-size: 14px; margin-bottom: 15px;
        }
        input:focus { border-color: #C4A46B; outline: none; }

        button { 
            width: 100%; padding: 14px; background: #C4A46B; border: none; color: #FFF; 
            font-weight: 700; cursor: pointer; border-radius: 6px; font-size: 14px; 
            text-transform: uppercase; transition: 0.3s;
        }
        button:hover { background: #3D2E25; }
        
        .footer-link { text-align: center; margin-top: 20px; font-size: 13px; }
        .footer-link a { color: #C4A46B; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="box">
    <h2>Mot de passe oublié</h2>

    <?php if (!empty($err)): ?>
      <div class="err"><?= htmlspecialchars($err[0]) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <input type="email" name="email" placeholder="Entrez votre email" required>
      <button type="submit" name="submit">Envoyer le lien</button>
    </form>
    
    <div class="footer-link">
        <a href="login.php">Retour à la connexion</a>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>