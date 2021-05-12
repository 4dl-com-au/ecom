@extends('layouts.app')
@section('content')


@php
  $register = request()->get('register') ?? 0;
@endphp
<div class="signup_full mt-8 bg-white">

  <!-- Start form_signup_onek -->
  <section class="form_signup_one signup_two">
    <div class="container">
      <div class="row">

        <div class="col-md-7 col-lg-5 mx-auto">
          <div class="item_group card card-shadow border-0 radius-5">
            <form method="POST" action="{{ route('user-profile-login-post', ['profile' => $user->username, 'type' => 'login']) }}" class="row">
                @csrf
              <div class="col-12">
                <div class="title_sign">
                  <h2>{{ !$register ? __('Sign in') : __('Sign up') }}</h2>
                  <p>{{ !$register ? __('Enter your registered email and password to login') : __('Please enter your email and password to register') }}</p>
                  @if (!$register)
                    <a href="{{ route('user-profile-login', ['profile' => $user->username, 'register' => true]) }}" class="btn btn-link pl-0">{{ __('Register') }}</a>

                    @else
                    <a href="{{ route('user-profile-login', ['profile' => $user->username]) }}" class="btn btn-link pl-0">{{ __('Login') }}</a>
                  @endif
                </div>
              </div>
              <div class="col-12 mb-0">
                <div class="form-group">
                  <label>{{ __('Email address') }}</label>
                  <input type="email" name="email" class="form-control" placeholder="email address">
                </div>
              </div>
              @if (!$register)
              <div class="row w-100">
                <div class="col-6 ml-auto text-right">
                  <a href="{{ route('user-profile-login', ['profile' => $user->username, 'reset-password' => true]) }}" class="btn btn-link pr-0">{{ __('Forgot password?') }}</a>
                </div>
              </div>
              @endif
              <div class="col-12">
                <div class="form-group">
                  <label>{{ __('Password') }}</label>
                  <div class="input-group">
                    <input type="password" class="form-control" data-toggle="password" placeholder="{{ __('Enter password') }}" name="password" required="">
                  </div>
                </div>
              </div>

              <div class="col-12">
                <button class="btn w-100 btn_account bg-orange-red c-white rounded-8" style="{{ store_colors($uid, 'css') }}">
                  {{ !$register ? __('Proceed') : __('Register') }}
                </button>
              </div>

            </form>
          </div>
        </div>
      </div>

    </div>
  </section>
  <!-- End.form_signup_one -->
</div>
@endsection
