<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
use Illuminate\Support\Facades\Blade;


final class StudentsTable extends PowerGridComponent
{
    use WithExport;

    public function setUp(): array
    {
        // $this->showCheckBox();

        return [
            Exportable::make(fileName: 'لائحة الطلاب')
            ->type(Exportable::TYPE_XLS), 
            Header::make()->showSearchInput(),
            Footer::make()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return User::query()->where('role', '=', 'student');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('name')
            ->add('email')
            ->add('role')
            ->add('avatar', fn ($item) => '<img class="w-8 h-8 shrink-0 grow-0 rounded-full" src="' . asset("{$item->avatar}") . '" alt="">')
            ->add('created_at', fn ($item) => Carbon::parse($item->created_at->format('Y-m-d')))
            ->add('created_at_formatted', fn ($item) => Carbon::parse($item->created_at)->diffForhumans());
    }

    public function columns(): array
    {
        return [

            Column::make('الصورة', 'avatar')->visibleInExport(visible: false),

            Column::make('الإسم', 'name')
                ->sortable()
                ->searchable(),

            Column::make('البريد الإلكتروني', 'email')
                ->sortable()
                ->searchable(),

            Column::make('تاريخ', 'created_at')->hidden()->visibleInExport(visible: true),


            Column::add()
                ->title('تاريخ الإضافة')
                ->field('created_at_formatted')
                ->sortable()->visibleInExport(visible: false),

            Column::action('الإجراءات')->visibleInExport(visible: false),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    #[\Livewire\Attributes\On('view')]
    public function edit($rowId): void
    {
    }

    public function actions(User $row): array
    {
        return [
            Button::add('view')
                ->render(function ($row) {
                    $url = route('admin.view_student', ['id' => $row->id]);
                    return Blade::render(<<<HTML
                    <a href="{$url}"  > 
                        <div  class="flex items-center gap-3 py-1 px-2 me-1 rounded-lg bg-indigo-400 text-gray-100 border border-gray-100 hover:bg-indigo-500 hover:text-white transition-all duration-300 ease-in-out cursor-pointer">
                            <p> عرض الطالب</p>
                            <x-icons.user class="w-4 h-4" />
                        </div>
                    </a>
                    
                HTML);
                })
                ->route('admin.view_student', ['id' => $row->id]),

            Button::add('remove')
                ->render(function ($row) {
                    $url = route('admin.students.delete', ['student_id' =>  $row->id]);
                    return Blade::render(<<<HTML
                    <x-delete-confirmation 
                        url="{$url}"
                        elementName="طالب" 
                        class="flex items-center gap-3 py-1 px-2 me-1 rounded-lg bg-red-400 text-white border border-red-500 hover:bg-red-500 transition-all duration-300 ease-in-out"
                    >
                        <p> حظر الطالب</p>
                        <x-icons.remove-user class="w-5 h-5" />
                    </x-delete-confirmation>
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
