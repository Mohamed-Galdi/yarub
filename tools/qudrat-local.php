<?php

// Dedicated native development entry point. Never loads the project's real .env.
$command = $_SERVER['argv'][1] ?? '';
if (! in_array($command, ['qudrat:import', 'qudrat:review-setup', 'migrate', 'migrate:status'], true)
    || in_array('--seed', $_SERVER['argv'], true)) {
    fwrite(STDERR, "This isolated entry point supports only Qudrat import and migrations, without seeding.\n");
    exit(1);
}
$app = require __DIR__.'/qudrat-bootstrap.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
app(App\Services\Qudrat\ImportEnvironment::class)->assertSafe();
$input = new Symfony\Component\Console\Input\ArgvInput;
$output = new Symfony\Component\Console\Output\ConsoleOutput;
$status = $kernel->handle($input, $output);
$kernel->terminate($input, $status);
exit($status);
