@extends('layouts.dashboard')

@section('css')
	  {!! Html::style('assets/css/jquery.datatables.css') !!}
	  {!! Html::style('assets/css/bootstrap-editable.css') !!}
	  {!! Html::style('assets/css/start-campaigns.css') !!}
@stop

@section('title')
	Databases
@stop

@section('content')
<div class="pageheader">
    <h2><i class="fa fa-database"></i> Databases</h2>
</div>

<div class="contentpanel">
    <ul class="nav nav-tabs nav-tabs-custom campaigns-nav-tabs">
		<li class="border-right-1-gray">
			<a href="{{ URL::route('user.forms.createoredit') }}" title="Manage Lead Forms" class=""><i class="fa fa-wrench"></i> Manage Lead Forms</a>
		</li>
		<li class="border-right-1-gray">
			<a href="{{ URL::route('user.campaigns.create') }}" title="Create a New Campaign" class=""><i class="fa fa-paper-plane"></i> Create a New Campaign</a>
		</li>
        <li class="pull-right"><a data-toggle="tab" href="#added"><strong>Finished</strong></a></li>
        <li class="pull-right"><a data-toggle="tab" href="#archived"><strong>Parked</strong></a></li>
        <li class="active pull-right"><a data-toggle="tab" href="#all"><strong>Active Campaigns</strong></a></li>
		<li class="pull-right"><span class="nav-caption"><strong>Show: </strong></span></li>
		<li id="btnTabCampaignSearch" class="pull-right border-right-1-gray">
			<a href="javascript:void(0)"  title="Search" class=""><i class="fa fa-search"></i> Search</a>
		</li>
    </ul>
    <div class="tab-content">
        <div id="all" class="tab-pane active">

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

            <div id="activeCampaignList"></div>
        </div>

        <div id="added" class="tab-pane">
            <div id="finishCampaignList"></div>
        </div>

        <div id="archived" class="tab-pane">
            <div id="archivedCampaignList"></div>
        </div>
    </div>
</div>

@stop

@section('bootstrapModel')
		
<div id="endCampaignModel" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
        <div class="modal-header">
            <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
            <h4 class="modal-title">Are you sure?</h4>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to end this campaign early? All unactioned leads will be deleted from the campaign.</p>
        </div>
        <div class="modal-footer">
            <button onclick="endCampaignProcced()" id="endCampaignProcced" rel="" class="btn btn-primary btn-sm">Proceed</button>
            <button data-dismiss="modal" type="button" class="btn btn-default btn-sm">Cancel</button>
        </div>
    </div>
  </div>
</div>

<div id="startCampaignModel" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h4 class="modal-title">On your Marks!</h4>
            </div>
            <div class="modal-body">
                <p> <span id="campaignName"></span> will start in <span id="remainingStartTime" style="color: #ff0000; font-weight: bold"></span> seconds</p>
            </div>
            <div class="modal-footer">
                <button onclick="startCampaignProcced()" id="startCampaignProcced" rel="" class="btn btn-primary btn-sm">Start Now</button>
                <button data-dismiss="modal" type="button" class="btn btn-default btn-sm">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div id="editCampaignUserModel" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <div id="headerInfo">
                    <h4 class="modal-title">Add or remove user from this campaign</h4>
                    <p>You can add or remove team members in this campaign. Please check for adding members and uncheck for removing a member</p>
                </div>
            </div>
            <div class="modal-body">
                <div class="form-horizontal">
                    <div id="error3" ></div>
                    <div class="row">
                        <div id="campaignMembersDiv" class="col-sm-12">
                            <div class="ckbox ckbox-primary">
                                <input type="checkbox" id="int_website" value="m" name="int[]" required="">
                                <label for="int_website">Website</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="searchCampaignModel" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h4 class="modal-title">
                <p>Campaign Search</p>
                <p style="font-size: 13px;">Enter name and/or date range to list results</p></h4>
            </div>
            <form method="post" class="form-horizontal" id="campaignFilterForm">
                <div class="modal-body">
                    <div class="alert alert-danger" id="filterError">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <span></span>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Campaign Name</label>
                        <div class="col-sm-7">
                            <input type="text" id="campaignName" name="name" placeholder="Name" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Date Range</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" id="date1" name="from" placeholder="Date From">
                        </div>
                        <div class="col-sm-1 control-label" style="text-align: center">to</div>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" id="date2" name="to" placeholder="Date To">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="text-align: left">
                <button type="button" id="campaignFilterButton" rel="" class="btn btn-primary btn-sm">Submit</button>
                <input type="reset" class="btn btn-default btn-sm" value="Reset">
            </div>
            </form>
        </div>
    </div>
</div>

<div id="campaignInfoModal" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	<input type="hidden" id="campaignID4Info" name="campaignID4Info" value="">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <div id="headerInfo">
                    <h4 class="modal-title">Campaign Information</h4>
                </div>
            </div>
            <div class="modal-body">
                Loading...
            </div>
        </div>
    </div>
</div>

<div id="campaignMailSettingsModal" class="modal fade bs-example-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	<input type="hidden" id="campaignID4MailSetting" name="campaignID4MailSetting" value="">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        </div>
    </div>
</div>

@stop

@section('modelJavascript')
{!! HTML::script('assets/js/jquery.datatables.min.js') !!}
{!! HTML::script('assets/js/bootstrap-editable.min.js') !!}

<script type="text/javascript">
function endCampaign(campaignID) {
    $('#endCampaignProcced').attr('rel', campaignID);
    $('#endCampaignModel').modal('show');
}

var timer;
var timerVar;

function startCampaign(campaignID, campaignName) {
    timer = 5;
    clearInterval(timerVar);

    $('#campaignName').html(campaignName);
    $('#startCampaignProcced').attr('rel', campaignID);
    $('#startCampaignModel').modal('show');

    $('#remainingStartTime').html(timer);

    timerVar = setInterval(function(){
        timer--;
        if(timer <= 0) {
            clearInterval(timerVar);
            startCampaignProcced();
        }
        else {
            $('#remainingStartTime').html(timer);
        }
    }, 1000);
}

$('#startCampaignModel').on('hidden.bs.modal', function () {
    clearInterval(timerVar);
})
</script>
@stop

@section('javascript')
{!! HTML::script('assets/js/custom.js') !!}
{!! HTML::script('assets/js/bootbox.min.js') !!}
<script type="text/javascript">
var isFilter = false;
var isLetterFilter = false;
var savedLetterFilter = '';

$(function(){
    jQuery('#date1').datepicker({
        dateFormat: 'dd-mm-yy'
    });
    jQuery('#date2').datepicker({
        dateFormat: 'dd-mm-yy'
    });

    $('#activeCampaignList').on('click', '.pagination a', function (event) {
        blockUI('#activeCampaignList');
        event.preventDefault();
        if ( $(this).attr('href') != '#' ) {
            $("html, #activeCampaignList").animate({ scrollTop: 0 }, "fast");

			var url = $(this).attr('href');
            if(isFilter){
                var formData = getFormData();
                url = $(this).attr('href')+'&'+formData;
            } else if(isLetterFilter){
                url = $(this).attr('href')+'&letterFilter='+savedLetterFilter;
            }

            $.post(url, function(response){
                $('#activeCampaignList').html(response);

                if(isFilter){
                    $('#searchResults').removeClass('hidden');
                }
				refreshLetterList();
                unblockUI('#activeCampaignList');
            });
        }
    });

    $('#finishCampaignList').on('click', '.pagination a', function (event) {
        blockUI('#finishCampaignList');
        event.preventDefault();
        if ( $(this).attr('href') != '#' ) {
            $("html, #finishCampaignList").animate({ scrollTop: 0 }, "fast");
            $.post($(this).attr('href'), function(response){
                $('#finishCampaignList').html(response);
                unblockUI('#finishCampaignList');
            });
        }
    });

    $('#archivedCampaignList').on('click', '.pagination a', function (event) {
        blockUI('#archivedCampaignList');
        event.preventDefault();
        if ( $(this).attr('href') != '#' ) {
            $("html, #archivedCampaignList").animate({ scrollTop: 0 }, "fast");
            $.post($(this).attr('href'), function(response){
                $('#archivedCampaignList').html(response);
                unblockUI('#archivedCampaignList');
            });
        }
    });

    $('#archivedCampaignList').on('click', '.removeArchive', function(){
        var id = $(this).attr('rel');
        $('#archivedCampaign_'+id).fadeOut(500);

        $.post('{{URL::route('user.campaigns.removeArchive')}}', {'id' : id}, function(response){
            getActiveCampaign(false);
        });
    });

    $('#activeCampaignList').on('click', '#campaignSearch', function(){
        $('#filterError').addClass('hidden');
        $('#searchCampaignModel').modal('show');
    });

    $('#campaignFilterButton').click(function(){
        var campaignName = $('#campaignName').val();
        var date1 = $('#date1').val();
        var date2 = $('#date2').val();

        if((campaignName == '' || campaignName == undefined) && (date1 == '' || date1 == undefined) && (date2 == '' || date2 == undefined)){
            $('#filterError').find('span').html('Please fill one of the filters');
            $('#filterError').removeClass('hidden');
            return false;
        }

        var formData = getFormData();
        getActiveCampaign(true, formData);
        $('#searchCampaignModel').modal('hide');

        return false;
    });

    getActiveCampaign(true);
    getFinishCampaign(false);
    getArchivedCampaign(false);

	//--tabs
	$('.campaigns-nav-tabs a[data-toggle="tab"][href="#all"]').on('shown.bs.tab', function (e) {
		$('#btnTabCampaignSearch').show();
	});

	$('.campaigns-nav-tabs a[data-toggle="tab"][href!="#all"]').on('shown.bs.tab', function (e) {
		$('#btnTabCampaignSearch').hide();
	});
	//--tabs

	//--letters list
	$('#btnTabCampaignSearch').click(function(e) {
		var letterList = $('#ulLetterList');
		if(letterList.hasClass('hidden')) {
			letterList.removeClass('hidden');
			$('#ulLetterList a:contains("All")').parent().addClass('active');
		} else {
			letterList.addClass('hidden');
			getActiveCampaign(true);
		}
	});

	$('#activeCampaignList').on('click', '#ulLetterList a', function(e){
		e.preventDefault();
        getActiveCampaign(true, null, e.target.text);
	});
	//--letters list

});

function refreshLetterList() {
	if(isLetterFilter) {
		$('#ulLetterList').removeClass('hidden');
		$('#ulLetterList li').removeClass('active');
		$('#ulLetterList a:contains("' + savedLetterFilter + '")').parent().addClass('active');
	} else {
		$('#ulLetterList').addClass('hidden');
	}
}

function getFormData(){
    var formData = $('#campaignFilterForm').serialize();
    return formData;
}

function getActiveCampaign(isLoader, formData, letterFilter){
    if(isLoader){
        blockUI('#activeCampaignList');
    }

	isFilter = false;
	isLetterFilter = false;
	var url = '{{URL::route('user.campaigns.getActiveCampaign')}}';
    if(formData != null && formData != undefined && formData != ''){
        url = '{{URL::route('user.campaigns.getActiveCampaign')}}?'+formData;
        isFilter = true;
    } else if(letterFilter != null && letterFilter != undefined && letterFilter != '' && letterFilter != 'All'){
        url = '{{URL::route('user.campaigns.getActiveCampaign')}}?letterFilter='+ letterFilter;
		isLetterFilter = true;
		savedLetterFilter = letterFilter;
    }

    $.post(url, function(response){
        $('#activeCampaignList').html(response);
        if(isFilter){
            $('#searchResults').removeClass('hidden');
        }
		refreshLetterList();
        if(isLoader){
            unblockUI('#activeCampaignList');
        }
    });
}

function getFinishCampaign(isLoader){
    if(isLoader){
        blockUI('#finishCampaignList');
    }

    $.post('{{URL::route('user.campaigns.getFinishedCampaign')}}', function(response){
        $('#finishCampaignList').html(response);

        if(isLoader){
            unblockUI('#finishCampaignList');
        }
    });
}

function getArchivedCampaign(isLoader){
    if(isLoader){
        blockUI('#archivedCampaignList');
    }

    $.post('{{URL::route('user.campaigns.getArchivedCampaign')}}', function(response){
        $('#archivedCampaignList').html(response);

        if(isLoader){
            unblockUI('#archivedCampaignList');
        }
    });
}
	
function endCampaignProcced() {
    var endCampaignID = $('#endCampaignProcced').attr('rel');

     $.ajax({
              type: "POST",
              url: "endcampaign",
              data: { "campaignID": endCampaignID}
      }).done(function( response ) {
         var obj = response

         if(obj.status == "success") {
             location.reload();
         }
      });
  }

function deleteCampaignData(campaignID) {
	hideErrors();
	bootbox.confirm("Warning, All data from the campaign will be deleted. Please ensure you have exported it first.", function(result) {
		if(result) {
			$.ajax({
				type: "POST",
				url: '{{URL::route('user.campaigns.deleteCampaignData')}}',
				data: { "campaignID": campaignID}
			}).done(function( response ) {
				var obj = response;

				if(obj.status == "success") {
					location.reload();
				} else {
					showError("Error: " + obj.message);
				}
			});
		}
	});
}

function startCampaignProcced() {
    var startCampaignID = $('#startCampaignProcced').attr('rel');

    $.ajax({
          type: "POST",
          url: "startcampaign",
          data: { "campaignID": startCampaignID}
    }).done( function( response ) {
        var obj = response;
        if(obj.status == "success") {
            location.href = "{{ URL::route('user.leads.createlead') }}"+"/"+obj.leadID;
        }
        else {
            showError("Error: " + obj.message);
        }
  });
}

function generateLeadForCampaign(campaignID) {
    $.ajax({
      type: 'post',
      url: "{{ URL::route('user.leads.selectedcampaignlead') }}",
      cache: false,
      data: {"campaignID": campaignID}
    }).done( function( response ) {
      var obj =  response;
      if(obj.status == 'success') {
          location.href = "{{ URL::route('user.leads.createlead') }}/"+obj.leadID;
      }
    });
}

function archiveCampaign(campaignID) {
	$('#campaign_'+campaignID).fadeOut(500);
	$.post('{{URL::route('user.campaigns.addToArchive')}}', {'id' : campaignID}, function(response){
		getArchivedCampaign(false);
	});
}


function editCampaignUser(campaignID) {
    $('#campaignMembersDiv').html('');
    $.ajax({
          type: 'post',
          url: "{{ URL::route('user.campaigns.ajaxcampaignmembers') }}",
          cache: false,
          data: {"campaignID": campaignID}
      }).done(function( response ) {
        if(response.status == 'success') {
            $('#campaignMembersDiv').html(response.text);
            $('#editCampaignUserModel').modal('show');
        }
        else {
            showError("Could not get campaign members. Please try again after sometime.");
        }
    });
}

function addOrRemoveCampaignUser(userID, campaignID) {
    var action;
    if($("#campaignUserMember_"+userID).is(":checked")) {
        action = "Add";
    }
    else {
        action = "Remove";
    }

    $.ajax({
        type: 'post',
        url: "{{ URL::route('user.campaigns.addorremoveuserfromcampaign') }}",
        cache: false,
        data: {"campaignID": campaignID, "userID": userID, "action": action}
    }).done( function( response ) {
        var obj = response;
        if(obj.status == 'success') {
            showSuccess(obj.message, "error3");
        }
        else if(obj.status == 'fail') {
            showError(obj.message, "error3");
        }
        else {
            showError("This member is not in your team", "error3");
        }
    });
}

$(function(){
	$('#activeCampaignList')
		.on( "mouseenter", '.campaignNameCell', 
			function() {
				$(this).find('.editCampaignNameBtn').not( ".name-editing-active" ).removeClass('hidden');
			} 
		)
		.on( "mouseleave", '.campaignNameCell', 
			function() {
				$(this).find('.editCampaignNameBtn').not( ".name-editing-active" ).addClass('hidden');
			}
		)
	;
})

function editCampaignName(campaignID) {
	$('#startCampaignAnhor-' + campaignID).addClass('hidden');
	$('#editCampaignNameBtn-' + campaignID).addClass('hidden').addClass('name-editing-active');
	$('#editCampaignAnhor-' + campaignID)
		.removeClass('hidden')
		.editable({
			type: 'text',
			url: '{{URL::route('user.campaigns.renameCampaign')}}',    
			pk: campaignID,    
			placement: 'top',
			title: 'Enter campaign name',
			mode: 'inline',
			toggle: 'manual',
			error: function(response, newValue) {
				showError('Something went wrong. Please try again later!');
				return 'Something went wrong. Please try again later!';
			},
			success: function(response, newValue) {
				if(response.status != 'success') {
					showError(response.message);
					return response.message;
				} else {
					$('#startCampaignAnhor-' + campaignID)
						.html(newValue)
						.attr('href', 'start/' + newValue)
						;
				}
			}
		})
		.on('hidden', function(e, reason) {
			$('#startCampaignAnhor-' + campaignID).removeClass('hidden');
			$('#editCampaignNameBtn-' + campaignID).removeClass('hidden').removeClass('name-editing-active');
			$('#editCampaignAnhor-' + campaignID)
				.addClass('hidden')
				.editable('destroy')
				;
		});

	setTimeout(function() {
		$('#editCampaignAnhor-' + campaignID).editable('toggle');
	} , 0);
}

var assignLandingFormUrl = '{{URL::route('user.campaigns.assignLandingForm')}}';


$(function(){

	var reloadInfo4campaignId = null;

    $('#campaignInfoModal').on('hidden.bs.modal', function () {
		$(this).find('#landingFormID').off('change');
		$(this).find('#createMapping').off('click');
		$(this).off('click', '#embedBtn');
		$(this).off('click', '#deleteMapping');
		$(this).off('click', '#assignLandingForm2CampaignBtn');
        $(this).find('.modal-body').html('Loading...');
        $(this).find('.modal-title').html('Campaign Information');
        $(this).data('bs.modal', null);
		$(this).data('loaded', false);
		$('#campaignID4Info').val('');

		if(reloadInfo4campaignId != null) {
			var copy = reloadInfo4campaignId;
			reloadInfo4campaignId = null;
			viewCampaignInformation(copy);
		}
    });
	
	$('#campaignInfoModal').on('loaded.bs.modal', function () {
		var dlg = $(this);
		if(dlg.data('loaded')) {
			return;
		}
		dlg.data('loaded', true);
		
		dlg.on('click', '#embedBtn', function(){
			var embedDiv = $('#embedCodeDiv');
			if(embedDiv.hasClass('hidden')){
				embedDiv.removeClass('hidden');
			}
			else{
				embedDiv.addClass('hidden');
			}
		});


		var checkFieldsMapping  = function() {
			var landingFormID = dlg.find('#landingFormID').val();
			var leadFormID = dlg.find('#leadFormID').val();

			if(landingFormID != '' && leadFormID != ''){
				dlg.find('#createMapping, #assignLandingForm2CampaignBtn')
					.prop('disabled', true)
					.addClass('hidden');
				
				Landing2LeadFieldsMapping.edit(landingFormID, leadFormID, function() {
					dlg.find('#createMapping, #assignLandingForm2CampaignBtn')
						.prop('disabled', false)
						.removeClass('hidden');
				});
			} else {
				dlg.find('#createMapping, #assignLandingForm2CampaignBtn')
					.prop('disabled', true)
					.addClass('hidden');
			}
		}

		dlg.find('#createMapping').on('click', checkFieldsMapping);
		dlg.find('#landingFormID').on('change', checkFieldsMapping);
		
		dlg.on('click', '#assignLandingForm2CampaignBtn', function(){
			var campaignID = dlg.find('#campaignID4Info').val();

			if(campaignID == undefined || campaignID == ''){
				showError('Please select campaign');
				return false;
			}

			var landingFormID = dlg.find('#landingFormID').val();
			if(landingFormID == undefined || landingFormID == ''){
				showError('Please select landing form');
				return false;
			}

			var formData = {
				'campaignID' : campaignID,
				'landingFormID' : landingFormID
			};

			blockUI(dlg);

			$.ajax({
				type: 'post',
				url: assignLandingFormUrl,
				cache: false,
				data: formData,
				dataType: 'json',
				success: function(response) {
					unblockUI(dlg);
					dlg.modal('hide');
					showSuccess('Landing form is assigned to campaign successfully');
					//reopen dialog
					reloadInfo4campaignId = campaignID;
				},
				error: function(xhr, textStatus, thrownError) {
					unblockUI(dlg);
					showError('Something went wrong. Please try again later!');
				}
			});
		});
		
		dlg.on('click', '#deleteMapping', function(){
			var campaignID = dlg.find('#campaignID4Info').val();

			if(campaignID == undefined || campaignID == ''){
				showError('Please select campaign');
				return false;
			}

			var formData = {
				'campaignID' : campaignID,
			};

			blockUI(dlg);

			$.ajax({
				type: 'post',
				url: assignLandingFormUrl,
				cache: false,
				data: formData,
				dataType: 'json',
				success: function(response) {
					unblockUI(dlg);
					dlg.modal('hide');
					showSuccess('Landing form is deleted from campaign successfully');
					//reopen dialog
					reloadInfo4campaignId = campaignID;
				},
				error: function(xhr, textStatus, thrownError) {
					unblockUI(dlg);
					showError('Something went wrong. Please try again later!');
				}
			});
		});
    });

});

function viewCampaignInformation(campaignID){
	$('#campaignID4Info').val(campaignID);

    var url = '{{URL::route('user.campaigns.getInformation')}}'+'?campaignID='+campaignID;

    $("#campaignInfoModal").removeData('bs.modal').modal({
        remote: url,
        show: true
    });

    return false;
}

$(function(){
    $('#campaignMailSettingsModal').on('hidden.bs.modal', function () {
		$(this).off('click', '#saveCampaignMailSettingsBtn');
        $(this).find('.modal-body').html('Loading...');
        $(this).find('.modal-title').html('Campaign Mail Settings');
        $(this).data('bs.modal', null);
		$(this).data('loaded', false);
		$('#campaignID4MailSetting').val('');
		campaignEmailSettings.reset();
    });
	
	$('#campaignMailSettingsModal').on('loaded.bs.modal', function () {
		var dlg = $(this);
		if(dlg.data('loaded')) {
			return;
		}
		dlg.data('loaded', true);
		
		dlg.on('click', '#saveCampaignMailSettingsBtn', function(){
			var campaignID = dlg.find("#campaignID4MailSetting").val();

			if(campaignID == undefined || campaignID == ''){
				showError('Please select campaign');
				return false;
			}
			
			hideErrors();

			if(!campaignEmailSettings.validate()) {
				return false;
			}

			var formData = {
				'campaignID' : campaignID
			};

			formData = campaignEmailSettings.fillFormData(formData);	

			blockUI(dlg);

			$.ajax({
				type: 'post',
				url: '{{URL::route('user.campaigns.setMailSettings')}}',
				cache: false,
				data: formData,
				dataType: 'json',
				success: function(response) {
					unblockUI(dlg);
					if(response.status == 'error'){
						showError(response.message);
						showFieldError(response.message, 'smtpErrorLabel');
					} else {
						dlg.modal('hide');
						showSuccess('Mail settings is updated successfully');
					}
				},
				error: function(xhr, textStatus, thrownError) {
					unblockUI(dlg);
					showError("Request failed: " + textStatus);
				}
			});
		});
	});
});

function viewCampaignMailSettings(campaignID){
	$('#campaignID4MailSetting').val(campaignID);

    var url = '{{URL::route('user.campaigns.getMailSettingsDialog')}}'+'?campaignID='+campaignID;

	$("#campaignMailSettingsModal .modal-content").html('');
    $("#campaignMailSettingsModal").removeData('bs.modal').modal({
        remote: url,
        show: true
    });

    return false;
}

function verifyEmailSettings(){
	hideErrors();

	blockUI('#campaignMailSettingsModal');
	campaignEmailSettings.veryfyEmailSettings(
		'{{URL::route('user.campaigns.verifyEmailSetting')}}', 
		function() {
			unblockUI('#campaignMailSettingsModal');
		}
	);
}

</script>

{{-- tools for dialog to map landing form fields to lead form fields --}}
<script>
    var mappingInfoUrl = '{{URL::route('user.form.landing.getMapping')}}';
    var setMappingUrl = '{{ URL::route('user.form.landing.setMapping') }}';
</script>
{!! HTML::script('assets/js/dialogs/mapleadlandingfields.js') !!}
{!! HTML::script('assets/js/dialogs/campaignmailsettings.js') !!}

{{-- Change lead distribution options ("Next" Lead will be selected based on filter).  --}}
@include('site/campaigns/dlgPrevNextFilter')

@stop

