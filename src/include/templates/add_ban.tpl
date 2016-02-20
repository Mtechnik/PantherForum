<div class="content">
<div class="block">
		<h2>{{ lang_admin_bans['Ban advanced head'] }}</h2>
		
			<form id="bans2" method="post" action="{{ form_action }}">
				<div class="box">
				<input type="hidden" name="mode" value="{{ mode }}" />
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				{% if mode == 'edit' %}<input type="hidden" name="ban_id" value="{{ ban_id }}" />{% endif %}
				
						<p class="boxtitle">{{ lang_admin_bans['Ban advanced subhead'] }}</p>
						<div class="inbox">
						
								<div class="row">
									<div class="col label">{{ lang_admin_bans['Username label'] }}</div>
									<div class="col inputs">
										<input type="text" name="ban_user" maxlength="25" value="{{ ban_user }}" tabindex="1" />
										<p class="info">{{ lang_admin_bans['Username help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_bans['IP label'] }}</div>
									<div class="col inputs">
										<input type="text" name="ban_ip" maxlength="255" value="{{ ban_ip }}" tabindex="2" />
										<p class="info">{{ lang_admin_bans['IP help'] }}{% if ban_user != '' and user_id is not empty %}{{ lang_admin_bans['Ip help link']|format(ban_help, lang_admin_common['here']) }}{% endif %}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_bans['E-mail label'] }}</div>
									<div class="col inputs">
										<input type="text" name="ban_email" maxlength="80" value="{{ ban_email }}" tabindex="3" />
										<p class="info">{{ lang_admin_bans['E-mail help'] }}</p>
									</div>
								</div>
					
							<div class="row"><div class="col"><strong class="warntext">{{ lang_admin_bans['Ban IP range info'] }}</strong></div></div>
						</div>
					
				</div>
				
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_bans['Message expiry subhead'] }}</p>
						<div class="inbox">
							
								<div class="row">
									<div class="col label">{{ lang_admin_bans['Ban message label'] }}</div>
									<div class="col inputs">
										<input type="text" name="ban_message" maxlength="255" value="{{ ban_message }}" tabindex="4" />
										<p class="info">{{ lang_admin_bans['Ban message help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_bans['Expire date label'] }}</div>
									<div class="col inputs">
										<input type="text" name="ban_expire" maxlength="10" value="{{ ban_expire }}" tabindex="5" />
										<p class="info">{{ lang_admin_bans['Expire date help'] }}</p>
									</div>
								</div>
							
						</div>
					
				</div>
				<p class="submitend"><input type="submit" name="add_edit_ban" value="{{ lang_admin_common['Save'] }}" tabindex="6" /></p>
			</form>
		

</div>
</div>
</div><!-- .admin-console -->