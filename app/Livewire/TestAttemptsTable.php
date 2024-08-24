<?php

namespace App\Livewire;

use App\Models\TestAttempt;
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

final class TestAttemptsTable extends PowerGridComponent
{
    use WithExport;

    public $testId;

    public function setUp(): array
    {
        return [
            Exportable::make(fileName: 'لائحة إجتيازات الإختبارات')
            ->type(Exportable::TYPE_XLS),
            Header::make()->showSearchInput(),
            Footer::make()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return TestAttempt::query()->where('test_id', $this->testId)->with(['test.questions', 'user']);
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
            ->add('test_title', function ($item) {
                return $item->test->title;
            })
            ->add('test_type', function ($item) {
                return $item->test->type;
            })
            ->add('score', fn ($item) => $item->score. ' % ')

            ->add('attempt_at', function ($item) {
                return $item->created_at->format('d/m/Y');
            });
    }

    public function columns(): array
    {
        return [
            Column::make('إسم الطالب (ة)', 'student_name')
            ->searchable(),

            Column::make('عنوان الإختبار', 'test_title')
                ->searchable(),

            Column::make('نوع الإختبار', 'test_type'),

            Column::make('النتيجة', 'score'),

            Column::add()
                ->title('تاريخ الإجتياز')
                ->field('attempt_at')
                ->sortable(),

            Column::action('الإجراءات')->visibleInExport(visible: false)
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert('.$rowId.')');
    }

    public function actions(TestAttempt $row): array
    {
        return [
            Button::add('view')
                ->render(function ($row) {
                    $url = route('admin.test_attempt', ['id' => $row->id]);
                    return Blade::render(<<<HTML
                    <a href="{$url}"  > 
                        <div  class="flex items-center gap-3 py-1 px-2 me-1 rounded-lg bg-indigo-400 text-gray-100 border border-gray-100 hover:bg-indigo-500 hover:text-white transition-all duration-300 ease-in-out cursor-pointer">
                            <p> عرض </p>
                            <x-icons.test class="w-4 h-4" />
                        </div>
                    </a>
                    
                HTML);
                })
                ->route('admin.test_attempt', ['id' => $row->id]),
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
