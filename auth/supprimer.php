<?php
require_once __DIR__ . "/../includes/db.php";
//include 'includes/db.php';

$id = $_GET['id'] ?? 0;
$pdo->prepare("DELETE FROM books WHERE id=?")->execute([$id]);

header("Location:../client/dcmnt.php");
exit;