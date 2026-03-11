<?php
require_once dirname(__DIR__) . '/index.php';

use App\Auth\Auth;
use App\Models\Stage;
use App\Models\Period;

Auth::requireAuth();

$message = '';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$editStage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::checkCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        die("CSRF token validation failed");
    }
    $data = [
        'period_id' => $_POST['period_id'],
        'title' => $_POST['title'],
        'short_info' => $_POST['short_info'],
        'youtube_url' => $_POST['youtube_url'],
        'whatsapp_number' => $_POST['whatsapp_number']
    ];

    if (isset($_POST['create']) || isset($_POST['update'])) {
        $youtubeUrl = isset($_POST['youtube_url']) ? $_POST['youtube_url'] : '';
        $whatsappNumber = isset($_POST['whatsapp_number']) ? $_POST['whatsapp_number'] : '';

        // Валидация YouTube
        if ($youtubeUrl && !preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $youtubeUrl)) {
            $message = "Ката: YouTube шилтемеси туура эмес форматта!";
        } 
        // Валидация WhatsApp (сан гана жана узундугу 9-15)
        elseif ($whatsappNumber && !preg_match('/^[0-9]{9,15}$/', $whatsappNumber)) {
            $message = "Ката: WhatsApp номери туура эмес! (Мисалы: 996700123456)";
        }
        else {
            if (isset($_POST['create'])) {
                if (Stage::create($data)) {
                    $message = "Жаңы этап ийгиликтүү кошулду!";
                    $action = 'list';
                }
            } elseif (isset($_POST['update'])) {
                $id = $_POST['id'];
                if (Stage::update($id, $data)) {
                    $message = "Этап ийгиликтүү өзгөртүлдү!";
                    $action = 'list';
                }
            }
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    if (Stage::delete($_GET['id'])) {
        $message = "Этап өчүрүлдү!";
    }
    $action = 'list';
}

if ($action === 'edit' && isset($_GET['id'])) {
    $editStage = Stage::find($_GET['id']);
}

$stages = Stage::all();
$periods = Period::all();
?>
<!DOCTYPE html>
<html lang="ky">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Этаптар - Эне Мээрими</title>
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
            <a href="/admin/stages.php" class="block py-3 px-6 bg-pink-700 text-white font-semibold">Этаптар</a>
            <a href="/admin/checklists.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Чеклисттер</a>
            <a href="/admin/files.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Файлдар</a>
            <a href="/admin/settings.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Жөндөөлөр</a>
            <a href="/admin/logout.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 mt-10 transition">Чыгуу</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-10">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Этаптарды башкаруу</h2>
            <?php if ($action === 'list'): ?>
                <a href="?action=add" class="bg-pink-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-pink-700 transition">Жаңы кошуу</a>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <div class="<?php echo strpos($message, 'Ката') !== false ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700'; ?> border px-4 py-3 rounded relative mb-6">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($action === 'add' || $action === 'edit'): ?>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 max-w-2xl">
                <h3 class="text-xl font-bold text-gray-800 mb-6"><?php echo $editStage ? 'Этапты оңдоо' : 'Жаңы этап кошуу'; ?></h3>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCsrfToken(); ?>">
                    <?php if ($editStage): ?>
                        <input type="hidden" name="id" value="<?php echo $editStage['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Периодду тандаңыз</label>
                        <select name="period_id" class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-pink-500 outline-none" required>
                            <?php foreach ($periods as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo ($editStage && $editStage['period_id'] == $p['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Аталышы (Тема)</label>
                        <input type="text" name="title" value="<?php echo $editStage ? htmlspecialchars($editStage['title'] ?? '') : ''; ?>" 
                               class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-pink-500 outline-none" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Кыскача маалымат</label>
                        <textarea name="short_info" rows="4" class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-pink-500 outline-none"><?php echo $editStage ? htmlspecialchars($editStage['short_info'] ?? '') : ''; ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">YouTube URL</label>
                        <input type="text" name="youtube_url" value="<?php echo $editStage ? htmlspecialchars($editStage['youtube_url'] ?? '') : ''; ?>" 
                               placeholder="https://www.youtube.com/watch?v=..." class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-pink-500 outline-none">
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">WhatsApp Номер</label>
                        <input type="text" name="whatsapp_number" value="<?php echo $editStage ? htmlspecialchars($editStage['whatsapp_number'] ?? '') : ''; ?>" 
                               placeholder="996700123456" class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-pink-500 outline-none">
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" name="<?php echo $editStage ? 'update' : 'create'; ?>" 
                                class="bg-pink-600 text-white font-bold py-3 px-8 rounded-xl hover:bg-pink-700 transition">
                            Сактоо
                        </button>
                        <a href="?action=list" class="bg-gray-200 text-gray-700 font-bold py-3 px-8 rounded-xl hover:bg-gray-300 transition">Жокко чыгаруу</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-pink-50">
                            <th class="p-4 font-bold text-pink-700 border-b">Тема</th>
                            <th class="p-4 font-bold text-pink-700 border-b">Период</th>
                            <th class="p-4 font-bold text-pink-700 border-b">Дата</th>
                            <th class="p-4 font-bold text-pink-700 border-b text-right">Аракеттер</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stages as $s): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4 border-b font-semibold text-gray-800"><?php echo htmlspecialchars($s['title']); ?></td>
                                <td class="p-4 border-b text-gray-600">
                                    <span class="bg-pink-100 text-pink-700 px-2 py-1 rounded text-sm"><?php echo htmlspecialchars($s['period_name']); ?></span>
                                </td>
                                <td class="p-4 border-b text-gray-500 text-sm"><?php echo date('d.m.Y', strtotime($s['created_at'])); ?></td>
                                <td class="p-4 border-b text-right">
                                    <a href="?action=edit&id=<?php echo $s['id']; ?>" class="text-blue-600 hover:underline mr-4">Оңдоо</a>
                                    <a href="?action=delete&id=<?php echo $s['id']; ?>" class="text-red-600 hover:underline" 
                                       onclick="return confirm('Чын эле өчүрөсүзбү?')">Өчүрүү</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($stages)): ?>
                            <tr>
                                <td colspan="4" class="p-10 text-center text-gray-400">Маалымат жок</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
