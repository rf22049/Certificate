<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

class CertificateReview extends Controller
{
public function index(Request $request): View|RedirectResponse
    {
        $template = $request->session()->get('template');
        $recipients = $request->session()->get('recipients', []);
        $numberSettings = $request->session()->get(
            'certificate_number_settings'
        );
        $editSettings = $request->session()->get(
            'certificate_edit_settings',
            []
        );

        // CEK TEMPLATE
        if (!is_array($template) || empty($template['path'])) {
            return redirect()->route('home');
        }

        // CEK TEMPLATE BAWAAN ATAU TEMPLATE UPLOAD
        if (($template['source'] ?? 'upload') === 'builtin') {
            $templateExists = is_file(public_path($template['path']));
        } else {
            $templateExists = Storage::disk('local')->exists($template['path']);
        }

        if (!$templateExists) {
            $request->session()->forget('template');

            return redirect()->route('home')
                ->with('status', 'File template tidak ditemukan. Silakan pilih template kembali.');
        }

        // CEK DATA PENERIMA
        if (empty($recipients)) {
            return redirect()->route('recipients.index');
        }

        // CEK PENGATURAN NOMOR
        if (!is_array($numberSettings)) {
            return redirect()->route('certificate-number.index');
        }

        return view('certificate.step5', compact(
            'template',
            'recipients',
            'numberSettings',
            'editSettings'
        ));
    }

    public function previewTemplate(Request $request)
    {
        $template = $request->session()->get('template');

        if (!is_array($template) || empty($template['path'])) {
            abort(404);
        }

        // PREVIEW TEMPLATE BAWAAN
        if (($template['source'] ?? 'upload') === 'builtin') {
            $path = public_path($template['path']);

            if (!is_file($path)) {
                abort(404);
            }

            return response()->file($path);
        }

        // PREVIEW TEMPLATE UPLOAD
        if (!Storage::disk('local')->exists($template['path'])) {
            abort(404);
        }

        return Storage::disk('local')->response($template['path']);
    }
}