@extends('layouts.app')
@section('content')
	@foreach ($sections as $item)
		@if (!empty($data_includes[$item->block_slug]))
			@include('blocks.' . $data_includes[$item->block_slug]['include'], $sections_data[$item->id])
		@endif
	@endforeach
@endsection
