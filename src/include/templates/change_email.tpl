<div class="main">
<div class="block bprofile">

	<h2 class="blocktitle">{{ lang_profile['Change email'] }}</h2>
	<div class="box">
		<form id="change_email" method="post" action="{{ form_action }}" onsubmit="return process_form(this)">
			<div class="inform">
				<fieldset>
					<legend>{{ lang_profile['Email legend'] }}</legend>
					<div class="infldset">
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
						<label class="required"><strong>{{ lang_profile['New email'] }}<span>{{ lang_common['Required'] }}</span></strong><br /><input type="text" name="req_new_email" size="50" maxlength="80" /><br /></label>
						<label class="required"><strong>{{ lang_common['Password'] }} <span>{{ lang_common['Required'] }}</span></strong><br /><input type="password" name="req_password" size="16" /><br /></label>
						<p>{{ lang_profile['Email instructions'] }}</p>
					</div>
				</fieldset>
			</div>
			<p class="buttons"><input type="submit" name="new_email" value="{{ lang_common['Submit'] }}" /> <a href="javascript:history.go(-1)">{{ lang_common['Go back'] }}</a></p>
		</form>
	</div>
	
	</div>
</div>
</div> <!-- .profile-console -->