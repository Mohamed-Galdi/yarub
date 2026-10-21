<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Publication is now automatic. Keep archived questions and all attempt history intact.
        DB::transaction(function () {
            DB::table('qudrat_categories')->where('is_published', false)->update(['is_published' => true, 'updated_at' => now()]);
            DB::table('qudrat_questions')->whereNull('archived_at')->where('is_published', false)
                ->update(['is_published' => true, 'updated_at' => now()]);
        });
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE qudrat_categories ALTER COLUMN is_published SET DEFAULT 1');
            DB::statement('ALTER TABLE qudrat_questions ALTER COLUMN is_published SET DEFAULT 1');
        }
    }

    public function down(): void
    {
        // Reverting the default must not silently unpublish existing content.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE qudrat_categories ALTER COLUMN is_published SET DEFAULT 0');
            DB::statement('ALTER TABLE qudrat_questions ALTER COLUMN is_published SET DEFAULT 0');
        }
    }
};
