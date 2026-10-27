<?php

$app = require __DIR__.'/video-seed-bootstrap.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
if (($argv[1] ?? '') === 'setup') {
    foreach (['2014_10_12_000000_create_users_table.php', '2024_08_07_135632_create_contact_pages_table.php', '2026_10_21_000001_create_qudrat_tables.php', '2026_10_21_000002_publish_qudrat_content_by_default.php', '2026_10_23_000001_create_arabic_skill_tables.php', '2026_10_26_000001_create_library_videos_tables.php', '2026_10_26_000002_add_video_media_lifecycle.php', '2026_10_27_000001_create_video_seed_imports_table.php'] as $migration) {
        if (Illuminate\Support\Facades\Artisan::call('migrate', ['--path' => 'database/migrations/'.$migration, '--force' => true]) !== 0) throw new RuntimeException('Local migration failed.');
    }
    foreach (['01_nasaq.json', '02_rasm.json', '03_bayan.json', '04_midad.json', '05_burhan.json'] as $name) {
        $path = __DIR__.'/../arabic_skills_content/'.$name;
        $bank = json_decode(file_get_contents($path), true, 64, JSON_THROW_ON_ERROR)['bank'];
        App\Models\ArabicSkillBank::firstOrCreate(['slug' => $bank['id']], ['title' => $bank['title'], 'subtitle' => $bank['subtitle'], 'sort_order' => $bank['order'], 'source_metadata' => []]);
    }
    foreach (['admin', 'student'] as $role) App\Models\User::firstOrCreate(['email' => 'video-seed-'.$role.'@example.invalid'], ['name' => 'مراجعة الفيديوهات', 'role' => $role, 'password' => 'local-video-seed', 'avatar' => 'assets/favicon.ico']);
    App\Models\ContactPage::firstOrCreate([], array_fill_keys(['commercial_registration_no', 'phone_number', 'email', 'address', 'whatsapp_number', 'instagram', 'tiktok', 'snapchat'], 'local-only'));
    echo "Dedicated local library ready. Synthetic logins: video-seed-admin@example.invalid / video-seed-student@example.invalid; password: local-video-seed\n";
    exit;
}
if (($argv[1] ?? '') !== 'video-library:import') throw new RuntimeException('Use setup or video-library:import only.');
$input = new Symfony\Component\Console\Input\ArgvInput;
$status = $kernel->handle($input, new Symfony\Component\Console\Output\ConsoleOutput);
$kernel->terminate($input, $status);
exit($status);
