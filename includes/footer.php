<?php
// includes/footer.php
// Appelé en bas de chaque page : include "../includes/footer.php";

// $lang و $text متاحان تلقائياً من header.php ← languages.php
// نصوص الـ footer حسب اللغة

// Fallback si languages.php pas encore chargé
if (!isset($lang))  $lang = 'fr';
if (!isset($base))  $base = '/MEMOIR';
$ft = [
    'fr' => [
        'tagline'      => 'Plateforme de gestion de livres en ligne',
        'desc'         => 'Découvrez, empruntez et achetez vos livres en ligne. AuraLib connecte les lecteurs aux bibliothèques partout, simplement et rapidement.',
        'badge'        => ' Emprunt · Achat',
        // Nav col
        'nav_title'    => 'Navigation',
        'nav_home'     => 'Accueil',
        'nav_catalogue'=> 'Catalogue',
        'nav_loans'    => 'Mes emprunts',
        'nav_orders'   => 'Mes achats',
        'nav_profile'  => 'Mon profil',
        // Services col
        'srv_title'    => 'Services',
        'srv_borrow'   => 'Emprunter un livre',
        'srv_buy'      => 'Acheter un livre',
        'srv_wishlist' => 'Ma liste de souhaits',
        'srv_cart'     => 'Mon panier',
        'srv_notifs'   => 'Notifications',
        // Help col
        'hlp_title'    => 'Aide',
        'hlp_about'    => 'À propos',
        'hlp_contact'  => 'Contact',
        'hlp_policy'   => "Politique d'emprunt",
        'hlp_terms'    => "Conditions d'utilisation",
        'hlp_login'    => 'Connexion',
        // Bottom
        'copy_suffix'  => 'Gestion, vente et emprunt de livres en ligne',
        'privacy'      => 'Confidentialité',
        'legal'        => 'Mentions légales',
        'terms'        => 'Conditions',
    ],
    'en' => [
        'tagline'      => 'Online library management platform',
        'desc'         => 'Discover, borrow and buy your books online. AuraLib connects readers to libraries everywhere, simply and quickly.',
        'badge'        => 'Reading · Borrowing · Purchasing',
        'nav_title'    => 'Navigation',
        'nav_home'     => 'Home',
        'nav_catalogue'=> 'Catalogue',
        'nav_loans'    => 'My loans',
        'nav_orders'   => 'My purchases',
        'nav_profile'  => 'My profile',
        'srv_title'    => 'Services',
        'srv_borrow'   => 'Borrow a book',
        'srv_buy'      => 'Buy a book',
        'srv_wishlist' => 'My wishlist',
        'srv_cart'     => 'My cart',
        'srv_notifs'   => 'Notifications',
        'hlp_title'    => 'Help',
        'hlp_about'    => 'About',
        'hlp_contact'  => 'Contact',
        'hlp_policy'   => 'Borrowing policy',
        'hlp_terms'    => 'Terms of use',
        'hlp_login'    => 'Login',
        'copy_suffix'  => 'Library management, sales and loans online',
        'privacy'      => 'Privacy',
        'legal'        => 'Legal notice',
        'terms'        => 'Terms',
    ],
    'ar' => [
        'tagline'      => 'منصة لإدارة الكتب عبر الإنترنت',
        'desc'         => 'اكتشف، استعر واشترِ كتبك عبر الإنترنت. AuraLib يربط القراء بالمكتبات في كل مكان، ببساطة وسرعة.',
        'badge'        => 'قراءة · استعارة · شراء',
        'nav_title'    => 'التنقل',
        'nav_home'     => 'الرئيسية',
        'nav_catalogue'=> 'الكتالوج',
        'nav_loans'    => 'استعاراتي',
        'nav_orders'   => 'مشترياتي',
        'nav_profile'  => 'ملفي الشخصي',
        'srv_title'    => 'الخدمات',
        'srv_borrow'   => 'استعارة كتاب',
        'srv_buy'      => 'شراء كتاب',
        'srv_wishlist' => 'قائمة أمنياتي',
        'srv_cart'     => 'سلتي',
        'srv_notifs'   => 'الإشعارات',
        'hlp_title'    => 'المساعدة',
        'hlp_about'    => 'حول الموقع',
        'hlp_contact'  => 'اتصل بنا',
        'hlp_policy'   => 'سياسة الاستعارة',
        'hlp_terms'    => 'شروط الاستخدام',
        'hlp_login'    => 'تسجيل الدخول',
        'copy_suffix'  => 'إدارة وبيع واستعارة الكتب عبر الإنترنت',
        'privacy'      => 'الخصوصية',
        'legal'        => 'الإشعارات القانونية',
        'terms'        => 'الشروط',
    ],
];

$fl    = $ft[$lang] ?? $ft['fr'];
$isRtl = ($lang === 'ar');
?>

<footer class="aura-footer" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="aura-footer-inner">

        <!-- ══ GRID : Brand + 3 colonnes ══ -->
        <div class="aura-footer-grid">

            <!-- ── Brand ── -->
            <div class="ft-brand-col">

                <div class="ft-brand-name">
                    <span class="ft-white">Aura</span>Lib
                </div>
                <span class="ft-brand-sub"><?= $fl['tagline'] ?></span>

                <p class="ft-brand-desc">
                    <?= $fl['desc'] ?>
                </p>

                <div class="ft-socials">
                    <a href="mailto:contact@auralib.dz" class="ft-social-btn" title="Email">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                        </svg>
                    </a>
                    <a href="#" class="ft-social-btn" title="Facebook">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                        </svg>
                    </a>
                    <a href="#" class="ft-social-btn" title="Instagram">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5"/>
                            <circle cx="12" cy="12" r="4"/>
                            <circle cx="17.5" cy="6.5" r=".5" fill="currentColor"/>
                        </svg>
                    </a>
                </div>

                <div class="ft-badge">
                    <span class="ft-badge-dot"></span>
                    <span><?= $fl['badge'] ?></span>
                </div>

            </div>

            <!-- ── Navigation ── -->
            <div class="ft-col">
                <span class="ft-col-title"><?= $fl['nav_title'] ?></span>
                <ul class="ft-links">
                    <li><a href="<?= $base ?>/client/library.php"><?= $fl['nav_home'] ?></a></li>
                    <li><a href="<?= $base ?>/client/library.php"><?= $fl['nav_catalogue'] ?></a></li>
                    <li><a href="<?= $base ?>/emprunts/mes_emprunts.php"><?= $fl['nav_loans'] ?></a></li>
                    <li><a href="<?= $base ?>/commandes/commande_list.php"><?= $fl['nav_orders'] ?></a></li>
                    <li><a href="<?= $base ?>/client/profile.php"><?= $fl['nav_profile'] ?></a></li>
                </ul>
            </div>

            <!-- ── Services ── -->
            <div class="ft-col">
                <span class="ft-col-title"><?= $fl['srv_title'] ?></span>
                <ul class="ft-links">
                    <li><a href="<?= $base ?>/client/library.php?filter=emprunt"><?= $fl['srv_borrow'] ?></a></li>
                    <li><a href="<?= $base ?>/client/library.php?filter=achat"><?= $fl['srv_buy'] ?></a></li>
                    <li><a href="<?= $base ?>/client/profile.php?tab=wishlist"><?= $fl['srv_wishlist'] ?></a></li>
                    <li><a href="<?= $base ?>/cart/panier.php"><?= $fl['srv_cart'] ?></a></li>
                    <li><a href="<?= $base ?>/client/notifications.php"><?= $fl['srv_notifs'] ?></a></li>
                </ul>
            </div>

            <!-- ── Aide ── -->
            <div class="ft-col">
                <span class="ft-col-title"><?= $fl['hlp_title'] ?></span>
                <ul class="ft-links">
                    <li><a href="<?= $base ?>/client/about.php"><?= $fl['hlp_about'] ?></a></li>
                    <li><a href="<?= $base ?>/client/contact.php"><?= $fl['hlp_contact'] ?></a></li>
                    <li><a href="#"><?= $fl['hlp_policy'] ?></a></li>
                    <li><a href="#"><?= $fl['hlp_terms'] ?></a></li>
                    <li><a href="<?= $base ?>/auth/login.php"><?= $fl['hlp_login'] ?></a></li>
                </ul>
            </div>

        </div>

        <hr class="ft-divider">

        <!-- ══ Barre du bas ══ -->
        <div class="ft-bottom">
            <p class="ft-copy">
                &copy; <?= date('Y') ?> <strong>AuraLib</strong>
                &nbsp;·&nbsp; <?= $fl['copy_suffix'] ?>
            </p>
            <div class="ft-legal">
                <a href="#"><?= $fl['privacy'] ?></a>
                <a href="#"><?= $fl['legal'] ?></a>
                <a href="#"><?= $fl['terms'] ?></a>
            </div>
        </div>

    </div>
</footer>

<style>
/* ════════════════════════════════════════════
   AURALIB — Footer
   Thème : crème · taupe · doré
════════════════════════════════════════════ */
.aura-footer {
    background: #2C1F0E;
    padding: 52px 0 0;
    margin-top: 60px;
    border-top: 1px solid rgba(196,164,107,.2);
    font-family: 'Lato', sans-serif;
}

.aura-footer-inner {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 40px;
}

/* ── Grid ── */
.aura-footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 40px;
    margin-bottom: 40px;
}

/* ── Brand ── */
.ft-brand-name {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    color: #C4A46B;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 4px;
}
.ft-white { color: #ffffff; }

.ft-brand-sub {
    display: block;
    font-size: 9px;
    color: rgba(196,164,107,.32);
    letter-spacing: 2.5px;
    text-transform: uppercase;
    margin-bottom: 14px;
}

.ft-brand-desc {
    font-size: 12px;
    color: rgba(237,229,212,.4);
    line-height: 1.85;
    margin-bottom: 20px;
    max-width: 230px;
}

/* ── Socials ── */
.ft-socials {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
}
.ft-social-btn {
    width: 32px; height: 32px;
    border-radius: 7px;
    background: rgba(196,164,107,.1);
    border: 1px solid rgba(196,164,107,.22);
    display: flex; align-items: center; justify-content: center;
    color: #C4A46B;
    text-decoration: none;
    transition: background .15s, border-color .15s;
    flex-shrink: 0;
}
.ft-social-btn:hover {
    background: rgba(196,164,107,.22);
    border-color: rgba(196,164,107,.5);
}
.ft-social-btn svg { width: 14px; height: 14px; }

/* ── Badge ── */
.ft-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(196,164,107,.07);
    border: 1px solid rgba(196,164,107,.15);
    border-radius: 6px;
    padding: 6px 12px;
}
.ft-badge-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #C4A46B;
    flex-shrink: 0;
}
.ft-badge span {
    font-size: 10px;
    color: rgba(196,164,107,.55);
    letter-spacing: .5px;
}

/* ── Colonnes nav ── */
.ft-col-title {
    display: block;
    font-size: 10px;
    font-weight: 700;
    color: #C4A46B;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    margin-bottom: 14px;
}

.ft-links {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 9px;
}
.ft-links a {
    font-size: 12px;
    color: rgba(237,229,212,.44);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 7px;
    transition: color .15s;
}
.ft-links a::before {
    content: '';
    width: 4px; height: 4px;
    border-radius: 50%;
    background: rgba(196,164,107,.28);
    flex-shrink: 0;
    transition: background .15s;
}
.ft-links a:hover { color: #C4A46B; }
.ft-links a:hover::before { background: #C4A46B; }

/* ── Divider ── */
.ft-divider {
    border: none;
    border-top: 1px solid rgba(196,164,107,.12);
    margin: 0;
}
/* ── Bottom bar ── */
.ft-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 0;
    flex-wrap: wrap;
    gap: 10px;
}

.ft-copy {
    font-size: 11px;
    color: rgba(237,229,212,.24);
    letter-spacing: .2px;
}
.ft-copy strong {
    color: rgba(196,164,107,.45);
    font-weight: 600;
}

.ft-legal { display: flex; gap: 16px; }
.ft-legal a {
    font-size: 11px;
    color: rgba(237,229,212,.24);
    text-decoration: none;
    transition: color .15s;
}
.ft-legal a:hover { color: #C4A46B; }

/* ── RTL support ── */
.aura-footer[dir="rtl"] .ft-links a {
    flex-direction: row-reverse;
}
.aura-footer[dir="rtl"] .ft-links a::before {
    order: 1;
}
.aura-footer[dir="rtl"] .ft-brand-sub {
    letter-spacing: 0;
}
.aura-footer[dir="rtl"] .ft-col-title {
    letter-spacing: 0;
}
.aura-footer[dir="rtl"] .ft-socials {
    flex-direction: row-reverse;
}
.aura-footer[dir="rtl"] .ft-bottom {
    flex-direction: row-reverse;
}
.aura-footer[dir="rtl"] .ft-legal {
    flex-direction: row-reverse;
}
.aura-footer[dir="rtl"] .ft-badge {
    flex-direction: row-reverse;
}
.aura-footer[dir="rtl"] .ft-brand-desc {
    text-align: right;
}

/* ── Responsive ── */
@media (max-width: 768px) {
    .aura-footer-grid {
        grid-template-columns: 1fr 1fr;
    }
    .ft-brand-col {
        grid-column: 1 / -1;
    }
    .ft-brand-desc { max-width: 100%; }
}

@media (max-width: 480px) {
    .aura-footer-inner { padding: 0 20px; }
    .aura-footer-grid  { grid-template-columns: 1fr; }
    .ft-bottom {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}
</style>