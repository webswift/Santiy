<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="images/favicon.png" type="image/png">
    <title>{{ $campaign->name }} Landing Form</title>

    {!! Html::style('assets/css/style.default.css') !!}
    {!! Html::style('assets/css/landingform.css') !!}
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
</head>

<body class="horizontal-menu fixed" @if($landingForm->color != '' && $landingForm->color != null) style="background-color: {{ $landingForm->color }}" @endif>

<section>
    <div class="leftpanel">
        @if($landingForm->logo != '' && $landingForm->logo != null)
            <div class="logopanel"><img src="{{ URL::asset('assets/uploads/forms/logo/'.$landingForm->logo) }}" style="height: 30px;"/></div>
        @endif
    </div><!-- leftpanel -->

    <div class="mainpanel" style="height:auto;min-height:auto">
        <div class="headerbar">
            <div class="header-left">
                @if($landingForm->logo != '' && $landingForm->logo != null)
                <div class="logopanel">
                    <h1><img src="{{ URL::asset('assets/uploads/forms/logo/'.$landingForm->logo) }}" style="height: 30px;"/></h1>
                </div>
                @endif
            </div>
        </div>

        <div class="panel panel-default panel-blog" style=" ">
            <div class="panel-body">
				@if(trim($landingForm->header) != '')
				<div class="custom-header">
                {!! $landingForm->header !!}
				</div>
                @endif

                @if($finish == true)
                    <p>{{$message}}</p>
                @else
                <form class="form-horizontal form-bordered" method="post" action="{{ URL::route('landing.signup', [$campaign->slug, $landingForm->slug]) }}">
                    {!! csrf_field() !!}
                    @if(sizeof($formData) > 0)
                        @foreach($formData as $form)
                            <div class="form-group">
                                {{-- <label class="col-sm-3 control-label">{{ $form['fieldName'] }}</label> --}}
                                <div class="col-sm-12">
                                    @if($form['type'] == 'textarea')
									<textarea title="{{ $form['fieldName'] }}" 
										name="{{ $form['name'] }}" placeholder="{{ $form['fieldName'] }}" @if($form['required'] == 'Yes') required="" @endif class="form-control"></textarea>
                                    @elseif($form['type'] == 'dropdown')
                                        <select title="{{ $form['fieldName'] }}" 
												id="{{ $form['fieldName'] }}" @if($form['required'] == 'Yes') required="" @endif class="form-control" name="{{ $form['name'] }}">
                                            <option value="">Please select : {{ $form['fieldName'] }}</option>
                                            @foreach($form['values'] as $data)
                                                <option value="{{$data}}">{{$data}}</option>
                                            @endforeach
                                        </select>
                                    @elseif($form['type'] == 'date')
									<div class="input-group">
										<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
										<input title="{{ $form['fieldName'] }}" type="text" placeholder="{{ $form['fieldName'] }}" 
											class="form-control customDateField" @if($form['required'] == 'Yes') required="" @endif name="{{ $form['name'] }}">
									</div>
                                    @else
									<input title="{{ $form['fieldName'] }}" 
										type="text" placeholder="{{ $form['fieldName'] }}" class="form-control" @if($form['required'] == 'Yes') required="" @endif name="{{ $form['name'] }}">
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif

					<div class="form-group">
						<div class="form-action">
							<div class="col-md-8">
								<button class="btn btn-primary" type="submit">Submit</button>
							</div>
						</div>
                    </div>
                    <div class="clearfix"></div>
                </form>
                @endif
				@if(trim($landingForm->footer) != '')
				<div class="custom-footer">
                {!! $landingForm->footer !!}
				</div>
				@endif
            </div>
        </div>
    </div>

</section>

{!! HTML::script('assets/js/jquery-1.11.1.min.js') !!}
{!! HTML::script('assets/js/jquery-ui-1.10.3.min.js') !!}
{!! HTML::script('assets/js/bootstrap.min.js') !!}
{!! HTML::script('assets/js/toggles.min.js') !!}
{!! HTML::script('assets/js/jquery.cookies.js') !!}
{!! HTML::script('assets/js/custom.js') !!}
{!! HTML::script('assets/js/custom_date_field.js') !!}

<script>
$(function() {
	if(window.self !== window.top) {
		$('.headerbar').remove();
	}
});
</script>
</body>
</html>
