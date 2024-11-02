@extends('auth.master')

@section('title', 'Home: FarmEx')

@section('content')
<div class="auth-sidecontent">
    <div class="auth-sidefooter">
      <img class="img-fluid" src="{{ asset('assets/farmex-logo-white.png')}}" alt="">
      <hr class="mb-3 mt-4" />
      <div class="row">
        <div class="col my-1">
          <p class="m-0">Made with ♥ for all African Farmers</p>
        </div>
        <div class="col-auto my-1">
          <ul class="list-inline footer-link mb-0">
            <li class="list-inline-item"><a href="/">Home</a></li>
            <li class="list-inline-item"><a href="/register" target="">Register</a></li>
            <li class="list-inline-item"><a href="#" target="_blank">Support</a></li>
          </ul>
        </div>
      </div>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="f-w-500 mb-1">Vendor login</h4>
                    <p class="mb-3">Don't have an Account? <a href="{{ route('register')}} " class="link-primary">Register</a></p>
                </div>


                <div class="card-body">

                    @if(session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <input type="email" name="email" value="{{ old('email') }}"  autocomplete="email" class="form-control" id="floatingInput1" placeholder="Email Address" required autofocus>
                            @error('email')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <input type="password" name="password" required autocomplete="current-password" class="form-control" id="floatingInput" placeholder="Password">
                            @error('password')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="d-flex mt-1 justify-content-between align-items-center">
                            <div class="form-check">
                            <input class="form-check-input input-primary" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label text-muted" for="customCheckc1">Remember me?</label>
                            </div>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}">
                                    <h6 class="text-secondary f-w-400 mb-0">Forgot Password?</h6>
                                </a>
                            @endif

                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                    <div class="saprator my-3">
                        <span>Get Support</span>
                    </div>
                    <div class="text-center">
                        <ul class="list-inline mx-auto mt-3 mb-0">
                            <li class="list-inline-item">
                                <a href="#" class="avtar avtar-s rounded-circle bg-facebook" target="_blank">
                                <i class="fa fa-phone text-white"></i>
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <a href="https://twitter.com/" class="avtar avtar-s rounded-circle bg-twitter" target="_blank">
                                <i class="fa fa-envelope text-white"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
