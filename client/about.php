<?php
include "../includes/header.php";
?>
<title><?= t('about') ?> — AuraLibre</title>

<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Lato:wght@300;400;700&family=Tajawal:wght@400;700&display=swap" rel="stylesheet">

<style>

/* ═══════════════════════════════════════════════════════
   GLOBAL PAGE RESET — no gap after header
═══════════════════════════════════════════════════════ */
.about-root {
    width: 100%;
    margin: 0;
    padding: 0;
}

.about-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 60px;
}

/* ═══════════════════════════════════════════════════════
   HERO
═══════════════════════════════════════════════════════ */
.about-hero-section {
    position: relative;
    width: 100%;
    height: 100vh;
    min-height: 640px;
    max-height: 900px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    isolation: isolate;
    margin: 0;
}

.about-hero-section::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url('../assets/images/hero-librarian.jpg');
    background-size: cover;
    background-position: center center;
    filter: blur(3px) brightness(0.48) saturate(0.7);
    transform: scale(1.05);
    z-index: 0;
}

.about-hero-section::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom,
        rgba(10,6,2,0.35) 0%,
        rgba(20,12,4,0.58) 70%,
        rgba(26,16,8,0.75) 100%
    );
    z-index: 1;
}

.about-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    padding: 0 40px;
    max-width: 860px;
}

.about-hero-content .badge-pill {
    display: inline-block;
    background: rgba(196,164,107,0.14);
    border: 1px solid rgba(196,164,107,0.5);
    color: #E2C07A;
    font-family: 'Lato', sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 3.5px;
    text-transform: uppercase;
    padding: 8px 24px;
    border-radius: 30px;
    margin-bottom: 32px;
    backdrop-filter: blur(10px);
}

.about-hero-content h1 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 76px;
    font-weight: 700;
    line-height: 1.08;
    color: #FFFFFF;
    margin: 0 0 26px;
    letter-spacing: -1.5px;
    text-shadow: 0 3px 30px rgba(0,0,0,0.65);
}
.about-hero-content h1 em { color: #D4A853; font-style: italic; }

.about-hero-content p {
    font-family: 'Cormorant Garamond', serif;
    font-size: 24px;
    font-weight: 400;
    font-style: italic;
    color: #F0E6CC;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.7;
    text-shadow: 0 2px 14px rgba(0,0,0,0.65);
}

.hero-scroll-hint {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.hero-scroll-hint span {
    font-family: 'Lato', sans-serif;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.35);
}
.hero-scroll-line {
    width: 1px;
    height: 44px;
    background: linear-gradient(to bottom, rgba(196,164,107,0.85), transparent);
    animation: pulse 2.2s ease-in-out infinite;
}
@keyframes pulse { 0%,100% { opacity: 0.4; } 50% { opacity: 1; } }

/* ═══════════════════════════════════════════════════════
   BODY
═══════════════════════════════════════════════════════ */
.about-body {
    background: var(--page-bg, #F5F0E8);
    padding: 80px 0;
}

.stats-band {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1px;
    background: #DDD5C8;
    border: 1px solid #DDD5C8;
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 80px;
}
.stat-item { background: #FFFDF9; padding: 36px 24px; text-align: center; }
.stat-item .num {
    font-family: 'Cormorant Garamond', serif;
    font-size: 52px;
    font-weight: 600;
    color: #B8924A;
    line-height: 1;
    margin-bottom: 8px;
    letter-spacing: -1px;
}
.stat-item .lbl {
    font-family: 'Lato', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #7A6A55;
}

.section { margin-bottom: 80px; }
.section-tag {
    font-family: 'Lato', sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #B8924A;
    margin-bottom: 14px;
}
.section h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 46px;
    font-weight: 700;
    color: #1A1008;
    margin: 0 0 20px;
    letter-spacing: -0.5px;
    line-height: 1.1;
}
.section p {
    font-family: 'Lato', sans-serif;
    font-size: 16px;
    color: #5C4A30;
    line-height: 1.9;
    margin-bottom: 16px;
    max-width: 760px;
}

.divider {
    display: flex;
    align-items: center;
    gap: 18px;
    margin: 0 0 50px;
    color: #9A8470;
    font-family: 'Lato', sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 2.5px;
    text-transform: uppercase;
}
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #DDD5C8; }

/* ══════════════════════════════════════════════════════
   FEAT CARDS — icône SVG luxury (cercle doré comme dashboard)
══════════════════════════════════════════════════════ */
.two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px;
    margin-bottom: 80px;
}
.feat-card {
    background: #FFFDF9;
    border: 1px solid #DDD5C8;
    border-radius: 16px;
    padding: 32px 28px;
    border-top: 3px solid transparent;
    transition: border-color .2s, transform .2s;
    display: flex;
    align-items: flex-start;
    gap: 22px;
}
.feat-card:hover { border-top-color: #C4A46B; transform: translateY(-4px); }

/* Icône SVG luxury — même style que dashboard */
.feat-icon-wrap {
    width: 56px;
    height: 56px;
    min-width: 56px;
    border-radius: 50%;
    background: rgba(196,164,107,.1);
    border: 1px solid rgba(196,164,107,.28);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background .2s;
}
.feat-card:hover .feat-icon-wrap { background: rgba(196,164,107,.2); }
.feat-icon-wrap svg {
    width: 22px;
    height: 22px;
    stroke: #C4A46B;
    fill: none;
    stroke-width: 1.7;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.feat-text { flex: 1; }
.feat-card h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
    font-weight: 700;
    color: #1A1008;
    margin: 0 0 8px;
    letter-spacing: -0.2px;
}
.feat-card p {
    font-family: 'Lato', sans-serif;
    font-size: 14px;
    color: #6A5840;
    line-height: 1.75;
    margin: 0;
}

/* ══════════════════════════════════════════════════════
   VALUES GRID — icône SVG luxury
══════════════════════════════════════════════════════ */
.values-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 80px;
}
.value-card {
    background: #FFFDF9;
    border: 1px solid #DDD5C8;
    border-radius: 14px;
    padding: 28px 24px;
    transition: transform .2s, border-color .2s;
}
.value-card:hover { transform: translateY(-3px); border-color: rgba(196,164,107,.5); }

.value-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 12px;
}

/* Icône value — même cercle doré */
.val-icon-wrap {
    width: 46px;
    height: 46px;
    min-width: 46px;
    border-radius: 50%;
    background: rgba(196,164,107,.1);
    border: 1px solid rgba(196,164,107,.25);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background .2s;
}
.value-card:hover .val-icon-wrap { background: rgba(196,164,107,.2); }
.val-icon-wrap svg {
    width: 20px;
    height: 20px;
    stroke: #C4A46B;
    fill: none;
    stroke-width: 1.7;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.value-card h4 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
    font-weight: 700;
    color: #1A1008;
    margin: 0;
    letter-spacing: -0.2px;
}
.value-card p {
    font-family: 'Lato', sans-serif;
    font-size: 14px;
    color: #6A5840;
    line-height: 1.7;
    margin: 0;
}

/* ── Steps ── */
.steps {
    display: flex;
    gap: 0;
    position: relative;
    margin-bottom: 80px;
}
.steps::before {
    content: '';
    position: absolute;
    top: 30px;
    left: 60px;
    right: 60px;
    height: 1px;
    background: #DDD5C8;
    z-index: 0;
}
.step { flex: 1; text-align: center; position: relative; z-index: 1; padding: 0 16px; }
.step-num {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #FFFDF9;
    border: 2px solid #C4A46B;
    color: #B8924A;
    font-family: 'Cormorant Garamond', serif;
    font-size: 24px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
}
.step h4 { font-family: 'Cormorant Garamond', serif; font-size: 18px; font-weight: 700; color: #1A1008; margin: 0 0 8px; }
.step p  { font-family: 'Lato', sans-serif; font-size: 13px; color: #6A5840; line-height: 1.7; margin: 0; }

/* ── CTA Banner ── */
.cta-banner {
    background: #1A1008;
    border-radius: 20px;
    padding: 60px 50px;
    text-align: center;
    border: 1px solid rgba(196,164,107,0.2);
}
.cta-banner h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 42px;
    font-weight: 700;
    color: #FFFFFF;
    margin: 0 0 14px;
    letter-spacing: -0.5px;
}
.cta-banner p {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
    font-style: italic;
    color: rgba(255,255,255,0.65);
    margin: 0 0 32px;
    line-height: 1.7;
}
.cta-btns { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
.btn-gold { background: #C4A46B; color: #1A1008; padding: 14px 32px; border-radius: 10px; text-decoration: none; font-family: 'Lato', sans-serif; font-weight: 700; font-size: 15px; transition: background .2s; }
.btn-gold:hover { background: #D4B47B; }
.btn-outline-w { background: transparent; color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.2); padding: 14px 32px; border-radius: 10px; text-decoration: none; font-family: 'Lato', sans-serif; font-size: 15px; transition: border-color .2s, color .2s; }
.btn-outline-w:hover { border-color: #C4A46B; color: #C4A46B; }

/* ── Dark mode overrides ── */
html.dark .about-body    { background: #1A1610; }
html.dark .stat-item     { background: #2C2418; }
html.dark .stat-item .lbl { color: #A89880; }
html.dark .feat-card,
html.dark .value-card    { background: #2C2418; border-color: #3E3228; }
html.dark .feat-card h3,
html.dark .value-card h4 { color: #F0E8D8; }
html.dark .feat-card p,
html.dark .value-card p  { color: #A89880; }
html.dark .section h2    { color: #F0E8D8; }
html.dark .section p     { color: #C4B89A; }
html.dark .step-num      { background: #2C2418; }
html.dark .step h4       { color: #F0E8D8; }
html.dark .step p        { color: #A89880; }

/* ── Responsive ── */
@media (max-width: 900px) {
    .about-inner { padding: 0 30px; }
    .two-col     { grid-template-columns: 1fr; }
    .values-grid { grid-template-columns: 1fr 1fr; }
    .stats-band  { grid-template-columns: repeat(2, 1fr); }
    .about-hero-content h1 { font-size: 50px; }
    .about-hero-content p  { font-size: 20px; }
}
@media (max-width: 600px) {
    .about-inner { padding: 0 20px; }
    .values-grid { grid-template-columns: 1fr; }
    .steps       { flex-direction: column; gap: 24px; }
    .steps::before { display: none; }
    .about-hero-content h1 { font-size: 38px; }
    .feat-card   { flex-direction: column; }
    .cta-banner  { padding: 40px 24px; }
}
</style>

<div class="about-root">

    <!-- ══ HERO ══ -->
    <section class="about-hero-section">
        <div class="about-hero-content">
            <div class="badge-pill">✦ À propos de nous</div>
            <h1>La bibliothèque<br>de <em>demain</em>, aujourd'hui</h1>
            <p>AuraLibre est une plateforme numérique dédiée à rendre la lecture accessible — emprunter gratuitement ou acheter en quelques clics, depuis n'importe où.</p>
        </div>
        <div class="hero-scroll-hint">
            <div class="hero-scroll-line"></div>
            <span>Découvrir</span>
        </div>
    </section>

    <!-- ══ BODY ══ -->
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
                <div class="stat-item"><div class="num"><?= number_format($nb_docs) ?>+</div><div class="lbl">Documents</div></div>
                <div class="stat-item"><div class="num"><?= number_format($nb_users) ?>+</div><div class="lbl">Lecteurs</div></div>
                <div class="stat-item"><div class="num"><?= number_format($nb_emprunts) ?>+</div><div class="lbl">Emprunts réalisés</div></div>
                <div class="stat-item"><div class="num"><?= $nb_types ?></div><div class="lbl">Types de documents</div></div>
            </div>

            <!-- Mission -->
            <div class="section">
                <div class="section-tag">Notre mission</div>
                <h2>Démocratiser l'accès au savoir</h2>
                <p>AuraLibre est né d'une conviction simple : chaque personne mérite un accès facile, rapide et élégant aux livres, thèses, articles et documents académiques.</p>
                <p>En combinant emprunt gratuit et achat en ligne, nous offrons une expérience complète qui s'adapte à tous les besoins — étudiant, chercheur, ou simple passionné de lecture.</p>
            </div>

            <div class="divider">Ce que nous proposons</div>

            <!-- ══════════════════════════════════════════
                 FEAT CARDS — icônes SVG luxury doré
            ══════════════════════════════════════════ -->
            <div class="two-col">

                <!-- Emprunt gratuit -->
                <div class="feat-card">
                    <div class="feat-icon-wrap">
                        <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><line x1="10" y1="8" x2="16" y2="8"/><line x1="10" y1="12" x2="16" y2="12"/><line x1="10" y1="16" x2="13" y2="16"/></svg>
                    </div>
                    <div class="feat-text">
                        <h3>Emprunt gratuit &amp; simple</h3>
                        <p>Empruntez n'importe quel document disponible pour 14 jours, gratuitement. Renouvelez en un clic si vous avez besoin de plus de temps.</p>
                    </div>
                </div>

                <!-- Achat en ligne -->
                <div class="feat-card">
                    <div class="feat-icon-wrap">
                        <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    </div>
                    <div class="feat-text">
                        <h3>Achat en ligne sécurisé</h3>
                        <p>Achetez vos livres préférés directement depuis le catalogue. Ajoutez au panier, validez votre commande et suivez sa livraison.</p>
                    </div>
                </div>

                <!-- Recherche -->
                <div class="feat-card">
                    <div class="feat-icon-wrap">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </div>
                    <div class="feat-text">
                        <h3>Recherche intelligente</h3>
                        <p>Trouvez n'importe quel document par titre, auteur, ISBN ou catégorie. Les résultats apparaissent instantanément.</p>
                    </div>
                </div>

                <!-- Multilingue + dark mode -->
                <div class="feat-card">
                    <div class="feat-icon-wrap">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </div>
                    <div class="feat-text">
                        <h3>Interface multilingue &amp; dark mode</h3>
                        <p>Disponible en français, anglais et arabe. Le mode sombre s'active en un clic et reste mémorisé entre vos visites.</p>
                    </div>
                </div>

            </div>

            <div class="divider">Nos valeurs</div>

            <!-- ══════════════════════════════════════════
                 VALUES GRID — icônes SVG luxury doré
            ══════════════════════════════════════════ -->
            <div class="values-grid">

                <!-- Accessibilité -->
                <div class="value-card">
                    <div class="value-header">
                        <div class="val-icon-wrap">
                            <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/><circle cx="12" cy="16" r="1" fill="#C4A46B" stroke="none"/></svg>
                        </div>
                        <h4>Accessibilité</h4>
                    </div>
                    <p>Le savoir ne doit pas avoir de barrière. Emprunt gratuit pour tous.</p>
                </div>

                <!-- Excellence -->
                <div class="value-card">
                    <div class="value-header">
                        <div class="val-icon-wrap">
                            <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <h4>Excellence</h4>
                    </div>
                    <p>Une interface soignée, rapide et pensée pour l'expérience utilisateur.</p>
                </div>

                <!-- Sécurité -->
                <div class="value-card">
                    <div class="value-header">
                        <div class="val-icon-wrap">
                            <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <h4>Sécurité</h4>
                    </div>
                    <p>Vos données personnelles sont protégées et ne sont jamais partagées.</p>
                </div>

                <!-- Inclusion -->
                <div class="value-card">
                    <div class="value-header">
                        <div class="val-icon-wrap">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        </div>
                        <h4>Inclusion</h4>
                    </div>
                    <p>Disponible en 3 langues pour toucher le plus grand nombre.</p>
                </div>

                <!-- Diversité -->
                <div class="value-card">
                    <div class="value-header">
                        <div class="val-icon-wrap">
                            <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        </div>
                        <h4>Diversité</h4>
                    </div>
                    <p>Livres, thèses, articles, journaux — tout type de document en un seul endroit.</p>
                </div>

                <!-- Rapidité -->
                <div class="value-card">
                    <div class="value-header">
                        <div class="val-icon-wrap">
                            <svg viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        </div>
                        <h4>Rapidité</h4>
                    </div>
                    <p>Empruntez ou achetez en moins de 3 clics, sans file d'attente.</p>
                </div>

            </div>

            <div class="divider">Comment ça marche</div>

            <!-- Steps -->
            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <h4>Créez votre compte</h4>
                    <p>Inscription gratuite en moins d'une minute. Nom, email, mot de passe — c'est tout.</p>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <h4>Parcourez le catalogue</h4>
                    <p>Des milliers de documents filtrables par type, thème ou disponibilité.</p>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <h4>Empruntez ou achetez</h4>
                    <p>Choisissez votre mode selon le document. Emprunt gratuit ou achat sécurisé.</p>
                </div>
                <div class="step">
                    <div class="step-num">4</div>
                    <h4>Suivez &amp; gérez</h4>
                    <p>Votre tableau de bord affiche vos emprunts, retours et commandes en temps réel.</p>
                </div>
            </div>

            <!-- CTA -->
            <div class="cta-banner">
                <h2>Prêt à explorer notre catalogue ?</h2>
                <p>Rejoignez des centaines de lecteurs qui font confiance à AuraLibre chaque jour.</p>
                <div class="cta-btns">
                    <a href="/MEMOIR/client/library.php" class="btn-gold">Explorer le catalogue</a>
                    <?php if (!isset($_SESSION['id_user'])): ?>
                    <a href="/MEMOIR/auth/signup.php" class="btn-outline-w">Créer un compte gratuit</a>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /about-inner -->
    </div><!-- /about-body -->

</div><!-- /about-root -->

<?php include "../includes/footer.php"; ?>