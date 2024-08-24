<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Psy\Readline\Transient;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('created_at','DESC')->paginate(12);
        return view('admin.order.index',compact('orders'));
    }

    public function order_details($order_id)
    {
        $order = Order::findOrFail($order_id);
        $orderItems = OrderItem::where('order_id',$order_id)->orderBy('id')->paginate(12);
        $transaction = Transaction::where('order_id',$order_id)->first();
        return view('admin.order.order_details', compact('order','orderItems','transaction'));
    }

    public function update_order_status(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->status = $request->order_status;
        if($request->order_status == 'delivered')
        {
            $order->deliveryed_date = Carbon::now();
        }
        else if($request->order_status == 'cancelled')
        {
            $order->canceled_date = Carbon::now();
        }
        $order->save();

        if($request->order_status == 'delivered')
        {
            $transaction = Transaction::where('order_id',$request->order_id)->first();
            $transaction->status = 'approved';
            $transaction->save();
        }
        return back()->with('status','Status Changed Successfully');
    }
}
