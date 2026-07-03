# PaddleOCR Client-Side WASM (CodeIgniter 4 Integration)

Repositori ini berisi aplikasi web berbasis **CodeIgniter 4** yang mengimplementasikan **PaddleOCR secara 100% Client-Side (di dalam Browser)** menggunakan WebAssembly (WASM). 

Dengan arsitektur ini, server PHP Anda tidak perlu memproses AI/OCR. Semua beban komputasi pemindaian gambar/PDF diringankan ke browser client menggunakan ONNX Runtime Web dan OpenCV.js.

---

## 1. Persyaratan Sistem & Instalasi

### Cara Menjalankan Aplikasi Ini
1.  **Clone / Download** repositori ini ke folder server lokal Anda (misal: `D:\CraftThingy\php-ocr-application-test`).
2.  Pastikan PHP >= 8.2 terinstal (disertai ekstensi `intl` dan `mbstring` aktif pada `php.ini`).
3.  Jalankan server pengembangan bawaan CodeIgniter 4:
    ```bash
    php spark serve
    ```
4.  Buka web browser dan akses:
    ```
    http://localhost:8080
    ```

---

## 2. Cara Menggunakan Client-Side OCR di Proyek PHP Lain

Jika Anda memiliki aplikasi PHP lain (Vanilla PHP, Laravel, CodeIgniter 3/4, Symfony, dll.), Anda bisa memindahkan fitur OCR ini dengan langkah berikut:

### Langkah A: Salin Berkas Library & Model AI
Salin folder statis berikut dari proyek ini ke proyek PHP target Anda:
1.  **Pustaka Javascript**: Salin `public/js/paddle-ocr-client.js` ke folder asset publik JavaScript Anda (misal: `public/assets/js/`).
2.  **File Model ONNX**: Salin isi folder `public/models/` ke folder publik proyek Anda (misal: `public/assets/models/`). Folder ini berisi:
    *   `en_PP-OCRv3_det_infer.onnx` (Model Deteksi Teks ~2.4MB)
    *   `en_PP-OCRv3_rec_infer.onnx` (Model Pengenalan Karakter ~8.9MB)
    *   `en_dict.txt` (Kamus Karakter)

### Langkah B: Impor CDN Dependensi pada Halaman HTML/PHP
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

### Langkah C: Panggil Pustaka OCR lewat JavaScript Anda
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

  // 2. Unduh dan Muat Model ONNX secara Asinkron
  await ocr.init({
    detection: '/assets/models/en_PP-OCRv3_det_infer.onnx',
    recognition: '/assets/models/en_PP-OCRv3_rec_infer.onnx',
    charactersDictionary: '/assets/models/en_dict.txt'
  });

  // 3. Jalankan OCR pada element <img> atau <canvas>
  const imgElement = document.getElementById('my-image');
  const result = await ocr.recognize(imgElement);

  console.log("Full Text:", result.text);
  console.log("Lines & Coordinates:", result.lines);
}
```
*Catatan: Parameter `result.lines` mengembalikan koordinat geometris `{ x, y, width, height }` yang siap Anda overlay di atas gambar menggunakan CSS `position: absolute`.*
