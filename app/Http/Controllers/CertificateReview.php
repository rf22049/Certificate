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

        if (
            !is_array($template) ||
            empty($template['path']) ||
            !Storage::disk('local')->exists($template['path'])
        ) {
            return redirect()->route('home');
        }

        if (empty($recipients)) {
            return redirect()->route('recipients.index');
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

        if (
            !is_array($template) ||
            empty($template['path']) ||
            !Storage::disk('local')->exists($template['path'])
        ) {
            abort(404);
        }

        return Storage::disk('local')->response(
            $template['path']
        );
    }
}