<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Slider;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $sliders = Slider::where("status", 1)->get()->take(3);
        $categories = Category::orderBy('name')->get();
        return view('index', compact('sliders','categories'));
    }
}
