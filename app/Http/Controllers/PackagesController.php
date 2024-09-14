<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class PackagesController extends Controller
{
    public function packagesPage(Request $request)
    {
        $search = $request->input('search');
        $selectedType = $request->input('type');

        $query = app('App\Models\Package')->where('is_active', true)
            ->withCount('courses')
            ->withCount('lessons');

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if ($selectedType && $selectedType !== 'الكل') {
            $query->where('type', $selectedType);
        }

        $packages = $query->paginate(9);

        // Get all unique types from the filtered packages
        $availableTypes = app('App\Models\Package')->where('is_active', true)
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            })
            ->pluck('type')
            ->unique()
            ->sort()
            ->values();
        // dd($availableTypes, $selectedType, $search, $packages);

        return view('home.packages.packages_list', compact('packages', 'search', 'availableTypes', 'selectedType'));
    }

    public function packagePage($package)
    {
        $package = Package::with('courses', 'lessons')->find($package);
        return view('home.packages.package_page', compact('package'));
    }
}
