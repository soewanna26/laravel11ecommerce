<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::orderBy('id', 'desc')->paginate(12);
        return view('admin.slider.index', compact('sliders'));
    }

    public function create()
    {
        return view('admin.slider.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            $slider = new Slider();
            // Handle the image upload
            $slider->tagline = $request->tagline;
            $slider->title = $request->title;
            $slider->subtitle = $request->subtitle;
            $slider->link = $request->link;
            $slider->status = $request->status;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = uniqid() . "sliderImage." . $image->extension();
                // Move the new image
                $image->storeAs('public/sliderImage', $imageName);
                $this->GenerateSliderThumbnailsImage($image, $imageName);
                $slider->image = $imageName;
                $slider->save();
            }
            $slider->created_by = Auth::user()->name;
            $slider->save();

            return redirect()->route('admin.sliders')->with('success', 'Slider added successfully');
        } catch (\Exception $e) {
            Log::error('Failed to save slider: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to created Slider');
        }
    }
    public function edit($id)
    {
        $slider = Slider::findOrFail($id);
        return view('admin.slider.edit', compact('slider'));
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            $slider = Slider::findOrFail($id);
            $slider->tagline = $request->tagline;
            $slider->title = $request->title;
            $slider->subtitle = $request->subtitle;
            $slider->link = $request->link;
            $slider->status = $request->status;

            if ($request->hasFile('image')) {
                // Delete the old image if exists
                if ($slider->image && Storage::exists('public/sliderImage/' . $slider->image)) {
                    Storage::delete('public/sliderImage/' . $slider->image);
                    Storage::delete('public/sliderImage/sliderThumbnails/' . $slider->image);
                }

                // Handle the new image upload
                $image = $request->file('image');
                $imageName = uniqid() . "sliderImage." . $image->extension();

                // Generate thumbnails
                $this->GeneratesliderThumbnailsImage($image, $imageName);

                // Store the new image
                $image->storeAs('public/sliderImage', $imageName);
                $slider->image = $imageName;
            }

            $slider->created_by = Auth::user()->name;
            $slider->save();

            return redirect()->route('admin.sliders')->with('success', 'Slider updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to save slider: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to updated Slider');
        }
    }

    public function delete($id)
    {

        $slider = slider::findOrFail($id);
        if (Storage::exists('public/sliderImage/' . $slider->image)) {
            Storage::delete('public/sliderImage/' . $slider->image);
            Storage::delete('public/sliderImage/sliderThumbnails/' . $slider->image);
        }

        $slider->delete();
        return redirect()->back()->with('success', 'Slider deleted successfully');
    }
    public function GenerateSliderThumbnailsImage($image, $imageName)
    {
        $destinationPath = storage_path('app/public/sliderImage/sliderThumbnails');
        // Ensure the directory exists
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }
        $img = Image::read($image->path());
        $img->resize(400, 690, function ($constraint) {
            $constraint->upsize();
        })->save($destinationPath . '/' . $imageName);
    }
}
