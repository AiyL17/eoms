<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ExportController extends Controller
{
    /**
     * Export filtered documents as XLSX.
     * Documents are grouped into one sheet per year (newest year first),
     * and sorted by date received descending within each sheet.
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

        // ── Group by year of date_issued, newest year first ───────────────────
        $byYear = $documents
            ->sortByDesc(fn ($d) => $d->date_issued?->timestamp ?? 0)
            ->groupBy(fn ($d) => $d->date_issued?->year ?? 'Unknown')
            ->sortKeysDesc();   // 2026 → 2025 → 2024 …

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);   // remove the default blank sheet

        $headers = [
            'A' => 'Reference Number',
            'B' => 'Document Type',
            'C' => 'Title',
            'D' => 'Date Received',
            'E' => 'Deadline',
            'F' => 'Office / Origin',
            'G' => 'Recipient',
            'H' => 'Uploaded By',
            'I' => 'Created At',
        ];

        foreach ($byYear as $year => $docs) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle((string) $year);

            // ── Header row ────────────────────────────────────────────────────
            foreach ($headers as $col => $label) {
                $sheet->setCellValue("{$col}1", $label);
            }

            $sheet->getStyle('A1:I1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2563EB'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // ── Data rows ─────────────────────────────────────────────────────
            $row = 2;
            foreach ($docs as $doc) {
                $sheet->setCellValue("A{$row}", $doc->reference_number);
                $sheet->setCellValue("B{$row}", $doc->document_type_label);
                $sheet->setCellValue("C{$row}", $doc->title);
                $this->setDate($sheet, "D{$row}", $doc->date_issued);
                $this->setDate($sheet, "E{$row}", $doc->expiration_date);
                $sheet->setCellValue("F{$row}", $doc->received_from);
                $sheet->setCellValue("G{$row}", $doc->recipient);
                $sheet->setCellValue("H{$row}", $doc->uploader?->name ?? 'System');
                $this->setDatetime($sheet, "I{$row}", $doc->created_at);
                $row++;
            }

            // ── Auto-size + freeze ────────────────────────────────────────────
            foreach (array_keys($headers) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            $sheet->freezePane('A2');
        }

        // Make the first sheet (newest year) active on open
        $spreadsheet->setActiveSheetIndex(0);

        // ── Stream response ───────────────────────────────────────────────────
        $filename = 'DTS-Export-' . now()->format('Y-m-d') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Export a single document's full details as XLSX.
     */
    public function exportSingleCsv(Document $Document)
    {
        $Document->load(['uploader', 'updater', 'activityLogs.user']);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Document Details');

        // ── Document details section ──────────────────────────────────────────
        $sheet->setCellValue('A1', '=== DOCUMENT DETAILS ===');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);

        $details = [
            ['Field',           'Value'],
            ['Reference Number', $Document->reference_number],
            ['Document Type',    $Document->document_type_label],
            ['Title',            $Document->title],
            ['Date Received',    null],   // filled via setDate below
            ['Deadline',         null],   // filled via setDate below
            ['Office / Origin',  $Document->received_from ?? 'N/A'],
            ['Recipient',        $Document->recipient ?? 'N/A'],
            ['Uploaded By',      $Document->uploader?->name ?? 'System'],
            ['Last Updated By',  $Document->updater?->name ?? 'N/A'],
            ['Created At',       null],   // filled via setDatetime below
            ['Updated At',       null],   // filled via setDatetime below
        ];

        $rowIdx = 2;
        foreach ($details as $i => $pair) {
            $sheet->setCellValue("A{$rowIdx}", $pair[0]);
            if ($pair[1] !== null) {
                $sheet->setCellValue("B{$rowIdx}", $pair[1]);
            }
            $rowIdx++;
        }

        // Fill the date/datetime cells by their known positions
        // Row 2 = header, rows 3-13 = data (0-indexed $details[0..10])
        // Date Received = details[4] → sheet row 6, Deadline = details[5] → row 7
        // Created At = details[10] → row 12, Updated At = details[11] → row 13
        $this->setDate($sheet,     'B6',  $Document->date_issued);
        $this->setDate($sheet,     'B7',  $Document->expiration_date, 'N/A');
        $this->setDatetime($sheet, 'B12', $Document->created_at);
        $this->setDatetime($sheet, 'B13', $Document->updated_at);

        // Left-align date cells to match the rest of the Value column
        foreach (['B6', 'B7', 'B12', 'B13'] as $cell) {
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        // Bold the header row of the details table
        $sheet->getStyle('A2:B2')->getFont()->setBold(true);
        $sheet->getStyle('A2:B2')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF2563EB');
        $sheet->getStyle('A2:B2')->getFont()->getColor()->setARGB('FFFFFFFF');

        // ── Activity log section ──────────────────────────────────────────────
        $logStartRow = $rowIdx + 1;   // blank spacer row
        $sheet->setCellValue("A{$logStartRow}", '=== ACTIVITY LOG ===');
        $sheet->getStyle("A{$logStartRow}")->getFont()->setBold(true)->setSize(12);
        $logStartRow++;

        $logHeaders = ['Action', 'Performed By', 'Notes', 'IP Address', 'Date/Time'];
        foreach ($logHeaders as $ci => $lh) {
            $col = chr(ord('A') + $ci);
            $sheet->setCellValue("{$col}{$logStartRow}", $lh);
        }
        $sheet->getStyle("A{$logStartRow}:E{$logStartRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$logStartRow}:E{$logStartRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF2563EB');
        $sheet->getStyle("A{$logStartRow}:E{$logStartRow}")->getFont()->getColor()->setARGB('FFFFFFFF');

        $logRow = $logStartRow + 1;
        foreach ($Document->activityLogs as $log) {
            $sheet->setCellValue("A{$logRow}", $log->action_label);
            $sheet->setCellValue("B{$logRow}", $log->user?->name ?? 'System');
            $sheet->setCellValue("C{$logRow}", $log->notes ?? '');
            $sheet->setCellValue("D{$logRow}", $log->ip_address ?? '');
            $this->setDatetime($sheet, "E{$logRow}", $log->created_at);
            $sheet->getStyle("E{$logRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $logRow++;
        }

        // ── Auto-size columns A–E ─────────────────────────────────────────────
        foreach (['A', 'B', 'C', 'D', 'E'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->freezePane('A3');

        // ── Stream response ───────────────────────────────────────────────────
        $filename = 'DTS-' . $Document->title . '-' . now()->format('Y-m-d') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

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
