<?php
require_once __DIR__ . '/index.php';

use App\Models\Period;
use App\Models\Stage;
use App\Config\Database;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$period = Period::find($id);

if (!$period) {
    header('Location: /');
    exit;
}

// Ушул периодго таандык бардык этаптарды алуу
$db = Database::getInstance();
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($searchQuery) {
    $sql = "SELECT * FROM stages WHERE period_id = ? AND (title ILIKE ? OR short_info ILIKE ?) ORDER BY created_at ASC";
    $stmt = $db->prepare($sql);
    $searchTerm = "%$searchQuery%";
    $stmt->execute([$id, $searchTerm, $searchTerm]);
} else {
    $stmt = $db->prepare("SELECT * FROM stages WHERE period_id = ? ORDER BY created_at ASC");
    $stmt->execute([$id]);
}
$stages = $stmt->fetchAll();

// YouTube URL форматын оңдоо функциясы (Embed кылуу үчүн)
function getYoutubeEmbedUrl($url) {
    if (!$url) return '';
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
    if (preg_match($pattern, $url, $match)) {
        return "https://www.youtube.com/embed/" . $match[1];
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="ky">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($period['name']); ?> - Эне Мээрими</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; }
    </style>
</head>
<body class="bg-white">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-pink-50">
        <div class="p-6 flex justify-between items-center max-w-7xl mx-auto">
            <a href="/" class="text-2xl font-bold text-pink-600">Эне Мээрими</a>
            <a href="/" class="text-gray-600 font-semibold hover:text-pink-600 transition flex items-center">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Артка
            </a>
        </div>
    </nav>

    <!-- Header -->
    <header class="bg-pink-50 py-12 px-6">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-4xl font-bold text-pink-800 mb-4"><?php echo htmlspecialchars($period['name']); ?></h1>
            <p class="text-pink-600 opacity-80 mb-8">Бул периоддогу бардык маалыматтар жана кеңештер</p>

            <!-- Search Bar inside Period -->
            <form action="/period.php" method="GET" class="max-w-xl mx-auto flex">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" 
                       placeholder="Бул периоддон издөө..." 
                       class="flex-1 p-4 rounded-l-2xl border-none shadow-md focus:ring-2 focus:ring-pink-400 outline-none text-gray-700">
                <button type="submit" class="bg-pink-600 text-white px-8 rounded-r-2xl font-bold shadow-md hover:bg-pink-700 transition">Издөө</button>
            </form>
        </div>
    </header>

    <!-- Stages Content -->
    <main class="max-w-4xl mx-auto py-16 px-6">
        <?php if ($searchQuery): ?>
            <div class="mb-10">
                <h3 class="text-xl font-bold text-gray-800">Издөө жыйынтыгы: "<?php echo htmlspecialchars($searchQuery); ?>"</h3>
                <a href="/period.php?id=<?php echo $id; ?>" class="text-pink-600 hover:underline text-sm">Баарын көрсөтүү</a>
            </div>
        <?php endif; ?>

        <?php if (empty($stages)): ?>
            <div class="text-center text-gray-400 py-20">
                <?php echo $searchQuery ? 'Тилекке каршы, издөө боюнча эч нерсе табылган жок.' : 'Бул период боюнча азырынча маалымат жок.'; ?>
            </div>
        <?php else: ?>
            <div class="space-y-12">
                <?php foreach ($stages as $index => $s): ?>
                    <article id="stage-<?php echo $s['id']; ?>" class="bg-white p-8 rounded-3xl shadow-sm border border-pink-50 hover:shadow-md transition duration-300">
                        <div class="flex items-center gap-4 mb-6">
                            <span class="bg-pink-100 text-pink-600 w-10 h-10 rounded-full flex items-center justify-center font-bold">
                                <?php echo $index + 1; ?>
                            </span>
                            <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($s['title']); ?></h2>
                        </div>
                        
                        <div class="prose prose-pink max-w-none text-gray-700 leading-relaxed mb-8">
                            <?php echo nl2br(htmlspecialchars($s['short_info'])); ?>
                        </div>

                        <div class="flex flex-wrap gap-4 mb-8">
                            <?php if ($s['youtube_url']): ?>
                                <a href="<?php echo htmlspecialchars($s['youtube_url']); ?>" target="_blank" 
                                   class="flex items-center gap-2 bg-red-50 text-red-600 px-6 py-3 rounded-2xl font-bold hover:bg-red-100 transition border border-red-100">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                                    Видеону көрүү (YouTube)
                                </a>
                            <?php endif; ?>

                            <?php if ($s['whatsapp_number']): ?>
                                <?php 
                                    $waMessage = urlencode("Саламатсызбы! Мен '{$s['title']}' боюнча суроо берейин дегем.");
                                    $waUrl = "https://wa.me/{$s['whatsapp_number']}?text={$waMessage}";
                                ?>
                                <a href="<?php echo $waUrl; ?>" target="_blank" 
                                   class="flex items-center gap-2 bg-green-50 text-green-600 px-6 py-3 rounded-2xl font-bold hover:bg-green-100 transition border border-green-100">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.417-.003 6.557-5.338 11.892-11.893 11.892-1.997-.001-3.951-.5-5.688-1.448l-6.305 1.652zm6.599-3.835c1.544.917 3.31 1.401 5.109 1.402 5.512 0 9.998-4.486 10-9.998.001-2.67-1.039-5.179-2.926-7.068-1.887-1.889-4.394-2.93-7.064-2.931-5.515 0-10.002 4.487-10.003 9.999 0 1.764.463 3.485 1.339 5.006l-.859 3.138 3.204-.84zm11.163-7.854c-.29-.145-1.714-.847-1.979-.942-.266-.096-.459-.145-.653.145-.193.291-.748.944-.917 1.138-.17.193-.339.217-.628.072-.29-.145-1.226-.452-2.335-1.441-.863-.77-1.445-1.721-1.614-2.012-.17-.291-.018-.448.126-.592.13-.13.29-.339.435-.509.145-.17.193-.291.29-.485.097-.194.048-.364-.024-.509-.072-.145-.653-1.573-.894-2.155-.235-.568-.475-.489-.653-.497-.17-.008-.363-.009-.556-.009-.193 0-.507.072-.773.363-.266.291-1.015 1.144-1.015 2.793 0 1.647 1.185 3.242 1.353 3.461.169.218 2.33 3.558 5.645 4.992.788.341 1.405.545 1.885.698.791.252 1.512.216 2.081.131.634-.094 1.714-.7 1.956-1.377.243-.677.243-1.259.17-1.377-.073-.119-.266-.194-.556-.339z"/></svg>
                                    WhatsApp-тан жазуу
                                </a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-50 py-12 border-t border-gray-100 mt-20">
        <div class="max-w-7xl mx-auto px-6 text-center text-gray-500 text-sm">
            <p>© <?php echo date('Y'); ?> Эне Мээрими. Бардык укуктар корголгон.</p>
        </div>
    </footer>
</body>
</html>
