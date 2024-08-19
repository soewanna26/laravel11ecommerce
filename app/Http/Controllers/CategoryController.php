<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);
        return view('admin.category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            $category = new Category();
            // Handle the image upload
            $category->name = $request->name;
            $category->slug = Str::slug($request->name);
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = uniqid() . "categoryImage." . $image->extension();
                // Move the new image
                $image->storeAs('public/categoryImage', $imageName);
                $this->GenerateCategoryThumbnailsImage($image, $imageName);
                $category->image = $imageName;
                $category->save();
            }
            $category->created_by = Auth::user()->name;
            $category->save();

            return redirect()->route('admin.categories')->with('success', 'Category added successfully');
        } catch (\Exception $e) {
            Log::error('Failed to save category: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to created Category');
        }
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.category.edit', compact('category'));
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $id . ',id',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            $category = Category::findOrFail($id);
            // Handle the image upload
            $category->name = $request->name;
            $category->slug = Str::slug($request->name);
            if ($request->hasFile('image')) {
                // Delete the old image if exists
                if ($category->image && Storage::exists('public/categoryImage/' . $category->image)) {
                    Storage::delete('public/categoryImage/' . $category->image);
                    Storage::delete('public/categoryImage/categoryThumbnails/' . $category->image);
                }

                // Handle the new image upload
                $image = $request->file('image');
                $imageName = uniqid() . "categoryImage." . $image->extension();

                // Generate thumbnails
                $this->GeneratecategoryThumbnailsImage($image, $imageName);

                // Store the new image
                $image->storeAs('public/categoryImage', $imageName);
                $category->image = $imageName;
            }

            $category->created_by = Auth::user()->name;
            $category->save();

            return redirect()->route('admin.categories')->with('success', 'Category updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to save category: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to updated category');
        }
    }

    public function delete($id)
    {

        $category = Category::findOrFail($id);
        if (Storage::exists('public/categoryImage/' . $category->image)) {
            Storage::delete('public/categoryImage/' . $category->image);
            Storage::delete('public/categoryImage/categoryThumbnails/' . $category->image);
        }

        $category->delete();
        return redirect()->back()->with('success', 'Category deleted successfully');
    }

    public function GenerateCategoryThumbnailsImage($image, $imageName)
    {
        $destinationPath = storage_path('app/public/categoryImage/categoryThumbnails');
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
