<div class="content">
<div class="block">
		<h2>{{ lang_admin_users['Ban users'] }}</h2>
		
			<form id="bans2" name="confirm_ban_users" method="post" action="{{ form_action }}">
				<input type="hidden" name="users" value="{{ user_ids|join(', ') }}" />
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<div class="inform">
					<fieldset>
						<legend>{{ lang_admin_users['Message expiry subhead'] }}</legend>
						<div class="infldset">
							<table class="aligntop">
								<tr>
									<th scope="row">{{ lang_admin_users['Ban message label'] }}</th>
									<td>
										<input type="text" name="ban_message" size="50" maxlength="255" tabindex="1" />
										<span>{{ lang_admin_users['Ban message help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_users['Expire date label'] }}</th>
									<td>
										<input type="text" name="ban_expire" size="17" maxlength="10" tabindex="2" />
										<span>{{ lang_admin_users['Expire date help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_users['Ban IP label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="ban_the_ip" tabindex="3" value="1" checked="checked" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="ban_the_ip" tabindex="4" value="0" checked="checked" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_users['Ban IP help'] }}</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend"><input type="submit" name="ban_users_comply" value="{{ lang_admin_common['Save'] }}" tabindex="3" /></p>
			</form>
		
	
</div>

</div>
</div><!-- .admin-console -->