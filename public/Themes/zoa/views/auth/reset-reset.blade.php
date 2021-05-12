@extends('layouts.app')
@section('content')

<div class="signup_full mt-8 bg-white">

  <!-- Start form_signup_onek -->
  <section class="form_signup_one signup_two">
    <div class="container">
      <div class="row">

        <div class="col-md-7 col-lg-5 mx-auto">
          <div class="item_group card card-shadow border-0 radius-5">
            <form method="POST" action="{{ route('user-profile-login-post', ['profile' => $user->username, 'type' => 'reset-reset']) }}" class="row">
                @csrf
              <div class="col-12">
                <div class="title_sign">
                  <h2>{{ __('New Password') }}</h2>
                  <p>{{ __('Enter your new password') }}</p>
                </div>
              </div>
              <input type="hidden" value="{{ $code }}" name="code">
              <div class="col-12 mb-0">
                <div class="form-group">
                  <label>{{ __('Password') }}</label>
                  <input type="password" name="password" class="form-control" placeholder="{{ __('Enter your new password') }}">
                </div>
              </div>
              <div class="col-12">
                <button class="btn w-100 btn_account bg-orange-red c-white rounded-8" style="{{ store_colors($uid, 'css') }}">
                  {{ __('Save') }}
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
