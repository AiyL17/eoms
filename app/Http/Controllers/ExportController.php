<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use League\Csv\Writer;

class ExportController extends Controller
{
    /**
     * Export filtered documents as CSV.
     * Accepts the same query params as the index: search, document_type, year, sort, dir.
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

        $sortable = ['doc_number', 'date_issued'];
        $sort     = in_array($request->sort, $sortable) ? $request->sort : null;
        $dir      = $request->dir === 'asc' ? 'asc' : 'desc';
        if ($sort) {
            $query->reorder()->orderBy($sort, $dir);
        }

        $orders = $query->get();

        $csv = Writer::createFromString();
        $csv->insertOne([
            'Document Number', 'Document Type', 'Title',
            'Date Received', 'Expiration Date', 'Office / Origin', 'Recipient',
            'Uploaded By', 'File Size', 'Created At',
        ]);

        foreach ($orders as $doc) {
            $csv->insertOne([
                $doc->doc_number,
                $doc->document_type_label,
                $doc->title,
                $doc->date_issued?->format('Y-m-d'),
                $doc->expiration_date?->format('Y-m-d'),
                $doc->received_from,
                $doc->recipient,
                $doc->uploader?->name ?? 'System',
                $doc->file_size_formatted,
                $doc->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        $filename = 'documents-' . now()->format('Y-m-d-His') . '.csv';

        return response((string) $csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export a single document's full details as CSV (for audit trail / reporting).
     */
    public function exportSingleCsv(Document $Document)
    {
        $Document->load(['uploader', 'updater', 'activityLogs.user']);

        $csv = Writer::createFromString();

        $csv->insertOne(['=== DOCUMENT DETAILS ===']);
        $csv->insertOne(['Field', 'Value']);
        $csv->insertOne(['Document Number',  $Document->doc_number]);
        $csv->insertOne(['Document Type',    $Document->document_type_label]);
        $csv->insertOne(['Title',            $Document->title]);
        $csv->insertOne(['Date Received',    $Document->date_issued?->format('F d, Y')]);
        $csv->insertOne(['Expiration Date',  $Document->expiration_date?->format('F d, Y') ?? 'N/A']);
        $csv->insertOne(['Office / Origin',  $Document->received_from ?? 'N/A']);
        $csv->insertOne(['Recipient',        $Document->recipient ?? 'N/A']);
        $csv->insertOne(['Uploaded By',      $Document->uploader?->name ?? 'System']);
        $csv->insertOne(['Last Updated By',  $Document->updater?->name ?? 'N/A']);
        $csv->insertOne(['Created At',       $Document->created_at->format('Y-m-d H:i:s')]);
        $csv->insertOne(['Updated At',       $Document->updated_at->format('Y-m-d H:i:s')]);
        $csv->insertOne([]);

        $csv->insertOne(['=== ACTIVITY LOG ===']);
        $csv->insertOne(['Action', 'Performed By', 'Notes', 'IP Address', 'Date/Time']);

        foreach ($Document->activityLogs as $log) {
            $csv->insertOne([
                $log->action_label,
                $log->user?->name ?? 'System',
                $log->notes ?? '',
                $log->ip_address ?? '',
                $log->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        $filename = 'document-' . preg_replace('/[^a-z0-9\-]/', '-', strtolower($Document->doc_number))
                   . '-' . now()->format('Y-m-d') . '.csv';

        return response((string) $csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
