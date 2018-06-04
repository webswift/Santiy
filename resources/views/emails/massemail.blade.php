{!! $emailText !!}
@if(!$showSanityOsSignature)
<a href="{{ $unsubscribeLink }}">Click here to unsubscribe</a>
@else
	<br/>
	<br/>
	<br/>
	<br/>
	<br/>
	<br/>
	<br/>
	<br/>
	<br/>
	<br/>
	<br/>
	<br/>
	<br/>
	<br/>
	<br/>
	<br/>
	<br/>
	<br/>
	<center style="font-size: 14px;">
{{--
<img src="https://www.sanityos.com/assets/images/logo.png" style="height: 30px;">
<br/>
--}}
This Email was sent using <a href="https://www.sanityos.com/">SanityOS.com</a> Business Growth & Development Software
<br/>You are receiving this email on behalf of one of our users. 
<br/>If it’s not relevant or you wish to unsubscribe <a href="{{ $unsubscribeLink }}">click here</a>
	</center>
@endif
<img src="{{ $link }}">
