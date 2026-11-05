<?php

namespace App\Services;

use InvalidArgumentException;
use setasign\Fpdi\Tfpdf\Fpdi;

class CertificatePdfGenerator
{
    private const FONT_FILES = [
        'DejaVuSans' => [
            '' => 'DejaVuSans.ttf',
            'B' => 'DejaVuSans-Bold.ttf',
            'I' => 'DejaVuSans-Oblique.ttf',
            'BI' => 'DejaVuSans-BoldOblique.ttf',
        ],
        'DejaVuSerif' => [
            '' => 'DejaVuSerif.ttf',
            'B' => 'DejaVuSerif-Bold.ttf',
            'I' => 'DejaVuSerif-Italic.ttf',
            'BI' => 'DejaVuSerif-BoldItalic.ttf',
        ],
        'DejaVuSansCondensed' => [
            '' => 'DejaVuSansCondensed.ttf',
            'B' => 'DejaVuSansCondensed-Bold.ttf',
            'I' => 'DejaVuSansCondensed-Oblique.ttf',
            'BI' => 'DejaVuSansCondensed-BoldOblique.ttf',
        ],
        'DejaVuSansMono' => [
            '' => 'DejaVuSansMono.ttf',
            'B' => 'DejaVuSansMono-Bold.ttf',
            'I' => 'DejaVuSansMono-Oblique.ttf',
            'BI' => 'DejaVuSansMono-BoldOblique.ttf',
        ],
    ];

    /**
     * @param  array{path: string, mime: string}  $template
     * @param  list<array{name: string, note?: string}>  $recipients
     * @param  array{year: int, month: int, start_number: int}  $numberSettings
     * @param  array{global?: array, individual?: array}  $editSettings
     */
    public function generateAll(
        array $template,
        array $recipients,
        array $numberSettings,
        array $editSettings,
    ): string {
        return $this->generate($template, $recipients, $numberSettings, $editSettings, array_keys($recipients));
    }

    /**
     * @param  array{path: string, mime: string}  $template
     * @param  array{name: string, note?: string}  $recipient
     * @param  array{year: int, month: int, start_number: int}  $numberSettings
     * @param  array{global?: array, individual?: array}  $editSettings
     */
    public function generateOne(
        array $template,
        array $recipient,
        int $recipientIndex,
        array $numberSettings,
        array $editSettings,
    ): string {
        return $this->generate(
            $template,
            [$recipient],
            $numberSettings,
            $this->settingsForRecipient($editSettings, $recipientIndex),
            [$recipientIndex],
        );
    }

    /**
     * @param  array{path: string, mime: string}  $template
     * @param  list<array{name: string, note?: string}>  $recipients
     * @param  array{year: int, month: int, start_number: int}  $numberSettings
     * @param  array{global?: array, individual?: array}  $editSettings
     * @param  list<int>  $recipientIndexes
     */
    private function generate(
        array $template,
        array $recipients,
        array $numberSettings,
        array $editSettings,
        array $recipientIndexes,
    ): string {
        $pdf = new Fpdi('P', 'pt');
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetTitle('ProCert - Sertifikat', true);
        $this->registerFonts($pdf);

        $templatePath = $template['path'];
        $mime = mime_content_type($templatePath);
        $sourcePage = null;

        if ($mime === 'application/pdf') {
            if ($pdf->setSourceFile($templatePath) < 1) {
                throw new InvalidArgumentException('Template PDF tidak memiliki halaman.');
            }

            $sourcePage = $pdf->importPage(1);
            $pageSize = $pdf->getTemplateSize($sourcePage);
            $pageWidth = (float) $pageSize['width'];
            $pageHeight = (float) $pageSize['height'];
        } elseif (in_array($mime, ['image/jpeg', 'image/png'], true)) {
            $imageSize = getimagesize($templatePath);

            if ($imageSize === false) {
                throw new InvalidArgumentException('Ukuran gambar template tidak dapat dibaca.');
            }

            $scale = 842 / max($imageSize[0], $imageSize[1]);
            $pageWidth = $imageSize[0] * $scale;
            $pageHeight = $imageSize[1] * $scale;
        } else {
            throw new InvalidArgumentException('Format template tidak didukung untuk ekspor.');
        }

        $orientation = $pageWidth > $pageHeight ? 'L' : 'P';

        foreach ($recipients as $recipientOffset => $recipient) {
            $recipientIndex = $recipientIndexes[$recipientOffset];
            $pdf->AddPage($orientation, [$pageWidth, $pageHeight]);

            if ($sourcePage !== null) {
                $pdf->useTemplate($sourcePage, 0, 0, $pageWidth, $pageHeight);
            } else {
                $pdf->Image($templatePath, 0, 0, $pageWidth, $pageHeight);
            }

            $sequence = $numberSettings['start_number'] + $recipientIndex;
            $certificateNumber = implode('/', [
                str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                'UP-DKN2.2',
                'SERTIFIKAT',
                'PP05',
                $this->romanMonth($numberSettings['month']),
                $numberSettings['year'],
            ]);

            $this->renderText(
                $pdf,
                $this->styleFor($editSettings, $recipientIndex, 'name'),
                $recipient['name'],
                $pageWidth,
                $pageHeight,
            );
            $this->renderText(
                $pdf,
                $this->styleFor($editSettings, $recipientIndex, 'number'),
                $certificateNumber,
                $pageWidth,
                $pageHeight,
            );

            $textBoxes = ($editSettings['syncTextBoxes'] ?? false)
                ? ($editSettings['global']['textBoxes'] ?? [])
                : ($editSettings['individual'][$recipientIndex]['textBoxes'] ?? null);
            $legacyTextBox = $editSettings['global']['customText'] ?? null;

            if (! is_array($textBoxes)) {
                $textBoxes = $editSettings['global']['textBoxes'] ?? [];
            }

            if ($textBoxes === [] && is_array($legacyTextBox)) {
                $textBoxes = [$legacyTextBox];
            }

            foreach ($textBoxes as $textBox) {
                if (! is_array($textBox) || ! is_string($textBox['content'] ?? null) || trim($textBox['content']) === '') {
                    continue;
                }

                $this->renderText(
                    $pdf,
                    array_merge($this->styleFor($editSettings, $recipientIndex, 'textBox'), $textBox),
                    $textBox['content'],
                    $pageWidth,
                    $pageHeight,
                );
            }
        }

        return $pdf->Output('S');
    }

    private function registerFonts(Fpdi $pdf): void
    {
        foreach (self::FONT_FILES as $family => $styles) {
            foreach ($styles as $style => $file) {
                $pdf->AddFont($family, $style, $file, true);
            }
        }
    }

    /**
     * @param  array{font_family?: string, font_size?: int|float, color?: string, alignment?: string, bold?: bool, italic?: bool, underline?: bool, x?: int|float, y?: int|float}  $style
     */
    private function renderText(Fpdi $pdf, array $style, string $text, float $pageWidth, float $pageHeight): void
    {
        $family = $this->fontFamily((string) ($style['font_family'] ?? 'Manrope'));
        $fontStyle = ($style['bold'] ?? false ? 'B' : '').($style['italic'] ?? false ? 'I' : '');
        $fontSize = max(6, min(112.5, (float) ($style['font_size'] ?? 24) * 0.75));

        if ($style['underline'] ?? false) {
            $fontStyle .= 'U';
        }

        $pdf->SetFont($family, $fontStyle, $fontSize);

        $color = (string) ($style['color'] ?? '#4042A4');

        if (preg_match('/^#([0-9A-Fa-f]{6})$/', $color, $matches) === 1) {
            $pdf->SetTextColor(
                hexdec(substr($matches[1], 0, 2)),
                hexdec(substr($matches[1], 2, 2)),
                hexdec(substr($matches[1], 4, 2)),
            );
        }

        $boxWidth = $pageWidth * 0.9;
        $lineHeight = $fontSize * 1.25;
        $lines = $this->wrapText($pdf, $text, $boxWidth);
        $boxHeight = count($lines) * $lineHeight;
        $centerX = max(0, min($pageWidth, (float) ($style['x'] ?? 50) * $pageWidth / 100));
        $centerY = max(0, min($pageHeight, (float) ($style['y'] ?? 50) * $pageHeight / 100));
        $left = max(0, min($pageWidth - $boxWidth, $centerX - $boxWidth / 2));
        $top = max(0, min($pageHeight - $boxHeight, $centerY - $boxHeight / 2));
        $alignment = match ($style['alignment'] ?? 'center') {
            'left' => 'L',
            'right' => 'R',
            'justify' => 'J',
            default => 'C',
        };

        $pdf->SetXY($left, $top);
        $pdf->MultiCell($boxWidth, $lineHeight, implode("\n", $lines), 0, $alignment);
    }

    private function wrapText(Fpdi $pdf, string $text, float $maxWidth): array
    {
        $lines = [];

        foreach (preg_split('/\R/u', $text) ?: [$text] as $paragraph) {
            $line = '';
            $words = preg_split('/\s+/u', trim($paragraph), -1, PREG_SPLIT_NO_EMPTY) ?: [];

            foreach ($words as $word) {
                $candidate = $line === '' ? $word : $line.' '.$word;

                if ($pdf->GetStringWidth($candidate) <= $maxWidth) {
                    $line = $candidate;

                    continue;
                }

                if ($line !== '') {
                    $lines[] = $line;
                    $line = '';
                }

                foreach (mb_str_split($word) as $character) {
                    $candidate = $line.$character;

                    if ($line !== '' && $pdf->GetStringWidth($candidate) > $maxWidth) {
                        $lines[] = $line;
                        $line = $character;
                    } else {
                        $line = $candidate;
                    }
                }
            }

            if ($line !== '' || $words === []) {
                $lines[] = $line;
            }
        }

        return $lines === [] ? [''] : $lines;
    }

    private function fontFamily(string $fontFamily): string
    {
        return match ($fontFamily) {
            'Georgia', 'Times New Roman', 'Playfair Display', 'Merriweather', 'Lora', 'Roboto Slab' => 'DejaVuSerif',
            'Oswald' => 'DejaVuSansCondensed',
            'Courier' => 'DejaVuSansMono',
            default => 'DejaVuSans',
        };
    }

    private function romanMonth(int $month): string
    {
        return [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ][$month] ?? throw new InvalidArgumentException('Bulan sertifikat tidak valid.');
    }

    /**
     * @param  array{global?: array, individual?: array}  $editSettings
     * @return array{global?: array, individual?: array}
     */
    private function settingsForRecipient(array $editSettings, int $recipientIndex): array
    {
        return [
            'global' => $editSettings['global'] ?? [],
            'syncTextBoxes' => $editSettings['syncTextBoxes'] ?? false,
            'individual' => [
                $recipientIndex => $editSettings['individual'][$recipientIndex] ?? [],
            ],
        ];
    }

    /**
     * @param  array{global?: array, individual?: array}  $editSettings
     * @return array{font_family: string, font_size: int|float, color: string, alignment: string, bold: bool, italic: bool, underline: bool, x: int|float, y: int|float}
     */
    private function styleFor(array $editSettings, int $recipientIndex, string $element): array
    {
        $defaultStyle = [
            'font_family' => 'Manrope',
            'font_size' => $element === 'number' ? 14 : 24,
            'color' => '#4042A4',
            'alignment' => 'center',
            'bold' => $element === 'name',
            'italic' => false,
            'underline' => false,
            'x' => 50,
            'y' => match ($element) {
                'name' => 50,
                'number' => 70,
                default => 80,
            },
        ];

        $globalStyle = $editSettings['global'][$element] ?? [];
        $individualStyle = $editSettings['individual'][$recipientIndex][$element] ?? [];

        return array_merge(
            $defaultStyle,
            is_array($globalStyle) ? $globalStyle : [],
            is_array($individualStyle) ? $individualStyle : [],
        );
    }
}
