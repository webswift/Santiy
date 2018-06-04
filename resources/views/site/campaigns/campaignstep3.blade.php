@extends('layouts.dashboard')
@section('css')
{!! Html::style('assets/css/jquery.datatables.css') !!}
<style>
.leadBox {
min-height:320px;
width:250px;
margin: 10px;
}
.leadOptions {
padding: 10px;
height: 150px;
vertical-align: middle;
}
.unchanged {
border: 2px solid #d0d0d0;
}
.matched {
border: 2px solid #52bad5;
}
.unmatched {
border: 2px solid #EE836E;
}
.leadSamples .sampleHeading, .leadSamples .sample {
padding: 5px 15px;
margin: 0px;
}
.unchanged .sampleHeading {
background-color: #e0e0e0;
}
.unchanged .sample {
background-color: #ffffff;
}
.matched .sampleHeading {
background-color: #52BAD5;
}
.matched .sample {
background-color: #B1E0EC;
}
.unmatched .sampleHeading {
background-color: #EE836E;
}
.unmatched .sample {
background-color: #FBE3E4;
}
.leadBox .unmatchedWarning {
color: #EE836E;
}
.leadBox .notimported {
padding: 5px 10px;
margin: 5px 0px;
}
.sampleHeading, .sample {
overflow-x: hidden;
max-height: 31px;
overflow-y: hidden;
}
</style>
@stop

@section('title')
	Import Data
@stop

@section('content')
<div class="pageheader">
	<h2><i class="glyphicon glyphicon-edit"></i> Import Data</h2>
</div>

<div class="contentpanel">
	<div class="panel" id="beforeSubmitting">
		<div class="panel-heading">
			@if($is_new_campaign)
			<h4 class="panel-title">Step 3 - Sort Your Import</h4>
			<p>Please sort the data you have uploaded by matching the columns in the CSV to the fields in the leads associated with the campaign.</p>
			@else
			<h4 class="panel-title">Sort Your Import</h4>
			<p>Please sort the data you have uploaded by matching the columns in the CSV to the fields in the leads associated with the campaign.
			Please tick the boxes for columns that will be used to detect duplicates.</p>
			@endif
		</div>
        <div class="panel-body">
        	<div class="alert alert-success" id="getUnMatchedSuccess" style="display: none;">
            	<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <strong>Well done!</strong> You have successfully matched all the columns. Please click on submit to save.</a>.
			</div>
			@if(!$is_new_campaign)
            <div class="form-horizontal">
            <div class="row form-group col-sm-12">
				<label for="selDuplicates" class="control-label col-sm-4">Where duplicate rows are found: </label>
				<div class="col-sm-5">
				<select id="selDuplicates" class="form-control">
					<option value="">Skip columns that are the same from this import</option>
					<option value="skip">Skip records from this import that already exist based on selected fields</option>
					<option value="update">Update columns from this import that already exist based on selected fields</option>
				</select>
				</div>
			</div>
			</div>
			@endif
            <div class="col-sm-12">
            	<div class="col-sm-4 pull-left">
                	<b><span id="unmatchedCount">{{ $unmatchCount }}</span> unmatched columns</b> · <a href="javascript:void(0);" onclick="skipall()">Skip All</a>
                </div>
				<div class="col-sm-4 pull-right">
					<div class="form-group">
						<div class="pull-right">
							<div class="ckbox ckbox-default">
								<input type="checkbox" id="showSkipped" checked="checked">
								<label for="showSkipped">Show Skipped Columns</label>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-sm-12" style="overflow-x: scroll;">
					<table>
						<tbody>
							<tr>
							@if(sizeof($csvHeadingFields) > 0)
                            	@foreach($csvHeadingFields as $key1 => $value1)
                            	<td>
                            	<div class="row leadBox {{ ($matchedColumnsDetail[$key1] == -1) ? "unmatched" : "matched"  }}" id="box_{{ $key1 }}">
									<div class="leadOptions">
										<div class="row selectColumnNameBox" id="selectColumnNameBox_{{ $key1 }}" style="display:none;">
											<div class="col-sm-12">
												<div class="form-group">
													<label class="control-label">Column Name</label>
													<div id="selectOptionList_{{ $key1 }}">
														@if($matchedColumns[$key1] == TRUE)
														<select id="columnName_{{ $key1 }}" class="form-control input-sm mb15">
															<option value="{{ $matchedColumnsDetail[$key1] }}">{{ $formColumnDetailsByID[$matchedColumnsDetail[$key1]] }}</option>
														</select>
														@else
														<select id="columnName_{{ $key1 }}" class="form-control input-sm mb15">
															<option value="-1">Select a Column...</option>
														</select>
														@endif
													</div>
												</div>
											</div><!-- col-sm-12 -->

											<div class="col-sm-12">
												<p>
													<button onclick="goBack({{ $key1 }})" class="btn btn-info btn-sm" type="button">Back</button>
													<button onclick="saveColumnBox({{ $key1 }})" class="btn btn-white btn-sm" type="button">Save</button>
													<a href="javascript:void(0);" onclick="skipColumnBox({{ $key1 }})">Skip</a>
												</p>
											</div><!-- col-sm-12 -->
										</div>

										<div class="row columnDescriptionBox" id="columnDescriptionBox_{{ $key1 }}">
											@if(!$is_new_campaign)
											<div class="col-sm-12 divUniqueKeys">
												<div class="ckbox ckbox-default">
													<input type="checkbox" id="is_part_of_unique_{{ $key1 }}" class="is_part_of_unique" data-unique-col="{{ $key1 }}">
													<label for="is_part_of_unique_{{ $key1 }}"> </label>
												</div>
											</div>
											@endif
											<div class="col-sm-12">
												<h4 id="columnDescriptionBoxColumnName_{{ $key1 }}">{{ $value1 }}</h4>
												<p id="columnDescriptionBoxText_{{ $key1 }}">
													@if($matchedColumns[$key1] == TRUE)
														{{ $formColumnDetailsByID[$matchedColumnsDetail[$key1]] }}
													@else
														<span class="unmatchedWarning" id="unmatchedWarning_{{$key1}}">(unmatched column)</span>
													@endif
												</p>
												<p class="alert alert-warning notimported" id="columnSkipBox_{{ $key1 }}" style="display:none;">will not be Imported</p>
											</div><!-- col-sm-12 -->
										</div>

										<div class="row editAndSkipBox" id="editAndSkipBox_{{ $key1 }}">
											<div class="col-sm-12">
												<a href="javascript:void(0);" onclick="showColumnBox({{ $key1 }})">Edit</a>&nbsp;
												<a href="javascript:void(0);" onclick="skipColumnBox({{ $key1 }})" id="skipButton_{{ $key1 }}">Skip</a>
											</div><!-- col-sm-12 -->
										</div>
									</div>

									<div class="leadSamples">
										<p class="sampleHeading">{{ $value1 }}</p>
										@if(sizeof($csvFields[$key1]) > 0)
										@foreach($csvFields[$key1] as $key2 => $value2)
											<p class="sample">{{ $value2 }}</p>
										@endforeach
										@endif
									</div>
								</div>
							</td>
                            @endforeach
                            @endif
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div><!-- panel-body -->
	<div class="panel-footer">
		<div class="row">
			<div class="col-sm-6 col-sm-offset-3">
				<button class="btn btn-primary" type="button" disabled onclick="submit()" id="submit">Submit</button>&nbsp;
				<button type="button" class="btn btn-default" onclick="cancelImportData()">Cancel</button>
			</div>
		</div>
	</div>
</div><!-- panel -->

<div class="panel" id="afterSubmitting" style="display:none">
	<div class="panel-heading">
		<h3 class="panel-title">Import Data - In Progress</h3>
	</div><!-- panel-heading-->
	<div class="panel-body">
		<div id="progressError" style="display: none"></div>
		<p>Importing... <strong id="progressAmount">Please wait...</strong></p>
		<div class="progress progress-striped active">
			<div id="processingBarStatus" class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100" >
				<span class="sr-only">40% Complete (success)</span>
			</div>
		</div>
	</div>
</div>
</div><!-- contentpanel -->
@stop

@section('modelJavascript')
<script type="text/javascript">

    // Current column being edited
    var columnID = 0;

//    var matchedColumnsDetailArray = [];

    // Fields associated with this lead
    var jsColumnArray = $.parseJSON('{!! addslashes(json_encode($leadFields)) !!}');
    var currentLeadColumnID = jsColumnArray[0].id; // By default column 0 is selected

    // Array to store matched columns. ith element tells that Column i of the CSV matches with which field
    // of the lead. Initially each columns is matched serially with columns in the CSV
    var jsMatchedColumnArray = $.parseJSON('{!! addslashes(json_encode($matchedColumnsDetail)) !!}');

    // Array to indicate which of the leads columns have been matched
    var leadsMatchedColumns = $.parseJSON('{!! addslashes(json_encode($leadMatchedColumns)) !!}');

    $(document).ready(function() {
        // Show first column box for editing
        var unmatched = getUnMatched();
        $("#unmatchedCount").html(unmatched);

        if(getUnMatched() == 0) {
            $("#getUnMatchedSuccess").show();
            $("#submit").removeAttr("disabled");
        }
        else {
            showColumnBox(columnID);
        }
    });

    // Generate the select control for this column box
    function generateSelectList(columnID)
    {
//        var selectedColumnID = $('#columnName_'+columnID+' option:selected').val();

        // So that we can select column if user edits it
        var selectedColumnID = jsMatchedColumnArray[columnID];

        var text = '<select id="columnName_'+columnID+'" class="form-control input-sm mb15">' +
         '<option value="-1">Select a Column...</option>';

        for(var i=0; i < jsColumnArray.length; i++)
        {
          var id = jsColumnArray[i]['id'];
          var name = jsColumnArray[i]['name'];


          if(leadsMatchedColumns[id] != undefined && leadsMatchedColumns[id] != -1 && selectedColumnID != id ) {
            // This means this column is matched. We should not show this column
            continue;
          }

          if(selectedColumnID == id)
          {
            text += '<option value="'+id+'" selected>'+name+'</option>';
          }else{
            text += '<option value="'+id+'">'+name+'</option>';
          }
          
        }

        text += "</select>";

        return text;
    }

  </script>
@stop

@section('javascript')
    {!! HTML::script('assets/js/custom.js') !!}
    <script type="text/javascript">

    function showColumnBox(columnID)
    {
       // Hide all other edit boxes
       $(".selectColumnNameBox").hide();
       $(".editAndSkipBox").show();
       $(".columnDescriptionBox").show();

       // Show hide for this column
       $('#skipButton_'+columnID).show();
       $('#columnSkipBox_'+columnID).hide();
       $('#editAndSkipBox_'+columnID).hide();
       $('#columnDescriptionBox_'+columnID).hide();
       $('#selectColumnNameBox_'+columnID).show();

       // Hide back button for first column
       if(columnID == 0) {
          $("#selectColumnNameBox_"+columnID+" .btn-info").hide();
       }

       var selectedOption = $('#columnName_'+columnID+' option:selected');
       var selectedColumnID = selectedOption.val();
       currentLeadColumnID = selectedColumnID;
       var columnName = selectedOption.text();

       var selectListText = generateSelectList(columnID);

       $('#selectOptionList_'+columnID).html(selectListText);
       console.log(jsMatchedColumnArray);
       console.log(leadsMatchedColumns);
    }

    function hideColumnBox(columnID) {
        // Show hide for this column
       $('#skipButton_'+columnID).show();
       $('#columnSkipBox_'+columnID).hide();
       $('#editAndSkipBox_'+columnID).hide();
       $('#columnDescriptionBox_'+columnID).hide();
       $('#selectColumnNameBox_'+columnID).show();

        if(jsMatchedColumnArray[columnID] == -2) {
            $('#columnSkipBox_'+columnID).show();
        }

    }
    
    function goBack(columnID)
    {
        $('#skipButton_'+columnID).show();
        $('#columnSkipBox_'+columnID).hide();
        $('#selectColumnNameBox_'+columnID).hide();
        $('#editAndSkipBox_'+columnID).show();
        $('#columnDescriptionBox_'+columnID).show();

        if(jsMatchedColumnArray[columnID] == -2) {
            $('#columnSkipBox_'+columnID).show();
        }

        while(jsMatchedColumnArray[--columnID] == -2);

        showColumnBox(columnID);
    }    

    function saveColumnBox(columnID)
    {
      var selectedOption = $('#columnName_'+columnID+' option:selected');
      var selectedColumnID = selectedOption.val();
     
      if(selectedColumnID == "-1") {
        window.alert("Please select a column or click on skip");
      }
      else{
            var columnName = selectedOption.text();

            // Now this column is matched. So we can save it in leadsMatchedColumns array
            leadsMatchedColumns[selectedColumnID] = 1;
            jsMatchedColumnArray[columnID] = selectedColumnID;

            $('#skipButton_'+columnID).show();

            $('#columnSkipBox_'+columnID).hide();
            $('#columnDescriptionBoxText_'+columnID).html(columnName);
            $('#selectColumnNameBox_'+columnID).hide();
            $('#columnDescriptionBox_'+columnID).show();
            $('#columnDescriptionBoxText_'+columnID).show();
            $('#editAndSkipBox_'+columnID).show();
            $('#unmatchedWarning_'+columnID).hide();
			$('#is_part_of_unique_'+columnID).parent().parent().show();

            $('#box_'+columnID).removeClass('unchanged unmatched').addClass('matched');

            // Skip skipped columns
            while(jsMatchedColumnArray[++columnID] == -2);
            var unmatched = getUnMatched();
          $("#unmatchedCount").html(unmatched);

          if(unmatched == 0) {
              $("#getUnMatchedSuccess").show();
              $("#submit").removeAttr("disabled");
          }
          else {
              showColumnBox(columnID);
          }
      }
      
    }

    function skipColumnBox(columnID)
    {
      var selectedOption = $('#columnName_'+columnID+' option:selected');
      var selectedColumnID = selectedOption.val();
      var columnName = selectedOption.text();

      if (currentLeadColumnID == -1) {
          if (jsMatchedColumnArray[columnID] != -1)
              leadsMatchedColumns[jsMatchedColumnArray[columnID]] = -1;
          else{
              jsMatchedColumnArray[columnID] = -2;
          }
      } else {
          leadsMatchedColumns[currentLeadColumnID] = -1;
          jsMatchedColumnArray[columnID] = -2;
      }

      $('#selectOptionList_'+columnID).html("");

      $('#columnDescriptionBox_'+columnID).show();
      $('#selectColumnNameBox_'+columnID).hide();
      $('#columnDescriptionBoxText_'+columnID).hide();
      $('#skipButton_'+columnID).hide();

      $('#columnSkipBox_'+columnID).show();
      $('#editAndSkipBox_'+columnID).show();
      $('#unmatchedWarning_'+columnID).hide();
      $('#is_part_of_unique_'+columnID).parent().parent().hide();

     
      $('#box_'+columnID).removeClass('matched unchanged').addClass('unmatched');

      // Skip skipped columns
      while(jsMatchedColumnArray[++columnID] == -2);
      var unmatched = getUnMatched();
      $("#unmatchedCount").html(unmatched);

      if(unmatched == 0) {
          $("#getUnMatchedSuccess").show();
          $("#submit").removeAttr("disabled");
      }
      else {
          showColumnBox(columnID);
      }
      
    }

    $("#showSkipped").click(function (e) {
        if(this.checked) {
            $(".unmatched").show();
        }
        else {
            $(".unmatched").hide();
        }
    });

	$('#selDuplicates').change(function(e) {
		var value = $(this).val();
		if(value == null || value == '') {
			$('.divUniqueKeys').hide();
		} else {
			$('.divUniqueKeys').show();
		}
	});
	$('.divUniqueKeys').hide();

    function submit() {
       
        var newData = JSON.stringify(jsMatchedColumnArray);

		var selDuplicates = $('#selDuplicates').val();

		//is_part_of_unique
		var uniqueKeyCoumns = [];
		var isUniqueChecked = false;
        for(var i=0; i< jsMatchedColumnArray.length; i++) {
			if(jsMatchedColumnArray[i] > 0) {
				var isChecked = $('#is_part_of_unique_' + i).prop('checked');
				uniqueKeyCoumns.push(isChecked);
				if(isChecked) {
					isUniqueChecked = true;
				}
			} else {
				uniqueKeyCoumns.push(false);
			}
        }
		//console.log(uniqueKeyCoumns);
		//console.log(jsMatchedColumnArray);

		if(selDuplicates != null && selDuplicates != '' && !isUniqueChecked) {
			showError("Please select at least one column as unique key");
			return;
		}

        $('#beforeSubmitting').hide();
        $('#afterSubmitting').show();
        $('#processingBarStatus').width('1%');

        var pingTimer = 0;

        $.ajax({
                  type: "POST",
                  url: "{{ URL::route('user.campaigns.step4') }}",
				  data: { 
					  "sorting": newData
					 ,"duplicates" : selDuplicates
					 ,"uniqueKeys" : JSON.stringify(uniqueKeyCoumns)
				  }
          }).done(
              function( response ) {
                    clearInterval(pingTimer);
                    var obj = jQuery.parseJSON(response);

                    if(obj.success == "success")
                    {
                      $('#processingBarStatus').width('100%');
                      location.href = "{{ URL::route('user.campaigns.step5') }}";
                    }
          }).fail(function (response) {
              $("#progressError").html('<div class="alert alert-danger"><strong>Oh snap!</strong> Error occurred while importing. ' +
               'Please go back to <a href="{{ URL::route("user.campaigns.create") }}">Create Campaign</a> page and try again. If problem persists, please contact support.').show();
              clearInterval(pingTimer);
          });


        var pingTimer = setInterval(function()
        { 
          $.ajax({
                  type: "POST",
                  url: "{{ URL::route('user.campaigns.checkstep4status') }}",
                  data: {}
          }).done(
                  function( response ) {
                      $('#processingBarStatus').width(response+'%');
                      $("#progressAmount").html(Math.ceil(response) + "% Completed");
          }); 
        }, 5000);
        

    }

    function getUnMatched() {
        var matched = 0;
        for(var i=0; i< jsMatchedColumnArray.length; i++) {
            if(jsMatchedColumnArray[i] == -1) {
                matched++;
            }
        }
        return matched;
    }

    function skipall() {
        for(var i=0; i< jsMatchedColumnArray.length; i++) {
            if(jsMatchedColumnArray[i] == -1) {
                skipColumnBox(i);
            }
        }
    }

    function cancelImportData()
    {
        var answer = confirm("Do you really want to cancel?");

        if(answer)
        {
            $.ajax({
                type: "POST",
                url: "{{ URL::route('user.campaigns.cancelimportdata') }}",
                data: {}
            }).done(
                function( response ) {
                    var obj = response;

                    if(obj.status == 'success')
                    {
                        location.href = "{{ URL::route('user.campaigns.start') }}";
                    }
                    else {
                    	showError("Failed to delete campaign");
                    }
                });
        }
    }

    </script>
@stop
