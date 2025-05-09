<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Exports\OrdersExport;
use App\Services\EscrowService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\GeneralWalletService;

class OrderController extends Controller
{
    use ApiResponder;

    public $total_amount = 0.0;

    protected $GeneralWalletService;

    public function __construct(GeneralWalletService $GeneralWalletService)
    {
        $this->GeneralWalletService = $GeneralWalletService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        $orders = $user->vendor->orders()->with(['product.product_images', 'agent.user'])->get()->map(function($order){
            return [
                'id' => $order->id,
                'agent' => $order->agent->user->firstname.' '.$order->agent->user->lastname,
                'product_name' => $order->product->name,
                'product_image' => optional($order->product->product_images->first())->image_path,
                'farmer' => $order->farmer->fname.' '.$order->farmer->lname,
                'quantity' => $order->quantity,
                'agent_price' => $order->product->agent_price,
                'created_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
                'updated_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
                'status' => $order->status
            ];
        });
        if($orders){
            return response()->json(['status' => true, 'message' => 'My Orders list', 'data' => ['orders' => $orders, 'total' => $orders->count()]], 200);
        }

        return response()->json(['status' => false, 'message' => "Can not find any orders!"], 404);

    }


    public function pendingOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $orders = Order::where('status', 'pending')->whereHas('product', function($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->get()->map(function($order){
            return [
                'id' => $order->id,
                'agent' => $order->agent->user->firstname.' '.$order->agent->user->lastname,
                'product_name' => $order->product->name,
                'product_image' => optional($order->product->product_images->first())->image_path,
                'farmer' => $order->farmer->fname.' '.$order->farmer->lname,
                'quantity' => $order->quantity,
                'agent_price' => $order->unit_price,
                'created_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
                'updated_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
                'status' => $order->status
            ];
        });

        if($orders){
            return response()->json(['status' => true, 'message' => "List of pending orders.", 'data' => ['order' => $orders]], 200);
        }

        return response()->json([ 'status' => false, 'message' => "No pending orders found!"], 500);
    }

    public function singleOrder($order_id)
    {
        $order = Order::with('product')->find($order_id);

        if($order){

            $order = [
                'id' => $order->id,
                'agent' => $order->agent->user->firstname.' '.$order->agent->user->lastname,
                'product_name' => $order->product->name,
                'product_image' => optional($order->product->product_images->first())->image_path,
                'farmer' => $order->farmer->fname.' '.$order->farmer->lname,
                'quantity' => $order->quantity,
                'agent_price' => $order->product->agent_price,
                'created_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
                'updated_date' => Carbon::parse($order->updated_at)->format('M j, Y, g:ia'),
                'status' => $order->status
            ];

            return response()->json(['status' => true, 'message' => "Single Order.", 'data' => ['order' => $order]], 200);

        }

        return response()->json([ 'status' => false, 'message' => "order not found"], 404);
    }

    public function accept($order_id)
    {
        $order = Order::with('product')->find($order_id);

        if($order){

            $order->status = 'accepted';
            $order->save();

            $order = [
                'id' => $order->id,
                'agent' => $order->agent->user->firstname.' '.$order->agent->user->lastname,
                'product_name' => $order->product->name,
                'product_image' => optional($order->product->product_images->first())->image_path,
                'farmer' => $order->farmer->fname.' '.$order->farmer->lname,
                'quantity' => $order->quantity,
                'agent_price' => $order->product->agent_price,
                'created_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
                'updated_date' => Carbon::parse($order->updated_at)->format('M j, Y, g:ia'),
                'status' => $order->status
            ];

            return response()->json(['status' => true, 'message' => "Order accepted.", 'data' => ['order' => $order]], 200);

        }

        return response()->json([ 'status' => false, 'message' => "order not found"], 404);
    }

    public function acceptedOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $orders = Order::where('status', 'accepted')->whereHas('product', function($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->get()
        ->map(function($order){
            return [
                'id' => $order->id,
                'agent' => $order->agent->user->firstname.' '.$order->agent->user->lastname,
                'product_image' => optional($order->product->product_images->first())->image_path,
                'product_name' => $order->product->name,
                'farmer' => $order->farmer->fname.' '.$order->farmer->lname,
                'quantity' => $order->quantity,
                'agent_price' => $order->product->agent_price,
                'created_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
                'updated_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
                'status' => $order->status
            ];
        });

        if($orders){
            return response()->json(['status' => true, 'message' => "List of accepted orders.", 'data' => ['order' => $orders]], 200);
        }

        return response()->json([ 'status' => false, 'message' => "No accepted orders found!"], 500);
    }

    public function declineNew($order_id, EscrowService $escrowService)
    {
        $order = Order::with('product')->find($order_id);

        if($order){

            $user = User::where('id', $order->agent->user_id)->first();

            if($order->status != "pending"){
                return $this->error(null, "You can only decline a pending order.", 422);
            }

            $defaultProvider = $this->GeneralWalletService->getDefaultWalletProviderForUser($user);

            $escrow = $escrowService->delineEscrow($order_id, $defaultProvider);

            return $escrow;

        }

        return response()->json([ 'status' => false, 'message' => "Order not found!"], 400);
    }

    public function declinedOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $orders = Order::where('status', 'declined')->whereHas('product', function($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->get()->map(function($order){
            return [
                'id' => $order->id,
                'agent' => $order->agent->user->firstname.' '.$order->agent->user->lastname,
                'product_name' => $order->product->name,
                'product_image' => optional($order->product->product_images->first())->image_path,
                'farmer' => $order->farmer->fname.' '.$order->farmer->lname,
                'quantity' => $order->quantity,
                'agent_price' => $order->product->agent_price,
                'created_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
                'updated_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
                'status' => $order->status
            ];
        });

        if($orders){
            return response()->json(['status' => true, 'message' => "List of declined orders.", 'data' => ['order' => $orders]], 200);
        }

        return response()->json([ 'status' => false, 'message' => "No declined orders found!"], 500);
    }

    public function confirmSupplied($order_id)
    {
        $order = Order::with('product')->find($order_id);

        if($order){

            $order->status = 'supplied';
            $order->save();

            $order = [
                'id' => $order->id,
                'agent' => $order->agent->user->firstname.' '.$order->agent->user->lastname,
                'product_name' => $order->product->name,
                'product_image' => optional($order->product->product_images->first())->image_path,
                'farmer' => $order->farmer->fname.' '.$order->farmer->lname,
                'quantity' => $order->quantity,
                'agent_price' => $order->product->agent_price,
                'created_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
                'updated_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
                'status' => $order->status
            ];
            return response()->json(['status' => true, 'message' => "Order supplied.", 'data' => ['order' => $order]], 200);

        }

        return response()->json([ 'status' => false, 'message' => "Order not found!"], 400);
    }

    public function suppliedOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $orders = Order::where('status', 'supplied')->whereHas('product', function($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->get()->map(function($order){
            return [
                'id' => $order->id,
                'agent' => $order->agent->user->firstname.' '.$order->agent->user->lastname,
                'product_name' => $order->product->name,
                'product_image' => optional($order->product->product_images->first())->image_path,
                'farmer' => $order->farmer->fname.' '.$order->farmer->lname,
                'quantity' => $order->quantity,
                'agent_price' => $order->product->agent_price,
                'created_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
                'updated_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
                'status' => $order->status
            ];
        });

        if($orders){
            return response()->json(['status' => true, 'message' => "List of supplied orders.", 'data' => ['order' => $orders]], 200);
        }

        return response()->json([ 'status' => false, 'message' => "No supplied orders found!"], 500);
    }

    //Export user Orders
    public function exportOrder()
    {
        $fileName = 'orders_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new OrdersExport, $fileName);

        // $fileName = 'orders_' . date('Y-m-d') . '.xlsx';
        // Excel::store(new OrdersExport, $fileName, 'public');

        // return response()->json([
        //     'success' => true,
        //     'file' => url('storage/' . $fileName),
        //     'message' => 'Export generated successfully'
        // ]);

    }

}
