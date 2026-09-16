/**
 * img — Upload logic
 * Drag-drop, paste, file picker, bulk upload, copy URLs
 * All states render inside #upload-zone (in-place).
 */

const MAX_SIZE = 5 * 1024 * 1024;
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

// Elements
const zone = document.getElementById('upload-zone');
const fileInput = document.getElementById('file-input');
const idleEl = document.getElementById('upload-idle');
const previewEl = document.getElementById('upload-preview');
const progressEl = document.getElementById('upload-progress');
const progressBar = document.getElementById('progress-bar');
const progressPct = document.getElementById('progress-pct');
const progressText = document.getElementById('progress-text');
const previewImg = document.getElementById('preview-img');
const fileInfo = document.getElementById('file-info');
const uploadResult = document.getElementById('upload-result');
const toast = document.getElementById('toast');
const toastMsg = document.getElementById('toast-msg');
let uploadedUrls = [];

// --- State transitions ---

function transitionTo(from, to, cb) {
    if (from) {
        from.classList.add('state-fade-out');
        setTimeout(() => {
            from.classList.add('hidden');
            from.classList.remove('state-fade-out');
            show(to, cb);
        }, 200);
    } else {
        show(to, cb);
    }
}

function show(el, cb) {
    el.classList.remove('hidden');
    el.classList.add('state-fade-in');
    el.addEventListener('animationend', function handler() {
        el.classList.remove('state-fade-in');
        el.removeEventListener('animationend', handler);
        if (cb) cb();
    });
}

// --- Event Listeners ---

zone.addEventListener('click', (e) => {
    if (e.target.closest('label[for="file-input"]')) return;
    if (zone.classList.contains('uploading')) return;
    if (!idleEl.classList.contains('hidden')) {
        fileInput.click();
    }
});

zone.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        if (!idleEl.classList.contains('hidden')) {
            fileInput.click();
        }
    }
});

fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
        handleFiles(fileInput.files);
    }
});

zone.addEventListener('dragover', (e) => {
    e.preventDefault();
    zone.classList.add('dragover');
});

zone.addEventListener('dragleave', () => {
    zone.classList.remove('dragover');
});

zone.addEventListener('drop', (e) => {
    e.preventDefault();
    zone.classList.remove('dragover');
    if (zone.classList.contains('uploading')) return;
    if (!idleEl.classList.contains('hidden')) {
        if (e.dataTransfer.files.length > 0) {
            handleFiles(e.dataTransfer.files);
        }
    }
});

document.addEventListener('paste', (e) => {
    if (zone.classList.contains('uploading')) return;
    if (!uploadResult.classList.contains('hidden')) return;
    const items = e.clipboardData?.items;
    if (!items) return;
    const imageFiles = [];
    for (const item of items) {
        if (item.type.startsWith('image/')) {
            const file = item.getAsFile();
            if (file) imageFiles.push(file);
        }
    }
    if (imageFiles.length > 0) {
        handleFiles(imageFiles);
    }
});

// --- Core Functions ---

function handleFiles(fileList) {
    const files = Array.from(fileList).filter(f => {
        if (!ALLOWED_TYPES.includes(f.type)) {
            showToast(`${f.name}: format tidak didukung.`);
            return false;
        }
        if (f.size > MAX_SIZE) {
            showToast(`${f.name}: ukuran maksimal 5MB.`);
            return false;
        }
        return true;
    });

    if (files.length === 0) return;

    // Single file: show preview before uploading
    if (files.length === 1) {
        const url = URL.createObjectURL(files[0]);
        previewImg.src = url;
        fileInfo.textContent = `${files[0].name} — ${formatSize(files[0].size)}`;
        transitionTo(idleEl, previewEl, () => uploadFiles(files));
    } else {
        uploadFiles(files);
    }
}

function uploadFiles(files) {
    zone.classList.add('uploading');
    // Ensure progress state is visible
    if (!previewEl.classList.contains('hidden')) {
        previewEl.classList.add('hidden');
    }
    if (!idleEl.classList.contains('hidden')) {
        idleEl.classList.add('hidden');
    }
    progressEl.classList.remove('hidden');
    progressBar.style.width = '0%';
    progressPct.textContent = '0%';
    progressText.textContent = files.length > 1
        ? `upload ${files.length} files`
        : `upload ${files[0].name}`;

    uploadedUrls = [];

    if (files.length === 1) {
        uploadSingleWithProgress(files[0], 0, 1).then(() => finishUpload());
    } else {
        uploadSequential(files, 0).then(() => finishUpload());
    }
}

function uploadSequential(files, index) {
    if (index >= files.length) return Promise.resolve();
    progressText.textContent = `upload ${files[index].name} (${index + 1}/${files.length})`;
    return uploadSingleWithProgress(files[index], index, files.length)
        .then(() => uploadSequential(files, index + 1));
}

function uploadSingleWithProgress(file, fileIndex, totalFiles) {
    return new Promise((resolve) => {
        const formData = new FormData();
        formData.append('images[]', file);

        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const filePct = Math.round((e.loaded / e.total) * 100);
                const overallPct = totalFiles === 1
                    ? filePct
                    : Math.round(((fileIndex + e.loaded / e.total) / totalFiles) * 100);
                progressBar.style.width = overallPct + '%';
                progressPct.textContent = overallPct + '%';
            }
        });

        xhr.addEventListener('load', () => {
            try {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
                    uploadedUrls.push(data.url);
                } else {
                    showToast(data.error || `${file.name}: gagal upload.`);
                }
            } catch {
                showToast(`${file.name}: gagal memproses respons server.`);
            }
            resolve();
        });

        xhr.addEventListener('error', () => {
            showToast(`${file.name}: gagal menghubungi server.`);
            resolve();
        });

        xhr.open('POST', 'api/upload.php');
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) {
            xhr.setRequestHeader('X-CSRF-Token', csrfMeta.content);
            formData.append('csrf_token', csrfMeta.content);
        }
        xhr.send(formData);
    });
}

function finishUpload() {
    progressBar.style.width = '100%';
    progressPct.textContent = '100%';
    progressText.textContent = 'done ✓';

    setTimeout(() => {
        zone.classList.remove('uploading');
        transitionTo(progressEl, uploadResult, () => renderResult());
    }, 600);
}

function renderResult() {
    if (uploadedUrls.length === 0) {
        uploadResult.innerHTML = '';
        transitionTo(uploadResult, idleEl);
        return;
    }

    const isMulti = uploadedUrls.length > 1;

    let html = '';

    if (isMulti) {
        // Bulk: show all results in scrollable list
        html += `<div class="bulk-links">
            <div class="bulk-links-header">
                <span>${uploadedUrls.length} gambar berhasil diupload</span>
                <button onclick="copyAllLinks()" class="btn-copy">Salin Semua</button>
            </div>
            <textarea id="bulk-links-text" rows="3" readonly style="width:100%;background:var(--bg-primary);border:1px solid var(--border-subtle);border-radius:var(--radius-sm);padding:8px 12px;font-size:12px;font-family:'Sansation',monospace;color:var(--text-secondary);resize:none;outline:none;margin-bottom:var(--space-md);">${escapeHtml(uploadedUrls.join('\n'))}</textarea>
        </div>`;
    }

    uploadedUrls.forEach((url, i) => {
        const label = isMulti ? `#${i + 1}` : '';
        html += `
        <div class="result-status">
            <span class="result-status-dot"></span>
            <span class="result-status-text">Uploaded${label}</span>
        </div>
        <div class="result-preview">
            <img src="${escapeAttr(url)}" alt="Preview">
        </div>
        <div class="result-links">
            <div class="result-link-row">
                <span class="result-link-label">Direct</span>
                <input type="text" value="${escapeAttr(url)}" class="result-link-input" readonly>
                <button onclick="copyText(this)" class="btn-copy">Copy</button>
            </div>
            <div class="result-link-row">
                <span class="result-link-label">Markdown</span>
                <input type="text" value="![image](${escapeAttr(url)})" class="result-link-input" readonly>
                <button onclick="copyText(this)" class="btn-copy">Copy</button>
            </div>
            <div class="result-link-row">
                <span class="result-link-label">HTML</span>
                <input type="text" value="${escapeAttr(`<img src="${url}">`)}" class="result-link-input" readonly>
                <button onclick="copyText(this)" class="btn-copy">Copy</button>
            </div>
            <div class="result-link-row">
                <span class="result-link-label">BBCode</span>
                <input type="text" value="[img]${escapeAttr(url)}[/img]" class="result-link-input" readonly>
                <button onclick="copyText(this)" class="btn-copy">Copy</button>
            </div>
        </div>`;
    });

    // Reset button
    html += `
    <button onclick="resetUpload()" class="btn-back">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        <span data-lang="id">Upload Gambar Lain</span>
        <span data-lang="en">Upload Another</span>
    </button>`;

    uploadResult.innerHTML = html;

    // Apply current language to new elements
    if (typeof applyLang === 'function') {
        const lang = document.documentElement.lang || 'id';
        uploadResult.querySelectorAll('[data-lang]').forEach(el => {
            el.style.display = el.getAttribute('data-lang') === lang ? '' : 'none';
        });
    }
}

function resetUpload() {
    transitionTo(uploadResult, idleEl, () => {
        progressEl.classList.add('hidden');
        previewEl.classList.add('hidden');
        progressBar.style.width = '0%';
        progressPct.textContent = '0%';
        progressText.textContent = '';
        uploadResult.innerHTML = '';
        uploadedUrls = [];
        fileInput.value = '';
    });
}

// --- Utilities ---

function copyText(btn) {
    const input = btn.parentElement.querySelector('input');
    if (!input) return;
    navigator.clipboard.writeText(input.value).then(() => {
        const orig = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(() => btn.textContent = orig, 1500);
    }).catch(() => {
        input.select();
        document.execCommand('copy');
        btn.textContent = 'Copied!';
        setTimeout(() => btn.textContent = 'Copy', 1500);
    });
}

function copyAllLinks() {
    const textarea = document.getElementById('bulk-links-text');
    if (!textarea) return;
    navigator.clipboard.writeText(textarea.value).then(() => {
        showToast('Semua link disalin!');
    }).catch(() => {
        textarea.select();
        document.execCommand('copy');
        showToast('Semua link disalin!');
    });
}

function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

function escapeAttr(str) {
    return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function showToast(msg) {
    toastMsg.textContent = msg;
    toast.classList.add('show');
    clearTimeout(showToast._timer);
    showToast._timer = setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

