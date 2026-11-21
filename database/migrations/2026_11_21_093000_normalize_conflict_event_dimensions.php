<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('sub_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['event_type_id', 'name']);
        });

        Schema::create('other_events', function (Blueprint $table) {
            $table->id();
            $table->char('value_hash', 64)->unique();
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('subthemes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['theme_id', 'name']);
        });

        Schema::create('actors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::table('conflict_events', function (Blueprint $table) {
            $table->foreignId('event_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sub_event_id')->nullable()->constrained('sub_events')->nullOnDelete();
            $table->foreignId('other_event_id')->nullable()->constrained('other_events')->nullOnDelete();
            $table->foreignId('theme_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subtheme_id')->nullable()->constrained('subthemes')->nullOnDelete();
        });

        Schema::create('conflict_event_actors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conflict_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained()->restrictOnDelete();
            $table->string('role', 16);
            $table->string('association')->nullable();
            $table->string('interaction')->nullable();
            $table->timestamps();
            $table->unique(['conflict_event_id', 'role']);
        });

        DB::transaction(function () {
            foreach (DB::table('conflict_events')->orderBy('id')->get() as $event) {
                $eventTypeId = $this->lookupId('event_types', 'name', $event->event_type);
                $subEventId = $this->lookupId('sub_events', 'name', $event->sub_event, [
                    'event_type_id' => $eventTypeId,
                ]);
                $otherEventId = $this->lookupOtherEventId($event->other_event);
                $themeId = $this->lookupId('themes', 'name', $event->theme);
                $subthemeId = $this->lookupId('subthemes', 'name', $event->subtheme, [
                    'theme_id' => $themeId,
                ]);

                DB::table('conflict_events')->where('id', $event->id)->update([
                    'event_type_id' => $eventTypeId,
                    'sub_event_id' => $subEventId,
                    'other_event_id' => $otherEventId,
                    'theme_id' => $themeId,
                    'subtheme_id' => $subthemeId,
                ]);

                foreach ([
                    ['role' => 'actor_1', 'name' => $event->actor_1, 'association' => $event->association_1, 'interaction' => $event->interaction_1],
                    ['role' => 'actor_2', 'name' => $event->actor_2, 'association' => $event->association_2, 'interaction' => $event->interaction_2],
                ] as $actorData) {
                    $actorId = $this->lookupId('actors', 'name', $actorData['name']);

                    if ($actorId !== null) {
                        DB::table('conflict_event_actors')->insert([
                            'conflict_event_id' => $event->id,
                            'actor_id' => $actorId,
                            'role' => $actorData['role'],
                            'association' => $actorData['association'],
                            'interaction' => $actorData['interaction'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conflict_event_actors');

        Schema::table('conflict_events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_type_id');
            $table->dropConstrainedForeignId('sub_event_id');
            $table->dropConstrainedForeignId('other_event_id');
            $table->dropConstrainedForeignId('theme_id');
            $table->dropConstrainedForeignId('subtheme_id');
        });

        Schema::dropIfExists('actors');
        Schema::dropIfExists('subthemes');
        Schema::dropIfExists('themes');
        Schema::dropIfExists('other_events');
        Schema::dropIfExists('sub_events');
        Schema::dropIfExists('event_types');
    }

    private function lookupId(string $table, string $column, ?string $value, array $where = []): ?int
    {
        if ($value === null) {
            return null;
        }

        $query = DB::table($table);
        foreach ($where as $key => $whereValue) {
            $whereValue === null ? $query->whereNull($key) : $query->where($key, $whereValue);
        }

        $id = $query->where($column, $value)->value('id');

        if ($id !== null) {
            return (int) $id;
        }

        return (int) DB::table($table)->insertGetId([
            ...$where,
            $column => $value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function lookupOtherEventId(?string $description): ?int
    {
        if ($description === null) {
            return null;
        }

        $hash = hash('sha256', $description);
        $id = DB::table('other_events')->where('value_hash', $hash)->value('id');

        return $id === null
            ? (int) DB::table('other_events')->insertGetId([
                'value_hash' => $hash,
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            : (int) $id;
    }
};
