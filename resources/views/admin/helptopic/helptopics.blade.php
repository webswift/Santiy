@extends('layouts.admindashboard')

@section('title')
    Help Topics
@stop

@section('css')
{!! Html::style('assets/css/helvetica.css') !!}
@stop

@section('content')

<div class="pageheader">
    <h2><i class="fa fa-envelope"></i> User Help Topics and Documentations </h2>
</div>

<div class="contentpanel panel-email">
    @if($successMessage != '')
    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-{{ $successMessageClass or 'success'}}">
                <a class="close" data-dismiss="alert" href="#" aria-hidden="true">×</a>
                {{ $successMessage }}
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <a class="btn btn-danger btn-block btn-compose-email" onclick="createNewTopicModal()">Create a new Topic</a>
            <input type="hidden" value="{{ $postedTopicID or ''}}" id="postedTopicID">

            <ul class="nav nav-pills nav-stacked nav-email">
                @foreach($topicLists as $topicList)
                <li>
                    <a>
                        <span class="badge pull-right" onclick="deleteTopic({{ $topicList->id }})">x</span>
                        <i class="fa fa-plus"></i> <span onclick="showTopicArticles({{ $topicList->id }})">{{ $topicList->topic }}</span>
                    </a>
                </li>
                @endforeach
            </ul>
        </div><!-- col-sm-3 -->

        <div class="col-sm-9 col-lg-10">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="pull-right">
                        <div class="btn-group mr10">
                            <a id="addButton" href="{{ URL::route('admin.helptopics.addoreditarticle', array('add')) }}" class="btn btn-sm btn-white tooltips" type="button" data-toggle="tooltip" title="Add New Article"><i class="glyphicon glyphicon-plus"></i></a>
                        </div>
                    </div><!-- pull-right -->

                    <h5 class="subtitle mb5">Article Listed Below</h5>
                    <p class="text-muted">Showing 1 - <span id="totalarticleCount">{{ $totalResultCount }}</span></p>

                    <div class="table-responsive">
                        <table class="table table-email">
                            <tbody id="articleTopics">

                            @foreach($topicArticles as $topicArticle)
                            <tr>
                                <td>
                                    <div class="ckbox ckbox-success">
                                        <input type="checkbox" id="checkbox3">
                                        <label for="checkbox3"></label>
                                    </div>
                                </td>
                                <td>

                                </td>
                                <td>
                                    <div class="media">
                                        <div class="media-body">
                                            <span class="media-meta pull-right" onclick="deleteThisArticle({{ $topicArticle->id }})">x</span>
                                            <h4 class="text-primary"><a href="{{ URL::route('admin.helptopics.addoreditarticle', array('edit', $topicArticle->id)) }}">{{ $topicArticle->articleName }}</a></h4>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('bootstrapModel')
<!-- Add new Topic model -->
<div id="addNewTopicModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <div id="headerInfo">
                    <h4 class="modal-title">Topic Name</h4>

                </div>
            </div>
            <div class="modal-body">
                <div id="error1" ></div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="control-label">Name </label>
                            <input id="newTopicName" type="text" class="form-control" />
                        </div>
                    </div><!-- col-sm-12 -->
                </div><!-- row -->

            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-12">
                        <button onclick="createNewTopic()" class="btn btn-xs btn-success">Submit</button>&nbsp;
<!--                        <button id="resetThisTopic" class="btn btn-xs btn-success">Reset</button>-->
                    </div>
                </div>
            </div><!-- panel-footer -->

        </div>

    </div>
</div>
</div>
</div><!-- Add new Topic model -->
@stop


@section('modelJavascript')


@stop

@section('javascript')

<script>

    jQuery(document).ready(function()
    {
        var postedTopicID = $('#postedTopicID').val();

        if(postedTopicID != "")
        {
            showTopicArticles(postedTopicID);
        }
    });

    function createNewTopicModal()
    {
        $('#addNewTopicModal').modal('show');
    }

    function createNewTopic()
    {
        var topicName = $('#newTopicName').val();

        if(topicName == '')
        {
            $('#error1').html('<div class="alert alert-danger">Please Enter Topic Name.</div>');
        }else{
            $.ajax({
                type: 'post',
                url: "{{ URL::route('admin.helptopics.addnewtopic') }}",
                cache: false,
                data: {"topicName": topicName},
                success: function(response) {
                    var obj = jQuery.parseJSON(response);

                    if(obj.success == "success")
                    {
                        location.reload();
                    }
                },
                error: function(xhr, textStatus, thrownError) {
                    alert('Something went wrong. Please Try again later!');
                }
            });
        }
    }


    function showTopicArticles(topicID)
    {
        if(topicID == '')
        {
            alert('Please Select a topic');
        }else{
            $.ajax({
                type: 'post',
                url: "{{ URL::route('admin.helptopics.ajaxtopicarticle') }}",
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

    function deleteThisArticle(articleID)
    {
        var answer = confirm("Want to delete this Article?");

        if(answer)
        {
            $.ajax({
                type: 'post',
                url: "{{ URL::route('admin.helptopics.deletearticle') }}",
                cache: false,
                data: {"articleID": articleID},
                success: function(response) {
                    var obj = jQuery.parseJSON(response);

                    if(obj.success == 'success')
                    {
                        location.href = "{{ URL::route('admin.helptopics') }}";
                    }


                },
                error: function(xhr, textStatus, thrownError) {
                    alert('Something went wrong. Please Try again later!');
                }
            });
        }

    }

    function deleteTopic(topicID)
    {
        var answer = confirm("Want to delete this Topic?");

        if(answer)
        {
            $.ajax({
                type: 'post',
                url: "{{ URL::route('admin.helptopics.deletetopic') }}",
                cache: false,
                data: {"topicID": topicID},
                success: function(response) {
                    var obj = jQuery.parseJSON(response);

                    if(obj.success == 'success')
                    {
                        location.href = "{{ URL::route('admin.helptopics') }}";
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