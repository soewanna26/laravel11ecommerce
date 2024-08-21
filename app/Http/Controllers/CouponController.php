<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::orderBy("expiry_date", "DESC")->paginate(12);
        return view("admin.coupon.index", compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupon.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|unique:coupons,code',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            $coupon = new Coupon();
            $coupon->code = $request->code;
            $coupon->type = $request->type;
            $coupon->value = $request->value;
            $coupon->cart_value = $request->cart_value;
            $coupon->expiry_date = $request->expiry_date;
            $coupon->save();
            return redirect()->route('admin.coupons')->with('success', 'Coupon added successfully');
        } catch (\Exception $e) {
            Log::error('Failed to save Coupon: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to created Coupon');
        }
    }

    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        return view('admin.coupon.edit', compact('coupon'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|unique:coupons,code,' . $id . ',id',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            $coupon = Coupon::findOrFail($id);
            $coupon->code = $request->code;
            $coupon->type = $request->type;
            $coupon->value = $request->value;
            $coupon->cart_value = $request->cart_value;
            $coupon->expiry_date = $request->expiry_date;
            $coupon->save();
            return redirect()->route('admin.coupons')->with('success', 'Coupon updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to save Coupon: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to updated Coupon');
        }
    }

    public function delete($id)
    {

        $coupon = Coupon::findOrFail($id);

        $coupon->delete();
        return redirect()->back()->with('success', 'Coupon deleted successfully');
    }
}
