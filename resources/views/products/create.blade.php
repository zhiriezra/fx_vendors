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
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Create Product</h4>
                        <div class="mt-3">
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- [ form-element ] start -->
                        @livewire('product.product-create')
                        <!-- [ form-element ] end -->
                    </div>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
@endsection
