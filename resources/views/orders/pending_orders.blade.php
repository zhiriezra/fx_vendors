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
                            <h2 class="mb-0">Orders</h2>
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
                        <h4>Pending Orders</h4>
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
                    @livewire('order.vendor-orders')
                </div>
            </div>
        </div>
      <!-- [ Main Content ] end -->
    </div>
</div>
@endsection
