@extends('layouts.app')
@section('content')

<div class="signup_full mt-8 bg-white">

  <!-- Start form_signup_onek -->
  <section class="form_signup_one signup_two">
    <div class="container">
      <div class="row">

        <div class="col-md-7 col-lg-5 mx-auto">
          <div class="item_group card card-shadow border-0 radius-5">
            <form method="POST" action="{{ route('user-profile-login-post', ['profile' => $user->username, 'type' => 'password']) }}" class="row">
                @csrf
            <input type="hidden" value="{{ request()->get('email') }}" name="email">
              <div class="col-12">
                <div class="title_sign">
                  <h2>{{ __('Sign in') }}</h2>
                  <p>{{ __('We sent you a passcode to the email below. Enter the passcode to access your account') }} {{ '@' . request()->get('email') }}</p>
                </div>
              </div>
              <div class="col-12">
                <div class="form-group">
                  <label>{{ __('Passcode') }}</label>
                  <div class="input-group">
                    <input type="password" class="form-control" data-toggle="password" placeholder="{{ __('Enter passcode') }}" name="password" required="">
                  </div>
                </div>
              </div>

              <div class="col-12">
                <button class="btn w-100 btn_account bg-orange-red c-white rounded-8" style="{{ store_colors($uid, 'css') }}">
                  {{ __('Login') }}
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