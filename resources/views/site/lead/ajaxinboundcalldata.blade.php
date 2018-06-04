<div class="table-responsive" id="newInBoundData">
	<table id="table1" class="table table-success">
		<thead>
			<tr>
				{{--@if($campaignID != 'All')--}}
					<th style="width: 20%" class="col_0">
						Company Name
						@if(sizeof($inBoundCallDatas) > 0)
							<i class="fa fa-chevron-right pull-right colToggle next" onclick="getNextColumn();"></i>
						@endif
					</th>
					@if(sizeof($inBoundCallDatas) > 0)
						<?php $count = 1; ?>
						@foreach($inBoundCallDatas[0]['leadInfo'] as $key => $value)
							@if(!in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Company Name')) &&
							!in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('First Name')) &&
							!in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Last Name')) &&
							!in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Post/Zip code')) &&
							!in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Zip Code')))
								<th class="hidden col_{{$count}}" style="width: 20%">
									<i class="fa fa-chevron-left pull-left colToggle enable prev" onclick="getPreviousColumn();"></i>
									{{$key}}
									<i class="fa fa-chevron-right pull-right colToggle enable next" onclick="getNextColumn();"></i>
								</th>
								<?php $count++; ?>
							@endif
						@endforeach
					@endif
				{{--@else--}}
					{{--<th style="width: 16%">Company Name</th>--}}
				{{--@endif--}}
				<th style="width: 17%">
					<div class="sorting-div inboundSort" data-name="contactPerson" data-type="asc" title="asc"> Contact Person </div>
                </th>
				<th style="width: 15%">
					<div class="sorting-div inboundSort" data-name="campaign" data-type="asc" title="asc"> Campaign </div>
                </th>
				<th style="width: 15%">
					<div class="sorting-div inboundSort" data-name="reference" data-type="asc" title="asc"> Reference </div>
                </th>
				<th style="width: 17%">
					<div class="sorting-div inboundSort" data-name="zipCode" data-type="asc" title="asc"> Post/Zip Code </div>
                </th>
				<th style="width: 15%">
					<div class="sorting-div inboundSort" data-name="advisor" data-type="asc" title="asc"> Advisor </div>
                </th>
				<th>Edit</th>
			</tr>
		</thead>
		<tbody>
			@if(sizeof($inBoundCallDatas) > 0)
				@foreach($inBoundCallDatas as $inBoundCallData)
					<tr>
						<td class="col_0 leadRow" style="width: 20%">{{ $inBoundCallData['companyName'] or '-'}}</td>
						{{--@if($campaignID != 'All')--}}
							<?php $counter = 1; ?>
							@foreach($inBoundCallData['leadInfo'] as $key => $value)
								@if(!in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Company Name')) &&
									!in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('First Name')) &&
									!in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Last Name')) &&
									!in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Post/Zip code')) &&
									!in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Zip Code')))
								<td class="leadRow hidden col_{{$counter}}" style="width: 20%">@if($value != '' || $value != null){{$value}}@else{{'-'}}@endif</td>
								<?php $counter++; ?>
								@endif
							@endforeach
						{{--@endif--}}
						<td style="width: 17%">@if(trim($inBoundCallData['contactPerson']) != ''){{$inBoundCallData['contactPerson']}}@else{{'-'}}@endif</td>
						<td>{{ $inBoundCallData->campaignName }}</td>
						<td>{{ $inBoundCallData->referenceNumber or '-' }}</td>
						<td>{{ $inBoundCallData['zipCode'] or '-' }}</td>
						<td>@if(trim($inBoundCallData['advisor']) != ''){{$inBoundCallData['advisor']}}@else{{'-'}}@endif</td>
						<td><a href="{{ URL::route('user.leads.createlead', array('leadID' => $inBoundCallData->leadID, 'edit' => 'edit')) }}" ><i class="fa fa-edit"></i></a></td>
					</tr>
				@endforeach
			@else
			<tr><td colspan="7" class="text-center">No data available</td></tr>
			@endif
		</tbody>
	</table>
</div><!-- table-responsive -->

<div class="row pull-right">
	<div class="col-sm-12">
	 @if(is_object($inBoundCallDatas))
		{!! str_replace('/?', '?', $inBoundCallDatas->appends(Input::except('page'))->render()) !!}
	 @endif
	</div>
</div>

<script>
var totalColumn = $('#table1').find('th').length;

var extraColumn = totalColumn - 7;
var currentColumn = 0;

$(function(){
	@if(isset($column))
	var type = '{!! $type !!}';
	if(type == 'asc') {
		$('.inboundSort[data-name="{!! $column !!}"]').addClass('highlight');
		$('.inboundSort[data-name="{!! $column !!}"]').attr('data-type', 'desc');
		$('.inboundSort[data-name="{!! $column !!}"]').attr('title', 'desc');
	}
	else if(type == 'desc') {
		$('.inboundSort[data-name="{!! $column !!}"]').addClass('highlight');
		$('.inboundSort[data-name="{!! $column !!}"]').attr('data-type', 'asc');
		$('.inboundSort[data-name="{!! $column !!}"]').attr('title', 'asc');
	}
	@endif
    if(extraColumn > 0){
        $('#table1').find('.col_0').find('.next').addClass('enable');
    }

    if(extraColumn == 0){
        $('#table1').find('.col_0').find('.next').addClass('hidden');
    }

    $('.inboundSort').click(function() {
        var column = $(this).attr('data-name');
        var type = $(this).attr('data-type');

        $('.inboundSort').removeClass('highlight');
        $(this).addClass('highlight');

        var sortingFromData = '{!! $sortingFromData !!}';
        sortingFromData = sortingFromData + '&column='+column;
        sortingFromData = sortingFromData + '&type='+type;

        showInboundCallData(sortingFromData);
    });
});

function getPreviousColumn(){
	if(currentColumn > 0){

		currentColumn = currentColumn - 1;

        $('#table1').find('th[class^=col_]').addClass('hidden');
		for(var i=0; i <= extraColumn; i++){
			$('#table1').find('.col_'+i).addClass('hidden');
		}

        $('#table1').find('.col_'+currentColumn).removeClass('hidden');
	}
}

function getNextColumn(){
	if(currentColumn < extraColumn){
		currentColumn = currentColumn + 1;

        $('#table1').find('th[class^=col_]').addClass('hidden');

		for(var i=0; i < extraColumn; i++){
			$('#table1').find('.col_'+i).addClass('hidden');
		}

        $('#table1').find('.col_'+currentColumn).removeClass('hidden');

		if(currentColumn == extraColumn){
            $('#table1').find('.col_'+currentColumn).find('.next').addClass('hidden');
		}
	}
}

</script>
