<?php
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>img — Dashboard</title>
    <meta name="csrf-token" content="<?= htmlspecialchars(generateCsrfToken()) ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        surface: { 0: '#2C3639', 1: '#333F42', 2: '#3D4F52', 3: '#475B5F' },
                        border: { DEFAULT: 'rgba(213,201,182,0.09)', hover: 'rgba(213,201,182,0.14)' },
                        text: { primary: '#D5C9B6', secondary: '#B0A898', muted: '#7A7268' },
                        accent: '#A67B5B',
                    },
                    fontFamily: {
                        sans: ['Quicksand', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['Sansation', 'SF Mono', 'monospace'],
                    },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600&family=Sansation:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-surface-0 text-text-primary font-sans min-h-screen antialiased">

    <!-- Nav -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-surface-0/80 backdrop-blur-sm border-b border-border">
        <div class="max-w-4xl mx-auto px-6 h-14 flex items-center justify-between">
            <a href="index.php" class="text-sm font-medium tracking-tight">img</a>
            <div class="flex items-center gap-4">
                <span class="text-xs text-text-secondary">Dashboard</span>
            </div>
        </div>
    </nav>

    <main class="pt-14">
        <section class="max-w-4xl mx-auto px-6 pt-12 pb-16">
            <h1 class="text-xl font-medium tracking-tight mb-6">Your Images</h1>

            <?php
            $db = getDB();
            $stmt = $db->query('SELECT * FROM images ORDER BY created_at DESC LIMIT 50');
            $images = $stmt->fetchAll();
            ?>

            <?php if (empty($images)): ?>
                <p class="text-sm text-text-secondary">Belum ada gambar. <a href="index.php" class="text-text-primary underline underline-offset-2 hover:no-underline">Upload sekarang</a>.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($images as $img): ?>
                        <div class="flex items-center gap-4 border border-border rounded-lg p-4 hover:border-border-hover transition-colors duration-200" data-id="<?= $img['id'] ?>">
                            <img src="<?= htmlspecialchars($img['thumbnail_url'] ?: $img['image_url']) ?>" class="w-12 h-12 object-cover rounded" alt="">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm truncate"><?= htmlspecialchars($img['original_name']) ?></div>
                                <div class="text-[11px] text-text-muted font-mono truncate mt-0.5"><?= htmlspecialchars($img['image_url']) ?></div>
                            </div>
                            <div class="text-[11px] text-text-muted shrink-0">
                                <?= timeAgo($img['created_at']) ?>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button onclick="copyDashboardUrl(this, '<?= htmlspecialchars($img['image_url']) ?>')" class="text-[11px] text-text-primary border border-border rounded px-2 py-1 hover:border-border-hover transition-colors duration-200">Copy</button>
                                <button onclick="deleteImage(<?= $img['id'] ?>)" class="text-[11px] text-red-400 border border-border rounded px-2 py-1 hover:border-red-400/30 transition-colors duration-200">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <div id="toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-surface-3 border border-border rounded-lg px-4 py-2.5 text-xs text-text-primary opacity-0 pointer-events-none transition-opacity duration-200 z-50">
        <span id="toast-msg"></span>
    </div>

    <script>
        function copyDashboardUrl(btn, url) {
            navigator.clipboard.writeText(url).then(() => {
                btn.textContent = 'Copied!';
                setTimeout(() => btn.textContent = 'Copy', 1500);
            });
        }

        function deleteImage(id) {
            if (!confirm('Hapus gambar ini?')) return;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            fetch('api/delete.php?id=' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-Token': csrfToken }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector(`[data-id="${id}"]`).remove();
                        showToast('Gambar dihapus.');
                    } else {
                        showToast(data.error || 'Gagal menghapus.');
                    }
                })
                .catch(() => showToast('Gagal menghapus.'));
        }

        function showToast(msg) {
            const t = document.getElementById('toast');
            document.getElementById('toast-msg').textContent = msg;
            t.classList.add('show');
            clearTimeout(showToast._timer);
            showToast._timer = setTimeout(() => t.classList.remove('show'), 3000);
        }
    </script>
</body>
</html>
