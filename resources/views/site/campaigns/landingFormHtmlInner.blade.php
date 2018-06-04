
<!-- HTML API for Landing Form "{{$landingForm->name}}" of campaign "{{ $campaign->name }}" --!>

<form method="post" action="{{ URL::route('landing.api.html', [$campaign->slug, $landingForm->slug]) }}">

<label>Redirect Url - After submitting user will be redirected to this url, use it for 'Thank you' page : </label>
<input type="hidden" name="redirectUrl" value="https://www.sanityos.com/about-us"> 
<br /><br />

	@if(sizeof($formData) > 0)
		@foreach($formData as $form)

<label>{{ $form['fieldName'] }} : </label> 
@if($form['type'] == 'textarea')
<textarea title="{{ $form['fieldName'] }}" name="{{ $form['name'] }}" placeholder="{{ $form['fieldName'] }}" @if($form['required'] == 'Yes') required="" @endif></textarea>
@elseif($form['type'] == 'dropdown')
<select title="{{ $form['fieldName'] }}" id="{{ $form['fieldName'] }}" @if($form['required'] == 'Yes') required="" @endif name="{{ $form['name'] }}">
	<option value="">Please select : {{ $form['fieldName'] }}</option>
	@foreach($form['values'] as $data)
	<option value="{{$data}}">{{$data}}</option>
	@endforeach
</select>
@elseif($form['type'] == 'date')
<!-- This is a date field, supported formats are listed at http://php.net/manual/en/datetime.formats.date.php  --!>
<input title="{{ $form['fieldName'] }}" type="text" placeholder="{{ $form['fieldName'] }}" @if($form['required'] == 'Yes') required="" @endif name="{{ $form['name'] }}">
@else
<input title="{{ $form['fieldName'] }}" type="text" placeholder="{{ $form['fieldName'] }}" @if($form['required'] == 'Yes') required="" @endif name="{{ $form['name'] }}">
@endif
<br /><br />

		@endforeach
	@endif

<br />
<button type="submit">Submit</button>

</form>
