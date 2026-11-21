<?php

namespace App\Console\Commands;

use App\Support\ConflictCsvSchema;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportConflictData extends Command
{
    protected $signature = 'synex:import-conflict-data {path? : CSV path (defaults to the attached dataset)} {--dry-run : Validate without saving} {--append : Append rows instead of updating source row numbers} {--original-name= : Original uploaded file name}';

    protected $description = 'Import the supplied Tchad conflict CSV into the conflict_events table';

    private int $invalidNumericValues = 0;

    private array $lookupIds = [];

    private const SOURCE_FILE = 'Conflict Data Tchad 16 Avril 2026(Conflict Data Tchad 6 juillet 2).csv';

    public function handle(): int
    {
        $path = $this->argument('path') ?? base_path(self::SOURCE_FILE);
        $contents = file_get_contents($path);

        if ($contents === false) {
            $this->error("Unable to read CSV file: {$path}");

            return self::FAILURE;
        }

        $fileHash = hash_file('sha256', $path);
        if ($fileHash === false) {
            $this->error("Unable to fingerprint CSV file: {$path}");

            return self::FAILURE;
        }

        $isAppend = (bool) $this->option('append');
        $importingUser = Auth::user();
        $eventStatus = $importingUser !== null && !$importingUser->isAdmin() ? 'pending' : 'approved';
        if ($isAppend && DB::table('csv_import_batches')->where('sha256', $fileHash)->exists()) {
            $this->error('This exact CSV file has already been imported.');

            return self::FAILURE;
        }
        $sourceRowOffset = $isAppend
            ? (int) (DB::table('conflict_events')->max('source_row') ?? 0)
            : 0;

        if (str_starts_with($contents, "\xEF\xBB\xBF")) {
            $contents = substr($contents, 3);
        }

        if (!mb_check_encoding($contents, 'UTF-8')) {
            $contents = mb_convert_encoding($contents, 'UTF-8', 'ISO-8859-1');
        }
        $contents = strtr($contents, [
            "\u{0083}" => 'É',
            "\u{0086}" => '',
            "\u{0088}" => 'à',
            "\u{008e}" => 'é',
            "\u{008f}" => 'è',
            "\u{0090}" => 'ê',
            "\u{0092}" => '’',
            "\u{0093}" => '“',
            "\u{0094}" => '”',
            "\u{009c}" => 'œ',
        ]);
        $stream = fopen('php://temp', 'w+');

        if ($stream === false || fwrite($stream, $contents) === false) {
            $this->error('Unable to prepare the CSV contents for import.');

            return self::FAILURE;
        }

        rewind($stream);
        $headers = fgetcsv($stream, null, ';', '"', '');

        if ($headers === false) {
            fclose($stream);
            $this->error('The CSV file is empty.');

            return self::FAILURE;
        }

        $headerIndexes = $this->headerIndexes($headers);
        $records = [];
        $actorLinksBySourceRow = [];
        $skippedRows = 0;
        $sourceRow = 1;

        while (($row = fgetcsv($stream, null, ';', '"', '')) !== false) {
            $sourceRow++;
            $recordSourceRow = $isAppend ? $sourceRowOffset + $sourceRow : $sourceRow;

            if ($row === [null] || count($row) < count($headers) - 2) {
                $skippedRows++;

                continue;
            }

            $values = [];
            foreach ($headerIndexes as $field => $index) {
                $values[$field] = $this->normalizeText($row[$index] ?? null);
            }

            $year = $this->integer($values['event_year']);
            if ($year === null || $year < 1900 || $year > 2100 || $values['province'] === null) {
                $skippedRows++;

                continue;
            }

            $this->lookupId('provinces', $values['province']);
            $pastoral = $values['pastoral'];
            $eventNotes = $values['event_notes'];

            if ($pastoral !== null && !preg_match('/^[+-]?\d+$/D', $pastoral)) {
                $eventNotes = $eventNotes === null ? $pastoral : "{$pastoral}\n\n{$eventNotes}";
                $pastoral = null;
            }

            $eventTypeId = $this->lookupId('event_types', $values['event_type']);
            $subEventId = $this->lookupId('sub_events', $values['sub_event'], [
                'event_type_id' => $eventTypeId,
            ]);
            $otherEventId = $this->lookupOtherEventId($values['other_event']);
            $themeId = $this->lookupId('themes', $values['theme']);
            $subthemeId = $this->lookupId('subthemes', $values['subtheme'], [
                'theme_id' => $themeId,
            ]);

            foreach ([
                ['role' => 'actor_1', 'name' => $values['actor_1'], 'association' => $values['association_1'], 'interaction' => $values['interaction_1']],
                ['role' => 'actor_2', 'name' => $values['actor_2'], 'association' => $values['association_2'], 'interaction' => $values['interaction_2']],
            ] as $actorData) {
                $actorId = $this->lookupId('actors', $actorData['name']);

                if ($actorId !== null) {
                    $actorLinksBySourceRow[$recordSourceRow][] = [
                        'actor_id' => $actorId,
                        'role' => $actorData['role'],
                        'association' => $actorData['association'],
                        'interaction' => $actorData['interaction'],
                    ];
                }
            }

            $now = now();
            $records[] = [
                'source_row' => $recordSourceRow,
                'created_by_user_id' => Auth::id(),
                'status' => $eventStatus,
                'review_notes' => null,
                'event_type_id' => $eventTypeId,
                'sub_event_id' => $subEventId,
                'other_event_id' => $otherEventId,
                'theme_id' => $themeId,
                'subtheme_id' => $subthemeId,
                'event_date_raw' => $values['event_date_raw'],
                'month_label' => $values['month_label'],
                'event_year' => $year,
                'time_period' => $this->integer($values['time_period']),
                'event_type' => $values['event_type'],
                'sub_event' => $values['sub_event'],
                'other_event' => $values['other_event'],
                'theme' => $values['theme'],
                'subtheme' => $values['subtheme'],
                'actor_1' => $values['actor_1'],
                'association_1' => $values['association_1'],
                'interaction_1' => $values['interaction_1'],
                'actor_2' => $values['actor_2'],
                'association_2' => $values['association_2'],
                'interaction_2' => $values['interaction_2'],
                'interaction_type' => $values['interaction_type'],
                'province' => $values['province'],
                'department' => $values['department'],
                'commune' => $values['commune'],
                'locality' => $values['locality'],
                'neighborhood' => $values['neighborhood'],
                'street' => $values['street'],
                'latitude' => $this->decimal($values['latitude']),
                'longitude' => $this->decimal($values['longitude']),
                'geoprecision' => $this->integer($values['geoprecision']),
                'source' => $values['source'],
                'source_type' => $values['source_type'],
                'source_url_1' => $values['source_url_1'],
                'source_url_2' => $values['source_url_2'],
                'source_url_3' => $values['source_url_3'],
                'pastoral' => $this->boolean($pastoral),
                'event_notes' => $eventNotes,
                'deaths' => $this->integer($values['deaths']),
                'injuries' => $this->integer($values['injuries']),
                'kidnappings' => $this->integer($values['kidnappings']),
                'arrests' => $this->integer($values['arrests']),
                'property_damage' => $this->integer($values['property_damage']),
                'sexual_violence' => $this->integer($values['sexual_violence']),
                'displaced_people' => $this->integer($values['displaced_people']),
                'conflict_number' => $values['conflict_number'],
                'sequence' => $this->integer($values['sequence']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        fclose($stream);

        if ($records === []) {
            $this->error('No event rows were found. Check the CSV format and header.');

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->info(sprintf('CSV validé : %d événements prêts à importer; %d lignes vides ou de résumé ignorées.', count($records), $skippedRows));

            return self::SUCCESS;
        }

        DB::transaction(function () use ($records, $actorLinksBySourceRow, $isAppend, $fileHash, $path, $skippedRows) {
            foreach (array_chunk($records, 100) as $batch) {
                DB::table('conflict_events')->upsert(
                    $batch,
                    ['source_row'],
                    array_values(array_diff(array_keys($batch[0]), ['source_row', 'created_at']))
                );

                $sourceRows = array_column($batch, 'source_row');
                $eventIds = DB::table('conflict_events')
                    ->whereIn('source_row', $sourceRows)
                    ->pluck('id', 'source_row');
                $databaseEventIds = $eventIds->values()->all();
                DB::table('conflict_event_actors')
                    ->whereIn('conflict_event_id', $databaseEventIds)
                    ->delete();

                $actorLinks = [];
                foreach ($batch as $record) {
                    foreach ($actorLinksBySourceRow[$record['source_row']] ?? [] as $actorLink) {
                        $actorLinks[] = [
                            ...$actorLink,
                            'conflict_event_id' => $eventIds[$record['source_row']],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if ($actorLinks !== []) {
                    DB::table('conflict_event_actors')->insert($actorLinks);
                }
            }

            if ($isAppend) {
                DB::table('csv_import_batches')->insert([
                    'sha256' => $fileHash,
                    'original_name' => $this->option('original-name') ?: basename($path),
                    'imported_rows' => count($records),
                    'skipped_rows' => $skippedRows,
                    'imported_by_user_id' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        $this->info(sprintf('Import terminé : %d événements ajoutés; %d lignes vides ou de résumé ignorées.', count($records), $skippedRows));

        if ($this->invalidNumericValues > 0) {
            $this->warn(sprintf('%d non-numeric values in numeric columns were left empty.', $this->invalidNumericValues));
        }

        return self::SUCCESS;
    }

    private function headerIndexes(array $headers): array
    {
        $normalized = array_map(
            fn ($header) => preg_replace('/\s+/u', ' ', trim((string) $header)),
            $headers
        );
        $indexes = [];

        foreach (ConflictCsvSchema::COLUMNS as $field => $column) {
            $index = array_search($column, $normalized, true);

            if ($index === false) {
                throw new RuntimeException("Required CSV column is missing: {$column}");
            }

            $indexes[$field] = $index;
        }

        return $indexes;
    }

    private function normalizeText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/u', ' ', trim($value));
        $value = preg_replace('/\s+/u', ' ', $value ?? '');
        $value = trim($value ?? '');

        return $value === '' ? null : $value;
    }

    private function integer(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $parsed = filter_var($value, FILTER_VALIDATE_INT);

        if ($parsed === false) {
            $this->invalidNumericValues++;

            return null;
        }

        return $parsed;
    }

    private function decimal(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $value = str_replace([' ', '°', ','], ['', '', '.'], $value);

        if (!is_numeric($value)) {
            $this->invalidNumericValues++;

            return null;
        }

        return round((float) $value, 7);
    }

    private function boolean(?string $value): ?bool
    {
        $parsed = $this->integer($value);

        return $parsed === null ? null : $parsed !== 0;
    }

    private function lookupId(string $table, ?string $name, array $where = []): ?int
    {
        if ($name === null) {
            return null;
        }

        $cacheKey = json_encode([$table, $where, $name], JSON_THROW_ON_ERROR);
        if (isset($this->lookupIds[$cacheKey])) {
            return $this->lookupIds[$cacheKey];
        }

        $query = DB::table($table);
        foreach ($where as $column => $value) {
            $value === null ? $query->whereNull($column) : $query->where($column, $value);
        }

        $reference = $query->where('name', $name)->first(['id', 'is_active']);
        $user = Auth::user();

        if ($reference !== null && !$reference->is_active) {
            throw new RuntimeException("Le CSV utilise une valeur de référentiel bloquée : {$name}");
        }

        if ($reference === null && $user !== null && !$user->isAdmin()) {
            throw new RuntimeException("Cette valeur doit d’abord être ajoutée par un administrateur : {$name}");
        }

        $parentTable = match ($table) {
            'sub_events' => 'event_types',
            'subthemes' => 'themes',
            default => null,
        };
        $parentId = $parentTable !== null ? ($where['event_type_id'] ?? $where['theme_id'] ?? null) : null;

        if ($parentId !== null && !DB::table($parentTable)->where('id', $parentId)->where('is_active', true)->exists()) {
            throw new RuntimeException("Le CSV utilise un type ou une thématique bloqué pour : {$name}");
        }

        if ($this->option('dry-run')) {
            return $reference === null ? null : (int) $reference->id;
        }

        if ($reference === null) {
            $id = DB::table($table)->insertGetId([
                ...$where,
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $id = $reference->id;
        }

        return $this->lookupIds[$cacheKey] = (int) $id;
    }

    private function lookupOtherEventId(?string $description): ?int
    {
        if ($description === null) {
            return null;
        }

        $hash = hash('sha256', $description);
        $cacheKey = "other_events:{$hash}";
        if (isset($this->lookupIds[$cacheKey])) {
            return $this->lookupIds[$cacheKey];
        }

        $reference = DB::table('other_events')->where('value_hash', $hash)->first(['id', 'is_active']);
        $user = Auth::user();

        if ($reference !== null && !$reference->is_active) {
            throw new RuntimeException("Le CSV utilise une valeur de référentiel bloquée : {$description}");
        }

        if ($reference === null && $user !== null && !$user->isAdmin()) {
            throw new RuntimeException("Cette valeur doit d’abord être ajoutée par un administrateur : {$description}");
        }

        if ($this->option('dry-run')) {
            return $reference === null ? null : (int) $reference->id;
        }

        if ($reference === null) {
            $id = DB::table('other_events')->insertGetId([
                'value_hash' => $hash,
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $id = $reference->id;
        }

        return $this->lookupIds[$cacheKey] = (int) $id;
    }
}
