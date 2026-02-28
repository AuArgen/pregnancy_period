<?php
require_once dirname(__DIR__) . '/index.php';

use App\Auth\Auth;
use App\Models\Settings;

Auth::requireAuth();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::checkCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = "Ката: CSRF токен ката!";
    } else {
        $settingsData = [
            'hero_title' => $_POST['hero_title'] ?? '',
            'hero_description' => $_POST['hero_description'] ?? '',
            'hero_image' => $_POST['hero_image'] ?? '',
        ];
        
        Settings::updateMany($settingsData);
        $message = "Жөндөөлөр ийгиликтүү сакталды!";
    }
}

$settings = Settings::all();
?>
<!DOCTYPE html>
<html lang="ky">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Жөндөөлөр - Эне Мээрими</title>
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
            <a href="/admin/index.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Башкы бет</a>
            <a href="/admin/periods.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Периоддор</a>
            <a href="/admin/stages.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Этаптар</a>
            <a href="/admin/checklists.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Чеклисттер</a>
            <a href="/admin/files.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Файлдар</a>
            <a href="/admin/settings.php" class="block py-3 px-6 bg-pink-700 text-white font-semibold">Жөндөөлөр</a>
            <a href="/admin/logout.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 mt-10 transition">Чыгуу</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-10">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Сайттын жөндөөлөрү</h2>
        </div>

        <?php if ($message): ?>
            <div class="<?php echo strpos($message, 'Ката') !== false ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700'; ?> border px-4 py-3 rounded relative mb-6">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 max-w-4xl">
            <h3 class="text-xl font-bold text-gray-800 mb-6">Hero бөлүмүн (Башкы барак) жөндөө</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCsrfToken(); ?>">
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Hero сүрөтүнүн URL дареги</label>
                    <input type="text" name="hero_image" value="<?php echo htmlspecialchars($settings['hero_image'] ?? ''); ?>" 
                           class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-pink-500 outline-none" placeholder="https://example.com/image.jpg">
                    <p class="text-xs text-gray-400 mt-1">Башкы беттеги фондогу сүрөт.</p>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Hero Заголовок (Title)</label>
                    <input type="text" name="hero_title" value="<?php echo htmlspecialchars($settings['hero_title'] ?? ''); ?>" 
                           class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-pink-500 outline-none">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Hero Текст (Description)</label>
                    <textarea name="hero_description" rows="4" class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-pink-500 outline-none"><?php echo htmlspecialchars($settings['hero_description'] ?? ''); ?></textarea>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="bg-pink-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-pink-700 transition">Сактоо</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
