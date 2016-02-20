<div class="content">

<div class="block pagetitle">
<h1>{{ lang_admin_common['Permissions'] }}</h1>
</div>

	<div class="block">
		<h2>{{ lang_admin_permissions['Permissions head'] }}</h2>
	
			<form method="post" action="{{ form_action }}">
				<span class="submitform top"><input type="submit" name="save" value="{{ lang_admin_common['Save changes'] }}" /></span>
				<div class="box">
					<input type="hidden" name="form_sent" value="1" />
					<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
					
						<p class="boxtitle">{{ lang_admin_permissions['Posting subhead'] }}</p>
						<div class="inbox">
						
								<div class="row">
									<div class="col label">{{ lang_admin_permissions['BBCode label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[message_bbcode]" value="1"{% if panther_config['p_message_bbcode'] == '1' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[message_bbcode]" value="0"{% if panther_config['p_message_bbcode'] == '0' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<p class="info">{{ lang_admin_permissions['BBCode help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_permissions['Image tag label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[message_img_tag]" value="1"{% if panther_config['p_message_img_tag'] == '1' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[message_img_tag]" value="0"{% if panther_config['p_message_img_tag'] == '0' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<p class="info">{{ lang_admin_permissions['Image tag help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_permissions['All caps message label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[message_all_caps]" value="1"{% if panther_config['p_message_all_caps'] == '1' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[message_all_caps]" value="0"{% if panther_config['p_message_all_caps'] == '0' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<p class="info">{{ lang_admin_permissions['All caps message help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_permissions['All caps subject label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[subject_all_caps]" value="1"{% if panther_config['p_subject_all_caps'] == '1' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[subject_all_caps]" value="0"{% if panther_config['p_subject_all_caps'] == '0' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<p class="info">{{ lang_admin_permissions['All caps subject help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_permissions['Require e-mail label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[force_guest_email]" value="1"{% if panther_config['p_force_guest_email'] == '1' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[force_guest_email]" value="0"{% if panther_config['p_force_guest_email'] == '0' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<p class="info">{{ lang_admin_permissions['Require e-mail help'] }}</p>
									</div>
								</div>
						
						</div>
					
				</div>
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_permissions['Signatures subhead'] }}</p>
						<div class="inbox">
							
								<div class="row">
									<div class="col label">{{ lang_admin_permissions['BBCode sigs label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[sig_bbcode]" value="1"{% if panther_config['p_sig_bbcode'] == '1' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[sig_bbcode]" value="0"{% if panther_config['p_sig_bbcode'] == '0' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<p class="info">{{ lang_admin_permissions['BBCode sigs help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_permissions['Image tag sigs label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[sig_img_tag]" value="1"{% if panther_config['p_sig_img_tag'] == '1' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[sig_img_tag]" value="0"{% if panther_config['p_sig_img_tag'] == '0' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<p class="info">{{ lang_admin_permissions['Image tag sigs help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_permissions['All caps sigs label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[sig_all_caps]" value="1"{% if panther_config['p_sig_all_caps'] == '1' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[sig_all_caps]" value="0"{% if panther_config['p_sig_all_caps'] == '0' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<p class="info">{{ lang_admin_permissions['All caps sigs help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_permissions['Max sig length label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[sig_length]" size="5" maxlength="5" value="{{ panther_config['p_sig_length'] }}" />
										<p class="info">{{ lang_admin_permissions['Max sig length help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_permissions['Max sig lines label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[sig_lines]" size="3" maxlength="3" value="{{ panther_config['p_sig_lines'] }}" />
										<p class="info">{{ lang_admin_permissions['Max sig lines help'] }}</p>
									</div>
								</div>
							
						</div>
					
				</div>
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_permissions['Registration subhead'] }}</p>
						<div class="inbox">
						
								<div class="row">
									<div class="col label">{{ lang_admin_permissions['Banned e-mail label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[allow_banned_email]" value="1"{% if panther_config['p_allow_banned_email'] == '1' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[allow_banned_email]" value="0"{% if panther_config['p_allow_banned_email'] == '0' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<p class="info">{{ lang_admin_permissions['Banned e-mail help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_permissions['Duplicate e-mail label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[allow_dupe_email]" value="1"{% if panther_config['p_allow_dupe_email'] == '1' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[allow_dupe_email]" value="0"{% if panther_config['p_allow_dupe_email'] == '0' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<p class="info">{{ lang_admin_permissions['Duplicate e-mail help'] }}</p>
									</div>
								</div>
						
						</div>
					
				</div>
				<span class="submitform bottom"><input type="submit" name="save" value="{{ lang_admin_common['Save changes'] }}" /></span>
			</form>
		
	</div>

</div>
</div><!-- .admin-console -->	