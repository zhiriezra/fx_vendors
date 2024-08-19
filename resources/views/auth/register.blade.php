@extends('auth.master')

@section('title', 'Home: FarmEx')

@section('content')

<div class="auth-form">
    <div class="card my-5 mx-3">
      <div class="card-body">
        <h4 class="f-w-500 mb-1">Vendor Register</h4>
        <p class="mb-3">Already have an Account? <a href="/login" class="link-primary">Log in</a></p>
        <form method="POST" action="{{ route('register') }}">
          @csrf
          <div class="row">
            <div class="col-sm-6">
              <div class="mb-3">
                <input name="firstname" value="{{ old('firstname') }}" type="text" class="form-control" placeholder="First Name">
                  @error('firstname')
                      <span class="invalid-feedback" role="alert">
                          <strong>{{ $message }}</strong>
                      </span>
                  @enderror
              </div>
            </div>
            <div class="col-sm-6">
              <div class="mb-3">
                <input type="text" value="{{ old('lastname') }}" name="lastname" class="form-control" placeholder="Last Name">
                  @error('lastname')
                      <span class="invalid-feedback" role="alert">
                          <strong>{{ $message }}</strong>
                      </span>
                  @enderror
              </div>
            </div>
          </div>  
          <div class="mb-3">
            <input type="email" value="{{ old('middlename') }}" name="middlename" class="form-control" placeholder="Middle Name">
                  @error('middlename')
                      <span class="invalid-feedback" role="alert">
                          <strong>{{ $message }}</strong>
                      </span>
                  @enderror
          </div>
          <div class="mb-3">
            <input type="email" value="{{ old('email') }}" name="email" class="form-control" placeholder="Email Address">
                  @error('email')
                      <span class="invalid-feedback" role="alert">
                          <strong>{{ $message }}</strong>
                      </span>
                  @enderror
          </div>
          <div class="mb-3">
            <input type="number" value="{{ old('phone') }}" name="phone" class="form-control" placeholder="Phone number">
                  @error('number')
                      <span class="invalid-feedback" role="alert">
                          <strong>{{ $message }}</strong>
                      </span>
                  @enderror
          </div>
          <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password">
              @error('password')
                  <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                  </span>
              @enderror
          </div>
          <div class="mb-3">
            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password">
          </div>
          <div class="d-flex mt-1 justify-content-between">
            <div class="form-check">
              <input class="form-check-input input-primary" type="checkbox" id="customCheckc1">
              <label class="form-check-label text-muted" for="customCheckc1">I agree to all the Terms & Condition</label>
            </div>
          </div>
          <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary">Create Account</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
