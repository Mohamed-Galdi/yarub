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
use Maatwebsite\Excel\DefaultValueBinder;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class StudentsListExport extends DefaultValueBinder implements FromQuery, ShouldAutoSize, WithCustomValueBinder, WithEvents, WithHeadings, WithMapping
{
    public function __construct(private array $filters) {}

    public function query()
    {
        return app(StudentTracking::class)->directoryQuery(UsersListState::normalize($this->filters));
    }

    public function headings(): array
    {
        return ['الإسم', 'البريد الإلكتروني', 'تاريخ التسجيل'];
    }

    public function map($student): array
    {
        return [$student->name, $student->email, $student->created_at?->format('Y-m-d H:i:s')];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

        return true;
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => fn (AfterSheet $event) => $event->sheet->getDelegate()->setRightToLeft(true)];
    }
}
