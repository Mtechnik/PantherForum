{% if errors is not empty %}
<div id="posterror" class="block">
	<h2><span>{{ lang_login['New password errors'] }}</span></h2>
	<div class="box">
		<div class="inbox error-info">
			<p>{{ lang_login['New passworderrors info'] }}</p>
			<ul class="error-list">
{% for error in errors %}
<li><strong>{{ error }}</strong></li>
{% endfor %}
			</ul>
		</div>
	</div>
</div>
{% endif %}
<div class="block blogin">
	<h2 class="blocktitle">{{ lang_login['Request pass'] }}</h2>
	<div class="box">
		<form id="request_pass" method="post" action="{{ form_url }}" onsubmit="this.request_pass.disabled=true;if(process_form(this)){return true;}else{this.request_pass.disabled=false;return false;}">
			<div class="inform">
				<fieldset>
					<legend>{{ lang_login['Request pass legend'] }}</legend>
					<div class="infldset">
						<input type="hidden" name="form_sent" value="1" />
						<input name="csrf_token" value="{{ csrf_token }}" type="hidden" />
						<label class="required"><strong>{{ lang_common['Email'] }} <span>{{ lang_common['Required'] }}</span></strong><br /><input id="req_email" type="text" name="req_email" size="50" maxlength="80" /><br /></label>
						<p>{{ lang_login['Request pass info'] }}</p>
					</div>
				</fieldset>
			</div>
			<div class="buttons"><input type="submit" name="request_pass" value="{{ lang_common['Submit'] }}" class="btn submit" />{% if errors is not empty %}<a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a>{% endif %}</div>
		</form>
	</div>
</div>