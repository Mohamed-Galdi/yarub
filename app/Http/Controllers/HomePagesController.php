<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomePagesController extends Controller
{
    public function homePage(){
        return view('home.home');
    }

    public function coursesPage(){
        return view('home.courses.courses_list');
    }

    public function lessonsPage(){
        return view('home.lessons.lessons_list');
    }

    public function aboutPage(){
        return view('home.about');
    }

    public function contactPage(){
        return view('home.contact');
    }
}
