<?php

namespace App\Exports;

use App\Services\Admin\Tracking\StudentTracking;
use App\Services\Admin\Tracking\UsersListState;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\DefaultValueBinder;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class StudentsListExport extends DefaultValueBinder implements FromQuery, ShouldAutoSize, WithCustomValueBinder, WithEvents, WithHeadings, WithMapping, WithStrictNullComparison
{
    public function __construct(private array $filters) {}

    public function query()
    {
        return app(StudentTracking::class)->directoryQuery(UsersListState::normalize($this->filters))
            ->addSelect('users.email_verified_at')
            ->withCount(['activeCourses', 'activeLessons', 'test_attempts', 'certificates']);
    }

    public function headings(): array
    {
        $headings = ['الإسم', 'البريد الإلكتروني', 'تاريخ التسجيل', 'رقم الطالب', 'حالة الحساب', 'تأكيد البريد الإلكتروني',
            'آخر نشاط تدريبي', 'الدورات المنشورة المشترك بها', 'الشروح المنشورة المشترك بها', 'محاولات الاختبارات', 'الشهادات'];
        foreach (['القدرات', 'مهارات اللغة العربية'] as $program) {
            array_push($headings, $program.' - المشاركة', $program.' - الأسئلة المتاحة', $program.' - الأسئلة المجاب عنها دون تكرار',
                $program.' - تغطية الأسئلة (%)', $program.' - دقة المحاولة الأولى (%)', $program.' - التدريبات المكتملة',
                $program.' - الإجابات المسلّمة مع التكرار', $program.' - آخر نشاط');
        }

        return $headings;
    }

    public function map($student): array
    {
        $summary = app(StudentTracking::class)->present($student);
        $row = [$student->name, $student->email, $student->created_at?->format('Y-m-d H:i:s'), $student->id,
            $student->trashed() ? 'محظور' : 'فعال', $student->email_verified_at ? 'مؤكد' : 'غير مؤكد', $student->last_practice_at,
            (int) $student->active_courses_count, (int) $student->active_lessons_count, (int) $student->test_attempts_count, (int) $student->certificates_count];
        foreach (StudentTracking::PROGRAMS as $program) {
            $metrics = $summary['programs'][$program];
            array_push($row, $metrics['participated'] ? 'بدأ' : 'لم يبدأ', $metrics['available_questions'], $metrics['answered_questions'],
                $metrics['coverage'] ?? 'غير متاح', $metrics['first_attempt_accuracy'] ?? 'غير متاح', $metrics['completed_sessions'],
                (int) $student->getAttribute($program.'_submissions'), $metrics['last_practice_at']);
        }

        return $row;
    }

    public function bindValue(Cell $cell, $value): bool
    {
        $cell->setValueExplicit(is_int($value) || is_float($value) ? $value : (string) $value,
            is_int($value) || is_float($value) ? DataType::TYPE_NUMERIC : DataType::TYPE_STRING);

        return true;
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $sheet->setRightToLeft(true);
            $sheet->freezePane('D2');
            $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
            $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);
        }];
    }
}
