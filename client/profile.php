<?php
session_start();
require_once "../includes/db.php";
include_once '../includes/languages.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$pg = [
    'fr' => [
        'page_title'        => 'Mon Profil — AuraLib',
        'role_admin'        => 'Administrateur',
        'role_reader'       => 'Lecteur',
        'member_since'      => 'Membre depuis',
        'total_loans'       => 'Emprunts',
        'active_loans'      => 'Actifs',
        'late'              => 'En retard',
        'total_purchases'   => 'Total achats',
        'readers'           => 'Lecteurs',
        'documents'         => 'Documents',
        'active_loans_all'  => 'Emprunts actifs',
        'tab_dashboard'     => 'Tableau de bord',
        'tab_profile'       => 'Mon Profil',
        'tab_history'       => 'Historique',
        'tab_favorites'     => 'Mes Favoris',
        'tab_messages'      => 'Mes Messages',
        'msg_title'         => 'Mes Messages',
        'msg_empty'         => "Vous n'avez envoyé aucun message.",
        'msg_subject_lbl'   => 'Sujet',
        'msg_date_lbl'      => 'Envoyé le',
        'msg_reply_lbl'     => 'Réponse du support',
        'msg_no_reply'      => 'Pas encore de réponse.',
        'msg_badge_replied' => 'Répondu',
        'msg_badge_pending' => 'En attente',
        'personal_info'     => 'Informations personnelles',
        'firstname'         => 'Prénom',
        'lastname'          => 'Nom de famille',
        'email'             => 'Email',
        'phone'             => 'Téléphone',
        'gender'            => 'Genre',
        'role_lbl'          => 'Rôle',
        'since_lbl'         => 'Membre depuis',
        'edit_btn'          => 'Modifier',
        'cancel_btn'        => 'Annuler',
        'save_btn'          => 'Enregistrer',
        'select_gender'     => '— Sélectionner —',
        'male'              => 'Homme',
        'female'            => 'Femme',
        'other'             => 'Autre',
        'ph_phone'          => '0XXXXXXXXX',
        'admin_access'      => 'Accès rapide administration',
        'active_loans_lbl'  => 'Emprunts en cours',
        'return_lbl'        => 'Retour :',
        'overdue_lbl'       => 'En retard',
        'no_active_loans'   => 'Aucun emprunt actif',
        'see_all_loans'     => 'Voir tous mes emprunts →',
        'notifications_lbl' => 'Notifications',
        'no_notifs'         => 'Aucune notification',
        'quick_actions'     => 'Actions rapides',
        'qa_catalogue'      => 'Catalogue',
        'qa_catalogue_sub'  => 'Chercher un livre',
        'qa_loans'          => 'Mes emprunts',
        'qa_loans_sub'      => 'Voir & renouveler',
        'qa_cart'           => 'Mon panier',
        'qa_cart_sub'       => 'Finaliser mes achats',
        'last_loans'        => 'Mes derniers emprunts',
        'book_col'          => 'Livre',
        'loan_date'         => 'Date emprunt',
        'return_date'       => 'Date retour',
        'status_col'        => 'Statut',
        'returned'          => 'Retourné',
        'in_progress'       => 'En cours',
        'no_loans'          => 'Aucun emprunt enregistré',
        'last_orders'       => 'Mes dernières commandes',
        'order_num'         => 'N° commande',
        'amount_col'        => 'Montant',
        'paid'              => 'Payée',
        'pending'           => 'En attente',
        'cancelled'         => 'Annulée',
        'no_orders'         => 'Aucune commande enregistrée',
        'back_library'      => 'Retour à la bibliothèque',
        'favorites_lbl'     => 'Mes Favoris',
        'fav_book'          => 'livre',
        'fav_books'         => 'livres',
        'empty_fav'         => 'Votre liste de favoris est vide.',
        'explore_btn'       => 'Explorer le catalogue',
        'view_book'         => 'Voir le livre',
        'keep_exploring'    => 'Continuer à explorer',
        'buy_tag'           => 'ACHAT',
        'borrow_tag'        => 'EMPRUNT',
        'both_tag'          => 'LES DEUX',
        'free_lbl'          => 'Gratuit',
        'confirm_remove'    => 'Retirer ce livre de vos favoris ?',
        'success_info'      => 'Informations mises à jour avec succès !',
        'err_required_info' => "Le prénom et l'email sont obligatoires.",
        'err_email'         => 'Adresse email invalide.',
        'err_email_taken'   => 'Cette adresse email est déjà utilisée.',
        'notif_overdue'     => 'Retour dépassé :',
        'notif_overdue_j'   => 'j de retard',
        'notif_reminder'    => 'Rappel :',
        'notif_reminder_j'  => 'jour(s)',
        'notif_reminder_in' => 'à rendre dans',
        'notif_order'       => 'Commande',
        'notif_confirmed'   => 'confirmée',
        'urgent'            => 'Urgent',
        'soon'              => 'Bientôt',
    ],
    'en' => [
        'page_title'        => 'My Profile — AuraLib',
        'role_admin'        => 'Administrator',
        'role_reader'       => 'Reader',
        'member_since'      => 'Member since',
        'total_loans'       => 'Loans',
        'active_loans'      => 'Active',
        'late'              => 'Overdue',
        'total_purchases'   => 'Total purchases',
        'readers'           => 'Readers',
        'documents'         => 'Documents',
        'active_loans_all'  => 'Active loans',
        'tab_dashboard'     => 'Dashboard',
        'tab_profile'       => 'My Profile',
        'tab_history'       => 'History',
        'tab_favorites'     => 'My Favourites',
        'tab_messages'      => 'My Messages',
        'msg_title'         => 'My Messages',
        'msg_empty'         => 'You have not sent any messages yet.',
        'msg_subject_lbl'   => 'Subject',
        'msg_date_lbl'      => 'Sent on',
        'msg_reply_lbl'     => 'Support reply',
        'msg_no_reply'      => 'No reply yet.',
        'msg_badge_replied' => 'Replied',
        'msg_badge_pending' => 'Pending',
        'personal_info'     => 'Personal information',
        'firstname'         => 'First name',
        'lastname'          => 'Last name',
        'email'             => 'Email',
        'phone'             => 'Phone',
        'gender'            => 'Gender',
        'role_lbl'          => 'Role',
        'since_lbl'         => 'Member since',
        'edit_btn'          => 'Edit',
        'cancel_btn'        => 'Cancel',
        'save_btn'          => 'Save',
        'select_gender'     => '— Select —',
        'male'              => 'Male',
        'female'            => 'Female',
        'other'             => 'Other',
        'ph_phone'          => '0XXXXXXXXX',
        'admin_access'      => 'Quick admin access',
        'active_loans_lbl'  => 'Active loans',
        'return_lbl'        => 'Return:',
        'overdue_lbl'       => 'Overdue',
        'no_active_loans'   => 'No active loans',
        'see_all_loans'     => 'See all my loans →',
        'notifications_lbl' => 'Notifications',
        'no_notifs'         => 'No notifications',
        'quick_actions'     => 'Quick actions',
        'qa_catalogue'      => 'Catalogue',
        'qa_catalogue_sub'  => 'Search a book',
        'qa_loans'          => 'My loans',
        'qa_loans_sub'      => 'View & renew',
        'qa_cart'           => 'My cart',
        'qa_cart_sub'       => 'Finalise purchases',
        'last_loans'        => 'My recent loans',
        'book_col'          => 'Book',
        'loan_date'         => 'Loan date',
        'return_date'       => 'Return date',
        'status_col'        => 'Status',
        'returned'          => 'Returned',
        'in_progress'       => 'Active',
        'no_loans'          => 'No loans recorded',
        'last_orders'       => 'My recent orders',
        'order_num'         => 'Order #',
        'amount_col'        => 'Amount',
        'paid'              => 'Paid',
        'pending'           => 'Pending',
        'cancelled'         => 'Cancelled',
        'no_orders'         => 'No orders recorded',
        'back_library'      => 'Back to library',
        'favorites_lbl'     => 'My Favourites',
        'fav_book'          => 'book',
        'fav_books'         => 'books',
        'empty_fav'         => 'Your favourites list is empty.',
        'explore_btn'       => 'Explore the catalogue',
        'view_book'         => 'View book',
        'keep_exploring'    => 'Keep exploring',
        'buy_tag'           => 'PURCHASE',
        'borrow_tag'        => 'BORROW',
        'both_tag'          => 'BOTH',
        'free_lbl'          => 'Free',
        'confirm_remove'    => 'Remove this book from your favourites?',
        'success_info'      => 'Information updated successfully!',
        'err_required_info' => 'First name and email are required.',
        'err_email'         => 'Invalid email address.',
        'err_email_taken'   => 'This email is already in use.',
        'notif_overdue'     => 'Overdue return:',
        'notif_overdue_j'   => 'days late',
        'notif_reminder'    => 'Reminder:',
        'notif_reminder_j'  => 'day(s)',
        'notif_reminder_in' => 'due in',
        'notif_order'       => 'Order',
        'notif_confirmed'   => 'confirmed',
        'urgent'            => 'Urgent',
        'soon'              => 'Soon',
    ],
    'ar' => [
        'page_title'        => 'ملفي الشخصي — AuraLib',
        'role_admin'        => 'مشرف',
        'role_reader'       => 'قارئ',
        'member_since'      => 'عضو منذ',
        'total_loans'       => 'استعارات',
        'active_loans'      => 'نشطة',
        'late'              => 'متأخرة',
        'total_purchases'   => 'مجموع المشتريات',
        'readers'           => 'القراء',
        'documents'         => 'وثائق',
        'active_loans_all'  => 'استعارات نشطة',
        'tab_dashboard'     => 'لوحة القيادة',
        'tab_profile'       => 'ملفي',
        'tab_history'       => 'السجل',
        'tab_favorites'     => 'مفضلتي',
        'tab_messages'      => 'رسائلي',
        'msg_title'         => 'رسائلي',
        'msg_empty'         => 'لم ترسل أي رسالة بعد.',
        'msg_subject_lbl'   => 'الموضوع',
        'msg_date_lbl'      => 'أُرسل في',
        'msg_reply_lbl'     => 'رد الدعم',
        'msg_no_reply'      => 'لا يوجد رد بعد.',
        'msg_badge_replied' => 'تم الرد',
        'msg_badge_pending' => 'قيد الانتظار',
        'personal_info'     => 'المعلومات الشخصية',
        'firstname'         => 'الاسم الأول',
        'lastname'          => 'اسم العائلة',
        'email'             => 'البريد الإلكتروني',
        'phone'             => 'الهاتف',
        'gender'            => 'الجنس',
        'role_lbl'          => 'الدور',
        'since_lbl'         => 'عضو منذ',
        'edit_btn'          => 'تعديل',
        'cancel_btn'        => 'إلغاء',
        'save_btn'          => 'حفظ',
        'select_gender'     => '— اختر —',
        'male'              => 'ذكر',
        'female'            => 'أنثى',
        'other'             => 'آخر',
        'ph_phone'          => '0XXXXXXXXX',
        'admin_access'      => 'وصول سريع للإدارة',
        'active_loans_lbl'  => 'الاستعارات النشطة',
        'return_lbl'        => 'الإعادة:',
        'overdue_lbl'       => 'متأخر',
        'no_active_loans'   => 'لا توجد استعارات نشطة',
        'see_all_loans'     => 'عرض كل استعاراتي ←',
        'notifications_lbl' => 'الإشعارات',
        'no_notifs'         => 'لا توجد إشعارات',
        'quick_actions'     => 'إجراءات سريعة',
        'qa_catalogue'      => 'الكتالوج',
        'qa_catalogue_sub'  => 'البحث عن كتاب',
        'qa_loans'          => 'استعاراتي',
        'qa_loans_sub'      => 'عرض وتجديد',
        'qa_cart'           => 'سلتي',
        'qa_cart_sub'       => 'إتمام المشتريات',
        'last_loans'        => 'آخر استعاراتي',
        'book_col'          => 'الكتاب',
        'loan_date'         => 'تاريخ الاستعارة',
        'return_date'       => 'تاريخ الإعادة',
        'status_col'        => 'الحالة',
        'returned'          => 'مُعادة',
        'in_progress'       => 'جارية',
        'no_loans'          => 'لا توجد استعارات',
        'last_orders'       => 'آخر طلباتي',
        'order_num'         => 'رقم الطلب',
        'amount_col'        => 'المبلغ',
        'paid'              => 'مدفوع',
        'pending'           => 'قيد الانتظار',
        'cancelled'         => 'ملغى',
        'no_orders'         => 'لا توجد طلبات',
        'back_library'      => 'العودة للمكتبة',
        'favorites_lbl'     => 'مفضلتي',
        'fav_book'          => 'كتاب',
        'fav_books'         => 'كتب',
        'empty_fav'         => 'قائمة مفضلتك فارغة.',
        'explore_btn'       => 'استكشف الكتالوج',
        'view_book'         => 'عرض الكتاب',
        'keep_exploring'    => 'مواصلة الاستكشاف',
        'buy_tag'           => 'شراء',
        'borrow_tag'        => 'استعارة',
        'both_tag'          => 'كلاهما',
        'free_lbl'          => 'مجاني',
        'confirm_remove'    => 'إزالة هذا الكتاب من المفضلة؟',
        'success_info'      => 'تم تحديث المعلومات بنجاح!',
        'err_required_info' => 'الاسم الأول والبريد الإلكتروني مطلوبان.',
        'err_email'         => 'البريد الإلكتروني غير صالح.',
        'err_email_taken'   => 'البريد الإلكتروني مستخدم.',
        'notif_overdue'     => 'تأخر الإعادة:',
        'notif_overdue_j'   => 'أيام تأخير',
        'notif_reminder'    => 'تذكير:',
        'notif_reminder_j'  => 'يوم/أيام',
        'notif_reminder_in' => 'موعد الإعادة خلال',
        'notif_order'       => 'الطلب',
        'notif_confirmed'   => 'تم تأكيده',
        'urgent'            => 'عاجل',
        'soon'              => 'قريباً',
    ],
];
$p     = $pg[$lang] ?? $pg['fr'];
$isRtl = ($lang === 'ar');
$id_user = (int)$_SESSION['id_user'];
$tab     = $_GET['tab'] ?? 'profil';
$success = '';
$error   = '';

$stmt = $conn->prepare("SELECT id, firstname, lastname, email, phone, Gender, role, created_at, password FROM users WHERE id = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$role = $user['role'] ?? 'client';
$first_letter     = strtoupper(substr($user['firstname'] ?? $user['email'] ?? 'U', 0, 1));
$display_name     = htmlspecialchars($user['firstname'] ?? '');
$display_email    = htmlspecialchars($user['email']     ?? '');
$display_fullname = htmlspecialchars(trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')));

$nb_emprunts = $nb_retours = $nb_en_cours = $nb_retards = $nb_commandes = 0;
$total_achats = 0.0;
$emprunts_actifs = $hist_emprunts = $hist_commandes = null;
$wishlist_items = []; $nb_wishlist = 0; $notifications = [];
$nb_users = $nb_livres = $nb_emprunts_actifs_total = $nb_retards_total = 0;
$chiffre_affaires = 0.0;
$user_messages = []; $nb_user_messages = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_info') {
        $new_firstname = trim($_POST['firstname'] ?? '');
        $new_lastname  = trim($_POST['lastname']  ?? '');
        $new_email     = trim($_POST['email']     ?? '');
        $new_phone     = trim($_POST['phone']     ?? '');
        $new_gender    = trim($_POST['gender']    ?? '');
        if (empty($new_firstname) || empty($new_email)) {
            $error = $p['err_required_info']; $tab = 'profil';
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = $p['err_email']; $tab = 'profil';
        } elseif (!empty($new_phone) && !preg_match('/^0[0-9]{9}$/', $new_phone)) {
            $error = 'Numéro de téléphone invalide (10 chiffres, commence par 0).'; $tab = 'profil';
        } else {
            $chk = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $chk->bind_param("si", $new_email, $id_user); $chk->execute();
            if ($chk->get_result()->num_rows > 0) {
                $error = $p['err_email_taken']; $tab = 'profil';
            } else {
                $upd = $conn->prepare("UPDATE users SET firstname=?, lastname=?, email=?, phone=?, Gender=? WHERE id=?");
                $upd->bind_param("sssssi", $new_firstname, $new_lastname, $new_email, $new_phone, $new_gender, $id_user);
                $upd->execute();
                $user['firstname'] = $new_firstname; $user['lastname'] = $new_lastname;
                $user['email'] = $new_email; $user['phone'] = $new_phone; $user['Gender'] = $new_gender;
                $first_letter = strtoupper(substr($new_firstname, 0, 1));
                $display_name = htmlspecialchars($new_firstname);
                $display_email = htmlspecialchars($new_email);
                $display_fullname = htmlspecialchars(trim("$new_firstname $new_lastname"));
                $success = $p['success_info']; $tab = 'profil';
            }
        }
    }
}

if ($role === 'client') {
    $r = $conn->query("SELECT COUNT(*) c FROM emprunt WHERE id_user=$id_user"); if ($r) $nb_emprunts = (int)$r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) c FROM emprunt WHERE id_user=$id_user AND statut='en_cours'"); if ($r) $nb_en_cours = (int)$r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) c FROM emprunt WHERE id_user=$id_user AND statut='en_cours' AND date_fin < CURDATE()"); if ($r) $nb_retards = (int)$r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) c FROM commande WHERE id_user=$id_user"); if ($r) $nb_commandes = (int)$r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COALESCE(SUM(total),0) s FROM commande WHERE id_user=$id_user AND statut='payee'"); if ($r) $total_achats = (float)$r->fetch_assoc()['s'];
    $emprunts_actifs = $conn->query("SELECT e.*, d.titre, DATEDIFF(e.date_fin, CURDATE()) AS jours_restants FROM emprunt e JOIN documents d ON e.id_doc = d.id_doc WHERE e.id_user = $id_user AND e.statut = 'en_cours' ORDER BY e.date_fin ASC LIMIT 5");
    $hist_emprunts   = $conn->query("SELECT e.*, d.titre FROM emprunt e JOIN documents d ON e.id_doc = d.id_doc WHERE e.id_user = $id_user ORDER BY e.date_debut DESC LIMIT 5");
    $hist_commandes  = $conn->query("SELECT * FROM commande WHERE id_user=$id_user ORDER BY id_commande DESC LIMIT 5");
    $wres = $conn->query("SELECT w.id_wishlist, d.id_doc, d.titre, d.prix, d.disponible_pour, d.auteur, d.image_doc FROM wishlist w JOIN documents d ON w.id_doc = d.id_doc WHERE w.id_user = $id_user ORDER BY w.created_at DESC");
    if ($wres) while ($w = $wres->fetch_assoc()) $wishlist_items[] = $w;
    $nb_wishlist = count($wishlist_items);
    $conn->query("ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS reponse TEXT NULL DEFAULT NULL");
    $conn->query("ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS date_reponse DATETIME NULL DEFAULT NULL");
    $msg_res = $conn->query("SELECT * FROM contact_messages WHERE id_user = $id_user ORDER BY created_at DESC");
    if ($msg_res) while ($m = $msg_res->fetch_assoc()) $user_messages[] = $m;
    $nb_user_messages = count($user_messages);
    $r = $conn->query("SELECT d.titre, DATEDIFF(CURDATE(), e.date_fin) AS jours_retard FROM emprunt e JOIN documents d ON e.id_doc = d.id_doc WHERE e.id_user = $id_user AND e.statut = 'en_cours' AND e.date_fin < CURDATE() ORDER BY e.date_fin ASC LIMIT 5");
    if ($r) while ($n = $r->fetch_assoc()) $notifications[] = ['type'=>'danger','text'=>$p['notif_overdue'].' <strong>'.htmlspecialchars($n['titre']).'</strong> ('.$n['jours_retard'].' '.$p['notif_overdue_j'].')','time'=>$p['urgent']];
    $r = $conn->query("SELECT d.titre, DATEDIFF(e.date_fin, CURDATE()) AS jours_restants FROM emprunt e JOIN documents d ON e.id_doc = d.id_doc WHERE e.id_user = $id_user AND e.statut = 'en_cours' AND e.date_fin >= CURDATE() AND DATEDIFF(e.date_fin, CURDATE()) <= 3 ORDER BY e.date_fin ASC LIMIT 5");
    if ($r) while ($n = $r->fetch_assoc()) $notifications[] = ['type'=>'warning','text'=>$p['notif_reminder'].' <strong>'.htmlspecialchars($n['titre']).'</strong> '.$p['notif_reminder_in'].' '.$n['jours_restants'].' '.$p['notif_reminder_j'],'time'=>$p['soon']];
    $r = $conn->query("SELECT id_commande, date_commande FROM commande WHERE id_user=$id_user AND statut='payee' ORDER BY date_commande DESC LIMIT 2");
    if ($r) while ($n = $r->fetch_assoc()) $notifications[] = ['type'=>'success','text'=>$p['notif_order'].' <strong>#'.str_pad($n['id_commande'],3,'0',STR_PAD_LEFT).'</strong> '.$p['notif_confirmed'],'time'=>date('d/m/Y',strtotime($n['date_commande']))];
} else {
    $r = $conn->query("SELECT COUNT(*) c FROM users WHERE role='client'"); if ($r) $nb_users = (int)$r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) c FROM documents"); if ($r) $nb_livres = (int)$r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) c FROM emprunt WHERE statut='en_cours'"); if ($r) $nb_emprunts_actifs_total = (int)$r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) c FROM emprunt WHERE statut='en_cours' AND date_fin < CURDATE()"); if ($r) $nb_retards_total = (int)$r->fetch_assoc()['c'];
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
<meta charset="UTF-8">
<?php include '../includes/dark_init.php'; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $p['page_title'] ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<style>
:root {
    --gold:#C4A46B; --gold2:#D4B47B; --gold-deep:#A8884E;
    --gold-faint:rgba(196,164,107,.08); --gold-border:rgba(196,164,107,.25);
    --gold-shadow:0 6px 20px rgba(196,164,107,.22);
    --ink:#2C1F0E; --ink2:#3A2A14;
    --page-bg:#F5F0E8; --page-bg2:#EDE5D4; --page-white:#FFFDF9;
    --page-text:#2C1F0E; --page-muted:#9A8C7E; --page-border:#DDD5C8;
    --success:#2E7D52; --success-bg:rgba(46,125,82,.08);
    --danger:#C0392B; --danger-bg:rgba(192,57,43,.08);
    --warning:#B8832A;
    --font-serif:'Cormorant Garamond',Georgia,serif;
    --font-ui:'Plus Jakarta Sans',sans-serif;
    --nav-h:66px; --radius:14px;
    --shadow-sm:0 3px 12px rgba(44,31,14,.07);
    --shadow-md:0 8px 28px rgba(44,31,14,.10);
    --tr:.22s cubic-bezier(.4,0,.2,1);
}
html.dark { --page-bg:#100C07; --page-bg2:#1A1308; --page-white:#1E1610; --page-text:#EDE5D4; --page-muted:#9A8C7E; --page-border:#3A2E1E; }
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:var(--font-ui);background:var(--page-bg);color:var(--page-text);padding-top:var(--nav-h);min-height:100vh;transition:background .35s,color .35s}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.profile-hero{background:linear-gradient(135deg,#1A0E05 0%,#2E1D08 55%,#1A0E05 100%);padding:28px 5%;display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;border-bottom:1px solid rgba(196,164,107,.15)}
.hero-avatar{width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--gold) 0%,var(--gold-deep) 100%);color:#1A0E05;display:flex;align-items:center;justify-content:center;font-family:var(--font-serif);font-size:22px;font-weight:700;flex-shrink:0;box-shadow:0 3px 12px rgba(196,164,107,.35);border:2.5px solid rgba(196,164,107,.35)}
.hero-info h1{font-family:var(--font-serif);font-size:clamp(18px,3vw,26px);font-weight:700;color:#FDFAF5;line-height:1;margin-bottom:4px}
.hero-info p{font-size:12px;color:rgba(255,255,255,.4)}
.hero-meta{display:flex;gap:10px;margin-top:7px;flex-wrap:wrap}
.hero-meta span{font-size:10px;color:rgba(255,255,255,.35);display:flex;align-items:center;gap:4px}
.hero-role{background:rgba(196,164,107,.12);border:1px solid rgba(196,164,107,.3);color:var(--gold);font-size:10px;font-weight:700;padding:4px 14px;border-radius:20px;letter-spacing:.8px;text-transform:uppercase;flex-shrink:0}
.stats-strip{display:grid;grid-template-columns:repeat(4,1fr);border-bottom:1px solid var(--page-border);background:var(--page-white)}
.stat-tile{padding:16px 20px;text-align:center;border-right:1px solid var(--page-border);border-top:3px solid transparent}
.stat-tile:last-child{border-right:none}
.stat-tile.gold{border-top-color:var(--gold)}
.stat-tile.green{border-top-color:var(--success)}
.stat-tile.red{border-top-color:var(--danger)}
.stat-num{font-family:var(--font-serif);font-size:26px;font-weight:700;color:var(--page-text);line-height:1}
.stat-lbl{font-size:10px;color:var(--page-muted);margin-top:3px}
.profile-page{max-width:940px;margin:0 auto;padding:24px 20px 80px}
.tabs{display:flex;gap:2px;border-bottom:1px solid var(--page-border);margin-bottom:22px;overflow-x:auto}
.tab-btn{display:flex;align-items:center;gap:6px;padding:10px 16px;font-size:12px;font-weight:600;color:var(--page-muted);background:none;border:none;border-bottom:2.5px solid transparent;cursor:pointer;text-decoration:none;transition:color var(--tr),border-color var(--tr);white-space:nowrap;flex-shrink:0;font-family:var(--font-ui)}
.tab-btn:hover{color:var(--page-text)}
.tab-btn.active{color:var(--page-text);border-bottom-color:var(--gold)}
.tab-btn i{font-size:11px}
.tab-badge{background:var(--gold);color:var(--ink);font-size:9px;font-weight:700;padding:1px 7px;border-radius:10px;margin-left:2px}
.panel{display:none;animation:fadeUp .3s ease both}
.panel.active{display:block}
.profile-card{background:var(--page-white);border:1px solid var(--page-border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm);margin-bottom:18px}
.profile-card-head{background:linear-gradient(135deg,var(--ink) 0%,var(--ink2) 100%);padding:16px 20px;display:flex;align-items:center;justify-content:space-between}
.profile-card-title{font-family:var(--font-serif);font-size:18px;font-weight:700;color:var(--gold)}
.btn-edit-toggle{display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:50px;font-size:11px;font-weight:700;font-family:var(--font-ui);background:rgba(196,164,107,.15);color:var(--gold);border:1.5px solid rgba(196,164,107,.3);cursor:pointer;transition:all var(--tr)}
.btn-edit-toggle:hover{background:var(--gold);color:var(--ink);border-color:var(--gold)}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--page-border)}
.info-field{background:var(--page-white);padding:14px 18px}
.info-field label{font-size:9px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--page-muted);display:block;margin-bottom:4px}
.info-field span{font-size:14px;color:var(--page-text);font-weight:500}
.edit-form{padding:20px 18px;display:none}
.edit-form.visible{display:block}
.edit-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.edit-group{display:flex;flex-direction:column;gap:5px}
.edit-group label{font-size:10px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--page-muted)}
.edit-group input,.edit-group select{padding:9px 12px;border:1.5px solid var(--page-border);border-radius:9px;font-family:var(--font-ui);font-size:13px;color:var(--page-text);background:var(--page-bg);outline:none;transition:border-color var(--tr),box-shadow var(--tr);width:100%}
.edit-group input:focus,.edit-group select:focus{border-color:var(--gold-border);box-shadow:0 0 0 3px rgba(196,164,107,.1);background:var(--page-white)}
.edit-actions{display:flex;gap:10px;margin-top:18px}
.btn-save-inline{display:inline-flex;align-items:center;gap:6px;padding:10px 24px;border-radius:50px;background:var(--ink);color:var(--gold);border:1.5px solid rgba(196,164,107,.3);font-family:var(--font-ui);font-size:12px;font-weight:700;cursor:pointer;transition:all var(--tr)}
.btn-save-inline:hover{background:var(--gold);color:var(--ink);border-color:var(--gold);box-shadow:var(--gold-shadow)}
.btn-cancel-inline{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:50px;background:transparent;color:var(--page-muted);border:1.5px solid var(--page-border);font-family:var(--font-ui);font-size:12px;font-weight:600;cursor:pointer;transition:all var(--tr)}
.btn-cancel-inline:hover{border-color:var(--gold);color:var(--gold-deep)}
.dash-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px}
.dash-card{background:var(--page-white);border:1px solid var(--page-border);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow-sm)}
.dash-card h4{font-size:12px;font-weight:700;color:var(--page-text);margin-bottom:12px;display:flex;align-items:center;gap:7px;padding-bottom:10px;border-bottom:1px solid var(--page-border)}
.dash-card h4 i{color:var(--gold);font-size:12px}
.borrow-item{display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--page-border)}
.borrow-item:last-child{border-bottom:none}
.borrow-title{font-size:12px;color:var(--page-text);font-weight:600}
.borrow-date{font-size:10px;color:var(--page-muted);margin-top:2px}
.day-badge{font-size:10px;font-weight:700;padding:3px 9px;border-radius:10px;flex-shrink:0}
.db-ok{background:rgba(46,125,82,.1);color:var(--success)}
.db-soon{background:rgba(184,131,42,.1);color:var(--warning)}
.db-late{background:var(--danger-bg);color:var(--danger)}
.notif-item{display:flex;align-items:flex-start;gap:8px;padding:7px 0;border-bottom:1px solid var(--page-border)}
.notif-item:last-child{border-bottom:none}
.notif-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;margin-top:4px}
.nd-danger{background:var(--danger)}.nd-warning{background:var(--warning)}.nd-success{background:var(--success)}
.notif-text{font-size:11px;color:var(--page-muted);line-height:1.5}
.notif-time{font-size:10px;color:var(--page-muted);margin-top:1px}
.section-lbl{font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--page-muted);margin-bottom:10px;padding-bottom:7px;border-bottom:1px solid var(--page-border)}
.qa-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px}
.qa-btn{background:var(--page-white);border:1px solid var(--page-border);border-radius:12px;padding:18px 14px;text-align:center;text-decoration:none;color:var(--page-text);transition:border-color var(--tr),background var(--tr),transform var(--tr),box-shadow var(--tr);display:block}
.qa-btn:hover{border-color:var(--gold);background:var(--gold-faint);transform:translateY(-2px);box-shadow:0 6px 20px rgba(196,164,107,.15)}
.qa-icon{font-size:22px;margin-bottom:8px;height:32px;display:flex;align-items:center;justify-content:center}
.qa-label{font-size:12px;font-weight:700;color:var(--page-text)}
.qa-sub{font-size:10px;color:var(--page-muted);margin-top:3px}
.alert{padding:11px 14px;border-radius:10px;font-size:12px;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.alert-success{background:var(--success-bg);border:1px solid rgba(46,125,82,.25);color:var(--success)}
.alert-error{background:var(--danger-bg);border:1px solid rgba(192,57,43,.25);color:var(--danger)}
.alert i{font-size:13px;flex-shrink:0}
.table-wrap{background:var(--page-white);border:1px solid var(--page-border);border-radius:12px;overflow:hidden;margin-bottom:14px;box-shadow:var(--shadow-sm)}
.table-header{padding:12px 16px;border-bottom:1px solid var(--page-border);font-size:12px;font-weight:700;display:flex;align-items:center;gap:7px;background:linear-gradient(135deg,var(--ink) 0%,var(--ink2) 100%);color:var(--gold)}
.table-header i{font-size:12px}
.data-table{width:100%;border-collapse:collapse;font-size:12px}
.data-table th{text-align:left;padding:9px 14px;font-size:10px;font-weight:700;color:var(--page-muted);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--page-border);background:var(--page-bg2)}
.data-table td{padding:11px 14px;border-bottom:1px solid var(--page-border);color:var(--page-text)}
.data-table tr:last-child td{border-bottom:none}
.data-table tr:hover td{background:var(--gold-faint)}
.empty-row td{text-align:center;color:var(--page-muted);padding:20px}
.badge{display:inline-block;padding:3px 9px;border-radius:20px;font-size:10px;font-weight:700}
.badge-green{background:var(--success-bg);color:var(--success);border:1px solid rgba(46,125,82,.2)}
.badge-amber{background:rgba(184,131,42,.1);color:var(--warning);border:1px solid rgba(184,131,42,.2)}
.badge-red{background:var(--danger-bg);color:var(--danger);border:1px solid rgba(192,57,43,.2)}
.badge-gray{background:var(--page-bg2);color:var(--page-muted);border:1px solid var(--page-border)}
.wish-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.wish-card{background:var(--page-white);border:1px solid var(--page-border);border-radius:12px;overflow:hidden;transition:transform var(--tr),box-shadow var(--tr);box-shadow:var(--shadow-sm)}
.wish-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-md);border-color:var(--gold-border)}
.wish-cover{height:120px;background:var(--page-bg2);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden}
.wish-cover img{width:100%;height:100%;object-fit:cover}
.wish-type-badge{position:absolute;top:7px;right:7px;font-size:9px;font-weight:700;padding:2px 8px;border-radius:10px;text-transform:uppercase}
.wish-body{padding:11px 12px}
.wish-title{font-size:12px;font-weight:700;color:var(--page-text);margin-bottom:3px;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden;line-height:1.3}
.wish-author{font-size:10px;color:var(--page-muted);margin-bottom:7px}
.wish-price{font-size:13px;font-weight:700;color:var(--page-text);margin-bottom:9px}
.wish-price.free{color:var(--success);font-size:12px}
.wish-actions{display:flex;gap:6px}
.wish-btn-voir{flex:1;background:var(--gold);color:var(--ink);border:none;padding:7px 0;border-radius:7px;font-size:11px;font-weight:700;text-align:center;text-decoration:none;display:block;cursor:pointer;transition:background var(--tr);font-family:var(--font-ui)}
.wish-btn-voir:hover{background:var(--gold2)}
.wish-btn-del{background:var(--danger-bg);color:var(--danger);border:none;padding:7px 10px;border-radius:7px;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background var(--tr)}
.wish-btn-del:hover{background:rgba(192,57,43,.18)}
.empty-state{text-align:center;padding:40px 20px;background:var(--page-white);border:1px solid var(--page-border);border-radius:12px}
.empty-state .empty-icon{font-size:36px;margin-bottom:12px}
.empty-state p{font-size:13px;color:var(--page-muted);margin-bottom:14px}
.btn-explore{display:inline-flex;align-items:center;gap:6px;padding:9px 20px;border-radius:50px;background:var(--gold);color:var(--ink);font-family:var(--font-ui);font-size:12px;font-weight:700;text-decoration:none;border:none;cursor:pointer;transition:background var(--tr)}
.btn-explore:hover{background:var(--gold2)}
.back-link{font-size:12px;color:var(--page-muted);text-decoration:none;display:inline-flex;align-items:center;gap:4px}
.back-link:hover{color:var(--page-text)}
@media(max-width:600px){.stats-strip{grid-template-columns:1fr 1fr}.edit-grid,.info-grid{grid-template-columns:1fr}.dash-grid{grid-template-columns:1fr}.wish-grid{grid-template-columns:1fr 1fr}.qa-grid{grid-template-columns:1fr 1fr 1fr}}
</style>
</head>
<body>
<?php include "../includes/header.php"; ?>

<div class="profile-hero">
    <div style="display:flex;align-items:center;gap:14px">
        <div class="hero-avatar"><?= $first_letter ?></div>
        <div class="hero-info">
            <h1><?= $display_fullname ?: $display_email ?></h1>
            <p><?= $display_email ?></p>
            <div class="hero-meta">
                <?php if (!empty($user['phone'])): ?>
                <span><i class="fa-solid fa-phone" style="font-size:9px;opacity:.5"></i> <?= htmlspecialchars($user['phone']) ?></span>
                <?php endif; ?>
                <?php if (!empty($user['created_at'])): ?>
                <span><i class="fa-regular fa-calendar" style="font-size:9px;opacity:.5"></i> <?= $p['member_since'] ?> <?= date('M Y', strtotime($user['created_at'])) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <span class="hero-role"><?= $role === 'admin' ? $p['role_admin'] : $p['role_reader'] ?></span>
</div>

<?php if ($role === 'client'): ?>
<div class="stats-strip">
    <div class="stat-tile gold"><div class="stat-num"><?= $nb_emprunts ?></div><div class="stat-lbl"><?= $p['total_loans'] ?></div></div>
    <div class="stat-tile green"><div class="stat-num"><?= $nb_en_cours ?></div><div class="stat-lbl"><?= $p['active_loans'] ?></div></div>
    <div class="stat-tile red"><div class="stat-num"><?= $nb_retards ?></div><div class="stat-lbl"><?= $p['late'] ?></div></div>
    <div class="stat-tile"><div class="stat-num"><?= number_format($total_achats,0) ?><small style="font-size:11px"> DA</small></div><div class="stat-lbl"><?= $p['total_purchases'] ?></div></div>
</div>
<?php else: ?>
<div class="stats-strip">
    <div class="stat-tile gold"><div class="stat-num"><?= $nb_users ?></div><div class="stat-lbl"><?= $p['readers'] ?></div></div>
    <div class="stat-tile green"><div class="stat-num"><?= $nb_livres ?></div><div class="stat-lbl"><?= $p['documents'] ?></div></div>
    <div class="stat-tile"><div class="stat-num"><?= $nb_emprunts_actifs_total ?></div><div class="stat-lbl"><?= $p['active_loans_all'] ?></div></div>
    <div class="stat-tile red"><div class="stat-num"><?= $nb_retards_total ?></div><div class="stat-lbl"><?= $p['late'] ?></div></div>
</div>
<?php endif; ?>

<div class="profile-page">

<?php if ($success): ?>
<div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- TABS -->
<div class="tabs">
    <?php if ($role === 'client'): ?>
    <a href="?tab=dashboard" class="tab-btn <?= $tab==='dashboard'?'active':'' ?>">
        <i class="fa-solid fa-chart-pie"></i> <?= $p['tab_dashboard'] ?>
    </a>
    <?php endif; ?>
    <a href="?tab=profil" class="tab-btn <?= $tab==='profil'?'active':'' ?>">
        <i class="fa-solid fa-user"></i> <?= $p['tab_profile'] ?>
    </a>
    <?php if ($role === 'client'): ?>
    <a href="?tab=historique" class="tab-btn <?= $tab==='historique'?'active':'' ?>">
        <i class="fa-solid fa-clock-rotate-left"></i> <?= $p['tab_history'] ?>
    </a>
    <a href="?tab=wishlist" class="tab-btn <?= $tab==='wishlist'?'active':'' ?>">
        <i class="fa-solid fa-heart"></i> <?= $p['tab_favorites'] ?>
        <?php if ($nb_wishlist > 0): ?><span class="tab-badge"><?= $nb_wishlist ?></span><?php endif; ?>
    </a>
    <a href="?tab=messages" class="tab-btn <?= $tab==='messages'?'active':'' ?>">
        <i class="fa-solid fa-envelope"></i> <?= $p['tab_messages'] ?>
        <?php if ($nb_user_messages > 0): ?><span class="tab-badge"><?= $nb_user_messages ?></span><?php endif; ?>
    </a>
    <?php endif; ?>
</div>

<!-- PANEL: DASHBOARD -->
<?php if ($role === 'client'): ?>
<div class="panel <?= $tab==='dashboard'?'active':'' ?>">
    <div class="dash-grid">
        <div class="dash-card">
            <h4><i class="fa-solid fa-book-open"></i> <?= $p['active_loans_lbl'] ?></h4>
            <?php if ($emprunts_actifs && $emprunts_actifs->num_rows > 0):
                while ($e = $emprunts_actifs->fetch_assoc()):
                    $j = (int)$e['jours_restants'];
                    $bc = $j < 0 ? 'db-late' : ($j <= 3 ? 'db-soon' : 'db-ok');
                    $bt = $j < 0 ? $p['overdue_lbl'] : 'J-'.$j;
            ?>
            <div class="borrow-item">
                <div>
                    <div class="borrow-title"><?= htmlspecialchars($e['titre']) ?></div>
                    <div class="borrow-date"><?= $p['return_lbl'] ?> <?= date('d/m/Y',strtotime($e['date_fin'])) ?></div>
                </div>
                <span class="day-badge <?= $bc ?>"><?= $bt ?></span>
            </div>
            <?php endwhile; else: ?>
            <p style="font-size:12px;color:var(--page-muted);text-align:center;padding:14px 0"><?= $p['no_active_loans'] ?></p>
            <?php endif; ?>
            <div style="margin-top:10px">
                <a href="/MEMOIR/emprunts/emprunt.php" style="font-size:11px;color:var(--gold);text-decoration:none;font-weight:700"><?= $p['see_all_loans'] ?></a>
            </div>
        </div>
        <div class="dash-card">
            <h4>
                <i class="fa-solid fa-bell"></i> <?= $p['notifications_lbl'] ?>
                <?php if (count($notifications)): ?>
                <span style="background:var(--gold);color:var(--ink);font-size:9px;font-weight:700;padding:1px 7px;border-radius:10px;margin-left:auto"><?= count($notifications) ?></span>
                <?php endif; ?>
            </h4>
            <?php if (!empty($notifications)):
                foreach ($notifications as $n): ?>
            <div class="notif-item">
                <div class="notif-dot nd-<?= $n['type'] ?>"></div>
                <div><div class="notif-text"><?= $n['text'] ?></div><div class="notif-time"><?= $n['time'] ?></div></div>
            </div>
            <?php endforeach; else: ?>
            <p style="font-size:12px;color:var(--page-muted);text-align:center;padding:14px 0"><?= $p['no_notifs'] ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="section-lbl"><?= $p['quick_actions'] ?></div>
    <div class="qa-grid">
        <a href="/MEMOIR/client/library.php" class="qa-btn">
            <div class="qa-icon"><i class="fa-solid fa-compass" style="color:var(--gold);font-size:22px"></i></div>
            <div class="qa-label"><?= $p['qa_catalogue'] ?></div>
            <div class="qa-sub"><?= $p['qa_catalogue_sub'] ?></div>
        </a>
        <a href="/MEMOIR/emprunts/mes_emprunts.php" class="qa-btn">
            <div class="qa-icon"><i class="fa-solid fa-book-bookmark" style="color:var(--gold);font-size:22px"></i></div>
            <div class="qa-label"><?= $p['qa_loans'] ?></div>
            <div class="qa-sub"><?= $p['qa_loans_sub'] ?></div>
        </a>
        <a href="/MEMOIR/cart/panier.php" class="qa-btn">
            <div class="qa-icon"><i class="fa-solid fa-bag-shopping" style="color:var(--gold);font-size:22px"></i></div>
            <div class="qa-label"><?= $p['qa_cart'] ?></div>
            <div class="qa-sub"><?= $p['qa_cart_sub'] ?></div>
        </a>
    </div>
</div>
<?php endif; ?>

<!-- PANEL: MON PROFIL -->
<div class="panel <?= $tab==='profil'?'active':'' ?>">
    <div class="profile-card">
        <div class="profile-card-head">
            <div class="profile-card-title"><?= $p['personal_info'] ?></div>
            <button class="btn-edit-toggle" id="btnEditToggle" onclick="toggleEdit()">
                <i class="fa-solid fa-pen" style="font-size:10px"></i> <?= $p['edit_btn'] ?>
            </button>
        </div>
        <div class="info-grid" id="viewMode">
            <div class="info-field"><label><?= $p['firstname'] ?></label><span><?= htmlspecialchars($user['firstname'] ?? '—') ?></span></div>
            <div class="info-field"><label><?= $p['lastname'] ?></label><span><?= htmlspecialchars($user['lastname'] ?? '—') ?></span></div>
            <div class="info-field"><label><?= $p['email'] ?></label><span><?= htmlspecialchars($user['email'] ?? '—') ?></span></div>
            <div class="info-field"><label><?= $p['phone'] ?></label><span><?= htmlspecialchars($user['phone'] ?? '—') ?></span></div>
            <div class="info-field"><label><?= $p['gender'] ?></label><span><?= htmlspecialchars($user['Gender'] ?? '—') ?></span></div>
            <div class="info-field"><label><?= $p['role_lbl'] ?></label><span><?= $role==='admin'?$p['role_admin']:$p['role_reader'] ?></span></div>
            <div class="info-field"><label><?= $p['since_lbl'] ?></label><span><?= isset($user['created_at'])?date('d/m/Y',strtotime($user['created_at'])):'—' ?></span></div>
        </div>
        <div class="edit-form" id="editMode">
            <form id="profileForm" method="POST" onsubmit="return false">
                <input type="hidden" name="action" value="update_info">
                <input type="hidden" name="firstname" value="<?= htmlspecialchars($user['firstname'] ?? '') ?>">
                <input type="hidden" name="lastname"  value="<?= htmlspecialchars($user['lastname']  ?? '') ?>">
                <input type="hidden" name="email"     value="<?= htmlspecialchars($user['email']     ?? '') ?>">
                <div class="edit-grid">
                    <div class="edit-group">
                        <label><?= $p['phone'] ?></label>
                        <input type="tel" id="phoneInput" name="phone"
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                               placeholder="0XXXXXXXXX"
                               maxlength="10"
                               pattern="0[0-9]{9}"
                               oninput="validatePhone(this)"
                               autocomplete="tel">
                        <span id="phoneError" style="display:none;font-size:10px;color:var(--danger);margin-top:3px;font-weight:600">
                            <i class="fa-solid fa-circle-exclamation" style="font-size:9px"></i>
                            10 chiffres, commence par 0
                        </span>
                    </div>
                    <div class="edit-group">
                        <label><?= $p['gender'] ?></label>
                        <select name="gender">
                            <option value=""><?= $p['select_gender'] ?></option>
                            <option value="Homme" <?= ($user['Gender']??'')==='Homme'?'selected':'' ?>><?= $p['male'] ?></option>
                            <option value="Femme" <?= ($user['Gender']??'')==='Femme'?'selected':'' ?>><?= $p['female'] ?></option>
                            <option value="Autre" <?= ($user['Gender']??'')==='Autre'?'selected':'' ?>><?= $p['other'] ?></option>
                        </select>
                    </div>
                </div>
                <div class="edit-actions">
                    <button type="button" id="btnSave" class="btn-save-inline" onclick="saveProfile(event)">
                        <i class="fa-solid fa-floppy-disk" style="font-size:10px"></i> <?= $p['save_btn'] ?>
                    </button>
                    <button type="button" class="btn-cancel-inline" onclick="toggleEdit()"><?= $p['cancel_btn'] ?></button>
                </div>
                <div id="saveMsg" style="display:none;margin-top:12px;padding:9px 14px;border-radius:9px;font-size:12px;font-weight:600;display:none;align-items:center;gap:7px"></div>
            </form>
        </div>
    </div>
    <?php if ($role === 'admin'): ?>
    <div class="section-lbl"><?= $p['admin_access'] ?></div>
    <div class="qa-grid">
        <a href="/MEMOIR/admin/admin_dashboard.php" class="qa-btn">
            <div class="qa-icon"><i class="fa-solid fa-gauge-high" style="color:var(--gold);font-size:22px"></i></div>
            <div class="qa-label">Dashboard</div>
            <div class="qa-sub">Vue d'ensemble</div>
        </a>
        <a href="/MEMOIR/admin/gerer_documents.php" class="qa-btn">
            <div class="qa-icon"><i class="fa-solid fa-book-open" style="color:var(--gold);font-size:22px"></i></div>
            <div class="qa-label">Documents</div>
            <div class="qa-sub">Gérer le catalogue</div>
        </a>
        <a href="/MEMOIR/admin/users.php" class="qa-btn">
            <div class="qa-icon"><i class="fa-solid fa-users" style="color:var(--gold);font-size:22px"></i></div>
            <div class="qa-label">Utilisateurs</div>
            <div class="qa-sub">Gérer les lecteurs</div>
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- PANEL: HISTORIQUE -->
<?php if ($role === 'client'): ?>
<div class="panel <?= $tab==='historique'?'active':'' ?>">
    <div class="table-wrap">
        <div class="table-header"><i class="fa-solid fa-book"></i> <?= $p['last_loans'] ?></div>
        <table class="data-table">
            <thead><tr><th><?= $p['book_col'] ?></th><th><?= $p['loan_date'] ?></th><th><?= $p['return_date'] ?></th><th><?= $p['status_col'] ?></th></tr></thead>
            <tbody>
            <?php if ($hist_emprunts && $hist_emprunts->num_rows > 0):
                while ($e = $hist_emprunts->fetch_assoc()):
                    $s = $e['statut'] ?? '';
                    $bc = $s==='retourne'?'badge-green':($s==='en_cours'?'badge-amber':'badge-gray');
                    $bl = $s==='retourne'?$p['returned']:($s==='en_cours'?$p['in_progress']:ucfirst($s));
            ?>
            <tr>
                <td><?= htmlspecialchars($e['titre']) ?></td>
                <td><?= $e['date_debut']?date('d/m/Y',strtotime($e['date_debut'])):'—'?></td>
                <td><?= !empty($e['date_fin'])?date('d/m/Y',strtotime($e['date_fin'])):'—'?></td>
                <td><span class="badge <?= $bc ?>"><?= $bl ?></span></td>
            </tr>
            <?php endwhile; else: ?>
            <tr class="empty-row"><td colspan="4"><?= $p['no_loans'] ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="table-wrap">
        <div class="table-header"><i class="fa-solid fa-bag-shopping"></i> <?= $p['last_orders'] ?></div>
        <table class="data-table">
            <thead><tr><th><?= $p['order_num'] ?></th><th><?= $p['amount_col'] ?></th><th><?= $p['status_col'] ?></th></tr></thead>
            <tbody>
            <?php if ($hist_commandes && $hist_commandes->num_rows > 0):
                while ($c = $hist_commandes->fetch_assoc()):
                    $s = $c['statut'] ?? '';
                    $bc = $s==='payee'?'badge-green':($s==='en_attente'?'badge-amber':($s==='annulee'?'badge-red':'badge-gray'));
                    $bl = $s==='payee'?$p['paid']:($s==='en_attente'?$p['pending']:($s==='annulee'?$p['cancelled']:ucfirst($s)));
            ?>
            <tr>
                <td>#<?= str_pad($c['id_commande'],3,'0',STR_PAD_LEFT) ?></td>
                <td><?= number_format((float)$c['total'],2) ?> DA</td>
                <td><span class="badge <?= $bc ?>"><?= $bl ?></span></td>
            </tr>
            <?php endwhile; else: ?>
            <tr class="empty-row"><td colspan="3"><?= $p['no_orders'] ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <a href="/MEMOIR/client/library.php" class="back-link"><i class="fa-solid fa-arrow-left" style="font-size:10px"></i> <?= $p['back_library'] ?></a>
</div>

<!-- PANEL: FAVORIS -->
<div class="panel <?= $tab==='wishlist'?'active':'' ?>">
    <div class="section-lbl"><?= $p['favorites_lbl'] ?> (<?= $nb_wishlist ?> <?= $nb_wishlist > 1 ? $p['fav_books'] : $p['fav_book'] ?>)</div>
    <?php if ($nb_wishlist === 0): ?>
    <div class="empty-state">
        <div class="empty-icon">🤍</div>
        <p><?= $p['empty_fav'] ?></p>
        <a href="/MEMOIR/client/library.php" class="btn-explore"><?= $p['explore_btn'] ?></a>
    </div>
    <?php else: ?>
    <div class="wish-grid">
    <?php foreach ($wishlist_items as $w):
        $dispo = $w['disponible_pour'] ?? 'both';
        $badge_cls = $dispo==='achat'?'badge-amber':($dispo==='emprunt'?'badge-green':'badge-gray');
        $badge_lbl = $dispo==='achat'?$p['buy_tag']:($dispo==='emprunt'?$p['borrow_tag']:$p['both_tag']);
    ?>
    <div class="wish-card">
        <div class="wish-cover">
            <?php if (!empty($w['image_doc'])): ?>
            <img src="/MEMOIR/uploads/<?= htmlspecialchars($w['image_doc']) ?>" alt="<?= htmlspecialchars($w['titre']) ?>">
            <?php else: ?>
            <div style="width:50px;height:68px;background:var(--page-border);border-radius:3px;display:flex;align-items:center;justify-content:center"><i class="fa-solid fa-book" style="color:var(--gold);font-size:18px;opacity:.5"></i></div>
            <?php endif; ?>
            <span class="wish-type-badge <?= $badge_cls ?>" style="<?= $dispo==='achat'?'background:rgba(184,131,42,.9);color:#fff':($dispo==='emprunt'?'background:rgba(46,125,82,.9);color:#fff':'background:rgba(44,31,14,.75);color:#C4A46B') ?>"><?= $badge_lbl ?></span>
        </div>
        <div class="wish-body">
            <div class="wish-title"><?= htmlspecialchars($w['titre']) ?></div>
            <?php if (!empty($w['auteur'])): ?><div class="wish-author"><?= htmlspecialchars($w['auteur']) ?></div><?php endif; ?>
            <?php if (!empty($w['prix']) && (float)$w['prix'] > 0): ?>
            <div class="wish-price"><?= number_format((float)$w['prix'],2) ?> DA</div>
            <?php else: ?>
            <div class="wish-price free"><?= $p['free_lbl'] ?></div>
            <?php endif; ?>
            <div class="wish-actions">
                <a href="/MEMOIR/client/doc_details.php?id=<?= (int)$w['id_doc'] ?>" class="wish-btn-voir"><?= $p['view_book'] ?></a>
                <a href="/MEMOIR/client/remove_wish.php?id=<?= (int)$w['id_wishlist'] ?>" class="wish-btn-del" onclick="return confirm('<?= addslashes($p['confirm_remove']) ?>')" title="×"><i class="fa-solid fa-trash" style="font-size:10px"></i></a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
    <div style="margin-top:16px">
        <a href="/MEMOIR/client/library.php" class="back-link"><i class="fa-solid fa-arrow-left" style="font-size:10px"></i> <?= $p['keep_exploring'] ?></a>
    </div>
    <?php endif; ?>
</div>

<!-- PANEL: MESSAGES -->
<div class="panel <?= $tab==='messages'?'active':'' ?>">
    <div class="section-lbl"><?= $p['msg_title'] ?></div>
    <?php if (empty($user_messages)): ?>
    <div class="empty-state">
        <div class="empty-icon">✉️</div>
        <p><?= $p['msg_empty'] ?></p>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:14px">
    <?php foreach ($user_messages as $um):
        $has_reply = !empty($um['reponse']);
    ?>
    <div style="background:var(--page-white);border:1px solid var(--page-border);border-radius:14px;overflow:hidden;box-shadow:var(--shadow-sm)">
        <div style="background:linear-gradient(135deg,var(--ink) 0%,var(--ink2) 100%);padding:12px 18px;display:flex;align-items:center;justify-content:space-between;gap:10px">
            <div style="display:flex;align-items:center;gap:8px">
                <i class="fa-solid fa-envelope" style="color:var(--gold);font-size:11px"></i>
                <span style="font-weight:700;font-size:13px;color:#FDFAF5"><?= htmlspecialchars($um['subject'] ?? 'Message') ?></span>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:10px;color:rgba(255,255,255,.4)"><?= date('d/m/Y à H:i',strtotime($um['created_at'])) ?></span>
                <?php if ($has_reply): ?>
                <span style="font-size:9px;font-weight:700;background:rgba(46,125,82,.3);color:#4ade80;border:1px solid rgba(46,125,82,.4);padding:2px 9px;border-radius:20px"><?= $p['msg_badge_replied'] ?></span>
                <?php else: ?>
                <span style="font-size:9px;font-weight:700;background:rgba(196,164,107,.15);color:var(--gold);border:1px solid rgba(196,164,107,.3);padding:2px 9px;border-radius:20px"><?= $p['msg_badge_pending'] ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div style="padding:14px 18px;border-bottom:<?= $has_reply?'1px solid var(--page-border)':'none' ?>">
            <div style="font-size:10px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--page-muted);margin-bottom:7px"><?= $p['msg_subject_lbl'] ?></div>
            <div style="font-size:13px;color:var(--page-text);line-height:1.65"><?= nl2br(htmlspecialchars($um['message'])) ?></div>
        </div>
        <div style="padding:14px 18px;background:<?= $has_reply?'rgba(196,164,107,.04)':'var(--page-bg)' ?>">
            <div style="font-size:10px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:<?= $has_reply?'var(--gold-deep)':'var(--page-muted)' ?>;margin-bottom:7px;display:flex;align-items:center;gap:6px">
                <i class="fa-solid fa-reply" style="font-size:9px"></i>
                <?= $p['msg_reply_lbl'] ?>
                <?php if ($has_reply && !empty($um['date_reponse'])): ?>
                <span style="font-size:10px;color:var(--page-muted);font-weight:400;letter-spacing:0;text-transform:none"><?= date('d/m/Y à H:i',strtotime($um['date_reponse'])) ?></span>
                <?php endif; ?>
            </div>
            <?php if ($has_reply): ?>
            <div style="font-size:13px;color:var(--page-text);line-height:1.65;padding:10px 14px;background:rgba(196,164,107,.06);border-left:3px solid var(--gold);border-radius:0 8px 8px 0"><?= nl2br(htmlspecialchars($um['reponse'])) ?></div>
            <?php else: ?>
            <div style="font-size:12px;color:var(--page-muted);font-style:italic"><?= $p['msg_no_reply'] ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>

</div><!-- .profile-page -->

<script>
function toggleEdit() {
    const view = document.getElementById('viewMode');
    const form = document.getElementById('editMode');
    const btn  = document.getElementById('btnEditToggle');
    const editing = form.classList.contains('visible');
    if (editing) {
        form.classList.remove('visible');
        view.style.display = '';
        btn.innerHTML = '<i class="fa-solid fa-pen" style="font-size:10px"></i> <?= addslashes($p['edit_btn']) ?>';
    } else {
        view.style.display = 'none';
        form.classList.add('visible');
        btn.innerHTML = '<i class="fa-solid fa-xmark" style="font-size:10px"></i> <?= addslashes($p['cancel_btn']) ?>';
    }
}

function validatePhone(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
    const err = document.getElementById('phoneError');
    const val = input.value;
    const invalid = val !== '' && (val.length !== 10 || !val.startsWith('0'));
    if (err) err.style.display = invalid ? 'block' : 'none';
    input.style.borderColor = invalid ? 'var(--danger)' : '';
}

function saveProfile(e) {
    // Validate phone
    const phoneInput = document.getElementById('phoneInput');
    const val = phoneInput ? phoneInput.value.trim() : '';
    if (val !== '' && (val.length !== 10 || !val.startsWith('0'))) {
        const err = document.getElementById('phoneError');
        if (err) err.style.display = 'block';
        if (phoneInput) phoneInput.style.borderColor = 'var(--danger)';
        return;
    }

    const btn  = document.getElementById('btnSave');
    const msg  = document.getElementById('saveMsg');
    const form = document.getElementById('profileForm');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="font-size:10px"></i> Enregistrement…';

    const data = new FormData(form);

    fetch(window.location.href, { method: 'POST', body: data })
        .then(r => r.text())
        .then(html => {
            // Parse response to extract updated values
            const parser = new DOMParser();
            const doc    = parser.parseFromString(html, 'text/html');

            // Check for error alert in response
            const errEl = doc.querySelector('.alert-error');
            if (errEl) {
                showMsg(msg, errEl.textContent.trim(), 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk" style="font-size:10px"></i> <?= addslashes($p['save_btn']) ?>';
                return;
            }

            // Update view fields live
            const newPhone  = phoneInput ? phoneInput.value.trim() : '';
            const genderSel = form.querySelector('select[name="gender"]');
            const newGender = genderSel ? genderSel.value : '';

            // Update view mode spans
            const fields = document.querySelectorAll('#viewMode .info-field');
            fields.forEach(f => {
                const lbl = f.querySelector('label');
                const spn = f.querySelector('span');
                if (!lbl || !spn) return;
                const lbl_text = lbl.textContent.trim().toUpperCase();
                if (lbl_text === '<?= strtoupper(addslashes($p['phone'])) ?>') {
                    spn.textContent = newPhone || '—';
                }
                if (lbl_text === '<?= strtoupper(addslashes($p['gender'])) ?>') {
                    spn.textContent = newGender || '—';
                }
            });

            // Update hero phone display
            const heroMeta = document.querySelector('.hero-meta');
            if (heroMeta && newPhone) {
                const phoneSpan = heroMeta.querySelector('span:first-child');
                if (phoneSpan) phoneSpan.innerHTML = '<i class="fa-solid fa-phone" style="font-size:9px;opacity:.5"></i> ' + newPhone;
            }

            showMsg(msg, '<?= addslashes($p['success_info']) ?>', 'success');

            // Close edit mode after short delay
            setTimeout(() => {
                toggleEdit();
                msg.style.display = 'none';
            }, 1800);

            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk" style="font-size:10px"></i> <?= addslashes($p['save_btn']) ?>';
        })
        .catch(() => {
            showMsg(msg, 'Erreur réseau. Réessayez.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk" style="font-size:10px"></i> <?= addslashes($p['save_btn']) ?>';
        });
}

function showMsg(el, text, type) {
    if (!el) return;
    el.style.display = 'flex';
    if (type === 'success') {
        el.style.background = 'var(--success-bg)';
        el.style.border = '1px solid rgba(46,125,82,.25)';
        el.style.color = 'var(--success)';
        el.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + text;
    } else {
        el.style.background = 'var(--danger-bg)';
        el.style.border = '1px solid rgba(192,57,43,.25)';
        el.style.color = 'var(--danger)';
        el.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + text;
    }
}
<?php if ($error && $tab === 'profil'): ?>
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('viewMode').style.display = 'none';
    document.getElementById('editMode').classList.add('visible');
    document.getElementById('btnEditToggle').innerHTML = '<i class="fa-solid fa-xmark" style="font-size:10px"></i> <?= addslashes($p['cancel_btn']) ?>';
});
<?php endif; ?>
</script>
</body>
</html>