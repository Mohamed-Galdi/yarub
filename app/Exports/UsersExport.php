<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;



class UsersExport implements FromCollection, WithStyles, ShouldAutoSize, WithEvents, WithHeadings, WithMapping
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
        return User::query()->where('role', 'student')->whereBetween('created_at', [$this->start_date, $this->end_date])->withTrashed()->get();
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone ?? 'لا يوجد',
            $user->deleted_at == null ? 'محضور' : 'فعال ',
            $user->created_at->toDateTimeString()
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'الإسم',
            'البريد الإلكتروني',
            'الهاتف',
            'حالة الحساب',
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
