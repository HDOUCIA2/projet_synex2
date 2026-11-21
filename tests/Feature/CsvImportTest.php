<?php

namespace Tests\Feature;

use App\Models\Province;
use App\Models\EventType;
use App\Models\Theme;
use App\Models\User;
use App\Support\ConflictCsvSchema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CsvImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_uses_the_importer_header_schema(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']));

        $response = $this->get(route('admin.import.template'));

        $response->assertOk();
        $this->assertSame(
            ConflictCsvSchema::headers(),
            str_getcsv(substr($response->getContent(), 3), ';', '"', '')
        );
    }

    public function test_csv_is_validated_before_commit_and_identical_files_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']));
        Province::create(['name' => 'Mandoul']);
        EventType::create(['name' => 'Affrontement']);
        Theme::create(['name' => 'Communautaire']);
        $csv = $this->sampleCsv();

        $preview = $this->post(route('admin.import.preview'), [
            'csv_file' => UploadedFile::fake()->createWithContent('events.csv', $csv),
        ]);

        $preview->assertOk()->assertViewHas('previewOutput');
        $uploadId = $preview->viewData('uploadId');
        $this->assertDatabaseCount('conflict_events', 0);
        $this->assertDatabaseCount('provinces', 1);

        $this->post(route('admin.import.commit'), [
            'upload_id' => $uploadId,
            'confirm_import' => '1',
        ])->assertRedirect(route('admin.import.index'));

        $this->assertDatabaseCount('conflict_events', 1);
        $this->assertDatabaseHas('conflict_events', [
            'province' => 'Mandoul',
            'event_year' => 2025,
            'source_row' => 2,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('csv_import_batches', ['imported_rows' => 1]);
        $this->assertDatabaseHas('provinces', ['name' => 'Mandoul']);

        $this->from(route('admin.import.index'))
            ->post(route('admin.import.preview'), [
                'csv_file' => UploadedFile::fake()->createWithContent('events.csv', $csv),
            ])
            ->assertRedirect(route('admin.import.index'))
            ->assertSessionHasErrors('csv_file');

        $this->assertDatabaseCount('conflict_events', 1);
    }

    public function test_user_csv_cannot_use_a_blocked_reference(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']));
        $province = Province::create(['name' => 'Mandoul']);
        $province->is_active = false;
        $province->save();
        EventType::create(['name' => 'Affrontement']);
        Theme::create(['name' => 'Communautaire']);

        $this->from(route('admin.import.index'))
            ->post(route('admin.import.preview'), [
                'csv_file' => UploadedFile::fake()->createWithContent('events.csv', $this->sampleCsv()),
            ])
            ->assertRedirect(route('admin.import.index'))
            ->assertSessionHasErrors('csv_file');

        $this->assertDatabaseCount('conflict_events', 0);
    }

    private function sampleCsv(): string
    {
        $values = [
            'date' => '12-mai-25',
            'année' => '2025',
            'événement' => 'Affrontement',
            'thématique' => 'Communautaire',
            'province' => 'Mandoul',
            'latitude' => '8.1234567',
            'longitude' => '17.4567890',
        ];
        $stream = fopen('php://temp', 'w+');

        fputcsv($stream, ConflictCsvSchema::headers(), ';', '"', '');
        fputcsv($stream, array_map(fn (string $header) => $values[$header] ?? '', ConflictCsvSchema::headers()), ';', '"', '');
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv ?: '';
    }
}
