@extends('layouts.dashboard')

@section('title')
    Help Topics
@stop

@section('css')
{!! Html::style('assets/css/helvetica.css') !!}
@stop

@section('content')

<div class="pageheader">
    <h2><i class="fa fa-envelope"></i> Help Topics and Documentations </h2>
   
</div>

<div class="contentpanel panel-email">


<div class="row">
<div class="col-sm-3 col-lg-2">
<h4>Help Topics</h4>
    <input type="hidden" value="{{ $postedTopicID or ''}}" id="postedTopicID">
    <ul class="nav nav-pills nav-stacked nav-email">

    @if(sizeof($topicLists) > 0)

        @foreach($topicLists as $topicList)
        <li>
            <a href="javascript:void(0)">
                <i class="glyphicon glyphicon-folder-open"></i> <span onclick="showTopicArticles({{ $topicList->id }})">{{ $topicList->topic }}</span>
            </a>
        </li>
        @endforeach
        @endif
    </ul>

</div><!-- col-sm-3 -->

<div class="col-sm-9 col-lg-10">

<div class="panel panel-default">
<div class="panel-body">


<h5 class="subtitle mb5">Artical Listed Below</h5>
<p class="text-muted">Showing 1 - <span id="totalarticleCount">{{ $totalResultCount }}</span></p>

<div class="table-responsive">
<table class="table table-email">
<tbody id="articleTopics">

@foreach($topicArticles as $topicArticle)
<tr>

    <td>
        <div class="media">
            <div class="media-body">
                <h4 class="text-primary"><a href="{{ URL::route('user.helptopics.detail', array($topicArticle->id)) }}">{{ $topicArticle->articleName }}</a></h4>
            </div>
        </div>
    </td>
</tr>
@endforeach


</tbody>
</table>
</div><!-- table-responsive -->

</div><!-- panel-body -->
</div><!-- panel -->

</div><!-- col-sm-9 -->

</div><!-- row -->

</div>

@stop


@section('javascript')
    {!! HTML::script('assets/js/custom.js') !!}
<script>

    jQuery(document).ready(function()
    {
        var postedTopicID = $('#postedTopicID').val();

        if(postedTopicID != "")
        {
            showTopicArticles(postedTopicID);
        }
    });

    function showTopicArticles(topicID)
    {
        if(topicID == '')
        {
            alert('Please Select a topic');
        }else{
            $.ajax({
                type: 'post',
                url: "{{ URL::route('user.helptopics.ajaxtopicarticle') }}",
                cache: false,
                data: {"topicID": topicID},
                success: function(response) {
                    var obj = jQuery.parseJSON(response);

                    if(obj.success == 'success')
                    {
                        $('#articleTopics').html(obj.results);
                        $('#totalarticleCount').html(obj.totalResultCount);
                        $('#addButton').attr('href', "{{ URL::route('admin.helptopics.addoreditarticle') }}"+"/add/"+topicID);
                    }


                },
                error: function(xhr, textStatus, thrownError) {
                    alert('Something went wrong. Please Try again later!');
                }
            });
        }
    }




</script>

@stop