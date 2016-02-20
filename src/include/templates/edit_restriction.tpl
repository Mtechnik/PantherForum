	<div class="blockform">
		<h2><span>{{ lang_admin_restrictions['restrictions head'] }}</span></h2>
		<div class="box">
			<form id="restrictions2" method="post" action="{{ form_action }}">
				<input type="hidden" name="form_sent" value="1" />
				<p class="submittop"><input tabindex="1" type="submit" name="submit" value="{{ lang_common['Submit'] }}" /></p>
				<div class="inform">
					<input type="hidden" name="admin_id" value="{{ user }}" />
					<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
					<fieldset>
						<legend>{{ lang_admin_restrictions['restrictions for user x']|format(username) }}</legend>
						<div class="infldset">
							<p>{{ lang_admin_restrictions['admin restrictions'] }}</p>
							<table class="aligntop">
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board config'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_config" value="1"{% if admin['admin_options'] == '1' %} checked="checked"{% endif %} tabindex="1" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_config" value="0"{% if admin['admin_options'] == '0' %} checked="checked"{% endif %} tabindex="2" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change config label'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board archive'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_archive" value="1"{% if admin['admin_archive'] == '1' %} checked="checked"{% endif %} tabindex="3" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_archive" value="0"{% if admin['admin_archive'] == '0' %} checked="checked"{% endif %} tabindex="4" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change archive label'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board perms'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_perms" value="1"{% if admin['admin_permissions'] == '1' %} checked="checked"{% endif %} tabindex="5" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_perms" value="0"{% if admin['admin_permissions'] == '0' %} checked="checked"{% endif %} tabindex="6" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change perms label'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board cats'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_cats" value="1"{% if admin['admin_categories'] == '1' %} checked="checked"{% endif %} tabindex="7" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_cats" value="0"{% if admin['admin_categories'] == '0' %} checked="checked"{% endif %} tabindex="8" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change cats label'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board forums'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_forums" value="1"{% if admin['admin_forums'] == '1' %} checked="checked"{% endif %} tabindex="9" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_forums" value="0"{% if admin['admin_forums'] == '0' %} checked="checked"{% endif %} tabindex="10" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change forums label'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board groups'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_groups" value="1"{% if admin['admin_groups'] == '1' %} checked="checked"{% endif %} tabindex="11" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_groups" value="0"{% if admin['admin_groups'] == '0' %} checked="checked"{% endif %} tabindex="12" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change groups label'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board censoring'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_censoring" value="1"{% if admin['admin_censoring'] == '1' %} checked="checked"{% endif %} tabindex="13" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_censoring" value="0"{% if admin['admin_censoring'] == '0' %} checked="checked"{% endif %} tabindex="14" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change censoring label'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board ranks'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_ranks" value="1"{% if admin['admin_ranks'] == '1' %} checked="checked"{% endif %} tabindex="15" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_ranks" value="0"{% if admin['admin_ranks'] == '0' %} checked="checked"{% endif %} tabindex="16" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change ranks label'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board robots'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_robots" value="1"{% if admin['admin_robots'] == '1' %} checked="checked"{% endif %} tabindex="17" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_robots" value="0"{% if admin['admin_robots'] == '0' %} checked="checked"{% endif %} tabindex="18" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change robots label'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board smilies'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_smilies" value="1"{% if admin['admin_smilies'] == '1' %} checked="checked"{% endif %} tabindex="19" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_smilies" value="0"{% if admin['admin_smilies'] == '0' %} checked="checked"{% endif %} tabindex="20" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change smilies label'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board warnings'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_warnings" value="1"{% if admin['admin_warnings'] == '1' %} checked="checked"{% endif %} tabindex="21" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_warnings" value="0"{% if admin['admin_warnings'] == '0' %} checked="checked"{% endif %} tabindex="22" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change warnings label'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board moderate'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_moderate" value="1"{% if admin['admin_moderate'] == '1' %} checked="checked"{% endif %} tabindex="23" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_moderate" value="0"{% if admin['admin_moderate'] == '0' %} checked="checked"{% endif %} tabindex="24" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change moderate label'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board attachments'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_attachments" value="1"{% if admin['admin_attachments'] == '1' %} checked="checked"{% endif %} tabindex="25" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_attachments" value="0"{% if admin['admin_attachments'] == '0' %} checked="checked"{% endif %} tabindex="26" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change attachments label'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board restrictions'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_restrictions" value="1"{% if admin['admin_restrictions'] == '1' %} checked="checked"{% endif %} tabindex="27" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_restrictions" value="0"{% if admin['admin_restrictions'] == '0' %} checked="checked"{% endif %} tabindex="28" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change restrictions label']|raw }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board tasks'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_tasks" value="1"{% if admin['admin_tasks'] == '1' %} checked="checked"{% endif %} tabindex="29" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_tasks" value="0"{% if admin['admin_tasks'] == '0' %} checked="checked"{% endif %} tabindex="30" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change tasks label']|raw }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board addons'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_addons" value="1"{% if admin['admin_addons'] == '1' %} checked="checked"{% endif %} tabindex="31" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_addons" value="0"{% if admin['admin_addons'] == '0' %} checked="checked"{% endif %} tabindex="32" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change addons label']|raw }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board maintenance'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_maintenance" value="1"{% if admin['admin_maintenance'] == '1' %} checked="checked"{% endif %} tabindex="33" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_maintenance" value="0"{% if admin['admin_maintenance'] == '0' %} checked="checked"{% endif %} tabindex="34" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change maintenance label'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board updates'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_updates" value="1"{% if admin['admin_updates'] == '1' %} checked="checked"{% endif %} tabindex="35" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_updates" value="0"{% if admin['admin_updates'] == '0' %} checked="checked"{% endif %} tabindex="36" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['install updates label'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board plugins'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_plugins" value="1"{% if admin['admin_plugins'] == '1' %} checked="checked"{% endif %} tabindex="37" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_plugins" value="0"{% if admin['admin_plugins'] == '0' %} checked="checked"{% endif %} tabindex="38" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change plugins label'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_restrictions['board users'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="board_users" value="1"{% if admin['admin_users'] == '1' %} checked="checked"{% endif %} tabindex="39" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="board_users" value="0"{% if admin['admin_users'] == '0' %} checked="checked"{% endif %} tabindex="40" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_restrictions['change users label'] }}</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend"><input type="submit" name="submit" value="{{ lang_common['Submit'] }}" tabindex="41" /></p>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
</div>