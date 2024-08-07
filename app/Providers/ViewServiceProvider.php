<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use App\Models\ContactPage;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        View::composer('layouts.guest', function ($view) {
            $contactPage = ContactPage::first();

            $view->with([
                'whatsapp_number' => $contactPage->whatsapp_number,
                'instagram' => $contactPage->instagram,
                'tiktok' => $contactPage->tiktok,
                'snapchat' => $contactPage->snapchat,
                'commercial_registration_no' => $contactPage->commercial_registration_no,
            ]);
        });
    }
}
