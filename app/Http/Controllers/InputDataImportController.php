<?php

namespace App\Http\Controllers;

use App\Services\InputDataImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InputDataImportController extends Controller
{
    private const PREVIEW_LIMIT = 200;

    public function __construct(private InputDataImporter $importer)
    {
    }

    /**
     * Store the uploaded CSV and return a row-by-row preview with validation results.
     */
    public function preview(Request $request, string $tab)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ], [
            'file.required' => 'Choose a CSV file first.',
            'file.mimes' => 'Only .csv files are supported.',
            'file.max' => 'The file may not be larger than 10MB.',
        ]);

        $token = Str::random(40);
        $path = $request->file('file')->storeAs('imports', "{$tab}-{$token}.csv");

        $parsed = $this->importer->parse(Storage::path($path), $tab);

        if ($parsed['rows'] === []) {
            Storage::delete($path);

            return response()->json(['message' => $parsed['errors'][0] ?? 'The file has no data rows.'], 422);
        }

        $columns = $this->importer->columns($tab);
        $seen = [];
        $rows = [];
        $valid = 0;

        foreach ($parsed['rows'] as $row) {
            [, $errors] = $this->importer->process($tab, $row, $seen);
            $valid += $errors === [] ? 1 : 0;

            if (count($rows) < self::PREVIEW_LIMIT) {
                $rows[] = [
                    'line' => $row['_line'],
                    'cells' => array_map(fn ($column) => $row[$column], $columns),
                    'errors' => $errors,
                ];
            }
        }

        return response()->json([
            'token' => $token,
            'columns' => $columns,
            'rows' => $rows,
            'total' => count($parsed['rows']),
            'valid' => $valid,
            'invalid' => count($parsed['rows']) - $valid,
            'notes' => $parsed['errors'],
        ]);
    }

    /**
     * Import every valid row of a previously uploaded file; invalid rows are skipped.
     */
    public function commit(Request $request, string $tab)
    {
        $token = $request->validate(['token' => ['required', 'regex:/^[A-Za-z0-9]{40}$/']])['token'];
        $path = "imports/{$tab}-{$token}.csv";

        abort_unless(Storage::exists($path), 422, 'This upload has expired. Please upload the file again.');

        $parsed = $this->importer->parse(Storage::path($path), $tab);
        $seen = [];
        $imported = 0;

        foreach ($parsed['rows'] as $row) {
            [$data, $errors] = $this->importer->process($tab, $row, $seen);

            if ($errors === [] && $data !== null) {
                $this->importer->create($tab, $data);
                $imported++;
            }
        }

        Storage::delete($path);

        $skipped = count($parsed['rows']) - $imported;
        $message = "Imported {$imported} row" . ($imported === 1 ? '' : 's') . ($skipped ? ", skipped {$skipped} with errors." : '.');
        session()->flash('success', $message);

        return response()->json(['imported' => $imported, 'skipped' => $skipped, 'message' => $message]);
    }

    /**
     * A CSV containing only the header row for the tab.
     */
    public function template(string $tab)
    {
        $headers = $this->importer->columns($tab);

        return response(implode(',', $headers) . "\n", 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$tab}_template.csv\"",
        ]);
    }
}
