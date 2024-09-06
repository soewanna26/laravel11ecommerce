<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('created_at', 'desc')->get()->take(10);
        $dashboardDats = DB::select("SELECT sum(total) As TotalAmount,
                                    sum(if(status = 'ordered',total,0)) As TotalOrderedAmount,
                                    sum(if(status = 'delivered',total,0)) As TotalDeliveredAmount,
                                    sum(if(status = 'canceled',total,0)) As TotalCanceledAmount,
                                    Count(*) As Total,
                                    sum(if(status = 'ordered',1,0)) As TotalOrdered,
                                    sum(if(status = 'delivered',1,0)) As TotalDelivered,
                                    sum(if(status = 'canceled',1,0)) As TotalCanceled
                                    From Orders
        ");

        $monthDatas = DB::select("SELECT M.id As MonthNo, M.name AS MonthName,
                                    IFNULL(D.TotalAmount,0) As TotalAmount,
                                    IFNULL(D.TotalOrderedAmount,0) As TotalOrderedAmount,
                                    IFNULL(D.TotalDeliveredAmount,0) As TotalDeliveredAmount,
                                    IFNULL(D.TotalCanceledAmount,0) As TotalCanceledAmount FROM month_names M
                                    LEFT JOIN(SELECT DATE_FORMAT(created_at, '%b') AS MonthName,
                                    MONTH(created_at) As MonthNo,
                                    sum(total) As TotalAmount,
                                    sum(if(status = 'ordered',total,0)) As TotalOrderedAmount,
                                    sum(if(status = 'delivered',total,0)) As TotalDeliveredAmount,
                                    sum(if(status = 'canceled',total,0)) As TotalCanceledAmount
                                    FROM Orders WHERE YEAR(created_at)=YEAR(NOW()) GROUP BY YEAR(created_at),MONTH(created_at), DATE_FORMAT(created_at,'%b')
                                    Order BY MONTH(created_at)) D on D.monthNo=M.id
        ");

        $AmountM = implode(',', collect($monthDatas)->pluck('TotalAmount')->toArray());
        $orderedAmountM = implode(',', collect($monthDatas)->pluck('TotalOrderedAmount')->toArray());
        $deliveredAmountM = implode(',', collect($monthDatas)->pluck('TotalDeliveredAmount')->toArray());
        $canceledAmountM = implode(',', collect($monthDatas)->pluck('TotalCanceledAmount')->toArray());

        $TotalAmount = collect($monthDatas)->sum('TotalAmount');
        $TotalOrderedAmount = collect($monthDatas)->sum('TotalOrderedAmount');
        $TotalDeliveredAmount = collect($monthDatas)->sum('TotalDeliveredAmount');
        $TotalCanceledAmount = collect($monthDatas)->sum('TotalCanceledAmount');

        return view('admin.index', compact('orders', 'dashboardDats', 'AmountM', 'orderedAmountM', 'deliveredAmountM', 'canceledAmountM', 'TotalAmount', 'TotalOrderedAmount', 'TotalDeliveredAmount', 'TotalCanceledAmount'));
    }
}
