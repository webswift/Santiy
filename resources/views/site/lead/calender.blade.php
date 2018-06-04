<div class="modal-header">
    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
    <h4 class="modal-title">Calendar</h4>
</div>
<div class="modal-body">
    @if($error == true)
        <div class="no-data-placeholder">{{ $message }}</div>
    @else
    <div class="col-md-3">
        <div class="panel panel-default panel-dark panel-alt">
            <div class="panel-heading">
                <h4 class="panel-title">SALES TEAM</h4>
            </div>
            <div class="panel-body">
                <div id='external-events'>
                    @if(sizeof($salesMemberLists) > 0)
                        @foreach($salesMemberLists as $salesMemberList)
                            <div class='external-event'>
                                <div class="ckbox ckbox-default" style="margin-top: 7px;">
                                    <input class="salesmanBox" type="checkbox" value="{{ $salesMemberList->id }}" id="{{ $salesMemberList->id }}" checked="checked">
                                    <label for="{{ $salesMemberList->id }}" style="color: white;">{{ $salesMemberList->firstName }}</label>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div><!-- col-md-3 -->
    <div class="col-md-9">
        <div id="calendar"></div>
    </div>
    <div class="clearfix"></div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>


<script>

    //calender rendering code is in main page
    $(function() {
        jQuery('#calenderModal').find('#calendar').fullCalendar({
            header: {
                left: 'prev,next,today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            events: {
                url: "{{ URL::route('user.leads.calanderappointmentdata') }}",
                type: 'POST',
                data: function() {
                    var salesmanIDs = new Array();
                    $('.salesmanBox:checked').each(function() {
                        salesmanIDs.push($(this).val());
                    });
                    return {salesmanIDs: salesmanIDs};
                },
                error: function() {
                    unblockUI('.contentpanel');
                    showError('Please select a salesman');
                }
            },
            loading : function(bool) {
                if(bool) {
                    blockUI('#calendar');
                }
                else {
                    unblockUI('#calendar');
                }
            },
            eventClick: function(calEvent, jsEvent, view) {
                var appointmentID = calEvent.id;
                var newdate = calEvent.newdate;
                var time = calEvent.time;
                var salesmanID = calEvent.salesmanID;
                var leadID = calEvent.leadID;

                var viewLeadUrl = '{{ URL::route('user.leads.viewlead', ['leadID' => '#id']) }}';
                viewLeadUrl = viewLeadUrl.replace('#id', leadID);

                $('#bookedInfo').find('#salesmanID').val(salesmanID);
                $('#bookedInfo').find('#bookDate').val(newdate);
                $('#bookedInfo').find('#bookTimepicker').val(time);
                $('#bookedInfo').find('#leadID').val(leadID);

                $('#bookedInfo').find('#cancelAppointment').attr('onclick', "cancelAppointment("+appointmentID+")");
                $('#bookedInfo').find('#viewAppointmentInfo').attr('href', viewLeadUrl);
                $('#bookedInfo').find('#saveApppintment').attr('onclick', "saveAppointment("+appointmentID+")");

                $('#bookedInfo').modal('show');
            }
        });

        $('.salesmanBox:checked').click(function() {
            jQuery('#calenderModal').find('#calendar').fullCalendar( 'refetchEvents' );
        });

        $('#bookedInfo').find('#bookTimepicker').timepicker();
        $('#bookedInfo').find("#bookDate").mask("99-99-9999");

        jQuery('#external-events div.external-event').each(function() {
            var eventObject = {title: $.trim($(this).text())};

            jQuery(this).data('eventObject', eventObject);

            jQuery(this).draggable({
                zIndex: 999,
                revert: true,
                revertDuration: 0
            });
        });
    });
</script>
