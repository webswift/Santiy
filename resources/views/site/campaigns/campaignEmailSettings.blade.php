<div class="form-group">
	<div class="col-sm-3"></div>
	<div class="col-sm-3">
		<div class="rdio rdio-success">
			<input type="radio" name="setting" value="default" id="default" 
				@if(!isset($mailSettings)) checked @endif 
				onchange="return campaignEmailSettings.switchDefaultSettings();">
			<label for="default">Use Default Settings</label>
		</div>
	</div>
	<div class="col-sm-6">
		<div class="rdio rdio-success">
			<input type="radio" name="setting" value="advanced" id="advanced" 
				@if(isset($mailSettings)) checked @endif 
				onchange="return campaignEmailSettings.switchDefaultSettings();">
			<label for="advanced">Use a different email address for this campaign</label>
		</div>
	</div>
</div>


<div id="advancedDiv" @if(!isset($mailSettings)) class="hidden" @endif>

	<div class="form-group">
		<label class="col-sm-3 control-label" for="fromEmail">From Email</label>
		<div class="col-sm-2">
			<input class="form-control input-sm" type="text" name="fromEmail" id="fromEmail"
				@if(isset($mailSettings)) value="{{ $mailSettings->fromEmail }}" @endif	
			>
		</div>

		<label class="col-sm-2 control-label" for="replyEmail">Reply To Email</label>
		<div class="col-sm-2">
			<input class="form-control input-sm" type="email" name="replyEmail" id="replyEmail"
				@if(isset($mailSettings)) value="{{ $mailSettings->replyToEmail }}" @endif	
			>
		</div>
	</div>

	<div class="form-group">
		<div class="col-sm-12">
			<div class="ckbox ckbox-default">
				<input type="checkbox" value="Yes" id="smtpSetting" name="smtpSetting" 
					@if(isset($mailSettings) && $mailSettings->smtpSetting == 'Yes') checked @endif	
					onchange="return campaignEmailSettings.switchSmtpSettings();">
				<label for="smtpSetting">Use your own Mail Server (Recommended)</label>
			</div>
		</div>
	</div>
	
	<div class="form-group">
		<div class="col-sm-12">
			<div id="smtpErrorLabel">
			</div>
		</div>
	</div>

	<div id="smtpDiv" 
	  @if(!isset($mailSettings) || $mailSettings->smtpSetting == 'No') class="hidden" @endif	
	  >
		<div class="form-group">
			<div class="col-sm-2">Add mail server details (recommended)</div>
			<label class="control-label col-sm-1">Host</label>
			<div class="col-sm-2">
				<input class="form-control input-sm" type="text" name="host" id="host"
					@if(isset($mailSettings) && $mailSettings->smtpSetting == 'Yes') value="{{ $mailSettings->host }}" @endif	
				>
			</div>

			<label class="control-label col-sm-1">Port</label>
			<div class="col-sm-1">
				<input class="form-control input-sm" type="text" name="port" id="port"
					@if(isset($mailSettings) && $mailSettings->smtpSetting == 'Yes') value="{{ $mailSettings->port }}" @endif	
				>
			</div>

			<label class="control-label col-sm-1">Username</label>
			<div class="col-sm-2">
				<input class="form-control input-sm" type="text" name="username" id="username"
					@if(isset($mailSettings) && $mailSettings->smtpSetting == 'Yes') value="{{ $mailSettings->username }}" @endif	
				>
			</div>

			<label class="control-label col-sm-1">Password</label>
			<div class="col-sm-1">
				<input class="form-control input-sm" type="password" name="password" id="password">
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-3 control-label">Security</label>
			<div class="col-sm-3">
				<select id="security" name="security" class="form-control">
					<option value="No"@if(isset($mailSettings) && $mailSettings->smtpSetting == 'Yes' && $mailSettings->security == 'No') selected @endif>None</option>
					<option value="tls" @if(isset($mailSettings) && $mailSettings->smtpSetting == 'Yes' && $mailSettings->security == 'tls') selected @endif>TLS/STARTTLS</option>
					<option value="ssl" @if(isset($mailSettings) && $mailSettings->smtpSetting == 'Yes' && $mailSettings->security == 'ssl') selected @endif>SSL</option>
				</select>
			</div>
			<div class="col-sm-3"><button class="btn btn-success btn-sm" type="button" onclick="verifyEmailSettings();">Verify email settings</button></div>
		</div>
	</div>
</div>
