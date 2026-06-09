<?php
declare(strict_types=1);

namespace App\Lib;

final class SimplePdf
{
    private const PAGE_W = 595;
    private const PAGE_H = 842;

    private string $baseFont = 'Helvetica';
    private int $fontSize = 10;
    private int $leading = 14;
    private int $marginX = 40;
    private int $startY = 800;

    /** @var array<int, array<int, string>> */
    private array $pages = [[]];
    private int $currentY;

    public function __construct()
    {
        $this->currentY = $this->startY;
    }

    public function setFont(string $baseFont, int $fontSize, int $leading): void
    {
        $this->baseFont = $baseFont;
        $this->fontSize = $fontSize;
        $this->leading = $leading;
    }

    public function addLine(string $text): void
    {
        if ($this->currentY < 60) {
            $this->pages[] = [];
            $this->currentY = $this->startY;
        }

        $this->pages[array_key_last($this->pages)][] = $this->sanitize($text);
        $this->currentY -= $this->leading;
    }

    public function output(string $downloadName = 'report.pdf'): void
    {
        $pdf = $this->render();
        header('Content-Type: application/pdf');
        header('Content-Length: ' . (string)strlen($pdf));
        header('Content-Disposition: attachment; filename="' . addslashes($downloadName) . '"');
        echo $pdf;
        exit;
    }

    private function render(): string
    {
        $objects = [];

        // 1: Catalog
        $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";

        // 2: Pages (Kids filled later)
        $objects[] = '';

        // 3: Font
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /" . $this->baseFont . " >>";

        $pageObjectNumbers = [];

        // Start numbering: objects are 1-indexed in output.
        $nextObjNum = 4;
        foreach ($this->pages as $pageLines) {
            $pageObjNum = $nextObjNum++;
            $contentObjNum = $nextObjNum++;
            $pageObjectNumbers[] = $pageObjNum;

            $content = $this->pageContentStream($pageLines);
            $objects[$pageObjNum - 1] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " . self::PAGE_W . " " . self::PAGE_H . "] /Resources << /Font << /F1 3 0 R >> >> /Contents {$contentObjNum} 0 R >>";
            $objects[$contentObjNum - 1] = "<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream";
        }

        $kids = implode(' ', array_map(static fn (int $n): string => $n . ' 0 R', $pageObjectNumbers));
        $objects[1] = "<< /Type /Pages /Kids [ {$kids} ] /Count " . count($pageObjectNumbers) . " >>";

        $out = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $i => $obj) {
            $offsets[] = strlen($out);
            $n = $i + 1;
            $out .= $n . " 0 obj\n" . $obj . "\nendobj\n";
        }

        $xrefPos = strlen($out);
        $out .= "xref\n";
        $out .= "0 " . (count($objects) + 1) . "\n";
        $out .= sprintf("%010d 65535 f \n", 0);
        for ($i = 1; $i <= count($objects); $i++) {
            $out .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $out .= "trailer\n";
        $out .= "<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $out .= "startxref\n{$xrefPos}\n%%EOF";

        return $out;
    }

    /** @param array<int, string> $lines */
    private function pageContentStream(array $lines): string
    {
        $parts = [];
        $parts[] = 'BT';
        $parts[] = '/F1 ' . $this->fontSize . ' Tf';
        $parts[] = $this->leading . ' TL';
        $parts[] = $this->marginX . ' ' . $this->startY . ' Td';

        foreach ($lines as $i => $line) {
            $escaped = $this->pdfEscape($line);
            $parts[] = '(' . $escaped . ') Tj';
            if ($i !== array_key_last($lines)) {
                $parts[] = 'T*';
            }
        }

        $parts[] = 'ET';
        return implode("\n", $parts);
    }

    private function pdfEscape(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);
        $text = str_replace(')', '\\)', $text);
        return $text;
    }

    private function sanitize(string $text): string
    {
        $out = '';
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $ord = ord($text[$i]);
            $out .= ($ord >= 32 && $ord <= 126) ? $text[$i] : '?';
        }
        return $out;
    }
}
