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


final class DeletedStudentsTable extends PowerGridComponent
{
    use WithExport;

    public function setUp(): array
    {
        return [
            Exportable::make(fileName: ' لائحة المشتركين المحذوفين')
                ->type(Exportable::TYPE_XLS),

            Header::make()->showSearchInput(),
            Footer::make()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return User::onlyTrashed();
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
            ->add('avatar', fn($item) => '<img class="w-8 h-8 shrink-0 grow-0 rounded-full" src="' . asset("{$item->avatar}") . '" alt="">')
            ->add('created_at', fn($item) => Carbon::parse($item->created_at))
            ->add('created_at_formatted', fn($item) => Carbon::parse($item->created_at)->diffForhumans());
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

            Column::action('الإجراءات')->visibleInExport(visible: false)
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

    public function actions(User $row): array
    {
        return [
            Button::add('restore')
                ->render(function ($row) {
                    $url = route('admin.students.restore', ['student_id' =>  $row->id]);
                    return Blade::render(<<<HTML
                    <x-delete-confirmation
                        title="هل أنت متأكد من إزالة هذا الطالب من الحظر؟"
                        confirmButtonText="نعم، قم بالإزالة!"
                        url="{$url}"
                        elementName="طالب" 
                        class="flex items-center gap-3 py-1 px-2 me-1 rounded-lg bg-green-400 text-white border border-green-500 hover:bg-green-500 transition-all duration-300 ease-in-out"
                    >
                        <p> إزالة الحظر </p>
                        <x-icons.square-check class="w-5 h-5" />
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
