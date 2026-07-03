<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Ocr extends Controller
{
    /**
     * Show the upload interface.
     */
    public function index()
    {
        return view('ocr/upload');
    }

    /**
     * Handle file upload, save it locally, and redirect to the client-side viewer page.
     */
    public function upload()
    {
        $file = $this->request->getFile('file');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Silakan pilih file yang valid.');
        }

        // Validate type (Images and PDFs only)
        $mimeType = $file->getMimeType();
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        if (!in_array($mimeType, $allowedMimes)) {
            return redirect()->back()->with('error', 'Hanya file Gambar (JPG/PNG/WEBP) atau PDF yang diperbolehkan.');
        }

        // Ensure public uploads directory exists
        $uploadPath = FCPATH . 'uploads';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Save file with random name to avoid collisions
        $newName = $file->getRandomName();
        if (!$file->move($uploadPath, $newName)) {
            return redirect()->back()->with('error', 'Gagal memindahkan file yang diunggah.');
        }

        $publicUrl = base_url('uploads/' . $newName);
        $isPdf = $mimeType === 'application/pdf';
        $model = $this->request->getPost('model') ?? 'v3';

        // Redirect directly to visualization page where OCR will run client-side in the browser
        return redirect()->to(site_url("ocr/job/{$newName}?file=" . urlencode($publicUrl) . "&pdf=" . ($isPdf ? '1' : '0') . "&model=" . urlencode($model)));
    }

    /**
     * Render the page layout visualization.
     */
    public function job($fileName)
    {
        $fileUrl = $this->request->getGet('file');
        $isPdf = $this->request->getGet('pdf') === '1';
        $model = $this->request->getGet('model') ?? 'v3';

        if (empty($fileUrl)) {
            return redirect()->to(site_url('/'))->with('error', 'File URL tidak ditemukan.');
        }

        return view('ocr/result', [
            'fileName' => $fileName,
            'fileUrl'  => $fileUrl,
            'isPdf'    => $isPdf,
            'model'    => $model
        ]);
    }
}
