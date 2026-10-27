<?php

// Disposable UI fixture setup/cleanup; never boots the owner's real environment.
require __DIR__.'/../vendor/autoload.php';
$case = new class('review') extends Tests\VideoLibraryTestCase {};
$app = $case->createApplication();
$runtime = storage_path('app/qudrat-local/video-admin-review');
$manifest = $runtime.'/fixture.json';
if (! is_dir($runtime)) mkdir($runtime, 0750, true);
config(['filesystems.disks.video_library.root' => $runtime.'/private', 'video_library.minimum_free_bytes' => 0, 'video_library.free_space_reserve_ratio' => 0]);
$files = app(App\Services\VideoLibrary\MediaFiles::class);
$manager = app(App\Services\VideoLibrary\VideoManager::class);
if (in_array($argv[1] ?? '', ['review-session', 'review-admin', 'review-student'], true)) {
    $fixture = json_decode(file_get_contents($manifest), true, 32, JSON_THROW_ON_ERROR);
    if (! isset($fixture['student_id'])) throw new RuntimeException('Session review requires a synthetic student fixture.');
    $fixture['auth_mode'] = substr($argv[1], 7);
    file_put_contents($manifest, json_encode($fixture, JSON_PRETTY_PRINT));
    echo "Disposable review authentication mode updated.\n";
    exit;
}
if (in_array($argv[1] ?? '', ['hide-student', 'show-student'], true)) {
    $fixture = json_decode(file_get_contents($manifest), true, 32, JSON_THROW_ON_ERROR);
    if (! isset($fixture['student_id'])) throw new RuntimeException('This operation requires disposable student review fixtures.');
    $admin = App\Models\User::findOrFail($fixture['admin_id']);
    foreach (App\Models\LibraryVideo::where('uploaded_by', $admin->id)->where('processing_state', 'ready')->get() as $video) {
        $manager->visibility($admin, $video, $argv[1] === 'show-student');
    }
    echo "Disposable student fixture visibility updated.\n";
    exit;
}
if (($argv[1] ?? '') === 'cleanup') {
    if (! is_file($manifest)) exit;
    $fixture = json_decode(file_get_contents($manifest), true, 32, JSON_THROW_ON_ERROR);
    $admin = App\Models\User::findOrFail($fixture['admin_id']);
    foreach (App\Models\LibraryVideo::where('uploaded_by', $admin->id)->get() as $video) $manager->delete($admin, $video, $video->title);
    foreach ($files->disk()->allFiles('objects') as $path) $files->discardLater($path);
    $files->cleanup();
    Illuminate\Support\Facades\DB::table('video_media_operations')->where('admin_id', $admin->id)->delete();
    App\Models\QudratCategory::whereKey($fixture['category_id'])->delete();
    App\Models\ArabicSkillBank::whereKey($fixture['bank_id'])->delete();
    $admin->forceDelete();
    if (isset($fixture['student_id'])) App\Models\User::findOrFail($fixture['student_id'])->forceDelete();
    if (isset($fixture['contact_id'])) App\Models\ContactPage::whereKey($fixture['contact_id'])->delete();
    unlink($manifest);
    echo "Disposable video UI fixtures removed.\n";
    exit;
}
if (! in_array($argv[1] ?? '', ['prepare', 'prepare-student', 'prepare-qa'], true) || is_file($manifest)) throw new RuntimeException('Use prepare, prepare-student or prepare-qa once, or cleanup the existing fixture first.');
$qaReview = $argv[1] === 'prepare-qa';
$studentReview = in_array($argv[1], ['prepare-student', 'prepare-qa'], true);
foreach (['2014_10_12_000000_create_users_table.php', '2024_08_07_135632_create_contact_pages_table.php', '2026_10_21_000001_create_qudrat_tables.php', '2026_10_21_000002_publish_qudrat_content_by_default.php', '2026_10_23_000001_create_arabic_skill_tables.php', '2026_10_26_000001_create_library_videos_tables.php', '2026_10_26_000002_add_video_media_lifecycle.php'] as $migration) {
    if (Illuminate\Support\Facades\Artisan::call('migrate', ['--path' => 'database/migrations/'.$migration, '--force' => true]) !== 0) throw new RuntimeException('Review migration failed.');
}
if (App\Models\LibraryVideo::exists()) throw new RuntimeException('Refusing to mix browser review fixtures with existing video test data.');
$slug = 'video-review-'.Illuminate\Support\Str::uuid();
$admin = App\Models\User::create(['name' => 'مشرف تجريبي', 'email' => $slug.'@example.invalid', 'password' => 'local-video-review', 'role' => 'admin']);
$category = App\Models\QudratCategory::create(['slug' => $slug, 'title' => 'التناظر اللفظي', 'sort_order' => 1]);
$bank = App\Models\ArabicSkillBank::create(['slug' => $slug, 'title' => 'نسق — نوع تجريبي', 'subtitle' => 'بيانات محلية للمراجعة', 'sort_order' => 1, 'source_metadata' => []]);
$fixture = ['admin_id' => $admin->id, 'category_id' => $category->id, 'bank_id' => $bank->id];
$contact = App\Models\ContactPage::firstOrCreate([], array_fill_keys(['commercial_registration_no', 'phone_number', 'email', 'address', 'whatsapp_number', 'instagram', 'tiktok', 'snapchat'], 'local-only'));
if ($contact->wasRecentlyCreated) $fixture['contact_id'] = $contact->id;
if ($studentReview) {
    $student = App\Models\User::create(['name' => 'طالب تجريبي', 'email' => 'review-'.substr($slug, -4).'@example.invalid', 'password' => 'local-video-review', 'role' => 'student', 'avatar' => 'assets/favicon.ico']);
    $fixture['student_id'] = $student->id;
}
file_put_contents($manifest, json_encode($fixture, JSON_PRETTY_PRINT));
$process = new Symfony\Component\Process\Process(['ffmpeg', '-nostdin', '-v', 'error', '-f', 'lavfi', '-i', 'testsrc2=size=270x480:rate=15', '-t', '3', '-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-profile:v', 'main', '-level:v', '3.0', '-movflags', '+faststart', '-y', $runtime.'/portrait.mp4']);
$process->setTimeout(30)->mustRun();
if ($qaReview) {
    // Author a clearly labeled synthetic teaching clip, then use its actual generated poster.
    // Use a Unicode Arabic fixture font supported by the local FFmpeg renderer.
    // The product UI continues using its existing Hacen font.
    $fixtureFont = PHP_OS_FAMILY === 'Windows' ? 'C:/Windows/Fonts/arial.ttf' : resource_path('css/fonts/hacen.ttf');
    if (! is_file($fixtureFont)) throw new RuntimeException('The synthetic QA clip requires an available Arabic font.');
    copy($fixtureFont, $runtime.'/font.ttf');
    file_put_contents($runtime.'/heading.txt', 'العلاقة بين كلمتين');
    file_put_contents($runtime.'/example.txt', 'قلم : كتابة');
    file_put_contents($runtime.'/label.txt', 'مثال محلي للاختبار');
    $filter = 'drawbox=x=22:y=110:w=226:h=250:color=0x292a65:t=fill,drawtext=fontfile=font.ttf:textfile=heading.txt:fontcolor=0x292a65:fontsize=22:x=(w-tw)/2:y=60,drawtext=fontfile=font.ttf:textfile=example.txt:fontcolor=white:fontsize=30:x=(w-tw)/2:y=210,drawtext=fontfile=font.ttf:textfile=label.txt:fontcolor=0x475569:fontsize=16:x=(w-tw)/2:y=400';
    (new Symfony\Component\Process\Process(['ffmpeg', '-nostdin', '-v', 'error', '-f', 'lavfi', '-i', 'color=c=0xf5f1e8:s=270x480:r=15', '-vf', $filter, '-t', '4', '-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-profile:v', 'main', '-level:v', '3.0', '-movflags', '+faststart', '-y', 'portrait.mp4'], $runtime))->setTimeout(30)->mustRun();
}
file_put_contents($runtime.'/invalid.mp4', 'invalid video fixture');
$image = imagecreatetruecolor(270, 480); imagefill($image, 0, 0, imagecolorallocate($image, 41, 42, 101)); imagepng($image, $runtime.'/cover.png'); imagedestroy($image);
$titles = ['كيف نكتشف العلاقة بين كلمتين؟', 'تأمل السياق قبل أن تختار الإجابة: شرح قصير ومثال تطبيقي على العلاقات بين الكلمات', 'أكمل العبارة بما يناسب المعنى'];
foreach ($titles as $i => $title) {
    $video = $manager->upload($admin, ['title' => $title, 'description' => $qaReview && $i === 0 ? null : 'مثال محلي للمراجعة البصرية واختبار الإدارة.', $i === 2 ? 'arabic_skill_bank_id' : 'qudrat_category_id' => $i === 2 ? $bank->id : $category->id],
        new Illuminate\Http\UploadedFile($runtime.'/portrait.mp4', 'portrait.mp4', 'video/mp4', null, true), $i === 2 ? new Illuminate\Http\UploadedFile($runtime.'/cover.png', 'cover.png', 'image/png', null, true) : null, (string) Illuminate\Support\Str::uuid());
    if ($i === 0) {
        $firstVideo = $video;
        app(App\Services\VideoLibrary\PublicExample::class)->select($admin, $video);
        if ($qaReview) {
            $poster = imagecreatefromjpeg($files->path($video->cover_path));
            imagepng($poster, $runtime.'/cover.png'); imagedestroy($poster);
        }
    }
    if ($i === 1 && ! $studentReview) $manager->visibility($admin, $video, false);
}
for ($i = 0; $i < 11; $i++) {
    $copy = ($studentReview && $i % 2 === 0 ? $firstVideo : $video)->replicate();
    $copy->title = 'شرح تجريبي '.($i + 1).' — مهارات قراءة المعنى';
    if ($studentReview) $copy->published_at = now();
    $copy->save();
}
$failed = App\Models\LibraryVideo::create(['title' => 'فيديو يحتاج إلى إعادة المحاولة', 'qudrat_category_id' => $category->id]);
$failed->forceFill(['uploaded_by' => $admin->id, 'processing_state' => 'failed', 'processing_error' => 'تعذّرت معالجة الملف. اختر فيديو صالحًا وأعد المحاولة.'])->save();
echo "Disposable video admin review ready on isolated test schema.\n";
