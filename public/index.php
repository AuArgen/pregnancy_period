<?php

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = dirname(__DIR__) . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\SchemaInitializer;
use App\Models\Period;
use App\Models\Checklist;
use App\Models\Stage;
use App\Models\Settings;
use App\Auth\Auth;

if (!defined('SESSION_STARTED')) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    define('SESSION_STARTED', true);
}

if (!defined('INITIALIZED')) {
    try {
        SchemaInitializer::init();
        define('INITIALIZED', true);
    } catch (\Exception $e) {
        error_log("Initialization failed: " . $e->getMessage());
    }
}

// Эгер бул файл түздөн-түз чакырылса (index.php же башка тамыр файл катары), анда HTMLди көрсөтөбүз.
// Эгер башка файлдар тарабынан require кылынса (мисалы /admin/*), анда HTMLди көрсөтпөйбүз.
$allowed_scripts = ['/index.php'];
if (in_array($_SERVER['SCRIPT_NAME'], $allowed_scripts)) {
    $searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
    $periods = Period::all();
    $checklists = Checklist::all();
    $searchResults = $searchQuery ? Stage::search($searchQuery) : [];
    
    $settings = Settings::all();
    $heroTitle = $settings['hero_title'] ?? 'Кош бойлуулук - бул кереметтүү саякат';
    $heroDescription = $settings['hero_description'] ?? 'Ар бир этапта сизди колдойбуз. Пайдалуу маалыматтар, видео-сабактар жана адистердин кеңештери.';
    $heroImage = $settings['hero_image'] ?? 'https://www.bipolar.su/wp-content/uploads/2021/05/beremennost-e1673450643393.jpg';
?>
<!DOCTYPE html>
<html lang="ky">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Эне Мээрими - Кош бойлуу аялдар үчүн портал</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; }
        .hero-bg { 
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('<?php echo $heroImage; ?>');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="bg-white">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-pink-50">
        <div class="p-6 flex justify-between items-center max-w-7xl mx-auto">
            <a href="/" class="text-2xl font-bold text-pink-600">Эне Мээрими</a>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex space-x-8 text-gray-600 font-semibold items-center">
                <a href="/" class="hover:text-pink-600 transition">Башкы бет</a>
                <a href="#periods" class="hover:text-pink-600 transition">Периоддор</a>
                <a href="#checklists" class="hover:text-pink-600 transition">Чеклисттер</a>
                <?php if (Auth::check()): ?>
                    <a href="/admin/index.php" class="bg-pink-100 text-pink-600 px-5 py-2.5 rounded-xl hover:bg-pink-200 transition">Админ панел</a>
                <?php else: ?>
                    <a href="/admin/login.php" class="bg-pink-100 text-pink-600 px-5 py-2.5 rounded-xl hover:bg-pink-200 transition">Кирүү</a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <button id="menu-btn" class="md:hidden text-pink-600 focus:outline-none">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu Content -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-pink-50 py-4 px-6 space-y-4 shadow-xl animate-fade-in-down">
            <a href="/" class="block text-gray-700 font-semibold hover:text-pink-600 transition">Башкы бет</a>
            <a href="#periods" class="block text-gray-700 font-semibold hover:text-pink-600 transition">Периоддор</a>
            <a href="#checklists" class="block text-gray-700 font-semibold hover:text-pink-600 transition">Чеклисттер</a>
            <?php if (Auth::check()): ?>
                <a href="/admin/index.php" class="block bg-pink-50 text-pink-600 px-4 py-3 rounded-xl font-bold text-center">Админ панел</a>
            <?php else: ?>
                <a href="/admin/login.php" class="block bg-pink-50 text-pink-600 px-4 py-3 rounded-xl font-bold text-center">Кирүү</a>
            <?php endif; ?>
        </div>
    </nav>

<script>
    const btn = document.getElementById('menu-btn');
    const menu = document.getElementById('mobile-menu');

    btn.addEventListener('click', () => {
        menu.classList.toggle('hidden');
    });

    // Закрывать меню при клике на ссылки
    document.querySelectorAll('#mobile-menu a').forEach(link => {
        link.addEventListener('click', () => {
            menu.classList.add('hidden');
        });
    });

    // Accordion Logic
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.accordion-header').forEach(header => {
            header.addEventListener('click', () => {
                const item = header.parentElement;
                const content = header.nextElementSibling;
                const icon = header.querySelector('.accordion-icon');
                
                // Close other items if needed (optional)
                /*
                document.querySelectorAll('.accordion-content').forEach(c => {
                    if (c !== content) {
                        c.classList.add('hidden');
                        c.parentElement.querySelector('.accordion-icon').classList.remove('rotate-180');
                    }
                });
                */

                content.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
            });
        });
    });
</script>

    <header class="hero-bg py-24 px-6 text-center">
        <div class="max-w-3xl mx-auto">
            <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-6"><?php echo htmlspecialchars($heroTitle); ?></h2>
            <p class="text-lg text-white/90 mb-8 font-medium"><?php echo htmlspecialchars($heroDescription); ?></p>
            
            <!-- Search Bar -->
            <form action="/" method="GET" class="max-w-xl mx-auto mb-10 flex">
                <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" 
                       placeholder="Кандай маалымат издейсиз?" 
                       class="flex-1 p-4 rounded-l-2xl border-none shadow-md focus:ring-2 focus:ring-pink-400 outline-none text-gray-700">
                <button type="submit" class="bg-pink-600 text-white px-8 rounded-r-2xl font-bold shadow-md hover:bg-pink-700 transition">Издөө</button>
            </form>

            <a href="#periods" class="bg-pink-600 text-white px-8 py-4 rounded-2xl font-bold text-lg shadow-lg hover:bg-pink-700 transition inline-block">Маалыматтарды көрүү</a>
        </div>
    </header>

    <?php if ($searchQuery): ?>
    <!-- Search Results -->
    <section class="py-16 max-w-7xl mx-auto px-6">
        <h3 class="text-2xl font-bold text-gray-800 mb-8">Издөө жыйынтыгы: "<?php echo htmlspecialchars($searchQuery); ?>"</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($searchResults as $s): ?>
                <a href="/period.php?id=<?php echo $s['period_id']; ?>#stage-<?php echo $s['id']; ?>" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <h4 class="font-bold text-pink-700 mb-2"><?php echo htmlspecialchars($s['title']); ?></h4>
                    <p class="text-gray-500 text-sm line-clamp-2"><?php echo htmlspecialchars($s['short_info']); ?></p>
                    <span class="text-xs text-pink-400 mt-3 block"><?php echo htmlspecialchars($s['period_name']); ?></span>
                </a>
            <?php endforeach; ?>
            
            <?php if (empty($searchResults)): ?>
                <div class="col-span-full text-center text-gray-400 py-10">
                    Тилекке каршы, эч нерсе табылган жок.
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Periods Section -->
    <section id="periods" class="py-20 max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
            <h3 class="text-3xl font-bold text-gray-800 mb-4">Кош бойлуулуктун этаптары</h3>
            <p class="text-gray-500">Тиешелүү периодду тандап, кенен маалымат алыңыз</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($periods as $p): ?>
                <a href="/period.php?id=<?php echo $p['id']; ?>" class="group">
                    <div class="bg-pink-50 p-8 rounded-3xl border border-pink-100 transition duration-300 group-hover:shadow-xl group-hover:-translate-y-2 group-hover:bg-white text-center">
                        <div class="w-16 h-16 bg-pink-200 rounded-2xl flex items-center justify-center mx-auto mb-6 text-pink-600 text-2xl">
                            🌸
                        </div>
                        <h4 class="text-xl font-bold text-pink-800 mb-2"><?php echo htmlspecialchars($p['name']); ?></h4>
                        <p class="text-pink-600 opacity-70 mb-4"><?php echo $p['stages_count']; ?> маалымат &rarr;</p>
                    </div>
                </a>
            <?php endforeach; ?>
            
            <?php if (empty($periods)): ?>
                <div class="col-span-full text-center text-gray-400 py-10">
                    Азырынча маалыматтар кошула элек.
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Checklists Section -->
    <section id="checklists" class="py-20 bg-pink-50">
            <div class="max-w-4xl mx-auto px-6">
            <div class="text-center mb-16">
                <h3 class="text-3xl font-bold text-gray-800 mb-4">Пайдалуу чеклисттер</h3>
                <p class="text-gray-500">Керектүү буюмдардын жана иштердин тизмеси</p>
            </div>

            <div class="space-y-4">
                <?php foreach ($checklists as $c): ?>
                    <div class="bg-white rounded-3xl shadow-sm border border-pink-100 overflow-hidden">
                        <button class="w-full p-8 flex justify-between items-center text-left focus:outline-none accordion-header hover:bg-pink-50/30 transition-colors">
                            <h4 class="text-xl font-bold text-pink-800 flex items-center">
                                <!-- <span class="mr-3 text-2xl">📝</span> -->
                                <?php echo htmlspecialchars($c['title']); ?>
                            </h4>
                            <svg class="w-6 h-6 text-pink-500 transform transition-transform duration-300 accordion-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div class="accordion-content hidden px-8 pb-8">
                            <div class="h-px bg-pink-50 mb-6"></div>
                            <ul class="space-y-4">
                                <?php 
                                    $items = json_decode($c['items'], true) ?: [];
                                    foreach ($items as $item): 
                                        $text = is_array($item) ? ($item['text'] ?? '') : $item;
                                ?>
                                    <li class="flex items-start text-gray-700 font-medium">
                                        <div class="w-2 h-2 rounded-full bg-pink-300 mt-2 mr-4 flex-shrink-0"></div>
                                        <?php echo htmlspecialchars($text); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($checklists)): ?>
                    <div class="text-center text-gray-400 py-10">
                        Азырынча чеклисттер кошула элек.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-50 py-12 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h5 class="text-xl font-bold text-pink-600 mb-4">Эне Мээрими</h5>
            <p class="text-gray-500 text-sm mb-6">© <?php echo date('Y'); ?> Бардык укуктар корголгон. Сүйүү жана мээрим менен жасалган.</p>
            <div class="flex justify-center space-x-4">
                <a href="#" class="w-10 h-10 bg-white rounded-full shadow-sm flex items-center justify-center text-pink-600 hover:shadow-md transition">IG</a>
                <a href="#" class="w-10 h-10 bg-white rounded-full shadow-sm flex items-center justify-center text-pink-600 hover:shadow-md transition">WA</a>
            </div>
        </div>
    </footer>
</body>
</html>
<?php } ?>
