<div class="row">
    <!-- [ sample-page ] start -->
    <div class="col-sm-12">
      <div class="card">
        <div class="card-header">
            <h4>Profile</h4>
            @if(Session::has('message'))
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="fas fa-check-circle px-3"></i>
                    <div> {{ Session::get('message') }}</div>
                </div>
            @endif
        </div>
      </div>
      <div class="row">
        <div class="col-lg-5 col-xxl-3">
          <div class="card overflow-hidden">
            <div class="card-body position-relative">
              <div class="text-center mt-3">
                <!-- Left Navigation start -->
                <div class="chat-avtar d-inline-flex mx-auto">
                  <img class="rounded-circle img-fluid wid-90 img-thumbnail"
                    src="{{ Storage::url($vendor->user->profile_image) }}" alt="User image">
                  <i class="chat-badge bg-success me-2 mb-2"></i>
                </div>
                <h5 class="mb-1">{{ $vendor->user->firstname }} {{ $vendor->user->lastname }}</h5>
                
                <!-- <ul class="list-inline mx-auto my-4">
                  <li class="list-inline-item">
                    <a href="#" class="avtar avtar-s text-white bg-dribbble">
                      <i class="ti ti-brand-dribbble f-24"></i>
                    </a>
                  </li>
                  <li class="list-inline-item">
                    <a href="#" class="avtar avtar-s text-white bg-amazon">
                      <i class="ti ti-brand-figma f-24"></i>
                    </a>
                  </li>
                  <li class="list-inline-item">
                    <a href="#" class="avtar avtar-s text-white bg-pinterest">
                      <i class="ti ti-brand-pinterest f-24"></i>
                    </a>
                  </li>
                  <li class="list-inline-item">
                    <a href="#" class="avtar avtar-s text-white bg-behance">
                      <i class="ti ti-brand-behance f-24"></i>
                    </a>
                  </li>
                </ul> -->
                <div class="row g-3">
                  <div class="col-6">
                    <h5 class="mb-0">{{ $productCount }}</h5>
                    <small class="text-muted">Products</small>
                  </div>
                  <!-- <div class="col-6 border border-top-0 border-bottom-0">
                    <h5 class="mb-0">40</h5>
                    <small class="text-muted">Orders</small>
                  </div> -->
                  <div class="col-4">
                    <h5 class="mb-0">{{ $orderCount }}</h5>
                    <small class="text-muted">Orders</small>
                  </div>
                </div>
              </div>
            </div>
            <div class="nav flex-column nav-pills list-group list-group-flush account-pills mb-0" id="user-set-tab"
              role="tablist" aria-orientation="vertical">
              <a class="nav-link list-group-item list-group-item-action active" id="user-set-profile-tab"
                data-bs-toggle="pill" href="#user-set-profile" role="tab" aria-controls="user-set-profile"
                aria-selected="true">
                <span class="f-w-500"><i class="ph-duotone ph-user-circle m-r-10"></i>Personal Details</span>
              </a>
              <a class="nav-link list-group-item list-group-item-action" id="user-set-information-tab"
                data-bs-toggle="pill" href="#user-set-information" role="tab" aria-controls="user-set-information"
                aria-selected="false">
                <span class="f-w-500"><i class="ph-duotone ph-clipboard-text m-r-10"></i>Location</span>
              </a>
              <a class="nav-link list-group-item list-group-item-action" id="user-set-account-tab"
                data-bs-toggle="pill" href="#user-set-account" role="tab" aria-controls="user-set-account"
                aria-selected="false">
                <span class="f-w-500"><i class="ph-duotone ph-notebook m-r-10"></i>Business Information</span>
              </a>
              <!-- Top navigation ends -->
            </div>
          </div>
        </div>
        <div class="col-lg-7 col-xxl-9">
          <div class="tab-content" id="user-set-tabContent">
            <div class="tab-pane fade show active" id="user-set-profile" role="tabpanel"
              aria-labelledby="user-set-profile-tab">
              
              <div class="card">
                <div class="card-header">
                  <h5>Personal Details</h5>
                </div>
                <div class="card-body">
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0 pt-0">
                      <div class="row">
                        <div class="col-md-6">
                          <p class="mb-1 text-muted">First Name</p>
                          <p class="mb-0">{{ $vendor->user->firstname }}</p>
                        </div>
                        <div class="col-md-6">
                          <p class="mb-1 text-muted">Other Name</p>
                          <p class="mb-0">{{ $vendor->user->lastname }} {{ $vendor->user->middlename }}</p>
                        </div>
                      </div>
                    </li>
                    <li class="list-group-item px-0">
                      <div class="row">
                        <div class="col-md-6">
                          <p class="mb-1 text-muted">Phone</p>
                          <p class="mb-0">{{ $vendor->user->phone }}</p>
                        </div>
                            <div class="col-md-6">
                            <p class="mb-1 text-muted">Email</p>
                            <p class="mb-0">{{ $vendor->user->email }}</p>
                            </div>
                      </div>
                    </li>
                    <li class="list-group-item px-0">
                        <div class="row">
                          <div class="col-md-6">
                              <p class="mb-1 text-muted">Date of Birth</p>
                              <p class="mb-0">{{ $vendor->dob }}</p>
                            </div>
                          <div class="col-md-6">
                            <p class="mb-1 text-muted">Gender</p>
                            <p class="mb-0">{{ $vendor->gender }}</p>
                          </div>
                        </div>
                      </li>
                      <li class="list-group-item px-0">
                        <div class="row">
                          <div class="col-md-6">
                              <p class="mb-1 text-muted">Mode Of Identity</p>
                              <p class="mb-0">{{ $vendor->identification_mode }}</p>
                            </div>
                          <div class="col-md-6">
                            <p class="mb-1 text-muted">Identity Number</p>
                            <p class="mb-0">{{ $vendor->identification_no }}</p>
                          </div>
                        </div>
                      </li>
                  </ul>
                </div>
              </div>
              
            </div>
            <div class="tab-pane fade" id="user-set-information" role="tabpanel"
              aria-labelledby="user-set-information-tab">
              <div class="card">
                <div class="card-header">
                  <h5>Location</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0">
                            <div class="row">
                              <div class="col-md-6">
                                  <p class="mb-1 text-muted">State</p>
                                  <p class="mb-0">{{ $vendor->state->name }}</p>
                                </div>
                              <div class="col-md-6">
                                <p class="mb-1 text-muted">LGA</p>
                                <p class="mb-0">{{ $vendor->lga->name }}</p>
                              </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0 pb-2">
                            <p class="mb-1 text-muted">Current Address</p>
                            <p class="mb-0">{{ $vendor->current_location }}</p>
                        </li>
                        <li class="list-group-item px-0 pb-2">
                            <p class="mb-1 text-muted">Permanent Address</p>
                            <p class="mb-0">{{ $vendor->permanent_address }}</p>
                        </li>
                        <li class="list-group-item px-0 pb-2">
                            <p class="mb-1 text-muted">Community</p>
                            <p class="mb-0">{{ $vendor->community }}</p>
                        </li>
                    </ul>
                  </div>
              </div>
            </div>
            <div class="tab-pane fade" id="user-set-account" role="tabpanel" aria-labelledby="user-set-account-tab">
              <div class="card">
                <div class="card-header">
                  <h5>Business Information</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                      <li class="list-group-item px-0 pt-0">
                        <div class="row">
                          <div class="col-md-6">
                            <p class="mb-1 text-muted">Business Name</p>
                            <p class="mb-0">{{ $vendor->business_name }}</p>
                          </div>
                          <div class="col-md-6">
                            <p class="mb-1 text-muted">Business Address</p>
                            <p class="mb-0">{{ $vendor->business_address }} </p>
                          </div>
                        </div>
                      </li>
                      <li class="list-group-item px-0">
                        <div class="row">
                          <div class="col-md-6">
                            <p class="mb-1 text-muted">Business Email</p>
                            <p class="mb-0">{{ $vendor->business_email }}</p>
                          </div>
                              <div class="col-md-6">
                              <p class="mb-1 text-muted">Business Mobile</p>
                              <p class="mb-0">{{ $vendor->business_mobile }}</p>
                              </div>
                        </div>
                      </li>
                      <li class="list-group-item px-0">
                          <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1 text-muted">Business Type</p>
                                <p class="mb-0">{{ $vendor->business_type }}</p>
                              </div>
                            <div class="col-md-6">
                              <p class="mb-1 text-muted">Registration Number</p>
                              <p class="mb-0">{{ $vendor->registration_no }}</p>
                            </div>
                          </div>
                        </li>
                        <li class="list-group-item px-0">
                          <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1 text-muted">Bank</p>
                                <p class="mb-0">{{ $vendor->bank }}</p>
                              </div>
                            <div class="col-md-6">
                              <p class="mb-1 text-muted">Bank name</p>
                              <p class="mb-0">{{ $vendor->account_name }}</p>
                            </div>
                          </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row">
                              <div class="col-md-6">
                                  <p class="mb-1 text-muted">Account Number</p>
                                  <p class="mb-0">{{ $vendor->account_no }}</p>
                                </div>
                              <div class="col-md-6">
                                <p class="mb-1 text-muted">TIN</p>
                                <p class="mb-0">{{ $vendor->tin }}</p>
                              </div>
                            </div>
                          </li>
                    </ul>
                  </div>
              </div>
             
            </div>
            
          </div>
        </div>
      </div>
    </div>
    <!-- [ sample-page ] end -->
  </div>