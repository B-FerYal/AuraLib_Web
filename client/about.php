<?php
include "../includes/header.php";
// $lang و $text متاحان تلقائياً من header.php

// ── نصوص الصفحة حسب اللغة ──────────────────────────────
$pg = [
    'fr' => [
        // Hero
        'badge'           => '✦ À propos de nous',
        'hero_h1'         => "La bibliothèque<br>de <em>demain</em>, aujourd'hui",
        'hero_p'          => "AuraLib est une plateforme numérique dédiée à rendre la lecture accessible — emprunter gratuitement ou acheter en quelques clics, depuis n'importe où.",
        'scroll'          => 'Découvrir',

        // Stats
        'stat_docs'       => 'Documents',
        'stat_users'      => 'Lecteurs',
        'stat_loans'      => 'Emprunts réalisés',
        'stat_types'      => 'Types de documents',

        // Mission
        'mission_tag'     => 'Notre mission',
        'mission_h2'      => 'Démocratiser l\'accès au savoir',
        'mission_p1'      => 'AuraLib est né d\'une conviction simple : chaque personne mérite un accès facile, rapide et élégant aux livres, thèses, articles et documents académiques.',
        'mission_p2'      => 'En combinant emprunt gratuit et achat en ligne, nous offrons une expérience complète qui s\'adapte à tous les besoins — étudiant, chercheur, ou simple passionné de lecture.',

        // Dividers
        'div_offer'       => 'Ce que nous proposons',
        'div_values'      => 'Nos valeurs',
        'div_how'         => 'Comment ça marche',

        // Features
        'feat1_h'  => 'Emprunt gratuit &amp; simple',
        'feat1_p'  => 'Empruntez n\'importe quel document disponible pour 14 jours, gratuitement. Renouvelez en un clic si vous avez besoin de plus de temps.',
        'feat2_h'  => 'Achat en ligne sécurisé',
        'feat2_p'  => 'Achetez vos livres préférés directement depuis le catalogue. Ajoutez au panier, validez votre commande et suivez sa livraison.',
        'feat3_h'  => 'Recherche intelligente',
        'feat3_p'  => 'Trouvez n\'importe quel document par titre, auteur, ISBN ou catégorie. Les résultats apparaissent instantanément.',
        'feat4_h'  => 'Interface multilingue &amp; dark mode',
        'feat4_p'  => 'Disponible en français, anglais et arabe. Le mode sombre s\'active en un clic et reste mémorisé entre vos visites.',

        // Values
        'val1_h' => 'Accessibilité', 'val1_p' => 'Le savoir ne doit pas avoir de barrière. Emprunt gratuit pour tous.',
        'val2_h' => 'Excellence',    'val2_p' => 'Une interface soignée, rapide et pensée pour l\'expérience utilisateur.',
        'val3_h' => 'Sécurité',      'val3_p' => 'Vos données personnelles sont protégées et ne sont jamais partagées.',
        'val4_h' => 'Inclusion',     'val4_p' => 'Disponible en 3 langues pour toucher le plus grand nombre.',
        'val5_h' => 'Diversité',     'val5_p' => 'Livres, thèses, articles, journaux — tout type de document en un seul endroit.',
        'val6_h' => 'Rapidité',      'val6_p' => 'Empruntez ou achetez en moins de 3 clics, sans file d\'attente.',

        // Steps
        'step1_h' => 'Créez votre compte',      'step1_p' => 'Inscription gratuite en moins d\'une minute. Nom, email, mot de passe — c\'est tout.',
        'step2_h' => 'Parcourez le catalogue',  'step2_p' => 'Des milliers de documents filtrables par type, thème ou disponibilité.',
        'step3_h' => 'Empruntez ou achetez',    'step3_p' => 'Choisissez votre mode selon le document. Emprunt gratuit ou achat sécurisé.',
        'step4_h' => 'Suivez &amp; gérez',       'step4_p' => 'Votre tableau de bord affiche vos emprunts, retours et commandes en temps réel.',

        // CTA
        'cta_h2'      => 'Prêt à explorer notre catalogue ?',
        'cta_p'       => 'Rejoignez des centaines de lecteurs qui font confiance à AuraLib chaque jour.',
        'cta_btn1'    => 'Explorer le catalogue',
        'cta_btn2'    => 'Créer un compte gratuit',
    ],

    'en' => [
        'badge'           => '✦ About us',
        'hero_h1'         => "The library of<br><em>tomorrow</em>, today",
        'hero_p'          => "AuraLib is a digital platform dedicated to making reading accessible — borrow for free or buy in a few clicks, from anywhere.",
        'scroll'          => 'Discover',

        'stat_docs'       => 'Documents',
        'stat_users'      => 'Readers',
        'stat_loans'      => 'Loans completed',
        'stat_types'      => 'Document types',

        'mission_tag'     => 'Our mission',
        'mission_h2'      => 'Democratising access to knowledge',
        'mission_p1'      => 'AuraLib was born from a simple belief: everyone deserves easy, fast and elegant access to books, theses, articles and academic documents.',
        'mission_p2'      => 'By combining free borrowing and online purchasing, we offer a complete experience that adapts to all needs — student, researcher, or avid reader.',

        'div_offer'       => 'What we offer',
        'div_values'      => 'Our values',
        'div_how'         => 'How it works',

        'feat1_h'  => 'Free &amp; simple borrowing',
        'feat1_p'  => 'Borrow any available document for 14 days, free of charge. Renew in one click if you need more time.',
        'feat2_h'  => 'Secure online purchase',
        'feat2_p'  => 'Buy your favourite books directly from the catalogue. Add to cart, confirm your order and track delivery.',
        'feat3_h'  => 'Smart search',
        'feat3_p'  => 'Find any document by title, author, ISBN or category. Results appear instantly.',
        'feat4_h'  => 'Multilingual interface &amp; dark mode',
        'feat4_p'  => 'Available in French, English and Arabic. Dark mode activates in one click and is remembered between visits.',

        'val1_h' => 'Accessibility', 'val1_p' => 'Knowledge should have no barriers. Free borrowing for everyone.',
        'val2_h' => 'Excellence',    'val2_p' => 'A refined, fast interface designed for the best user experience.',
        'val3_h' => 'Security',      'val3_p' => 'Your personal data is protected and never shared.',
        'val4_h' => 'Inclusion',     'val4_p' => 'Available in 3 languages to reach as many people as possible.',
        'val5_h' => 'Diversity',     'val5_p' => 'Books, theses, articles, newspapers — every type of document in one place.',
        'val6_h' => 'Speed',         'val6_p' => 'Borrow or buy in fewer than 3 clicks, no queuing.',

        'step1_h' => 'Create your account',   'step1_p' => 'Free registration in under a minute. Name, email, password — that\'s it.',
        'step2_h' => 'Browse the catalogue',  'step2_p' => 'Thousands of documents filterable by type, theme or availability.',
        'step3_h' => 'Borrow or buy',         'step3_p' => 'Choose your method depending on the document. Free loan or secure purchase.',
        'step4_h' => 'Track &amp; manage',    'step4_p' => 'Your dashboard shows your loans, returns and orders in real time.',

        'cta_h2'   => 'Ready to explore our catalogue?',
        'cta_p'    => 'Join hundreds of readers who trust AuraLib every day.',
        'cta_btn1' => 'Explore the catalogue',
        'cta_btn2' => 'Create a free account',
    ],

    'ar' => [
        'badge'           => '✦ من نحن',
        'hero_h1'         => "مكتبة <em>الغد</em>، اليوم",
        'hero_p'          => "AuraLib منصة رقمية تجعل القراءة في متناول الجميع — استعر مجاناً أو اشترِ بنقرات قليلة، من أي مكان.",
        'scroll'          => 'اكتشف',

        'stat_docs'       => 'وثيقة',
        'stat_users'      => 'قارئ',
        'stat_loans'      => 'استعارة مُنجزة',
        'stat_types'      => 'أنواع الوثائق',

        'mission_tag'     => 'مهمتنا',
        'mission_h2'      => 'ديمقراطية الوصول إلى المعرفة',
        'mission_p1'      => 'وُلد AuraLib من قناعة بسيطة: كل شخص يستحق وصولاً سهلاً وسريعاً وأنيقاً إلى الكتب والرسائل والمقالات والوثائق الأكاديمية.',
        'mission_p2'      => 'بالجمع بين الاستعارة المجانية والشراء الإلكتروني، نقدم تجربة متكاملة تلائم جميع الاحتياجات — طالب، باحث، أو قارئ متحمس.',

        'div_offer'       => 'ما نقدمه',
        'div_values'      => 'قيمنا',
        'div_how'         => 'كيف يعمل',

        'feat1_h'  => 'استعارة مجانية وسهلة',
        'feat1_p'  => 'استعر أي وثيقة متاحة لمدة 14 يوماً مجاناً. جدّد بنقرة واحدة إذا احتجت وقتاً أكثر.',
        'feat2_h'  => 'شراء إلكتروني آمن',
        'feat2_p'  => 'اشترِ كتبك المفضلة مباشرةً من الكتالوج. أضف إلى السلة، أكّد طلبك وتابع التسليم.',
        'feat3_h'  => 'بحث ذكي',
        'feat3_p'  => 'ابحث عن أي وثيقة بالعنوان أو المؤلف أو الفئة. تظهر النتائج فورياً.',
        'feat4_h'  => 'واجهة متعددة اللغات والوضع الليلي',
        'feat4_p'  => 'متوفر بالفرنسية والإنجليزية والعربية. الوضع الليلي يُفعَّل بنقرة واحدة ويُحفظ بين الزيارات.',

        'val1_h' => 'إتاحة للجميع', 'val1_p' => 'المعرفة لا يجب أن تكون خلف حواجز. استعارة مجانية للجميع.',
        'val2_h' => 'تميّز',         'val2_p' => 'واجهة مصقولة وسريعة مصممة لأفضل تجربة مستخدم.',
        'val3_h' => 'أمان',          'val3_p' => 'بياناتك الشخصية محمية ولا تُشارك أبداً.',
        'val4_h' => 'شمولية',        'val4_p' => 'متاح بـ 3 لغات للوصول إلى أكبر عدد ممكن.',
        'val5_h' => 'تنوع',          'val5_p' => 'كتب، رسائل، مقالات، جرائد — كل أنواع الوثائق في مكان واحد.',
        'val6_h' => 'سرعة',          'val6_p' => 'استعر أو اشترِ في أقل من 3 نقرات، بدون انتظار.',

        'step1_h' => 'أنشئ حسابك',         'step1_p' => 'تسجيل مجاني في أقل من دقيقة. الاسم والبريد وكلمة المرور — هذا كل شيء.',
        'step2_h' => 'تصفح الكتالوج',      'step2_p' => 'آلاف الوثائق قابلة للتصفية حسب النوع والموضوع والتوفر.',
        'step3_h' => 'استعر أو اشترِ',     'step3_p' => 'اختر الطريقة المناسبة. استعارة مجانية أو شراء آمن.',
        'step4_h' => 'تابع وأدر',           'step4_p' => 'لوحة التحكم تعرض استعاراتك وإعاداتك وطلباتك في الوقت الفعلي.',

        'cta_h2'   => 'مستعد لاستكشاف الكتالوج؟',
        'cta_p'    => 'انضم إلى مئات القراء الذين يثقون في AuraLib كل يوم.',
        'cta_btn1' => 'استكشف الكتالوج',
        'cta_btn2' => 'أنشئ حساباً مجانياً',
    ],
];

$p     = $pg[$lang] ?? $pg['fr'];
$isRtl = ($lang === 'ar');
?>
<title><?= t('about') ?> — AuraLib</title>

<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Lato:wght@300;400;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

<style>
.about-root { width:100%; margin:0; padding:0; direction:<?= $isRtl?'rtl':'ltr' ?>; }
.about-inner { max-width:1200px; margin:0 auto; padding:0 60px; }

/* ── HERO ── */
.about-hero-section {
    position:relative; width:100%; height:100vh;
    min-height:640px; max-height:900px;
    display:flex; align-items:center; justify-content:center;
    overflow:hidden; isolation:isolate; margin:0;
}
.about-hero-section::before {
    content:''; position:absolute; inset:0;
    background-image:url('../assets/images/hero-librarian.jpg');
    background-size:cover; background-position:center;
    filter:blur(3px) brightness(0.48) saturate(0.7);
    transform:scale(1.05); z-index:0;
}
.about-hero-section::after {
    content:''; position:absolute; inset:0;
    background:linear-gradient(to bottom,rgba(10,6,2,.35) 0%,rgba(20,12,4,.58) 70%,rgba(26,16,8,.75) 100%);
    z-index:1;
}
.about-hero-content { position:relative; z-index:2; text-align:center; padding:0 40px; max-width:860px; }
.badge-pill {
    display:inline-block;
    background:rgba(196,164,107,.14); border:1px solid rgba(196,164,107,.5);
    color:#E2C07A;
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Lato',sans-serif" ?>;
    font-size:10px; font-weight:700;
    letter-spacing:<?= $isRtl?'1px':'3.5px' ?>;
    text-transform:uppercase; padding:8px 24px; border-radius:30px;
    margin-bottom:32px; backdrop-filter:blur(10px);
}
.about-hero-content h1 {
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Cormorant Garamond',serif" ?>;
    font-size:<?= $isRtl?'58px':'76px' ?>; font-weight:700;
    line-height:1.15; color:#fff; margin:0 0 26px;
    letter-spacing:<?= $isRtl?'0':'-1.5px' ?>;
    text-shadow:0 3px 30px rgba(0,0,0,.65);
}
.about-hero-content h1 em { color:#D4A853; font-style:italic; }
.about-hero-content p {
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Cormorant Garamond',serif" ?>;
    font-size:<?= $isRtl?'18px':'24px' ?>; font-weight:400;
    font-style:<?= $isRtl?'normal':'italic' ?>;
    color:#F0E6CC; max-width:600px; margin:0 auto;
    line-height:1.7; text-shadow:0 2px 14px rgba(0,0,0,.65);
}
.hero-scroll-hint {
    position:absolute; bottom:30px; left:50%; transform:translateX(-50%);
    z-index:2; display:flex; flex-direction:column; align-items:center; gap:8px;
}
.hero-scroll-hint span {
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Lato',sans-serif" ?>;
    font-size:9px; font-weight:700;
    letter-spacing:<?= $isRtl?'1px':'3px' ?>;
    text-transform:uppercase; color:rgba(255,255,255,.35);
}
.hero-scroll-line {
    width:1px; height:44px;
    background:linear-gradient(to bottom,rgba(196,164,107,.85),transparent);
    animation:pulse 2.2s ease-in-out infinite;
}
@keyframes pulse { 0%,100%{opacity:.4} 50%{opacity:1} }

/* ── BODY ── */
.about-body { background:var(--page-bg,#F5F0E8); padding:80px 0; }

/* Stats */
.stats-band {
    display:grid; grid-template-columns:repeat(4,1fr);
    gap:1px; background:#DDD5C8;
    border:1px solid #DDD5C8; border-radius:16px;
    overflow:hidden; margin-bottom:80px;
}
.stat-item { background:#FFFDF9; padding:36px 24px; text-align:center; }
.stat-item .num {
    font-family:'Cormorant Garamond',serif;
    font-size:52px; font-weight:600; color:#B8924A;
    line-height:1; margin-bottom:8px; letter-spacing:-1px;
}
.stat-item .lbl {
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Lato',sans-serif" ?>;
    font-size:11px; font-weight:700;
    letter-spacing:<?= $isRtl?'0':'1.5px' ?>;
    text-transform:uppercase; color:#7A6A55;
}

/* Section titles */
.section { margin-bottom:80px; }
.section-tag {
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Lato',sans-serif" ?>;
    font-size:10px; font-weight:700;
    letter-spacing:<?= $isRtl?'0':'3px' ?>;
    text-transform:uppercase; color:#B8924A; margin-bottom:14px;
}
.section h2 {
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Cormorant Garamond',serif" ?>;
    font-size:<?= $isRtl?'36px':'46px' ?>; font-weight:700;
    color:#1A1008; margin:0 0 20px;
    letter-spacing:<?= $isRtl?'0':'-0.5px' ?>; line-height:1.2;
}
.section p {
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Lato',sans-serif" ?>;
    font-size:16px; color:#5C4A30; line-height:1.9;
    margin-bottom:16px; max-width:760px;
}

/* Divider */
.divider {
    display:flex; align-items:center; gap:18px;
    margin:0 0 50px; color:#9A8470;
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Lato',sans-serif" ?>;
    font-size:10px; font-weight:700;
    letter-spacing:<?= $isRtl?'0':'2.5px' ?>;
    text-transform:uppercase;
}
.divider::before,.divider::after { content:''; flex:1; height:1px; background:#DDD5C8; }

/* Feat cards */
.two-col { display:grid; grid-template-columns:1fr 1fr; gap:22px; margin-bottom:80px; }
.feat-card {
    background:#FFFDF9; border:1px solid #DDD5C8; border-radius:16px;
    padding:32px 28px; border-top:3px solid transparent;
    transition:border-color .2s, transform .2s;
    display:flex; align-items:flex-start; gap:22px;
    flex-direction:<?= $isRtl?'row-reverse':'row' ?>;
    text-align:<?= $isRtl?'right':'left' ?>;
}
.feat-card:hover { border-top-color:#C4A46B; transform:translateY(-4px); }
.feat-icon-wrap {
    width:56px; height:56px; min-width:56px; border-radius:50%;
    background:rgba(196,164,107,.1); border:1px solid rgba(196,164,107,.28);
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; transition:background .2s;
}
.feat-card:hover .feat-icon-wrap { background:rgba(196,164,107,.2); }
.feat-icon-wrap svg { width:22px; height:22px; stroke:#C4A46B; fill:none; stroke-width:1.7; stroke-linecap:round; stroke-linejoin:round; }
.feat-text { flex:1; }
.feat-card h3 {
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Cormorant Garamond',serif" ?>;
    font-size:<?= $isRtl?'18px':'22px' ?>; font-weight:700; color:#1A1008;
    margin:0 0 8px;
}
.feat-card p {
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Lato',sans-serif" ?>;
    font-size:14px; color:#6A5840; line-height:1.75; margin:0;
}

/* Values grid */
.values-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; margin-bottom:80px; }
.value-card {
    background:#FFFDF9; border:1px solid #DDD5C8; border-radius:14px;
    padding:28px 24px; transition:transform .2s, border-color .2s;
    text-align:<?= $isRtl?'right':'left' ?>;
}
.value-card:hover { transform:translateY(-3px); border-color:rgba(196,164,107,.5); }
.value-header {
    display:flex; align-items:center; gap:14px; margin-bottom:12px;
    flex-direction:<?= $isRtl?'row-reverse':'row' ?>;
}
.val-icon-wrap {
    width:46px; height:46px; min-width:46px; border-radius:50%;
    background:rgba(196,164,107,.1); border:1px solid rgba(196,164,107,.25);
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; transition:background .2s;
}
.value-card:hover .val-icon-wrap { background:rgba(196,164,107,.2); }
.val-icon-wrap svg { width:20px; height:20px; stroke:#C4A46B; fill:none; stroke-width:1.7; stroke-linecap:round; stroke-linejoin:round; }
.value-card h4 {
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Cormorant Garamond',serif" ?>;
    font-size:<?= $isRtl?'17px':'22px' ?>; font-weight:700; color:#1A1008; margin:0;
}
.value-card p {
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Lato',sans-serif" ?>;
    font-size:14px; color:#6A5840; line-height:1.7; margin:0;
}

/* Steps */
.steps {
    display:flex; gap:0; position:relative; margin-bottom:80px;
    flex-direction:<?= $isRtl?'row-reverse':'row' ?>;
}
.steps::before {
    content:''; position:absolute; top:30px;
    <?= $isRtl?'right':'left' ?>:60px; <?= $isRtl?'left':'right' ?>:60px;
    height:1px; background:#DDD5C8; z-index:0;
}
.step { flex:1; text-align:center; position:relative; z-index:1; padding:0 16px; }
.step-num {
    width:60px; height:60px; border-radius:50%;
    background:#FFFDF9; border:2px solid #C4A46B;
    color:#B8924A;
    font-family:'Cormorant Garamond',serif;
    font-size:24px; font-weight:700;
    display:flex; align-items:center; justify-content:center;
    margin:0 auto 18px;
}
.step h4 {
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Cormorant Garamond',serif" ?>;
    font-size:<?= $isRtl?'16px':'18px' ?>; font-weight:700; color:#1A1008; margin:0 0 8px;
}
.step p {
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Lato',sans-serif" ?>;
    font-size:13px; color:#6A5840; line-height:1.7; margin:0;
}

/* CTA */
.cta-banner {
    background:#1A1008; border-radius:20px; padding:60px 50px;
    text-align:center; border:1px solid rgba(196,164,107,.2);
}
.cta-banner h2 {
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Cormorant Garamond',serif" ?>;
    font-size:<?= $isRtl?'32px':'42px' ?>; font-weight:700;
    color:#fff; margin:0 0 14px;
}
.cta-banner p {
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Cormorant Garamond',serif" ?>;
    font-size:<?= $isRtl?'16px':'22px' ?>; font-style:<?= $isRtl?'normal':'italic' ?>;
    color:rgba(255,255,255,.65); margin:0 0 32px; line-height:1.7;
}
.cta-btns { display:flex; gap:14px; justify-content:center; flex-wrap:wrap; }
.btn-gold {
    background:#C4A46B; color:#1A1008; padding:14px 32px;
    border-radius:10px; text-decoration:none;
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Lato',sans-serif" ?>;
    font-weight:700; font-size:15px; transition:background .2s;
}
.btn-gold:hover { background:#D4B47B; }
.btn-outline-w {
    background:transparent; color:rgba(255,255,255,.7);
    border:1px solid rgba(255,255,255,.2); padding:14px 32px;
    border-radius:10px; text-decoration:none;
    font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Lato',sans-serif" ?>;
    font-size:15px; transition:border-color .2s, color .2s;
}
.btn-outline-w:hover { border-color:#C4A46B; color:#C4A46B; }

/* Dark mode */
html.dark .about-body    { background:#1A1610; }
html.dark .stat-item     { background:#2C2418; }
html.dark .stat-item .lbl { color:#A89880; }
html.dark .feat-card,
html.dark .value-card    { background:#2C2418; border-color:#3E3228; }
html.dark .feat-card h3,
html.dark .value-card h4 { color:#F0E8D8; }
html.dark .feat-card p,
html.dark .value-card p  { color:#A89880; }
html.dark .section h2    { color:#F0E8D8; }
html.dark .section p     { color:#C4B89A; }
html.dark .step-num      { background:#2C2418; }
html.dark .step h4       { color:#F0E8D8; }
html.dark .step p        { color:#A89880; }

/* Responsive */
@media (max-width:900px) {
    .about-inner { padding:0 30px; }
    .two-col     { grid-template-columns:1fr; }
    .values-grid { grid-template-columns:1fr 1fr; }
    .stats-band  { grid-template-columns:repeat(2,1fr); }
    .about-hero-content h1 { font-size:50px; }
    .about-hero-content p  { font-size:18px; }
}
@media (max-width:600px) {
    .about-inner { padding:0 20px; }
    .values-grid { grid-template-columns:1fr; }
    .steps       { flex-direction:column; gap:24px; }
    .steps::before { display:none; }
    .about-hero-content h1 { font-size:36px; }
    .feat-card   { flex-direction:column; }
    .cta-banner  { padding:40px 24px; }
}
</style>

<div class="about-root">

    <!-- HERO -->
    <section class="about-hero-section">
        <div class="about-hero-content">
            <div class="badge-pill"><?= $p['badge'] ?></div>
            <h1><?= $p['hero_h1'] ?></h1>
            <p><?= $p['hero_p'] ?></p>
        </div>
        <div class="hero-scroll-hint">
            <div class="hero-scroll-line"></div>
            <span><?= $p['scroll'] ?></span>
        </div>
    </section>

    <!-- BODY -->
    <div class="about-body">
        <div class="about-inner">

            <!-- Stats -->
            <div class="stats-band">
                <?php
                $nb_docs     = $conn->query("SELECT COUNT(*) c FROM documents")->fetch_assoc()['c'] ?? 0;
                $nb_users    = $conn->query("SELECT COUNT(*) c FROM users WHERE role='client'")->fetch_assoc()['c'] ?? 0;
                $nb_emprunts = $conn->query("SELECT COUNT(*) c FROM emprunt")->fetch_assoc()['c'] ?? 0;
                $nb_types    = $conn->query("SELECT COUNT(*) c FROM types_documents")->fetch_assoc()['c'] ?? 0;
                ?>
                <div class="stat-item"><div class="num"><?= number_format($nb_docs) ?>+</div><div class="lbl"><?= $p['stat_docs'] ?></div></div>
                <div class="stat-item"><div class="num"><?= number_format($nb_users) ?>+</div><div class="lbl"><?= $p['stat_users'] ?></div></div>
                <div class="stat-item"><div class="num"><?= number_format($nb_emprunts) ?>+</div><div class="lbl"><?= $p['stat_loans'] ?></div></div>
                <div class="stat-item"><div class="num"><?= $nb_types ?></div><div class="lbl"><?= $p['stat_types'] ?></div></div>
            </div>

            <!-- Mission -->
            <div class="section">
                <div class="section-tag"><?= $p['mission_tag'] ?></div>
                <h2><?= $p['mission_h2'] ?></h2>
                <p><?= $p['mission_p1'] ?></p>
                <p><?= $p['mission_p2'] ?></p>
            </div>

            <div class="divider"><?= $p['div_offer'] ?></div>

            <!-- Features -->
            <div class="two-col">
                <div class="feat-card">
                    <div class="feat-icon-wrap">
                        <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><line x1="10" y1="8" x2="16" y2="8"/><line x1="10" y1="12" x2="16" y2="12"/></svg>
                    </div>
                    <div class="feat-text">
                        <h3><?= $p['feat1_h'] ?></h3>
                        <p><?= $p['feat1_p'] ?></p>
                    </div>
                </div>
                <div class="feat-card">
                    <div class="feat-icon-wrap">
                        <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    </div>
                    <div class="feat-text">
                        <h3><?= $p['feat2_h'] ?></h3>
                        <p><?= $p['feat2_p'] ?></p>
                    </div>
                </div>
                <div class="feat-card">
                    <div class="feat-icon-wrap">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </div>
                    <div class="feat-text">
                        <h3><?= $p['feat3_h'] ?></h3>
                        <p><?= $p['feat3_p'] ?></p>
                    </div>
                </div>
                <div class="feat-card">
                    <div class="feat-icon-wrap">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </div>
                    <div class="feat-text">
                        <h3><?= $p['feat4_h'] ?></h3>
                        <p><?= $p['feat4_p'] ?></p>
                    </div>
                </div>
            </div>

            <div class="divider"><?= $p['div_values'] ?></div>

            <!-- Values -->
            <div class="values-grid">
                <?php
                $vals = [
                    ['lock', 'val1'],
                    ['star', 'val2'],
                    ['shield', 'val3'],
                    ['globe', 'val4'],
                    ['book', 'val5'],
                    ['zap', 'val6'],
                ];
                $icons = [
                    'lock'   => '<svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/><circle cx="12" cy="16" r="1" fill="#C4A46B" stroke="none"/></svg>',
                    'star'   => '<svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
                    'shield' => '<svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
                    'globe'  => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
                    'book'   => '<svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
                    'zap'    => '<svg viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
                ];
                foreach ($vals as [$icon, $key]):
                ?>
                <div class="value-card">
                    <div class="value-header">
                        <div class="val-icon-wrap"><?= $icons[$icon] ?></div>
                        <h4><?= $p[$key.'_h'] ?></h4>
                    </div>
                    <p><?= $p[$key.'_p'] ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="divider"><?= $p['div_how'] ?></div>

            <!-- Steps -->
            <div class="steps">
                <?php for ($s = 1; $s <= 4; $s++): ?>
                <div class="step">
                    <div class="step-num"><?= $s ?></div>
                    <h4><?= $p['step'.$s.'_h'] ?></h4>
                    <p><?= $p['step'.$s.'_p'] ?></p>
                </div>
                <?php endfor; ?>
            </div>

            <!-- CTA -->
            <div class="cta-banner">
                <h2><?= $p['cta_h2'] ?></h2>
                <p><?= $p['cta_p'] ?></p>
                <div class="cta-btns">
                    <a href="/MEMOIR/client/library.php" class="btn-gold"><?= $p['cta_btn1'] ?></a>
                    <?php if (!isset($_SESSION['id_user'])): ?>
                    <a href="/MEMOIR/auth/signup.php" class="btn-outline-w"><?= $p['cta_btn2'] ?></a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>