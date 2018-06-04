@extends('layouts.admindashboard')

@section('title')
    Email Templates
@stop

@section('content')

<div class="pageheader">
    <h2><i class="glyphicon glyphicon-edit"></i> Manage Email Templates</h2>
</div>

<div class="contentpanel">
    <div class="panel-heading">
        <h3 class="panel-title">Email Templates </h3>
    </div>

    <div class="panel">
        <div class="panel-body">

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

            <div id="error"></div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive" id="tableData">
                        <table class="table table-success mb30">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Subject</th>
                                <th>Email Content</th>
                                <th>Email Variables</th>
                                <th>Edit</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($emailTemplates as $emailTemplate)
                            <tr>
                                <td>{{ $emailTemplate->name }}</td>
                                <td>{{ $emailTemplate->subject }}</td>
                                <td>{!! $emailTemplate->content !!}  </td>
                                <td>{{ $emailTemplate->variables }}</td>
                                <td><button onclick="editThisTemplate('{{ $emailTemplate->id }}')" class="btn btn-info btn-xs">	<i class="fa fa-edit"></i> </button></td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div><!-- table-responsive -->
                </div>
            </div><!-- panel-body -->
        </div><!-- panel -->
    </div><!-- contentpanel -->

    <!-- Edit Email Preview model -->
    <div id="editEmail" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                    <div id="headerInfo">
                        <h4 class="modal-title">Email Preview</h4>
                    </div>
                </div>
                <div class="modal-body">

                    <div class="form-horizontal form-bordered">

                        <div class="form-group">
                            <div class="col-sm-12">
                                <input type="hidden" id="emailTemplateID" />
                                <label class="col-sm-4 control-label" for="name">Name</label>
                                <div class="col-sm-8">
                                    <input type="text" name="name" id="name" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" for="description">Description</label>
                                <div class="col-sm-8">
                                    <input type="text" name="description" id="description" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" for="from">From</label>
                                <div class="col-sm-8">
                                    <input type="text" name="from" id="from" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" for="replyTo">Reply to</label>
                                <div class="col-sm-8">
                                    <input type="text" name="replyTo" id="replyTo" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" for="subject">Subject</label>
                                <div class="col-sm-8">
                                    <input type="text" name="subject" id="subject" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" for="content">Email Contents</label>
                                <div class="col-sm-8">
                                    <textarea class="form-control" name="content" id="content" rows="5"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="col-sm-4 control-label" for="variables">Email Variables</label>
                                <div class="col-sm-8">
                                    <input type="text" name="variables" id="variables" class="form-control" disabled>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-sm-7 col-sm-offset-5">
                            <button onclick="updateTemplate()" class="btn btn-xs btn-success" data-dismiss="modal">Update</button>
                        </div>
                    </div>
                </div><!-- panel-footer -->

            </div>

        </div>
    </div>
</div>
</div><!-- Email Preview model -->
@stop

@section('javascript')
<script type="text/javascript">

        (function(){
            String.prototype.allIndexOf = function(string, ignoreCase) {
                if (this === null) { return [-1]; }
                var t = (ignoreCase) ? this.toLowerCase() : this,
                    s = (ignoreCase) ? string.toString().toLowerCase() : string.toString(),
                    i = this.indexOf(s),
                    len = this.length,
                    n,
                    indx = 0,
                    result = [];
                if (len === 0 || i === -1) { return [i]; } // "".indexOf("") is 0
                for (n = 0; n <= len; n++) {
                    i = t.indexOf(s, indx);
                    if (i !== -1) {
                        indx = i + 1;
                        result.push(i);
                    } else {
                        return result;
                    }
                }
                return result;
            }
        })();

        function editThisTemplate(templateID)
        {

            $.ajax({
                type: 'post',
                url: "{{ URL::route('admin.ajaxemailtemplatedetails') }}",
                cache: false,
                data: {"templateID": templateID},
                success: function(response) {
                    var obj = jQuery.parseJSON(response);

                    if(obj.success == 'success')
                    {
                        $('#name').val(obj.templateDetails.name);
                        $('#description').val(obj.templateDetails.description);
                        $('#from').val(obj.templateDetails.from);
                        $('#replyTo').val(obj.templateDetails.replyTo);
                        $('#subject').val(obj.templateDetails.subject);
                        $('#content').val(obj.templateDetails.content);
                        $('#variables').val(obj.templateDetails.variables);
                        $('#emailTemplateID').val(obj.templateDetails.id);

                        $('#editEmail').modal('show');
                    }

                },
                error: function(xhr, textStatus, thrownError) {
                    alert('Something went wrong. Please Try again later!');
                }
            });

        }

        $('#content').keyup(function()
        {
            var content = $(this).val();
            var variables = "";

            var indexes = content.allIndexOf('##');
            var totalCount = indexes.length;

            if(totalCount % 2 == 0)
            {
                for(var i = 0; i < totalCount; i=i+2 )
                {
                    variables += content.substring(indexes[i]+2, indexes[i+1]) +',';
                }
            }

            variables = variables.replace(/,\s*$/, "")

            $('#variables').val(variables);

        });

        function updateTemplate()
        {
            var templateID = $('#emailTemplateID').val();
            var name = $('#name').val();
            var description = $('#description').val();
            var from = $('#from').val();
            var replyTo = $('#replyTo').val();
            var subject = $('#subject').val();
            var content = $('#content').val();
            var variables = $('#variables').val();

            $.ajax({
                type: 'post',
                url: "{{ URL::route('admin.updateemailtemplate') }}",
                cache: false,
                data: {"templateID": templateID, "name": name, "description": description, "from": from, "replyTo": replyTo, "subject": subject, "content": content, "variables": variables},
                success: function(response) {
                    var obj = jQuery.parseJSON(response);

                    if(obj.success == 'success')
                    {
                        location.reload();
                    }
                },
                error: function(xhr, textStatus, thrownError) {
                    alert('Something went wrong. Please Try again later!');
                }
            });
        }

    </script>
    @stop