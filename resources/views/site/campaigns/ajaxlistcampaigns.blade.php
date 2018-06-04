<table id="table1" class="table">
    <thead>
        <tr>
        	<th></th>
            <th>Campaign Name</th>
            <th>Created On</th>
            <th>Total Leads</th>
            <th>Calls Remaining</th>
            <!-- <th>Number of Interested</th> -->
        </tr>
    </thead>
    <tbody>
    	@foreach($campaignLists as $campaignList)
            <tr>
            	<td><input type="checkbox" class="campaignCheckBox" id="campaign_{{ $campaignList->id }}" value="{{ $campaignList->id }}"></td>
                <td>{{ $campaignList->name }}</td>
                <td>{{ $campaignList->timeStarted or '-' }}</td>
                <td>{{ $campaignList->totalLeads}}</td>
                <td>{{ $campaignList->callsRemaining }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
	jQuery('#table1').dataTable({
      "sPaginationType": "full_numbers",
      "bSort": false
    });


     jQuery(".dataTables_wrapper select").select2({
         minimumResultsForSearch: -1
     });
</script>
