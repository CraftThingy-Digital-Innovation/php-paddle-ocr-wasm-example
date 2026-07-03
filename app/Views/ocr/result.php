<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<!-- Main Workspace Container -->
<div class="flex-grow flex flex-col relative w-full h-[calc(100vh-4rem)] overflow-hidden">
    
    <!-- LOADING / INITIALIZATION SCREEN -->
    <div id="loader-screen" class="absolute inset-0 bg-slate-950/95 flex flex-col items-center justify-center z-50 p-6 transition-all duration-500">
        <div class="max-w-md w-full text-center space-y-6">
            <div class="relative inline-flex items-center justify-center">
                <div class="h-24 w-24 rounded-full border-4 border-slate-800 border-t-blue-500 animate-spin"></div>
                <i class="fa-solid fa-microchip text-blue-500 text-3xl absolute"></i>
            </div>
            <div class="space-y-2">
                <h3 id="loader-title" class="text-2xl font-bold text-white">Menyiapkan Engine OCR...</h3>
                <p id="loader-desc" class="text-slate-400 text-sm">Mengunduh model AI dan menginisialisasi WebAssembly di browser Anda.</p>
            </div>
            <!-- Progress Bar -->
            <div class="bg-slate-900 border border-slate-800 h-4 w-full rounded-full overflow-hidden p-0.5">
                <div id="loader-progress" class="bg-gradient-to-r from-blue-600 to-indigo-600 h-full rounded-full w-0 transition-all duration-300"></div>
            </div>
            <div id="loader-status" class="text-xs font-semibold text-slate-500 uppercase tracking-widest">
                Mengunduh OpenCV WASM...
            </div>
            <!-- Error Alert -->
            <div id="loader-error" class="hidden p-4 rounded-xl bg-red-950/40 border border-red-500/20 text-red-200 text-sm text-left">
                <p class="font-bold text-red-400 mb-1">Gagal Memproses Dokumen</p>
                <span id="error-text">Gagal memuat WebAssembly runtime.</span>
                <a href="<?= site_url('/') ?>" class="mt-3 block text-center bg-red-600 hover:bg-red-500 text-white font-semibold py-2 px-4 rounded-lg transition-colors">Kembali ke Beranda</a>
            </div>
        </div>
    </div>

    <!-- WORKSPACE TOOLBAR -->
    <div id="toolbar" class="hidden h-14 bg-slate-900/60 border-b border-slate-800/50 backdrop-blur-md px-4 flex items-center justify-between shrink-0">
        <div class="flex items-center space-x-3">
            <a href="<?= site_url('/') ?>" class="text-slate-400 hover:text-white transition-colors" title="Kembali">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <span class="h-4 w-px bg-slate-800"></span>
            <span id="doc-title" class="text-sm font-semibold text-slate-200 truncate max-w-xs md:max-w-md">Dokumen</span>
            <span class="px-2 py-0.5 text-[10px] font-extrabold bg-blue-600/20 text-blue-400 border border-blue-500/25 rounded-md uppercase tracking-wider">Client-Side WASM</span>
            <span class="h-4 w-px bg-slate-800"></span>
            <!-- Model Selection Dropdown -->
            <div class="flex items-center space-x-1.5">
                <span class="text-xs text-slate-400 font-semibold hidden sm:inline">Model AI:</span>
                <select id="model-selector" onchange="changeOcrModel()" class="bg-slate-950 border border-slate-800 text-xs text-slate-300 rounded-lg px-2.5 py-1 focus:outline-none focus:border-blue-500 font-semibold transition-all">
                    <option value="v3">PP-OCR v3 (Default)</option>
                    <option value="v6_ort">PP-OCR v6 (ORT - Cepat)</option>
                    <option value="v6_onnx">PP-OCR v6 (ONNX)</option>
                </select>
            </div>
        </div>
        
        <div class="flex items-center space-x-3">
            <!-- Zoom controls (PDF only) -->
            <?php if ($isPdf) : ?>
                <div class="flex items-center space-x-1 bg-slate-950 border border-slate-800 rounded-lg p-0.5">
                    <button onclick="zoomOut()" class="h-8 w-8 text-slate-400 hover:text-white hover:bg-slate-800 rounded-md transition-all">
                        <i class="fa-solid fa-minus"></i>
                    </button>
                    <span id="zoom-text" class="text-xs font-semibold text-slate-300 w-12 text-center">100%</span>
                    <button onclick="zoomIn()" class="h-8 w-8 text-slate-400 hover:text-white hover:bg-slate-800 rounded-md transition-all">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
            <?php endif; ?>

            <span class="h-4 w-px bg-slate-800"></span>

            <!-- Toggle Overlay Borders -->
            <button id="btn-toggle-borders" onclick="toggleBorders()" class="px-3 py-2 bg-blue-600 text-white hover:bg-blue-500 rounded-lg text-xs font-semibold flex items-center space-x-1.5 transition-all shadow-md">
                <i class="fa-solid fa-border-all"></i>
                <span class="hidden sm:inline">Batas Teks</span>
            </button>

            <!-- Toggle Background Opacity -->
            <button id="btn-toggle-bg" onclick="toggleBackground()" class="px-3 py-2 bg-blue-600 text-white hover:bg-blue-500 rounded-lg text-xs font-semibold flex items-center space-x-1.5 transition-all shadow-md">
                <i class="fa-solid fa-eye"></i>
                <span class="hidden sm:inline">Transparansi</span>
            </button>
        </div>
    </div>

    <!-- MAIN GRID PANELS -->
    <div id="workspace" class="hidden flex-grow flex flex-col md:flex-row overflow-hidden bg-slate-950">
        
        <!-- Viewer Pane (Left) -->
        <div class="flex-grow overflow-y-auto p-6 flex flex-col items-center custom-scroll" id="viewer-pane">
            <div id="viewer-container" class="relative max-w-full">
                <!-- If it is an image, render img template -->
                <?php if (!$isPdf) : ?>
                    <div id="img-wrapper" class="relative select-none rounded-lg overflow-hidden shadow-2xl bg-white border border-slate-800">
                        <img id="source-img" src="<?= $fileUrl ?>" class="block max-w-full h-auto transition-opacity duration-300 animate-pulse" style="opacity: 0.15;" />
                        <div id="img-overlay-layer" class="absolute inset-0 z-10 pointer-events-auto"></div>
                    </div>
                <?php else : ?>
                    <!-- PDF pages containers will be dynamically injected here -->
                    <div id="pdf-pages-wrapper" class="space-y-8 select-none"></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar Panel (Right) -->
        <aside class="w-full md:w-96 bg-slate-900/40 border-l border-slate-900 flex flex-col shrink-0">
            <!-- Sidebar Tabs -->
            <div class="h-12 border-b border-slate-950 flex">
                <button onclick="switchTab('text')" id="tab-btn-text" class="flex-1 text-sm font-semibold border-b-2 border-blue-500 text-blue-500 flex items-center justify-center space-x-2 bg-slate-900/10">
                    <i class="fa-solid fa-align-left"></i>
                    <span>Teks Hasil</span>
                </button>
                <button onclick="switchTab('json')" id="tab-btn-json" class="flex-1 text-sm font-semibold border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-code"></i>
                    <span>JSON Raw</span>
                </button>
            </div>

            <!-- Tab Contents -->
            <div class="flex-grow overflow-y-auto p-4 custom-scroll flex flex-col min-h-0">
                
                <!-- Text Tab Panel -->
                <div id="tab-panel-text" class="flex flex-col flex-grow min-h-0">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Ekstraksi Teks Lengkap</span>
                        <button onclick="copyToClipboard()" class="text-xs font-bold text-blue-500 hover:text-blue-400 flex items-center space-x-1">
                            <i class="fa-solid fa-copy"></i>
                            <span>Salin Teks</span>
                        </button>
                    </div>
                    <textarea id="extracted-text-area" class="flex-grow w-full bg-slate-950 border border-slate-800/80 rounded-xl p-4 text-slate-300 font-mono text-sm leading-relaxed focus:outline-none focus:border-blue-500/50 resize-none min-h-[250px]" readonly></textarea>
                </div>

                <!-- JSON Tab Panel -->
                <div id="tab-panel-json" class="hidden flex-col flex-grow min-h-0">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Struktur Data Koordinat</span>
                        <button onclick="copyJsonToClipboard()" class="text-xs font-bold text-blue-500 hover:text-blue-400 flex items-center space-x-1">
                            <i class="fa-solid fa-copy"></i>
                            <span>Salin JSON</span>
                        </button>
                    </div>
                    <pre id="json-raw-area" class="flex-grow w-full bg-slate-950 border border-slate-800/80 rounded-xl p-4 text-emerald-400 font-mono text-xs overflow-auto leading-normal min-h-[250px]"></pre>
                </div>
            </div>
        </aside>
    </div>
</div>

<!-- 1. PDF.js (Loaded for both if required, specifically PDF) -->
<?php if ($isPdf) : ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
    </script>
<?php endif; ?>

<!-- 2. ONNX Web Assembly Runtime -->
<script src="https://cdn.jsdelivr.net/npm/onnxruntime-web@1.20.1/dist/ort.min.js"></script>

<!-- 3. OpenCV WebAssembly wrapper (CDN) -->
<script>
  // Setup callback for OpenCV init
  window.cv = window.Module = {
    onRuntimeInitialized: function() {
      console.log('OpenCV WASM runtime is fully loaded and ready.');
      window.isOpencvReady = true;
      if (window.onOpencvLoaded) window.onOpencvLoaded();
    }
  };
</script>
<script async src="https://docs.opencv.org/4.5.4/opencv.js"></script>

<!-- Node.js Environment Shim for Browser -->
<script>
  window.process = {
    env: { NODE_ENV: 'production' },
    cwd: function() { return '/'; }
  };
  window.setImmediate = window.setImmediate || function(fn, ...args) {
    return setTimeout(fn, 0, ...args);
  };
</script>

<!-- 4. Our compiled client-side library -->
<script src="<?= base_url('js/paddle-ocr-client.js') ?>"></script>

<script>
    const isPdf = <?= $isPdf ? 'true' : 'false' ?>;
    const fileUrl = "<?= $fileUrl ?>";

    let ocrClient = null;
    let jobResults = []; // Stores the final processed page structures
    let bgOpacity = 0.45;
    let hasBorders = true;
    let currentScale = 1.25; // PDF rendering zoom scale
    let pdfDoc = null;
    let currentModelType = 'v3';

    // Document name extraction
    try {
        const decodedUrl = decodeURIComponent(fileUrl);
        const name = decodedUrl.substring(decodedUrl.lastIndexOf('/') + 1);
        document.getElementById('doc-title').textContent = name;
    } catch(e) {}

    // Helper promise to wait for OpenCV
    function waitForOpenCV() {
        return new Promise((resolve) => {
            if (window.isOpencvReady) {
                resolve();
            } else {
                window.onOpencvLoaded = resolve;
            }
        });
    }

    // Main Engine Orchestrator
    async function startOcrEngine() {
        try {
            // Step 1: Wait for OpenCV WASM runtime
            updateLoader('Mengunduh OpenCV WASM...', 20);
            await waitForOpenCV();

            // Step 2: Initialize PaddleOCR client side
            updateLoader('Memuat Model AI (ONNX)...', 50);
            
            ocrClient = new window.PaddleOCRClient({
                verbose: true,
                maxSideLength: 2000 // match previous settings for high accuracy
            });

            // Initialize models pointing to local static URLs
            let modelConfig = {};
            if (currentModelType === 'v3') {
                modelConfig = {
                    detection: '<?= base_url("models/en_PP-OCRv3_det_infer.onnx") ?>',
                    recognition: '<?= base_url("models/en_PP-OCRv3_rec_infer.onnx") ?>',
                    charactersDictionary: '<?= base_url("models/en_dict.txt") ?>'
                };
            } else if (currentModelType === 'v6_onnx') {
                modelConfig = {
                    detection: '<?= base_url("models/PP-OCRv6_medium_det.onnx") ?>',
                    recognition: '<?= base_url("models/PP-OCRv6_medium_rec.onnx") ?>',
                    charactersDictionary: '<?= base_url("models/ppocrv6_dict.txt") ?>'
                };
            } else if (currentModelType === 'v6_ort') {
                modelConfig = {
                    detection: '<?= base_url("models/PP-OCRv6_medium_det.ort") ?>',
                    recognition: '<?= base_url("models/PP-OCRv6_medium_rec.ort") ?>',
                    charactersDictionary: '<?= base_url("models/ppocrv6_dict.txt") ?>'
                };
            }

            await ocrClient.init(modelConfig);

            updateLoader('Menginisialisasi File...', 80);

            jobResults = []; // Clear previous results
            if (!isPdf) {
                document.getElementById('img-overlay-layer').innerHTML = '';
                await processImageOcr();
            } else {
                await processPdfOcr();
            }

            // Finish and show workspace
            finishProcessing();

        } catch (err) {
            console.error('Initialization / OCR Error:', err);
            showError(err.message);
        }
    }

    function updateLoader(statusText, percentage) {
        document.getElementById('loader-status').textContent = statusText;
        document.getElementById('loader-progress').style.width = `${percentage}%`;
    }

    function showError(msg) {
        document.getElementById('loader-title').textContent = 'Error';
        document.getElementById('loader-desc').textContent = 'Terjadi kesalahan saat memproses OCR di browser Anda.';
        document.getElementById('loader-progress').parentElement.classList.add('hidden');
        document.getElementById('loader-status').classList.add('hidden');
        
        const errorEl = document.getElementById('loader-error');
        document.getElementById('error-text').textContent = msg;
        errorEl.classList.remove('hidden');
    }

    // ==========================================
    // IMAGE OCR PROCESSOR
    // ==========================================
    async function processImageOcr() {
        updateLoader('Menjalankan Model OCR pada Gambar...', 90);

        const img = document.getElementById('source-img');
        
        // Wait for image load
        await new Promise((resolve, reject) => {
            if (img.complete) resolve();
            img.onload = resolve;
            img.onerror = () => reject(new Error('Gagal memuat URL gambar.'));
        });

        // Run OCR on the image
        const ocrResult = await ocrClient.recognize(img);
        
        // Store as Page 1
        jobResults.push({
            page: 1,
            text: ocrResult.text,
            lines: ocrResult.lines
        });

        // Setup the scaling and overlay layer
        setupImageOverlay(ocrResult);
    }

    function setupImageOverlay(ocrResult) {
        const img = document.getElementById('source-img');
        const overlayLayer = document.getElementById('img-overlay-layer');

        // Stop skeleton loader effect
        img.classList.remove('animate-pulse');
        img.style.opacity = bgOpacity;

        function renderOverlay() {
            overlayLayer.innerHTML = '';
            
            // Image responsive dimensions scale calculation
            const naturalW = img.naturalWidth;
            const displayW = img.clientWidth;
            const scaleFactor = displayW / naturalW;

            if (!ocrResult || !ocrResult.lines) return;

            ocrResult.lines.forEach(line => {
                const box = line.box;
                const left = box.x * scaleFactor;
                const top = box.y * scaleFactor;
                const width = box.width * scaleFactor;
                const height = box.height * scaleFactor;
                const fontSize = Math.max(7, Math.round(height * 0.75));

                const span = document.createElement('div');
                span.className = 'text-overlay absolute pointer-events-auto transition-all flex items-center box-border';
                span.style.left = `${left}px`;
                span.style.top = `${top}px`;
                span.style.width = `${width}px`;
                span.style.height = `${height}px`;
                span.style.fontSize = `${fontSize}px`;
                span.title = `Text: ${line.text}\nBox: x:${box.x}, y:${box.y}, w:${box.width}, h:${box.height}`;
                span.innerText = line.text;

                applyStyleToElement(span);
                overlayLayer.appendChild(span);
            });
        }

        renderOverlay();
        window.addEventListener('resize', renderOverlay);
    }

    // ==========================================
    // PDF OCR PROCESSOR
    // ==========================================
    async function processPdfOcr() {
        const wrapper = document.getElementById('pdf-pages-wrapper');
        wrapper.innerHTML = '';

        console.log('Loading PDF from URL:', fileUrl);
        const loadingTask = pdfjsLib.getDocument(fileUrl);
        pdfDoc = await loadingTask.promise;
        
        const totalPages = pdfDoc.numPages;
        console.log(`PDF loaded. Total Pages: ${totalPages}`);

        for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
            updateLoader(`Membaca Halaman ${pageNum} dari ${totalPages}...`, Math.round(80 + (pageNum / totalPages) * 15));
            
            // Create container templates
            const container = document.createElement('div');
            container.className = 'relative rounded-lg shadow-xl border border-slate-800 bg-white';
            container.dataset.page = pageNum;
            
            const canvas = document.createElement('canvas');
            canvas.className = 'block rounded-lg transition-opacity duration-300';
            canvas.style.opacity = bgOpacity;
            
            const overlayLayer = document.createElement('div');
            overlayLayer.className = 'absolute inset-0 z-10 pointer-events-auto';

            container.appendChild(canvas);
            container.appendChild(overlayLayer);
            wrapper.appendChild(container);

            // 1. Render PDF page using PDF.js
            const page = await pdfDoc.getPage(pageNum);
            const viewport = page.getViewport({ scale: currentScale });
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            const context = canvas.getContext('2d');
            await page.render({ canvasContext: context, viewport: viewport }).promise;

            // 2. Perform OCR on this rendered canvas in memory
            updateLoader(`Memindai Teks Halaman ${pageNum} dari ${totalPages}...`, Math.round(80 + (pageNum / totalPages) * 15));
            const ocrResult = await ocrClient.recognize(canvas);

            // 3. Save result
            const pageData = {
                page: pageNum,
                text: ocrResult.text,
                lines: ocrResult.lines
            };
            jobResults.push(pageData);

            // 4. Render overlay box items on top of the page canvas
            renderPdfOverlay(pageNum, overlayLayer, pageData, currentScale);
        }
    }

    function renderPdfOverlay(pageNum, overlayLayer, pageData, scale) {
        overlayLayer.innerHTML = '';
        
        // Since we ran OCR on the canvas rendered at currentScale directly, 
        // the coordinate systems map 1:1! 
        // We do NOT need to scale down or up because we read directly from the screen canvas!
        // This is incredibly robust!
        if (pageData && pageData.lines) {
            pageData.lines.forEach(line => {
                const box = line.box;
                const left = box.x;
                const top = box.y;
                const width = box.width;
                const height = box.height;
                const fontSize = Math.max(7, Math.round(height * 0.75));

                const span = document.createElement('div');
                span.className = 'text-overlay absolute pointer-events-auto transition-all flex items-center box-border';
                span.style.left = `${left}px`;
                span.style.top = `${top}px`;
                span.style.width = `${width}px`;
                span.style.height = `${height}px`;
                span.style.fontSize = `${fontSize}px`;
                span.title = `Text: ${line.text}\nBox: x:${box.x}, y:${box.y}, w:${box.width}, h:${box.height}`;
                span.innerText = line.text;

                applyStyleToElement(span);
                overlayLayer.appendChild(span);
            });
        }
    }

    async function reRenderPdfLayoutOnly() {
        if (!pdfDoc) return;
        const wrapper = document.getElementById('pdf-pages-wrapper');

        for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
            const container = wrapper.querySelector(`[data-page="${pageNum}"]`);
            if (!container) continue;

            const canvas = container.querySelector('canvas');
            const overlayLayer = container.querySelector('.absolute');
            
            const page = await pdfDoc.getPage(pageNum);
            const viewport = page.getViewport({ scale: currentScale });
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            const context = canvas.getContext('2d');
            await page.render({ canvasContext: context, viewport: viewport }).promise;

            // Re-render layout overlay scale calculations
            // Since we scale the PDF canvas, but the OCR results coordinates are stored 
            // from the first scan (which ran at a different initial scale, e.g. 1.25):
            // We scale coordinates using: scaleFactor = currentScale / initialScale (which was 1.25)
            const relativeScale = currentScale / 1.25;
            const pageData = jobResults.find(r => r.page === pageNum);

            overlayLayer.innerHTML = '';
            if (pageData && pageData.lines) {
                pageData.lines.forEach(line => {
                    const box = line.box;
                    const left = box.x * relativeScale;
                    const top = box.y * relativeScale;
                    const width = box.width * relativeScale;
                    const height = box.height * relativeScale;
                    const fontSize = Math.max(7, Math.round(height * 0.75));

                    const span = document.createElement('div');
                    span.className = 'text-overlay absolute pointer-events-auto transition-all flex items-center box-border';
                    span.style.left = `${left}px`;
                    span.style.top = `${top}px`;
                    span.style.width = `${width}px`;
                    span.style.height = `${height}px`;
                    span.style.fontSize = `${fontSize}px`;
                    span.title = `Text: ${line.text}\nBox: x:${box.x}, y:${box.y}, w:${box.width}, h:${box.height}`;
                    span.innerText = line.text;

                    applyStyleToElement(span);
                    overlayLayer.appendChild(span);
                });
            }
        }
    }

    // ==========================================
    // UTILITY METHODS
    // ==========================================
    function finishProcessing() {
        // Transition Loader to Workspace
        document.getElementById('loader-screen').classList.add('hidden');
        document.getElementById('toolbar').classList.remove('hidden');
        document.getElementById('workspace').classList.remove('hidden');

        // Populate sidebars
        document.getElementById('extracted-text-area').value = jobResults.map(page => `=== HALAMAN ${page.page} ===\n${page.text}`).join('\n\n');
        document.getElementById('json-raw-area').textContent = JSON.stringify(jobResults, null, 2);
    }

    function applyStyleToElement(el) {
        if (hasBorders) {
            el.style.border = '1px dashed rgba(239, 68, 68, 0.35)';
            el.style.backgroundColor = 'rgba(253, 224, 71, 0.08)';
            el.style.color = '#000000';
            
            el.onmouseenter = () => {
                el.style.backgroundColor = 'rgba(253, 224, 71, 0.35)';
                el.style.borderColor = 'rgba(239, 68, 68, 0.8)';
            };
            el.onmouseleave = () => {
                el.style.backgroundColor = 'rgba(253, 224, 71, 0.08)';
                el.style.borderColor = 'rgba(239, 68, 68, 0.35)';
            };
        } else {
            el.style.border = 'none';
            el.style.backgroundColor = 'transparent';
            el.style.color = bgOpacity === 0 ? '#f1f5f9' : '#000000';
            el.onmouseenter = null;
            el.onmouseleave = null;
        }
    }

    // Toolbar Actions
    function toggleBackground() {
        const btn = document.getElementById('btn-toggle-bg');
        
        if (bgOpacity > 0) {
            bgOpacity = 0;
            btn.classList.remove('bg-blue-600', 'hover:bg-blue-500');
            btn.classList.add('bg-slate-800', 'hover:bg-slate-700');
        } else {
            bgOpacity = 0.45;
            btn.classList.add('bg-blue-600', 'hover:bg-blue-500');
            btn.classList.remove('bg-slate-800', 'hover:bg-slate-700');
        }

        if (!isPdf) {
            document.getElementById('source-img').style.opacity = bgOpacity;
        } else {
            document.querySelectorAll('canvas').forEach(c => c.style.opacity = bgOpacity);
        }
        
        document.querySelectorAll('.text-overlay').forEach(applyStyleToElement);
    }

    function toggleBorders() {
        const btn = document.getElementById('btn-toggle-borders');
        hasBorders = !hasBorders;

        if (hasBorders) {
            btn.classList.add('bg-blue-600', 'hover:bg-blue-500');
            btn.classList.remove('bg-slate-800', 'hover:bg-slate-700');
        } else {
            btn.classList.remove('bg-blue-600', 'hover:bg-blue-500');
            btn.classList.add('bg-slate-800', 'hover:bg-slate-700');
        }

        document.querySelectorAll('.text-overlay').forEach(applyStyleToElement);
    }

    async function zoomIn() {
        if (currentScale < 3.0) {
            currentScale += 0.25;
            document.getElementById('zoom-text').textContent = `${Math.round(currentScale * 100)}%`;
            await reRenderPdfLayoutOnly();
        }
    }

    async function zoomOut() {
        if (currentScale > 0.5) {
            currentScale -= 0.25;
            document.getElementById('zoom-text').textContent = `${Math.round(currentScale * 100)}%`;
            await reRenderPdfLayoutOnly();
        }
    }

    function switchTab(tab) {
        const textBtn = document.getElementById('tab-btn-text');
        const jsonBtn = document.getElementById('tab-btn-json');
        const textPanel = document.getElementById('tab-panel-text');
        const jsonPanel = document.getElementById('tab-panel-json');

        if (tab === 'text') {
            textBtn.className = "flex-1 text-sm font-semibold border-b-2 border-blue-500 text-blue-500 flex items-center justify-center space-x-2 bg-slate-900/10";
            jsonBtn.className = "flex-1 text-sm font-semibold border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center justify-center space-x-2";
            textPanel.classList.remove('hidden');
            textPanel.classList.add('flex');
            jsonPanel.classList.add('hidden');
            jsonPanel.classList.remove('flex');
        } else {
            jsonBtn.className = "flex-1 text-sm font-semibold border-b-2 border-blue-500 text-blue-500 flex items-center justify-center space-x-2 bg-slate-900/10";
            textBtn.className = "flex-1 text-sm font-semibold border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center justify-center space-x-2";
            jsonPanel.classList.remove('hidden');
            jsonPanel.classList.add('flex');
            textPanel.classList.add('hidden');
            textPanel.classList.remove('flex');
        }
    }

    function copyToClipboard() {
        const text = document.getElementById('extracted-text-area').value;
        navigator.clipboard.writeText(text).then(() => {
            alert('Teks hasil OCR berhasil disalin!');
        });
    }

    function copyJsonToClipboard() {
        const text = document.getElementById('json-raw-area').textContent;
        navigator.clipboard.writeText(text).then(() => {
            alert('Struktur data JSON berhasil disalin!');
        });
    }

    async function changeOcrModel() {
        currentModelType = document.getElementById('model-selector').value;
        
        // Transition to loading screen
        const loader = document.getElementById('loader-screen');
        loader.classList.remove('hidden');
        loader.style.display = 'flex'; // Ensure active flex layout
        
        document.getElementById('loader-title').textContent = 'Mengganti Model OCR...';
        document.getElementById('loader-desc').textContent = 'Mengunduh model baru dan menginisialisasi WebAssembly runtime.';
        document.getElementById('loader-progress').parentElement.classList.remove('hidden');
        document.getElementById('loader-status').classList.remove('hidden');
        document.getElementById('loader-error').classList.add('hidden');
        
        // Restart the engine
        await startOcrEngine();
    }

    // Trigger initialization on load
    window.addEventListener('load', startOcrEngine);
</script>
<?= $this->endSection() ?>
