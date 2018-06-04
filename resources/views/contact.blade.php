@extends("base")

@section("title")
	Contact Us - Lead Management Software
@endsection

@section("content")
<div class="main">
	<div class="container">
		<div class="row margin-bottom-40">
			<!-- BEGIN CONTENT -->
			<div class="col-md-12">
				<h1>Contact SanityOS</h1>
				<div class="content-page">
					<div class="row">
						<div class="col-md-12"></div>
						@if (!Session::has("contactSuccess"))
						<div class="col-md-9 col-sm-9" id="contactform">
							<p>Thank you for taking the time to check out SanityOS.com. If you would like to arrange a remote demonstration or would
	like a sales representative to call you please email us or use the contact form below to get in touch.</p>
                            @if(Session::has('formName') && Session::get('formName') == 'contact')
							@if (count($errors) > 1)
								<div class="alert alert-danger">
									<ul>
										@foreach ($errors->all() as $error)
											<li>{{ $error }}</li>
										@endforeach
									</ul>
								</div>
							@elseif(count($errors) == 1)
								<div class="alert alert-danger">
									@foreach ($errors->all() as $error)
										{{ $error }}
									@endforeach
								</div>
							@endif
                            @endif
						<!-- BEGIN FORM-->

							<form action="{{ URL::route("contactform") }}" role="form" method="POST">
								{!!  Honeypot::generate('password', 'confirmPassword') !!}
								{!! csrf_field() !!}
								<div class="form-group">
									<label for="contacts-name">Name</label>
									<input type="text" class="form-control" id="contacts-name" name="name">
								</div>

								<div class="form-group">
									<label for="contacts-email">Email</label>
									<input type="email" class="form-control" id="contacts-email" name="email">
								</div>

								<div class="form-group">
									<label for="contacts-message">Message</label>
									<textarea class="form-control" rows="5" id="contacts-message" name="request_message"></textarea>
								</div>
								<button type="submit" class="btn btn-primary"><i class="icon-ok"></i> Send</button>
							</form>
						</div>
						@else
							{{--<div class="alert alert-success">{{ Session::get("success") }}</div>--}}

						<div class="col-md-9 col-sm-9" id="success">
							<div class="note note-success">
								<h4 class="block">Thanks!</h4>
								<p>Thank you for your request. We will try to get back to you as soon as possible.</p>
							</div>
						</div>
						@endif

						<div class="col-md-3 col-sm-3 sidebar2">
							<h2>Our Contacts</h2>
							<address>
								<strong>Sanity OS Limited</strong><br>
								Slapton Hill Offices<br>
								Blakesley Road,<br>
								Towcester, Northants,<br>
								NN12 8QD<br>United Kingdom
							</address>
							<address>
								<strong>Email</strong><br>
								<a href="mailto:contact@sanityos.com">contact@sanityos.com</a><br>
							</address>
						</div>
					</div>
				</div>
			</div>
			<!-- END CONTENT -->
		</div>
	</div>
</div>
@endsection