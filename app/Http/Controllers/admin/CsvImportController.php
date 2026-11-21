<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Support\ConflictCsvSchema;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class CsvImportController extends Controller
{
    public function index()
    {
        return view('content.admin.import.index');
    }

    public function template()
    {
        $stream = fopen('php://temp', 'w+');
        if ($stream === false) {
            throw new RuntimeException('Unable to create the CSV template stream.');
        }

        if (fputcsv($stream, ConflictCsvSchema::headers(), ';', '"', '') === false) {
            fclose($stream);
            throw new RuntimeException('Unable to write the CSV template.');
        }
        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        if ($contents === false) {
            throw new RuntimeException('Unable to read the CSV template stream.');
        }

        return response("\xEF\xBB\xBF".$contents, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="synex-evenements-template.csv"',
        ]);
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:20480'],
        ]);

        $uploadId = (string) Str::uuid();
        $file = $validated['csv_file'];
        $relativePath = $file->storeAs('csv-imports', "{$uploadId}.csv", 'local');

        if ($relativePath === false) {
            throw new RuntimeException('Unable to store the uploaded CSV file.');
        }

        try {
            $exitCode = $this->runImport(
                Storage::disk('local')->path($relativePath),
                $file->getClientOriginalName(),
                dryRun: true,
                append: true
            );
        } catch (RuntimeException $exception) {
            Storage::disk('local')->delete($relativePath);

            return back()->withErrors(['csv_file' => $exception->getMessage()]);
        }

        $output = Artisan::output();
        if ($exitCode !== Command::SUCCESS) {
            Storage::disk('local')->delete($relativePath);

            return back()->withErrors(['csv_file' => trim($output) ?: 'Le fichier CSV n’a pas pu être validé.']);
        }

        $request->session()->put("synex_csv_imports.{$uploadId}", [
            'path' => $relativePath,
            'name' => basename($file->getClientOriginalName()),
        ]);

        return view('content.admin.import.index', [
            'uploadId' => $uploadId,
            'previewOutput' => trim($output),
        ]);
    }

    public function commit(Request $request)
    {
        $validated = $request->validate([
            'upload_id' => ['required', 'uuid'],
            'confirm_import' => ['required', 'accepted'],
        ]);

        $uploadId = $validated['upload_id'];
        $pendingImport = $request->session()->get("synex_csv_imports.{$uploadId}");

        if (!is_array($pendingImport)
            || !is_string($pendingImport['path'] ?? null)
            || !str_starts_with($pendingImport['path'], 'csv-imports/')) {
            return redirect()->route('admin.import.index')
                ->withErrors(['csv_file' => 'Le fichier temporaire a expiré. Téléversez-le à nouveau.']);
        }

        $relativePath = $pendingImport['path'];
        $originalName = (string) ($pendingImport['name'] ?? basename($relativePath));
        $disk = Storage::disk('local');
        if (!$disk->exists($relativePath)) {
            $request->session()->forget("synex_csv_imports.{$uploadId}");

            return redirect()->route('admin.import.index')
                ->withErrors(['csv_file' => 'Le fichier temporaire est introuvable. Téléversez-le à nouveau.']);
        }

        try {
            $exitCode = $this->runImport($disk->path($relativePath), $originalName, append: true);
        } catch (RuntimeException $exception) {
            $disk->delete($relativePath);
            $request->session()->forget("synex_csv_imports.{$uploadId}");

            return redirect()->route('admin.import.index')->withErrors(['csv_file' => $exception->getMessage()]);
        }

        $output = trim(Artisan::output());
        $disk->delete($relativePath);
        $request->session()->forget("synex_csv_imports.{$uploadId}");

        if ($exitCode !== Command::SUCCESS) {
            return redirect()->route('admin.import.index')
                ->withErrors(['csv_file' => $output ?: 'L’import du fichier CSV a échoué.']);
        }

        $status = Auth::user()->isAdmin()
            ? $output
            : "{$output} Les événements importés sont en attente de validation.";

        return redirect()->route('admin.import.index')->with('status', $status);
    }

    private function runImport(string $path, string $originalName, bool $dryRun = false, bool $append = false): int
    {
        $arguments = [
            'path' => $path,
            '--original-name' => mb_substr(basename($originalName), 0, 255),
        ];

        if ($dryRun) {
            $arguments['--dry-run'] = true;
        }

        if ($append) {
            $arguments['--append'] = true;
        }

        return Artisan::call('synex:import-conflict-data', $arguments);
    }
}
