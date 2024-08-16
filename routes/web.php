<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home.index');

Route::middleware(['auth'])->group(function(){
    Route::get('/account-dashboard',[UserController::class,'index'])->name('user.index');
});

Route::middleware(['auth',AuthAdmin::class])->group(function(){
    Route::get('/admin',[AdminController::class,'index'])->name('admin.index');

    //Brands
    Route::get('/admin/brands',[BrandController::class,'index'])->name('admin.brands');
    Route::get('/admin/brand/create',[BrandController::class,'create'])->name('admin.brand.create');
    Route::post('/admin/brand/store',[BrandController::class,'store'])->name('admin.brand.store');
    Route::get('/admin/brand/edit/{id}',[BrandController::class,'edit'])->name('admin.brand.edit');
    Route::post('/admin/brand/update/{id}',[BrandController::class,'update'])->name('admin.brand.update');
    Route::delete('/admin/brand/delete/{id}',[BrandController::class,'delete'])->name('admin.brand.delete');

    //Categories
    Route::get('/admin/categories',[CategoryController::class,'index'])->name('admin.categories');
    Route::get('/admin/category/create',[CategoryController::class,'create'])->name('admin.category.create');
    Route::post('/admin/category/store',[CategoryController::class,'store'])->name('admin.category.store');
    Route::get('/admin/category/edit/{id}',[CategoryController::class,'edit'])->name('admin.category.edit');
    Route::post('/admin/category/update/{id}',[CategoryController::class,'update'])->name('admin.category.update');
    Route::delete('/admin/category/delete/{id}',[CategoryController::class,'delete'])->name('admin.category.delete');
});
