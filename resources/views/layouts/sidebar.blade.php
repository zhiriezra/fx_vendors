      <div class="m-header">
        <a href="/" class="b-brand text-primary">
          <!-- ========   Change your logo from here   ============ -->
          <img src="{{asset('assets/farmex-logo-main-with-tagline.png')}}" alt="" class="pt-4 img-fluid" />
        </a>
      </div>
      <div class="mt-4 navbar-content">
        <ul class="pc-navbar">
          <li class="pc-item pc-caption">
            <label>Navigation</label>
          </li>

          <li class="pc-item">
            <a href="{{ route('vendor.dashboard') }} " class="pc-link">
              <span class="pc-micon">
                <i class="fas fa-tachometer-alt"></i>
              </span>
              <span class="pc-mtext">Dashboard</span>
            </a>
          </li>
          <li class="pc-item pc-caption">
            <label>Products</label>
            <i class="ph-duotone ph-chart-pie"></i>
          </li>

          <li class="pc-item">
            <a href="{{ route('vendor.product.index') }}" class="pc-link">
              <span class="pc-micon">
                <i class="fas fa-cannabis"></i>
              </span>
              <span class="pc-mtext">Manage Products</span>
            </a>
          </li>

          <li class="pc-item pc-caption">
            <label>Orders</label>
            <i class="fas fa-gifts"></i>
          </li>

          <li class="pc-item pc-hasmenu">
            <a href="#!" class="pc-link">
              <span class="pc-micon">
                <i class="fas fa-layer-group"></i>
              </span>
              <span class="pc-mtext">Orders</span>
              <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
            </a>
            <ul class="pc-submenu">
              <li class="pc-item"><a class="pc-link" href="{{ route('vendor.orders.pending') }} ">Pending Orders</a></li>
              <li class="pc-item"><a class="pc-link" href="{{ route('vendor.orders.accepted') }}">Accepted Orders</a></li>
              <li class="pc-item"><a class="pc-link" href="{{ route('vendor.orders.supplied') }}">Supplied Orders</a></li>
            </ul>
          </li>

          <li class="pc-item pc-caption">
            <label>Others</label>
            <i class="ph-duotone ph-buildings"></i>
          </li>


          <li class="pc-item">
            <a href="{{ route('vendor.category_request') }} " class="pc-link">
              <span class="pc-micon">
                <i class="fas fa-boxes"></i>
              </span>
              <span class="pc-mtext">Category Request</span>
            </a>
          </li>

          <li class="pc-item pc-caption">
            <label>Records</label>
            <i class="ph-duotone ph-buildings"></i>
          </li>
          
          <li class="pc-item">
            <a href="{{ route('vendor.wallet.index') }} " class="pc-link">
              <span class="pc-micon">
                <i class="fas fa-wallet"></i>
              </span>
              <span class="pc-mtext">Wallet</span>
            </a>
          </li>


        </ul>

      </div>

