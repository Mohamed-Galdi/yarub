<?php

namespace App\Services\Admin\Tracking;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UsersListState
{
    public static function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'status' => ['nullable', Rule::in(['active', 'blocked', 'all'])],
            'participation' => ['nullable', Rule::in(['all', 'qudrat', 'arabic_skills', 'neither'])],
            'sort' => ['nullable', Rule::in(['joined', 'last_practice', 'qudrat_coverage', 'arabic_skills_coverage'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])], 'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public static function normalize(array $input): array
    {
        $data = Validator::make($input, self::rules())->validate();

        return ['search' => trim($data['search'] ?? ''), 'status' => $data['status'] ?? 'active',
            'participation' => $data['participation'] ?? 'all', 'sort' => $data['sort'] ?? 'joined',
            'direction' => $data['direction'] ?? 'desc', 'per_page' => (int) ($data['per_page'] ?? 20),
            'page' => (int) ($data['page'] ?? 1)];
    }
}
