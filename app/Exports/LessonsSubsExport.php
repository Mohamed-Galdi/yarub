<?php

namespace App\Exports;

use App\Models\LessonSubscription;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LessonsSubsExport implements FromCollection, WithStyles, ShouldAutoSize, WithEvents, WithHeadings, WithMapping
{
    use RegistersEventListeners;


    /**
     * @return \Illuminate\Support\Collection
     */

    protected $start_date, $end_date;

    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function collection()
    {
        return LessonSubscription::query()->whereBetween('created_at', [$this->start_date, $this->end_date])->with('lesson')->with('user')->get();
    }

    public function map($lesson_subscription): array
    {
        return [
            $lesson_subscription->id,
            $lesson_subscription->user->name ?? 'N/A',
            $lesson_subscription->user->deleted_at == null ? 'فعال' : 'محضور ',
            $lesson_subscription->lesson->title ?? 'N/A',
            $lesson_subscription->lesson->is_published ? 'مفعل' : 'غير مفعل',
            $lesson_subscription->sub_plan == 'annual' ? 'سنوي' : 'شهري',
            $lesson_subscription->cost,
            $lesson_subscription->created_at->toDateTimeString()
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'المشترك',
            'حالة الحساب',
            'الدورة',
            'حالة الدورة',
            'الخطة',
            'مبلغ الإشتراك',
            'تاريخ التسجيل'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => '868686']
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $event->getDelegate()->setRightToLeft(true);
            }
        ];
    }
}
