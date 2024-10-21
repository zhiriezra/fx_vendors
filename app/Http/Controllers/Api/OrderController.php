<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($vendor_id)
    {
        $user = auth()->user();
        $orders = $user->vendor->orders()->with(['product', 'agent.user'])->get();

        if($orders){
            return response()->json(['status' => true, 'message' => 'My Orders list', 'data' => ['orders' => $orders, 'total' => $orders->count()]], 200);
        }else{
            return response()->json(['status' => false, 'message' => "Can not find any orders!"], 404);
        }
    }

    public function accept($order_id)
    {
        $order = Order::with('product')->find($order_id);

        if($order){

            $order->status = 'accepted';
            $order->save();
            return response()->json(['status' => true, 'message' => "Order accepted.", 'data' => ['order' => $order]], 200);

        }

        return response()->json([ 'status' => false, 'message' => "Something went wrong!"], 500);
    }

    public function decline($order_id)
    {
        $order = Order::with('product')->find($order_id);

        if($order){

            $order->status = 'declined';
            $order->save();
            return response()->json(['status' => true, 'message' => "Order declined.", 'data' => ['order' => $order]], 200);

        }

        return response()->json([ 'status' => false, 'message' => "Something went wrong!"], 500);
    }

    public function supplied($order_id)
    {
        $order = Order::with('product')->find($order_id);

        if($order){

            $order->status = 'supplied';
            $order->save();
            return response()->json(['status' => true, 'message' => "Order supplied.", 'data' => ['order' => $order]], 200);

        }

        return response()->json([ 'status' => false, 'message' => "Something went wrong!"], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
