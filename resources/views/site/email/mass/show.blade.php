<div class="modal-header">
    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
    <div id="headerInfo">
        <h4 class="modal-title">Email Preview</h4>
    </div>
</div>
<div class="modal-body">
	<div id="emailPreviewContent">
    {!! $email->content !!}
	</div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>
