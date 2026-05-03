<?php
session_start();
session_unset();
session_destroy();
 
// Redirect to the main library page (public homepage before login)
header("Location: /MEMOIR/client/library.php");
exit();
?>
 