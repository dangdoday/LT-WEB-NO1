<?php
session_start();

function requireLogin() {
    if (!isset($_SESSION['admin'])) {
        header("Location: /final-api/login.php");
        exit;
    }
}
