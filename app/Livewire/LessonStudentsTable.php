<?php

namespace App\Livewire;

use App\Models\Lesson;
use App\Models\User;
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

final class LessonStudentsTable extends PowerGridComponent
{
    use WithExport;

    public $lessonId;

    public function setUp(): array
    {

        return [
            
            Header::make()->showSearchInput(),
            Footer::make()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return User::whereHas('lessons', function ($query) {
            $query->where('lesson_id', $this->lessonId);
        })->with(['lessons' => function ($query) {
            $query->where('lesson_id', $this->lessonId);
        }]);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('avatar', fn ($item) => '<img class="w-8 h-8 shrink-0 grow-0 rounded-full" src="' . asset("{$item->avatar}") . '" alt="">')
            ->add('name')
            ->add('email')

            ->add('subscription_date', function ($item) {
                $lesson = $item->lessons->first();
                return $lesson ? $lesson->pivot->created_at->diffForHumans() : null;
            });
    }

    public function columns(): array
    {
        return [
            Column::make('الصورة', 'avatar'),

            Column::make('الإسم', 'name')
                ->searchable(),

            Column::make('البريد الإلكتروني', 'email')
                ->searchable(),
            Column::add()
                ->title('تاريخ الاشتراك')
                ->field('subscription_date')
                ->sortable(),

            Column::action('الإجراءات')
        ];
    }

    public function filters(): array
    {
        return [];
    }

    

    public function actions(User $row): array
    {
        return [
            Button::add('remove')
                ->render(function ($row) {
                    $url = route('admin.lessons.detach', ['lesson_id' => $this->lessonId, 'student_id' => $row->id]);
                    $params = json_encode(['lesson_id' => $this->lessonId]);

                    return Blade::render(<<<HTML
                    <x-delete-confirmation 
                        url="{$url}"
                        params='{$params}'
                        elementName="طالب" 
                        class="flex gap-3 py-1 px-2 me-1 rounded-lg bg-red-400 text-white border border-red-500 hover:bg-red-500 transition-all duration-300 ease-in-out"
                    >
                        <p> إزالة الطالب</p>
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
