<div class="table-responsive">
	<table class="table table-hover mb30">
		<thead>
			<tr>
				<th>Date</th>
                <th>Attached By</th>
                <th>File Name</th>
                <th>Actions</th>
			</tr>
		</thead>
		<tbody>
			@if(sizeof($attachments) > 0)
				@foreach($attachments as $data)
					<tr id="attached_{{$data->id}}">
						<td>{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->time)->format('d/m/Y')}}</td>
						<td>{{$data->userName}}</td>
						<td>{{$data->originalFileName}}</td>
						<td>
							<a title="Download File" href="{{URL::route('user.leads.downloadAttachment', array($data->id))}}"  class="btn btn-primary btn-xs"><i class="fa fa-download"></i></a>
							<button title="Delete File" onclick="deleteAttachedFile('{{$data->id}}')" class="btn btn-danger btn-xs"><i class="fa fa-trash-o"></i></button>
						</td>
					</tr>
				@endforeach
			@else
			<tr><td colspan="4" style="text-align: center">No attachments found</td></tr>
			@endif
		</tbody>
	</table>
</div>