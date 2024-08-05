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
    case COUPONS = 'القسائم';
    case GUEST_MESSAGES = 'رسائل الزوار';
    case PAGES = 'محتوى الواجهات';
    case DEFAULT = 'لوحة التحكم';

    public static function getTitle(string $path): string
    {
        switch (true) {
            case str_contains($path, 'admin-dashboard/students'):
                return self::STUDENTS->value;
            case str_contains($path, 'admin-dashboard/courses'):
                return self::COURSES->value;
            case str_contains($path, 'admin-dashboard/lessons'):
                return self::LESSONS->value;
            case str_contains($path, 'admin-dashboard/tests'):
                return self::TESTS->value;
            case str_contains($path, 'admin-dashboard/certificates'):
                return self::CERTIFICATES->value;
            case str_contains($path, 'admin-dashboard/payments'):
                return self::PAYMENTS->value;
            case str_contains($path, 'admin-dashboard/support'):
                return self::SUPPORT->value;
            case str_contains($path, 'admin-dashboard/coupons'):
                return self::COUPONS->value;
            case str_contains($path, 'admin-dashboard/guest-messages'):
                return self::GUEST_MESSAGES->value;
            case str_contains($path, 'admin-dashboard/pages'):
                return self::PAGES->value;
            case str_contains($path, 'admin-dashboard'):
                return self::DASHBOARD->value;

            default:
                return self::DEFAULT->value;
        }
    }
}
