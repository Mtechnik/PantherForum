<div class="content">
	<div class="block">
		<h2>{{ lang_admin_forums['Edit forum head'] }}</h2>
		<div class="box">
			<form id="edit_forum" method="post" action="{{ form_action }}">
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<p class="submittop"><input type="submit" name="save" value="{{ lang_admin_common['Save changes'] }}" tabindex="6" /></p>
				<div class="inform">
					<fieldset>
						<legend>{{ lang_admin_forums['Edit details subhead'] }}</legend>
						<div class="infldset">
							<table class="aligntop">
								<tr>
									<th scope="row">{{ lang_admin_forums['Forum name label'] }}</th>
									<td><input type="text" name="forum_name" size="35" maxlength="80" value="{{ forum['forum_name'] }}" tabindex="1" /></td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_forums['Forum description label'] }}</th>
									<td><textarea name="forum_desc" rows="3" cols="50" tabindex="2">{{ forum['forum_desc'] }}</textarea></td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_forums['Category label'] }}</th>
									<td>
										<select name="cat_id" tabindex="3">
{% for category in categories %}
<option value="{{ category['id'] }}"{% if category['id'] == forum['cat_id'] %} selected="selected"{% endif %}>{{ category['name'] }}</option>
{% endfor %}
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_forums['Sort by label'] }}</th>
									<td>
										<select name="sort_by" tabindex="4">
											<option value="0"{% if forum['sort_by'] == '0' %} selected="selected"{% endif %}>{{ lang_admin_forums['Last post'] }}</option>
											<option value="1"{% if forum['sort_by'] == '1' %} selected="selected"{% endif %}>{{ lang_admin_forums['Topic start'] }}</option>
											<option value="2"{% if forum['sort_by'] == '2' %} selected="selected"{% endif %}>{{ lang_admin_forums['Subject'] }}</option>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_forums['Redirect label'] }}</th>
									<td>
									{% if forum['num_topics'] %}{{ lang_admin_forums['Redirect help'] }}{% else %}<input type="text" name="redirect_url" size="45" maxlength="100" value="{{ forum['redirect_url'] }}" tabindex="5" />{% endif %}
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_forums['Parent forum'] }}</th>
									<td>
										<select name="parent_forum">
											<option value="0">{{ lang_admin_forums['No parent forum'] }}</option>
{% for category in category_list %}
<optgroup label="{{ category['cat_name'] }}">
{% for list_forum in forums if category['id'] == list_forum['category_id'] %}
<option value="{{ list_forum['id'] }}"{% if list_forum['id'] == forum['parent_forum'] %} selected="selected"{% endif %}>{{ list_forum['name'] }}</option>
{% endfor %}
</optgroup>
{% endfor %}
											</optgroup>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_forums['protected forum label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="show_post_info" value="1"{% if forum['show_post_info'] == '1' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="show_post_info" value="0"{% if forum['show_post_info'] == '0' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_forums['protected forum help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_forums['Open forum'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="protected" value="1"{% if forum['protected'] == '1' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="protected" value="0"{% if forum['protected'] == '0' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_forums['Open forum help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_forums['Quickjump label'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="quickjump" value="1"{% if forum['quickjump'] == '1' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="quickjump" value="0"{% if forum['quickjump'] == '0' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_forums['Quickjump help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_forums['Allow reputation'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="use_reputation" value="1"{% if forum['use_reputation'] == '1' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="use_reputation" value="0"{% if forum['use_reputation'] == '0' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_forums['Allow reputation help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_forums['Increment post count'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="increment_posts" value="1"{% if forum['increment_posts'] == '1' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="increment_posts" value="0"{% if forum['increment_posts'] == '0' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_forums['Increment posts help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_forums['force approve'] }}</th>
									<td>
										<select name="moderator_approve">
											<option value="0"{% if forum['force_approve'] == '0' %} selected="selected"{% endif %}>{{ lang_admin_forums['no force approve'] }}</option>
											<option value="1"{% if forum['force_approve'] == '1' %} selected="selected"{% endif %}>{{ lang_admin_forums['force approve topics'] }}</option>
											<option value="2"{% if forum['force_approve'] == '2' %} selected="selected"{% endif %}>{{ lang_admin_forums['force approve posts'] }}</option>
											<option value="3"{% if forum['force_approve'] == '3' %} selected="selected"{% endif %}>{{ lang_admin_forums['force approve both'] }}</option>
										</select>
										<span class="clearb">{{ lang_admin_forums['force approve help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_forums['forum password label'] }}</th>
									<td>
										<label><input type="checkbox" name="change_forum_pass" value="1"{% if forum['redirect_url'] != '' %} disabled="disabled" checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_forums['forum password change help'] }}</strong></label><input type="password" name="forum_password1" size="25" maxlength="50" value="{{ password }}"{% if forum['redirect_url'] != '' %} disabled="disabled"{% endif %} />
										<span class="clearb">{{ lang_admin_forums['forum password help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_forums['forum password label 2'] }}</th>
									<td>
										<input type="password" name="forum_password2" size="25" maxlength="50" value="{{ password }}"{% if forum['redirect_url'] != '' %} disabled="disabled"{% endif %} />
										<span class="clearb">{{ lang_admin_forums['forum password help 2'] }}</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>{{ lang_admin_forums['Group permissions subhead'] }}</legend>
						<div class="infldset">
							<p>{{ lang_admin_forums['Group permissions info']|format(groups_link, lang_admin_common['User groups'])|raw }}</p>
							<table id="forumperms">
							<thead>
								<tr>
									<th class="atcl">&#160;</th>
									<th>{{ lang_admin_forums['Read forum label'] }}</th>
									<th>{{ lang_admin_forums['Post replies label'] }}</th>
									<th>{{ lang_admin_forums['Post topics label'] }}</th>
									<th>{{ lang_admin_forums['Post polls label'] }}</th>
									<th>{{ lang_admin_forums['Upload label'] }}</th>
									<th>{{ lang_admin_forums['Download label'] }}</th>
									<th>{{ lang_admin_forums['Delete label'] }}</th>
								</tr>
							</thead>
							<tbody>
{% for group in groups %}
								<tr>
									<th class="atcl">{{ group['perm']['g_title'] }}</th>
									<td{% if group['read_forum_def'] == false %} class="nodefault"{% endif %}>
										<input type="hidden" name="read_forum_old[{{ group['perm']['g_id'] }}]" value="{% if group['read_forum'] %}1{% else %}0{% endif %}" />
										<input type="checkbox" name="read_forum_new[{{ group['perm']['g_id'] }}]" value="1"{% if group['read_forum'] %} checked="checked"{% endif %}{% if forum['redirect_url'] != '' %} disabled="disabled"{% endif %} />
									</td>
									<td{% if group['post_replies_def'] == false and forum['redirect_url'] == '' %} class="nodefault"{% endif %}>
										<input type="hidden" name="post_replies_old[{{ group['perm']['g_id'] }}]" value="{% if group['post_replies'] %}1{% else %}0{% endif %}" />
										<input type="checkbox" name="post_replies_new[{{ group['perm']['g_id'] }}]" value="1"{% if group['post_replies'] %} checked="checked"{% endif %}{% if forum['redirect_url'] != '' %} disabled="disabled"{% endif %} />
									</td>
									<td{% if group['post_topics_def'] == false and forum['redirect_url'] == '' %} class="nodefault"{% endif %}>
										<input type="hidden" name="post_topics_old[{{ group['perm']['g_id'] }}]" value="{% if group['post_topics'] %}1{% else %}0{% endif %}" />
										<input type="checkbox" name="post_topics_new[{{ group['perm']['g_id'] }}]" value="1"{% if group['post_topics'] %} checked="checked"{% endif %}{% if forum['redirect_url'] != '' %} disabled="disabled"{% endif %} />
									</td>
									<td{% if group['post_polls_def'] == false and forum['redirect_url'] == '' %} class="nodefault"{% endif %}>
										<input type="hidden" name="post_polls_old[{{ group['perm']['g_id'] }}]" value="{% if group['post_polls'] %}1{% else %}0{% endif %}" />
										<input type="checkbox" name="post_polls_new[{{ group['perm']['g_id'] }}]" value="1"{% if group['post_polls'] %} checked="checked"{% endif %}{% if forum['redirect_url'] != '' %} disabled="disabled"{% endif %} />
									</td>
									<td{% if group['upload_def'] == false and forum['redirect_url'] == '' %} class="nodefault"{% endif %}>
										<input type="hidden" name="upload_old[{{ group['perm']['g_id'] }}]" value="{% if group['upload'] %}1{% else %}0{% endif %}" />
										<input type="checkbox" name="upload_new[{{ group['perm']['g_id'] }}]" value="1"{% if group['upload'] %} checked="checked"{% endif %}{% if forum['redirect_url'] != '' %} disabled="disabled"{% endif %} />
									</td>
									<td{% if group['download_def'] == false and forum['redirect_url'] == '' %} class="nodefault"{% endif %}>
										<input type="hidden" name="download_old[{{ group['perm']['g_id'] }}]" value="{% if group['download'] %}1{% else %}0{% endif %}" />
										<input type="checkbox" name="download_new[{{ group['perm']['g_id'] }}]" value="1"{% if group['download'] %} checked="checked"{% endif %}{% if forum['redirect_url'] != '' %} disabled="disabled"{% endif %} />
									</td>
									<td{% if group['delete_def'] == false and forum['redirect_url'] == '' %} class="nodefault"{% endif %}>
										<input type="hidden" name="delete_old[{{ group['perm']['g_id'] }}]" value="{% if delete %}1{% else %}0{% endif %}" />
										<input type="checkbox" name="delete_new[{{ group['perm']['g_id'] }}]" value="1"{% if group['delete'] %} checked="checked"{% endif %}{% if forum['redirect_url'] != '' %} disabled="disabled"{% endif %} />
									</td>
								</tr>
{% endfor %}
							</tbody>
							</table>
							<div class="fsetsubmit"><input type="submit" name="revert_perms" value="{{ lang_admin_forums['Revert to default'] }}" /></div>
						</div>
					</fieldset>
				</div>
				<p class="submitend"><input type="submit" name="save" value="{{ lang_admin_common['Save changes'] }}" /></p>
			</form>
		</div>
	</div>
</div>
</div><!-- .admin-console -->