<?php
require_once dirname(__DIR__) . '/index.php';

use App\Auth\Auth;
use App\Models\Checklist;

Auth::requireAuth();

$message = '';
$action = $_GET['action'] ?? 'list';
$editChecklist = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::checkCsrfToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed");
    }
    if (isset($_POST['create']) || isset($_POST['update'])) {
        $title = $_POST['title'] ?? '';
        $itemsRaw = $_POST['items'] ?? '';
        $items = array_filter(array_map('trim', explode("\n", $itemsRaw)));
        $isActive = isset($_POST['is_active']);

        if (isset($_POST['create'])) {
            if (Checklist::create($title, $items)) {
                $message = "Чеклист ийгиликтүү кошулду!";
            }
        } else {
            $id = $_POST['id'] ?? 0;
            if (Checklist::update($id, $title, $items, $isActive)) {
                $message = "Чеклист ийгиликтүү өзгөртүлдү!";
                $action = 'list';
            }
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    if (Checklist::delete($_GET['id'])) {
        $message = "Чеклист өчүрүлдү!";
    }
    $action = 'list';
}

if ($action === 'edit' && isset($_GET['id'])) {
    $editChecklist = Checklist::find($_GET['id']);
}

$checklists = Checklist::all();
?>
<!DOCTYPE html>
<html lang="ky">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Чеклисттер - Эне Мээрими</title>
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
            <a href="/admin/checklists.php" class="block py-3 px-6 bg-pink-700 text-white font-semibold">Чеклисттер</a>
            <a href="/admin/files.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Файлдар</a>
            <a href="/admin/settings.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Жөндөөлөр</a>
            <a href="/admin/logout.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 mt-10 transition">Чыгуу</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-10">
        <h2 class="text-3xl font-bold text-gray-800 mb-8">Чеклисттерди башкаруу</h2>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-fit">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    <?php echo $editChecklist ? 'Чеклистти оңдоо' : 'Жаңы чеклист кошуу'; ?>
                </h3>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCsrfToken(); ?>">
                    <?php if ($editChecklist): ?>
                        <input type="hidden" name="id" value="<?php echo $editChecklist['id']; ?>">
                    <?php endif; ?>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Аталышы</label>
                        <input type="text" name="title" value="<?php echo $editChecklist ? htmlspecialchars($editChecklist['title']) : ''; ?>" 
                               class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-pink-500 outline-none" required
                               placeholder="Мис: Төрөт үйүнө керектелүүчү буюмдар">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Тизме элементтери (ар бир сапта бирден)</label>
                                  <textarea name="items" rows="10" class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-pink-500 outline-none" required
                                  placeholder="Памперс&#10;Кийимдер&#10;Суусундук..."><?php 
                                    if ($editChecklist) {
                                        $itemsArr = json_decode($editChecklist['items'], true) ?: [];
                                        $displayText = [];
                                        foreach ($itemsArr as $itemVal) {
                                            if (is_array($itemVal)) {
                                                $displayText[] = isset($itemVal['text']) ? $itemVal['text'] : (isset($itemVal[0]) ? $itemVal[0] : '');
                                            } else {
                                                $displayText[] = $itemVal;
                                            }
                                        }
                                        echo htmlspecialchars(implode("\n", $displayText));
                                    }
                                  ?></textarea>
                    </div>
                    <div class="mb-6 flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" class="mr-2" 
                               <?php echo (!$editChecklist || $editChecklist['is_active']) ? 'checked' : ''; ?>>
                        <label for="is_active" class="text-gray-700 text-sm font-bold">Активдүү</label>
                    </div>
                    <button type="submit" name="<?php echo $editChecklist ? 'update' : 'create'; ?>" 
                            class="w-full bg-pink-600 text-white font-bold py-3 rounded-xl hover:bg-pink-700 transition">
                        <?php echo $editChecklist ? 'Сактоо' : 'Кошуу'; ?>
                    </button>
                    <?php if ($editChecklist): ?>
                        <a href="/admin/checklists.php" class="block text-center text-gray-500 mt-4 underline">Жокко чыгаруу</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Table -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-pink-50">
                            <th class="p-4 font-bold text-pink-700 border-b">Аталышы</th>
                            <th class="p-4 font-bold text-pink-700 border-b">Элементтер</th>
                            <th class="p-4 font-bold text-pink-700 border-b">Статус</th>
                            <th class="p-4 font-bold text-pink-700 border-b text-right">Аракеттер</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($checklists as $c): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4 border-b font-semibold text-gray-800"><?php echo htmlspecialchars($c['title']); ?></td>
                                <td class="p-4 border-b text-gray-600 text-sm">
                                    <?php 
                                        $items = json_decode($c['items'], true);
                                        echo count($items) . " элемент";
                                    ?>
                                </td>
                                <td class="p-4 border-b">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold <?php echo $c['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                        <?php echo $c['is_active'] ? 'Активдүү' : 'Өчүрүлгөн'; ?>
                                    </span>
                                </td>
                                <td class="p-4 border-b text-right">
                                    <a href="?action=edit&id=<?php echo $c['id']; ?>" class="text-blue-600 hover:underline mr-4">Оңдоо</a>
                                    <a href="?action=delete&id=<?php echo $c['id']; ?>" class="text-red-600 hover:underline" 
                                       onclick="return confirm('Чын эле өчүрөсүзбү?')">Өчүрүү</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($checklists)): ?>
                            <tr>
                                <td colspan="4" class="p-10 text-center text-gray-400">Чеклисттер жок.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
