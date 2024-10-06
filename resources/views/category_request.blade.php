@extends('layouts.app')

@section('title', 'Dashboard: FarmEx')

@section('content')
    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
            <div class="row align-items-center">
                
                <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Category Request</h2>
                </div>
                </div>
            </div>
            </div>
        </div>
        
        <!-- [ breadcrumb ] end -->
        <!-- [ Main Content ] start -->
        @livewire('category.category-request')
        <!-- [ Main Content ] end -->
        
        </div>
    </div>
    <!-- [ Main Content ] end -->
  @endsection
