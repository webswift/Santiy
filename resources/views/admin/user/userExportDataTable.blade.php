<table class="table table-success mb30">
    <thead>
    <tr>
        <th>Uploaded By</th>
        <th>Total Leads</th>
        <th>Total Number of database</th>
        <th>Industry</th>
        <th>Export as Zip</th>
    </tr>
    </thead>
    <tbody>
    @foreach($dataResults as $dataResult)
        <tr>
            <td>{{ $dataResult->email }}</td>
            <td>{{ $dataResult->totalLeads }}</td>
            <td>{{ $dataResult->totalDatabase }}</td>
            <td>{{ $dataResult->industry }}</td>
            <td><button onclick="exportIt(this)" startdate="{{ $startDate }}" enddate="{{ $endDate }}" industry="{{ $dataResult->industry }}" creator="{{ $dataResult->creator }}" class="btn btn-sm btn-success">Export</button></td>
        </tr>
    @endforeach
    </tbody>
</table>