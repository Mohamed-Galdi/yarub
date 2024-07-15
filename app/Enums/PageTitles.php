<?php

namespace App\Enums;

enum PageTitles: string
{
    case DASHBOARD = 'إحصائيات المنصة';
    case STUDENTS = 'الطلاب';
    case COURSES = 'الدورات';
    case LESSONS = 'الشروحات';
    case TESTS = 'الإختبارات';
    case CERTIFICATES = 'الشواهد';
    case PAYMENTS = 'المدفوعات';
    case SUPPORT = 'الدعم';
    case DEFAULT = 'لوحة التحكم';

    public static function getTitle(string $path): string
    {
        if (str_contains($path, 'admin-dashboard/students')) {
            return self::STUDENTS->value;
        } elseif (str_contains($path, 'admin-dashboard/courses')) {
            return self::COURSES->value;
        } elseif (str_contains($path, 'admin-dashboard/lessons')) {
            return self::LESSONS->value;
        } elseif (str_contains($path, 'admin-dashboard/tests')) {
            return self::TESTS->value;
        } elseif (str_contains($path, 'admin-dashboard/certificates')) {
            return self::CERTIFICATES->value;
        } elseif (str_contains($path, 'admin-dashboard/payments')) {
            return self::PAYMENTS->value;
        } elseif (str_contains($path, 'admin-dashboard/support')) {
            return self::SUPPORT->value;
        } elseif (str_contains($path, 'admin-dashboard')) {
            return self::DASHBOARD->value;
        } else {
            return self::DEFAULT->value;
        }
    }
}
