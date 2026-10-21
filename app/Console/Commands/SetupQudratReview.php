<?php

namespace App\Console\Commands;

use App\Models\ContactPage;
use App\Models\User;
use App\Services\Qudrat\ImportEnvironment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SetupQudratReview extends Command
{
    protected $signature = 'qudrat:review-setup';

    protected $description = 'Create minimal local-only admin review fixtures in the dedicated Qudrat development database';

    public function handle(ImportEnvironment $environment): int
    {
        $environment->assertSafe();
        if (! app()->environment('local') || ! str_starts_with(DB::connection()->getDatabaseName(), 'yarub_qudrat_dev_')) {
            $this->error('Review fixtures require the dedicated local development database.');

            return self::FAILURE;
        }
        $file = storage_path('app/qudrat-local/review-login.json');
        $email = 'qudrat-admin@example.invalid';
        $credentials = DB::transaction(function () use ($email) {
            ContactPage::firstOrCreate([], ['commercial_registration_no' => 'local-review', 'phone_number' => '', 'email' => $email,
                'address' => '', 'whatsapp_number' => '', 'instagram' => '', 'tiktok' => '', 'snapchat' => '']);
            if (User::where('email', $email)->exists()) {
                return null;
            }
            $password = Str::random(24);
            User::create(['name' => 'مشرف مراجعة القدرات', 'email' => $email, 'password' => $password, 'role' => 'admin']);

            return ['email' => $email, 'password' => $password];
        });
        if ($credentials) {
            file_put_contents($file, json_encode($credentials, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        }
        $this->info('Local review fixtures ready. Login: /yarub-moshref-center. Credentials are in ignored storage/app/qudrat-local/review-login.json.');

        return self::SUCCESS;
    }
}
