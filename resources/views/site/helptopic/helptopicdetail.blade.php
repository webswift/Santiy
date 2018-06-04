@extends('layouts.dashboard')

@section('title')
 Help Topic Detail
@stop

@section('content')

<div class="pageheader">
    <h2><i class="fa fa-envelope"></i> Help Topic </h2>

</div>

<div class="contentpanel panel-email">

    <div class="row">
        <div class="col-sm-3 col-lg-2">
        <h4>Help Topics</h4>

            <ul class="nav nav-pills nav-stacked nav-email">
					@if(sizeof($topicLists) > 0)
                    @foreach($topicLists as $topicList)
                    <li>
                        <a href="{{ URL::route('user.helptopics', array($topicList->id)) }}">
                            <i class="glyphicon glyphicon-folder-open"></i> <span>{{ $topicList->topic }}</span>
                        </a>
                    </li>
                    @endforeach
                    @endif
                </ul>
        </div><!-- col-sm-3 -->

        <div class="col-sm-9 col-lg-10">

            <div class="panel panel-default">
                <div class="panel-body">

                    <div class="btn-group mr10">
                        <a href="{{ URL::route('user.helptopics') }}" class="btn btn-sm btn-white tooltips" type="button" data-toggle="tooltip" title="Show All Messages"><i class="glyphicon glyphicon-chevron-left"></i></a>
                    </div>

                    <div class="read-panel">

                        <div class="media">
                            <div class="media-body">
                                <span class="media-meta pull-right">{{ (new DateTime($articleDetail->timeCreated))->format('d-m-Y') }}</span>
                                <h4 class="text-primary">{{ $articleDetail->articleName }}</h4>
                            </div>
                        </div><!-- media -->


                        <p>{!! $articleDetail->text !!}</p>

                        <br />



                    </div><!-- read-panel -->

                </div><!-- panel-body -->
            </div><!-- panel -->

        </div><!-- col-sm-9 -->

    </div><!-- row -->

</div>
@stop

@section('javascript')
{!! Html::script('assets/js/custom.js') !!}
<script>

</script>
@stop