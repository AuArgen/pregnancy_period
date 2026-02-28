<?php
require_once dirname(__DIR__) . '/index.php';

use App\Auth\Auth;
use App\Models\File;

Auth::requireAuth();

$message = '';
$uploadDir = dirname(__DIR__) . '/uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::checkCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $message = "Ката: CSRF токен ката!";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'upload') {
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $originalName = $_FILES['file']['name'];
            $fileType = $_FILES['file']['type'];
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $newName = uniqid() . '.' . $extension;
            $uploadFilePath = $uploadDir . $newName;

            $tempPath = $_FILES['file']['tmp_name'];

            // Сүрөт болсо кысуу
            $imageTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (in_array($fileType, $imageTypes) && function_exists('imagecreatefromjpeg')) {
                $info = getimagesize($tempPath);
                if ($info) {
                    $width = $info[0];
                    $height = $info[1];
                    $mime = $info['mime'];

                    // Максималдуу өлчөм 1200px
                    $maxSize = 1200;
                    $newWidth = $width;
                    $newHeight = $height;

                    if ($width > $maxSize || $height > $maxSize) {
                        if ($width > $height) {
                            $newWidth = $maxSize;
                            $newHeight = floor($height * ($maxSize / $width));
                        } else {
                            $newHeight = $maxSize;
                            $newWidth = floor($width * ($maxSize / $height));
                        }
                    }

                    $srcImage = null;
                    if ($mime == 'image/jpeg' || $mime == 'image/jpg') {
                        $srcImage = imagecreatefromjpeg($tempPath);
                    } elseif ($mime == 'image/png') {
                        $srcImage = imagecreatefrompng($tempPath);
                    }

                    if ($srcImage) {
                        $dstImage = imagecreatetruecolor($newWidth, $newHeight);
                        
                        // PNG үчүн тунуктукту сактоо
                        if ($mime == 'image/png') {
                            imagealphablending($dstImage, false);
                            imagesavealpha($dstImage, true);
                        }

                        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                        if ($mime == 'image/jpeg' || $mime == 'image/jpg') {
                            imagejpeg($dstImage, $uploadFilePath, 80); // 80% сапат
                        } elseif ($mime == 'image/png') {
                            imagepng($dstImage, $uploadFilePath, 8); // 8 деңгээл (0-9)
                        }

                        imagedestroy($srcImage);
                        imagedestroy($dstImage);
                        $fileMoved = true;
                    }
                }
            }

            if (!isset($fileMoved) && move_uploaded_file($tempPath, $uploadFilePath)) {
                $fileMoved = true;
            }

            if (isset($fileMoved)) {
                File::create([
                    'name' => $newName,
                    'original_name' => $originalName,
                    'file_type' => $fileType,
                    'file_path' => '/uploads/' . $newName
                ]);
                $message = "Файл ийгиликтүү жүктөлдү!";
            } else {
                $message = "Ката: Файлды жүктөөдө ката кетти.";
            }
        } else {
            $message = "Ката: Файл тандалган жок же жүктөөдө ката кетти.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (isset($_POST['id']) && File::delete($_POST['id'])) {
            $message = "Файл ийгиликтүү өчүрүлдү!";
        } else {
            $message = "Ката: Файлды өчүрүү мүмкүн болгон жок.";
        }
    }
}

$files = File::all();
?>
<!DOCTYPE html>
<html lang="ky">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Файлдарды башкаруу - Эне Мээрими</title>
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
            <a href="/admin/files.php" class="block py-3 px-6 bg-pink-700 text-white font-semibold">Файлдар</a>
            <a href="/admin/settings.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 transition">Жөндөөлөр</a>
            <a href="/admin/logout.php" class="block py-3 px-6 text-pink-100 hover:bg-pink-500 mt-10 transition">Чыгуу</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-10">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Файлдарды башкаруу</h2>
        </div>

        <?php if ($message): ?>
            <div class="<?php echo strpos($message, 'Ката') !== false ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700'; ?> border px-4 py-3 rounded relative mb-6">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 mb-8 max-w-2xl">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Жаңы файл кошуу</h3>
            <form id="upload-form" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCsrfToken(); ?>">
                <input type="hidden" name="action" value="upload">
                <div class="mb-4">
                    <input type="file" id="file-input" name="file" class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-pink-500 outline-none" required>
                    <p class="text-xs text-gray-400 mt-1">Сүрөт, PDF же DOCX файлдарын кошо аласыз. Сүрөттөр автоматтык түрдө кысылат.</p>
                </div>
                <div id="status-message" class="hidden mb-4 p-3 rounded-xl text-sm font-semibold bg-blue-50 text-blue-700">
                    Сураныч, күтө туруңуз... Сүрөт иштетилип жатат.
                </div>
                <button type="submit" id="submit-btn" class="bg-pink-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-pink-700 transition">Жүктөө</button>
            </form>
        </div>

        <!-- Files List -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-700">Аталышы</th>
                        <th class="px-6 py-4 font-bold text-gray-700">Түрү</th>
                        <th class="px-6 py-4 font-bold text-gray-700">Дата</th>
                        <th class="px-6 py-4 font-bold text-gray-700">Аракеттер</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($files as $file): ?>
                        <tr>
                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($file['original_name']); ?></span>
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-sm">
                                <?php echo htmlspecialchars($file['file_type']); ?>
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-sm">
                                <?php echo date('d.m.Y H:i', strtotime($file['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-3">
                                    <button onclick="copyLink('<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $file['file_path']; ?>')" 
                                            class="text-pink-600 hover:text-pink-800 font-semibold text-sm">Ссылка көчүрүү</button>
                                    
                                    <form method="POST" onsubmit="return confirm('Бул файлды өчүрүүнү каалайсызбы?');" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCsrfToken(); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $file['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-semibold text-sm">Өчүрүү</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($files)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-400">Файлдар жок.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const form = document.getElementById('upload-form');
        const fileInput = document.getElementById('file-input');
        const submitBtn = document.getElementById('submit-btn');
        const statusMessage = document.getElementById('status-message');

        form.addEventListener('submit', async (e) => {
            const file = fileInput.files[0];
            if (!file || !file.type.startsWith('image/')) {
                return; // Сүрөт эмес болсо, кадимкидей эле кете берет
            }

            e.preventDefault();
            
            submitBtn.disabled = true;
            statusMessage.classList.remove('hidden');

            try {
                const compressedFile = await compressImage(file);
                
                // Керектүү маалыматтарды DataTransfer аркылуу input'ко салабыз
                // Бул fetch колдонбой эле кадимки форманы жөнөтүүгө мүмкүндүк берет
                const dataTransfer = new DataTransfer();
                const newImageFile = new File([compressedFile], file.name, { type: 'image/jpeg' });
                dataTransfer.items.add(newImageFile);
                fileInput.files = dataTransfer.files;

                // Эми форманы жөнөтөбүз
                form.submit();
            } catch (error) {
                console.error('Compression error:', error);
                form.submit(); // Ката болсо, оригиналды жөнөтүп көрөбүз
            }
        });

        function compressImage(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = (event) => {
                    const img = new Image();
                    img.src = event.target.result;
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        let width = img.width;
                        let height = img.height;

                        const maxSize = 1200;
                        if (width > maxSize || height > maxSize) {
                            if (width > height) {
                                height = Math.round((height * maxSize) / width);
                                width = maxSize;
                            } else {
                                width = Math.round((width * maxSize) / height);
                                height = maxSize;
                            }
                        }

                        canvas.width = width;
                        canvas.height = height;

                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        canvas.toBlob((blob) => {
                            if (blob) {
                                resolve(blob);
                            } else {
                                reject(new Error('Canvas to Blob conversion failed'));
                            }
                        }, 'image/jpeg', 0.8); // 80% сапат
                    };
                    img.onerror = (err) => reject(err);
                };
                reader.onerror = (err) => reject(err);
            });
        }

        function copyLink(url) {
            navigator.clipboard.writeText(url).then(() => {
                alert('Ссылка көчүрүлдү!');
            }).catch(err => {
                console.error('Көчүрүүдө ката кетти: ', err);
            });
        }
    </script>
</body>
</html>
