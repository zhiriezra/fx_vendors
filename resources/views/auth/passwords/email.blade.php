@extends('auth.master')

@section('title', 'Home: FarmEx')

@section('content')

<div class="auth-form">
        <div class="card my-5 mx-3">
            <div class="card-body">
                <h4 class="f-w-500 mb-1">Reset your password</h4>
                {{-- <p class="mb-3">Don't have an Account? <a href="../pages/register-v2.html" class="link-primary ms-1">Create Account</a></p> --}}

                @if (session()->has('message'))
                    <div class="alert alert-success">
                        {{ session('message') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('password.email') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" id="floatingInput1" placeholder="Email address" value="{{ old('email') }}">
                        @error('email')
                            <div class="text-danger">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">Send Reset Link</button>
                    </div>
                </form>

            </div>
        </div>
</div>

@endsection