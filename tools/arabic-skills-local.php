<?php

// Reuse the owner's existing non-live review environment, never the root .env.
$command = $_SERVER['argv'][1] ?? '';
$migration = '--path=database/migrations/2026_10_23_000001_create_arabic_skill_tables.php';
if (! in_array($command, ['arabic-skills:import', 'migrate'], true)
    || ($command === 'migrate' && array_slice($_SERVER['argv'], 2) !== [$migration])) {
    fwrite(STDERR, "Use arabic-skills:import, or migrate with only the Arabic Skills migration path.\n");
    exit(1);
}
$app = require __DIR__.'/qudrat-bootstrap.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
// This wrapper opts in only the owner-approved loopback review DB; tests use their own bootstrap.
if (! app()->environment(['local', 'development'])) {
    throw new RuntimeException('Local wrapper requires local/development mode.');
}
config(['arabic_skills.allow_development_import' => true, 'arabic_skills.existing_local_database' => 'yarub']);
app(App\Services\ArabicSkills\ImportEnvironment::class)->assertSafe();
if (Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== 'yarub') {
    throw new RuntimeException('Local review must use the existing yarub database.');
}
$input = new Symfony\Component\Console\Input\ArgvInput;
$output = new Symfony\Component\Console\Output\ConsoleOutput;
$status = $kernel->handle($input, $output);
$kernel->terminate($input, $status);
exit($status);
