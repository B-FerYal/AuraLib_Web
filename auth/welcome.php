<?php
session_start();
require_once "../includes/db.php";

// 1. Sécurité : Check ida rahou m-connecti
if (!isset($_SESSION['id_user'])) {
    header('Location: auth/login.php');
    exit;
}

// 2. Récupération ta l-ism l-kamal (Firstname + Lastname)
// Mlahda: Lazem t-kon derti $_SESSION['lastname'] f login.php bach yban
$firstname = htmlspecialchars($_SESSION['firstname'] ?? 'Utilisateur');
$lastname  = htmlspecialchars($_SESSION['lastname'] ?? ''); 
$full_name = trim($firstname . ' ' . $lastname);

$role = htmlspecialchars($_SESSION['role'] ?? 'Étudiant');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <?php include '../includes/dark_init.php'; ?>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bienvenue — BiblioUHBC</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --cream:      #F5F0E8;
      --gold:       #C4A46B;
      --white:      #FFFDF9;
      --taupe-deep: #3D2E25;
      --taupe:      #9A8C7E;
    }

    body {
      background: var(--cream);
      font-family: 'Lato', sans-serif;
      height: 100vh; 
      display: flex; 
      align-items: center; 
      justify-content: center;
      margin: 0;
      overflow: hidden;
    }

    .splash-card {
      background: var(--white);
      padding: 70px 60px;
      text-align: center;
      max-width: 550px; 
      width: 90%;
      border-radius: 30px;
      box-shadow: 0 25px 70px rgba(61, 46, 37, 0.08);
      animation: fadeIn 0.8s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .main-circle {
      width: 110px; 
      height: 110px; 
      background: var(--taupe-deep);
      border-radius: 50%; 
      display: flex; 
      align-items: center; 
      justify-content: center;
      margin: 0 auto 35px; 
      border: 4px solid var(--gold);
    }

    .text-bienvenue {
      font-size: 15px; 
      letter-spacing: 5px; 
      text-transform: uppercase;
      color: var(--taupe); 
      margin-bottom: 15px;
      font-weight: 400;
    }

    .text-nom {
      font-family: 'Playfair Display', serif;
      font-size: 52px; /* TAILLE BIG SIZE */
      color: var(--gold);
      font-weight: 700;
      line-height: 1.1;
      margin-bottom: 40px;
      font-style: italic;
    }

    .loader-bar {
      width: 100%; 
      height: 4px; 
      background: #eee; 
      border-radius: 10px; 
      overflow: hidden;
      max-width: 300px;
      margin: 0 auto;
    }

    .loader-fill {
      width: 0%; 
      height: 100%; 
      background: var(--gold); 
      transition: width 3s linear;
    }

    @media (max-width: 480px) {
      .text-nom { font-size: 35px; }
      .splash-card { padding: 40px 30px; }
    }
  </style>
</head>
<body>

  <div class="splash-card">
      
    <div class="main-circle">
      <svg width="50" height="50" viewBox="0 0 40 40" fill="none">
        <rect x="11" y="7" width="18" height="25" rx="2" fill="#EDE5D4" stroke="#C4A46B" stroke-width="1.5"/>
        <line x1="15" y1="14" x2="25" y2="14" stroke="#C4A46B" stroke-width="2.5" stroke-linecap="round"/>
        <line x1="15" y1="20" x2="25" y2="20" stroke="#C4A46B" stroke-width="2.5" stroke-linecap="round"/>
        <line x1="15" y1="26" x2="21" y2="26" stroke="#C4A46B" stroke-width="2.5" stroke-linecap="round"/>
      </svg>
    </div>

    <p class="text-bienvenue">Bienvenue</p>

    <h1 class="text-nom"><?= $full_name ?></h1>

    <div class="loader-bar">
      <div class="loader-fill" id="fill"></div>
    </div>
    
  </div>

  <script>
    window.onload = function() {
      // Animation ta l-barre de chargement
      setTimeout(function() {
        document.getElementById('fill').style.width = '100%';
      }, 100);

      // Redirection automatique après 3.5 secondes
      setTimeout(function() {
        window.location.href = '../client/library.php'; 
      }, 3500);
    };
  </script>

</body>
</html>