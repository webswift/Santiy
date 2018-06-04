@extends('layouts.admindashboard')

@section('title')
Help Topics
@stop

@section('css')
{!! Html::style('assets/css/bootstrap-wysihtml5.css') !!}
@stop

@section('content')

<div class="pageheader">
    <h2><i class="fa fa-envelope"></i> User Help Topics and Documentations </h2>

</div>

<div class="contentpanel panel-email">


    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <a class="btn btn-danger btn-block btn-compose-email" onclick="createNewTopicModal()">Create a new Topic</a>

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

            <div class="panel-heading">
                <h3 class="panel-title">Create Article</h3>
            </div>

            <div class="panel panel-default">
                <div class="panel-body">

                    <div id="error"></div>
                    <div class="col-sm-12">
                        <div class="form-group col-sm-12">
                            <input type="text" id="articleName" placeholder="Article Name" class="form-control" />
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="col-md-5">
                            <div class="form-group">
                                <select id="topicID" class="form-control mb15">
                                    <option value=""> Select a Topic</option>
                                    @foreach($topicLists as $topicList)
                                        <option value="{{ $topicList->id }}" @if($topicList->id == $alreadySelectedValue) selected @endif >{{ $topicList->topic }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group form-horizontal">
                                <label class="col-sm-3 control-label">Keywords</label>
                                <div class="col-sm-9">
                                    <input type="text" id="keywords" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <textarea id="articleText" placeholder="Your message here..." class="form-control" rows="20"></textarea>
                    </div>



                </div><!-- panel-body -->
                <div class="panel-footer">
                    <button onclick="addArticle()" class="btn btn-primary">Save</button>
                </div>
            </div><!-- panel -->

        </div><!-- col-sm-9 -->

    </div><!-- row -->

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
{!! HTML::script('assets/js/wysihtml5-0.3.0.min.js') !!}
{!! HTML::script('assets/js/bootstrap-wysihtml5.js') !!}
<script>

    // HTML5 WYSIWYG Editor
    jQuery('#articleText').wysihtml5({color: true,html:true});


    function createNewTopicModal()
    {
        $('#addNewTopicModal').modal('show');
    }

    function showTopicArticles(topicID)
    {
        location.href = "{{ URL::route('admin.helptopics') }}/"+topicID;
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

    function addArticle()
    {
        var errorID = "#error";
        $(errorID).html('');

        var articleName = $('#articleName').val();
        var topicID = $('#topicID').val();
        var keywords = $('#keywords').val();
        var articleText = $('#articleText').val();

        if(articleName == '')
        {
            $(errorID).html('<div class="alert alert-danger">Please Enter Article Name.</div>');
        }else if(topicID == '')
        {
            $(errorID).html('<div class="alert alert-danger">Please Select a Topic.</div>');
        }else if(keywords == '')
        {
            $(errorID).html('<div class="alert alert-danger">Please Enter Keywords.</div>');
        }else if(articleText == '')
        {
            $(errorID).html('<div class="alert alert-danger">Please Enter Article Text.</div>');
        }else{
            $.ajax({
                type: 'post',
                url: "{{ URL::route('admin.helptopics.addnewarticle') }}",
                cache: false,
                data: {"articleName": articleName, "topicID": topicID, "keywords": keywords, "articleText": articleText},
                success: function(response) {
                    var obj = jQuery.parseJSON(response);

                    if(obj.success == "success")
                    {
                        location.href = "{{ URL::route('admin.helptopics')}}";
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