<style>
.colToggle{
margin-top: 3px;
cursor: pointer;
}
.colToggle.enable{
	color: #ffffff;
}
.colToggle.disable{
	color: #808080;
}
.prev{
  margin-right: 1.6em !important;
}

.leadRow{
	cursor: pointer;
}
.sorting-div {
    cursor: pointer;
}

.highlight {
	color: #bcbcbc;
}

#lead_table {
	width:auto;
	max-width:auto;
}

#table_wrapper {
	overflow-x:auto;
}
						
#lead_table th {
	white-space:nowrap;
}
#lead_table td {
	white-space:nowrap;
}

</style>
<div id="table_wrapper" class="table-responsive col-sm-12">
	<table class="table table-primary mb30" id="lead_table">
		<thead>
			<tr>
				<th style="">
					<div class="ckbox ckbox-default">
						<input type="checkbox" id="parentCheckBox"><label for="parentCheckBox"></label>
					</div>
				</th>

				@if(sizeof($fieldName) > 0)
				<?php $count = 0; ?>
				@foreach($fieldName as $value)
					@if(!in_array($value, \App\Http\Controllers\CommonController::getFieldVariations('Notes')))
					<th class="col_{{$count}}" style="">
						{{$value}}
					</th>
					<?php $count++; ?>
					@endif
				@endforeach
				@endif

				<th style="">
                    <div class="sorting-div sort" data-name="lastContacted" data-type="asc"> Last Updated </div>
				</th>
				@if($leadType != 'unactioned')
					<th>
						<div class="sorting-div sort" data-name="contactedBy" data-type="asc"> Contacted By </div>
					</th>
				@endif
				<th style="">
                    <div class="sorting-div sort" data-name="notes" data-type="asc"> Notes (if any) </div>
				</th>
				@if($campaignID == 'All')
					<th>
                        <div class="sorting-div sort" data-name="campaign" data-type="asc"> Campaign </div>
					</th>
				@endif
				@if($leadType != 'unactioned')
					<th>
						<div class="sorting-div sort" data-name="contacted" data-type="asc"> Contacted </div>
					</th>
				@endif
				<th><i class="fa fa-paperclip"></i></th>
			</tr>
		</thead>
		<tbody>
		@if(sizeof($leadData) > 0)
			@foreach($leadData as $data)
			<tr id="lead_{{$data->id}}" data-leadtype=@if($leadType != 'unactioned')"actioned"@else"unactioned"@endif>
					<td>
						<div class="ckbox ckbox-default">
							<input class="checkBox" type="checkbox" id="{{"check_".$data->id}}"><label for="{{"check_".$data->id}}"></label>
						</div>
					</td>
					<?php $counter = 0; ?>
					@foreach($data['leadInfo'] as $key => $value)
						@if(!in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Notes')))
						<td class="leadRow col_{{$counter}}">@if($value != '' || $value != null){{$value}}@else{{'-'}}@endif</td>
						<?php $counter++; ?>
						@endif
					@endforeach

					<td class="leadRow">{{ \App\Http\Controllers\CommonController::formatDateForDisplay($data->timeEdited) }}</td>
					@if($leadType != 'unactioned')
						<td class="leadRow">{{ $data->lastContact }}</td>
					@endif
					<td>
                        @if(isset($data['Notes']))
                            @if($data['Notes'] == '')
                                <i style="cursor: pointer" title="View cal history notes" onclick="viewCallHistoryNotes({{ $data->id }})" class="fa fa-info-circle"></i>
                            @else
                                <p style="cursor: pointer" title="View cal history notes" onclick="viewCallHistoryNotes({{ $data->id }})">{!! str_limit($data['Notes'], $limit = 20, $end = '...') !!}</p>
                            @endif
                        @else
                            -
                        @endif
                    </td>
					@if($campaignID == 'All')
						<td>{{ $data->campaignName }}</td>
					@endif
					@if($leadType != 'unactioned')
						<td class="leadRow">@if($data->status == 'Actioned'){{$data->count + 1}}@else {{$data->count}} @endif times</td>
					@endif
					<td><i title="View attachments" class="fa fa-paperclip" style="cursor: pointer" onclick="viewLeadAttachments('{{$data->id}}');"></i></td>
				</tr>
			@endforeach
		@else
			@if($campaignID == 'All')
				<tr><td class="text-center" colspan="10"><i>No leads found</i></td></tr>
			@else
				<tr><td class="text-center" colspan="9"><i>No leads found</i></td></tr>
			@endif
		@endif
		</tbody>
	</table>
</div>

<div class="row pull-right">
	<div class="col-sm-12">
	{{-- $leadData might be pagination object or simple collection --}}
	 @if(method_exists($leadData, 'appends'))
		{!! str_replace('/?', '?', $leadData->appends(Input::except('page'))->render()) !!}
		<input id="hiddenLeadsTotalCount" class="hidden" type="text" value="{!! $leadData->total() !!}" />
	 @else
		<input id="hiddenLeadsTotalCount" class="hidden" type="text" value="{!! $leadData->count() !!}" />
	 @endif
	</div>
</div>

<?php Input::flush(); ?>

<div class="clearfix"></div>

<script>
var totalColumn = $('#lead_table').find('th').length;

@if($campaignID == 'All')
var extraColumn = totalColumn - 10;
@else
var extraColumn = totalColumn - 9;
@endif

var currentColumn = 0;

$(function() {
	@if(isset($column))
	    var type = '{!! $type !!}';
        if(type == 'asc') {
            $('.sort[data-name="{!! $column !!}"]').addClass('highlight');
            $('.sort[data-name="{!! $column !!}"]').attr('data-type', 'desc');
            $('.sort[data-name="{!! $column !!}"]').attr('title', 'desc');
        }
        else if(type == 'desc') {
            $('.sort[data-name="{!! $column !!}"]').addClass('highlight');
            $('.sort[data-name="{!! $column !!}"]').attr('data-type', 'asc');
            $('.sort[data-name="{!! $column !!}"]').attr('title', 'asc');
        }
	@endif

    if(extraColumn > 0) {
    	$('.col_0').find('.next').addClass('enable');
    }

    if(extraColumn == 0) {
    	$('.col_0').find('.next').addClass('hidden');
    }

	$('.sort').click(function() {
		var column = $(this).attr('data-name');
		var type = $(this).attr('data-type');

		$('.sort').removeClass('highlight');
		$(this).addClass('highlight');

		$('.search').each(function() {
			var id = $(this).attr('id');

			if(!$(this).hasClass('hidden')) {
				if(id == 'normalSearch') {
					var formData = getCampaignLeadData();

                    var campaignID = $('#normalSearch').find('#campaign').val();
                    hideErrors();

                    if(campaignID != '') {
                        formData = formData + '&column=' + column + '&type=' + type;
                        getLeadData(formData);
                    }
                    else {
                        showError('Please select a campaign');
                        $('.sort').removeClass('highlight');
                        return false;
                    }
				}
				else if(id == 'advanceSearch') {
					$('#advanceSearch').find('#search').trigger('click');
				}
			}
		});
	});
});

</script>
