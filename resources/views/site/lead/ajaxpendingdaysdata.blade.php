<style>
    .colToggle {
        margin-top: 3px;
        cursor: pointer;
    }
    .colToggle.enable {
        color: #ffffff;
    }
    .colToggle.disable {
        color: #808080;
    }
    .prev {
        margin-right: 1.6em !important;
    }

    .leadRow {
        cursor: pointer;
    }
</style>
<table id="tableDiv" class="table table-warning mb30">
    <thead>
        <tr>
            {{--@if($campaignID != 'All')--}}
                @if(sizeof($pendingDatas) > 0)
					<th style="width: 5%">
						<div class="ckbox ckbox-default">
							<input type="checkbox" id="parentCheckBox"><label for="parentCheckBox"></label>
						</div>
					</th>
                    <th style="width: 16%" class="col_0">
                        Company Name
                        <i class="fa fa-chevron-right pull-right colToggle next" onclick="getNextColumn();"></i>
                    </th>
                    <?php $count = 1; ?>
                    @forelse($pendingDatas[0]['leadInfo'] as $key => $value)
                        @if(!in_array($key, \App\Http\Controllers\CommonController::getFieldVariations("Company Name")) &&
                        !in_array($key, \App\Http\Controllers\CommonController::getFieldVariations("First Name")) &&
                        !in_array($key, \App\Http\Controllers\CommonController::getFieldVariations("Last Name")))
                            <th class="hidden col_{{$count}}" style="width: 16%">
                                <i class="fa fa-chevron-left pull-left colToggle enable prev" onclick="getPreviousColumn();"></i>
                                {{$key}}
                                <i class="fa fa-chevron-right pull-right colToggle enable next" onclick="getNextColumn();"></i>
                            </th>
                            <?php $count++; ?>
                        @endif
                    @empty
                    @endforelse
                @else
                    <th>Company Name</th>
                @endif
            {{--@else--}}
                {{--<th>Company Name</th>--}}
            {{--@endif--}}

            <th>First Name</th>
            <th>Last Name</th>
            <th>Campaign Name</th>
            <th>Call Back On</th>
            <th>Agent</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    @if(count($pendingDatas) == 0)
    	<tr><td colspan="7" class="text-center"><i>No callbacks found for this campaign</i></td> </tr>
    @else
    	@foreach($pendingDatas as $pendingData)
            <tr class="cancelCallBack_{{ $pendingData['callBackID'] }} deleteLead_{{ $pendingData['leadID'] }}">
					<td>
						<div class="ckbox ckbox-default">
							<input class="checkBox" type="checkbox" id="{{"check_".$pendingData['callBackID']}}"><label for="{{"check_".$pendingData['callBackID']}}"></label>
						</div>
					</td>
                {{--@if($campaignID != 'All')--}}
                    <td style="width: 16%" class="col_0 leadRow">{{ $pendingData['companyName'] or '-' }}</td>
                    <?php $counter = 1; ?>
                    @foreach($pendingData['leadInfo'] as $key => $value)
                        @if(!in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Company Name')) &&
                        !in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('First Name')) &&
                        !in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Last Name')))
                            <td style="width: 16%" class="leadRow hidden col_{{ $counter }}">@if($value != '' || $value != null){{ $value }}@else{{ '-' }}@endif</td>
                            <?php $counter++; ?>
                        @endif
                    @endforeach
                {{--@else--}}
                    {{--<td>{{ $pendingData['companyName'] or '-' }}</td>--}}
                {{--@endif--}}
                <td>{{ $pendingData['firstName'] or '-' }}</td>
                <td>{{ $pendingData['lastName'] or '-' }}</td>
                <td>{{ $pendingData['campaignName'] }}</td>
                <td>{{ \App\Http\Controllers\CommonController::formatDateForDisplay($pendingData['callBackOn']) }}</td>
                <td>{{ $pendingData['agent'] }}</td>
                <td>
                    <div class="btn-group mr5">
                        <button type="button" class="btn btn-primary btn-xs">Action</button>
                        <button type="button" class="btn btn-primary dropdown-toggle btn-xs" data-toggle="dropdown">
                          <span class="caret"></span>
                          <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                        {{--@if($user->userType != "Multi")--}}
                          <li><a onclick="openPendingCallBackLead({{ $pendingData['leadID'] }})" href="javascript:void(0)">Open Lead</a></li>
                        {{--@else--}}
                          {{--<li><a href="{{ URL::route("user.leads.viewlead", array("leadID" => $pendingData["leadID"] )) }}">View Lead</a></li>--}}
                        {{--@endif--}}
                          <li><a href="javascript:void(0);" onclick="cancelCallBak({{ $pendingData['callBackID'] }})">Cancel Call back</a></li>
                          <li class="divider"></li>
                          <li><a href="javascript:void(0);" onclick="deleteLead({{ $pendingData['leadID'] }})">Delete Lead</a></li>
                        </ul>
                    </div><!-- btn-group -->
                </td>
            </tr>
        @endforeach
     @endif
    </tbody>
</table>

<div class="text-center">{!! str_replace('/?', '?', $pendingLeads->render()) !!}</div>

<script>
var totalColumn = $('#tableDiv').find('th').length;

var extraColumn = totalColumn - 9;
var currentColumn = 0;

$(function(){
    if(extraColumn > 0) {
        $('.col_0').find('.next').addClass('enable');
    }

    if(extraColumn == 0) {
        $('.col_0').find('.next').addClass('hidden');
    }
});

function getPreviousColumn() {
    if(currentColumn > 0) {
        currentColumn = currentColumn - 1;

        $('th[class^=col_]').addClass('hidden');
        for(var i=0; i <= extraColumn; i++) {
            $('#newPendingData').find('.col_'+i).addClass('hidden');
        }

        $('.col_'+currentColumn).removeClass('hidden');
    }
}

function getNextColumn() {
    if(currentColumn < extraColumn) {
        currentColumn = currentColumn + 1;

        $('th[class^=col_]').addClass('hidden');

        for(var i = 0; i < extraColumn; i++) {
            $('#newPendingData').find('.col_'+i).addClass('hidden');
        }

        $('.col_'+currentColumn).removeClass('hidden');

        if(currentColumn == extraColumn) {
            $('.col_'+currentColumn).find('.next').addClass('hidden');
        }
    }
}

$('.pagination li a').click(function(e) {
    e.preventDefault();
    var campaignID = $('#campaignName').val();
    var pendingDays = $('#pendingdays').val();
    var leadType = $('#leadType').val();

    var url = $(this).attr('href');

    showPendingData(campaignID, pendingDays, leadType, url);
});

function openPendingCallBackLead(leadID) {
    $.ajax({
        type: 'post',
        url: "{{ URL::route('user.leads.openpendingcallbacklead') }}",
        cache: false,
        data: {"leadID": leadID}
    }).done(function( response ) {
        var obj = jQuery.parseJSON(response);
        if(obj.success == 'success') {
            location.href = "{{ URL::route('user.leads.createlead') }}/"+leadID;
        }
    });
}
</script>

