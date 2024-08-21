
<!-- [ Header Topbar ] start -->
<header class="pc-header">
    <div class="header-wrapper"> <!-- [Mobile Media Block] start -->
  <div class="me-auto pc-mob-drp">
    <ul class="list-unstyled">
      <!-- ======= Menu collapse Icon ===== -->
      <li class="pc-h-item pc-sidebar-collapse">
        <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
          <i class="ti ti-menu-2"></i>
        </a>
      </li>
      <li class="pc-h-item pc-sidebar-popup">
        <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
          <i class="ti ti-menu-2"></i>
        </a>
      </li>
    </ul>
  </div>
  <!-- [Mobile Media Block end] -->
  <div class="ms-auto">
    <ul class="list-unstyled">
      <li class="dropdown pc-h-item header-user-profile">
        <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button"
          aria-haspopup="false" data-bs-auto-close="outside" aria-expanded="false">
          <img src="{{asset('dist/assets/images/user/avatar-2.jpg')}}" alt="user-image" class="user-avtar" />
        </a>
        {{-- <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
          <div class="dropdown-header d-flex align-items-center justify-content-between">
            <h5 class="m-0"><a href="{{ route('logout') }}">Logout</a> </h5>
          </div>
        </div> --}}

        <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
            <div class="dropdown-header d-flex align-items-center justify-content-between">
            <h5 class="m-0">Profile</h5>
            </div>
            <div class="dropdown-body">
              <div class="profile-notification-scroll position-relative" style="max-height: calc(100vh - 225px)">
                <ul class="list-group list-group-flush w-100">
                  <li class="list-group-item">
                    <div class="d-flex align-items-center">
                      <div class="flex-shrink-0">
                        <img src="{{ asset('dist/assets/images/user/avatar-2.jpg') }}" alt="user-image" class="wid-50 rounded-circle" />
                      </div>
                      <div class="flex-grow-1 mx-3">
                        <h5 class="mb-0">{{ auth()->user()->firstname }} {{ auth()->user()->lastname }}</h5>
                        <a class="link-primary" href="tel:{{ auth()->user()->phone }}">{{ auth()->user()->phone }}</a>
                      </div>
                      {{-- <span class="badge bg-primary">PRO</span> --}}
                    </div>
                  </li>
                  <li class="list-group-item">
                    <a href="{{ route('vendor.profile.index') }}" class="dropdown-item">
                      <span class="d-flex align-items-center">
                        <i class="material-icons-two-tone">person</i>
                        <span>My Profile</span>
                      </span>
                    </a>
                    
                  </li>

                  <li class="list-group-item">

                    <a href="{{ route('logout') }}" onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();" class="dropdown-item">
                      <span class="d-flex align-items-center">
                        <i class="ph-duotone ph-power"></i>
                        <span>Logout</span>
                      </span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                    
                  </li>
                </ul>
              </div>
            </div>
          </div>
      </li>
    </ul>
  </div> </div>
  </header>
  <!-- [ Header ] end -->
