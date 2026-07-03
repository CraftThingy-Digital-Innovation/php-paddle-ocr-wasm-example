# PHP PaddleOCR WASM Example (CodeIgniter 4)

[Bahasa Indonesia](#bahasa-indonesia) | [English](#english)

---

## Bahasa Indonesia

Proyek ini adalah contoh aplikasi web berbasis **CodeIgniter 4** yang mengimplementasikan **PaddleOCR secara 100% Client-Side** di dalam browser menggunakan WebAssembly (WASM). 

Arsitektur ini mereduksi beban pemrosesan AI di server PHP Anda dengan memindahkan komputasi ekstraksi teks ke browser client menggunakan ONNX Runtime Web dan OpenCV.js.

### 1. Persyaratan Sistem & Instalasi
1.  Pastikan PHP >= 8.2 terinstal (dengan ekstensi `intl` dan `mbstring` aktif pada `php.ini`).
2.  Jalankan server pengembangan lokal:
    ```bash
    php spark serve
    ```
3.  Akses web browser di `http://localhost:8080`.

### 2. Cara Menggunakan Client-Side OCR di Proyek PHP Lain
Untuk memindahkan fitur OCR ini ke proyek PHP Anda yang lain (Laravel, Vanilla PHP, CI3, dll.), ikuti langkah berikut:

1.  **Salin File**:
    *   Salin berkas `public/js/paddle-ocr-client.js` ke folder asset Javascript Anda.
    *   Salin seluruh file model AI di dalam folder `public/models/` ke folder publik Anda.
2.  **Impor Dependensi CDN di HTML/PHP**:
    ```html
    <!-- 1. PDF.js (Opsional, untuk membaca PDF) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script>pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';</script>

    <!-- 2. ONNX Runtime Web -->
    <script src="https://cdn.jsdelivr.net/npm/onnxruntime-web@1.20.1/dist/ort.min.js"></script>

    <!-- 3. OpenCV WebAssembly Bindings -->
    <script>
      window.cv = window.Module = {
        onRuntimeInitialized: () => { window.isOpencvReady = true; if (window.onOpencvLoaded) window.onOpencvLoaded(); }
      };
    </script>
    <script async src="https://docs.opencv.org/4.5.4/opencv.js"></script>

    <!-- 4. Node.js Environment Shims -->
    <script>
      window.process = { env: { NODE_ENV: 'production' }, cwd: () => '/' };
      window.setImmediate = (fn, ...args) => setTimeout(fn, 0, ...args);
    </script>

    <!-- 5. PaddleOCR Client Library -->
    <script src="/assets/js/paddle-ocr-client.js"></script>
    ```
3.  **Panggil OCR Lewat JavaScript**:
    ```javascript
    const ocr = new PaddleOCRClient();
    await ocr.init({
      detection: '/assets/models/en_PP-OCRv3_det_infer.onnx',
      recognition: '/assets/models/en_PP-OCRv3_rec_infer.onnx',
      charactersDictionary: '/assets/models/en_dict.txt'
    });
    const result = await ocr.recognize(document.getElementById('my-image'));
    console.log(result.text);
    ```

---

## English

This repository showcases a **CodeIgniter 4** web application demonstrating **100% client-side PaddleOCR** execution directly in the browser via WebAssembly (WASM).

This architecture offloads the AI computation/inference overhead from your PHP backend server to the client's web browser using ONNX Runtime Web and OpenCV.js.

### 1. Requirements & Local Setup
1.  Ensure you have PHP >= 8.2 installed (with `intl` and `mbstring` extensions enabled in `php.ini`).
2.  Start the development server:
    ```bash
    php spark serve
    ```
3.  Navigate to `http://localhost:8080` in your web browser.

### 2. Implementing Client-Side OCR in Other PHP Projects
To port the client-side OCR engine into your own PHP project (Laravel, Vanilla PHP, CI3, Symfony, etc.), perform the following steps:

1.  **Copy Assets**:
    *   Copy the script file `public/js/paddle-ocr-client.js` into your public asset folder.
    *   Copy the AI models under `public/models/` into your public assets directory.
2.  **Import CDN Dependencies in your Layout**:
    ```html
    <!-- 1. PDF.js (Optional, only if scanning PDF files) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script>pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';</script>

    <!-- 2. ONNX Runtime Web -->
    <script src="https://cdn.jsdelivr.net/npm/onnxruntime-web@1.20.1/dist/ort.min.js"></script>

    <!-- 3. OpenCV WebAssembly Bindings -->
    <script>
      window.cv = window.Module = {
        onRuntimeInitialized: () => { window.isOpencvReady = true; if (window.onOpencvLoaded) window.onOpencvLoaded(); }
      };
    </script>
    <script async src="https://docs.opencv.org/4.5.4/opencv.js"></script>

    <!-- 4. Node.js Environment Shims -->
    <script>
      window.process = { env: { NODE_ENV: 'production' }, cwd: () => '/' };
      window.setImmediate = (fn, ...args) => setTimeout(fn, 0, ...args);
    </script>

    <!-- 5. PaddleOCR Client Library -->
    <script src="/assets/js/paddle-ocr-client.js"></script>
    ```
3.  **Execute the OCR Client via JS**:
    ```javascript
    const ocr = new PaddleOCRClient();
    await ocr.init({
      detection: '/assets/models/en_PP-OCRv3_det_infer.onnx',
      recognition: '/assets/models/en_PP-OCRv3_rec_infer.onnx',
      charactersDictionary: '/assets/models/en_dict.txt'
    });
    const result = await ocr.recognize(document.getElementById('my-image'));
    console.log(result.text);
    ```
