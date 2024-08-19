<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.product.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        return view('admin.product.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:products,slug',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048',
            'category_id' => 'required',
            'brand_id' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            DB::beginTransaction();
            $product = new Product();
            $product->name = $request->name;
            $product->slug = Str::slug($request->name);
            $product->short_description = $request->short_description;
            $product->description = $request->description;
            $product->regular_price = $request->regular_price;
            $product->sale_price = $request->sale_price;
            $product->SKU = $request->SKU;
            $product->quantity = $request->quantity;
            $product->stock_status = $request->stock_status;
            $product->featured = $request->featured;
            $product->category_id = $request->category_id;
            $product->brand_id = $request->brand_id;

            $current_timestamp = Carbon::now()->timestamp;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = $current_timestamp . ".productImage." . $image->extension();
                // Move the new image
                $image->storeAs('public/productImage', $imageName);
                $this->GenerateProductThumbnailsImage($image, $imageName);
                $product->image = $imageName;
            }

            $gallery_arr = array();
            $gallery_images = "";
            $counter = 1;
            if ($request->has('images')) {
                $allowedfileExtion = ['jpg', 'png', 'jpeg'];
                $files = $request->file('images');

                foreach ($files as $file) {
                    $gextension = $file->getClientOriginalExtension();
                    $gcheck = in_array($gextension, $allowedfileExtion);
                    if ($gcheck) {
                        $gfileName = $current_timestamp . "-" . $counter . ".productImage." . $gextension;
                        $this->GenerateProductThumbnailsImage($file, $gfileName);
                        array_push($gallery_arr, $gfileName);
                        $counter = $counter + 1;
                    };
                }
                $gallery_images = implode(",", $gallery_arr);
                $product->images = $gallery_images;
            }
            $product->save();
            DB::commit();
            return redirect()->route('admin.products')->with('success', 'Product added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save Product: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to created Product');
        }
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        return view('admin.product.edit', compact('product', 'categories', 'brands'));
    }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:products,slug,' . $id . ',id',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
            'category_id' => 'required',
            'brand_id' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            DB::beginTransaction();
            $product = Product::findOrFail($id);
            $product->name = $request->name;
            $product->slug = Str::slug($request->name);
            $product->short_description = $request->short_description;
            $product->description = $request->description;
            $product->regular_price = $request->regular_price;
            $product->sale_price = $request->sale_price;
            $product->SKU = $request->SKU;
            $product->quantity = $request->quantity;
            $product->stock_status = $request->stock_status;
            $product->featured = $request->featured;
            $product->category_id = $request->category_id;
            $product->brand_id = $request->brand_id;

            $current_timestamp = Carbon::now()->timestamp;

            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($product->image && Storage::exists('public/productImage/' . $product->image)) {
                    Storage::delete('public/productImage/' . $product->image);
                    Storage::delete('public/productImage/productThumbnails/' . $product->image);
                }
                $image = $request->file('image');
                $imageName = $current_timestamp . ".productImage." . $image->extension();
                // Move the new image
                $image->storeAs('public/productImage', $imageName);
                $this->GenerateProductThumbnailsImage($image, $imageName);
                $product->image = $imageName;
            }

            $gallery_arr = [];
            $gallery_images = "";
            $counter = 1;

            // Check if new gallery images are uploaded
            if ($request->hasFile('images')) {

                foreach (explode(',', $product->images) as $ofile) {
                    $thumbnailPath = 'public/productImage/productThumbnails/' . $ofile;
                    if ($ofile && Storage::exists($thumbnailPath)) {
                        Storage::delete($thumbnailPath);
                    } else {
                        Log::warning('Thumbnail not found: ' . $thumbnailPath);
                    }
                }

                $allowedfileExtion = ['jpg', 'png', 'jpeg'];
                $files = $request->file('images');

                foreach ($files as $file) {
                    $gextension = $file->getClientOriginalExtension();
                    $gcheck = in_array($gextension, $allowedfileExtion);
                    if ($gcheck) {
                        $gfileName = $current_timestamp . "-" . $counter . ".productImage." . $gextension;
                        $this->GenerateProductThumbnailsImage($file, $gfileName);
                        array_push($gallery_arr, $gfileName);
                        $counter++;
                    } else {
                        Log::warning('Invalid file extension: ' . $gextension);
                    }
                }

                $gallery_images = implode(",", $gallery_arr);
                $product->images = $gallery_images;
            }

            $product->save();
            DB::commit();
            return redirect()->route('admin.products')->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update Product: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to update Product');
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $product = Product::findOrFail($id);

            // Delete the main image if it exists
            if ($product->image && Storage::exists('public/productImage/' . $product->image)) {
                Storage::delete('public/productImage/' . $product->image);
                Storage::delete('public/productImage/productThumbnails/' . $product->image);
            }

            // Delete all gallery images if they exist
            if ($product->images) {
                foreach (explode(',', $product->images) as $ofile) {
                    $thumbnailPath = 'public/productImage/productThumbnails/' . $ofile;

                    if ($ofile && Storage::exists($thumbnailPath)) {
                        Storage::delete($thumbnailPath);
                    } else {
                        Log::warning('Thumbnail not found: ' . $thumbnailPath);
                    }
                }
            }

            // Delete the product record from the database
            $product->delete();

            DB::commit();
            return redirect()->route('admin.products')->with('success', 'Product deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete Product: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete Product');
        }
    }

    public function GenerateProductThumbnailsImage($image, $imageName)
    {
        $destinationPath = storage_path('app/public/productImage/productThumbnails');
        // Ensure the directory exists
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }
        $img = Image::read($image);
        $img->cover(540, 689, "top");
        $img->resize(540, 689, function ($constraint) {
            $constraint->upsize();
        })->save($destinationPath . '/' . $imageName);

        $img->resize(540, 689, function ($constraint) {
            $constraint->upsize();
        })->save($destinationPath . '/' . $imageName);
    }
}
