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
                        <h4>Update your Profile</h4>
                        <span class="text-danger">* indicates compulsory fields</span>
                        @if(Session::has('message'))
                            <div class="alert alert-warning d-flex align-items-center mt-3" role="alert">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24">
                                <use xlink:href="#exclamation-triangle-fill" ></use>
                                </svg>
                                <div> {{ Session::get('message') }} </div>
                            </div>
                        @endif
                        
                    </div>
                    <div class="card-body">
                        <!-- [ form-element ] start -->
                        @livewire('profile.profile-create')
                        <!-- [ form-element ] end -->
                    </div>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
@endsection
