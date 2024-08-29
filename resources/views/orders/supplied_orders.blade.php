@extends('layouts.app')

@section('title', 'Dashboard: FarmEx')

@section('content')
<div class="pc-container">
    <div class="pc-content">
        <!-- [ Content head ] start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">

                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h2 class="mb-0">Vendor's Dashboard</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ content Head ] end -->


        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-12">
                <div class="card table-card">
                    <div class="card-header">
                        <h4>Supplied Orders</h4>
                        @if(Session::has('message'))
                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24">
                                <use xlink:href="#check-circle-fill" ></use>
                                </svg>
                                <div> {{ Session::get('message') }} </div>
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24">
                                <use xlink:href="#exclamation-triangle-fill" ></use>
                                </svg>
                                <div> {{ session('error') }}</div>
                            </div>
                          @endif
                    </div>
                    <div class="card-body pt-3">
                        <div class="table-responsive">
                            <div class="datatable-top">
                                <button class="btn btn-light-primary mb-1 btn-sm csv">Export PDF</button>
                                <button class="btn btn-light-primary mb-1 btn-sm csv">Export Excel</button>
                                <div class="datatable-search">
                                    <input wire:model.live="search" class="datatable-input" placeholder="Search..." type="search" title="Search within table" aria-controls="pc-dt-satetime-sorting">
                                </div>
                            </div>
                            <table class="table table-hover " id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <!-- <th scope="col">Image</th> -->
                                        <th scope="col">Agent</th>
                                        <th scope="col">Product</th>
                                        <th scope="col">Farmer</th>
                                        <th scope="col">Quantity</th>
                                        <th scope="col">Agent Price</th>
                                        <th scope="col">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Search section start -->
                                    @forelse($orders as $order)
                                    <tr wire:key="{{$order->id }} ">
                                        <td>{{$order->agent->user->firstname }} {{ $order->agent->user->lastname }}</td>
                                        <td>{{ $order->product->name }}</td>
                                        <td>{{ $order->farmer->fname }} {{ $order->farmer->lname }} </td>
                                        <td>{{ $order->quantity }}</td>
                                        <td>₦{{ $order->product->agent_price }}</td>
                                        <td>{{ \Carbon\Carbon::parse($order->updated_at)->diffForHumans() }} </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6"><h5 style="text-align: center;"><i class="fas fa-shopping-cart"></i> 0 Rejected Orders</h5></td>
                                    </tr>
                                    @endforelse 
                    
                                 
                                    
                                </tbody>
                                
                            </table>
                            
                        </div>
                        
                        
                    </div>
                    
                    
                </div>
            </div>
        </div>
      <!-- [ Main Content ] end -->
    </div>
</div>
@endsection
