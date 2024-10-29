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

    public function pendingOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $pending_orders = Order::where('status', 'pending')->whereHas('product', function($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->get();

        if($pending_orders){
            return response()->json(['status' => true, 'message' => "List of pending orders.", 'data' => ['order' => $pending_orders]], 200);
        }

        return response()->json([ 'status' => false, 'message' => "No pending orders found!"], 500);
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

    public function acceptedOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $accepted_orders = Order::where('status', 'accepted')->whereHas('product', function($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->get();

        if($accepted_orders){
            return response()->json(['status' => true, 'message' => "List of accepted orders.", 'data' => ['order' => $accepted_orders]], 200);
        }

        return response()->json([ 'status' => false, 'message' => "No accepted orders found!"], 500);
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

    public function declinedOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $declined_orders = Order::where('status', 'declined')->whereHas('product', function($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->get();

        if($declined_orders){
            return response()->json(['status' => true, 'message' => "List of declined orders.", 'data' => ['order' => $declined_orders]], 200);
        }

        return response()->json([ 'status' => false, 'message' => "No declined orders found!"], 500);
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

    public function suppliedOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $supplied_orders = Order::where('status', 'supplied')->whereHas('product', function($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->get();

        if($supplied_orders){
            return response()->json(['status' => true, 'message' => "List of supplied orders.", 'data' => ['order' => $supplied_orders]], 200);
        }

        return response()->json([ 'status' => false, 'message' => "No supplied orders found!"], 500);
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
