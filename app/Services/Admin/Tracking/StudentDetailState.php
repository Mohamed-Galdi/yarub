<?php

namespace App\Services\Admin\Tracking;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentDetailState
{
    public const TABS = ['overview', 'qudrat', 'arabic_skills', 'courses', 'lessons', 'tests', 'certificates'];

    public static function normalize(array $input): array
    {
        $rules = ['tab' => ['nullable', Rule::in(self::TABS)]];
        foreach (array_diff(self::TABS, ['overview']) as $tab) {
            $rules[$tab.'_page'] = 'nullable|integer|min:1';
        }
        foreach (StudentTracking::PROGRAMS as $program) {
            $rules[$program.'_type'] = 'nullable|integer|min:1';
        }
        $data = Validator::make($input, $rules)->validate();
        $state = ['tab' => $data['tab'] ?? 'overview'];
        foreach (array_diff(self::TABS, ['overview']) as $tab) {
            $state[$tab.'_page'] = (int) ($data[$tab.'_page'] ?? 1);
        }
        foreach (StudentTracking::PROGRAMS as $program) {
            if (isset($data[$program.'_type'])) {
                $state[$program.'_type'] = (int) $data[$program.'_type'];
            }
        }

        return $state;
    }
}
