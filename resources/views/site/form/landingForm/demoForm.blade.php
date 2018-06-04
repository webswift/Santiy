<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="images/favicon.png" type="image/png">
    <title>Landing Form Demo</title>

    {!!  Html::style('assets/css/style.default.css') !!}
    {!! Html::style('assets/css/landingform.css') !!}
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
</head>

<body class="horizontal-menu fixed" @if($color != '' && $color != null) style="background-color: {{ $color }}" @endif>

<section>
    <div class="leftpanel">
        @if(isset($logo) && $logo != '')
        <div class="logopanel"><img src="{{ $logo }}" style="height: 30px;"/></div>
        @endif
    </div><!-- leftpanel -->

    <div class="mainpanel" style="height:auto;min-height:auto">
        <div class="headerbar">
            <div class="header-left">
                @if(isset($logo) && $logo != '')
                <div class="logopanel">
                    <h1><img src="{{ $logo }}" style="height: 30px;"/></h1>
                </div>
                @endif
            </div>
        </div>

        <div class="panel panel-default panel-blog">
            <div class="panel-body">
				@if(trim($header) != '')
				<div class="custom-header">
                {!! $header !!}
				</div>
                @endif
                <form class="form-horizontal form-bordered">
                    @if(sizeof($formData) > 0)
                        @foreach($formData as $form)
                            <div class="form-group">
                                {{-- <label class="col-sm-3 control-label">{{ $form['fieldName'] }}</label> --}}
                                <div class="col-sm-12">
                                    @if($form['type'] == 'textarea')
                                        <textarea title="{{ $form['fieldName'] }}" name="" placeholder="{{ $form['fieldName'] }}" class="form-control"></textarea>
                                    @elseif($form['type'] == 'date')
									<div class="input-group">
										<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
										<input title="{{ $form['fieldName'] }}" type="text" placeholder="{{ $form['fieldName'] }}" 
											class="form-control customDateField" >
									</div>
                                    @elseif($form['type'] == 'dropdown')
                                        <select title="{{ $form['fieldName'] }}" id="{{ $form['fieldName'] }}" class="form-control">
                                            <option value="">Please select : {{ $form['fieldName'] }}</option>
                                            @foreach($form['values'] as $data)
                                                <option value="{{$data}}">{{$data}}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input title="{{ $form['fieldName'] }}" type="text" placeholder="{{ $form['fieldName'] }}" class="form-control">
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

				@if(trim($footer) != '')
				<div class="custom-footer">
                {!! $footer !!}
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
</body>
</html>
