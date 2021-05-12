@extends('layouts.app')
@section('content')

<div class="signup_full mt-8 bg-white">

  <!-- Start form_signup_onek -->
  <section class="form_signup_one signup_two">
    <div class="container">
      <div class="row">

        <div class="col-md-7 col-lg-5 mx-auto">
          <div class="item_group card card-shadow border-0 radius-5">
            <form method="POST" action="{{ route('user-profile-login-post', ['profile' => $user->username, 'type' => 'reset-password-send']) }}" class="row">
                @csrf
              <div class="col-12">
                <div class="title_sign">
                  <h2>{{ __('Reset Password') }}</h2>
                  <p>{{ __('Please enter your email so we can send you a reset link') }}</p>
                </div>
              </div>
              <div class="col-12 mb-0">
                <div class="form-group">
                  <label>{{ __('Email') }}</label>
                  <input type="email" name="email" class="form-control" placeholder="{{ __('Enter your email address') }}">
                </div>
              </div>
              <div class="col-12">
                <button class="btn w-100 btn_account bg-orange-red c-white rounded-8" style="{{ store_colors($uid, 'css') }}">
                  {{ __('Proceed') }}
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
