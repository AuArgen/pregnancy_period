<?php
require_once dirname(__DIR__) . '/index.php';
use App\Auth\Auth;
Auth::logout();
header('Location: /admin/login.php');
exit;
