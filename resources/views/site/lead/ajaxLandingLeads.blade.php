<div class="table-responsive" id="landingDiv">
    <table id="landingTable" class="table table-success">
        <thead>
        <tr>
            <th style="width: 16%" class="col_0">
                Lead received on
               {{--@if($campaignID != 'all')--}}
                @if(sizeof($landingLeads) > 0)
                <i class="fa fa-chevron-right pull-right colToggle next" onclick="getNextLandingColumn();"></i>
                @endif
                {{--@endif--}}
            </th>
            {{--@if($campaignID != 'all')--}}
            @if(sizeof($landingLeads) > 0)
                <?php $count = 1; ?>
                @foreach($landingLeads[0]['leadInfo'] as $key => $value)
                    @if(!in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Telephone No')) &&
                    !in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('First Name')) &&
                    !in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Email')) &&
                    !in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Post/Zip code')) &&
                    !in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Zip Code')))
                        <th class="hidden col_{{$count}}" style="width: 16%">
                            <i class="fa fa-chevron-left pull-left colToggle enable prev" onclick="getPreviousLandingColumn();"></i>
                            {{$key}}
                            <i class="fa fa-chevron-right pull-right colToggle enable next" onclick="getNextLandingColumn();"></i>
                        </th>
                        <?php $count++; ?>
                    @endif
                @endforeach
            @endif
            {{--@endif--}}
            <th style="width: 16%">
                <div class="sorting-div sort" data-name="firstName" data-type="asc" title="asc"> First Name </div>
            </th>
            <th>
                <div class="sorting-div sort" data-name="telephone" data-type="asc" title="asc"> Telephone </div>
            </th>
            <th style="width: 16%">
                <div class="sorting-div sort" data-name="email" data-type="asc" title="asc"> Email </div>
            </th>
            <th>
                <div class="sorting-div sort" data-name="zipCode" data-type="asc" title="asc"> Post/Zip Code </div>
            </th>
            @if($campaignID == 'all')
                <th>
                    <div class="sorting-div sort" data-name="campaign" data-type="asc"> Campaign </div>
                </th>
            @endif
            <th>Open</th>
        </tr>
        </thead>
        <tbody>
        @if(sizeof($landingLeads) > 0)
            @foreach($landingLeads as $landingLead)
                <tr>
                    <td class="col_0 leadRow">{{ $landingLead['receivedOn'] or '-'}}</td>
                    {{--@if($campaignID != 'all')--}}
                    <?php $counter = 1; ?>
                    @foreach($landingLead['leadInfo'] as $key => $value)
                        @if(!in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Telephone No')) &&
                            !in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('First Name')) &&
                            !in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Email')) &&
                            !in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Post/Zip code')) &&
                            !in_array($key, \App\Http\Controllers\CommonController::getFieldVariations('Zip Code')))
                            <td class="leadRow hidden col_{{$counter}}" style="width: 20%">@if($value != '' || $value != null) {{ $value }} @else {{ '-' }} @endif</td>
                            <?php $counter++; ?>
                        @endif
                    @endforeach
                    {{--@endif--}}
                    <td>@if(trim($landingLead['firstName']) != ''){{$landingLead['firstName']}}@else{{'-'}}@endif</td>
                    <td>{{ $landingLead['telephone'] or '-' }}</td>
                    <td>{{ $landingLead['email'] or '-' }}</td>
                    <td>{{ $landingLead['zipCode'] or '-' }}</td>
                    @if($campaignID == 'all')
                        <td>{{ $landingLead['campaignName'] or '-' }}</td>
                    @endif
					<td><i class="fa fa-edit cursor_pointer" onclick="openInboundLead({{ $landingLead->id }});"></i></a></td>
                </tr>
            @endforeach
        @else
            <tr><td colspan="6" class="text-center">No data available</td></tr>
        @endif
        </tbody>
    </table>
</div><!-- table-responsive -->

<div class="row pull-right">
    <div class="col-sm-12">
        @if(is_object($landingLeads))
            {!! str_replace('/?', '?', $landingLeads->appends(Input::except('page'))->render()) !!}
        @endif
    </div>
</div>

<script>
var totalLandingColumn = $('#landingTable').find('th').length;

@if($campaignID == 'all')
    var extraLandingColumn = totalLandingColumn - 7;
@else
    var extraLandingColumn = totalLandingColumn - 6;
@endif

var currentLandingColumn = 0;

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

    if(extraLandingColumn > 0) {
        $('#landingTable').find('.col_0').find('.next').addClass('enable');
    }

    if(extraLandingColumn == 0) {
        $('#landingTable').find('.col_0').find('.next').addClass('hidden');
    }

    $('.sort').click(function() {
        var column = $(this).attr('data-name');
        var type = $(this).attr('data-type');

        $('.sort').removeClass('highlight');
        $(this).addClass('highlight');

        showLandingLeads(true, {column : column, type : type});
    });
});

function getPreviousLandingColumn() {
    if(currentLandingColumn > 0){

        currentLandingColumn = currentLandingColumn - 1;

        $('#landingTable').find('th[class^=col_]').addClass('hidden');
        for(var i=0; i <= extraLandingColumn; i++){
            $('#landingTable').find('.col_'+i).addClass('hidden');
        }

        $('#landingTable').find('.col_'+currentLandingColumn).removeClass('hidden');
    }
}

function getNextLandingColumn(){
    if(currentLandingColumn < extraLandingColumn){
        currentLandingColumn = currentLandingColumn + 1;

        $('#landingTable').find('th[class^=col_]').addClass('hidden');

        for(var i=0; i < extraLandingColumn; i++){
            $('#landingTable').find('.col_'+i).addClass('hidden');
        }


        $('#landingTable').find('.col_'+currentLandingColumn).removeClass('hidden');

        if(currentLandingColumn == extraLandingColumn){
            $('#landingTable').find('.col_'+currentLandingColumn).find('.next').addClass('hidden');
        }
    }
}

</script>
