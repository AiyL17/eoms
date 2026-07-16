<?php

namespace App\Http\Controllers;

use App\Models\ExecutiveOrder;
use Illuminate\Http\Request;
use League\Csv\Writer;

class ExportController extends Controller
{
    /**
     * Export filtered EOs as CSV.
     * Accepts the same query params as the index: search, status, year, tag, sort, dir.
     */
    public function exportCsv(Request $request)
    {
        $query = ExecutiveOrder::with('uploader')->latest();

        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        if ($request->filled('year')) {
            $query->byYear((int) $request->year);
        }
        if ($request->filled('tag')) {
            $query->whereJsonContains('tags', $request->tag);
        }

        $sortable = ['eo_number', 'date_issued', 'signed_by', 'status', 'year'];
        $sort     = in_array($request->sort, $sortable) ? $request->sort : null;
        $dir      = $request->dir === 'asc' ? 'asc' : 'desc';
        if ($sort) {
            $query->reorder()->orderBy($sort, $dir);
        }

        $orders = $query->get();

        $csv = Writer::createFromString();
        $csv->insertOne([
            'EO Number', 'Item Number', 'Year', 'Title', 'Subject',
            'Date Issued', 'Date Effective', 'Signed By', 'Status',
            'Status Notes', 'Tags', 'Uploaded By', 'File Size', 'Created At',
        ]);

        foreach ($orders as $eo) {
            $csv->insertOne([
                $eo->eo_number,
                $eo->item_number,
                $eo->year,
                $eo->title,
                $eo->subject,
                $eo->date_issued?->format('Y-m-d'),
                $eo->date_effective?->format('Y-m-d'),
                $eo->signed_by,
                $eo->status_label,
                $eo->status_notes,
                $eo->tags ? implode('; ', $eo->tags) : '',
                $eo->uploader?->name ?? 'System',
                $eo->file_size_formatted,
                $eo->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        $filename = 'executive-orders-' . now()->format('Y-m-d-His') . '.csv';

        return response((string) $csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export a single EO's full details as CSV (for audit trail / reporting).
     */
    public function exportSingleCsv(ExecutiveOrder $executiveOrder)
    {
        $executiveOrder->load(['uploader', 'updater', 'amends', 'amendedBy', 'activityLogs.user']);

        $csv = Writer::createFromString();

        // EO Details section
        $csv->insertOne(['=== EXECUTIVE ORDER DETAILS ===']);
        $csv->insertOne(['Field', 'Value']);
        $csv->insertOne(['EO Number',        $executiveOrder->eo_number]);
        $csv->insertOne(['Title',            $executiveOrder->title]);
        $csv->insertOne(['Subject',          $executiveOrder->subject]);
        $csv->insertOne(['Date Issued',      $executiveOrder->date_issued?->format('F d, Y')]);
        $csv->insertOne(['Date Effective',   $executiveOrder->date_effective?->format('F d, Y') ?? 'N/A']);
        $csv->insertOne(['Signed By',        $executiveOrder->signed_by]);
        $csv->insertOne(['Status',           $executiveOrder->status_label]);
        $csv->insertOne(['Status Notes',     $executiveOrder->status_notes ?? 'N/A']);
        $csv->insertOne(['Tags',             $executiveOrder->tags ? implode('; ', $executiveOrder->tags) : 'N/A']);
        $csv->insertOne(['Content Summary',  $executiveOrder->content_summary ?? 'N/A']);
        $csv->insertOne(['Amends',           $executiveOrder->amends?->eo_number ?? 'N/A']);
        $csv->insertOne(['Amended By',       $executiveOrder->amendedBy?->eo_number ?? 'N/A']);
        $csv->insertOne(['Uploaded By',      $executiveOrder->uploader?->name ?? 'System']);
        $csv->insertOne(['Last Updated By',  $executiveOrder->updater?->name ?? 'N/A']);
        $csv->insertOne(['Created At',       $executiveOrder->created_at->format('Y-m-d H:i:s')]);
        $csv->insertOne(['Updated At',       $executiveOrder->updated_at->format('Y-m-d H:i:s')]);
        $csv->insertOne([]);

        // Activity log section
        $csv->insertOne(['=== ACTIVITY LOG ===']);
        $csv->insertOne(['Action', 'Performed By', 'Notes', 'IP Address', 'Date/Time']);

        foreach ($executiveOrder->activityLogs as $log) {
            $csv->insertOne([
                $log->action_label,
                $log->user?->name ?? 'System',
                $log->notes ?? '',
                $log->ip_address ?? '',
                $log->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        $filename = 'eo-' . preg_replace('/[^a-z0-9\-]/', '-', strtolower($executiveOrder->eo_number))
                   . '-' . now()->format('Y-m-d') . '.csv';

        return response((string) $csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
