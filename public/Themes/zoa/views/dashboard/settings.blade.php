@extends('layouts.app')
 @section('content')
 <div class="container">
  <div class="row mt-7">
    <div class="col-md-3">
        @include('dashboard.dashboard-head')
    </div>
    <div class="col-md-9">
<form method="post" action="{{ route('user-store-dashboard-edit-account-post', ['profile' => $user->username]) }}" class="mt-3" enctype="multipart/form-data">
@csrf

 <!-- Edit contact Info -->
 <div class="card card-shadow border-0 radius-7 p-4 mb-4">
     <!-- Title -->
     <div class="card-header radius-5 border-0">
         <h3 class="m-0 fs-19px">{{ __('Contact info') }}</h3>
     </div>
     <div class="card-body">
         <div class="row">
          <div class="col-md-3">
            <div class="avatar-upload mt-0">
                <div class="avatar-edit">
                    <input type="file" id="imageUpload" name="avatar" class="file-image-upload" accept=".png, .jpg, .jpeg, .gif, .svg" />
                    <label for="imageUpload"><i class="tio edit"></i></label>
                </div>
                <div class="avatar-preview card-shadow">
                    <div class="image-preview" style="background-image: url({{ c_avatar(auth_user($uid)->id) }});">
                    </div>
                </div>
            </div>
          </div>
             <div class="col-md-5">
                 <div class="form-group">
                     <label class="label-text">
                         <span>{{ __('First Name') }}</span>
                     </label>
                     <div class="control">
                         <input type="text" class="form-control" name="details[first_name]" value="{{ auth_user($uid)->details->first_name ?? '' }}">
                     </div>
                 </div>

                 <div class="form-group">
                     <label class="label-text">
                         <span>{{ __('Email') }}</span>
                     </label>
                     <div class="control">
                         <input type="email" class="form-control" name="details[email]" value="{{ auth_user($uid)->details->email ?? auth_user($uid)->email }}">
                     </div>
                 </div>
             </div>

             <div class="col-md-4">
                 <div class="form-group">
                     <label class="label-text">
                         <span>{{ __('Last Name') }}</span>
                     </label>
                     <div class="control">
                         <input type="text" class="form-control" name="details[last_name]" value="{{ auth_user($uid)->details->last_name ?? '' }}">
                     </div>
                 </div>
                 <div class="form-group">
                     <label class="label-text">
                         <span>{{ __('Phone') }}</span>
                     </label>
                     <div class="control">
                         <input type="text" class="form-control" name="details[phone_number]" value="{{ auth_user($uid)->details->phone_number ?? '' }}">
                     </div>
                 </div>
             </div>
         </div>

         <button class="btn bg-blue effect-letter c-white radius-5 mt-4 btn_sm_primary">{{ __('Save') }}</button>
     </div>
 </div>
 <!-- /Edit contact Info -->
   <!-- Edit Billing Address -->
   <div class="card card-shadow border-0 radius-7 p-4">
       <!-- Title -->
       <div class="card-header radius-5 border-0">
           <h3 class="m-0 fs-19px">{{ __('Billing Address') }}</h3>
       </div>
       <div class="card-body">
           <div class="row">
               <div class="col-md-6">
                   <!-- Form group -->
                   <div class="form-group">
                       <label class="label-text">
                           <span>{{ __('Number') }}</span>
                       </label>
                       <div class="control">
                           <input type="text" class="form-control" name="details[billing_number]" placeholder="{{ __('23, Block C2') }}" value="{{ auth_user($uid)->details->billing_number ?? '' }}">
                       </div>
                   </div>


                   <div class="form-group">
                       <label class="label-text">
                           <span>{{ __('City') }}</span>
                       </label>
                       <div class="control">
                           <input type="text" class="form-control" placeholder="{{ __('Los Angeles') }}" name="details[city]" value="{{ auth_user($uid)->details->city ?? '' }}">
                       </div>
                   </div>


                   <div class="form-group">
                       <label class="label-text">
                           <span>{{ __('State') }}</span>
                       </label>
                       <div class="control">
                           <input type="text" class="form-control" placeholder="{{ __('CA') }}" name="details[state]" value="{{ auth_user($uid)->details->state ?? '' }}">
                       </div>
                   </div>

               </div>

               <div class="col-md-6">
                   <div class="form-group">
                       <label class="label-text">
                           <span>{{ __('Street') }}</span>
                       </label>
                       <div class="control">
                           <input type="text" class="form-control" placeholder="{{ __('Sunny Street') }}" name="details[street]" value="{{ auth_user($uid)->details->street ?? '' }}">
                       </div>
                   </div>


                   <div class="form-group">
                       <label class="label-text">
                           <span>{{ __('Postal Code') }}</span>
                       </label>
                       <div class="control">
                           <input type="text" class="form-control" name="details[postal_code]" value="{{ auth_user($uid)->details->postal_code ?? '' }}">
                       </div>
                   </div>

                   <div class="form-group">
                       <label class="label-text">
                           <span>{{ __('Country') }}</span>
                       </label>
                         @php
                           $countries = countries();
                           unset($countries[0]);
                         @endphp
                       <div class="control">
                           <select class="form-control custom-select" data-search="off" data-ui="lg" name="details[country]">
                               @foreach ($countries as $item)
                                <option value="{{$item}}" {{ !empty(auth_user($uid)->details->country) &&auth_user($uid)->details->country == $item ? 'selected' : '' }}> {{ $item }} </option>
                               @endforeach
                          </select>
                       </div>
                   </div>
               </div>
           </div>
         <button class="btn bg-blue effect-letter c-white radius-5 mt-4 btn_sm_primary">{{ __('Save') }}</button>
       </div>
   </div>
 </form>
</div>
</div>
</div>
  @stop