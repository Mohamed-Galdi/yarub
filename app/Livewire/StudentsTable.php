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

final class StudentsTable extends PowerGridComponent
{
    use WithExport;

    public function setUp(): array
    {
        // $this->showCheckBox();

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
            ->add('created_at', fn ($item) => Carbon::parse($item->created_at))
            ->add('created_at_formatted', fn ($item) => Carbon::parse($item->created_at)->diffForhumans());
    }

    public function columns(): array
    {
        return [

            Column::make('الصورة', 'avatar'),

            Column::make('الإسم', 'name')
                ->sortable()
                ->searchable(),

            Column::make('البريد الإلكتروني', 'email')
                ->sortable()
                ->searchable(),



            Column::add()
                ->title('تاريخ الإضافة')
                ->field('created_at_formatted')
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

    public function actions(User $row): array
    {
        return [
            Button::add('view')
                ->slot('<x-icons.user class="w-4 h-4" />')
                ->id()
                ->class('py-2 px-3 me-1 rounded-lg bg-white text-blue-500 border border-blue-500 hover:bg-blue-500 hover:text-white transition-all duration-300 ease-in-out')
                ->dispatch('edit', ['rowId' => $row->id]),
            Button::add('edit')
                ->slot('<x-icons.edit class="w-4 h-4" />')
                ->id()
                ->class('py-2 px-3 me-1 rounded-lg bg-white text-yellow-500 border border-yellow-500 hover:bg-yellow-500 hover:text-white transition-all duration-300 ease-in-out ')
                ->dispatch('edit', ['rowId' => $row->id]),
            Button::add('delete')
                ->slot('<x-icons.trash class="w-4 h-4" />')
                ->id()
                ->class('py-2 px-3 me-1 rounded-lg bg-white text-red-500 border border-red-500 hover:bg-red-500 hover:text-white transition-all duration-300 ease-in-out ')
                ->dispatch('edit', ['rowId' => $row->id])
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
