<?php

namespace App\Livewire;

use App\Models\Certificate;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Footer;
use PowerComponents\LivewirePowerGrid\Header;
use PowerComponents\LivewirePowerGrid\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class CertificatesTable extends PowerGridComponent
{
    use WithExport;

    public function setUp(): array
    {

        return [
            Exportable::make('export')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            Header::make()->showSearchInput(),
            Footer::make()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Certificate::query()->with(['user', 'course', 'lesson']);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('student_name', function ($item) {
                return $item->user->name;
            })
            ->add('content_type', function ($item) {
                if ($item->course) {
                    return 'الدروس';
                }
                if ($item->lesson) {
                    return 'الشروحات';
                }
            })
            ->add('content_title', function ($item) {
                if ($item->course) {
                    return $item->course->title;
                }
                if ($item->lesson) {
                    return $item->lesson->title;
                }
            })
            ->add('assigned_at', function ($item) {
                return $item->created_at->format('d/m/Y');
            });
    }

    public function columns(): array
    {
        return [
            Column::make('إسم الطالب (ة)', 'student_name')
                ->searchable(),
            Column::make('نوع المادة', 'content_type'),
            Column::make('عنوان الدرس/ الشرح', 'content_title')
                ->searchable(),
            Column::add()
                ->title('تاريخ المنح')
                ->field('assigned_at')
                ->sortable(),

            Column::action('الإجراءات')
        ];
    }

    public function filters(): array
    {
        return [];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }

    public function actions(Certificate $row): array
    {
        return [
            Button::add('view')
                ->render(function ($row) {
                    $url = route('admin.certificates.view', ['id' => $row->id]);
                    return Blade::render(<<<HTML
                    <a href="{$url}" target="_blank">
                        <div  class="flex items-center gap-3 py-1 px-2 me-1 rounded-lg bg-indigo-400 text-gray-100 border border-gray-100 hover:bg-indigo-500 hover:text-white transition-all duration-300 ease-in-out cursor-pointer">
                            <p> عرض </p>
                            <x-icons.eye class="w-4 h-4" />
                        </div>
                    </a>
                    
                HTML);
                }),
            Button::add('download')
                ->render(function ($row) {
                    $url = route('admin.certificates.download', ['id' => $row->id]);
                    return Blade::render(<<<HTML
                    <a href="{$url}" target="_blank">
                        <div  class="flex items-center gap-3 py-1 px-2 me-1 rounded-lg bg-green-400 text-gray-100 border border-gray-100 hover:bg-green-500 hover:text-white transition-all duration-300 ease-in-out cursor-pointer">
                            <p> تنزيل </p>
                            <x-icons.download class="w-4 h-4" />
                        </div>
                    </a>
                    
                HTML);
                }),
        ];
    }

    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
