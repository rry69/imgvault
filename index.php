<?php require_once __DIR__ . '/includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>img — Upload Images</title>
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
                        serif: ['DM Serif Display', 'Georgia', 'serif'],
                        mono: ['Sansation', 'SF Mono', 'monospace'],
                    },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Quicksand:wght@400;500;600;700&family=Sansation:wght@400&family=Orbitron:wght@400;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-surface-0 text-text-primary font-sans min-h-screen antialiased">

    <!-- LANGUAGE TOGGLE — single button, inline onclick -->
    <div style="position:fixed;top:20px;right:24px;z-index:99999;">
        <button id="lang-btn" onclick="toggleLang()" style="display:flex;align-items:center;gap:8px;padding:8px 16px;background:#3D4F52;border:1px solid rgba(213,201,182,0.15);border-radius:9999px;cursor:pointer;">
            <span id="lang-flag"></span>
            <span id="lang-text" style="font-size:12px;font-weight:600;font-family:Inter,system-ui,sans-serif;color:#A67B5B;letter-spacing:0.04em;"></span>
        </button>
    </div>

    <!-- ═══════════════════════════════════════════════
         HERO SECTION — CH-01 Glitch
         ═══════════════════════════════════════════════ -->
    <section class="hero">
        <div class="hero-tagline animate-blur-reveal blur-delay-1">
            <span data-lang="id">Ubah Gambar Anda Menjadi Link dalam Sekejap</span>
            <span data-lang="en">Turn Your Images Into Links in an Instant</span>
        </div>

        <h1 class="hero-title animate-blur-reveal blur-delay-2">
            <span class="main-text" data-text="ImgVault">ImgVault</span>
        </h1>

        <p class="hero-subtitle-glitch animate-blur-reveal blur-delay-3">
            <span data-lang="id">Upload gambar secara gratis tanpa perlu mendaftar. Dapatkan direct link instan yang siap dibagikan ke mana saja.</span>
            <span data-lang="en">Upload images for free. No registration. Instant direct links.</span>
        </p>
    </section>

    <!-- ═══════════════════════════════════════════════
         UPLOAD ZONE — VHS Style
         ═══════════════════════════════════════════════ -->
    <div class="container-narrow" style="padding: 0 24px;">
        <div class="vhs-upload-wrapper animate-blur-reveal blur-delay-3">
            <div id="vhs-uploadState">
                <div class="vhs-tape-area" id="vhs-tapeArea">
                    <span class="vhs-rec-dot" id="vhs-recDot"></span>
                    <div class="vhs-reel-holes">
                        <div class="vhs-reel" id="vhs-reel1"></div>
                        <div class="vhs-reel" id="vhs-reel2"></div>
                    </div>
                    <div class="vhs-tape-label">[ DRAG &amp; DROP IMAGE HERE ]</div>
                    <div class="vhs-tape-hint">or click to browse • JPEG, PNG, GIF, WEBP • max 10MB • bulk upload OK</div>
                    <span class="vhs-sp-label">SP 120min</span>

                    <div class="vhs-thumb-wrap" id="vhs-thumbWrap">
                        <img id="vhs-thumbImg" alt="preview">
                    </div>

                    <div class="vhs-progress-wrap" id="vhs-progressWrap">
                        <div class="vhs-progress-label">
                            <span class="vhs-chromatic" style="font-size:.6rem">PLAY ▶</span>
                            <div class="vhs-flip-counter">
                                <span class="vhs-flip-digit" id="vhs-d1">0</span><span class="vhs-flip-digit" id="vhs-d2">0</span><span class="vhs-flip-pct">%</span>
                            </div>
                        </div>
                        <div class="vhs-progress-bar">
                            <div class="vhs-progress-fill" id="vhs-progressFill"></div>
                            <div class="vhs-play-indicator" id="vhs-playInd">PLAY ▶</div>
                        </div>
                    </div>
                </div>
                <input type="file" id="vhs-fileInput" accept="image/*" multiple hidden>
            </div>

            <div id="vhs-resultState" style="display:none;">
                <div class="vhs-result-card vhs-pause-flicker" id="vhs-resultCard">
                    <div class="vhs-result-title">img-upload.sh</div>
                    <div class="vhs-result-status">● Uploaded</div>
                    <div class="vhs-result-img-wrap">
                        <img id="vhs-resultImg" alt="uploaded">
                        <div class="vhs-timestamp"><span id="vhs-tsDate"></span> <span id="vhs-tsTime"></span> <span class="vhs-rec-badge">● REC</span></div>
                    </div>
                    <div class="vhs-link-row"><span class="vhs-link-label">Direct</span><input class="vhs-link-input" id="vhs-linkDirect" readonly><button class="vhs-copy-btn" onclick="vhsCopyLink('vhs-linkDirect',this)">Copy</button></div>
                    <div class="vhs-link-row"><span class="vhs-link-label">Markdown</span><input class="vhs-link-input" id="vhs-linkMd" readonly><button class="vhs-copy-btn" onclick="vhsCopyLink('vhs-linkMd',this)">Copy</button></div>
                    <div class="vhs-link-row"><span class="vhs-link-label">HTML</span><input class="vhs-link-input" id="vhs-linkHtml" readonly><button class="vhs-copy-btn" onclick="vhsCopyLink('vhs-linkHtml',this)">Copy</button></div>
                    <div class="vhs-link-row"><span class="vhs-link-label">BBCode</span><input class="vhs-link-input" id="vhs-linkBb" readonly><button class="vhs-copy-btn" onclick="vhsCopyLink('vhs-linkBb',this)">Copy</button></div>
                    <a class="vhs-back-link" onclick="vhsResetTool()">← Upload Gambar Lain</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Trust Line — Blueprint Stamps -->
    <div class="trust-line bp-stamps animate-blur-reveal blur-delay-4">
        <span class="bp-stamp">
            <span class="bp-stamp-icon">✓</span>
            <span data-lang="id">Gratis selamanya</span>
            <span data-lang="en">No visa required</span>
        </span>
        <span class="bp-stamp-sep">│</span>
        <span class="bp-stamp">
            <span class="bp-stamp-icon">✓</span>
            <span data-lang="id">Upload cepat</span>
            <span data-lang="en">Fast upload</span>
        </span>
        <span class="bp-stamp-sep">│</span>
        <span class="bp-stamp">
            <span class="bp-stamp-icon">✓</span>
            <span data-lang="id">Tautan langsung</span>
            <span data-lang="en">Direct links</span>
        </span>
    </div>

    <!-- ═══════════════════════════════════════════════
         FEATURES — VHS SCAN
         ═══════════════════════════════════════════════ -->
    <div class="vhs-wrap" id="features">
        <div class="vhs-title reveal delay-1">
            <span data-lang="id">Dirancang untuk kesederhanaan,<br>dibuat untuk kecepatan.</span>
            <span data-lang="en">Built for simplicity,<br>designed for speed.</span>
        </div>
        <div class="vhs-sub reveal delay-2">
            <span data-lang="id">PLAY &#9654; &nbsp; SEMUA YANG ANDA BUTUKAN UNTUK HOSTING DAN MEMBAGIKAN GAMBAR, TANPA KERUMITAN.</span>
            <span data-lang="en">PLAY &#9654; &nbsp; EVERYTHING YOU NEED TO HOST AND SHARE IMAGES, WITHOUT THE COMPLEXITY.</span>
        </div>

        <div class="vhs-cards">
            <div class="vhs-card reveal delay-1">
                <div class="vhs-num">CH-01</div>
                <h3>
                    <span data-lang="id">Upload Cepat</span>
                    <span data-lang="en">Fast Upload</span>
                </h3>
                <p>
                    <span data-lang="id">Seret, lepas, selesai. Gambar Anda langsung online dalam hitungan detik dengan tautan langsung yang siap dibagikan.</span>
                    <span data-lang="en">Drag, drop, done. Your images are live in seconds with direct links ready to share.</span>
                </p>
                <div class="rec-badge"><span class="rec-dot"></span> REC</div>
            </div>

            <div class="vhs-card reveal delay-2">
                <div class="vhs-num">CH-02</div>
                <h3>
                    <span data-lang="id">Tautan Langsung</span>
                    <span data-lang="en">Direct Image Links</span>
                </h3>
                <p>
                    <span data-lang="id">Dapatkan URL langsung, Markdown, HTML, dan BBCode. Tempel di mana saja.</span>
                    <span data-lang="en">Get direct URLs, Markdown, HTML, and BBCode. Paste anywhere.</span>
                </p>
                <div class="rec-badge"><span class="rec-dot"></span> REC</div>
            </div>

            <div class="vhs-card reveal delay-3">
                <div class="vhs-num">CH-03</div>
                <h3>
                    <span data-lang="id">Akses Selamanya</span>
                    <span data-lang="en">Forever Accessible</span>
                </h3>
                <p>
                    <span data-lang="id">Tanpa kedaluwarsa, tanpa batas tersembunyi. Bagikan dengan percaya diri.</span>
                    <span data-lang="en">No expiration, no hidden limits. Share with confidence.</span>
                </p>
                <div class="rec-badge"><span class="rec-dot"></span> REC</div>
            </div>
        </div>
    </div>

    <!-- Error Toast -->
    <div id="toast" class="toast">
        <span id="toast-msg"></span>
    </div>

    <!-- VHS Noise Bars -->
    <div class="vhs-noise-bar vhs-nb1"></div>
    <div class="vhs-noise-bar vhs-nb2"></div>
    <div class="vhs-noise-bar vhs-nb3"></div>

    <!-- VHS Error Overlay -->
    <div class="vhs-error-overlay" id="vhs-errorOverlay">
        <div class="vhs-error-static"></div>
        <div class="vhs-error-text">TRACKING ERROR</div>
        <button class="vhs-error-dismiss" onclick="vhsDismissError()">[ REWIND ]</button>
    </div>

    <!-- ═══════════════════════════════════════════════
         NEON FOOTER
         ═══════════════════════════════════════════════ -->
    <footer class="footer-neon">
        <div class="neon-tube-top"></div>
        <div class="neon-left">
            <div class="neon-icon"></div>
            <div class="neon-text">IMG<span class="dim">_</span>IO<span class="dim">_</span></div>
        </div>
        <div class="neon-center">
            <a href="https://hrry.win/" target="_blank">HRRY</a>
            <a href="https://instagram.com/hrry.prstyo/" target="_blank">INSTAGRAM</a>
        </div>
        <div class="neon-right">
            <div class="neon-volt"><span>220</span>V · 60Hz</div>
        </div>
    </footer>

    <!-- ═══════════════════════════════════════════════
         SCRIPTS
         ═══════════════════════════════════════════════ -->
    <script>
        /* ── Language Toggle ──────────────────────────── */
        var _savedLang = localStorage.getItem('img-lang');
        var _lang = _savedLang || 'id';
        var _flag = document.getElementById('lang-flag');
        var _text = document.getElementById('lang-text');

        var _flags = {
            id: '<svg viewBox="0 0 36 24" width="18" height="13"><rect width="36" height="12" fill="#FF0000"/><rect y="12" width="36" height="12" fill="#FFFFFF"/></svg>',
            en: '<svg viewBox="0 0 60 40" width="18" height="13"><rect width="60" height="40" fill="#012169"/><path d="M0 0L60 40M60 0L0 40" stroke="#FFF" stroke-width="6"/><path d="M0 0L60 40M60 0L0 40" stroke="#C8102E" stroke-width="2"/><path d="M30 0V40M0 20H60" stroke="#FFF" stroke-width="10"/><path d="M30 0V40M0 20H60" stroke="#C8102E" stroke-width="6"/></svg>'
        };

        var _labels = { id: 'ID', en: 'EN' };
        var _targets = { id: 'en', en: 'id' };

        /* ── Glitch-wrap initialization ─────────────────── */
        (function initGlitchWraps() {
            var all = document.querySelectorAll('[data-lang]');
            var processed = new WeakSet();
            for (var i = 0; i < all.length; i++) {
                var el = all[i];
                if (processed.has(el)) continue;
                if (el.querySelector('[data-lang]')) continue;
                var parent = el.parentElement;
                if (!parent) continue;
                var sibLangs = parent.querySelectorAll(':scope > [data-lang]');
                if (sibLangs.length < 2) continue;
                if (parent.classList.contains('glitch-wrap')) continue;

                var wrap = document.createElement('span');
                wrap.className = 'glitch-wrap';
                parent.insertBefore(wrap, sibLangs[0]);
                for (var j = 0; j < sibLangs.length; j++) {
                    sibLangs[j].setAttribute('data-text', sibLangs[j].textContent);
                    wrap.appendChild(sibLangs[j]);
                    processed.add(sibLangs[j]);
                }
            }
        })();

        var _glitchBusy = false;

        function applyLang(lang, animate) {
            _flag.innerHTML = _flags[lang];
            _text.textContent = _labels[lang];

            var all = document.querySelectorAll('[data-lang]');
            var toShow = [];
            var toHide = [];

            for (var i = 0; i < all.length; i++) {
                var el = all[i];
                if (el.querySelector('[data-lang]')) continue;
                if (el.getAttribute('data-lang') === lang) {
                    toShow.push(el);
                } else {
                    toHide.push(el);
                }
            }

            if (!animate) {
                for (var i = 0; i < toHide.length; i++) {
                    toHide[i].style.opacity = '0';
                    toHide[i].style.display = 'none';
                }
                for (var i = 0; i < toShow.length; i++) {
                    toShow[i].style.opacity = '1';
                    toShow[i].style.display = '';
                }
                return;
            }

            if (_glitchBusy) return;
            _glitchBusy = true;

            // Start glitch on all glitch-wraps
            var wraps = document.querySelectorAll('.glitch-wrap');
            for (var i = 0; i < wraps.length; i++) {
                wraps[i].classList.add('glitching');
            }

            // Swap text mid-glitch (200ms — matches reference)
            setTimeout(function() {
                for (var i = 0; i < toHide.length; i++) {
                    toHide[i].style.display = 'none';
                    toHide[i].style.opacity = '0';
                }
                for (var i = 0; i < toShow.length; i++) {
                    toShow[i].style.display = '';
                    toShow[i].style.opacity = '1';
                }
            }, 200);

            // End glitch after animation completes (500ms)
            setTimeout(function() {
                requestAnimationFrame(function() {
                    requestAnimationFrame(function() {
                        for (var i = 0; i < wraps.length; i++) {
                            wraps[i].classList.remove('glitching');
                        }
                        _glitchBusy = false;
                    });
                });
            }, 550);
        }

        function toggleLang() {
            if (_glitchBusy) return;
            _lang = _targets[_lang];
            localStorage.setItem('img-lang', _lang);
            document.documentElement.lang = _lang;
            applyLang(_lang, true);
        }

        applyLang(_lang, false);

        // Detect negara dari IP jika belum ada saved preference
        if (!_savedLang) {
            var _geoTimeout = setTimeout(function() {
                // Fallback: assume 'en' if geo API is slow/unavailable
                if (_lang === 'id') {
                    _lang = 'en';
                    document.documentElement.lang = _lang;
                    applyLang(_lang, false);
                }
            }, 3000);

            fetch('https://ipapi.co/json/')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    clearTimeout(_geoTimeout);
                    var detected = (data.country_code === 'ID') ? 'id' : 'en';
                    if (detected !== _lang) {
                        _lang = detected;
                        document.documentElement.lang = _lang;
                        applyLang(_lang, false);
                    }
                })
                .catch(function() {
                    clearTimeout(_geoTimeout);
                });
        }
    </script>
    <script>
        /* ── Scroll Reveal ─────────────────────────────── */
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

        /* ── Smooth scroll for anchor links ────────────── */
        document.querySelectorAll('a[href^="#"]').forEach(link => {
            link.addEventListener('click', (e) => {
                const target = document.querySelector(link.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
    <script>
        /* ── VHS Upload Tool (Real Upload + Bulk) ────────── */
        var VHS_MAX_SIZE = 5 * 1024 * 1024;
        var VHS_ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        var vhsUploadedUrls = [];
        var vhsUploading = false;

        var vhsTape = document.getElementById('vhs-tapeArea');
        var vhsFileInput = document.getElementById('vhs-fileInput');
        var vhsThumbWrap = document.getElementById('vhs-thumbWrap');
        var vhsThumbImg = document.getElementById('vhs-thumbImg');
        var vhsProgressWrap = document.getElementById('vhs-progressWrap');
        var vhsProgressFill = document.getElementById('vhs-progressFill');
        var vhsPlayInd = document.getElementById('vhs-playInd');
        var vhsRecDot = document.getElementById('vhs-recDot');
        var vhsUploadState = document.getElementById('vhs-uploadState');
        var vhsResultState = document.getElementById('vhs-resultState');
        var vhsResultImg = document.getElementById('vhs-resultImg');
        var vhsReel1 = document.getElementById('vhs-reel1');
        var vhsReel2 = document.getElementById('vhs-reel2');

        vhsTape.addEventListener('click', function(e) {
            if (vhsUploading) return;
            if (e.target.classList.contains('vhs-copy-btn')) return;
            vhsFileInput.click();
        });
        vhsTape.addEventListener('dragover', function(e) {
            e.preventDefault();
            vhsTape.classList.add('vhs-dragover');
        });
        vhsTape.addEventListener('dragleave', function() {
            vhsTape.classList.remove('vhs-dragover');
        });
        vhsTape.addEventListener('drop', function(e) {
            e.preventDefault();
            vhsTape.classList.remove('vhs-dragover');
            if (vhsUploading) return;
            if (e.dataTransfer.files.length > 0) vhsHandleFiles(e.dataTransfer.files);
        });
        vhsFileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) vhsHandleFiles(e.target.files);
        });

        document.addEventListener('paste', function(e) {
            if (vhsUploading) return;
            var items = e.clipboardData?.items;
            if (!items) return;
            var files = [];
            for (var i = 0; i < items.length; i++) {
                if (items[i].type.startsWith('image/')) {
                    var file = items[i].getAsFile();
                    if (file) files.push(file);
                }
            }
            if (files.length > 0) vhsHandleFiles(files);
        });

        function vhsHandleFiles(fileList) {
            var files = Array.from(fileList).filter(function(f) {
                if (!VHS_ALLOWED_TYPES.includes(f.type)) return false;
                if (f.size > VHS_MAX_SIZE) return false;
                return true;
            });
            if (files.length === 0) return;
            if (files.length > 6) {
                showToast('Maksimal 6 foto per upload.');
                return;
            }

            // Show all thumbnails
            vhsThumbWrap.innerHTML = '';
            vhsThumbWrap.style.display = 'flex';
            vhsThumbWrap.style.flexWrap = 'wrap';
            vhsThumbWrap.style.justifyContent = 'center';
            vhsThumbWrap.style.gap = '8px';

            var loaded = 0;
            files.forEach(function(file, i) {
                var reader = new FileReader();
                reader.onload = function(ev) {
                    var img = document.createElement('img');
                    img.src = ev.target.result;
                    img.alt = 'preview';
                    img.style.maxHeight = '120px';
                    img.style.borderRadius = '4px';
                    img.style.border = '1px solid #475B5F';
                    img.style.objectFit = 'cover';
                    vhsThumbWrap.appendChild(img);
                    loaded++;
                    if (loaded === files.length) {
                        vhsStartUpload(files);
                    }
                };
                reader.readAsDataURL(file);
            });
        }

        function vhsStartUpload(files) {
            vhsUploading = true;
            vhsUploadedUrls = [];
            vhsProgressWrap.style.display = 'block';
            vhsRecDot.classList.add('vhs-on');
            vhsReel1.classList.add('vhs-reel-spin');
            vhsReel2.classList.add('vhs-reel-spin');

            var fakePct = 0;
            var realDone = false;

            // Simulated progress
            var fakeIv = setInterval(function() {
                if (realDone) { clearInterval(fakeIv); return; }
                fakePct += Math.random() * 4 + 1;
                if (fakePct > 90) fakePct = 90;
                vhsUpdateProgress(Math.floor(fakePct));
            }, 120);

            // Upload sequentially
            var currentIndex = 0;

            function uploadNext() {
                if (currentIndex >= files.length) {
                    realDone = true;
                    clearInterval(fakeIv);
                    vhsFinishProgress(function() { vhsUploadDone(); });
                    return;
                }

                var file = files[currentIndex];
                var formData = new FormData();
                formData.append('images[]', file);

                var xhr = new XMLHttpRequest();

                xhr.upload.addEventListener('progress', function(e) {
                    // Update progress based on overall completion
                    if (e.lengthComputable) {
                        var filePct = Math.round((e.loaded / e.total) * 100);
                        var overallPct = Math.round(((currentIndex + e.loaded / e.total) / files.length) * 100);
                        // Use the higher of fake or real progress
                        if (overallPct > fakePct) {
                            vhsUpdateProgress(overallPct);
                        }
                    }
                });

                xhr.addEventListener('load', function() {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        if (data.success) {
                            vhsUploadedUrls.push(data.url);
                            currentIndex++;
                            uploadNext();
                        } else {
                            realDone = true;
                            clearInterval(fakeIv);
                            vhsUploading = false;
                            vhsShowError();
                            vhsResetTool();
                        }
                    } catch (err) {
                        realDone = true;
                        clearInterval(fakeIv);
                        vhsUploading = false;
                        vhsShowError();
                        vhsResetTool();
                    }
                });

                xhr.addEventListener('error', function() {
                    realDone = true;
                    clearInterval(fakeIv);
                    vhsUploading = false;
                    vhsShowError();
                    vhsResetTool();
                });

                xhr.open('POST', 'api/upload.php');
                xhr.setRequestHeader('X-CSRF-Token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
                xhr.send(formData);
            }

            uploadNext();
        }

        function vhsFinishProgress(cb) {
            var d1 = document.getElementById('vhs-d1');
            var d2 = document.getElementById('vhs-d2');
            var current = parseInt(d1.textContent + d2.textContent) || 0;
            var iv = setInterval(function() {
                current += Math.random() * 8 + 2;
                if (current >= 100) {
                    current = 100;
                    clearInterval(iv);
                    vhsUpdateProgress(100);
                    setTimeout(cb, 300);
                } else {
                    vhsUpdateProgress(Math.floor(current));
                }
            }, 60);
        }

        function vhsUpdateProgress(p) {
            vhsProgressFill.style.width = p + '%';
            vhsPlayInd.style.left = 'calc(' + p + '% - 24px)';
            var d1 = document.getElementById('vhs-d1');
            var d2 = document.getElementById('vhs-d2');
            var s = String(p).padStart(2, '0');
            if (d1.textContent !== s[0]) {
                d1.classList.remove('vhs-flipping');
                void d1.offsetWidth;
                d1.classList.add('vhs-flipping');
                d1.textContent = s[0];
            }
            if (d2.textContent !== s[1]) {
                d2.classList.remove('vhs-flipping');
                void d2.offsetWidth;
                d2.classList.add('vhs-flipping');
                d2.textContent = s[1];
            }
        }

        function vhsUploadDone() {
            setTimeout(function() {
                vhsUploadState.style.display = 'none';
                vhsResultState.style.display = 'block';
                var resultCard = document.getElementById('vhs-resultCard');
                var isBulk = vhsUploadedUrls.length > 1;

                // Remove existing bulk card
                var existingBulk = resultCard.querySelector('.vhs-bulk-card');
                if (existingBulk) existingBulk.remove();

                if (isBulk) {
                    // BULK: hide single result, show all images with thumbnails
                    vhsResultImg.style.display = 'none';
                    document.querySelector('.vhs-timestamp').style.display = 'none';
                    document.getElementById('vhs-linkDirect').parentElement.style.display = 'none';
                    document.getElementById('vhs-linkMd').parentElement.style.display = 'none';
                    document.getElementById('vhs-linkHtml').parentElement.style.display = 'none';
                    document.getElementById('vhs-linkBb').parentElement.style.display = 'none';

                    var safeUrl, mdUrl, htmlUrl, bbUrl;
                    var bulkHtml = '<div class="vhs-bulk-card">';
                    bulkHtml += '<div style="color:#00ff41;text-align:center;font-size:1.05rem;margin-bottom:16px;font-weight:600">' + vhsUploadedUrls.length + ' gambar berhasil diupload</div>';

                    vhsUploadedUrls.forEach(function(url, i) {
                        safeUrl = vhsEscapeAttr(url);
                        mdUrl = vhsEscapeAttr('![image](' + url + ')');
                        htmlUrl = vhsEscapeAttr('<img src="' + url + '" alt="image">');
                        bbUrl = vhsEscapeAttr('[img]' + url + '[/img]');

                        bulkHtml += '<div class="vhs-bulk-item">';
                        bulkHtml += '<img src="' + safeUrl + '" class="vhs-bulk-thumb">';
                        bulkHtml += '<div class="vhs-bulk-info">';
                        bulkHtml += '<div class="vhs-bulk-label">#' + (i + 1) + '</div>';
                        bulkHtml += '<input class="vhs-bulk-url" value="' + safeUrl + '" readonly>';
                        bulkHtml += '<div class="vhs-bulk-actions">';
                        bulkHtml += '<button onclick="vhsCopyText(this)" data-url="' + safeUrl + '">Direct</button>';
                        bulkHtml += '<button onclick="vhsCopyText(this)" data-url="' + mdUrl + '">MD</button>';
                        bulkHtml += '<button onclick="vhsCopyText(this)" data-url="' + htmlUrl + '">HTML</button>';
                        bulkHtml += '<button onclick="vhsCopyText(this)" data-url="' + bbUrl + '">BB</button>';
                        bulkHtml += '</div></div></div>';
                    });

                    bulkHtml += '<button class="vhs-copy-all-btn" onclick="vhsCopyAllBulkLinks()">Salin Semua Link</button>';
                    bulkHtml += '</div>';

                    var backLink = resultCard.querySelector('.vhs-back-link');
                    backLink.insertAdjacentHTML('beforebegin', bulkHtml);

                } else {
                    // SINGLE: show image thumbnail with links
                    var url = vhsUploadedUrls[0];
                    vhsResultImg.style.display = '';
                    vhsResultImg.src = url;
                    document.querySelector('.vhs-timestamp').style.display = '';
                    document.getElementById('vhs-linkDirect').parentElement.style.display = '';
                    document.getElementById('vhs-linkMd').parentElement.style.display = '';
                    document.getElementById('vhs-linkHtml').parentElement.style.display = '';
                    document.getElementById('vhs-linkBb').parentElement.style.display = '';

                    document.getElementById('vhs-linkDirect').value = url;
                    document.getElementById('vhs-linkMd').value = '![image](' + url + ')';
                    document.getElementById('vhs-linkHtml').value = '<img src="' + url + '" alt="image">';
                    document.getElementById('vhs-linkBb').value = '[img]' + url + '[/img]';
                }

                var now = new Date();
                document.getElementById('vhs-tsDate').textContent = now.toLocaleDateString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit' });
                document.getElementById('vhs-tsTime').textContent = now.toLocaleTimeString('en-US', { hour12: false });
                resultCard.classList.add('vhs-pause-flicker');
            }, 400);
        }

        function vhsEscapeAttr(str) {
            return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        function vhsCopyLink(inputId, btn) {
            var val = document.getElementById(inputId).value;
            navigator.clipboard.writeText(val).then(function() {
                btn.textContent = 'Copied!';
                btn.classList.add('vhs-copied');
                setTimeout(function() { btn.textContent = 'Copy'; btn.classList.remove('vhs-copied'); }, 1500);
            }).catch(function() {
                var inp = document.getElementById(inputId);
                inp.select();
                document.execCommand('copy');
                btn.textContent = 'Copied!';
                btn.classList.add('vhs-copied');
                setTimeout(function() { btn.textContent = 'Copy'; btn.classList.remove('vhs-copied'); }, 1500);
            });
        }

        function vhsCopyText(btn) {
            var text = btn.getAttribute('data-url');
            if (!text) return;
            var orig = btn.textContent;
            function onCopied() {
                btn.textContent = 'Copied!';
                btn.classList.add('copied');
                setTimeout(function() {
                    btn.textContent = orig;
                    btn.classList.remove('copied');
                }, 1500);
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(onCopied).catch(function() {
                    fallbackCopy(text); onCopied();
                });
            } else {
                fallbackCopy(text); onCopied();
            }
        }

        function fallbackCopy(text) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.cssText = 'position:fixed;opacity:0';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); } catch(e) {}
            document.body.removeChild(ta);
        }

        function vhsCopyAllBulkLinks() {
            var text = vhsUploadedUrls.join('\n');
            navigator.clipboard.writeText(text).then(function() {
                showToast('Semua link disalin!');
            }).catch(function() {
                showToast('Gagal menyalin link.');
            });
        }

        function vhsResetTool() {
            vhsUploading = false;
            vhsResultState.style.display = 'none';
            vhsUploadState.style.display = '';
            vhsProgressWrap.style.display = 'none';
            vhsThumbWrap.style.display = 'none';
            vhsThumbWrap.innerHTML = '';
            vhsThumbWrap.style.flexWrap = '';
            vhsThumbWrap.style.justifyContent = '';
            vhsThumbWrap.style.gap = '';
            vhsRecDot.classList.remove('vhs-on');
            vhsReel1.classList.remove('vhs-reel-spin');
            vhsReel2.classList.remove('vhs-reel-spin');
            vhsProgressFill.style.width = '0%';
            vhsPlayInd.style.left = '0';
            document.getElementById('vhs-d1').textContent = '0';
            document.getElementById('vhs-d2').textContent = '0';
            vhsFileInput.value = '';
            vhsUploadedUrls = [];

            // Reset result card visibility to single mode
            vhsResultImg.style.display = '';
            vhsResultImg.src = '';
            document.querySelector('.vhs-timestamp').style.display = '';
            document.getElementById('vhs-linkDirect').parentElement.style.display = '';
            document.getElementById('vhs-linkMd').parentElement.style.display = '';
            document.getElementById('vhs-linkHtml').parentElement.style.display = '';
            document.getElementById('vhs-linkBb').parentElement.style.display = '';
            document.getElementById('vhs-linkDirect').value = '';
            document.getElementById('vhs-linkMd').value = '';
            document.getElementById('vhs-linkHtml').value = '';
            document.getElementById('vhs-linkBb').value = '';

            // Remove bulk card
            var bulkCard = document.getElementById('vhs-resultCard').querySelector('.vhs-bulk-card');
            if (bulkCard) bulkCard.remove();
        }

        function vhsShowError() {
            document.getElementById('vhs-errorOverlay').classList.add('vhs-show');
        }

        function vhsDismissError() {
            document.getElementById('vhs-errorOverlay').classList.remove('vhs-show');
        }
    </script>
</body>
</html>