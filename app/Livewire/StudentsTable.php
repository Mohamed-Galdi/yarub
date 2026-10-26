<?php

namespace App\Livewire;

use App\Exports\StudentsListExport;
use App\Models\User;
use App\Services\Admin\Tracking\StudentTracking;
use App\Services\Admin\Tracking\UsersListState;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

final class StudentsTable extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'active')]
    public string $status = 'active';

    #[Url(except: 'all')]
    public string $participation = 'all';

    #[Url(except: 'joined')]
    public string $sort = 'joined';

    #[Url(except: 'desc')]
    public string $direction = 'desc';

    #[Url(as: 'per_page', except: 20)]
    public int $perPage = 20;

    public function boot(): void
    {
        Gate::authorize('view-student-tracking');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'participation', 'sort', 'direction', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'status', 'participation', 'sort', 'direction', 'perPage');
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        abort_unless(in_array($column, ['joined', 'last_practice', 'qudrat_coverage', 'arabic_skills_coverage'], true), 422);
        $this->direction = $this->sort === $column && $this->direction === 'desc' ? 'asc' : 'desc';
        $this->sort = $column;
        $this->resetPage();
    }

    #[On('student-account-updated')]
    public function refreshRows(): void {}

    public function state(): array
    {
        return UsersListState::normalize(['search' => $this->search, 'status' => $this->status,
            'participation' => $this->participation, 'sort' => $this->sort, 'direction' => $this->direction,
            'per_page' => $this->perPage, 'page' => $this->getPage()]);
    }

    public function exportToXLS()
    {
        Gate::authorize('view-student-tracking');

        return Excel::download(new StudentsListExport($this->state()), 'لائحة المشتركين.xlsx');
    }

    public function render()
    {
        $state = $this->state();
        $students = null;
        $loadError = false;
        $blockedCount = 0;
        try {
            $students = app(StudentTracking::class)->directory($state);
            if ($students->isEmpty() && $this->getPage() > $students->lastPage()) {
                $this->setPage($students->lastPage());
                $state = $this->state();
                $students = app(StudentTracking::class)->directory($state);
            }
            $blockedCount = User::onlyTrashed()->where('role', 'student')->count();
        } catch (\Illuminate\Database\QueryException $exception) {
            Log::warning('Admin users directory query failed.', ['exception' => $exception]);
            $loadError = true;
        }

        return view('livewire.students-table', compact('students', 'loadError', 'blockedCount', 'state'));
    }
}
