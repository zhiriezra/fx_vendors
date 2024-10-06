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
                            <h2 class="mb-0">Manage Products</h2>
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
                        <h4>List of Products</h4>
                        <div class="mt-3 mb-3">
                            <a class="btn btn-info btn-sm" href="{{ route('vendor.product.create') }} ">Add new product</a>
                        </div>
                   
                        @if(Session::has('message'))
                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24">
                                <use xlink:href="#check-circle-fill" ></use>
                                </svg>
                                <div> {{ Session::get('message') }} </div>
                            </div>
                        @endif
                    </div>
                    @livewire('product.product-list')
                </div>
            </div>
        </div>
      <!-- [ Main Content ] end -->
    </div>
</div>
@endsection
