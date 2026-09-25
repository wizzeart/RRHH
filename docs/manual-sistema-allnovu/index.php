<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['guser_id'])) {
    header('Location: ../../login.html');
    exit;
}

header('Location: guia.php');
exit;
