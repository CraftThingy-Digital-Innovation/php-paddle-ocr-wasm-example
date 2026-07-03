# PHP PaddleOCR WASM Example (CodeIgniter 4)

[Bahasa Indonesia](#bahasa-indonesia) | [English](#english)

---

## Bahasa Indonesia

Proyek ini adalah contoh aplikasi web berbasis **CodeIgniter 4** yang mengimplementasikan **PaddleOCR secara 100% Client-Side** di dalam browser menggunakan WebAssembly (WASM). 

Arsitektur ini mereduksi beban pemrosesan AI di server PHP Anda dengan memindahkan komputasi ekstraksi teks ke browser client menggunakan ONNX Runtime Web dan OpenCV.js.

### 1. Persyaratan Sistem & Instalasi

#### Persyaratan Sistem
*   PHP versi 8.2 atau lebih tinggi.
*   Ekstensi PHP yang aktif: `intl`, `mbstring`, `json`, `libcurl`.
*   Node.js (hanya jika ingin melakukan *bundling* ulang library client-side).

#### Langkah Instalasi
1.  **Unduh / Clone repositori** ini ke server lokal Anda (misal: `D:\CraftThingy\php-ocr-application-test`).
2.  Buka terminal pada folder proyek tersebut dan jalankan server pengembangan lokal bawaan CodeIgniter 4:
    ```bash
    php spark serve
    ```
3.  Buka web browser Anda dan akses:
    ```
    http://localhost:8080
    ```

---

### 2. Cara Menggunakan Client-Side OCR di Proyek PHP Lain

Jika Anda ingin mengintegrasikan fungsionalitas ini ke dalam proyek PHP Anda yang lain (Laravel, Vanilla PHP, CI3, Symfony, dll.), Anda bisa menyalin aset statisnya:

#### Langkah A: Salin Berkas Library & Model AI
1.  **Pustaka Javascript**: Salin `public/js/paddle-ocr-client.js` ke folder asset publik JavaScript Anda (misal: `public/assets/js/`).
2.  **File Model ONNX/ORT**: Salin isi folder `public/models/` ke folder publik proyek Anda (misal: `public/assets/models/`). Folder ini berisi:
    *   **PP-OCRv3 (Ringan & Cepat)**:
        *   `en_PP-OCRv3_det_infer.onnx` (Model Deteksi ~2.4MB)
        *   `en_PP-OCRv3_rec_infer.onnx` (Model Rekognisi ~8.9MB)
        *   `en_dict.txt` (Kamus V3)
    *   **PP-OCRv6 (Akurasi Tinggi)**:
        *   `PP-OCRv6_medium_det.ort` (Model Deteksi ORT ~62.1MB)
        *   `PP-OCRv6_medium_rec.ort` (Model Rekognisi ORT ~76.6MB)
        *   `ppocrv6_dict.txt` (Kamus V6)

> [!TIP]
> Seluruh berkas model teroptimasi di atas dapat diunduh langsung dari repositori model resmi organisasi Anda:
> **[cty-paddle-ocr-models](https://github.com/CraftThingy-Digital-Innovation/cty-paddle-ocr-models)**.

#### Langkah B: Impor CDN Dependensi pada Halaman HTML/PHP
Masukkan script berikut ke dalam view HTML/PHP Anda:

```html
<!-- 1. PDF.js (Wajib jika Anda ingin memindai file PDF) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
</script>

<!-- 2. ONNX Runtime Web (WASM execution engine) -->
<script src="https://cdn.jsdelivr.net/npm/onnxruntime-web@1.20.1/dist/ort.min.js"></script>

<!-- 3. OpenCV WebAssembly (Untuk crop dan pengolahan matriks gambar) -->
<script>
  window.cv = window.Module = {
    onRuntimeInitialized: function() {
      console.log('OpenCV WASM runtime is ready.');
      window.isOpencvReady = true;
      if (window.onOpencvLoaded) window.onOpencvLoaded();
    }
  };
</script>
<script async src="https://docs.opencv.org/4.5.4/opencv.js"></script>

<!-- 4. Environment Shim (Node.js compatibility layer untuk browser) -->
<script>
  window.process = {
    env: { NODE_ENV: 'production' },
    cwd: function() { return '/'; }
  };
  window.setImmediate = window.setImmediate || function(fn, ...args) {
    return setTimeout(fn, 0, ...args);
  };
</script>

<!-- 5. PaddleOCR Client Library -->
<script src="/assets/js/paddle-ocr-client.js"></script>
```

#### Langkah C: Panggil Pustaka OCR lewat JavaScript Anda
Inisialisasi engine dan lakukan pembacaan gambar:

```javascript
async function runOCR() {
  // Tunggu sampai OpenCV WASM siap
  if (!window.isOpencvReady) {
    await new Promise(resolve => window.onOpencvLoaded = resolve);
  }

  // 1. Inisialisasi Client
  const ocr = new window.PaddleOCRClient({
    verbose: true,
    maxSideLength: 2000 // Presisi tinggi
  });

  // 2. Unduh dan Muat Model secara Asinkron
  // Memilih model PP-OCRv6 (ORT) untuk akurasi tinggi
  await ocr.init({
    detection: '/assets/models/PP-OCRv6_medium_det.ort',
    recognition: '/assets/models/PP-OCRv6_medium_rec.ort',
    charactersDictionary: '/assets/models/ppocrv6_dict.txt'
  });

  // 3. Jalankan OCR pada element <img> atau <canvas>
  const imgElement = document.getElementById('my-image');
  const result = await ocr.recognize(imgElement);

  console.log("Full Text:", result.text);
  console.log("Lines & Coordinates:", result.lines);
}
```

---

## English

This repository showcases a **CodeIgniter 4** web application demonstrating **100% client-side PaddleOCR** execution directly in the browser via WebAssembly (WASM).

This architecture offloads the AI computation/inference overhead from your PHP backend server to the client's web browser using ONNX Runtime Web and OpenCV.js.

### 1. Requirements & Local Setup

#### System Requirements
*   PHP version 8.2 or higher.
*   Required active PHP extensions: `intl`, `mbstring`, `json`, `libcurl`.
*   Node.js (only needed if you plan to re-bundle the client-side library).

#### Installation Steps
1.  **Clone / Download this repository** into your local environment (e.g. `D:\CraftThingy\php-ocr-application-test`).
2.  Open a terminal inside the project directory and run CodeIgniter's built-in local development server:
    ```bash
    php spark serve
    ```
3.  Open your browser and navigate to:
    ```
    http://localhost:8080
    ```

---

### 2. Implementing Client-Side OCR in Other PHP Projects

To port this client-side OCR engine into your own PHP project (Laravel, Vanilla PHP, CI3, Symfony, etc.), perform the following steps:

#### Step A: Copy Assets
1.  **Javascript Library**: Copy the script `public/js/paddle-ocr-client.js` into your public asset folder (e.g., `public/assets/js/`).
2.  **ONNX/ORT AI Models**: Copy all model files inside the `public/models/` folder into your public assets directory (e.g., `public/assets/models/`). This directory includes:
    *   **PP-OCRv3 (Lightweight & Fast)**:
        *   `en_PP-OCRv3_det_infer.onnx` (Text Detection Model ~2.4MB)
        *   `en_PP-OCRv3_rec_infer.onnx` (Character Recognition Model ~8.9MB)
        *   `en_dict.txt` (V3 Dictionary)
    *   **PP-OCRv6 (High Accuracy)**:
        *   `PP-OCRv6_medium_det.ort` (ORT Detection Model ~62.1MB)
        *   `PP-OCRv6_medium_rec.ort` (ORT Recognition Model ~76.6MB)
        *   `ppocrv6_dict.txt` (V6 Dictionary)

> [!TIP]
> All optimized model weights listed above can be downloaded from your organization's official models repository:
> **[cty-paddle-ocr-models](https://github.com/CraftThingy-Digital-Innovation/cty-paddle-ocr-models)**.

#### Step B: Import CDN Dependencies in your Layout
Insert the following script tags into your HTML/PHP layout file:

```html
<!-- 1. PDF.js (Required only if you are scanning PDF documents) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
</script>

<!-- 2. ONNX Runtime Web (WASM execution engine) -->
<script src="https://cdn.jsdelivr.net/npm/onnxruntime-web@1.20.1/dist/ort.min.js"></script>

<!-- 3. OpenCV WebAssembly (Used for matrix processing and image cropping) -->
<script>
  window.cv = window.Module = {
    onRuntimeInitialized: function() {
      console.log('OpenCV WASM runtime is ready.');
      window.isOpencvReady = true;
      if (window.onOpencvLoaded) window.onOpencvLoaded();
    }
  };
</script>
<script async src="https://docs.opencv.org/4.5.4/opencv.js"></script>

<!-- 4. Environment Shim (Node.js compatibility layer for browser engines) -->
<script>
  window.process = {
    env: { NODE_ENV: 'production' },
    cwd: function() { return '/'; }
  };
  window.setImmediate = window.setImmediate || function(fn, ...args) {
    return setTimeout(fn, 0, ...args);
  };
</script>

<!-- 5. PaddleOCR Client Library -->
<script src="/assets/js/paddle-ocr-client.js"></script>
```

#### Step C: Execute the OCR Client via JS
Initialize the OCR engine and process document images:

```javascript
async function runOCR() {
  // Ensure OpenCV WebAssembly is fully loaded
  if (!window.isOpencvReady) {
    await new Promise(resolve => window.onOpencvLoaded = resolve);
  }

  // 1. Instantiate the Client
  const ocr = new window.PaddleOCRClient({
    verbose: true,
    maxSideLength: 2000 // High-precision scaling
  });

  // 2. Fetch and load ORT Models asynchronously (loading V6)
  await ocr.init({
    detection: '/assets/models/PP-OCRv6_medium_det.ort',
    recognition: '/assets/models/PP-OCRv6_medium_rec.ort',
    charactersDictionary: '/assets/models/ppocrv6_dict.txt'
  });

  // 3. Scan the image element or canvas
  const imgElement = document.getElementById('my-image');
  const result = await ocr.recognize(imgElement);

  console.log("Full Text:", result.text);
  console.log("Lines & Coordinates:", result.lines);
}
```

---

## Asal Usul & Kredit / Origins & Credits

### Bahasa Indonesia
Proyek ini dikembangkan oleh **CraftThingy Digital Innovation (Alif Nurhidayat)**. Proyek ini dibangun di atas fondasi inovasi open-source berikut:
1.  **Baidu PaddleOCR**: Model deteksi & pengenalan teks kelas dunia yang menjadi inti dari sistem OCR ini.
2.  **cty-paddle-ocr**: Pustaka isomorphic mandiri yang kita gunakan untuk menjalankan PaddleOCR di sisi client browser secara stabil dan responsif.
3.  **cty-paddle-ocr-models**: Repositori model resmi organisasi kita tempat menampung dan mendistribusikan model secara aman.
4.  **ONNX Runtime Web (Microsoft)**: Engine eksekusi WebAssembly yang menjalankan model neural network di browser.
5.  **OpenCV.js**: Pustaka pengolahan citra komputer yang menangani transformasi geometris dan cropping karakter.
6.  **PDF.js (Mozilla)**: Pustaka rendering dokumen PDF yang memproses halaman dokumen menjadi frame canvas.

### English
This project is developed by **CraftThingy Digital Innovation (Alif Nurhidayat)**. It is built upon the following open-source projects and innovations:
1.  **Baidu PaddleOCR**: The world-class OCR system providing the core deep learning models for text detection and recognition.
2.  **cty-paddle-ocr**: Our decoupled isomorphic library running text detection and recognition inside the browser.
3.  **cty-paddle-ocr-models**: Our official models repository hosting large neural network weight files.
4.  **ONNX Runtime Web (Microsoft)**: The WebAssembly execution runtime that powers model inference in browser clients.
5.  **OpenCV.js**: The computer vision engine handling character cropping and geometry conversions.
6.  **PDF.js (Mozilla)**: The document rendering library enabling multi-page PDF scanning inside the browser canvas.

