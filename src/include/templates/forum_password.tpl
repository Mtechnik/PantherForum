<div class="block">
	<h2 class="blocktitle">{{ lang_common['forum password'] }}</h2>
	<div class="box">
		<form id="request_pass" method="post" action="{{ form_action }}" onsubmit="this.request_pass.disabled=true;if(process_form(this)){return true;}else{this.request_pass.disabled=false;return false;}">
			<div class="inbox">
			
					<legend>{{ lang_common['password legend'] }}</legend>
					<div class="infldset">
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="redirect_url" value="{{ redirect_url }}" />
						<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
						<label class="required"><strong>{{ lang_common['Password'] }} <span>{{ lang_common['Required'] }}</span></strong><br /><input id="req_password" type="password" name="req_password" size="50" maxlength="80" /><br /></label>
						<p>{{ lang_common['password information'] }}</p>
					</div>
		
			</div>
			<p class="buttons"><input type="submit" name="request_pass" value="{{ lang_common['Submit'] }}" /><a href="javascript:history.go(-1)">{{ lang_common['Go back'] }}</a></p>
		</form>
	</div>
</div>