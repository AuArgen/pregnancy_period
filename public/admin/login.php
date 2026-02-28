<?php
require_once dirname(__DIR__) . '/index.php';

use App\Auth\Auth;

if (Auth::check()) {
    if (!headers_sent()) {
        header('Location: /admin/index.php');
        exit;
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::checkCsrfToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed");
    }
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (Auth::login($username, $password)) {
        if (!headers_sent()) {
            header('Location: /admin/index.php');
            exit;
        }
    } else {
        $error = 'Логин же пароль туура эмес!';
    }
}
?>
<!DOCTYPE html>
<html lang="ky">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Кирүү - Админ Панель</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; }
    </style>
</head>
<body class="bg-pink-50 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-pink-100">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-pink-600 mb-2">Эне Мээрими</h1>
            <p class="text-gray-500">Администратордук панелге кирүү</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCsrfToken(); ?>">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                    Логин
                </label>
                <input class="shadow appearance-none border rounded-xl w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-pink-500" 
                       id="username" name="username" type="text" placeholder="Логинди киргизиңиз" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    Пароль
                </label>
                <input class="shadow appearance-none border rounded-xl w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-pink-500" 
                       id="password" name="password" type="password" placeholder="********" required>
            </div>
            <div class="flex items-center justify-between">
                <button class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-6 rounded-xl focus:outline-none focus:shadow-outline w-full transition duration-300 shadow-md" 
                        type="submit">
                    Кирүү
                </button>
            </div>
        </form>
    </div>
</body>
</html>
