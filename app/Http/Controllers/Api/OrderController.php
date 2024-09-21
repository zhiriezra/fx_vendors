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
        $products = Product::where('vendor_id', $vendor_id)->pluck('id');
        $orders = Order::whereIn('product_id', $products)->with(['product', 'agent.user'])->get();

        if($orders){
            return response()->json(['status' => true, 'orders' => $orders], 200);
        }else{
            return response()->json(['status' => false, 'message' => "Something went wrong!"], 500);
        }
    }

    public function accept($order_id)
    {
        $order = Order::find($order_id);

        if($order){

            $order->status = 'accepted';
            $order->save();
            return response()->json(['status' => 201, 'message' => "Order accepted."], 201);

        }

        return response()->json([ 'status' => 500, 'message' => "Something went wrong!"], 500);
    }

    public function decline($order_id)
    {
        $order = Order::find($order_id);

        if($order){

            $order->status = 'declined';
            $order->save();
            return response()->json(['status' => 201, 'message' => "Order declined."], 201);

        }

        return response()->json([ 'status' => 500, 'message' => "Something went wrong!"], 500);
    }

    public function supplied($order_id)
    {
        $order = Order::find($order_id);

        if($order){

            $order->status = 'supplied';
            $order->save();
            return response()->json(['status' => 201, 'message' => "Order supplied."], 201);

        }

        return response()->json([ 'status' => 500, 'message' => "Something went wrong!"], 500);
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
