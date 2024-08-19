<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brand.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brand.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            $brand = new Brand();
            // Handle the image upload
            $brand->name = $request->name;
            $brand->slug = Str::slug($request->name);
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = uniqid() . "brandImage." . $image->extension();
                // Move the new image
                $image->storeAs('public/brandImage', $imageName);
                $this->GenerateBrandThumbnailsImage($image, $imageName);
                $brand->image = $imageName;
                $brand->save();
            }
            $brand->created_by = Auth::user()->name;
            $brand->save();

            return redirect()->route('admin.brands')->with('success', 'Brand added successfully');
        } catch (\Exception $e) {
            Log::error('Failed to save brand: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to created Brand');
        }
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('admin.brand.edit', compact('brand'));
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $id . ',id',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            $brand = Brand::findOrFail($id);
            // Handle the image upload
            $brand->name = $request->name;
            $brand->slug = Str::slug($request->name);
            if ($request->hasFile('image')) {
                // Delete the old image if exists
                if ($brand->image && Storage::exists('public/brandImage/' . $brand->image)) {
                    Storage::delete('public/brandImage/' . $brand->image);
                    Storage::delete('public/brandImage/brandThumbnails/' . $brand->image);
                }

                // Handle the new image upload
                $image = $request->file('image');
                $imageName = uniqid() . "brandImage." . $image->extension();

                // Generate thumbnails
                $this->GenerateBrandThumbnailsImage($image, $imageName);

                // Store the new image
                $image->storeAs('public/brandImage', $imageName);
                $brand->image = $imageName;
            }

            $brand->created_by = Auth::user()->name;
            $brand->save();

            return redirect()->route('admin.brands')->with('success', 'Brand updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to save brand: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to updated Brand');
        }
    }

    public function delete($id)
    {

        $brand = Brand::findOrFail($id);
        if (Storage::exists('public/brandImage/' . $brand->image)) {
            Storage::delete('public/brandImage/' . $brand->image);
            Storage::delete('public/brandImage/brandThumbnails/' . $brand->image);
        }

        $brand->delete();
        return redirect()->back()->with('success', 'Brand deleted successfully');
    }

    public function GenerateBrandThumbnailsImage($image, $imageName)
    {
        $destinationPath = storage_path('app/public/brandImage/brandThumbnails');
        // Ensure the directory exists
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }
        $img = Image::read($image->path());
        $img->cover(124, 124, "top");
        $img->resize(540, 689, function ($constraint) {
            $constraint->upsize();
        })->save($destinationPath . '/' . $imageName);
    }
}
