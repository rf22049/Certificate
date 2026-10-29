<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TemplateUploadController extends Controller
{
    public function index(Request $request): View
    {
        $template = $request->session()->get('template');

        if (is_array($template) && (! isset($template['path']) || ! Storage::disk('local')->exists($template['path']))) {
            $request->session()->forget('template');
            $template = null;
        }

        return view('certificate-templates.create', ['template' => $template]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'template' => ['required', 'file', 'mimes:png,jpg,jpeg,pdf', 'max:10240'],
        ]);

        $uploadedFile = $validated['template'];
        $path = $uploadedFile->store('certificate-templates');

        if ($path === false) {
            return back()->withErrors(['template' => 'Template tidak dapat disimpan. Silakan coba lagi.']);
        }

        $previousTemplate = $request->session()->get('template');
        $request->session()->put('template', [
            'path' => $path,
            'name' => $uploadedFile->getClientOriginalName(),
            'mime' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'extension' => strtoupper($uploadedFile->getClientOriginalExtension()),
        ]);

        if (is_array($previousTemplate) && isset($previousTemplate['path'])) {
            Storage::disk('local')->delete($previousTemplate['path']);
        }

        return redirect()->route('home');
    }

    public function preview(Request $request): BinaryFileResponse
    {
        $template = $request->session()->get('template');
        abort_unless(
            is_array($template)
                && isset($template['path'])
                && Storage::disk('local')->exists($template['path']),
            404,
        );

        return response()->file(Storage::disk('local')->path($template['path']), [
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $template = $request->session()->get('template');

        if (is_array($template) && isset($template['path'])) {
            Storage::disk('local')->delete($template['path']);
        }

        $request->session()->forget('template');

        return redirect()->route('home');
    }
}
