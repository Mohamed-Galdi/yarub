<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentsExport implements FromCollection, WithStyles, ShouldAutoSize, WithEvents, WithHeadings, WithMapping
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
        return Payment::query()->whereBetween('created_at', [$this->start_date, $this->end_date])->with('user')->get();
    }

    public function map($payment): array
    {
        return [
            $payment->id,
            $payment->user->name ?? 'N/A',
            $payment->payment_status == 'paid' ? 'مدفوع' : 'غير مدفوع',
            $payment->original_amount,
            $payment->coupon_used,
            $payment->coupon_reduction,
            $payment->payment_amount,
            $payment->created_at->toDateTimeString()
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'المشترك',
            'حالة الدفع',
            'المبلغ الأصلي',
            'القسيمة المستخدمة',
            'خصم القسيمة',
            'المبلغ المدفوع',
            'تاريخ التسجيل',
            
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
