@extends(isset($user) ? 'layouts.dashboard' : 'layouts.homepage')

@section('title')
	HTML API for Landing Form "{{$landingForm->name}}" of campaign "{{ $campaign->name }}"
@stop

@section('content')
<div class="pageheader">
	<h2><i class="glyphicon glyphicon-edit"></i>HTML API for Landing Form "{{$landingForm->name}}" of campaign "{{ $campaign->name }}" </h2>
</div>

<div class="contentpanel">

@if($finish == true)
	
	<p>{{$message}}</p>

@else

@section('form_body')
@include('site/campaigns/landingFormHtmlInner')
@endsection

	<div class="panel panel-default" style="background-color: white;color: black">
		<div class="panel-heading" style="background-color: white;color: black"><h3>HTML Code</h3></div>
		<div class="panel-body">

<textarea rows="10" style="width:100%;background: rgba(238,238,238,.35);color: black" readonly>
<?php echo e(str_replace("<br /><br />\n\n", "", $__env->yieldContent('form_body'))); ?>
</textarea>

		</div>
	</div>
	<div class="panel panel-default" style="background-color: white;color: black">
		<div class="panel-heading" style="background-color: white;color: black"><h3>PHP Code</h3></div>
		<div class="panel-body">

@section('php_code')
@include('site/campaigns/landingFormPhpInner')
@endsection

<textarea rows="10" style="width:100%;background: rgba(238,238,238,.35);color: black" readonly>
<?php echo e($__env->yieldContent('php_code')); ?>
</textarea>

		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading" style="background-color: white;color: black"><h3>Live Demo</h3></div>
		<div class="panel-body">
			@yield('form_body')
		</div>
	</div>
@endif 


</div>
@stop

@section('javascript')
{!! Html::script('assets/js/custom.js') !!}
<script>
	//unhide redirect URL for testing
	var elements = document.getElementsByName("redirectUrl");
	if(elements.length) {
		elements[0].type = 'text';
	}
</script>
@stop
