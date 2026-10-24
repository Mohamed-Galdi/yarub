<?php

// Bootstrap only the existing guarded disposable test connection, never the review DB.
require __DIR__.'/../../vendor/autoload.php';
$test = new class('concurrencyWorker') extends Tests\ArabicSkillsTestCase {};
$app = $test->createApplication();
config(['arabic_skills.allow_development_import' => true]);
app(App\Services\ArabicSkills\ImportEnvironment::class)->assertSafe();
if (Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== 'yarub_qudrat_test_local') {
    throw new RuntimeException('Refusing an unspecified concurrency test database.');
}
$user = App\Models\User::findOrFail((int) $argv[2]);
$practice = app(App\Services\ArabicSkills\Practice::class);
echo "ready\n";
flush();
if ($argv[1] === 'start') {
    echo $practice->start($user, App\Models\ArabicSkillBank::findOrFail((int) $argv[3]))->id;
} else {
    $session = App\Models\ArabicSkillSession::findOrFail((int) $argv[3]);
    if ($argv[1] === 'answer') {
        $practice->answer($user, $session, (int) $argv[4], json_decode($argv[5], true, 512, JSON_THROW_ON_ERROR));
    } else {
        $practice->next($user, $session, (int) $argv[4]);
    }
    echo 'done';
}
