@extends('layouts.app')
@section('footerJS')
<script>
var totalSales = {
    labels: {!! $sales['sales_chart']['labels'] ?? '[]' !!},
    dataUnit: "{{__('Sold')}}",
    lineTension: .3,
    datasets: [{
         label: "Sales",
         color: "#9d72ff",
         background: NioApp.hexRGB("#9d72ff", .25),
         data: {!! $sales['sales_chart']['sales'] ?? '[]' !!}
     }]
};
</script>
 <script src="{{ asset('js/custom.js?v=' . env('APP_VERSION')) }}"></script>

@stop
 @section('content')
 <div class="container">
  <div class="row mt-7">
    <div class="col-md-3">
        @include('dashboard.dashboard-head')
    </div>
    <div class="col-md-9">
    	
		<div class="row">
		  <div class="col-md-7">
		    <div class="dashboard-card card-shadow bg-white radius-5 p-5 h-100">
		        <span class="text-muted-2">{{ __('Registered on') }}</span>
		        <div class="p-relative">
		           <h3 class="dark-inverted fs-20px">{{ Carbon\Carbon::parse($customer->created_at)->toFormattedDateString() }}</h3>
		        </div>

		          <div class="quick-stats mt-4">
		            <div class="quick-stats-inner">
		                <div class="row">
		                  <div class="col-md-12">
		                    <!--Stat-->
		                    <div class="quick-stat bg-blue p-3 radius-5 text-white">
		                        <div class="media-flex-center">
		                            <div class="flex-meta">
		                                <div class="title">
		                                  <p class="m-0 fs-12px">{{ __('Total Orders') }}</p>
		                                </div>
		                                <span class="fs-40px">{{ number_format(count($all_orders)) }}</span>
		                            </div>

		                            <div class="flex-end">
		                              <a href="{{ route('user-profile-dashboard-orders', ['profile' => $user->username]) }}" class="fs-13px text-white">{{ __('View Orders') }}</a>
		                            </div>
		                        </div>
		                    </div>
		              </div>
		          </div>
		        </div>
		      </div>
		   </div>
		  </div>
		  <div class="col-md-5">
		    <div class="p-3 border-0 card-shadow card h-100">
		                <!-- Title -->
		                <div class="card-header border-0">
		                    <h3 class="fs-20px m-0">{{ __('Billing address') }}</h3>
		                </div>
		                <!-- Billing Address -->
		                <div class="card-body">
		                    <div class="row">
		                        <div class="col-md-6">
		                            <div class="info-block mb-4">
		                                <span class="label-text d-block text-muted">{{ __('Street Number') }}</span>
		                                <span class="label-value d-block">{{ $customer->details->billing_number ?? '' }}</span>
		                            </div>

		                            <div class="info-block mb-4">
		                                <span class="label-text d-block text-muted">{{ __('City') }}</span>
		                                <span class="label-value d-block">{{ $customer->details->city ?? '' }}</span>
		                            </div>

		                            <div class="info-block mb-4">
		                                <span class="label-text d-block text-muted">{{ __('State') }}</span>
		                                <span class="label-value d-block">{{ $customer->details->state ?? '' }}</span>
		                            </div>
		                        </div>

		                        <div class="col-md-6">

		                            <div class="info-block mb-4">
		                                <span class="label-text d-block text-muted">{{ __('Street') }}</span>
		                                <span class="label-value d-block">{{ $customer->details->street ?? '' }}</span>
		                            </div>

		                            <div class="info-block mb-4">
		                                <span class="label-text d-block text-muted">{{ __('Postal Code') }}</span>
		                                <span class="label-value d-block">{{ $customer->details->postal_code ?? '' }}</span>
		                            </div>

		                            <div class="info-block mb-4">
		                                <span class="label-text d-block text-muted">{{ __('Country') }}</span>
		                                <span class="label-value d-block">{{ $customer->details->country ?? '' }}</span>
		                            </div>
		                        </div>
		                    </div>
		                </div>
		                <!-- /Address Form -->
		            </div>
		  </div>
		</div>

		<div class="row">
		  <div class="col-md-12">
		    <div class="card p-4 card-shadow border-0 radius-5 mt-5">
		       <div class="title mb-3">
		         <p class="m-0 fs-15px">{{ __('Amount spent') }}</p>
		       </div>
		      <div class="line-stats mb-4">
		           <div class="line-stat">
		               <span>{{ __('This Month') }}</span>
		               <span class="current">{!! number_format($sales['this_month']) !!}</span>
		           </div>
		           <div class="line-stat">
		               <span>{{ __('Last Month') }}</span>
		               <span class="dark-inverted">{!! number_format($sales['last_month']) !!}</span>
		           </div>
		           <div class="line-stat">
		               <span>{{ __('Total') }}</span>
		               <span class="dark-inverted">{!! number_format($sales['overall_sale']) !!}</span>
		           </div>
		       </div>
		      <div class="h-250px">
		         <canvas class="line-chart" id="totalSales"></canvas>
		      </div>
		    </div>
		  </div>
		  <div class="col-md-6">
		  </div>
		</div>
    </div>
 </div>
</div>
@endsection