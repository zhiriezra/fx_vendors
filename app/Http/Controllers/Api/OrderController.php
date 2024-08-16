<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;

        $orders = Order::where('vendor_id', $vendor->id)
            ->with(['product', 'agent.user'])
            ->get();
            if($orders){

                return response()->json([
                    'status' => 200,
                    'orders' => $orders
                ], 200);
            }else{
    
                return response()->json([
                    'status' => 500,
                    'message' => "Something went wrong!"
                ], 500);
            }
    }

    public function accept(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $order->status = 'accepted';
        $order->save();
        if($order){

            return response()->json([
                'status' => 201,
                'message' => "Order accepted."
            ], 201);
        }else{

            return response()->json([
                'status' => 500,
                'message' => "Something went wrong!"
            ], 500);
        }

    }

    public function decline(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $order->status = 'declined';
        $order->save();

        if($order){

            return response()->json([
                'status' => 201,
                'message' => "Order declined."
            ], 201);
        }else{

            return response()->json([
                'status' => 500,
                'message' => "Something went wrong!"
            ], 500);

        }
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
