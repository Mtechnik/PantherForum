<div class="content switchview list">

<div class="block pagetitle">
<h1>{{ lang_admin_common['Options'] }}</h1>
</div>
    <div class="btnview">
        <button class="btn list"><span class="text">List View</span></button>
        <button class="btn grid2"><span class="text">Grid 2 col View</span></button>
<button class="btn grid3"><span class="text">Grid 3 col View</span></button>
    </div>
		<h2>{{ lang_admin_options['Options head'] }}</h2>
	
	
	
	
		<div class="block">
			<form method="post" action="{{ form_action }}" enctype="multipart/form-data">
				<input type="submit" name="save" value="{{ lang_admin_common['Save changes'] }}" />
			
					<input type="hidden" name="form_sent" value="1" />
					<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
					
					
					<div class="box">
					
						<p class="boxtitle">{{ lang_admin_options['Essentials subhead'] }}</p>
						<div class="inbox">
							
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Board title label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[board_title]" maxlength="255" value="{{ panther_config['o_board_title'] }}" />
										<span class="info">{{ lang_admin_options['Board title help']|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Board desc label'] }}</div>
									<div class="col inputs">
										<textarea name="form[board_desc]" rows="3">{{ panther_config['o_board_desc'] }}</textarea>
										<span class="info">{{ lang_admin_options['Board desc help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Favicon label'] }}</div>
									<div class="col inputs">
										<input type="hidden" name="MAX_FILE_SIZE" value="{{ max_file_size }}" /><input type="file" name="favicon" />
										<span class="info">{{ lang_admin_options['Favicon help']|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Default avatar label'] }}</div>
									<div class="col inputs">
										<input type="hidden" name="MAX_FILE_SIZE" value="{{ max_file_size }}" /><input type="file" name="avatar" />
										<span class="info">{{ lang_admin_options['Default avatar help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Base URL label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[base_url]" maxlength="100" value="{{ panther_config['o_base_url'] }}" />
										<span class="info">{{ lang_admin_options['Base URL help']|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Timezone label'] }}</div>
									<div class="col inputs">
										<select name="form[default_timezone]">
											<option value="-12"{% if panther_config['o_default_timezone'] == -12 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC-12:00'] }}</option>
											<option value="-11"{% if panther_config['o_default_timezone'] == -11 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC-11:00'] }}</option>
											<option value="-10"{% if panther_config['o_default_timezone'] == -10 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC-10:00'] }}</option>
											<option value="-9.5"{% if panther_config['o_default_timezone'] == -9.5 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC-09:30'] }}</option>
											<option value="-9"{% if panther_config['o_default_timezone'] == -9 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC-09:00'] }}</option>
											<option value="-8.5"{% if panther_config['o_default_timezone'] == -8.5 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC-08:30'] }}</option>
											<option value="-8"{% if panther_config['o_default_timezone'] == -8 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC-08:00'] }}</option>
											<option value="-7"{% if panther_config['o_default_timezone'] == -7 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC-07:00'] }}</option>
											<option value="-6"{% if panther_config['o_default_timezone'] == -6 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC-06:00'] }}</option>
											<option value="-5"{% if panther_config['o_default_timezone'] == -5 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC-05:00'] }}</option>
											<option value="-4"{% if panther_config['o_default_timezone'] == -4 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC-04:00'] }}</option>
											<option value="-3.5"{% if panther_config['o_default_timezone'] == -3.5 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC-03:30'] }}</option>
											<option value="-3"{% if panther_config['o_default_timezone'] == -3 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC-03:00'] }}</option>
											<option value="-2"{% if panther_config['o_default_timezone'] == -2 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC-02:00'] }}</option>
											<option value="-1"{% if panther_config['o_default_timezone'] == -1 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC-01:00'] }}</option>
											<option value="0"{% if panther_config['o_default_timezone'] == 0 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC'] }}</option>
											<option value="1"{% if panther_config['o_default_timezone'] == 1 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+01:00'] }}</option>
											<option value="2"{% if panther_config['o_default_timezone'] == 2 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+02:00'] }}</option>
											<option value="3"{% if panther_config['o_default_timezone'] == 3 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+03:00'] }}</option>
											<option value="3.5"{% if panther_config['o_default_timezone'] == 3.5 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+03:30'] }}</option>
											<option value="4"{% if panther_config['o_default_timezone'] == 4 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+04:00'] }}</option>
											<option value="4.5"{% if panther_config['o_default_timezone'] == 4.5 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+04:30'] }}</option>
											<option value="5"{% if panther_config['o_default_timezone'] == 5 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+05:00'] }}</option>
											<option value="5.5"{% if panther_config['o_default_timezone'] == 5.5 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+05:30'] }}</option>
											<option value="5.75"{% if panther_config['o_default_timezone'] == 5.75 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+05:45'] }}</option>
											<option value="6"{% if panther_config['o_default_timezone'] == 6 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+06:00'] }}</option>
											<option value="6.5"{% if panther_config['o_default_timezone'] == 6.5 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+06:30'] }}</option>
											<option value="7"{% if panther_config['o_default_timezone'] == 7 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+07:00'] }}</option>
											<option value="8"{% if panther_config['o_default_timezone'] == 8 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+08:00'] }}</option>
											<option value="8.75"{% if panther_config['o_default_timezone'] == 8.75 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+08:45'] }}</option>
											<option value="9"{% if panther_config['o_default_timezone'] == 9 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+09:00'] }}</option>
											<option value="9.5"{% if panther_config['o_default_timezone'] == 9.5 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+09:30'] }}</option>
											<option value="10"{% if panther_config['o_default_timezone'] == 10 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+10:00'] }}</option>
											<option value="10.5"{% if panther_config['o_default_timezone'] == 10.5 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+10:30'] }}</option>
											<option value="11"{% if panther_config['o_default_timezone'] == 11 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+11:00'] }}</option>
											<option value="11.5"{% if panther_config['o_default_timezone'] == 11.5 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+11:30'] }}</option>
											<option value="12"{% if panther_config['o_default_timezone'] == 12 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+12:00'] }}</option>
											<option value="12.75"{% if panther_config['o_default_timezone'] == 12.75 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+12:45'] }}</option>
											<option value="13"{% if panther_config['o_default_timezone'] == 13 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+13:00'] }}</option>
											<option value="14"{% if panther_config['o_default_timezone'] == 14 %} selected="selected"{% endif %}>{{ lang_admin_options['UTC+14:00'] }}</option>
										</select>
										<span class="info">{{ lang_admin_options['Timezone help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['DST label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[default_dst]" value="1"{% if panther_config['o_default_dst'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[default_dst]" value="0"{% if panther_config['o_default_dst'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['DST help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['URL type label'] }}</div>
									<div class="col inputs">
										<select name="form[url_type]">
{% for type in types %}
<option value="{{ type['file'] }}"{% if type['file'] == panther_config['o_url_type'] %} selected="selected"{% endif %}>{{ type['title'] }}</option>
{% endfor %}
										</select>
										<span class="info">{{ lang_admin_options['URL type help']|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Language label'] }}</div>
									<div class="col inputs">
										<select name="form[default_lang]">
{% for language in languages %}
<option value="{{ language }}"{% if language == panther_config['o_default_lang'] %} selected="selected"{% endif %}>{{ language }}</option>
{% endfor %}
										</select>
										<span class="info">{{ lang_admin_options['Language help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Default style label'] }}</div>
									<div class="col inputs">
										<select name="form[default_style]">
{% for style in styles %}
<option value="{{ style }}"{% if style == panther_config['o_default_style'] %} selected="selected"{% endif %}>{{ style }}</option>
{% endfor %}
										</select>
										<span class="info">{{ lang_admin_options['Default style help'] }}</span>
									</div>
								</div>
{% if themes is not empty %}
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Default theme label'] }}</div>
									<div class="col inputs">
										<select name="form[theme]">
{% for theme in themes %}
<option value="{{ theme }}"{% if theme == panther_config['o_theme'] %} selected="selected"{% endif %}>{{ theme }}</option>
{% endfor %}
										</select>
										<span class="info">{{ lang_admin_options['Default theme help']|format(tasks_link)|raw }}</span>
									</div>
								</div>
{% endif %}
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Ban email label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[ban_email]" value="1"{% if panther_config['o_ban_email'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[ban_email]" value="0"{% if panther_config['o_ban_email'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Ban email help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Img path label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[image_path]" value="{{ panther_config['o_image_path'] }}" />
										<span class="info">{{ lang_admin_options['Img path help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Img directory label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[image_dir]" value="{{ panther_config['o_image_dir'] }}" />
										<span class="info">{{ lang_admin_options['Img directory help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['JS directory label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[js_dir]" value="{{ panther_config['o_js_dir'] }}" />
										<span class="info">{{ lang_admin_options['JS directory help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Style path label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[style_path]" value="{{ panther_config['o_style_path'] }}" />
										<span class="info">{{ lang_admin_options['Style path help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Style directory label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[style_dir]" value="{{ panther_config['o_style_dir'] }}" />
										<span class="info">{{ lang_admin_options['Style directory help'] }}</span>
									</div>
								</div>
							
						</div>
					
		          </div>
				  
				  
				  <div class="box">
					
						<p class="boxtitle">{{ lang_admin_options['Cookie subhead'] }}</p>
						<div class="inbox">
							
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Cookie name label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[cookie_name]" maxlength="25" value="{{ panther_config['o_cookie_name'] }}" />
										<span class="info">{{ lang_admin_options['Cookie name help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Cookie domain label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[cookie_domain]" maxlength="25" value="{{ panther_config['o_cookie_domain'] }}" />
										<span class="info">{{ lang_admin_options['Cookie domain help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Cookie seed label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[cookie_seed]" maxlength="25" value="{{ panther_config['o_cookie_seed'] }}" />
										<span class="info">{{ lang_admin_options['Cookie seed help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Cookie secure label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[cookie_secure]" value="1"{% if panther_config['o_cookie_secure'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[cookie_secure]" value="0"{% if panther_config['o_cookie_secure'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Cookie secure help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Cookie path label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[cookie_path]" maxlength="25" value="{{ panther_config['o_cookie_path'] }}" />
										<span class="info">{{ lang_admin_options['Cookie path help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Force ssl label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[force_ssl]" value="1"{% if panther_config['o_force_ssl'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[force_ssl]" value="0"{% if panther_config['o_force_ssl'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Force ssl help'] }}</span>
									</div>
								</div>
						
						</div>
					</div>
				
				<div class="box">
				
						<p class="boxtitle">{{ lang_admin_options['Timeouts subhead'] }}</p>
						<div class="inbox">
							
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Time format label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[time_format]" maxlength="25" value="{{ panther_config['o_time_format'] }}" />
										<span class="info">{{ lang_admin_options['Time format help']|format(time_format, lang_admin_options['PHP manual'])|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Date format label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[date_format]" maxlength="25" value="{{ panther_config['o_date_format'] }}" />
										<span class="info">{{ lang_admin_options['Date format help']|format(date_format, lang_admin_options['PHP manual'])|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Visit timeout label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[timeout_visit]" maxlength="5" value="{{ panther_config['o_timeout_visit'] }}" />
										<span class="info">{{ lang_admin_options['Visit timeout help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Online timeout label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[timeout_online]" maxlength="5" value="{{ panther_config['o_timeout_online'] }}" />
										<span class="info">{{ lang_admin_options['Online timeout help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Redirect time label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[redirect_delay]" maxlength="3" value="{{ panther_config['o_redirect_delay'] }}" />
										<span class="info">{{ lang_admin_options['Redirect time help'] }}</span>
									</div>
								</div>
					
						</div>
					</div>
				
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_options['Display subhead'] }}</p>
						<div class="inbox">
							
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Version number label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[show_version]" value="1"{% if panther_config['o_show_version'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[show_version]" value="0"{% if panther_config['o_show_version'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Version number help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Info in posts label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[show_user_info]" value="1"{% if panther_config['o_show_user_info'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[show_user_info]" value="0"{% if panther_config['o_show_user_info'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Info in posts help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Post count label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[show_post_count]" value="1"{% if panther_config['o_show_post_count'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[show_post_count]" value="0"{% if panther_config['o_show_post_count'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Post count help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Smilies label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[smilies]" value="1"{% if panther_config['o_smilies'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[smilies]" value="0"{% if panther_config['o_smilies'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Smilies help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Smilies path label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[smilies_path]" maxlength="50" value="{{ panther_config['o_smilies_path'] }}" />
										<span class="info">{{ lang_admin_options['Smilies path help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Smilies directory label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[smilies_dir]" maxlength="50" value="{{ panther_config['o_smilies_dir'] }}" />
										<span class="info">{{ lang_admin_options['Smilies directory help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Max width smilies label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[smilies_width]" maxlength="5" value="{{ panther_config['o_smilies_width'] }}" />
										<span class="info">{{ lang_admin_options['Max width smilies help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Max height smilies label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[smilies_height]" maxlength="5" value="{{ panther_config['o_smilies_height'] }}" />
										<span class="info">{{ lang_admin_options['Max height smilies help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Max size smilies label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[smilies_size]" maxlength="6" value="{{ panther_config['o_smilies_size'] }}" />
										<span class="info">{{ lang_admin_options['Max size smilies help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Smilies sigs label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[smilies_sig]" value="1"{% if panther_config['o_smilies_sig'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[smilies_sig]" value="0"{% if panther_config['o_smilies_sig'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Smilies sigs help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Clickable links label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[make_links]" value="1"{% if panther_config['o_make_links'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[make_links]" value="0"{% if panther_config['o_make_links'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Clickable links help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Topic review label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[topic_review]" maxlength="3" value="{{ panther_config['o_topic_review'] }}" />
										<span class="info">{{ lang_admin_options['Topic review help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Topics per page label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[disp_topics_default]" maxlength="2" value="{{ panther_config['o_disp_topics_default'] }}" />
										<span class="info">{{ lang_admin_options['Topics per page help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Posts per page label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[disp_posts_default]" maxlength="2" value="{{ panther_config['o_disp_posts_default'] }}" />
										<span class="info">{{ lang_admin_options['Posts per page help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Indent label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[indent_num_spaces]" maxlength="3" value="{{ panther_config['o_indent_num_spaces'] }}" />
										<span class="info">{{ lang_admin_options['Indent help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Quote depth label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[quote_depth]" maxlength="3" value="{{ panther_config['o_quote_depth'] }}" />
										<span class="info">{{ lang_admin_options['Quote depth help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['User tags label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[user_tags_max]" maxlength="2" value="{{ panther_config['o_user_tags_max'] }}" />
										<span class="info">{{ lang_admin_options['User tags help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Image group path label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[image_group_path]" maxlength="50" value="{{ panther_config['o_image_group_path'] }}" />
										<span class="info">{{ lang_admin_options['Image group path help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Image group dir label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[image_group_dir]" maxlength="50" value="{{ panther_config['o_image_group_dir'] }}" />
										<span class="info">{{ lang_admin_options['Image group dir help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Max width label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[image_group_width]" maxlength="5" value="{{ panther_config['o_image_group_width'] }}" />
										<span class="info">{{ lang_admin_options['Max width group help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Max height label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[image_group_height]" maxlength="5" value="{{ panther_config['o_image_group_height'] }}" />
										<span class="info">{{ lang_admin_options['Max height group help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Max size label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[image_group_size]" maxlength="6" value="{{ panther_config['o_image_group_size'] }}" />
										<span class="info">{{ lang_admin_options['Max size group help'] }}</span>
									</div>
								</div>
						
						</div>
					</div>
				
			<div class="box">
					
						<p class="boxtitle">{{ lang_admin_options['Features subhead'] }}</p>
						<div class="inbox">
					
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Quick post label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[quickpost]" value="1"{% if panther_config['o_quickpost'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[quickpost]" value="0"{% if panther_config['o_quickpost'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Quick post help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Users online label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[users_online]" value="1"{% if panther_config['o_users_online'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[users_online]" value="0"{% if panther_config['o_users_online'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Users online help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Popular topic label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[popular_topics]" maxlength="3" value="{{ panther_config['o_popular_topics'] }}" />
										<span class="info">{{ lang_admin_options['Popular topic help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['http authentication label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[http_authentication]" value="1"{% if panther_config['o_http_authentication'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[http_authentication]" value="0"{% if panther_config['o_http_authentication'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['http authentication help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Use editor label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[use_editor]" value="1"{% if panther_config['o_use_editor'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[use_editor]" value="0"{% if panther_config['o_use_editor'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Use editor help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label"><a name="censoring"></a>{{ lang_admin_options['Censor words label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[censoring]" value="1"{% if panther_config['o_censoring'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[censoring]" value="0"{% if panther_config['o_censoring'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Censor words help']|format(censoring_link, lang_admin_common['Censoring'])|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label"><a name="archiving"></a>{{ lang_admin_options['Archive label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[archive]" value="1"{% if panther_config['o_archiving'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[archive]" value="0"{% if panther_config['o_archiving'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Archive help']|format(archive_link)|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Warning label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[warnings]" value="1"{% if panther_config['o_warnings'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[warnings]" value="0"{% if panther_config['o_warnings'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Warning help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Warning custom label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[custom_warnings]" value="1"{% if panther_config['o_custom_warnings'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[custom_warnings]" value="0"{% if panther_config['o_custom_warnings'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Warning help']|format(archive_link) }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Warning status label'] }}</div>
									<div class="col inputs">
										<select name="form[warning_status]">
											<option value="0"{% if panther_config['o_warning_status'] == '0' %} selected="selected"{% endif %}>{{ lang_admin_options['All users'] }}</option>
											<option value="1"{% if panther_config['o_warning_status'] == '1' %} selected="selected"{% endif %}>{{ lang_admin_options['Moderators and warned'] }}</option>
											<option value="2"{% if panther_config['o_warning_status'] == '2' %} selected="selected"{% endif %}>{{ lang_admin_options['Moderators only'] }}</option>
										</select>
										<span class="info">{{ lang_admin_options['Warning status help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['delete full label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[delete_full]" value="1"{% if panther_config['o_delete_full'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[delete_full]" value="0"{% if panther_config['o_delete_full'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['delete full help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label"><a name="signatures"></a>{{ lang_admin_options['Signatures label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[signatures]" value="1"{% if panther_config['o_signatures'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[signatures]" value="0"{% if panther_config['o_signatures'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Signatures help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Attachments label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[attachments]" value="1"{% if panther_config['o_attachments'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[attachments]" value="0"{% if panther_config['o_attachments'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Attachments help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Attachments orphans label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[create_orphans]" value="1"{% if panther_config['o_create_orphans'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[create_orphans]" value="0"{% if panther_config['o_create_orphans'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Attachments orphans help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Attachment deny label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[always_deny]" value="{{ panther_config['o_always_deny'] }}" />
										<span class="info">{{ lang_admin_options['Attachment deny help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Attachment icons label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[attachment_icons]" value="1"{% if panther_config['o_attachment_icons'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[attachment_icons]" value="0"{% if panther_config['o_attachment_icons'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Attachment icons help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Attach basefolder label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="text" name="form[attachments_dir]" value="{{ panther_config['o_attachments_dir'] }}" tabindex="5" /></label>
										<span class="info">{{ lang_admin_options['Attach basefolder help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['File extensions label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[attachment_extensions]" value="{{ panther_config['o_attachment_extensions'] }}" tabindex="5" />{{ lang_admin_options['Extensions'] }}
										<input type="text" name="form[attachment_images]" value="{{ panther_config['o_attachment_images'] }}" tabindex="6" />{{ lang_admin_options['Icons'] }}
										<span class="info">{{ lang_admin_options['Icon options'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Attachment icon folder label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[attachment_icon_path]" value="{{ panther_config['o_attachment_icon_path'] }}" />
										<span class="info">{{ lang_admin_options['Attachment icon folder help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Attachment icon dir label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[attachment_icon_dir]" value="{{ panther_config['o_attachment_icon_dir'] }}" />
										<span class="info">{{ lang_admin_options['Attachment icon dir help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Max attachments label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[max_upload_size]" value="{{ panther_config['o_max_upload_size'] }}" />
										<span class="info">{{ lang_admin_options['Max attachments help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label"><a name="ranks"></a>{{ lang_admin_options['User ranks label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[ranks]" value="1"{% if panther_config['o_ranks'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[ranks]" value="0"{% if panther_config['o_ranks'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['User ranks help']|format(ranks_link, lang_admin_common['Ranks'])|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Polls label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[polls]" value="1"{% if panther_config['o_polls'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[polls]" value="0"{% if panther_config['o_polls'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Polls help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Polls max label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[max_poll_fields]" value="{{ panther_config['o_max_poll_fields'] }}" />
										<span class="info">{{ lang_admin_options['Polls max help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Reputation label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[reputation]" value="1"{% if panther_config['o_reputation'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[reputation]" value="0"{% if panther_config['o_reputation'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Reputation help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Rep type label'] }}</div>
									<div class="col inputs">
										<select name="form[rep_type]">
											<option value="1"{% if panther_config['o_rep_type'] == '1' %} selected="selected"{% endif %}>{{ lang_admin_options['Positive and negative'] }}</option>
											<option value="2"{% if panther_config['o_rep_type'] == '2' %} selected="selected"{% endif %}>{{ lang_admin_options['Positive only'] }}</option>
											<option value="3"{% if panther_config['o_rep_type'] == '3' %} selected="selected"{% endif %}>{{ lang_admin_options['Negative only'] }}</option>
										</select>
										<span class="info">{{ lang_admin_options['Rep type help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Rep abuse label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[rep_abuse]" maxlength="3" value="{{ panther_config['o_rep_abuse'] }}" />
										<span class="info">{{ lang_admin_options['Rep abuse help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['PM label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[private_messaging]" value="1"{% if panther_config['o_private_messaging'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[private_messaging]" value="0"{% if panther_config['o_private_messaging'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['PM help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['PM receiver label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[max_pm_receivers]" maxlength="2" value="{{ panther_config['o_max_pm_receivers'] }}" />
										<span class="info">{{ lang_admin_options['PM receiver help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['User has posted label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[show_dot]" value="1"{% if panther_config['o_show_dot'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[show_dot]" value="0"{% if panther_config['o_show_dot'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['User has posted help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Login queue label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[login_queue]" value="1"{% if panther_config['o_login_queue'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[login_queue]" value="0"{% if panther_config['o_login_queue'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Login queue help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Queue size label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[queue_size]" maxlength="5" value="{{ panther_config['o_queue_size'] }}" />
										<span class="info">{{ lang_admin_options['Queue size help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Max attempts label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[max_attempts]" maxlength="5" value="{{ panther_config['o_max_attempts'] }}" />
										<span class="info">{{ lang_admin_options['Max attempts help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Update type label'] }}</div>
									<div class="col inputs">
										<select name="form[update_type]">
											<option value="0"{% if panther_config['o_update_type'] == '0' %} selected="selected"{% endif %}>{{ lang_admin_options['Neither'] }}</option>
											<option value="1"{% if panther_config['o_update_type'] == '1' %} selected="selected"{% endif %}>{{ lang_admin_options['Install only'] }}</option>
											<option value="2"{% if panther_config['o_update_type'] == '2' %} selected="selected"{% endif %}>{{ lang_admin_options['Download only'] }}</option>
											<option value="3"{% if panther_config['o_update_type'] == '3' %} selected="selected"{% endif %}>{{ lang_admin_options['Download and install'] }}</option>
										</select>
										<span class="info">{{ lang_admin_options['Update type help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Debug mode label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[debug_mode]" value="1"{% if panther_config['o_debug_mode'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[debug_mode]" value="0"{% if panther_config['o_debug_mode'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Debug mode help']|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Show queries label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[show_queries]" value="1"{% if panther_config['o_show_queries'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[show_queries]" value="0"{% if panther_config['o_show_queries'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Show queries help']|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Topic views label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[topic_views]" value="1"{% if panther_config['o_topic_views'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[topic_views]" value="0"{% if panther_config['o_topic_views'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Topic views help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Quick jump label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[quickjump]" value="1"{% if panther_config['o_quickjump'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[quickjump]" value="0"{% if panther_config['o_quickjump'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Quick jump help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['GZip label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[gzip]" value="1"{% if panther_config['o_gzip'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[gzip]" value="0"{% if panther_config['o_gzip'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['GZip help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Search all label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[search_all_forums]" value="1"{% if panther_config['o_search_all_forums'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[search_all_forums]" value="0"{% if panther_config['o_search_all_forums'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Search all help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Task type label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[task_type]" value="1"{% if panther_config['o_task_type'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[task_type]" value="0"{% if panther_config['o_task_type'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Task type help']|format(tasks_link)|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['SFS api key'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[sfs_api]" maxlength="25" value="{{ panther_config['o_sfs_api'] }}" />
										<span class="info">{{ lang_admin_options['SFS api key help']|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Tinypng api'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[tinypng_api]" value="{{ panther_config['o_tinypng_api'] }}" />
										<span class="info">{{ lang_admin_options['Tinypng api help']|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Cloudflare api'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[cloudflare_api]" value="{{ panther_config['o_cloudflare_api'] }}" />
										<span class="info">{{ lang_admin_options['Cloudflare api help']|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Cloudflare email label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[cloudflare_email]" value="{{ panther_config['o_cloudflare_email'] }}" />
										<span class="info">{{ lang_admin_options['Cloudflare email help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Cloudflare domain label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[cloudflare_domain]" value="{{ panther_config['o_cloudflare_domain'] }}" />
										<span class="info">{{ lang_admin_options['Cloudflare domain help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Menu items label'] }}</div>
									<div class="col inputs">
										<textarea name="form[additional_navlinks]">{{ panther_config['o_additional_navlinks'] }}</textarea>
										<span class="info">{{ lang_admin_options['Menu items help']|raw }}</span>
									</div>
								</div>
	
						</div>
				   </div>
				   
<div class="box">
					
						<p class="boxtitle">{{ lang_admin_options['Feed subhead'] }}</p>
						<div class="inbox">
							
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Default feed label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[feed_type]" value="0"{% if panther_config['o_feed_type'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_options['None'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[feed_type]" value="1"{% if panther_config['o_feed_type'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_options['RSS'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[feed_type]" value="2"{% if panther_config['o_feed_type'] == '2' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_options['Atom'] }}</strong></label>
										<span class="info">{{lang_admin_options['Default feed help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Feed TTL label'] }}</div>
									<div class="col inputs">
										<select name="form[feed_ttl]">
											<option value="0"{% if panther_config['o_feed_type'] == '2' %} selected="selected"{% endif %}>{{ lang_admin_options['No cache'] }}</option>
{% for feed in feeds %}
<option value="{{ feed }}"{% if feed == panther_config['o_feed_ttl'] %} selected="selected"{% endif %}>{{ feed }}</option>
{% endfor %}
										</select>
										<span class="info">{{ lang_admin_options['Feed TTL help'] }}</span>
									</div>
								</div>
						
						</div>
					</div>
					
			<div class="box">
			
					
						<p class="boxtitle">{{ lang_admin_options['Reports subhead'] }}</p>
						<div class="inbox">
							
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Reporting method label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[report_method]" value="0"{% if panther_config['o_report_method'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_options['Internal'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[report_method]" value="1"{% if panther_config['o_report_method'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_options['By e-mail'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[report_method]" value="2"{% if panther_config['o_report_method'] == '2' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_options['Both'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Reporting method help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Mailing list label'] }}</div>
									<div class="col inputs">
										<textarea name="form[mailing_list]">{{ panther_config['o_mailing_list'] }}</textarea>
										<span class="info">{{ lang_admin_options['Mailing list help'] }}</span>
									</div>
								</div>
					
						</div>
					</div>
			
			<div class="box">
					
						<p class="boxtitle">{{ lang_admin_options['Avatars subhead'] }}</p>
						<div class="inbox">
							
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Use avatars label'] }}</div>
									<div class="col inputs">

							 <label class="conl"><input type="radio" name="form[avatars]" value="1"{% if panther_config['o_avatars'] == '1' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
<label class="conl"><input type="radio" name="form[avatars]" value="0"{% if panther_config['o_avatars'] == '0' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										
										<span class="info">{{ lang_admin_options['Use avatars help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Avatar upload label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[avatar_upload]" value="1"{% if panther_config['o_avatar_upload'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[avatar_upload]" value="0"{% if panther_config['o_avatar_upload'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Avatar upload help']|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Avatars path label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[avatars_path]" maxlength="50" value="{{ panther_config['o_avatars_path'] }}" />
										<span class="info">{{ lang_admin_options['Avatars path help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Upload directory label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[avatars_dir]" maxlength="50" value="{{ panther_config['o_avatars_dir'] }}" />
										<span class="info">{{ lang_admin_options['Upload directory help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Max width label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[avatars_width]" maxlength="5" value="{{ panther_config['o_avatars_width'] }}" />
										<span class="info">{{ lang_admin_options['Max width help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Max height label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[avatars_height]" maxlength="5" value="{{ panther_config['o_avatars_height'] }}" />
										<span class="info">{{ lang_admin_options['Max height help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Max size label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[avatars_size]" maxlength="6" value="{{ panther_config['o_avatars_size'] }}" />
										<span class="info">{{ lang_admin_options['Max size help'] }}</span>
									</div>
								</div>
				
						</div>
					</div>
			
			<div class="box">
					
						<p class="boxtitle">{{ lang_admin_options['E-mail subhead'] }}</p>
						<div class="inbox">
							
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['E-mail name label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[email_name]" maxlength="50" value="{{ panther_config['o_email_name'] }}" />
										<span class="info">{{ lang_admin_options['E-mail name help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Admin e-mail label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[admin_email]" maxlength="80" value="{{ panther_config['o_admin_email'] }}" />
										<span class="info">{{ lang_admin_options['Admin e-mail help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Webmaster e-mail label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[webmaster_email]" maxlength="80" value="{{ panther_config['o_webmaster_email'] }}" />
										<span class="info">{{ lang_admin_options['Webmaster e-mail help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Forum subscriptions label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[forum_subscriptions]" value="1"{% if panther_config['o_forum_subscriptions'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[forum_subscriptions]" value="0"{% if panther_config['o_forum_subscriptions'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Forum subscriptions help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Topic subscriptions label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[topic_subscriptions]" value="1"{% if panther_config['o_topic_subscriptions'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[topic_subscriptions]" value="0"{% if panther_config['o_topic_subscriptions'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Topic subscriptions help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['SMTP address label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[smtp_host]" maxlength="100" value="{{ panther_config['o_smtp_host'] }}" />
										<span class="info">{{ lang_admin_options['SMTP address help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['SMTP username label'] }}</div>
									<div class="col inputs">
										<input type="text" name="form[smtp_user]" maxlength="50" value="{{ panther_config['o_smtp_user'] }}" />
										<span class="info">{{ lang_admin_options['SMTP username help']|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['SMTP password label'] }}</div>
									<div class="col inputs">
										<label><input type="checkbox" name="form[smtp_change_pass]" value="1" /> {{ lang_admin_options['SMTP change password help'] }}</label>
										<input type="password" name="form[smtp_pass1]" maxlength="50" value="{{ smtp_pass }}" />
										<input type="password" name="form[smtp_pass2]" maxlength="50" value="{{ smtp_pass }}" />
										<span class="info">{{ lang_admin_options['SMTP password help']|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['SMTP SSL label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[smtp_ssl]" value="1"{% if panther_config['o_smtp_ssl'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[smtp_ssl]" value="0"{% if panther_config['o_smtp_ssl'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['SMTP SSL help'] }}</span>
									</div>
								</div>
						
						</div>
					</div>
			
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_options['Registration subhead'] }}</p>
						<div class="inbox">
					
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Allow new label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[regs_allow]" value="1"{% if panther_config['o_regs_allow'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[regs_allow]" value="0"{% if panther_config['o_regs_allow'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Allow new help'] }}</span>
									</div> 
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Verify label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[regs_verify]" value="1"{% if panther_config['o_regs_verify'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[regs_verify]" value="0"{% if panther_config['o_regs_verify'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Verify help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Report new label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[regs_report]" value="1"{% if panther_config['o_regs_report'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[regs_report]" value="0"{% if panther_config['o_regs_report'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Report new help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Use rules label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[rules]" value="1"{% if panther_config['o_rules'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[rules]" value="0"{% if panther_config['o_rules'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Use rules help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Rules label'] }}</div>
									<div class="col inputs">
										<textarea name="form[rules_message]">{{ panther_config['o_rules_message'] }}</textarea>
										<span class="info">{{ lang_admin_options['Rules help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['E-mail default label'] }}</div>
									<div class="col inputs">
										<span class="info">{{ lang_admin_options['E-mail default help'] }}</span>
										<label><input type="radio" name="form[default_email_setting]" id="form_default_email_setting_0" value="0"{% if panther_config['o_default_email_setting'] == '0' %} checked="checked"{% endif %} /> {{ lang_admin_options['Display e-mail label'] }}</label>
										<label><input type="radio" name="form[default_email_setting]" id="form_default_email_setting_1" value="1"{% if panther_config['o_default_email_setting'] == '1' %} checked="checked"{% endif %} /> {{ lang_admin_options['Hide allow form label'] }}</label>
										<label><input type="radio" name="form[default_email_setting]" id="form_default_email_setting_2" value="2"{% if panther_config['o_default_email_setting'] == '2' %} checked="checked"{% endif %} /> {{ lang_admin_options['Hide both label'] }}</label>
									</div>
								</div>
					
						</div>
					</div>
			
		<div class="box">
					
						<p class="boxtitle">{{ lang_admin_options['Announcement subhead'] }}</p>
						<div class="inbox">
						
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Display announcement label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[announcement]" value="1"{% if panther_config['o_announcement'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[announcement]" value="0"{% if panther_config['o_announcement'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Display announcement help'] }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Announcement message label'] }}</div>
									<div class="col inputs">
										<textarea name="form[announcement_message]">{{ panther_config['o_announcement_message'] }}</textarea>
										<span class="info">{{ lang_admin_options['Announcement message help'] }}</span>
									</div>
								</div>
							
						</div>
					</div>
			
			<div class="box">
					
						<p class="boxtitle">{{ lang_admin_options['Maintenance subhead'] }}</p>
						<div class="inbox">
						
								<div class="row tr">
									<div class="col label"><a name="maintenance"></a>{{ lang_admin_options['Maintenance mode label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="form[maintenance]" value="1"{% if panther_config['o_maintenance'] == '1' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="form[maintenance]" value="0"{% if panther_config['o_maintenance'] == '0' %} checked="checked"{% endif %} /> <strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="info">{{ lang_admin_options['Maintenance mode help']|raw }}</span>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_options['Maintenance message label'] }}</div>
									<div class="col inputs">
										<textarea name="form[maintenance_message]">{{ panther_config['o_maintenance_message'] }}</textarea>
										<span class="info">{{ lang_admin_options['Maintenance message help'] }}</span>
									</div>
								</div>
					
						</div>
					</div>
			
				<input type="submit" name="save" value="{{ lang_admin_common['Save changes'] }}" />
			</form>
		</div>

	
</div>
</div><!-- .admin-console -->