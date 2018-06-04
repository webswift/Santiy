@extends('layouts.admindashboard')

@section('title')
	Manage Users
@stop

@section('css')
	{!! Html::style('assets/css/jquery.datatables.css') !!}
@stop

@section('content')

<div class="pageheader">
	<h2><i class="fa fa-user"></i>Manage Users</h2>
	<div class="breadcrumb-wrapper">
		<span class="label">You are here:</span>
		<ol class="breadcrumb">
			<li>Super Admin</li>
			<li class="active"><a href="{{ URL::route("admin.users") }}">Manage Users</a></li>
        </ol>
	</div>
</div>

<div class="contentpanel">
	<div class="col-sm-12">
		<div class="panel">
			<div class="panel-body">
				@if($successMessage != '')
				<div class="row">
					<div class="col-sm-12">
						<div class="alert alert-success">
							<a class="close" data-dismiss="alert" href="#" aria-hidden="true">×</a>
							{{ $successMessage }}
						</div>
					</div>
				</div>
				@endif

				<div class="col-md-3">
					<div class="form-group">
						<select id="viewUserAction" class="form-control mb15">
							<option value="all"> All Users</option>
							<option value="active">Active Users</option>
							<option value="blocked">Blocked Users</option>
							<option value="expired">Expired License</option>
							<option value="expiredsoon">Expiring Soon</option>
							<option value="trial">Trial Users</option>
						</select>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<button class="btn btn-primary mr5" data-toggle="modal" data-target=".bs-example-modal-lg">Add a New User</button>
					</div>
				</div>
			</div><!-- panel-body -->
		</div>

		<div class="panel">
			<div class="panel-body">
				<div class="table-responsive">
					<table id="users" class="table table-striped" id="table2">
						<thead>
						<tr>
							<th>#</th>
							<th>First Name</th>
							<th>Company Name</th>
							<th>Email</th>
							<th>Last Logged in on</th>
							<th>Login Count</th>
							<th>Contact Number</th>
							<th>Renewal date</th>
							<th>License type</th>
							<th>Edit</th>
							<th>Delete</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>


<!-- Add User Model Strat -->
<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
	    	<div class="modal-header">
	            <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
	            <h4 class="modal-title">Create a New User (Mimics user that has already paid till expire date)</h4>
	        </div>
			<div class="modal-body">
				{!! Form::open(array('route' => 'admin.users.createuser', 'id' => 'createUserForm', 'method' => 'post')) !!}
				<div id="error" ></div>
				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">First Name</label>
							<input type="text" name="firstName" id="firstName" class="form-control input-sm">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">Last Name</label>
							<input type="text" name="lastName" id="lastName" class="form-control input-sm">
						</div>
					</div>
				</div>
                <div class="row">
	                <div class="col-sm-6">
	                  <div class="form-group">
	                    <label class="control-label">Email Address*</label>
	                    <input type="text" name="email" id="email" class="form-control input-sm">
	                  </div>
	                </div><!-- col-sm-6 -->
	                <div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">Telephone</label>
							<input type="text" name="contactNumber" id="telephone" class="form-control input-sm">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6 hidden">
						<div class="form-group">
							<label class="control-label">License Type</label>
							<input type="hidden" value="" id="licenseClass" name="licenseClass">

							<select name="licenseType" id="licenseType" class="form-control input-sm">
								<option value="" rel="">Select License Type</option>
								@foreach($allLicenseTypes as $allLicenseType)
								<option value="{{ $allLicenseType->id }}" @if($allLicenseType->licenseClass == 'Multi') selected @endif rel="{{ $allLicenseType->licenseClass }}">{{ $allLicenseType->name }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">Expires On</label>
							<div class="input-group">
								<input type="text" name="expireOn" id="expireOn" style="width:100%; padding: 3px" >
								<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
							</div>
	                  	</div>
	                </div><!-- col-sm-6 -->

	                <div class="col-sm-6">
	                  <div class="form-group">
	                    <label class="control-label">Company Name</label>
	                    <input type="text" name="companyName" id="companyName" class="form-control input-sm">
	                  </div>
	                </div><!-- col-sm-6 -->

                </div>

                <div class="row">
	                <div class="col-sm-6">
	                  <div class="form-group">
	                    <label class="control-label">Country</label>
	                    <select name="country" name="country" id="country" class="form-control input-sm">
	                      <option value="United States">United States</option>
		                  <option value="United Kingdom">United Kingdom</option>
		                  <option value="Afghanistan">Afghanistan</option>
		                  <option value="Aland Islands">Aland Islands</option>
		                  <option value="Albania">Albania</option>
		                  <option value="Algeria">Algeria</option>
		                  <option value="American Samoa">American Samoa</option>
		                  <option value="Andorra">Andorra</option>
		                  <option value="Angola">Angola</option>
		                  <option value="Anguilla">Anguilla</option>
		                  <option value="Antarctica">Antarctica</option>
		                  <option value="Antigua and Barbuda">Antigua and Barbuda</option>
		                  <option value="Argentina">Argentina</option>
		                  <option value="Armenia">Armenia</option>
		                  <option value="Aruba">Aruba</option>
		                  <option value="Australia">Australia</option>
		                  <option value="Austria">Austria</option>
		                  <option value="Azerbaijan">Azerbaijan</option>
		                  <option value="Bahamas">Bahamas</option>
		                  <option value="Bahrain">Bahrain</option>
		                  <option value="Bangladesh">Bangladesh</option>
		                  <option value="Barbados">Barbados</option>
		                  <option value="Belarus">Belarus</option>
		                  <option value="Belgium">Belgium</option>
		                  <option value="Belize">Belize</option>
		                  <option value="Benin">Benin</option>
		                  <option value="Bermuda">Bermuda</option>
		                  <option value="Bhutan">Bhutan</option>
		                  <option value="Bolivia, Plurinational State of">Bolivia, Plurinational State of</option>
		                  <option value="Bonaire, Sint Eustatius and Saba">Bonaire, Sint Eustatius and Saba</option>
		                  <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
		                  <option value="Botswana">Botswana</option>
		                  <option value="Bouvet Island">Bouvet Island</option>
		                  <option value="Brazil">Brazil</option>
		                  <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
		                  <option value="Brunei Darussalam">Brunei Darussalam</option>
		                  <option value="Bulgaria">Bulgaria</option>
		                  <option value="Burkina Faso">Burkina Faso</option>
		                  <option value="Burundi">Burundi</option>
		                  <option value="Cambodia">Cambodia</option>
		                  <option value="Cameroon">Cameroon</option>
		                  <option value="Canada">Canada</option>
		                  <option value="Cape Verde">Cape Verde</option>
		                  <option value="Cayman Islands">Cayman Islands</option>
		                  <option value="Central African Republic">Central African Republic</option>
		                  <option value="Chad">Chad</option>
		                  <option value="Chile">Chile</option>
		                  <option value="China">China</option>
		                  <option value="Christmas Island">Christmas Island</option>
		                  <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
		                  <option value="Colombia">Colombia</option>
		                  <option value="Comoros">Comoros</option>
		                  <option value="Congo">Congo</option>
		                  <option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
		                  <option value="Cook Islands">Cook Islands</option>
		                  <option value="Costa Rica">Costa Rica</option>
		                  <option value="Cote D'ivoire">Cote D'ivoire</option>
		                  <option value="Croatia">Croatia</option>
		                  <option value="Cuba">Cuba</option>
		                  <option value="Curacao">Curacao</option>
		                  <option value="Cyprus">Cyprus</option>
		                  <option value="Czech Republic">Czech Republic</option>
		                  <option value="Denmark">Denmark</option>
		                  <option value="Djibouti">Djibouti</option>
		                  <option value="Dominica">Dominica</option>
		                  <option value="Dominican Republic">Dominican Republic</option>
		                  <option value="Ecuador">Ecuador</option>
		                  <option value="Egypt">Egypt</option>
		                  <option value="El Salvador">El Salvador</option>
		                  <option value="Equatorial Guinea">Equatorial Guinea</option>
		                  <option value="Eritrea">Eritrea</option>
		                  <option value="Estonia">Estonia</option>
		                  <option value="Ethiopia">Ethiopia</option>
		                  <option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
		                  <option value="Faroe Islands">Faroe Islands</option>
		                  <option value="Fiji">Fiji</option>
		                  <option value="Finland">Finland</option>
		                  <option value="France">France</option>
		                  <option value="French Guiana">French Guiana</option>
		                  <option value="French Polynesia">French Polynesia</option>
		                  <option value="French Southern Territories">French Southern Territories</option>
		                  <option value="Gabon">Gabon</option>
		                  <option value="Gambia">Gambia</option>
		                  <option value="Georgia">Georgia</option>
		                  <option value="Germany">Germany</option>
		                  <option value="Ghana">Ghana</option>
		                  <option value="Gibraltar">Gibraltar</option>
		                  <option value="Greece">Greece</option>
		                  <option value="Greenland">Greenland</option>
		                  <option value="Grenada">Grenada</option>
		                  <option value="Guadeloupe">Guadeloupe</option>
		                  <option value="Guam">Guam</option>
		                  <option value="Guatemala">Guatemala</option>
		                  <option value="Guernsey">Guernsey</option>
		                  <option value="Guinea">Guinea</option>
		                  <option value="Guinea-bissau">Guinea-bissau</option>
		                  <option value="Guyana">Guyana</option>
		                  <option value="Haiti">Haiti</option>
		                  <option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
		                  <option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
		                  <option value="Honduras">Honduras</option>
		                  <option value="Hong Kong">Hong Kong</option>
		                  <option value="Hungary">Hungary</option>
		                  <option value="Iceland">Iceland</option>
		                  <option value="India">India</option>
		                  <option value="Indonesia">Indonesia</option>
		                  <option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
		                  <option value="Iraq">Iraq</option>
		                  <option value="Ireland">Ireland</option>
		                  <option value="Isle of Man">Isle of Man</option>
		                  <option value="Israel">Israel</option>
		                  <option value="Italy">Italy</option>
		                  <option value="Jamaica">Jamaica</option>
		                  <option value="Japan">Japan</option>
		                  <option value="Jersey">Jersey</option>
		                  <option value="Jordan">Jordan</option>
		                  <option value="Kazakhstan">Kazakhstan</option>
		                  <option value="Kenya">Kenya</option>
		                  <option value="Kiribati">Kiribati</option>
		                  <option value="Korea, Democratic People's Republic of">Korea, Democratic People's Republic of</option>
		                  <option value="Korea, Republic of">Korea, Republic of</option>
		                  <option value="Kuwait">Kuwait</option>
		                  <option value="Kyrgyzstan">Kyrgyzstan</option>
		                  <option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
		                  <option value="Latvia">Latvia</option>
		                  <option value="Lebanon">Lebanon</option>
		                  <option value="Lesotho">Lesotho</option>
		                  <option value="Liberia">Liberia</option>
		                  <option value="Libya">Libya</option>
		                  <option value="Liechtenstein">Liechtenstein</option>
		                  <option value="Lithuania">Lithuania</option>
		                  <option value="Luxembourg">Luxembourg</option>
		                  <option value="Macao">Macao</option>
		                  <option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option>
		                  <option value="Madagascar">Madagascar</option>
		                  <option value="Malawi">Malawi</option>
		                  <option value="Malaysia">Malaysia</option>
		                  <option value="Maldives">Maldives</option>
		                  <option value="Mali">Mali</option>
		                  <option value="Malta">Malta</option>
		                  <option value="Marshall Islands">Marshall Islands</option>
		                  <option value="Martinique">Martinique</option>
		                  <option value="Mauritania">Mauritania</option>
		                  <option value="Mauritius">Mauritius</option>
		                  <option value="Mayotte">Mayotte</option>
		                  <option value="Mexico">Mexico</option>
		                  <option value="Micronesia, Federated States of">Micronesia, Federated States of</option>
		                  <option value="Moldova, Republic of">Moldova, Republic of</option>
		                  <option value="Monaco">Monaco</option>
		                  <option value="Mongolia">Mongolia</option>
		                  <option value="Montenegro">Montenegro</option>
		                  <option value="Montserrat">Montserrat</option>
		                  <option value="Morocco">Morocco</option>
		                  <option value="Mozambique">Mozambique</option>
		                  <option value="Myanmar">Myanmar</option>
		                  <option value="Namibia">Namibia</option>
		                  <option value="Nauru">Nauru</option>
		                  <option value="Nepal">Nepal</option>
		                  <option value="Netherlands">Netherlands</option>
		                  <option value="New Caledonia">New Caledonia</option>
		                  <option value="New Zealand">New Zealand</option>
		                  <option value="Nicaragua">Nicaragua</option>
		                  <option value="Niger">Niger</option>
		                  <option value="Nigeria">Nigeria</option>
		                  <option value="Niue">Niue</option>
		                  <option value="Norfolk Island">Norfolk Island</option>
		                  <option value="Northern Mariana Islands">Northern Mariana Islands</option>
		                  <option value="Norway">Norway</option>
		                  <option value="Oman">Oman</option>
		                  <option value="Pakistan">Pakistan</option>
		                  <option value="Palau">Palau</option>
		                  <option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
		                  <option value="Panama">Panama</option>
		                  <option value="Papua New Guinea">Papua New Guinea</option>
		                  <option value="Paraguay">Paraguay</option>
		                  <option value="Peru">Peru</option>
		                  <option value="Philippines">Philippines</option>
		                  <option value="Pitcairn">Pitcairn</option>
		                  <option value="Poland">Poland</option>
		                  <option value="Portugal">Portugal</option>
		                  <option value="Puerto Rico">Puerto Rico</option>
		                  <option value="Qatar">Qatar</option>
		                  <option value="Reunion">Reunion</option>
		                  <option value="Romania">Romania</option>
		                  <option value="Russian Federation">Russian Federation</option>
		                  <option value="Rwanda">Rwanda</option>
		                  <option value="Saint Barthelemy">Saint Barthelemy</option>
		                  <option value="Saint Helena, Ascension and Tristan da Cunha">Saint Helena, Ascension and Tristan da Cunha</option>
		                  <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
		                  <option value="Saint Lucia">Saint Lucia</option>
		                  <option value="Saint Martin (French part)">Saint Martin (French part)</option>
		                  <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
		                  <option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
		                  <option value="Samoa">Samoa</option>
		                  <option value="San Marino">San Marino</option>
		                  <option value="Sao Tome and Principe">Sao Tome and Principe</option>
		                  <option value="Saudi Arabia">Saudi Arabia</option>
		                  <option value="Senegal">Senegal</option>
		                  <option value="Serbia">Serbia</option>
		                  <option value="Seychelles">Seychelles</option>
		                  <option value="Sierra Leone">Sierra Leone</option>
		                  <option value="Singapore">Singapore</option>
		                  <option value="Sint Maarten (Dutch part)">Sint Maarten (Dutch part)</option>
		                  <option value="Slovakia">Slovakia</option>
		                  <option value="Slovenia">Slovenia</option>
		                  <option value="Solomon Islands">Solomon Islands</option>
		                  <option value="Somalia">Somalia</option>
		                  <option value="South Africa">South Africa</option>
		                  <option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
		                  <option value="South Sudan">South Sudan</option>
		                  <option value="Spain">Spain</option>
		                  <option value="Sri Lanka">Sri Lanka</option>
		                  <option value="Sudan">Sudan</option>
		                  <option value="Suriname">Suriname</option>
		                  <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
		                  <option value="Swaziland">Swaziland</option>
		                  <option value="Sweden">Sweden</option>
		                  <option value="Switzerland">Switzerland</option>
		                  <option value="Syrian Arab Republic">Syrian Arab Republic</option>
		                  <option value="Taiwan, Province of China">Taiwan, Province of China</option>
		                  <option value="Tajikistan">Tajikistan</option>
		                  <option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
		                  <option value="Thailand">Thailand</option>
		                  <option value="Timor-leste">Timor-leste</option>
		                  <option value="Togo">Togo</option>
		                  <option value="Tokelau">Tokelau</option>
		                  <option value="Tonga">Tonga</option>
		                  <option value="Trinidad and Tobago">Trinidad and Tobago</option>
		                  <option value="Tunisia">Tunisia</option>
		                  <option value="Turkey">Turkey</option>
		                  <option value="Turkmenistan">Turkmenistan</option>
		                  <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
		                  <option value="Tuvalu">Tuvalu</option>
		                  <option value="Uganda">Uganda</option>
		                  <option value="Ukraine">Ukraine</option>
		                  <option value="United Arab Emirates">United Arab Emirates</option>
		                  <option value="United Kingdom">United Kingdom</option>
		                  <option value="United States">United States</option>
		                  <option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
		                  <option value="Uruguay">Uruguay</option>
		                  <option value="Uzbekistan">Uzbekistan</option>
		                  <option value="Vanuatu">Vanuatu</option>
		                  <option value="Venezuela, Bolivarian Republic of">Venezuela, Bolivarian Republic of</option>
		                  <option value="Viet Nam">Viet Nam</option>
		                  <option value="Virgin Islands, British">Virgin Islands, British</option>
		                  <option value="Virgin Islands, U.S.">Virgin Islands, U.S.</option>
		                  <option value="Wallis and Futuna">Wallis and Futuna</option>
		                  <option value="Western Sahara">Western Sahara</option>
		                  <option value="Yemen">Yemen</option>
		                  <option value="Zambia">Zambia</option>
		                  <option value="Zimbabwe">Zimbabwe</option>
		                </select>
	                  </div>
	                </div><!-- col-sm-6 -->
	                <div class="col-sm-6">
	                  <div class="form-group">
	                    <label class="control-label">ZipCode</label>
	                    <input type="text" name="zipCode" id="zipCode" class="form-control input-sm">
	                  </div>
	                </div><!-- col-sm-6 -->
                </div>
                <div class="row mb10">
	                <div class="col-sm-6">
	                  <div class="form-group">
	                    <label class="control-label">Password</label>
	                    <input type="text" name="password" id="password" class="form-control input-sm" value="{{ $fackerPassword }}">
	                  </div>
	                </div><!-- col-sm-6 -->
                </div>

				<hr />

                <div class="row">
	                <div class="col-sm-6">
	                  <div class="form-group">
	                    <label class="control-label">Transaction Number</label>
	                    <input type="text" name="transactionNumber" id="transactionNumber" class="form-control input-sm">
	                  </div>
	                </div><!-- col-sm-6 -->
                </div>

                <div class="row">
	                <div class="col-sm-6">
	                  <div class="form-group">
	                    <label class="control-label">Team Members Count (including Team Admin)</label><br>
	                    <input type="text" name="multiUsers" id="multiUsers" value="1" class="form-control input-sm"/>
	                  </div>
	                </div><!-- col-sm-6 -->

	                <div class="col-sm-6">
	                  <div class="form-group" title="Amount of team members that can be added to team for free">
	                    <label class="control-label">Free (unpaid) Team Members (Not including Team Admin)</label><br>
	                    <input type="text" name="free_users" id="free_users" value="{{ $licenseTypeDetail->free_users }}" class="form-control input-sm"/>
	                  </div>
	                </div><!-- col-sm-6 -->
                </div>
					
				@include('admin.license.licensepricesblock')
				
				<hr />

                <div class="row">
	                <div class="col-sm-12">
	                  <div class="form-group">
	                    <label class="control-label">Welcome Message Sent with Password</label>
	                    <textarea class="form-control" name="message" id="message" rows="5" placeholder="Message"></textarea>
	                  </div>
	                </div><!-- col-sm-6 -->
                </div>
                <div class="panel-footer text-center">
	              <button class="btn btn-primary">Create New User</button>
	            </div>
	           {!! Form::close() !!}
	        </div>
		</div>
	</div>
</div>
@stop

@section('modelJavascript')
<script type="text/javascript">
	// Date Picker
  	jQuery('#expireOn').datepicker({
  		dateFormat: 'yy-mm-dd'
  	});
</script>
@stop

@section('javascript')
{!! HTML::script('assets/js/jquery.datatables.min.js') !!}
{!! HTML::script('assets/js/bootbox.min.js') !!}
{!! HTML::script('assets/js/jquery.typewatch.js') !!}

@yield('licensesblock_javascript')

<script>
$('#viewUserAction').change(function(){
	var action = $(this).val();

	$('#users').dataTable().fnDestroy();

	$('#users').dataTable( {
		"bProcessing": true,
		"bServerSide": true,
		"ajax": "users/ajaxusers/"+action,
		"aaSorting": [[ 3, "desc" ]],
		"aoColumns": [
			{ 'sWidth': '20px', "bSortable": false  },
			{'sClass': 'center', "bSortable": false },
			{ 'sClass': 'center', "bSortable": false },
			{ 'sClass': 'center', "bSortable": false },
			{ 'sClass': 'center', "bSortable": false },
			{ 'sClass': 'center', "bSortable": false },
			{'sClass': 'center', "bSortable": false },
			{ 'sClass': 'center', "bSortable": false },
			{ 'sClass': 'center',"bSortable": false },
			{ 'sClass': 'center',"bSortable": false },
			{ 'sClass': 'center', "bSortable": false }
		],
		"sPaginationType": "full_numbers"
	});

	jQuery(".dataTables_wrapper select").select2({
		minimumResultsForSearch: -1
	});
});

$('#users').dataTable( {
	"bProcessing": true,
	"bServerSide": true,
	"ajax": "users/ajaxusers",
	"aaSorting": [[ 1, "asc" ]],
	"bSort" : false,
	"sPaginationType": "full_numbers"
});

$('#licenseType').change(function(){
	var licenseClass = $('#licenseType option:selected').attr('rel');
	var licenseType = $('#licenseType option:selected').val();

	if(licenseClass === 'Single') {
		$("#multiUsers").prop('disabled', true);
	}
	else if(licenseClass === 'Multi') {
		$("#multiUsers").prop('disabled', false);
	}
	else {
		$("#multiUsers").prop('disabled', true);
	}
		
	$('#licenseClass').val(licenseClass);
});

$('#createUserForm').submit(function(e) {
	e.preventDefault();
	$.ajax({
		type: 'post',
		url: $('#createUserForm').attr('action'),
		cache: false,
		data: $('#createUserForm').serialize(),
		dataType : 'json',
		beforeSend: function() {
			$('#error').html('<div class="alert alert-info">Loading...</div>');
		},
		success: function(obj) {
			if(obj.success === false) {
				$('#error').html('<div class="alert alert-danger"><p>'+obj.error+'</p></div>');
			}
			else {
				location.reload();
			}
		},
		error: function(xhr, textStatus, thrownError) {
			alert('Something went wrong. Please Try again later!');
		}
	});
	return false;
});

// Spinner
jQuery('#multiUsers').spinner({min: 1});
jQuery('#free_users').spinner({min: 0});

jQuery("#ui-datepicker-div").css("z-index", "9999");

function adminDeleteUser(userID) 
{
	bootbox.confirm("Warning, All data of this user and his team will be deleted. Please ensure you have exported it first.", function(result) {
		if(result) {
			blockUI('#users');
			$.ajax({
				type: 'post',
				url: "{{ URL::route('admin.users.deleteuser') }}",
				cache: false,
				data: {"userID": userID},
				success: function(response) {
					var obj = response;
					unblockUI('#users');

					if(obj.success == "success") {
						location.reload();
					} else {
						showError(obj.message);
					}
				},
				error: function(xhr, textStatus, thrownError) {
					unblockUI('#users');
					showError('Something went wrong. Please Try again later!');
				}
			});
		}
	});

}
</script>
@stop
