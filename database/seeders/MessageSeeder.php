<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Carbon\Carbon;

class MessageSeeder extends Seeder
{
    public function run()
    {
        // select 3 randon non admin users
        $students = User::where('role', 'student')->inRandomOrder()->take(3)->get();

        $subjects = [
            'استفسار عن الدورة التدريبية',
            'سؤال حول الواجب المنزلي',
            'طلب تمديد موعد التسليم',
            'مشكلة في الوصول إلى المحتوى',
            'استفسار عن موعد الاختبار القادم'
        ];

        foreach ($students as $index => $student) {
            // Create a conversation for each student
            $conversation = Conversation::create([
                'student_id' => $student->id,
                'subject' => $subjects[$index],
                'status' => 'open',
                'admin_unread_count' => 0,
                'last_message_at' => now(),
            ]);

            // Create some messages for each conversation
            $messages = $this->getMessagesBySubject($subjects[$index]);

            $messageCount = count($messages);
            foreach ($messages as $i => $message) {
                $createdAt = Carbon::now()->subHours($messageCount - $i);
                $newMessage = Message::create([
                    'conversation_id' => $conversation->id,
                    'sender' => $message['sender'],
                    'content' => $message['content'],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // Update conversation's last_message_at
                $conversation->update(['last_message_at' => $createdAt]);

                // Increment admin_unread_count for student messages
                if ($message['sender'] === 'student') {
                    $conversation->increment('admin_unread_count');
                }
            }
        }
    }

    private function getMessagesBySubject($subject)
    {
        switch ($subject) {
            case 'استفسار عن الدورة التدريبية':
                return [
                    ['sender' => 'student', 'content' => 'السلام عليكم، هل يمكنني الحصول على معلومات إضافية عن محتوى الدورة؟'],
                    ['sender' => 'admin', 'content' => 'وعليكم السلام، بالطبع! الدورة تغطي أساسيات النحو العربي، بما في ذلك الإعراب والبناء. هل هناك جزء معين تريد معرفة المزيد عنه؟'],
                    ['sender' => 'student', 'content' => 'نعم، أنا مهتم بشكل خاص بموضوع الإعراب. هل ستكون هناك تمارين عملية؟'],
                    ['sender' => 'admin', 'content' => 'بالتأكيد! سنقدم العديد من التمارين العملية لتطبيق قواعد الإعراب. سنبدأ بأمثلة بسيطة ثم ننتقل إلى جمل أكثر تعقيدًا.'],
                    ['sender' => 'student', 'content' => 'هذا رائع! شكرًا جزيلاً على المعلومات.'],
                    ['sender' => 'admin', 'content' => 'على الرحب والسعة! إذا كان لديك أي أسئلة أخرى، لا تتردد في السؤال. نحن هنا لمساعدتك.'],
                ];

            case 'سؤال حول الواجب المنزلي':
                return [
                    ['sender' => 'student', 'content' => 'مرحبًا، لدي سؤال حول الواجب المنزلي الأخير في درس الصرف.'],
                    ['sender' => 'admin', 'content' => 'أهلاً بك! ما هو سؤالك تحديدًا؟'],
                    ['sender' => 'student', 'content' => 'في السؤال الثالث، هل المطلوب تصريف الفعل "وعد" في جميع الأزمنة؟'],
                    ['sender' => 'admin', 'content' => 'نعم، صحيح. المطلوب هو تصريف الفعل "وعد" في الماضي والمضارع والأمر.'],
                    ['sender' => 'student', 'content' => 'شكرًا على التوضيح. هل يمكنك إعطائي مثالاً للتأكد من فهمي؟'],
                    ['sender' => 'admin', 'content' => 'بالطبع! مثلاً: الماضي "وعَدَ"، المضارع "يَعِدُ"، الأمر "عِدْ". هل هذا يساعدك؟'],
                    ['sender' => 'student', 'content' => 'نعم، هذا يساعد كثيرًا. شكرًا جزيلاً!'],
                    ['sender' => 'admin', 'content' => 'ممتاز! سعيد أنني استطعت المساعدة. إذا واجهت أي صعوبات أخرى، لا تتردد في السؤال.'],
                ];

                // Add more cases for other subjects...

            default:
                return [
                    ['sender' => 'student', 'content' => 'السلام عليكم، لدي استفسار بخصوص الدورة.'],
                    ['sender' => 'admin', 'content' => 'وعليكم السلام، كيف يمكنني مساعدتك؟'],
                    ['sender' => 'student', 'content' => 'هل يمكنك تزويدي بمزيد من المعلومات عن موضوع الدرس القادم؟'],
                    ['sender' => 'admin', 'content' => 'بالتأكيد! الدرس القادم سيكون عن أساليب البلاغة في اللغة العربية. سنتناول المجاز والاستعارة والكناية.'],
                    ['sender' => 'student', 'content' => 'شكرًا جزيلاً على المعلومات. هل هناك قراءات تحضيرية مقترحة؟'],
                    ['sender' => 'admin', 'content' => 'نعم، يمكنك الاطلاع على الفصل الخامس من كتاب "البلاغة الواضحة" لعلي الجارم ومصطفى أمين. سيكون مفيدًا كخلفية للدرس.'],
                ];
        }
    }
}
