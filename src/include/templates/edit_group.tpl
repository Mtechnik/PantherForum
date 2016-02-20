<div class="content">
	<div class="block">
	
		<h2>{{ lang_admin_groups['Group settings head'] }}</h2>
		<div class="box">
			<form id="groups2" method="post" action="{{ form_action }}" onsubmit="return process_form(this)">
				<p class="submittop"><input type="submit" name="add_edit_group" value="{{ lang_admin_common['Save'] }}" /></p>
				<div class="inform">
					<input type="hidden" name="mode" value="{{ mode }}" />
					<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
					{% if mode == 'edit' %}<input type="hidden" name="group_id" value="{{ group_id }}" />{% else %}<input type="hidden" name="base_group" value="{{ group_id }}" />{% endif %}
					<fieldset>
						<legend>{{ lang_admin_groups['Group settings subhead'] }}</legend>
						<div class="infldset">
							<p>{{ lang_admin_groups['Group settings info'] }}</p>
							<table class="aligntop">
								<tr>
									<th scope="row">{{ lang_admin_groups['Group title label'] }}</th>
									<td>
										<input type="text" name="req_title" size="25" maxlength="50" value="{{ group['g_title'] }}" tabindex="1" />
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['User title label'] }}</th>
									<td>
										<input type="text" name="user_title" size="25" maxlength="50" value="{{ group['g_user_title'] }}" tabindex="2" />
										<span>{{ lang_admin_groups['User title help']|format(lang) }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Group colour'] }}</th>
									<td>
										<input type="text" name="group_colour" size="7" maxlength="7" value="{{ group['g_colour'] }}" tabindex="25" />
										<span>{{ lang_admin_groups['Group colour help'] }}</span>
									</td>
								</tr>
{% if is_not_admin_group %}
{% if is_not_guest_group %}
								<tr>
									<th scope="row">{{ lang_admin_groups['Promote users label'] }}</th>
									<td>
										<select name="promote_next_group" tabindex="3">
											<option value="0">{{ lang_admin_groups['Disable promotion'] }}</option>
{% for option in group_options %}
<option value="{{ option['id'] }}"{% if option['id'] == group['g_promote_next_group'] %} selected="selected"{% endif %}>{{ option['title'] }}</option>
{% endfor %}
										</select>
										<input type="text" name="promote_min_posts" size="5" maxlength="10" value="{{ group['g_promote_min_posts'] }}" tabindex="4" />
										<span>{{ lang_admin_groups['Promote users help']|format(lang_admin_groups['Disable promotion'])|raw }}</span>
									</td>
								</tr>
{% if mode != 'edit' or panther_config['o_default_user_group'] != group['g_id'] %}
								<tr>
									<th scope="row">{{ lang_admin_groups['Mod privileges label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="moderator" value="1"{% if group['g_moderator'] == '1' %} checked="checked"{% endif %} tabindex="5" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="moderator" value="0"{% if group['g_moderator'] == '0' %} checked="checked"{% endif %} tabindex="6" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Mod privileges help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Mod CP label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="mod_cp" value="1"{% if group['g_mod_cp'] == '1' %} checked="checked"{% endif %} tabindex="15" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="mod_cp" value="0"{% if group['g_mod_cp'] == '0' %} checked="checked"{% endif %} tabindex="16" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Mod CP help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Admin label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="admin" value="1"{% if group['g_admin'] == '1' %} checked="checked"{% endif %} tabindex="15" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="admin" value="0"{% if group['g_admin'] == '0' %} checked="checked"{% endif %} tabindex="16" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Admin help']|raw }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Global mod label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="global_moderator" value="1"{% if group['g_global_moderator'] == '1' %} checked="checked"{% endif %} tabindex="5" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="global_moderator" value="0"{% if group['g_global_moderator'] == '0' %} checked="checked"{% endif %} tabindex="6" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Global mod help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Edit profile label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="mod_edit_users" value="1"{% if group['g_mod_edit_users'] == '1' %} checked="checked"{% endif %} tabindex="7" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="mod_edit_users" value="0"{% if group['g_mod_edit_users'] == '0' %} checked="checked"{% endif %} tabindex="8" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Edit profile help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Rename users label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="mod_rename_users" value="1"{% if group['g_mod_rename_users'] == '1' %} checked="checked"{% endif %} tabindex="9" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="mod_rename_users" value="0"{% if group['g_mod_rename_users'] == '0' %} checked="checked"{% endif %} tabindex="10" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Rename users help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Change passwords label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="mod_change_passwords" value="1"{% if group['g_mod_change_passwords'] == '1' %} checked="checked"{% endif %} tabindex="11" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="mod_change_passwords" value="0"{% if group['g_mod_change_passwords'] == '0' %} checked="checked"{% endif %} tabindex="12" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Change passwords help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Mod promote users label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="mod_promote_users" value="1"{% if group['g_mod_promote_users'] == '1' %} checked="checked"{% endif %} tabindex="13" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="mod_promote_users" value="0"{% if group['g_mod_promote_users'] == '0' %} checked="checked"{% endif %} tabindex="14" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Mod promote users help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Report posts label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="mod_sfs_report" value="1"{% if group['g_mod_sfs_report'] == '1' %} checked="checked"{% endif %} tabindex="15" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="mod_sfs_report" value="0"{% if group['g_mod_sfs_report'] == '0' %} checked="checked"{% endif %} tabindex="16" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Report posts help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Warn users label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="mod_warn_users" value="1"{% if group['g_mod_warn_users'] == '1' %} checked="checked"{% endif %} tabindex="15" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="mod_warn_users" value="0"{% if group['g_mod_warn_users'] == '0' %} checked="checked"{% endif %} tabindex="16" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Warn users help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Ban users label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="mod_ban_users" value="1"{% if group['g_mod_ban_users'] == '1' %} checked="checked"{% endif %} tabindex="15" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="mod_ban_users" value="0"{% if group['g_mod_ban_users'] == '0' %} checked="checked"{% endif %} tabindex="16" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Ban users help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Edit admin posts label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="mod_edit_admin_posts" value="1"{% if group['g_mod_edit_admin_posts'] == '1' %} checked="checked"{% endif %} tabindex="15" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="mod_edit_admin_posts" value="0"{% if group['g_mod_edit_admin_posts'] == '0' %} checked="checked"{% endif %} tabindex="16" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Edit admin posts help'] }}</span>
									</td>
								</tr>
{% endif %}
{% endif %}
								<tr>
									<th scope="row">{{ lang_admin_groups['Read board label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="read_board" value="1"{% if group['g_read_board'] == '1' %} checked="checked"{% endif %} tabindex="17" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="read_board" value="0"{% if group['g_read_board'] == '0' %} checked="checked"{% endif %} tabindex="18" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Read board help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['View user info label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="view_users" value="1"{% if group['g_view_users'] == '1' %} checked="checked"{% endif %} tabindex="19" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="view_users" value="0"{% if group['g_view_users'] == '0' %} checked="checked"{% endif %} tabindex="20" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['View user info help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Post replies label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="post_replies" value="1"{% if group['g_post_replies'] == '1' %} checked="checked"{% endif %} tabindex="21" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="post_replies" value="0"{% if group['g_post_replies'] == '0' %} checked="checked"{% endif %} tabindex="22" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Post replies help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Robot verification label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="robot_test" value="1"{% if group['g_robot_test'] == '1' %} checked="checked"{% endif %} tabindex="21" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="robot_test" value="0"{% if group['g_robot_test'] == '0' %} checked="checked"{% endif %} tabindex="22" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Robot verification help']|format(robots_link)|raw }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Post polls label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="post_polls" value="1"{% if group['g_post_polls'] == '1' %} checked="checked"{% endif %} tabindex="21" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="post_polls" value="0"{% if group['g_post_polls'] == '0' %} checked="checked"{% endif %} tabindex="22" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Post polls help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Post moderation label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="moderate_posts" value="1"{% if group['g_moderate_posts'] == '1' %} checked="checked"{% endif %} tabindex="19" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="moderate_posts" value="0"{% if group['g_moderate_posts'] == '0' %} checked="checked"{% endif %} tabindex="20" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Post moderation help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Post topics label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="post_topics" value="1"{% if group['g_post_topics'] == '1' %} checked="checked"{% endif %} tabindex="23" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="post_topics" value="0"{% if group['g_post_topics'] == '0' %} checked="checked"{% endif %} tabindex="24" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Post topics help'] }}</span>
									</td>
								</tr>
{% if is_not_guest_group %}
								<tr>
									<th scope="row">{{ lang_admin_groups['Add attachments label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="attach_files" value="1"{% if group['g_attach_files'] == '1' %} checked="checked"{% endif %} tabindex="25" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="attach_files" value="0"{% if group['g_attach_files'] == '0' %} checked="checked"{% endif %} tabindex="26" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Add attachments help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Max attachments label'] }}</th>
									<td>
										<input type="text" name="max_attachments" size="5" value="{{ group['g_max_attachments'] }}" tabindex="42" />
										<span class="clearb">{{ lang_admin_groups['Max attachments help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Max size label'] }}</th>
									<td>
										<input type="text" name="max_size" size="5" value="{{ group['g_max_size'] }}" tabindex="42" />
										<span class="clearb">{{ lang_admin_groups['Max size help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Edit posts label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="edit_posts" value="1"{% if group['g_edit_posts'] == '1' %} checked="checked"{% endif %} tabindex="25" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="edit_posts" value="0"{% if group['g_edit_posts'] == '0' %} checked="checked"{% endif %} tabindex="26" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Edit posts help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Edit post subject label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="edit_subject" value="1"{% if group['g_edit_subject'] == '1' %} checked="checked"{% endif %} tabindex="25" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="edit_subject" value="0"{% if group['g_edit_subject'] == '0' %} checked="checked"{% endif %} tabindex="26" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Edit subject help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Delete posts label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="delete_posts" value="1"{% if group['g_delete_posts'] == '1' %} checked="checked"{% endif %} tabindex="27" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="delete_posts" value="0"{% if group['g_delete_posts'] == '0' %} checked="checked"{% endif %} tabindex="28" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Delete posts help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Deledit flood label'] }}</th>
									<td>
										<input type="text" name="deledit_interval" size="5" value="{{ group['g_deledit_interval'] }}" tabindex="39" />
										<span>{{ lang_admin_groups['Deledit flood help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Delete topics label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="delete_topics" value="1"{% if group['g_delete_topics'] == '1' %} checked="checked"{% endif %} tabindex="29" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="delete_topics" value="0"{% if group['g_delete_topics'] == '0' %} checked="checked"{% endif %} tabindex="30" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Delete topics help'] }}</span>
									</td>
								</tr>
{% endif %}
								<tr>
									<th scope="row">{{ lang_admin_groups['Post links label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="post_links" value="1"{% if group['g_post_links'] == '1' %} checked="checked"{% endif %} tabindex="31" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="post_links" value="0"{% if group['g_post_links'] == '0' %} checked="checked"{% endif %} tabindex="32" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Post links help'] }}</span>
									</td>
								</tr>
{% if is_not_guest_group %}
								<tr>
									<th scope="row">{{ lang_admin_groups['Set own title label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="set_title" value="1"{% if group['g_set_title'] == '1' %} checked="checked"{% endif %} tabindex="33" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="set_title" value="0"{% if group['g_set_title'] == '0' %} checked="checked"{% endif %} tabindex="34" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Set own title help'] }}</span>
									</td>
								</tr>
{% endif %}
								<tr>
									<th scope="row">{{ lang_admin_groups['User search label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="search" value="1"{% if group['g_search'] == '1' %} checked="checked"{% endif %} tabindex="35" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="search" value="0"{% if group['g_search'] == '0' %} checked="checked"{% endif %} tabindex="36" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['User search help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['User list search label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="search_users" value="1"{% if group['g_search_users'] == '1' %} checked="checked"{% endif %} tabindex="37" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="search_users" value="0"{% if group['g_search_users'] == '0' %} checked="checked"{% endif %} tabindex="38" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['User list search help'] }}</span>
									</td>
								</tr>
{% if is_not_guest_group %}
								<tr>
									<th scope="row">{{ lang_admin_groups['Send e-mails label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="send_email" value="1"{% if group['g_send_email'] == '1' %} checked="checked"{% endif %} tabindex="39" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="send_email" value="0"{% if group['g_send_email'] == '0' %} checked="checked"{% endif %} tabindex="40" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Send e-mails help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['allow group reputation'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="rep_enabled" value="1"{% if group['g_rep_enabled'] == '1' %} checked="checked"{% endif %} tabindex="37" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="rep_enabled" value="0"{% if group['g_rep_enabled'] == '0' %} checked="checked"{% endif %} tabindex="38" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['allow reputation privileges'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['group max positive'] }}</th>
									<td>
										<input type="text" name="g_rep_plus" size="5" maxlength="4" value="{{ group['g_rep_plus'] }}" tabindex="42" />
										<span class="clearb">{{ lang_admin_groups['group max positive legend'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['group max negative'] }}</th>
									<td>
										<input type="text" name="g_rep_minus" size="5" maxlength="4" value="{{ group['g_rep_minus'] }}" tabindex="42" />
										<span class="clearb">{{ lang_admin_groups['group max negative legend'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['group interval'] }}</th>
									<td>
										<input type="text" name="g_rep_interval" size="5" maxlength="4" value="{{ group['g_rep_interval'] }}" tabindex="42" />
										<span class="clearb">{{ lang_admin_groups['group interval legend'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Use pm label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="use_pm" value="1"{% if group['g_use_pm'] == '1' %} checked="checked"{% endif %} tabindex="39" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="use_pm" value="0"{% if group['g_use_pm'] == '0' %} checked="checked"{% endif %} tabindex="40" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_groups['Use pm help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['PM limit label'] }}</th>
									<td>
										<input type="text" name="pm_limit" size="5" maxlength="4" value="{{ group['g_pm_limit'] }}" tabindex="42" />

										<span class="clearb">{{ lang_admin_groups['PM limit help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['PM folder limit label'] }}</th>
									<td>
										<input type="text" name="pm_folder_limit" size="5" maxlength="4" value="{{ group['g_pm_folder_limit'] }}" tabindex="42" />

										<span class="clearb">{{ lang_admin_groups['PM folder limit help'] }}</span>
									</td>
								</tr>
{% endif %}
								<tr>
									<th scope="row">{{ lang_admin_groups['Post flood label'] }}</th>
									<td>
										<input type="text" name="post_flood" size="5" maxlength="4" value="{{ group['g_post_flood'] }}" tabindex="41" />
										<span>{{ lang_admin_groups['Post flood help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Search flood label'] }}</th>
									<td>
										<input type="text" name="search_flood" size="5" maxlength="4" value="{{ group['g_search_flood'] }}" tabindex="42" />
										<span>{{ lang_admin_groups['Search flood help'] }}</span>
									</td>
								</tr>
{% if is_not_guest_group %}
								<tr>
									<th scope="row">{{ lang_admin_groups['E-mail flood label'] }}</th>
									<td>
										<input type="text" name="email_flood" size="5" maxlength="4" value="{{ group['g_email_flood'] }}" tabindex="43" />
										<span>{{ lang_admin_groups['E-mail flood help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_groups['Report flood label'] }}</th>
									<td>
										<input type="text" name="report_flood" size="5" maxlength="4" value="{{ group['g_report_flood'] }}" tabindex="44" />
										<span>{{ lang_admin_groups['Report flood help'] }}</span>
									</td>
								</tr>
{% endif %}
								{% endif %}
							</table>
							{% if group['g_moderator'] == '1' %}<p class="warntext">{{ lang_admin_groups['Moderator info'] }}</p>{% endif %}
						</div>
					</fieldset>
				</div>
{% if mode == 'edit' %}
				<div class="inform">
					<fieldset id="profileavatar">
						<legend>{{ lang_admin_groups['Image legend'] }}</legend>
						<div class="infldset">
						{% if img_size is not empty %}<p><img src="{{ image_dir }}{{ group_id }}.{{ group['g_image'] }}" {{ img_size[3] }} alt="{{ group['g_user_title'] }}" /></p>{% endif %}
							<p>{{ lang_admin_groups['Image info'] }}</p>
							<p class="clearb"><a href="{{ upload_link }}">{% if img_size is not empty %}{{ lang_admin_groups['Change image'] }}{% else %}{{ lang_admin_groups['Upload image'] }}{% endif %}</a>{% if img_size is not empty %}&nbsp;&nbsp;&nbsp;<a href="{{ delete_link }}">{{ lang_admin_groups['Delete image'] }}</a>{% endif %}</p>
						</div>
					</fieldset>
				</div>
{% endif %}
				<p class="submitend"><input type="submit" name="add_edit_group" value="{{ lang_admin_common['Save'] }}" tabindex="45" /></p>
			</form>
		</div>
	</div>
</div>
</div><!-- .admin-console -->