@extends('layouts.app')

@section('title', 'Dashboard: FarmEx')

@section('content')

<!-- [ Sub heading] start -->
<div class="pc-container">
    <div class="pc-content">
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">

            <div class="col-md-12">
              <div class="page-header-title">
                <h2 class="mb-0">Admin Dashboard</h2>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Sub Heading ] end -->
      <!-- [ Main Content ] start -->
      <!-- [ Summary ] start -->
      @if(Session::has('message'))
      <div class="alert alert-success d-flex align-items-center" role="alert">
        <svg class="bi flex-shrink-0 me-2" width="24" height="24">
          <use xlink:href="#check-circle-fill" ></use>
        </svg>
        <div> {{ Session::get('message') }} </div>
      </div>
      @endif
      @if(Session::has('warning'))
      <div class="alert alert-warning d-flex align-items-center" role="alert">
        <svg class="bi flex-shrink-0 me-2" width="24" height="24">
          <use xlink:href="#exclamation-triangle-fill" ></use>
        </svg>
        <div> {{ Session::get('warning') }} </div>
      </div>
      @endif
      
      @if($user->vendor && $user->vendor->status == 0)
      <div class="alert alert-primary d-flex align-items-center" role="alert">
        <svg class="bi flex-shrink-0 me-2" width="24" height="24">
          <use xlink:href="#info-fill" ></use>
        </svg>
        <div> Awaiting Approval! </div>
      </div>
      @endif
      <div class="row">
    
        <div class="col-md-6 col-sm-6">
          <div class="card statistics-card-1 overflow-hidden bg-brand-color-3">
            <div class="card-body">
              <img src="{{asset('dist/assets/images/widget/img-status-5.svg')}}" alt="img" class="img-fluid img-bg" >
              <h5 class="mb-4 text-white">Products</h5>
              <div class="d-flex align-items-center mt-3">
                <h3 class="text-white f-w-300 d-flex align-items-center m-b-0"> {{$products->count()}} Products</h3>
                <span class="badge bg-light-success ms-2"></span>
              </div>
              <!-- <p class="text-white text-opacity-75 mb-2 text-sm mt-3">23 Requests</p> -->

            </div>
          </div>
        </div>
        <div class="col-md-6 col-sm-6">
          <div class="card statistics-card-1 overflow-hidden bg-brand-color-3">
            <div class="card-body">
              <img src="{{asset('dist/assets/images/widget/img-status-2.svg')}}" alt="img" class="img-fluid img-bg" >
              <h5 class="mb-4 text-white">Orders</h5>
              <div class="d-flex align-items-center mt-3">
                <h3 class="text-white f-w-300 d-flex align-items-center m-b-0"> Orders</h3>
                <span class="badge bg-light-primary ms-2">12 requests</span>
              </div>
              <!-- <p class="text-white text-opacity-75 mb-2 text-sm mt-3">20 Requests</p> -->

            </div>
          </div>
        </div>
      </div>
      <!-- <div class="row">
        <div class="col-md-6 col-sm-6">
          <div class="card statistics-card-1 overflow-hidden bg-brand-color-3">
            <div class="card-body">
              <img src="{{asset('dist/assets/images/widget/img-status-3.svg')}}" alt="img" class="img-fluid img-bg" >
              <h5 class="mb-4 text-white">Farmers</h5>
              <div class="d-flex align-items-center mt-3">
                <h3 class="text-white f-w-300 d-flex align-items-center m-b-0"> Farmers</h3>
                <span class="badge bg-light-success ms-2"></span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-sm-6">
          <div class="card statistics-card-1 overflow-hidden bg-brand-color-3">
            <div class="card-body">
              <img src="{{asset('dist/assets/images/widget/img-status-4.svg')}}" alt="img" class="img-fluid img-bg" >
              <h5 class="mb-4 text-white">Farm Lands</h5>
              <div class="d-flex align-items-center mt-3">
                <h3 class="text-white f-w-300 d-flex align-items-center m-b-0">Farmers</h3>
                <span class="badge bg-light-primary ms-2"></span>
              </div>

            </div>
          </div>
        </div>
      </div> -->
      <hr>
      <!-- [ Summary ] end -->
      <!-- [ Statistics ] start -->
      {{-- <div class="row">
        <div class="col-lg-6">
            <div class="card">
              <div class="card-header d-flex align-items-center justify-content-between">
                <h5>Yearly Summary</h5>
                <div class="dropdown">
                  <a
                    class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                    href="#"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                    ><i class="material-icons-two-tone f-18">more_vert</i></a
                  >
                  <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="#">View</a>
                    <a class="dropdown-item" href="#">Edit</a>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div class="row justify-content-center g-3 text-center mb-3">
                  <div class="col-6 col-md-4">
                    <div class="overview-product-legends">
                      <p class="text-muted mb-1"><span>Invoiced</span></p>
                      <h4 class="mb-0">$2356.4</h4>
                    </div>
                  </div>
                  <div class="col-6 col-md-4">
                    <div class="overview-product-legends">
                      <p class="text-muted mb-1"><span>Profit</span></p>
                      <h4 class="mb-0">$1935.6</h4>
                    </div>
                  </div>
                  <div class="col-6 col-md-4">
                    <div class="overview-product-legends">
                      <p class="text-muted mb-1"><span>Expenses</span></p>
                      <h4 class="mb-0">$468.9</h4>
                    </div>
                  </div>
                </div>
                <div id="yearly-summary-chart"></div>
              </div>
            </div>
          </div>
        <div class="col-6">

        </div>
      </div> --}}
      <!-- [ Statistics ] end -->
      <!-- [ Main Content ] end -->
    </div>
  </div>
  <!-- [ Main Content ] end -->

@endsection
