<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomePage;
use App\Models\HomePageReview;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class PagesController extends Controller
{
    public function index()
    {
        return view('admin.pages.pages');
    }

    public function editHome()
    {
        $homePageReviews = HomePageReview::all();
        $homePage = HomePage::first();
        return view('admin.pages.edit-home', compact('homePage', 'homePageReviews'));
    }

    public function updateHome(Request $request)
    {
        if ($request->form == 'header') {
            $homePage = HomePage::first();
            $homePage->main_title = $request->input('main_title');
            $homePage->sub_title = $request->input('sub_title');
            $homePage->save();
            Alert::success('تم تعديل الصفحة الرئيسية');
            return back();
        } elseif ($request->form == 'features') {
            $homePage = HomePage::first();
            $homePage->our_features_title = $request->input('our_features_title');
            $homePage->first_feature_title = $request->input('first_feature_title');
            $homePage->first_feature_content = $request->input('first_feature_content');
            $homePage->second_feature_title = $request->input('second_feature_title');
            $homePage->second_feature_content = $request->input('second_feature_content');
            $homePage->third_feature_title = $request->input('third_feature_title');
            $homePage->third_feature_content = $request->input('third_feature_content');
            $homePage->save();
            Alert::success('تم تعديل الصفحة الرئيسية');
            return back();
        } elseif ($request->form == 'footer') {
            $homePage = HomePage::first();
            $homePage->last_section_title = $request->input('last_section_title');
            $homePage->last_section_content = $request->input('last_section_content');
            $homePage->save();
            Alert::success('تم تعديل الصفحة الرئيسية');
            return back();
        }
    }

    public function updateReview(Request $request, $review_id)
    {
        // dd($request->all());
        $review = HomePageReview::find($review_id);
        $review->reviewer_name = $request->input('reviewer_name');
        $review->review = $request->input('review_content');
        $review->stars = $request->input('stars');
        if ($request->hasFile('reviewer_image')) {
            $image = $request->file('reviewer_image');
            $path = $image->store('reviewers', 'public');
            $review->reviewer_image = '/storage//' . $path;
        }
        $review->save();
        Alert::success('تم تعديل الصفحة الرئيسية');
        return back();
    }

    public function editAbout()
    {
        return view('admin.pages.edit-about');
    }
}
