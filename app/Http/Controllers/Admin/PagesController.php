<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutPage;
use App\Models\ContactPage;
use App\Models\FAQ;
use App\Models\HomePage;
use App\Models\HomePageReview;
use App\Models\Partners;
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
        $aboutPage = AboutPage::first();
        $partners = Partners::all();
        $faqs = FAQ::all();
        return view('admin.pages.edit-about', compact('aboutPage', 'partners', 'faqs'));
    }

    public function updateAbout(Request $request)
    {
        $aboutPage = AboutPage::first();
        $aboutPage->our_team_content = $request->input('our_team_content');
        $aboutPage->our_goal_content = $request->input('our_goal_content');
        $aboutPage->save();
        Alert::success('تم تعديل الصفحة الرئيسية');
        return back();
    }

    public function addPartner(Request $request)
    {
        $partner = new Partners();
        $partner->name = $request->input('name');
        $partner->url = $request->input('url');
        $partner->save();
        Alert::success('تم إضافة الشريك ');
        return back();
    }

    public function updatePartner(Request $request, $partner_id)
    {
        $partner = Partners::find($partner_id);
        $partner->name = $request->input('name');
        $partner->url = $request->input('url');
        $partner->save();
        Alert::success('تم تعديل الشريك ');
        return back();
    }

    public function deletePartner(Request $request, $partner_id)
    {
        $partner = Partners::find($partner_id);
        $partner->delete();
        Alert::success('تم حذف الشريك ');
        return back();
    }

    public function addFaq(Request $request)
    {
        $faq = new FAQ();
        $faq->question = $request->input('question');
        $faq->answer = $request->input('answer');
        $faq->save();
        Alert::success('تم إضافة السؤال');
        return back();
    }

    public function updateFaq(Request $request, $faq_id)
    {
        $faq = FAQ::find($faq_id);
        $faq->question = $request->input('question');
        $faq->answer = $request->input('answer');
        $faq->save();
        Alert::success('تم تعديل السؤال');
        return back();
    }

    public function deleteFaq(Request $request, $faq_id)
    {
        $faq = FAQ::find($faq_id);
        $faq->delete();
        Alert::success('تم حذف السؤال');
        return back();
    }

    public function editContact()
    {
        $contactPage = ContactPage::first();
        return view('admin.pages.edit-contact', compact('contactPage'));
    }

    public function updateContact(Request $request)
    {
        $contactPage = ContactPage::first();
        $contactPage->commercial_registration_no = $request->input('commercial_registration_no');
        $contactPage->phone_number = $request->input('phone_number');
        $contactPage->email = $request->input('email');
        $contactPage->address = $request->input('address');
        $contactPage->whatsapp_number = $request->input('whatsapp_number');
        $contactPage->instagram = $request->input('instagram');
        $contactPage->tiktok = $request->input('tiktok');
        $contactPage->snapchat = $request->input('snapchat');
        $contactPage->save();
        Alert::success('تم تعديل صفحة التواصل معنا');
        return back();
    }
}
