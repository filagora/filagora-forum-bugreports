<?php

declare(strict_types=1);

use Filagora\Core\Modules\ModuleRegistry;
use Filagora\Core\Schema\Adopter;
use Filagora\Core\Schema\AdoptionStatus;
use Filagora\Core\Schema\SchemaRegistry;
use Filagora\Core\Schema\TableShape;
use Illuminate\Support\Facades\DB;

it('registers itself as a module that needs forums', function () {
    expect(app(ModuleRegistry::class)->get('forum-bugreports')->requires)->toBe(['core', 'forums']);
});

it('builds the table exactly as the snapshot has it', function () {
    $snapshot = app(SchemaRegistry::class)->get('forum-bugreports');

    expect(array_keys($snapshot->tables))->toBe(['bug_reports'])
        ->and(TableShape::inspect('bug_reports')->differencesFrom($snapshot->tables['bug_reports']))->toBe([]);
});

it('adopts an existing table by recording its migration', function () {
    $schema = app(SchemaRegistry::class)->get('forum-bugreports');
    DB::table('migrations')->whereIn('migration', $schema->migrationNames())->delete();

    expect(app(Adopter::class)->planFor($schema)->status)->toBe(AdoptionStatus::Adoptable);

    $this->artisan('filagora:adopt', ['--package' => 'forum-bugreports'])->assertExitCode(0);

    expect(app(Adopter::class)->planFor($schema)->status)->toBe(AdoptionStatus::AlreadyAdopted);
});
