<?php
/**
 * includes/head.php
 * À inclure en premier dans TOUTES les pages (avant le HTML)
 * Gère : session, DB, langue, dark mode appliqué avant le paint
 *
 * Usage : <?php require_once DIR . '/../includes/head.php'; ?>
 *         ou  <?php require_once 'includes/head.php'; ?> (depuis la racine)
 */

// ── Session ──────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Langue (languages.php doit être dans includes/) ──────
if (!isset($text)) {
    // Chemin relatif au fichier head.php lui-même
    $lang_file = 'languages.php';
    if (file_exists($lang_file)) {
        require_once $lang_file;
    } else {
        // Fallback minimal si languages.php introuvable
        $lang = 'fr';
        $text = [];
    }
}

// ── Base path ─────────────────────────────────────────────
if (!isset($base)) {
    $base = '/MEMOIR';
}

// ── Titre de page par défaut ──────────────────────────────
if (!isset($page_title)) {
    $page_title = 'AuraLib';
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?? 'fr' ?>" dir="<?= ($lang ?? 'fr') === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuraLib | <?= htmlspecialchars($page_title) ?></title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Lato:wght@300;400;600;700&family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Dark mode CSS — chargé sur TOUTES les pages -->
    <link rel="stylesheet" href="<?= $base ?>/css/dark-mode.css">

    <!--
        CRITIQUE : Ce script applique .dark sur <html> AVANT le paint
        → Aucun flash blanc au chargement
        NE PAS déplacer ce script ailleurs
    -->
    <script>
        (function () {
            if (localStorage.getItem('auralib_theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

</head>
<body>