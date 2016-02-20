{% if errors is not empty %}
<div class="block berror">
	<h2 class="blocktitle">{{ lang_misc['Email errors'] }}</h2>
	<div class="box">
		
			<p class="boxtitle">{{ lang_misc['Email errors info'] }}</p>
			<div class="row">
			<ul class="error-list">
{% for error in errors %}
<li><strong>{{ error }}</strong></li>
{% endfor %}
			</ul>
			</div>
		
	</div>
</div>
{% endif %}

<div id="emailform" class="block">
	<h2 class="blocktitle">{{ lang_misc['Send email to']|format(recipient) }}</h2>
	
		<form id="email" method="post" action="{{ form_action }}" onsubmit="this.submit.disabled=true;if(process_form(this)){return true;}else{this.submit.disabled=false;return false;}">
			<div class="box">
			
					
					<div class="row">
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
						<input type="hidden" name="redirect_url" value="{{ redirect_url }}" />
						<label class="required"><strong>{{ lang_misc['Email subject'] }} <span>{{ lang_common['Required'] }}</span></strong><br />
						<input class="longinput" type="text" name="req_subject" size="75" maxlength="70" tabindex="1" value="{{ subject }}" /><br /></label>
						<label class="required"><strong>{{ lang_misc['Email message'] }} <span>{{ lang_common['Required'] }}</span></strong><br />
						<textarea name="req_message" rows="10" cols="75" tabindex="2">{{ message }}</textarea><br /></label>
						<p>{{ lang_misc['Email disclosure note'] }}</p>
					</div>
			
			</div>
			<div class="blockbuttons">
			<a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a>
			<input type="submit" name="submit" value="{{ lang_common['Submit'] }}" tabindex="3" accesskey="s" class="btn submit" />
			</div>
		</form>
	
</div>