@extends('layouts.app')

@section('content')
<div class="container pt-8">
	<table class="shop_table">
		<thead>
			<tr>
				<th class="product-remove">&nbsp;</th>
				<th class="product-thumbnail">&nbsp;</th>
				<th class="product-name">{{ __('Product') }}</th>
				<th class="product-price">{{ __('Price') }}</th>
				<th class="product-quantity">{{ __('Quantity') }}</th>
				<th class="product-subtotal">{{ __('Subtotal') }}</th>
			</tr>
		</thead>
		<tbody>
		 @foreach ($cart as $key => $item)
			<tr class="cart_item">

				<td class="product-remove">
				   <form action="{{ route('remove-from-cart', ['user' => $user->id, 'id' => $key]) }}" method="post">
				     @csrf
				     <input type="hidden" name="action" value="remove">
				     <button class="delete-item remove delete cursor_pointer">x</button>
				   </form>
				</td>

				<td class="product-thumbnail">
					<a href="{{ route('user-profile-single-product', ['profile' => $user->username, 'id' => $item->associatedModel->id]) }}">
						<img src="{{ getfirstproductimg($item->associatedModel->id) }}" width="300" height="300">
					</a>
				</td>

				<td class="product-name bf" data-title="Product">
					<a href="{{ route('user-profile-single-product', ['profile' => $user->username, 'id' => $key]) }}">{{$item->associatedModel->title}}</a>
					<span class="d-block">{!! \App\Cart::getOptionsAttr($item->attributes->options) !!}</span>
				</td>

				<td class="product-price bf" data-title="Price">
					<span class="amount"><bdi><span>{!! Currency::symbol($user->gateway['currency'] ?? '') !!}</span> {{ nf($item->price * $item->quantity, 2) }}</bdi></span>
				</td>

				<td class="product-quantity bf" data-title="Quantity">
					<form class="product-quantity">
                      <div class="qnt mr-0">
                        <input type="number" id="quantity" class="cart_quantity" data-route="{{ route('update-cart', ['user' => $uid, 'id' => $key]) }}" name="quantity" value="{{ $item->quantity }}">
                        <a class="dec minus nt-button" href="#">-</a>
                        <a class="inc plus qnt-button" href="#">+</a>
                      </div>
                    </form>
				</td>
				<td class="product-subtotal bf" data-title="Subtotal">
					<span class="amount"><bdi><span>{!! Currency::symbol($user->gateway['currency'] ?? '') !!}</span> {{ nf($item->price) }}</bdi></span>
				</td>
			 </tr>
			 @endforeach
		</tbody>
	</table>
	<div class="col-12 col-md-6 ml-auto">
		<a href="{{ route('user-profile-checkout', ['profile' => $user->username]) }}" class="btn btn-primary btn-block ml-auto" style="{{ store_colors($uid, 'css')  }}">{{ __('Update Cart') }}</a>
	</div>
</div>
<div class="container-fluid pt-8">
	<form class="row justify-content-center" method="post" action="{{ route('user-profile-checkout', ['profile' => $user->username]) }}">
		<div class="col-11 col-lg-6">
        	 @csrf
        	  <div class="form-row">
			    <div class="col-12">
			    	<div class="section-head my-3">
			    		<p>{{ __('Your Personal Details') }}</p>
			    	</div>
			    </div>
			   <div class="m-0 card card-shadow border-0 radius-7 p-4 w-100 row flex-row">
			    <p class="form-group col-6">
			      <label>{{ __('First name') }}</label>
			      <input type="text" class="form-control" value="{{ auth_user($uid)->details->first_name ?? '' }}" class="form-control" name="first_name" required="">
			    </p>
			    <p class="form-group col-6">
			      <label>{{ __('Last name') }}</label>
			      <input type="text" class="form-control" value="{{ auth_user($uid)->details->last_name ?? '' }}" class="form-control" name="last_name" required="">
			    </p>
			      <p class="form-group col-6">
			        <label>{{ __('Phone') }}</label>
			        <input type="text" class="form-control" value="{{ auth_user($uid)->details->phone_number ?? '' }}" class="form-control" name="phone" required="">
			      </p>
			      <p class="form-group col-6">
			        <label id="email">{{ __('Email') }}</label>
			        <input type="text" class="form-control" class="form-control" value="{{ auth_user($uid)->details->email ?? '' }}" name="email" required="">
			      </p>
			  </div>
			    <div class="col-12">
			    	<div class="section-head my-3">
			    		<p>{{ __('Your Address') }}</p>
			    	</div>
			    </div>
			       <div class="card card-shadow border-0 radius-7 p-4 w-100">
				       <div class="card-body">
				           <div class="row">
				               <div class="col-md-6">
				                   <!-- Form group -->
				                   <div class="form-group">
				                       <label class="label-text">
				                           <span>{{ __('Number') }}</span>
				                       </label>
				                       <div class="control">
				                           <input type="text" class="form-control" name="billing_number" placeholder="{{ __('23, Block C2') }}" value="{{ auth_user($uid)->details->billing_number ?? '' }}">
				                       </div>
				                   </div>


				                   <div class="form-group">
				                       <label class="label-text">
				                           <span>{{ __('City') }}</span>
				                       </label>
				                       <div class="control">
				                           <input type="text" class="form-control" placeholder="{{ __('Los Angeles') }}" name="city" value="{{ auth_user($uid)->details->city ?? '' }}">
				                       </div>
				                   </div>


				                   <div class="form-group">
				                       <label class="label-text">
				                           <span>{{ __('State') }}</span>
				                       </label>
				                       <div class="control">
				                           <input type="text" class="form-control" placeholder="{{ __('CA') }}" name="state" value="{{ auth_user($uid)->details->state ?? '' }}">
				                       </div>
				                   </div>

				               </div>

				               <div class="col-md-6">
				                   <div class="form-group">
				                       <label class="label-text">
				                           <span>{{ __('Street') }}</span>
				                       </label>
				                       <div class="control">
				                           <input type="text" class="form-control" placeholder="{{ __('Sunny Street') }}" name="street" value="{{ auth_user($uid)->details->street ?? '' }}">
				                       </div>
				                   </div>


				                   <div class="form-group">
				                       <label class="label-text">
				                           <span>{{ __('Postal Code') }}</span>
				                       </label>
				                       <div class="control">
				                           <input type="text" class="form-control" name="postal_code" value="{{ auth_user($uid)->details->postal_code ?? '' }}">
				                       </div>
				                   </div>
				               </div>

				               <div class="col-12">
								 <div class="w-100 my-5">
								    <p class="form-group notes">
								      <label class="py-3">{{ __('Additional info') }}</label>
								      <textarea name="note" class="form-control" id="order_comments" placeholder="Notes about your order, e.g. special notes for delivery." rows="2" cols="5"></textarea></p>
								 </div>
				               </div>
				           </div>
				       </div>
				   </div>
				<div class="col-12 mb-4 mb-3 {{ user('extra.shipping_types', $uid) == 'disable' ? 'd-none' : '' }}">
					    <div class="col-12">
					    	<div class="section-head my-3">
					    		<p>{{ __('Shipping') }}</p>
					    	</div>
					    </div>
					<div class="radius-7 card card-shadow border-0 p-md-3 p-4">
					    <p class="form-group col-12">
					      <label class="py-3">{{ __('Country') }} <abbr class="required" title="required"></abbr>
					      </label>
					      @php
					      	$countries = countries();
					      	unset($countries['Worldwide']);
					      @endphp
		                   <select class="form-control form-select checkout-country-selector" name="country">
		                   	@if (user('extra.shipping_types', $uid) == 'enable')
		                      @foreach ($countries as $item)
		                        <option value="{{$item}}" {{ !empty(auth_user($uid)->details->country) && auth_user($uid)->details->country == $item ? 'selected' : '' }}>{{ $item }}</option>
		                      @endforeach
		                   	@endif
		                   	@if (user('extra.shipping_types', $uid) == 'my_shipping' && !empty(user('shipping', $uid)))
							 @foreach (user('shipping', $uid) as $key => $value)
		                        <option value="{{$key}}">{{ $key }}</option>
		                      @endforeach
		                   	@endif
		                  </select>
					    </p>
					    <div class="alert alert-info no-shipping" role="alert">
					    	@if (user('extra.shipping_types', $uid) == 'disable')
                           		<input type="radio" name="shipping_location" checked="" value="none">
                           		@elseif(user('extra.shipping_types', $uid) == 'enable')
                           		<input type="radio" name="shipping_location" value="none" class="no-shipping-val">
					    	@endif
					       {{ __('No Shipping Location available') }}
					    </div>
					    @if (!empty(user('shipping', $uid)))
                        @foreach (user('shipping', $uid) as $key => $value)
					       <div class="row align-items-stretch shipping-locations hide" data-country="{{ $key }}">
					       	@foreach ($value as $key => $value)
                            <div class="col-md-4 col-6 mb-4 mt-4 pricing-select">
                            <input type="radio" name="shipping_location" data-price="{{ $value['cost'] ?? '' }}" value="{{$key}}" class="custom-control-input" required="required" id="{{$key}}_price" data-price="">
                                <div class="pricing-select-inner flex-column">
                                    <label for="{{$key}}_price" class="px-4">{{ __('Choose') }}</label>
                                  <div class="mt-3 text-center mb-1">
                                  	<p class="m-0 fs-10px">{{ $value['type'] == 'flat' ? 'Flat Rate' : '' }} {{ $value['type'] == 'free' ? 'Free shipping' : '' }} {{ $value['type'] == 'pickup' ? 'Pickup Station' : '' }}</p>
                                    <span class="price">{!! Currency::symbol($user->gateway['currency'] ?? '') !!} {{ $value['type'] == 'free' ? 'Free' : nf($value['cost'] ?? 0) }}</span>
                                    <div class="muted-deep d-block">{{ $key }}</div>
                                    </div>
                                </div>
                            </div>
					       	@endforeach
						  </div>
                        @endforeach
					    @endif
                    </div>
				</div>
		 </div>
		</div>
		<div class="col-12 col-xl-4">
		<h5 class="font-weight-normal">{{ __('Your order') }}</h5>
			<div class="checkout-review-order">
				<div class="checkout-review-order-table">
					<div class="product-container bg-light-gray bdrs-20 p-3 mb-4 card-shadow">
		 			@foreach ($cart as $key => $item)
					  <div class="cart-item">
						<div class="product-image mr-2">
							<a href="{{ route('user-profile-single-product', ['profile' => $user->username, 'id' => $item->associatedModel->id]) }}" target="_blank">
								<img src="{{ getfirstproductimg($item->associatedModel->id) }}">
							</a>
						</div>
						<div class="product-info mt-2">
							<h4>{{$item->associatedModel->title}}&nbsp; <span class="product-quantity">{{ __('Quantity') }}: {{ $item->quantity }}</span>
							</h4>
							<span class="amount"><bdi><span>{!! Currency::symbol($user->gateway['currency'] ?? '') !!}</span> {{ nf(!empty($item->salePrice) ? ($item->quantity * $item->associatedModel->salePrice) : ($item->quantity * $item->price)) }}</bdi></span>
						 </div>
						</div>
						@endforeach
					</div>
				</div>
			</div>
			<div class="bg-light-gray product-container bdrs-20 card-shadow padding-30">
				<h5 class="font-weight-normal">{{ __('Your order') }}</h5>
				<table class="table cart-totals">
					<tbody>
						<tr>
							<th scope="row">{{ __('Qty') }}</th>
							<td>{{ \App\Cart::total($uid, 'quantity') }}<!-- <form action="{{-- route('user-add-to-cart', ['profile' => $user->username, 'id' => $key]) --}}" method="post">
						         @csrf
						         <input type="hidden" name="action" value="remove">
						         <button class="delete-item delete text-danger cursor_pointer">{{ __('Remove all') }}</button>
						       </form> -->
						   </td>
						</tr>
						<tr>
							@if (!empty($gateways))
							<th scope="row" class="mt-5"><div class="d-flex align-items-center mt-4">{{ __('Gateway') }}</div></th>
							<td>
					            @foreach ($gateways as $key => $values)
					            @if (user('gateway.'.$key.'_status', $user->id))
					            <div class="custom-control custom-radio custom-control-sm m-3">
					              <input type="radio" class="custom-control-input" id="gateway_{{$key}}" name="gateway" value="{{$key}}">
					              <label class="custom-control-label" for="gateway_{{$key}}">{{ $values['name'] }}</label>
					            </div>
					            @endif
					            @endforeach
							</td>
							@endif
						</tr>
          				@if (user('gateway.bank_status', $user->id))
						<tr>
							<th scope="row">{{ __('Bank transfer') }}</th>
							<td><p>{{ user('gateway.bank_details', $user->id) }} - {{ __('Contact us for confirmation') }}</td>
						</tr>
						@endif
						<tr>
							<th scope="row">{{ __('Total') }}</th>
							<td class="total-in-cart" data-total="{{ \App\Cart::total($uid) }}">{!! Currency::symbol($user->gateway['currency'] ?? '') !!} <span>{{ nf(\App\Cart::total($uid), 2) }}</span></td>
						</tr>
					</tbody>
				</table>
				@if (count($cart) > 0)
					@if (!user('extra.guest_checkout', $uid) && !auth_user($uid, 'check'))
						<a href="{{ route('user-profile-login', ['profile' => $user->username]) }}" class="btn btn_sm_primary btn-block" style="{{ store_colors($uid) }}">{{ __('Login') }}</a>
						@else

						<button class="button button-md button-dark button-fullwidth margin-top-20" style="{{ store_colors($uid) }}">{{ __('Place order') }}</button>
					@endif
					@else
					<h4 class="text-center">{{ __('Cart Empty') }}</h4>
				@endif
			</div>
		</div>
	</form><!-- end row -->
</div>
@endsection
