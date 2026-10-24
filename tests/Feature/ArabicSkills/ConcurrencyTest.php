<?php

namespace Tests\Feature\ArabicSkills;

use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillQuestion;
use App\Models\ArabicSkillSession;
use App\Models\User;
use App\Services\ArabicSkills\ContentValidator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\ArabicSkillsTestCase;

class ConcurrencyTest extends ArabicSkillsTestCase
{
    public function test_parallel_starts_answers_and_next_actions_are_serialized(): void
    {
        $student = User::create(['name' => 'Concurrency', 'email' => 'arabic-concurrent@example.invalid', 'password' => 'local', 'role' => 'student']);
        $bank = ArabicSkillBank::create(['slug' => 'concurrent', 'title' => 'اختبار', 'subtitle' => 'اختبار', 'sort_order' => 1, 'source_metadata' => []]);
        $source = app(ContentValidator::class)->validateDirectory(base_path('arabic_skills_content'))['banks'][0]['questions'][7];
        $data = array_intersect_key($source, array_flip(['skill', 'type', 'prompt', 'options', 'matching_items', 'answer', 'explanation', 'hint', 'scoring', 'max_score']));
        for ($i = 0; $i < 10; $i++) {
            ArabicSkillQuestion::create($data + ['bank_id' => $bank->id]);
        }
        DB::commit();
        $workers = [];
        $parallel = function (array $firstArgs, array $secondArgs) use (&$workers, $student): array {
            DB::beginTransaction();
            User::whereKey($student->id)->lockForUpdate()->firstOrFail();
            $pair = [];
            $ready = [false, false];
            try {
                foreach ([$firstArgs, $secondArgs] as $index => $arguments) {
                    $process = new Process(array_merge([PHP_BINARY, '-c', php_ini_loaded_file(), base_path('tests/Support/arabic-skills-concurrent-worker.php')], $arguments), base_path());
                    $process->setTimeout(30)->start(function ($type, $output) use (&$ready, $index) {
                        if (str_contains($output, 'ready')) {
                            $ready[$index] = true;
                        }
                    });
                    $workers[] = $pair[] = $process;
                }
                $deadline = microtime(true) + 10;
                foreach ($pair as $index => $process) {
                    while (! $ready[$index] && $process->isRunning() && microtime(true) < $deadline) {
                        usleep(10000);
                    }
                    $this->assertTrue($ready[$index], $process->getErrorOutput().$process->getOutput());
                }
            } finally {
                DB::commit();
            }
            foreach ($pair as $process) {
                $process->wait();
                $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
            }

            return $pair;
        };
        try {
            [$first, $second] = $parallel(['start', (string) $student->id, (string) $bank->id], ['start', (string) $student->id, (string) $bank->id]);
            $this->assertSame($first->getOutput(), $second->getOutput());
            $session = ArabicSkillSession::where('user_id', $student->id)->sole();
            $this->assertSame(10, $session->items()->count());
            $item = $session->items()->firstOrFail();
            $correct = $item->content_snapshot['answer'];
            $partial = $correct;
            $partial['matches'][1]['option_id'] = $partial['matches'][0]['option_id'];
            $parallel(['answer', (string) $student->id, (string) $session->id, (string) $item->id, json_encode($correct)],
                ['answer', (string) $student->id, (string) $session->id, (string) $item->id, json_encode($partial)]);
            $item->refresh();
            $this->assertContains($item->earned_points, [1, 2]);
            $this->assertSame($item->earned_points, $session->fresh()->earned_points);
            $this->assertSame(1, $session->items()->whereNotNull('submitted_at')->count());
            $parallel(['next', (string) $student->id, (string) $session->id, (string) $item->id],
                ['next', (string) $student->id, (string) $session->id, (string) $item->id]);
            $this->assertSame(2, $session->fresh()->current_position);
            $this->assertSame('active', $session->fresh()->status);
        } finally {
            foreach ($workers as $worker) {
                if ($worker->isRunning()) {
                    $worker->stop();
                }
            }
            // Only committed fixtures for this worker scenario; never broad test/review data deletion.
            $ids = ArabicSkillSession::where('user_id', $student->id)->pluck('id');
            DB::table('arabic_skill_session_questions')->whereIn('session_id', $ids)->delete();
            DB::table('arabic_skill_sessions')->whereIn('id', $ids)->delete();
            DB::table('arabic_skill_questions')->where('bank_id', $bank->id)->delete();
            DB::table('arabic_skill_banks')->where('id', $bank->id)->delete();
            DB::table('users')->where('id', $student->id)->delete();
            DB::beginTransaction();
        }
    }
}
