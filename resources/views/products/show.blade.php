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
            <!-- [ form-element ] start -->
            @livewire('product.product-show', ['productImage' => $productImage , 'product' => $product] )
            <!-- [ form-element ] end -->
        <!-- [ Main Content ] end -->
    </div>
</div>
@endsection
