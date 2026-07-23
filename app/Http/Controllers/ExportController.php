<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ExportController extends Controller
{
    // ── Design tokens ─────────────────────────────────────────────────────────
    private const COLOR_HEADER_BG    = 'FF1E40AF'; // deep blue header
    private const COLOR_HEADER_FG    = 'FFFFFFFF'; // white text
    private const COLOR_META_BG      = 'FFE0E7FF'; // light indigo metadata band
    private const COLOR_META_FG      = 'FF1E3A5F'; // dark navy metadata text
    private const COLOR_ROW_ODD      = 'FFFFFFFF'; // white
    private const COLOR_ROW_EVEN     = 'FFF0F4FF'; // very light blue
    private const COLOR_EXPIRED      = 'FFFEE2E2'; // light red  – expired deadline
    private const COLOR_EXPIRING     = 'FFFEF9C3'; // light yellow – expiring ≤ 30 days
    private const COLOR_BORDER       = 'FFCBD5E1'; // soft slate border
    private const COLOR_TOTAL_BG     = 'FFE0E7FF'; // totals row background
    private const COLOR_TOTAL_FG     = 'FF1E3A5F'; // totals row text

    /**
     * Export filtered documents as a ZIP archive.
     *
     * ZIP structure:
     *   DTS-Export-YYYY-MM-DD.zip
     *     └── DTS-Export-YYYY-MM-DD/
     *           ├── {year}/
     *           │     ├── Excel/
     *           │     │    └── DTS-{year}.xlsx   (hyperlinks in Title col → ../PDF/file.pdf)
     *           │     └── PDF/
     *           │          ├── DOC-001-title.pdf
     *           │          └── ...
     *           └── {year}/
     *                 ├── Excel/ ...
     *                 └── PDF/  ...
     */
    public function exportCsv(Request $request)
    {
        $query = Document::with('uploader')->latest();

        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        $sortable = ['reference_number', 'date_issued'];
        $sort     = in_array($request->sort, $sortable) ? $request->sort : null;
        $dir      = $request->dir === 'asc' ? 'asc' : 'desc';
        if ($sort) {
            $query->reorder()->orderBy($sort, $dir);
        }

        $documents = $query->get();

        // ── Group by year, newest first ───────────────────────────────────────
        $byYear = $documents
            ->sortByDesc(fn ($d) => $d->date_issued?->timestamp ?? 0)
            ->groupBy(fn ($d) => $d->date_issued?->year ?? 'Unknown')
            ->sortKeysDesc();

        // Filter summary for metadata block
        $filterParts = [];
        if ($request->filled('search'))        $filterParts[] = 'Search: "' . $request->search . '"';
        if ($request->filled('document_type')) $filterParts[] = 'Type: ' . ucfirst($request->document_type);
        $filterSummary = $filterParts ? implode(' | ', $filterParts) : 'None';

        $headers = [
            'A' => 'Reference Number',
            'B' => 'Document Type',
            'C' => 'Title',            // ← hyperlink to PDF
            'D' => 'Date Received',
            'E' => 'Deadline',
            'F' => 'Office / Origin',
            'G' => 'Recipient',
            'H' => 'Uploaded By',
            'I' => 'Created At',
        ];
        $lastCol = 'I';
        $today   = now()->startOfDay();

        // ── Create a temp ZIP file ─────────────────────────────────────────────
        $zipFilename = 'DTS-Export-' . now()->format('Y-m-d');   // used as both root folder and zip name
        $zipTmpPath  = tempnam(sys_get_temp_dir(), 'dts_export_');
        $zip         = new \ZipArchive();
        $zip->open($zipTmpPath, \ZipArchive::OVERWRITE);

        foreach ($byYear as $year => $docs) {
            // ── Build the XLSX for this year ──────────────────────────────────
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->setTitle((string) $year);

            // Metadata block (rows 1–4)
            $sheet->mergeCells("A1:{$lastCol}1");
            $sheet->setCellValue('A1', 'Document Tracking System — Export Report');
            $sheet->getStyle('A1')->applyFromArray([
                'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => self::COLOR_META_FG]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_META_BG]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(22);

            $sheet->mergeCells('A2:D2');
            $sheet->mergeCells("E2:{$lastCol}2");
            $sheet->setCellValue('A2', "Year: {$year}");
            $sheet->setCellValue('E2', 'Exported: ' . now()->format('F j, Y  g:i A'));
            $sheet->getStyle('A2:D2')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['argb' => self::COLOR_META_FG]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_META_BG]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
            ]);
            $sheet->getStyle("E2:{$lastCol}2")->applyFromArray([
                'font'      => ['color' => ['argb' => self::COLOR_META_FG]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_META_BG]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'indent' => 1],
            ]);

            $sheet->mergeCells("A3:{$lastCol}3");
            $sheet->setCellValue('A3', "Filters Applied: {$filterSummary}");
            $sheet->getStyle('A3')->applyFromArray([
                'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => self::COLOR_META_FG]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_META_BG]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
            ]);

            $sheet->mergeCells("A4:{$lastCol}4");
            $sheet->setCellValue('A4', '  🔴 Expired deadline    🟡 Expiring within 30 days    🔗 Click Title to open PDF');
            $sheet->getStyle('A4')->applyFromArray([
                'font'      => ['size' => 9, 'italic' => true, 'color' => ['argb' => self::COLOR_META_FG]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_META_BG]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
            ]);

            // Column headers (row 5)
            $headerRow    = 5;
            $dataStartRow = 6;

            foreach ($headers as $col => $label) {
                $sheet->setCellValue("{$col}{$headerRow}", $label);
            }
            $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['argb' => self::COLOR_HEADER_FG], 'size' => 10],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_HEADER_BG]]],
            ]);
            $sheet->getRowDimension($headerRow)->setRowHeight(18);

            // Data rows
            $row = $dataStartRow;
            foreach ($docs as $doc) {
                $isEven    = (($row - $dataStartRow) % 2 === 1);
                $rowBg     = $isEven ? self::COLOR_ROW_EVEN : self::COLOR_ROW_ODD;
                $deadlineBg = $rowBg;

                if ($doc->expiration_date) {
                    if ($doc->expiration_date->lt($today)) {
                        $deadlineBg = self::COLOR_EXPIRED;
                    } elseif ($doc->expiration_date->diffInDays($today) <= 30) {
                        $deadlineBg = self::COLOR_EXPIRING;
                    }
                }

                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rowBg);
                $sheet->getStyle("E{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($deadlineBg);
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDER);

                // ── Build a safe PDF filename (same logic used when adding to ZIP) ──
                $pdfFilename = $this->safePdfFilename($doc);

                // Reference Number cell — plain text
                $sheet->setCellValue("A{$row}", $doc->reference_number);

                // Document Type
                $sheet->setCellValue("B{$row}", $doc->document_type_label);

                // Title cell — hyperlink to ../PDF/{filename} (relative path from Excel/ folder)
                $sheet->setCellValue("C{$row}", $doc->title);
                if ($doc->pdf_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($doc->pdf_path)) {
                    $sheet->getCell("C{$row}")
                        ->getHyperlink()
                        ->setUrl('../PDF/' . $pdfFilename)
                        ->setTooltip('Click to open PDF');
                    $sheet->getStyle("C{$row}")->applyFromArray([
                        'font' => [
                            'color'     => ['argb' => 'FF1D4ED8'],
                            'underline' => \PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE,
                        ],
                    ]);
                }

                $this->setDate($sheet, "D{$row}", $doc->date_issued);
                $this->setDate($sheet, "E{$row}", $doc->expiration_date);
                $sheet->setCellValue("F{$row}", $doc->received_from);
                $sheet->setCellValue("G{$row}", $doc->recipient);
                $sheet->setCellValue("H{$row}", $doc->uploader?->name ?? 'System');
                $this->setDatetime($sheet, "I{$row}", $doc->created_at);

                foreach (['A', 'B', 'D', 'E', 'I'] as $c) {
                    $sheet->getStyle("{$c}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                foreach (['C', 'F', 'G', 'H'] as $c) {
                    $sheet->getStyle("{$c}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                $row++;
            }

            // Totals row
            $totalRow = $row;
            $docCount = count($docs);
            $sheet->mergeCells("A{$totalRow}:H{$totalRow}");
            $sheet->setCellValue("A{$totalRow}", "Total Documents: {$docCount}");
            $sheet->setCellValue("I{$totalRow}", '');
            $sheet->getStyle("A{$totalRow}:{$lastCol}{$totalRow}")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['argb' => self::COLOR_TOTAL_FG]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_TOTAL_BG]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
                'borders'   => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => self::COLOR_BORDER]]],
            ]);

            foreach (array_keys($headers) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            $sheet->freezePane("A{$dataStartRow}");

            // ── Save XLSX to a temp file and add to ZIP ───────────────────────
            // Entry path: {rootFolder}/{year}/Excel/DTS-{year}.xlsx
            $xlsTmp = tempnam(sys_get_temp_dir(), 'dts_xlsx_');
            (new Xlsx($spreadsheet))->save($xlsTmp);
            $zip->addFile($xlsTmp, "{$zipFilename}/{$year}/Excel/DTS-{$year}.xlsx");

            // ── Add PDFs for this year into {rootFolder}/{year}/PDF/ ─────────
            foreach ($docs as $doc) {
                if (! $doc->pdf_path) continue;
                $diskPath = \Illuminate\Support\Facades\Storage::disk('local')->path($doc->pdf_path);
                if (! file_exists($diskPath)) continue;

                $zip->addFile($diskPath, "{$zipFilename}/{$year}/PDF/" . $this->safePdfFilename($doc));
            }

            // Track temp files to clean up after ZIP is closed
            $tmpFiles[] = $xlsTmp;
        }

        $zip->close();

        // Clean up spreadsheet temp files
        foreach ($tmpFiles ?? [] as $tmp) {
            @unlink($tmp);
        }

        // ── Stream ZIP response ───────────────────────────────────────────────
        $downloadName = $zipFilename . '.zip';   // e.g. DTS-Export-2026-07-23.zip

        return response()->streamDownload(function () use ($zipTmpPath) {
            $handle = fopen($zipTmpPath, 'rb');
            while (! feof($handle)) {
                echo fread($handle, 8192);
                ob_flush();
                flush();
            }
            fclose($handle);
            @unlink($zipTmpPath);
        }, $downloadName, [
            'Content-Type'        => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Export a single document's full details as a ZIP archive.
     *
     * ZIP structure:
     *   DTS-{ref}-{title}-YYYY-MM-DD.zip
     *     └── DTS-{ref}-{title}-YYYY-MM-DD/
     *           ├── DTS-{ref}-{title}.xlsx   (Title cell hyperlinks to the PDF)
     *           └── DTS-{ref}-{title}.pdf
     *
     * Sheet 1: Document Details
     * Sheet 2: Activity Log
     */
    public function exportSingleCsv(Document $Document)
    {
        $Document->load(['uploader', 'updater', 'activityLogs.user']);

        $spreadsheet = new Spreadsheet();
        $today       = now()->startOfDay();
        $exportedAt  = now()->format('F j, Y  g:i A');
        $pdfFilename = $this->safePdfFilename($Document);

        // =====================================================================
        // SHEET 1 — Document Details
        // =====================================================================
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Document Details');

        // ── Metadata banner ───────────────────────────────────────────────────
        $sheet->mergeCells('A1:B1');
        $sheet->setCellValue('A1', 'Document Tracking System — Document Detail Report');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => self::COLOR_META_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_META_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        $sheet->mergeCells('A2:B2');
        $sheet->setCellValue('A2', 'Exported: ' . $exportedAt);
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => self::COLOR_META_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_META_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'indent' => 1],
        ]);

        // ── Section header ────────────────────────────────────────────────────
        $sheet->mergeCells('A3:B3');
        $sheet->setCellValue('A3', 'DOCUMENT DETAILS');
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(18);

        // ── Column headers ────────────────────────────────────────────────────
        $sheet->setCellValue('A4', 'Field');
        $sheet->setCellValue('B4', 'Value');
        $sheet->getStyle('A4:B4')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_HEADER_BG]],
            ],
        ]);

        // ── Detail rows ───────────────────────────────────────────────────────
        $details = [
            ['Reference Number', $Document->reference_number],
            ['Document Type',    $Document->document_type_label],
            ['Title',            $Document->title],   // hyperlink added below
            ['Date Received',    null],   // filled via setDate below
            ['Deadline',         null],   // filled via setDate below
            ['Office / Origin',  $Document->received_from ?? 'N/A'],
            ['Recipient',        $Document->recipient ?? 'N/A'],
            ['Uploaded By',      $Document->uploader?->name ?? 'System'],
            ['Last Updated By',  $Document->updater?->name ?? 'N/A'],
            ['Created At',       null],   // filled via setDatetime below
            ['Updated At',       null],   // filled via setDatetime below
        ];

        $detailStartRow = 5;
        $rowIdx         = $detailStartRow;

        foreach ($details as $i => $pair) {
            $rowBg = ($i % 2 === 1) ? self::COLOR_ROW_EVEN : self::COLOR_ROW_ODD;

            $sheet->setCellValue("A{$rowIdx}", $pair[0]);
            if ($pair[1] !== null) {
                $sheet->setCellValue("B{$rowIdx}", $pair[1]);
            }
            $sheet->getStyle("A{$rowIdx}:B{$rowIdx}")->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $rowBg]],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);
            $sheet->getStyle("A{$rowIdx}")->getFont()->setBold(true);
            $rowIdx++;
        }

        // Fill date/datetime cells
        $dateReceivedRow = $detailStartRow + 3;
        $deadlineRow     = $detailStartRow + 4;
        $createdAtRow    = $detailStartRow + 9;
        $updatedAtRow    = $detailStartRow + 10;

        // Title row (index 2) — hyperlink to the PDF sitting beside the XLSX in the ZIP
        $titleRow = $detailStartRow + 2;
        if ($Document->pdf_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($Document->pdf_path)) {
            $sheet->getCell("B{$titleRow}")
                ->getHyperlink()
                ->setUrl($pdfFilename)        // same folder in ZIP, so just the filename
                ->setTooltip('Click to open PDF');
            $sheet->getStyle("B{$titleRow}")->applyFromArray([
                'font' => [
                    'color'     => ['argb' => 'FF1D4ED8'],
                    'underline' => \PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE,
                ],
            ]);
        }

        $this->setDate($sheet,     "B{$dateReceivedRow}", $Document->date_issued);
        $this->setDate($sheet,     "B{$deadlineRow}",     $Document->expiration_date, 'N/A');
        $this->setDatetime($sheet, "B{$createdAtRow}",    $Document->created_at);
        $this->setDatetime($sheet, "B{$updatedAtRow}",    $Document->updated_at);

        // Deadline colour highlight
        if ($Document->expiration_date) {
            if ($Document->expiration_date->lt($today)) {
                $deadlineCellBg = self::COLOR_EXPIRED;
            } elseif ($Document->expiration_date->diffInDays($today) <= 30) {
                $deadlineCellBg = self::COLOR_EXPIRING;
            } else {
                $deadlineCellBg = null;
            }
            if ($deadlineCellBg) {
                $sheet->getStyle("B{$deadlineRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB($deadlineCellBg);
            }
        }

        foreach ([$dateReceivedRow, $deadlineRow, $createdAtRow, $updatedAtRow] as $r) {
            $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        foreach (['A', 'B'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A5');

        // =====================================================================
        // SHEET 2 — Activity Log
        // =====================================================================
        $logSheet = $spreadsheet->createSheet();
        $logSheet->setTitle('Activity Log');

        // ── Metadata banner ───────────────────────────────────────────────────
        $logSheet->mergeCells('A1:E1');
        $logSheet->setCellValue('A1', 'Document Tracking System — Activity Log');
        $logSheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => self::COLOR_META_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_META_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $logSheet->getRowDimension(1)->setRowHeight(22);

        // Row 2: document reference + export date
        $logSheet->mergeCells('A2:B2');
        $logSheet->mergeCells('C2:E2');
        $logSheet->setCellValue('A2', 'Document: ' . $Document->reference_number . ' — ' . $Document->title);
        $logSheet->setCellValue('C2', 'Exported: ' . $exportedAt);
        $logSheet->getStyle('A2:B2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => self::COLOR_META_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_META_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
        ]);
        $logSheet->getStyle('C2:E2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => self::COLOR_META_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_META_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'indent' => 1],
        ]);

        // ── Column headers (row 3) ────────────────────────────────────────────
        $logHeaders = ['Action', 'Performed By', 'Notes', 'IP Address', 'Date/Time'];
        foreach ($logHeaders as $ci => $lh) {
            $col = chr(ord('A') + $ci);
            $logSheet->setCellValue("{$col}3", $lh);
        }
        $logSheet->getStyle('A3:E3')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_HEADER_BG]]],
        ]);
        $logSheet->getRowDimension(3)->setRowHeight(18);

        // ── Log data rows ─────────────────────────────────────────────────────
        $logRow = 4;
        foreach ($Document->activityLogs as $li => $log) {
            $rowBg = ($li % 2 === 1) ? self::COLOR_ROW_EVEN : self::COLOR_ROW_ODD;

            $logSheet->setCellValue("A{$logRow}", $log->action_label);
            $logSheet->setCellValue("B{$logRow}", $log->user?->name ?? 'System');
            $logSheet->setCellValue("C{$logRow}", $log->notes ?? '');
            $logSheet->setCellValue("D{$logRow}", $log->ip_address ?? '');
            $this->setDatetime($logSheet, "E{$logRow}", $log->created_at);

            $logSheet->getStyle("A{$logRow}:E{$logRow}")->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $rowBg]],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);
            $logSheet->getStyle("E{$logRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $logRow++;
        }

        // ── Totals row ────────────────────────────────────────────────────────
        $logCount = count($Document->activityLogs);
        $logSheet->mergeCells("A{$logRow}:E{$logRow}");
        $logSheet->setCellValue("A{$logRow}", "Total Entries: {$logCount}");
        $logSheet->getStyle("A{$logRow}:E{$logRow}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => self::COLOR_TOTAL_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_TOTAL_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
            'borders'   => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => self::COLOR_BORDER]]],
        ]);

        foreach (['A', 'B', 'C', 'D', 'E'] as $col) {
            $logSheet->getColumnDimension($col)->setAutoSize(true);
        }
        $logSheet->freezePane('A4');

        // Open on Sheet 1 by default
        $spreadsheet->setActiveSheetIndex(0);

        // ── Build ZIP (XLSX + PDF inside a named root folder) ────────────────
        $baseName    = $this->safePdfFilename($Document);                        // e.g. DOC-001-Title.pdf
        $xlsName     = substr($baseName, 0, -4) . '.xlsx';                      // e.g. DOC-001-Title.xlsx
        $rootFolder  = substr($baseName, 0, -4) . '-' . now()->format('Y-m-d'); // e.g. DOC-001-Title-2026-07-23
        $zipName     = $rootFolder . '.zip';                                     // e.g. DOC-001-Title-2026-07-23.zip

        $xlsTmp  = tempnam(sys_get_temp_dir(), 'dts_single_xlsx_');
        $zipTmp  = tempnam(sys_get_temp_dir(), 'dts_single_zip_');

        (new Xlsx($spreadsheet))->save($xlsTmp);

        $zip = new \ZipArchive();
        $zip->open($zipTmp, \ZipArchive::OVERWRITE);
        $zip->addFile($xlsTmp, "{$rootFolder}/{$xlsName}");

        // Add the PDF if it exists on disk
        if ($Document->pdf_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($Document->pdf_path)) {
            $pdfDiskPath = \Illuminate\Support\Facades\Storage::disk('local')->path($Document->pdf_path);
            $zip->addFile($pdfDiskPath, "{$rootFolder}/{$pdfFilename}");
        }

        $zip->close();
        @unlink($xlsTmp);

        // ── Stream ZIP response ───────────────────────────────────────────────
        return response()->streamDownload(function () use ($zipTmp) {
            $handle = fopen($zipTmp, 'rb');
            while (! feof($handle)) {
                echo fread($handle, 8192);
                ob_flush();
                flush();
            }
            fclose($handle);
            @unlink($zipTmp);
        }, $zipName, [
            'Content-Type'        => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $zipName . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Build a safe, unique PDF filename for use inside the ZIP.
     * Format: {reference_number}-{sanitised_title}.pdf
     */
    private function safePdfFilename(Document $doc): string
    {
        $ref   = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $doc->reference_number ?? 'DOC');
        $title = preg_replace('/[^a-zA-Z0-9\-_ ]/', '', $doc->title ?? 'untitled');
        $title = trim(preg_replace('/\s+/', '_', $title));
        $title = substr($title, 0, 60); // cap length

        return "{$ref}-{$title}.pdf";
    }

    /**
     * Write a Carbon/DateTime value into a cell and apply a date format (YYYY-MM-DD).
     * Falls back to $fallback string when null.
     */
    private function setDate($sheet, string $cell, $date, string $fallback = ''): void
    {
        if ($date) {
            $sheet->setCellValue($cell, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($date->toDateTime()));
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('YYYY-MM-DD');
        } else {
            $sheet->setCellValue($cell, $fallback);
        }
    }

    /**
     * Write a Carbon/DateTime value into a cell and apply a datetime format.
     * Falls back to $fallback string when null.
     */
    private function setDatetime($sheet, string $cell, $dt, string $fallback = ''): void
    {
        if ($dt) {
            $sheet->setCellValue($cell, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($dt->toDateTime()));
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('YYYY-MM-DD HH:MM:SS');
        } else {
            $sheet->setCellValue($cell, $fallback);
        }
    }
}
