<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto px-4 py-16 w-full flex-grow flex flex-col justify-center">
    <div class="text-center mb-10">
        <h2 class="text-4xl font-extrabold tracking-tight text-white mb-3">
            Analisis Teks & Tata Letak Dokumen
        </h2>
        <p class="text-slate-400 max-w-xl mx-auto">
            Unggah file Gambar (JPG/PNG/WEBP) atau PDF untuk mengekstrak teks lengkap beserta koordinat layout geometrisnya menggunakan PaddleOCR.
        </p>
    </div>

    <!-- Upload Panel -->
    <div class="glass-panel rounded-3xl p-8 shadow-2xl relative overflow-hidden">
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Flash Message Alerts -->
        <?php if (session()->getFlashdata('error')) : ?>
            <div class="mb-6 p-4 rounded-xl bg-red-950/50 border border-red-500/30 text-red-200 flex items-start space-x-3 text-sm">
                <i class="fa-solid fa-circle-exclamation text-red-500 text-lg mt-0.5"></i>
                <div>
                    <span class="font-semibold block text-red-400">Terjadi kesalahan</span>
                    <?= session()->getFlashdata('error') ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form Upload -->
        <form action="<?= site_url('ocr/upload') ?>" method="POST" enctype="multipart/form-data" id="upload-form">
            <?= csrf_field() ?>

            <!-- Drag & Drop Zone -->
            <div id="drop-zone" class="border-2 border-dashed border-slate-700 hover:border-blue-500/50 bg-slate-900/30 hover:bg-blue-950/5 rounded-2xl p-12 transition-all text-center cursor-pointer group relative">
                <input type="file" name="file" id="file-input" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/jpeg,image/png,image/webp,application/pdf" required />
                
                <div class="flex flex-col items-center justify-center space-y-4">
                    <div class="h-16 w-16 rounded-2xl bg-slate-800/80 group-hover:bg-blue-600/10 group-hover:scale-105 flex items-center justify-center border border-slate-700/50 group-hover:border-blue-500/20 text-slate-400 group-hover:text-blue-500 transition-all shadow-inner">
                        <i class="fa-solid fa-cloud-arrow-up text-2xl"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-200">
                            Seret & taruh file Anda di sini, atau <span class="text-blue-500 group-hover:underline">klik untuk memilih</span>
                        </p>
                        <p class="text-xs text-slate-500 mt-1">
                            Mendukung Gambar (JPG, PNG, WEBP) atau file PDF (Maksimal 100MB)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Controls Container (Symmetric, aligned edges) -->
            <div class="mt-8 max-w-xl mx-auto space-y-6">
                
                <!-- Model Selection Dropdown -->
                <div class="space-y-2">
                    <label for="model-selector" class="block text-xs font-bold uppercase tracking-wider text-slate-400">Pilih Model AI OCR</label>
                    <div class="relative flex items-center">
                        <div class="absolute left-4 text-slate-500 pointer-events-none">
                            <i class="fa-solid fa-cpu text-blue-400"></i>
                        </div>
                        <select name="model" id="model-selector" class="w-full bg-slate-950/80 border border-slate-800 text-sm text-slate-200 rounded-xl pl-11 pr-10 py-3.5 focus:outline-none focus:border-blue-500 font-semibold transition-all appearance-none cursor-pointer shadow-inner">
                            <option value="v3">PP-OCR v3 (Default - Cepat & Ringan)</option>
                            <option value="v6_ort">PP-OCR v6 (ORT - Teroptimasi & Sangat Cepat)</option>
                            <option value="v6_onnx">PP-OCR v6 (ONNX - Standar)</option>
                        </select>
                        <div class="absolute right-4 text-slate-500 pointer-events-none">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <!-- Selected File Display -->
                <div id="file-details" class="hidden p-4 rounded-xl bg-slate-900/60 border border-slate-800/80 flex items-center justify-between">
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <div id="file-icon-box" class="h-10 w-10 rounded-lg flex items-center justify-center text-lg text-white shrink-0">
                            <i class="fa-solid fa-file"></i>
                        </div>
                        <div class="overflow-hidden w-full">
                            <p id="file-name" class="font-medium text-slate-200 text-sm truncate max-w-[240px] sm:max-w-xs">filename.pdf</p>
                            <p id="file-size" class="text-xs text-slate-500">0 KB</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded-md shrink-0">Siap Diproses</span>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" id="submit-btn" class="w-full py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/20 hover:shadow-blue-500/35 active:scale-95 transition-all flex items-center justify-center space-x-2">
                        <span>Mulai Proses OCR</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const fileInput = document.getElementById('file-input');
    const dropZone = document.getElementById('drop-zone');
    const fileDetails = document.getElementById('file-details');
    const fileNameEl = document.getElementById('file-name');
    const fileSizeEl = document.getElementById('file-size');
    const iconBox = document.getElementById('file-icon-box');
    const uploadForm = document.getElementById('upload-form');
    const submitBtn = document.getElementById('submit-btn');

    // Drag-over styling
    ['dragenter', 'dragover'].forEach(eventName => {
        fileInput.addEventListener(eventName, () => {
            dropZone.classList.add('border-blue-500', 'bg-blue-950/10');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        fileInput.addEventListener(eventName, () => {
            dropZone.classList.remove('border-blue-500', 'bg-blue-950/10');
        }, false);
    });

    // File change handler
    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            fileNameEl.textContent = file.name;
            fileSizeEl.textContent = formatBytes(file.size);
            
            // Adjust icon colors based on type
            if (file.type === 'application/pdf') {
                iconBox.className = 'h-10 w-10 rounded-lg flex items-center justify-center text-lg bg-red-600 text-white';
                iconBox.innerHTML = '<i class="fa-solid fa-file-pdf"></i>';
            } else {
                iconBox.className = 'h-10 w-10 rounded-lg flex items-center justify-center text-lg bg-emerald-600 text-white';
                iconBox.innerHTML = '<i class="fa-solid fa-file-image"></i>';
            }

            fileDetails.classList.remove('hidden');
        } else {
            fileDetails.classList.add('hidden');
        }
    });

    // Prevent double form submission, show loader
    uploadForm.addEventListener('submit', () => {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i> <span>Mengunggah File...</span>';
    });

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
</script>
<?= $this->endSection() ?>
