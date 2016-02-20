<div class="main">
<div class="block bprofile">
	<h2>{{ lang_profile['Change pass'] }}</h2>
	<div class="box">
		<form id="change_pass" method="post" action="{{ form_action }}" onsubmit="return process_form(this)">
			<div class="inform">
				<input type="hidden" name="form_sent" value="1" />
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<fieldset>
					<legend>{{ lang_profile['Change pass legend'] }}</legend>
					<div class="infldset">
					{% if panther_user['is_admmod'] == false %}
					<label class="required"><strong>{{ lang_profile['Old pass'] }}<span>{{ lang_common['Required'] }}</span></strong><br />
					<input type="password" name="req_old_password" size="16" /><br /></label>
					{% endif %}
						<label class="conl required"><strong>{{ lang_profile['New pass'] }}<span>{{ lang_common['Required'] }}</span></strong><br />
						<input type="password" name="req_new_password1" size="16" /><br /></label>
						<label class="conl required"><strong>{{ lang_profile['Confirm new pass'] }}<span>{{ lang_common['Required'] }}</span></strong><br />
						<input type="password" name="req_new_password2" size="16" /><br /></label>
						<p class="clearb">{{ lang_profile['Pass info'] }}</p>
					</div>
				</fieldset>
			</div>
			<p class="buttons"><a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a><input type="submit" name="update" value="{{ lang_common['Submit'] }}" class="btn submit" /></p>
		</form>
	</div>
</div>
</div>
</div> <!-- .profile-console -->