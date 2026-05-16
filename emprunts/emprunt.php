<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once "../includes/db.php"; 

if (!isset($_SESSION['id_user'])) {
    $redirect = urlencode($_SERVER['REQUEST_URI']);
    header("Location: ../auth/login.php?redirect=" . $redirect);
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] === 'admin') {
    header("Location: ../client/library.php");
    exit;
}
   
$id_user = (int)$_SESSION['id_user'];
$id_doc  = isset($_GET['id_doc']) ? intval($_GET['id_doc']) : 0;

$book    = null;
$success = false;
$error   = '';

if ($id_doc > 0) {
    $res  = $conn->query("SELECT * FROM documents WHERE id_doc = $id_doc");
    $book = $res->fetch_assoc();
}

// ════════════════════════════════════════
// TRAITEMENT DU FORMULAIRE
// ════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $book) {
    $date_debut = $_POST['date_debut'] ?? date('Y-m-d');

    $conn->begin_transaction();
    try {
        // 1. تحقق من المخزون (FOR UPDATE يمنع race condition)
        $chk = $conn->query("
            SELECT exemplaires_disponibles 
            FROM documents 
            WHERE id_doc = $id_doc 
            FOR UPDATE
        ");
        $doc = $chk->fetch_assoc();

        if ((int)$doc['exemplaires_disponibles'] <= 0) {
            $conn->rollback();
            $error = 'no_stock';

        } else {
            // 2. تحقق هل عنده طلب نشط لنفس الكتاب
            $chk2 = $conn->prepare("
                SELECT id_emprunt FROM emprunt 
                WHERE id_user = ? AND id_doc = ? 
                AND statut IN ('en attente', 'acceptée', 'retard')
            ");
            $chk2->bind_param("ii", $id_user, $id_doc);
            $chk2->execute();

            if ($chk2->get_result()->num_rows > 0) {
                $conn->rollback();
                $error = 'already_borrowed';

            } else {
                // 3. إدخال طلب الإعارة
                $ins = $conn->prepare("
                    INSERT INTO emprunt (id_user, id_doc, date_debut, statut) 
                    VALUES (?, ?, ?, 'en attente')
                ");
                $ins->bind_param("iis", $id_user, $id_doc, $date_debut);
                $ins->execute();

                // 4. حجز الكتاب من المخزون فوراً
                $conn->query("
                    UPDATE documents 
                    SET exemplaires_disponibles = exemplaires_disponibles - 1 
                    WHERE id_doc = $id_doc
                ");

                $conn->commit();
                $success = true;
            }
        }

    } catch (Exception $e) {
        $conn->rollback();
        $error = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <?php include '../includes/dark_init.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuraLib | Demande d'Emprunt</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --gold: #D4A942; --dark: #2C1F0E; --light-bg: #f4f1ea; }
        body { background: var(--light-bg); font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: white; display: flex; border-radius: 30px; box-shadow: 0 25px 50px rgba(0,0,0,0.1); width: 90%; max-width: 800px; overflow: hidden; border: 1px solid rgba(212, 169, 66, 0.1); }
        .book-preview { background: var(--dark); color: white; padding: 40px; width: 40%; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; position: relative; }
        .book-preview img { width: 160px; height: 230px; object-fit: cover; border-radius: 12px; box-shadow: 0 15px 30px rgba(0,0,0,0.4); margin-bottom: 25px; border: 2px solid var(--gold); z-index: 1; }
        .book-preview h3 { font-size: 20px; margin: 10px 0; z-index: 1; }
        .book-preview p { color: var(--gold); font-size: 15px; margin: 0; z-index: 1; }

        /* Stock badge */
        .stock-badge { margin-top: 14px; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; z-index: 1; }
        .stock-ok   { background: rgba(74,222,128,.15); color: #4ade80; border: 1px solid rgba(74,222,128,.3); }
        .stock-zero { background: rgba(248,113,113,.15); color: #f87171; border: 1px solid rgba(248,113,113,.3); }

        .form-side { padding: 50px; width: 60%; display: flex; flex-direction: column; justify-content: center; }
        h2 { color: var(--dark); margin-bottom: 10px; font-weight: 800; font-size: 26px; }
        .subtitle { color: #888; font-size: 14px; margin-bottom: 30px; }
        .info-box { background: #FDFBFA; padding: 15px 20px; border-radius: 15px; margin-bottom: 25px; border-left: 4px solid var(--gold); font-size: 14px; color: #555; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--dark); font-size: 14px; }
        input[type="date"] { width: 100%; padding: 14px; margin-bottom: 30px; border-radius: 12px; border: 1px solid #ddd; outline: none; transition: 0.3s; box-sizing: border-box; }
        input[type="date"]:focus { border-color: var(--gold); box-shadow: 0 0 0 4px rgba(212, 169, 66, 0.1); }
        .btn-confirm { background: var(--dark); color: white; border: none; padding: 18px; border-radius: 12px; width: 100%; cursor: pointer; font-weight: 700; font-size: 15px; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; }
        .btn-confirm:hover { background: var(--gold); transform: translateY(-3px); box-shadow: 0 10px 20px rgba(212, 169, 66, 0.2); }
        .btn-confirm:disabled { background: #ccc; cursor: not-allowed; transform: none; box-shadow: none; }
        .back-link { display: block; margin-top: 25px; color: #aaa; text-decoration: none; font-size: 13px; text-align: center; }
        .error-msg { background: #fee; color: #c33; padding: 20px; border-radius: 15px; text-align: center; }
        @media (max-width: 768px) { .card { flex-direction: column; width: 95%; } .book-preview, .form-side { width: 100%; padding: 30px; } }
    </style>
</head>
<body>

<div class="card">
    <?php if ($book): 
        $stock = (int)$book['exemplaires_disponibles'];
        $cover_path = "../uploads/" . $book['id_doc'] . ".jpg";
        $cover = file_exists($cover_path) ? $cover_path : "../uploads/default.jpg";
    ?>
        <div class="book-preview">
            <img src="<?= $cover ?>" alt="Couverture">
            <h3><?= htmlspecialchars($book['titre']) ?></h3>
            <p><i class="fa fa-pen-nib"></i> <?= htmlspecialchars($book['auteur']) ?></p>

            <!-- عرض المخزون مباشرة -->
            <?php if ($stock > 0): ?>
                <span class="stock-badge stock-ok">
                    ✓ <?= $stock ?> exemplaire<?= $stock > 1 ? 's' : '' ?> disponible<?= $stock > 1 ? 's' : '' ?>
                </span>
            <?php else: ?>
                <span class="stock-badge stock-zero">
                    ✗ Plus disponible
                </span>
            <?php endif; ?>
        </div>

        <div class="form-side">
            <h2>Demande d'Emprunt</h2>
            <p class="subtitle">Complétez les informations pour réserver votre livre.</p>

            <?php if ($stock <= 0): ?>
                <!-- الكتاب ما عادش متوفر -->
                <div class="info-box" style="border-left-color:#f87171; background:#fef2f2;">
                    <i class="fa fa-exclamation-circle" style="color:#f87171;"></i>
                    Ce document n'est <strong>plus disponible</strong> pour l'emprunt actuellement.
                    Revenez plus tard ou explorez d'autres titres.
                </div>
                <a href="../client/library.php" class="btn-confirm" 
                   style="background:#f87171; text-align:center; text-decoration:none; display:block; padding:18px; border-radius:12px; color:white; font-weight:700; font-size:15px; text-transform:uppercase; letter-spacing:1px;">
                    Explorer le catalogue
                </a>

            <?php else: ?>
                <form method="POST" id="loanForm" action="emprunt.php?id_doc=<?= (int)$id_doc ?>">
                    <div class="info-box">
                        <i class="fa fa-info-circle" style="color: var(--gold);"></i> 
                        La durée d'emprunt est fixée à <strong>15 jours</strong> à compter de la date de début.
                    </div>

                    <label>Date de début souhaitée :</label>
                    <input type="date" name="date_debut" 
                           value="<?= date('Y-m-d') ?>" 
                           min="<?= date('Y-m-d') ?>" required>
                    
                    <button type="submit" class="btn-confirm">
                        <i class="fa fa-check-circle"></i> Confirmer la demande
                    </button>
                </form>
            <?php endif; ?>

            <a href="../client/library.php" class="back-link">← Retourner au catalogue</a>
        </div>

    <?php else: ?>
        <div class="form-side" style="width: 100%;">
            <div class="error-msg">
                <i class="fa fa-exclamation-triangle fa-2x"></i><br><br>
                <strong>Aucun document sélectionné.</strong>
            </div>
            <a href="../client/library.php" class="back-link">← Retourner au catalogue</a>
        </div>
    <?php endif; ?>
</div>

<script>
<?php if ($success): ?>
    Swal.fire({
        title: '✅ Demande Envoyée !',
        html: 'Votre demande est <strong>en attente de validation</strong> par un administrateur.',
        icon: 'success',
        confirmButtonColor: '#2C1F0E',
        confirmButtonText: 'Voir mes emprunts'
    }).then(() => {
        window.location.href = 'mes_emprunts.php';
    });

<?php elseif ($error === 'no_stock'): ?>
    Swal.fire({
        title: '❌ Stock Épuisé',
        text: 'Ce livre n\'est plus disponible. Un autre lecteur vient de le réserver.',
        icon: 'error',
        confirmButtonColor: '#2C1F0E',
        confirmButtonText: 'Retour au catalogue'
    }).then(() => {
        window.location.href = '../client/library.php';
    });

<?php elseif ($error === 'already_borrowed'): ?>
    Swal.fire({
        title: '⚠️ Déjà en cours',
        text: 'Vous avez déjà une demande active pour ce livre.',
        icon: 'warning',
        confirmButtonColor: '#2C1F0E',
        confirmButtonText: 'Voir mes emprunts'
    }).then(() => {
        window.location.href = 'mes_emprunts.php';
    });

<?php elseif ($error === 'error'): ?>
    Swal.fire({
        title: '❌ Erreur',
        text: 'Une erreur est survenue. Veuillez réessayer.',
        icon: 'error',
        confirmButtonColor: '#2C1F0E'
    });
<?php endif; ?>
</script>
</body>
</html>