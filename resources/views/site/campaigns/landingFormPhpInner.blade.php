&lt;?php
/*
Sample of cURL API for Landing Form "{{$landingForm->name}}" of campaign "{{ $campaign->name }}" --!>
*/

$params = [
@if(sizeof($formData) > 0)
@foreach($formData as $form)
	'{{ $form['name'] }}'=>'value for field {{ $form['fieldName'] }}', 
@endforeach
@endif
];

$url = '{{ URL::route('landing.api.html', [$campaign->slug, $landingForm->slug]) }}';

$options = [
	CURLOPT_URL => $url, 
	CURLOPT_POST => true,
	CURLOPT_POSTFIELDS => $params,
	CURLOPT_RETURNTRANSFER => true,
];

$ch = curl_init();
curl_setopt_array($ch, $options);

$output = curl_exec($ch);
$info = curl_getinfo($ch);

if($output === true || $info['http_code'] != 200) {
	$message = "No cURL data returned for {$url}, error code : {$info['http_code']}";
	if(curl_error($ch)) {
		$message .= "\n". curl_error($ch);
	}
	echo $message . "\n";
} else {
	echo "Success\n";
}

curl_close($ch);
