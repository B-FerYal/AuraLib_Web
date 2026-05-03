<?php
$translations = [
    'fr' => [
        'home' => 'Accueil',
        'library' => 'Bibliothèque',
        'cart' => 'Panier',
        'search' => 'Recherche',
        'welcome' => 'Bienvenue dans notre bibliothèque'
    ],
    'en' => [
        'home' => 'Home',
        'library' => 'Library',
        'cart' => 'Cart',
        'search' => 'Search',
        'welcome' => 'Welcome to our library'
    ],
    'ar' => [
        'home' => 'الرئيسية',
        'library' => 'المكتبة',
        'cart' => 'السلة',
        'search' => 'بحث',
        'welcome' => 'مرحباً بكم في مكتبتنا'
    ]
];

// تحديد اللغة الافتراضية
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'fr';
}

// تغيير اللغة عند الطلب
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'];
$text = $translations[$lang];
?>