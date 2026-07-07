<?php

namespace App\Libraries;

/**
 * XlsxWriter
 * ─────────────────────────────────────────────────────────────────────
 * Membuat file .xlsx asli (Office Open XML) tanpa library eksternal.
 * Hanya membutuhkan ekstensi PHP bawaan: ZipArchive + libzip.
 *
 * CARA PAKAI:
 *   $xlsx = new XlsxWriter();
 *   $sheet = $xlsx->addSheet('Nama Sheet');
 *   $sheet->writeRow(['Kolom A', 'Kolom B'], XlsxWriter::STYLE_HEADER);
 *   $sheet->writeRow(['Data 1',  100],       XlsxWriter::STYLE_NORMAL);
 *   $xlsx->download('nama_file.xlsx');
 *
 * STYLE YANG TERSEDIA:
 *   STYLE_NORMAL         — teks biasa, border tipis
 *   STYLE_HEADER         — background navy, teks putih tebal, tengah
 *   STYLE_STRIPE         — background abu sangat muda
 *   STYLE_GREEN          — teks hijau tebal
 *   STYLE_RED            — teks merah tebal
 *   STYLE_BLUE           — teks biru tebal
 *   STYLE_ORANGE         — teks oranye tebal
 *   STYLE_TITLE          — font besar tebal, warna navy
 *   STYLE_SUBTITLE       — font kecil, warna abu
 *   STYLE_PERCENT        — angka format 0.00%
 *   STYLE_PERCENT_GREEN  — persen + teks hijau tebal
 *   STYLE_PERCENT_RED    — persen + teks merah tebal
 *   STYLE_CENTER         — teks biasa, tengah
 * ─────────────────────────────────────────────────────────────────────
 */
class XlsxWriter
{
    // ── Konstanta style index ────────────────────────────────────────────
    public const STYLE_NORMAL         = 0;
    public const STYLE_HEADER         = 1;
    public const STYLE_STRIPE         = 2;
    public const STYLE_GREEN          = 3;
    public const STYLE_RED            = 4;
    public const STYLE_BLUE           = 5;
    public const STYLE_ORANGE         = 6;
    public const STYLE_TITLE          = 7;
    public const STYLE_SUBTITLE       = 8;
    public const STYLE_PERCENT        = 9;
    public const STYLE_PERCENT_GREEN  = 10;
    public const STYLE_PERCENT_RED    = 11;
    public const STYLE_CENTER         = 12;

    /** @var XlsxSheet[] */
    private array  $sheets        = [];
    private array  $sharedStrings = [];
    private array  $ssIndex       = [];

    // ── Public API ───────────────────────────────────────────────────────

    public function addSheet(string $title): XlsxSheet
    {
        $sheet          = new XlsxSheet($title, $this);
        $this->sheets[] = $sheet;
        return $sheet;
    }

    public function download(string $filename): void
    {
        $content = $this->build();
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: max-age=0, no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $content;
        exit;
    }

    public function save(string $path): void
    {
        file_put_contents($path, $this->build());
    }

    public function addSharedString(string $str): int
    {
        if (isset($this->ssIndex[$str])) {
            return $this->ssIndex[$str];
        }
        $idx                   = count($this->sharedStrings);
        $this->sharedStrings[] = $str;
        $this->ssIndex[$str]   = $idx;
        return $idx;
    }

    // ── Build XLSX ───────────────────────────────────────────────────────

    private function build(): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml',        $this->buildContentTypes());
        $zip->addFromString('_rels/.rels',                $this->buildRootRels());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRels());
        $zip->addFromString('xl/workbook.xml',            $this->buildWorkbook());
        $zip->addFromString('xl/styles.xml',              $this->buildStyles());

        // Render sheets DULU agar shared strings terisi
        $sheetsXml = [];
        foreach ($this->sheets as $i => $sheet) {
            $sheetsXml[$i] = $sheet->render();
        }

        $zip->addFromString('xl/sharedStrings.xml', $this->buildSharedStrings());

        foreach ($this->sheets as $i => $sheet) {
            $zip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $sheetsXml[$i]);
        }

        $zip->close();
        $content = file_get_contents($tmp);
        unlink($tmp);
        return $content;
    }

    // ── XML Builders ─────────────────────────────────────────────────────

    private function buildContentTypes(): string
    {
        $overrides = '';
        for ($i = 1; $i <= count($this->sheets); $i++) {
            $overrides .= '  <Override PartName="/xl/worksheets/sheet' . $i . '.xml"'
                . ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' . "\n";
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' . "\n"
            . '  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' . "\n"
            . '  <Default Extension="xml" ContentType="application/xml"/>' . "\n"
            . '  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' . "\n"
            . '  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' . "\n"
            . '  <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>' . "\n"
            . $overrides
            . '</Types>';
    }

    private function buildRootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n"
            . '  <Relationship Id="rId1"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"'
            . ' Target="xl/workbook.xml"/>' . "\n"
            . '</Relationships>';
    }

    private function buildWorkbookRels(): string
    {
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n"
            . '  <Relationship Id="rId_styles"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"'
            . ' Target="styles.xml"/>' . "\n"
            . '  <Relationship Id="rId_ss"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings"'
            . ' Target="sharedStrings.xml"/>' . "\n";
        foreach ($this->sheets as $i => $sheet) {
            $rels .= '  <Relationship Id="rId' . ($i + 1) . '"'
                . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"'
                . ' Target="worksheets/sheet' . ($i + 1) . '.xml"/>' . "\n";
        }
        return $rels . '</Relationships>';
    }

    private function buildWorkbook(): string
    {
        $sheetsXml = '';
        foreach ($this->sheets as $i => $sheet) {
            $sheetsXml .= '    <sheet name="' . $this->xmlEsc($sheet->getTitle()) . '"'
                . ' sheetId="' . ($i + 1) . '"'
                . ' r:id="rId' . ($i + 1) . '"/>' . "\n";
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n"
            . '  <sheets>' . "\n"
            . $sheetsXml
            . '  </sheets>' . "\n"
            . '</workbook>';
    }

    private function buildSharedStrings(): string
    {
        $count = count($this->sharedStrings);
        $si    = '';
        foreach ($this->sharedStrings as $str) {
            $si .= '  <si><t xml:space="preserve">' . $this->xmlEsc($str) . '</t></si>' . "\n";
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' count="' . $count . '" uniqueCount="' . $count . '">' . "\n"
            . $si
            . '</sst>';
    }

    /**
     * Styles XML
     * INDEX MAP:
     *  0  NORMAL         — border tipis, teks biasa
     *  1  HEADER         — bg navy, teks putih, bold, center, wrap
     *  2  STRIPE         — bg abu muda, border tipis
     *  3  GREEN          — teks hijau bold, center
     *  4  RED            — teks merah bold, center
     *  5  BLUE           — teks biru bold, center
     *  6  ORANGE         — teks oranye bold, center
     *  7  TITLE          — font 14, bold, navy
     *  8  SUBTITLE       — font 9, abu
     *  9  PERCENT        — format 0.00%, center
     * 10  PERCENT_GREEN  — format 0.00%, teks hijau bold
     * 11  PERCENT_RED    — format 0.00%, teks merah bold
     * 12  CENTER         — teks biasa, center
     */
    private function buildStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
. '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
. '<numFmts count="1">'
.   '<numFmt numFmtId="164" formatCode="0.00&quot;%&quot;"/>'
. '</numFmts>'
. '<fonts count="8">'
.   '<font><sz val="10"/><name val="Calibri"/><color rgb="FF1E293B"/></font>'          // 0 normal
.   '<font><sz val="10"/><b/><name val="Calibri"/><color rgb="FFFFFFFF"/></font>'      // 1 putih bold
.   '<font><sz val="10"/><b/><name val="Calibri"/><color rgb="FF16A34A"/></font>'      // 2 hijau bold
.   '<font><sz val="10"/><b/><name val="Calibri"/><color rgb="FFDC2626"/></font>'      // 3 merah bold
.   '<font><sz val="10"/><b/><name val="Calibri"/><color rgb="FF2563EB"/></font>'      // 4 biru bold
.   '<font><sz val="10"/><b/><name val="Calibri"/><color rgb="FFD97706"/></font>'      // 5 oranye bold
.   '<font><sz val="14"/><b/><name val="Calibri"/><color rgb="FF1E3A5F"/></font>'      // 6 navy besar
.   '<font><sz val="9"/><name val="Calibri"/><color rgb="FF64748B"/></font>'           // 7 abu kecil
. '</fonts>'
. '<fills count="4">'
.   '<fill><patternFill patternType="none"/></fill>'
.   '<fill><patternFill patternType="gray125"/></fill>'
.   '<fill><patternFill patternType="solid"><fgColor rgb="FF1E3A5F"/><bgColor indexed="64"/></patternFill></fill>'  // 2 navy
.   '<fill><patternFill patternType="solid"><fgColor rgb="FFF1F5F9"/><bgColor indexed="64"/></patternFill></fill>'  // 3 abu muda
. '</fills>'
. '<borders count="2">'
.   '<border><left/><right/><top/><bottom/><diagonal/></border>'
.   '<border>'
.     '<left style="thin"><color rgb="FFCBD5E1"/></left>'
.     '<right style="thin"><color rgb="FFCBD5E1"/></right>'
.     '<top style="thin"><color rgb="FFCBD5E1"/></top>'
.     '<bottom style="thin"><color rgb="FFCBD5E1"/></bottom>'
.     '<diagonal/>'
.   '</border>'
. '</borders>'
. '<cellStyleXfs count="1">'
.   '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>'
. '</cellStyleXfs>'
. '<cellXfs count="13">'
// 0 NORMAL
.   '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"><alignment vertical="center"/></xf>'
// 1 HEADER
.   '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFill="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
// 2 STRIPE
.   '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1"><alignment vertical="center"/></xf>'
// 3 GREEN
.   '<xf numFmtId="0" fontId="2" fillId="0" borderId="1" xfId="0"><alignment horizontal="center" vertical="center"/></xf>'
// 4 RED
.   '<xf numFmtId="0" fontId="3" fillId="0" borderId="1" xfId="0"><alignment horizontal="center" vertical="center"/></xf>'
// 5 BLUE
.   '<xf numFmtId="0" fontId="4" fillId="0" borderId="1" xfId="0"><alignment horizontal="center" vertical="center"/></xf>'
// 6 ORANGE
.   '<xf numFmtId="0" fontId="5" fillId="0" borderId="1" xfId="0"><alignment horizontal="center" vertical="center"/></xf>'
// 7 TITLE
.   '<xf numFmtId="0" fontId="6" fillId="0" borderId="0" xfId="0"><alignment vertical="center"/></xf>'
// 8 SUBTITLE
.   '<xf numFmtId="0" fontId="7" fillId="0" borderId="0" xfId="0"><alignment vertical="center"/></xf>'
// 9 PERCENT
.   '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0"><alignment horizontal="center" vertical="center"/></xf>'
// 10 PERCENT_GREEN
.   '<xf numFmtId="164" fontId="2" fillId="0" borderId="1" xfId="0"><alignment horizontal="center" vertical="center"/></xf>'
// 11 PERCENT_RED
.   '<xf numFmtId="164" fontId="3" fillId="0" borderId="1" xfId="0"><alignment horizontal="center" vertical="center"/></xf>'
// 12 CENTER
.   '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"><alignment horizontal="center" vertical="center"/></xf>'
. '</cellXfs>'
. '<cellStyles count="1">'
.   '<cellStyle name="Normal" xfId="0" builtinId="0"/>'
. '</cellStyles>'
. '</styleSheet>';
    }

    public function xmlEsc(string $str): string
    {
        return htmlspecialchars($str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}


/**
 * XlsxSheet — satu worksheet di dalam XlsxWriter.
 */
class XlsxSheet
{
    private string     $title;
    private XlsxWriter $writer;
    private array      $rows      = [];
    private array      $colWidths = [];
    private int        $freezeRow = 0;
    private array      $merges    = [];

    public function __construct(string $title, XlsxWriter $writer)
    {
        $this->title  = $title;
        $this->writer = $writer;
    }

    public function getTitle(): string { return $this->title; }

    public function writeRow(array $cells, int $defaultStyle = XlsxWriter::STYLE_NORMAL): self
    {
        $this->rows[] = ['style' => $defaultStyle, 'cells' => $cells];
        return $this;
    }

    public function writeBlankRow(): self
    {
        $this->rows[] = ['style' => XlsxWriter::STYLE_NORMAL, 'cells' => []];
        return $this;
    }

    public function setColWidths(array $colWidths): self
    {
        $this->colWidths = $colWidths;
        return $this;
    }

    public function setFreezeRow(int $rowNum): self
    {
        $this->freezeRow = $rowNum;
        return $this;
    }

    public function addMerge(string $from, string $to): self
    {
        $this->merges[] = $from . ':' . $to;
        return $this;
    }

    public function render(): string
    {
        $colDefsXml   = $this->buildColDefs();
        $sheetDataXml = $this->buildSheetData();
        $mergesXml    = $this->buildMerges();
        $freezeXml    = $this->buildFreezePane();

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . $colDefsXml
            . '<sheetData>'
            . $sheetDataXml
            . '</sheetData>'
            . $mergesXml
            . $freezeXml
            . '</worksheet>';
    }

    private function buildColDefs(): string
    {
        if (empty($this->colWidths)) return '';
        $xml = '<cols>';
        foreach ($this->colWidths as $idx => $width) {
            $col  = $idx + 1;
            $xml .= '<col min="' . $col . '" max="' . $col . '" width="' . $width . '" customWidth="1"/>';
        }
        return $xml . '</cols>';
    }

    private function buildSheetData(): string
    {
        $xml = '';
        foreach ($this->rows as $rowIdx => $row) {
            $rowNum    = $rowIdx + 1;
            $defaultSt = $row['style'];
            $cells     = $row['cells'];

            if (empty($cells)) {
                $xml .= '<row r="' . $rowNum . '"/>';
                continue;
            }

            $xml .= '<row r="' . $rowNum . '" customHeight="1" ht="18">';
            foreach ($cells as $colIdx => $cell) {
                $colLetter = $this->colLetter($colIdx);
                $addr      = $colLetter . $rowNum;

                if (is_array($cell) && isset($cell['v'])) {
                    $val   = $cell['v'];
                    $style = $cell['s'] ?? $defaultSt;
                } else {
                    $val   = $cell;
                    $style = $defaultSt;
                }

                $xml .= $this->buildCell($addr, $val, $style);
            }
            $xml .= '</row>';
        }
        return $xml;
    }

    private function buildCell(string $addr, $val, int $style): string
    {
        if ($val === null || $val === '') {
            return '<c r="' . $addr . '" s="' . $style . '"/>';
        }
        if (is_int($val) || is_float($val)) {
            return '<c r="' . $addr . '" s="' . $style . '" t="n">'
                . '<v>' . $val . '</v>'
                . '</c>';
        }
        $str = (string)$val;
        $idx = $this->writer->addSharedString($str);
        return '<c r="' . $addr . '" s="' . $style . '" t="s">'
            . '<v>' . $idx . '</v>'
            . '</c>';
    }

    private function buildMerges(): string
    {
        if (empty($this->merges)) return '';
        $xml = '<mergeCells count="' . count($this->merges) . '">';
        foreach ($this->merges as $ref) {
            $xml .= '<mergeCell ref="' . $ref . '"/>';
        }
        return $xml . '</mergeCells>';
    }

    private function buildFreezePane(): string
    {
        if ($this->freezeRow <= 0) return '';
        return '<sheetViews>'
            . '<sheetView workbookViewId="0">'
            . '<pane ySplit="' . $this->freezeRow . '" topLeftCell="A' . ($this->freezeRow + 1) . '"'
            . ' activePane="bottomLeft" state="frozen"/>'
            . '</sheetView>'
            . '</sheetViews>';
    }

    private function colLetter(int $idx): string
    {
        $letter = '';
        $idx++;
        while ($idx > 0) {
            $rem    = ($idx - 1) % 26;
            $letter = chr(65 + $rem) . $letter;
            $idx    = (int)(($idx - $rem) / 26);
        }
        return $letter;
    }
}