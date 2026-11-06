<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TemplateUploadController extends Controller
{
private const BUILT_IN_TEMPLATES = [
        'template-1' => [
            'name' => 'Template 1',
            'path' => 'images/templates/template-1.png',
        ],
        'template-2' => [
            'name' => 'Template 2',
            'path' => 'images/templates/template-2.png',
        ],
        'template-3' => [
            'name' => 'Template 3',
            'path' => 'images/templates/template-3.png',
        ],
        'template-4' => [
            'name' => 'Template 4',
            'path' => 'images/templates/template-4.png',
        ],
        'template-5' => [
            'name' => 'Template 5',
            'path' => 'images/templates/template-5.png',
        ],
    ];

    public function index(Request $request): View
    {
        $template = $request->session()->get('template');

        if (
            is_array($template)
            && ($template['source'] ?? 'upload') === 'builtin'
        ) {
            $builtinPath = $template['path'] ?? '';

            if (
                ! array_key_exists(
                    $template['id'] ?? '',
                    self::BUILT_IN_TEMPLATES
                )
                || ! is_file(public_path($builtinPath))
            ) {
                $request->session()->forget('template');
                $template = null;
            }
        } elseif (
            is_array($template)
            && (
                ! isset($template['path'])
                || ! Storage::disk('local')->exists($template['path'])
            )
        ) {
            $request->session()->forget('template');
            $template = null;
        }

        return view('certificate-templates.create', [
            'template' => $template,
            'builtinTemplates' => self::BUILT_IN_TEMPLATES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'template' => [
                'required',
                'file',
                'mimes:png,jpg,jpeg,pdf',
                'max:10240',
            ],
        ]);

        $uploadedFile = $validated['template'];
        $path = $uploadedFile->store('certificate-templates');

        if ($path === false) {
            return back()->withErrors([
                'template' => 'Template tidak dapat disimpan. Silakan coba lagi.',
            ]);
        }

        $previousTemplate = $request->session()->get('template');

        $request->session()->put('template', [
            'source' => 'upload',
            'path' => $path,
            'name' => $uploadedFile->getClientOriginalName(),
            'mime' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'extension' => strtoupper(
                $uploadedFile->getClientOriginalExtension()
            ),
        ]);

        if (
            is_array($previousTemplate)
            && ($previousTemplate['source'] ?? 'upload') === 'upload'
            && isset($previousTemplate['path'])
        ) {
            Storage::disk('local')->delete($previousTemplate['path']);
        }

        return redirect()->route('home');
    }

    public function select(string $id, Request $request): RedirectResponse
    {
        if (! array_key_exists($id, self::BUILT_IN_TEMPLATES)) {
            abort(404);
        }

        $selected = self::BUILT_IN_TEMPLATES[$id];

        if (! is_file(public_path($selected['path']))) {
            abort(404, 'File template tidak ditemukan.');
        }

        $previousTemplate = $request->session()->get('template');

        $request->session()->put('template', [
            'source' => 'builtin',
            'id' => $id,
            'path' => $selected['path'],
            'name' => $selected['name'],
            'mime' => 'image/jpeg',
            'size' => filesize(public_path($selected['path'])),
            'extension' => 'JPEG',
        ]);

        if (
            is_array($previousTemplate)
            && ($previousTemplate['source'] ?? 'upload') === 'upload'
            && isset($previousTemplate['path'])
        ) {
            Storage::disk('local')->delete($previousTemplate['path']);
        }

        return redirect()->route('home');
    }

    public function preview(Request $request)
    {
        $template = $request->session()->get('template');

        abort_unless(
            is_array($template) && isset($template['path']),
            404
        );

        $headers = [
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        if (($template['source'] ?? 'upload') === 'builtin') {
            abort_unless(
                is_file(public_path($template['path'])),
                404
            );

            return response()->file(
                public_path($template['path']),
                $headers
            );
        }

        abort_unless(
            Storage::disk('local')->exists($template['path']),
            404
        );

        return response()->file(
            Storage::disk('local')->path($template['path']),
            $headers
        );
    }

    public function destroy(Request $request): RedirectResponse
    {
        $template = $request->session()->get('template');

        if (
            is_array($template)
            && ($template['source'] ?? 'upload') === 'upload'
            && isset($template['path'])
        ) {
            Storage::disk('local')->delete($template['path']);
        }

        $request->session()->forget('template');

        return redirect()->route('home');
    }
}