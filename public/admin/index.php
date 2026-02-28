<?php
require_once dirname(__DIR__) . '/index.php';

use App\Auth\Auth;
use App\Config\Database;

Auth::requireAuth();

$db = Database::getInstance();

// Статистика алуу
$periodsCount = $db->query("SELECT COUNT(*) FROM periods")->fetchColumn();
$stagesCount = $db->query("SELECT COUNT(*) FROM stages")->fetchColumn();
$checklistsCount = $db->query("SELECT COUNT(*) FROM checklists")->fetchColumn();

?>
<!DOCTYPE html>
<html lang="ky">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель - Эне Мээрими</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex">
    <!-- Sidebar -->
    <div class="w-64 bg-pink-600 min-h-screen shadow-lg">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-white">Эне Мээрими</h1>
            <p class="text-pink-200 text-sm">Админ Панель</p>
        </div>
        <nav class="mt-6">
            <a href="/" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Сайтка өтүү</a>
            <div class="border-t border-pink-500 my-2 opacity-30"></div>
            <a href="/admin/index.php" class="block py-3 px-6 bg-pink-700 text-white font-semibold">Башкы бет</a>
            <a href="/admin/periods.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Периоддор</a>
            <a href="/admin/stages.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Этаптар</a>
            <a href="/admin/checklists.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Чеклисттер</a>
            <a href="/admin/files.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Файлдар</a>
            <a href="/admin/settings.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Жөндөөлөр</a>
            <a href="/admin/logout.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 mt-10 transition">Чыгуу</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-10">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Добро пожаловать, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Админ'); ?>!</h2>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-pink-100">
                <p class="text-gray-500 mb-1">Периоддор (Категориялар)</p>
                <p class="text-4xl font-bold text-pink-600"><?php echo $periodsCount; ?></p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-pink-100">
                <p class="text-gray-500 mb-1">Этаптар (Маалыматтар)</p>
                <p class="text-4xl font-bold text-pink-600"><?php echo $stagesCount; ?></p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-pink-100">
                <p class="text-gray-500 mb-1">Чеклисттер</p>
                <p class="text-4xl font-bold text-pink-600"><?php echo $checklistsCount; ?></p>
            </div>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Тез аракеттер</h3>
            <div class="flex gap-4">
                <a href="/admin/periods.php" class="bg-pink-100 text-pink-700 px-4 py-2 rounded-lg font-semibold hover:bg-pink-200 transition">Период кошуу</a>
                <a href="/admin/stages.php" class="bg-pink-100 text-pink-700 px-4 py-2 rounded-lg font-semibold hover:bg-pink-200 transition">Жаңы маалымат кошуу</a>
                <a href="/admin/checklists.php" class="bg-pink-100 text-pink-700 px-4 py-2 rounded-lg font-semibold hover:bg-pink-200 transition">Жаңы чеклист</a>
                <a href="/admin/files.php" class="bg-pink-100 text-pink-700 px-4 py-2 rounded-lg font-semibold hover:bg-pink-200 transition">Файлдар</a>
                <a href="/admin/settings.php" class="bg-pink-100 text-pink-700 px-4 py-2 rounded-lg font-semibold hover:bg-pink-200 transition">Жөндөөлөр</a>
            </div>
        </div>
    </div>
</body>
</html>
