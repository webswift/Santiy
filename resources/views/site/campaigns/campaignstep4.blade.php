@extends('layouts.dashboard')

@section('css')
	  {!! Html::style('assets/css/jquery.datatables.css') !!}
@stop

@section('title')
	Import Completed
@stop

@section('content')

	<div class="pageheader">
        <h2><i class="glyphicon glyphicon-upload"></i> Import Completed</h2>

    </div>

     <div class="contentpanel">
        
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Well done! Import Completed! </h3>
                <p>Showing first 100 leads of all the imported leads for review.</p>
				@if(isset($duplicates) && $duplicates > 0)
				<p><b>We found {{ $duplicates }} duplicates and skipped them.</b></p>
				@endif
				@if(isset($duplicates_updated) && $duplicates_updated > 0)
				<p><b>{{ $duplicates_updated }} duplicates were updated.</b></p>
				@endif
            </div><!-- panel-heading-->
             <div class="panel-body">
                @if(!isset($nocolumn))
                 <div class="table-responsive" style="overflow-x: scroll">
                        <table id="table1" class="table">
                            <thead>
                                <tr>
                                @foreach($tableHeaders as $tableHeader)
                                    <th style="min-width: 200px">{{ $tableHeader->fieldName }}</th>
                                @endforeach
                                </tr>
                            </thead>
                            <tbody>
                              @foreach($newTableDatas as $newTableData)
                                <tr>
                                  @foreach($tableHeaders as $tableHeader)
                                    @if(isset($newTableData[$tableHeader->fieldName]))
                                    <td style="min-width: 200px">{{ $newTableData[$tableHeader->fieldName] }}</td>
                                    @else
                                    <td style="min-width: 200px">&nbsp;</td>
                                    @endif
                                  @endforeach
                                </tr>
                              @endforeach 
                               
                            </tbody>
                        </table>
                        </div><!-- table-responsive -->
                   @else
                   <div class="alert alert-warning">
                       Nothing was imported either because you skipped all the columns or CSV files had no distinct data.
                       Data already in the database was ignored as duplicate.
                   </div>

                   @endif
                        
                      

             </div><!-- panel-body -->
        </div><!-- panel -->

    </div><!-- contentpanel -->
@stop

@section('modelJavascript')
    {!! HTML::script('assets/js/jquery.datatables.min.js') !!}

  <script type="text/javascript">

    jQuery(document).ready(function() {
      
        jQuery('#table1').dataTable({
          "sPaginationType": "full_numbers"
        });

        jQuery(".dataTables_wrapper select").select2({
             minimumResultsForSearch: -1
         });
     });
  </script>
@stop

@section('javascript')
    {!! HTML::script('assets/js/custom.js') !!}
@stop
