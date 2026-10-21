<?php

namespace App\Livewire;

use App\Models\QudratCategory;
use App\Models\QudratQuestion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Footer;
use PowerComponents\LivewirePowerGrid\Header;
use PowerComponents\LivewirePowerGrid\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class QudratQuestionsTable extends PowerGridComponent
{
    public string $tableName = 'qudrat-questions';

    #[Locked]
    public ?int $categoryId = null;

    #[Locked]
    public ?int $passageId = null;

    public function boot(): void
    {
        // Runs on both initial rendering and every Livewire update/action.
        Gate::authorize('viewAny', QudratQuestion::class);
    }

    public function setUp(): array
    {
        return [Header::make()->showSearchInput(), Footer::make()->showPerPage(20, [10, 20, 50])->showRecordCount()];
    }

    public function datasource(): Builder
    {
        Gate::authorize('viewAny', QudratQuestion::class);

        return QudratQuestion::with('category')
            ->when($this->categoryId, fn ($query, $id) => $query->where('category_id', $id))
            ->when($this->passageId, fn ($query, $id) => $query->where('passage_id', $id))
            ->orderBy(QudratCategory::select('sort_order')->whereColumn('qudrat_categories.id', 'qudrat_questions.category_id'))
            ->orderBy('qudrat_questions.sort_order')->orderBy('qudrat_questions.id');
    }

    public function relationSearch(): array
    {
        return ['options' => ['text']];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()->add('id')->add('sort_order')
            ->add('prompt_display', fn ($question) => e(Str::limit($question->prompt ?: 'سؤال غير مكتمل', 110)))
            ->add('category_title', fn ($question) => e($question->category->title))
            ->add('publication', fn ($question) => $question->archived_at ? 'مؤرشف' : 'منشور');
    }

    public function columns(): array
    {
        return [Column::make('الترتيب', 'sort_order'), Column::make('السؤال', 'prompt_display', 'prompt')->searchable(),
            Column::make('القسم', 'category_title', 'category_id'), Column::make('الحالة', 'publication', 'is_published'),
            Column::action('الإجراءات')];
    }

    public function filters(): array
    {
        return [
            Filter::select('category_title', 'category_id')->dataSource(QudratCategory::orderBy('sort_order')->get(['id', 'title']))->optionValue('id')->optionLabel('title'),
            Filter::select('publication', 'is_published')->dataSource([
                ['id' => 'published', 'title' => 'منشور'], ['id' => 'archived', 'title' => 'مؤرشف'],
            ])->optionValue('id')->optionLabel('title')->builder(function (Builder $query, $value) {
                if ($value === 'archived') {
                    return $query->whereNotNull('archived_at');
                }

                return $query->whereNull('archived_at')->where('is_published', true);
            }),
        ];
    }

    public function actions(QudratQuestion $row): array
    {
        return [Button::add('edit')->slot('تحرير')->class('qudrat-table-link')->route('admin.qudrat.questions.edit', ['question' => $row->id]),
            Button::add('preview')->slot('معاينة')->class('qudrat-table-link')->route('admin.qudrat.questions.preview', ['question' => $row->id])];
    }
}
