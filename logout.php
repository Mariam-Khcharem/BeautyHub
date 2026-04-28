<?php
if (session_status() === PHP_SESSION_NONE) session_start();
session_destroy();
session_start();
$_SESSION['flash'] = ['type' => 'success', 'text' => 'Vous avez été déconnecté(e). À bientôt !'];
header('Location: index.php');
exit;
