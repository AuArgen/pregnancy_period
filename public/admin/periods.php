<?php
require_once dirname(__DIR__) . '/index.php';

use App\Auth\Auth;
use App\Models\Period;

Auth::requireAuth();

$message = '';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$editPeriod = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    if (empty($name)) {
        $message = "Ката: Аталышы бош болбошу керек!";
    } else {
        if (!Auth::checkCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
            die("CSRF token validation failed");
        }
        if (isset($_POST['create'])) {
            $sortOrder = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
            if (Period::create($name, $sortOrder)) {
                $message = "Период ийгиликтүү кошулду!";
            }
        } elseif (isset($_POST['update'])) {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $sortOrder = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
            if (Period::update($id, $name, $sortOrder)) {
                $message = "Период ийгиликтүү өзгөртүлдү!";
                $action = 'list';
            }
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    if (Period::delete($_GET['id'])) {
        $message = "Период өчүрүлдү!";
    }
    $action = 'list';
}

if ($action === 'edit' && isset($_GET['id'])) {
    $editPeriod = Period::find($_GET['id']);
}

$periods = Period::all();
?>
<!DOCTYPE html>
<html lang="ky">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Периоддор - Эне Мээрими</title>
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
            <a href="/admin/periods.php" class="block py-3 px-6 bg-pink-700 text-white font-semibold">Периоддор</a>
            <a href="/admin/stages.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Этаптар</a>
            <a href="/admin/checklists.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Чеклисттер</a>
            <a href="/admin/files.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Файлдар</a>
            <a href="/admin/settings.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Жөндөөлөр</a>
            <a href="/admin/logout.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 mt-10 transition">Чыгуу</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-10">
        <h2 class="text-3xl font-bold text-gray-800 mb-8">Периоддорду башкаруу</h2>

        <?php if ($message): ?>
            <div class="<?php echo strpos($message, 'Ката') !== false ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700'; ?> border px-4 py-3 rounded relative mb-6">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-fit">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    <?php echo $editPeriod ? 'Периодду оңдоо' : 'Жаңы период кошуу'; ?>
                </h3>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCsrfToken(); ?>">
                    <?php if ($editPeriod): ?>
                        <input type="hidden" name="id" value="<?php echo $editPeriod['id']; ?>">
                    <?php endif; ?>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Аталышы</label>
                        <input type="text" name="name" value="<?php echo $editPeriod ? htmlspecialchars($editPeriod['name']) : ''; ?>" 
                               class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-pink-500 outline-none" required>
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Иреттөө (Sort Order)</label>
                        <input type="number" name="sort_order" value="<?php echo $editPeriod ? $editPeriod['sort_order'] : 0; ?>" 
                               class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-pink-500 outline-none">
                    </div>
                    <button type="submit" name="<?php echo $editPeriod ? 'update' : 'create'; ?>" 
                            class="w-full bg-pink-600 text-white font-bold py-3 rounded-xl hover:bg-pink-700 transition">
                        <?php echo $editPeriod ? 'Сактоо' : 'Кошуу'; ?>
                    </button>
                    <?php if ($editPeriod): ?>
                        <a href="/admin/periods.php" class="block text-center text-gray-500 mt-4 underline">Жокко чыгаруу</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Table -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-pink-50">
                            <th class="p-4 font-bold text-pink-700 border-b">ID</th>
                            <th class="p-4 font-bold text-pink-700 border-b">Аталышы</th>
                            <th class="p-4 font-bold text-pink-700 border-b text-center">Ирет</th>
                            <th class="p-4 font-bold text-pink-700 border-b text-right">Аракеттер</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($periods as $p): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4 border-b text-gray-600"><?php echo $p['id']; ?></td>
                                <td class="p-4 border-b font-semibold text-gray-800"><?php echo htmlspecialchars($p['name']); ?></td>
                                <td class="p-4 border-b text-center text-gray-600"><?php echo $p['sort_order']; ?></td>
                                <td class="p-4 border-b text-right">
                                    <a href="?action=edit&id=<?php echo $p['id']; ?>" class="text-blue-600 hover:underline mr-4">Оңдоо</a>
                                    <a href="?action=delete&id=<?php echo $p['id']; ?>" class="text-red-600 hover:underline" 
                                       onclick="return confirm('Чын эле өчүрөсүзбү?')">Өчүрүү</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
