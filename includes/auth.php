<?php
session_start();

function requireRole($role) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
        header("Location: /memoir/login.php");
        exit;
    }
}
