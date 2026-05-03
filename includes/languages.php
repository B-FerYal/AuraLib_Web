<?php
/* ════════════════════════════════════════════════════════
   AURALIB — includes/languages.php
   Système de traduction global — FR / EN / AR
   Inclus automatiquement via includes/header.php
   Couvre : navbar, library, profile, panier, emprunts,
            commandes, notifications, auth, admin, footer
════════════════════════════════════════════════════════ */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$translations = [

    /* ══════════════════════════════════════════
       FRANÇAIS
    ══════════════════════════════════════════ */
    'fr' => [

        // ── Navbar ──────────────────────────
        'home'        => 'Accueil',
        'borrowed'    => 'Mes Emprunts',
        'purchased'   => 'Mes Achats',
        'about'       => 'À propos',
        'contact'     => 'Contact',
        'cart'        => 'Panier',
        'profile'     => 'Mon profil',
        'settings'    => 'Paramètres',
        'logout'      => 'Déconnexion',
        'login'       => 'Connexion',
        'signup'      => 'S\'inscrire',
        'dashboard'   => 'Dashboard',
        'hello'       => 'Bienvenue',

        // ── Library / Catalogue ─────────────
        'hero_title'          => 'Notre <em>Catalogue</em>',
        'hero_subtitle'       => 'Empruntez gratuitement ou achetez — trouvez le livre qu\'il vous faut.',
        'search_placeholder'  => 'Titre, auteur, catégorie...',
        'all_themes'          => 'Tous les thèmes',
        'all'                 => 'Tous',
        'to_borrow'           => 'Emprunt',
        'to_buy'              => 'Achat',
        'both'                => 'Les deux',
        'borrow_btn'          => '📖 Emprunter',
        'buy_btn'             => '🛒 Ajouter au panier',
        'view_btn'            => '🔍 Voir le livre',
        'no_results'          => 'Aucun livre trouvé.',
        'by_author'           => 'Par :',
        'available'           => 'Disponible',
        'unavailable'         => 'Indisponible',
        'free'                => 'Gratuit',

        // ── Modal choix ─────────────────────
        'modal_text'           => 'Cet ouvrage est disponible à l\'emprunt <strong>et</strong> à l\'achat. Choisissez votre option :',
        'modal_borrow_title'   => 'Emprunter',
        'modal_buy_title'      => 'Acheter',
        'modal_borrow_detail'  => 'Gratuit · 14 jours',
        'modal_confirm_borrow' => 'Emprunter maintenant',
        'close'                => '✕ Fermer',

        // ── Profil ──────────────────────────
        'my_profile'          => 'Mon profil',
        'profile_title'       => 'Tableau de bord',
        'edit_info'           => 'Modifier mes informations',
        'change_password'     => 'Changer le mot de passe',
        'firstname'           => 'Prénom',
        'lastname'            => 'Nom de famille',
        'email'               => 'Adresse email',
        'phone'               => 'Téléphone',
        'gender'              => 'Genre',
        'member_since'        => 'Membre depuis',
        'role_reader'         => 'Lecteur',
        'role_admin'          => 'Administrateur',
        'save'                => 'Enregistrer',
        'current_password'    => 'Mot de passe actuel',
        'new_password'        => 'Nouveau mot de passe',
        'confirm_password'    => 'Confirmer le mot de passe',
        'update_success'      => 'Informations mises à jour avec succès !',
        'password_success'    => 'Mot de passe modifié avec succès !',
        'error_required'      => 'Ce champ est obligatoire.',
        'error_email'         => 'Adresse email invalide.',
        'error_email_taken'   => 'Cette adresse email est déjà utilisée.',
        'error_password'      => 'Mot de passe actuel incorrect.',
        'error_match'         => 'Les mots de passe ne correspondent pas.',
        'error_short'         => 'Minimum 6 caractères.',
// ── Stats profil ────────────────────
        'total_loans'         => 'Emprunts total',
        'active_loans'        => 'Emprunts actifs',
        'returned'            => 'Retournés',
        'late'                => 'En retard',
        'total_orders'        => 'Commandes',
        'total_spent'         => 'Total achats',

        // ── Onglets profil ──────────────────
        'tab_dashboard'       => 'Tableau de bord',
        'tab_profile'         => 'Mon profil',
        'tab_settings'        => 'Paramètres',
        'tab_history'         => 'Historique',
        'tab_orders'          => 'Commandes récentes',

        // ── Emprunts ────────────────────────
        'my_loans'            => 'Mes emprunts',
        'loan_date'           => 'Date d\'emprunt',
        'return_date'         => 'Date de retour',
        'status'              => 'Statut',
        'status_active'       => 'En cours',
        'status_returned'     => 'Retourné',
        'status_late'         => 'En retard',
        'days_left'           => 'jours restants',
        'overdue'             => 'En retard',
        'renew'               => 'Renouveler',
        'return_book'         => 'Retourner',
        'no_loans'            => 'Aucun emprunt enregistré',
        'back_to_library'     => '← Retour à la bibliothèque',

        // ── Panier & Commandes ──────────────
        'my_cart'             => 'Mon panier',
        'empty_cart'          => 'Votre panier est vide.',
        'quantity'            => 'Quantité',
        'unit_price'          => 'Prix unitaire',
        'subtotal'            => 'Sous-total',
        'total'               => 'Total',
        'checkout'            => 'Passer la commande',
        'remove'              => 'Supprimer',
        'continue_shopping'   => 'Continuer mes achats',
        'order_confirmed'     => 'Commande confirmée !',
        'order_number'        => 'Commande n°',
        'payment_method'      => 'Mode de paiement',
        'order_status_pending'  => 'En attente',
        'order_status_paid'     => 'Payée',
        'order_status_cancelled'=> 'Annulée',

        // ── Notifications ───────────────────
        'my_notifications'    => 'Mes notifications',
        'no_notifications'    => 'Aucune notification',
        'mark_read'           => 'Marquer comme lu',
        'mark_all_read'       => 'Tout marquer comme lu',
        'delete_notif'        => 'Supprimer',
        'notif_all'           => 'Toutes',
        'notif_unread'        => 'Non lues',
        'notif_late'          => 'Retards',
        'notif_reminder'      => 'Rappels',
        'notif_order'         => 'Commandes',
        'new_notif'           => 'nouvelle',
        'new_notifs'          => 'nouvelles',

        // ── Auth ────────────────────────────
        'login_title'         => 'Connexion',
        'signup_title'        => 'Créer un compte',
        'forgot_password'     => 'Mot de passe oublié ?',
        'reset_password'      => 'Réinitialiser le mot de passe',
        'no_account'          => 'Pas encore de compte ?',
        'already_account'     => 'Déjà un compte ?',
        'password'            => 'Mot de passe',
        'remember_me'         => 'Se souvenir de moi',
        'send_reset'          => 'Envoyer le lien',
        'error_login'         => 'Email ou mot de passe incorrect.',

        // ── Admin ────────────────────────────
        'admin_dashboard'     => 'Tableau de bord',
        'manage_books'        => 'Gestion des livres',
        'manage_orders'       => 'Commandes',
        'manage_loans'        => 'Gestion emprunts',
        'manage_users'        => 'Utilisateurs',
        'add_book'            => 'Ajouter un livre',
        'edit_book'           => 'Modifier',
        'delete_book'         => 'Supprimer',
        'total_books'         => 'Livres',
        'total_users'         => 'Lecteurs',
        'all_orders'          => 'Toutes les commandes',
        'validated'           => 'Validée',
        'pending'             => 'En attente',
        'cancelled'           => 'Annulée',
// ── Messages généraux ───────────────
        'loading'             => 'Chargement...',
        'confirm_delete'      => 'Êtes-vous sûr de vouloir supprimer ?',
        'yes'                 => 'Oui',
        'no'                  => 'Non',
        'cancel'              => 'Annuler',
        'confirm'             => 'Confirmer',
        'back'                => 'Retour',
        'see_all'             => 'Voir tout',
        'quick_actions'       => 'Actions rapides',
        'welcome_back'        => 'Bon retour',
        'select'              => '— Sélectionner —',
        'male'                => 'Homme',
        'female'              => 'Femme',
        'other'               => 'Autre',
    ],

    /* ══════════════════════════════════════════
       ENGLISH
    ══════════════════════════════════════════ */
    'en' => [

        // ── Navbar ──────────────────────────
        'home'        => 'Home',
        'borrowed'    => 'My Loans',
        'purchased'   => 'My Purchases',
        'about'       => 'About',
        'contact'     => 'Contact',
        'cart'        => 'Cart',
        'profile'     => 'My Profile',
        'settings'    => 'Settings',
        'logout'      => 'Logout',
        'login'       => 'Login',
        'signup'      => 'Sign up',
        'dashboard'   => 'Dashboard',
        'hello'       => 'Welcome',

        // ── Library / Catalogue ─────────────
        'hero_title'          => 'Our <em>Catalogue</em>',
        'hero_subtitle'       => 'Borrow for free or buy — find the book you need.',
        'search_placeholder'  => 'Title, author, category...',
        'all_themes'          => 'All themes',
        'all'                 => 'All',
        'to_borrow'           => 'Borrow',
        'to_buy'              => 'Buy',
        'both'                => 'Both',
        'borrow_btn'          => '📖 Borrow',
        'buy_btn'             => '🛒 Add to Cart',
        'view_btn'            => '🔍 View Book',
        'no_results'          => 'No books found.',
        'by_author'           => 'By:',
        'available'           => 'Available',
        'unavailable'         => 'Unavailable',
        'free'                => 'Free',

        // ── Modal ───────────────────────────
        'modal_text'           => 'This book is available for borrowing <strong>and</strong> purchasing. Choose an option:',
        'modal_borrow_title'   => 'Borrow',
        'modal_buy_title'      => 'Buy',
        'modal_borrow_detail'  => 'Free · 14 days',
        'modal_confirm_borrow' => 'Borrow now',
        'close'                => '✕ Close',

        // ── Profile ─────────────────────────
        'my_profile'          => 'My Profile',
        'profile_title'       => 'Dashboard',
        'edit_info'           => 'Edit my information',
        'change_password'     => 'Change password',
        'firstname'           => 'First name',
        'lastname'            => 'Last name',
        'email'               => 'Email address',
        'phone'               => 'Phone',
        'gender'              => 'Gender',
        'member_since'        => 'Member since',
        'role_reader'         => 'Reader',
        'role_admin'          => 'Administrator',
        'save'                => 'Save',
        'current_password'    => 'Current password',
        'new_password'        => 'New password',
        'confirm_password'    => 'Confirm password',
        'update_success'      => 'Information updated successfully!',
        'password_success'    => 'Password changed successfully!',
        'error_required'      => 'This field is required.',
        'error_email'         => 'Invalid email address.',
        'error_email_taken'   => 'This email is already in use.',
        'error_password'      => 'Current password is incorrect.',
        'error_match'         => 'Passwords do not match.',
        'error_short'         => 'Minimum 6 characters.',
// ── Stats ───────────────────────────
        'total_loans'         => 'Total loans',
        'active_loans'        => 'Active loans',
        'returned'            => 'Returned',
        'late'                => 'Late',
        'total_orders'        => 'Orders',
        'total_spent'         => 'Total spent',

        // ── Tabs ────────────────────────────
        'tab_dashboard'       => 'Dashboard',
        'tab_profile'         => 'My profile',
        'tab_settings'        => 'Settings',
        'tab_history'         => 'History',
        'tab_orders'          => 'Recent orders',

        // ── Loans ───────────────────────────
        'my_loans'            => 'My loans',
        'loan_date'           => 'Loan date',
        'return_date'         => 'Return date',
        'status'              => 'Status',
        'status_active'       => 'Active',
        'status_returned'     => 'Returned',
        'status_late'         => 'Overdue',
        'days_left'           => 'days left',
        'overdue'             => 'Overdue',
        'renew'               => 'Renew',
        'return_book'         => 'Return',
        'no_loans'            => 'No loans recorded',
        'back_to_library'     => '← Back to library',

        // ── Cart & Orders ───────────────────
        'my_cart'             => 'My cart',
        'empty_cart'          => 'Your cart is empty.',
        'quantity'            => 'Quantity',
        'unit_price'          => 'Unit price',
        'subtotal'            => 'Subtotal',
        'total'               => 'Total',
        'checkout'            => 'Place order',
        'remove'              => 'Remove',
        'continue_shopping'   => 'Continue shopping',
        'order_confirmed'     => 'Order confirmed!',
        'order_number'        => 'Order #',
        'payment_method'      => 'Payment method',
        'order_status_pending'   => 'Pending',
        'order_status_paid'      => 'Paid',
        'order_status_cancelled' => 'Cancelled',

        // ── Notifications ───────────────────
        'my_notifications'    => 'My notifications',
        'no_notifications'    => 'No notifications',
        'mark_read'           => 'Mark as read',
        'mark_all_read'       => 'Mark all as read',
        'delete_notif'        => 'Delete',
        'notif_all'           => 'All',
        'notif_unread'        => 'Unread',
        'notif_late'          => 'Late returns',
        'notif_reminder'      => 'Reminders',
        'notif_order'         => 'Orders',
        'new_notif'           => 'new',
        'new_notifs'          => 'new',

        // ── Auth ────────────────────────────
        'login_title'         => 'Login',
        'signup_title'        => 'Create account',
        'forgot_password'     => 'Forgot password?',
        'reset_password'      => 'Reset password',
        'no_account'          => 'No account yet?',
        'already_account'     => 'Already have an account?',
        'password'            => 'Password',
        'remember_me'         => 'Remember me',
        'send_reset'          => 'Send link',
        'error_login'         => 'Incorrect email or password.',

        // ── Admin ───────────────────────────
        'admin_dashboard'     => 'Dashboard',
        'manage_books'        => 'Manage books',
        'manage_orders'       => 'Orders',
        'manage_loans'        => 'Manage loans',
        'manage_users'        => 'Users',
        'add_book'            => 'Add a book',
        'edit_book'           => 'Edit',
        'delete_book'         => 'Delete',
        'total_books'         => 'Books',
        'total_users'         => 'Readers',
        'all_orders'          => 'All orders',
        'validated'           => 'Validated',
        'pending'             => 'Pending',
        'cancelled'           => 'Cancelled',
// ── General ─────────────────────────
        'loading'             => 'Loading...',
        'confirm_delete'      => 'Are you sure you want to delete?',
        'yes'                 => 'Yes',
        'no'                  => 'No',
        'cancel'              => 'Cancel',
        'confirm'             => 'Confirm',
        'back'                => 'Back',
        'see_all'             => 'See all',
        'quick_actions'       => 'Quick actions',
        'welcome_back'        => 'Welcome back',
        'select'              => '— Select —',
        'male'                => 'Male',
        'female'              => 'Female',
        'other'               => 'Other',
    ],

    /* ══════════════════════════════════════════
       ARABIC — العربية
    ══════════════════════════════════════════ */
    'ar' => [

        // ── Navbar ──────────────────────────
        'home'        => 'الرئيسية',
        'borrowed'    => 'استعاراتي',
        'purchased'   => 'مشترياتي',
        'about'       => 'حول الموقع',
        'contact'     => 'اتصل بنا',
        'cart'        => 'السلة',
        'profile'     => 'ملفي الشخصي',
        'settings'    => 'الإعدادات',
        'logout'      => 'خروج',
        'login'       => 'دخول',
        'signup'      => 'تسجيل',
        'dashboard'   => 'لوحة التحكم',
        'hello'       => 'أهلاً',
        

        // ── Library ─────────────────────────
        'hero_title'          => '<em>كتالوج</em> الكتب',
        'hero_subtitle'       => 'استعر مجاناً أو اشتري — ابحث عن كتابك المفضل.',
        'search_placeholder'  => 'عنوان، مؤلف، تصنيف...',
        'all_themes'          => 'كل المواضيع',
        'all'                 => 'الكل',
        'to_borrow'           => 'إعارة',
        'to_buy'              => 'شراء',
        'both'                => 'كلاهما',
        'borrow_btn'          => '📖 استعارة',
        'buy_btn'             => '🛒 أضف للسلة',
        'view_btn'            => '🔍 عرض الكتاب',
        'no_results'          => 'لا توجد كتب.',
        'by_author'           => 'بقلم:',
        'available'           => 'متاح',
        'unavailable'         => 'غير متاح',
        'free'                => 'مجاني',

        // ── Modal ───────────────────────────
        'modal_text'           => 'هذا الكتاب متاح للاستعارة <strong>و</strong> الشراء. اختر ما يناسبك:',
        'modal_borrow_title'   => 'استعارة',
        'modal_buy_title'      => 'شراء',
        'modal_borrow_detail'  => 'مجاني · 14 يوم',
        'modal_confirm_borrow' => 'استعر الآن',
        'close'                => '✕ إغلاق',

        // ── Profile ─────────────────────────
        'my_profile'          => 'ملفي الشخصي',
        'profile_title'       => 'لوحة القيادة',
        'edit_info'           => 'تعديل معلوماتي',
        'change_password'     => 'تغيير كلمة المرور',
        'firstname'           => 'الاسم الأول',
        'lastname'            => 'اسم العائلة',
        'email'               => 'البريد الإلكتروني',
        'phone'               => 'الهاتف',
        'gender'              => 'الجنس',
        'member_since'        => 'عضو منذ',
        'role_reader'         => 'قارئ',
        'role_admin'          => 'مشرف',
        'save'                => 'حفظ',
        'current_password'    => 'كلمة المرور الحالية',
        'new_password'        => 'كلمة المرور الجديدة',
        'confirm_password'    => 'تأكيد كلمة المرور',
        'update_success'      => 'تم تحديث المعلومات بنجاح!',
        'password_success'    => 'تم تغيير كلمة المرور بنجاح!',
        'error_required'      => 'هذا الحقل مطلوب.',
        'error_email'         => 'البريد الإلكتروني غير صالح.',
        'error_email_taken'   => 'البريد الإلكتروني مستخدم.',
        'error_password'      => 'كلمة المرور الحالية غير صحيحة.',
        'error_match'         => 'كلمتا المرور غير متطابقتين.',
        'error_short'         => 'الحد الأدنى 6 أحرف.',
// ── Stats ───────────────────────────
        'total_loans'         => 'مجموع الاستعارات',
        'active_loans'        => 'استعارات نشطة',
        'returned'            => 'مُعادة',
        'late'                => 'متأخرة',
        'total_orders'        => 'الطلبات',
        'total_spent'         => 'مجموع المشتريات',

        // ── Tabs ────────────────────────────
        'tab_dashboard'       => 'لوحة القيادة',
        'tab_profile'         => 'ملفي',
        'tab_settings'        => 'الإعدادات',
        'tab_history'         => 'السجل',
        'tab_orders'          => 'آخر الطلبات',

        // ── Loans ───────────────────────────
        'my_loans'            => 'استعاراتي',
        'loan_date'           => 'تاريخ الاستعارة',
        'return_date'         => 'تاريخ الإعادة',
        'status'              => 'الحالة',
        'status_active'       => 'جارية',
        'status_returned'     => 'مُعادة',
        'status_late'         => 'متأخرة',
        'days_left'           => 'أيام متبقية',
        'overdue'             => 'متأخر',
        'renew'               => 'تجديد',
        'return_book'         => 'إعادة',
        'no_loans'            => 'لا توجد استعارات',
        'back_to_library'     => '→ العودة للمكتبة',

        // ── Cart & Orders ───────────────────
        'my_cart'             => 'سلتي',
        'empty_cart'          => 'سلتك فارغة.',
        'quantity'            => 'الكمية',
        'unit_price'          => 'سعر الوحدة',
        'subtotal'            => 'المجموع الجزئي',
        'total'               => 'المجموع',
        'checkout'            => 'تأكيد الطلب',
        'remove'              => 'حذف',
        'continue_shopping'   => 'مواصلة التسوق',
        'order_confirmed'     => 'تم تأكيد الطلب!',
        'order_number'        => 'طلب رقم',
        'payment_method'      => 'طريقة الدفع',
        'order_status_pending'   => 'قيد الانتظار',
        'order_status_paid'      => 'مدفوع',
        'order_status_cancelled' => 'ملغى',

        // ── Notifications ───────────────────
        'my_notifications'    => 'إشعاراتي',
        'no_notifications'    => 'لا توجد إشعارات',
        'mark_read'           => 'تعليم كمقروء',
        'mark_all_read'       => 'تعليم الكل كمقروء',
        'delete_notif'        => 'حذف',
        'notif_all'           => 'الكل',
        'notif_unread'        => 'غير مقروءة',
        'notif_late'          => 'التأخيرات',
        'notif_reminder'      => 'التذكيرات',
        'notif_order'         => 'الطلبات',
        'new_notif'           => 'جديد',
        'new_notifs'          => 'جديدة',

        // ── Auth ────────────────────────────
        'login_title'         => 'تسجيل الدخول',
        'signup_title'        => 'إنشاء حساب',
        'forgot_password'     => 'نسيت كلمة المرور؟',
        'reset_password'      => 'إعادة تعيين كلمة المرور',
        'no_account'          => 'ليس لديك حساب؟',
        'already_account'     => 'لديك حساب بالفعل؟',
        'password'            => 'كلمة المرور',
        'remember_me'         => 'تذكرني',
        'send_reset'          => 'إرسال الرابط',
        'error_login'         => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',

        // ── Admin ───────────────────────────
        'admin_dashboard'     => 'لوحة الإدارة',
        'manage_books'        => 'إدارة الكتب',
        'manage_orders'       => 'الطلبات',
        'manage_loans'        => 'إدارة الاستعارات',
        'manage_users'        => 'المستخدمون',
        'add_book'            => 'إضافة كتاب',
        'edit_book'           => 'تعديل',
        'delete_book'         => 'حذف',
        'total_books'         => 'الكتب',
        'total_users'         => 'القراء',
        'all_orders'          => 'جميع الطلبات',
        'validated'           => 'مُعتمد',
        'pending'             => 'قيد الانتظار',
        'cancelled'           => 'ملغى',
// ── General ─────────────────────────
        'loading'             => 'جارٍ التحميل...',
        'confirm_delete'      => 'هل أنت متأكد من الحذف؟',
        'yes'                 => 'نعم',
        'no'                  => 'لا',
        'cancel'              => 'إلغاء',
        'confirm'             => 'تأكيد',
        'back'                => 'رجوع',
        'see_all'             => 'عرض الكل',
        'quick_actions'       => 'إجراءات سريعة',
        'welcome_back'        => 'أهلاً بعودتك',
        'select'              => '— اختر —',
        'male'                => 'ذكر',
        'female'              => 'أنثى',
        'other'               => 'آخر',
    ],
];

/* ════════════════════════════════════════════════════════
   GESTION DU CHANGEMENT DE LANGUE
   — redirige proprement sans paramètre ?lang= dans l'URL
════════════════════════════════════════════════════════ */
if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $translations)) {
    $_SESSION['lang'] = $_GET['lang'];
    // Supprimer le paramètre lang de l'URL et rediriger
    $params = $_GET;
    unset($params['lang']);
    $clean_url = strtok($_SERVER['REQUEST_URI'], '?');
    $qs        = count($params) ? '?' . http_build_query($params) : '';
    // JS redirect pour éviter "headers already sent"
    echo "<script>window.location.replace('" . addslashes($clean_url . $qs) . "');</script>";
    exit();
}

/* ════════════════════════════════════════════════════════
   LANGUE ACTIVE
════════════════════════════════════════════════════════ */
$lang = $_SESSION['lang'] ?? 'fr';

// Fallback : si la langue n'existe pas, revenir en français
if (!array_key_exists($lang, $translations)) {
    $lang = 'fr';
    $_SESSION['lang'] = 'fr';
}

$text = $translations[$lang];

/* ════════════════════════════════════════════════════════
   HELPER : t('clé')
   Permet d'utiliser t('home') au lieu de $text['home']
   dans toutes les pages — plus court et plus lisible
════════════════════════════════════════════════════════ */
function t(string $key, string $fallback = ''): string {
    global $text;
    return htmlspecialchars($text[$key] ?? $fallback, ENT_QUOTES, 'UTF-8');
}

/* ════════════════════════════════════════════════════════
   HELPER : th('clé')
   Même chose mais sans htmlspecialchars — pour le HTML
   (titres avec <em>, messages avec <strong>, etc.)
════════════════════════════════════════════════════════ */
function th(string $key, string $fallback = ''): string {
    global $text;
    return $text[$key] ?? $fallback;
}
