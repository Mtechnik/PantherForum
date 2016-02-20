<div class="main">

<div class="block bprofile">
		<h2 class="blocktitle">{{ user['username'] }} - {{ lang_profile['Section essentials'] }}</h2>
	
			<form id="profile1" method="post" action="{{ form_action }}" onsubmit="return process_form(this)">
	
					<div class="box">
						<p class="boxtitle">{{ lang_profile['Username and pass legend'] }}</p>
						<div class="row">
							<input type="hidden" name="form_sent" value="1" />
							<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
							{% if panther_user['is_admmod'] %}
							{% if panther_user['is_admin'] or panther_user['g_mod_rename_users'] == '1' %}
							<label class="required">{{ lang_common['Username'] }} <span class="required">{{ lang_common['Required'] }}</span><input type="text" name="req_username" value="{{ user['username'] }}" maxlength="25" /></label>
							{% else %}
							<p>{{ lang_profile['Username info']|format(user['username']) }}</p>
							{% endif %}
							{% else %}
							<p>{{ lang_common['Username'] }}: {{ user['username'] }}</p>
							{% endif %}
							{% if (panther_user['id'] == id or panther_user['is_admin'] or (user['g_moderator'] == '0' and panther_user['g_mod_change_passwords'] == '1')) %}
							<div class="actions"><a href="{{ change_pass_link }}">{{ lang_profile['Change pass'] }}</a></div>
							{% endif %}
						</div>
					</div>
	
	
					<div class="box">
						<p class="boxtitle">{{ lang_prof_reg['Email legend'] }}</p>
						<div class="infldset">
							{% if panther_user['is_admmod'] %}
							<label class="required">{{ lang_common['Email'] }} <span>{{ lang_common['Required'] }}</span><input type="text" name="req_email" value="{{ user['email'] }}" maxlength="80" /></label><div class="actions"><a href="{{ email_link }}">{{ lang_common['Send email'] }}</a></div>
							{% else %}
							{% if panther_config['o_regs_verify'] == '1' %}
							<p>{{ lang_profile['Email info']|format(user['email']) }} - <a href="{{ email_link }}">{{ lang_profile['Change email'] }}</a></p>
							{% else %}
							<label class="required">{{ lang_common['Email'] }} <span>{{ lang_common['Required'] }}</span><input type="text" name="req_email" value="{{ user['email'] }}" maxlength="80" /></label>
							{% endif %}
							{% endif %}
						</div>
					</div>
			
			
					<div class="box">
						<p class="boxtitle">{{ lang_prof_reg['Localisation legend'] }}</p>
				
							<p>{{ lang_prof_reg['Time zone info'] }}</p>
							<label>{{ lang_prof_reg['Time zone'] }}
							<select name="form[timezone]">
								<option value="-12"{% if user['timezone'] == -12 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-12:00'] }}</option>
								<option value="-11"{% if user['timezone'] == -11 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-11:00'] }}</option>
								<option value="-10"{% if user['timezone'] == -10 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-10:00'] }}</option>
								<option value="-9.5"{% if user['timezone'] == -9.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-09:30'] }}</option>
								<option value="-9"{% if user['timezone'] == -9 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-09:00'] }}</option>
								<option value="-8.5"{% if user['timezone'] == -8.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-08:30'] }}</option>
								<option value="-8"{% if user['timezone'] == -8 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-08:00'] }}</option>
								<option value="-7"{% if user['timezone'] == -7 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-07:00'] }}</option>
								<option value="-6"{% if user['timezone'] == -6 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-06:00'] }}</option>
								<option value="-5"{% if user['timezone'] == -5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-05:00'] }}</option>
								<option value="-4"{% if user['timezone'] == -4 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-04:00'] }}</option>
								<option value="-3.5"{% if user['timezone'] == -3.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-03:30'] }}</option>
								<option value="-3"{% if user['timezone'] == -3 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-03:00'] }}</option>
								<option value="-2"{% if user['timezone'] == -2 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-02:00'] }}</option>
								<option value="-1"{% if user['timezone'] == -1 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-01:00'] }}</option>
								<option value="0"{% if user['timezone'] == 0 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC'] }}</option>
								<option value="1"{% if user['timezone'] == 1 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+01:00'] }}</option>
								<option value="2"{% if user['timezone'] == 2 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+02:00'] }}</option>
								<option value="3"{% if user['timezone'] == 3 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+03:00'] }}</option>
								<option value="3.5"{% if user['timezone'] == 3.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+03:30'] }}</option>
								<option value="4"{% if user['timezone'] == 4 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+04:00'] }}</option>
								<option value="4.5"{% if user['timezone'] == 4.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+04:30'] }}</option>
								<option value="5"{% if user['timezone'] == 5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+05:00'] }}</option>
								<option value="5.5"{% if user['timezone'] == 5.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+05:30'] }}</option>
								<option value="5.75"{% if user['timezone'] == 5.75 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+05:45'] }}</option>
								<option value="6"{% if user['timezone'] == 6 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+06:00'] }}</option>
								<option value="6.5"{% if user['timezone'] == 6.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+06:30'] }}</option>
								<option value="7"{% if user['timezone'] == 7 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+07:00'] }}</option>
								<option value="8"{% if user['timezone'] == 8 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+08:00'] }}</option>
								<option value="8.75"{% if user['timezone'] == 8.75 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+08:45'] }}</option>
								<option value="9"{% if user['timezone'] == 9 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+09:00'] }}</option>
								<option value="9.5"{% if user['timezone'] == 9.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+09:30'] }}</option>
								<option value="10"{% if user['timezone'] == 10 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+10:00'] }}</option>
								<option value="10.5"{% if user['timezone'] == 10.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+10:30'] }}</option>
								<option value="11"{% if user['timezone'] == 11 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+11:00'] }}</option>
								<option value="11.5"{% if user['timezone'] == 11.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+11:30'] }}</option>
								<option value="12"{% if user['timezone'] == 12 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+12:00'] }}</option>
								<option value="12.75"{% if user['timezone'] == 12.75 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+12:45'] }}</option>
								<option value="13"{% if user['timezone'] == 13 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+13:00'] }}</option>
								<option value="14"{% if user['timezone'] == 14 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+14:00'] }}</option>
							</select>
							</label>
							<div class="rbox">
								<input type="checkbox" name="form[dst]" value="1"{% if user['dst'] == '1' %} checked="checked"{% endif %} />{{ lang_prof_reg['DST'] }}</label>
							</div>
							<label>{{ lang_prof_reg['Time format'] }}
							<select name="form[time_format]">
{% for format in time_formats %}
<option value="{{ format['value'] }}"{% if user['timezone'] == format['value'] %} selected="selected"{% endif %}>{{ format['time'] }}</option>
{% endfor %}
							</select>
							</label>
							<label>{{ lang_prof_reg['Date format'] }}
							<select name="form[date_format]">
{% for format in date_formats %}
<option value="{{ format['value'] }}"{% if user['date_format'] == format['value'] %} selected="selected"{% endif %}>{{ format['time'] }}</option>
{% endfor %}
							</select>
							</label>
{% if languages|length > 1 %}
<label>{{ lang_prof_reg['Language'] }}
<select name="language">
{% for language in languages %}
<option value="{{ language }}"{% if user['language'] == language %} selected="selected"{% endif %}>{{ language }}</option>
{% endfor %}
</select>
</label>
{% endif %}
						
					</div>
			
				
				
		
				    <div class="box">
						<p class="boxtitle">{{ lang_profile['User activity'] }}</p>
							<ul class="list">
							<li>{{ lang_profile['Registered info']|format(registered) }} {% if panther_user['is_admmod'] %} (<a href="{{ ip_link }}">{{ user['registration_ip'] }}</a>){% endif %}</li>
							<li>{{ lang_profile['Last post info']|format(last_post) }}</li>
							<li>{{ lang_profile['Last visit info']|format(last_visit) }}</li>
							</ul>
				
							{% if panther_user['is_admin'] %}
							<label>{{ lang_common['Posts'] }}<input type="text" name="num_posts" value="{{ user['num_posts'] }}" maxlength="8" /></label>
							{% endif %}
							
							{% if posts_actions is not empty %}
							<div class="actions">
							
							{% if (panther_config['o_show_post_count'] == '1' or panther_user['is_admmod']) and panther_user['is_admin'] == false %}
							{{ lang_profile['Posts info']|format(posts) }}
							{% endif %}
							
							{% for action in posts_actions %}
							<a href="{{ action['href'] }}">{{ action['lang'] }}</a>{% if not loop.last %} - {% endif %}
							{% endfor %}
							</div>
							{% endif %}
							
							
							{% if ((panther_user['is_admin'] or (panther_user['is_admmod'] and panther_user['g_mod_warn_users'] == '1')) and panther_config['o_warnings'] == '1') %}
							<p>{{ lang_warnings['Warning level'] }}: {{ has_active }} - <a href="{{ warning_link }}">{{ lang_warnings['Show all warnings'] }}</a> - <a href="{{ warn_link }}">{{ lang_warnings['Warn user'] }}</a></p>
							{% elseif ((panther_config['o_warning_status'] == '0' or panther_user['is_admmod'] or (panther_config['o_warning_status'] == '1' and has_active)) and panther_config['o_warnings'] == '1') %}
							<p>{{ lang_warnings['Warning level'] }}: {{ has_active }} - <a href="{{ warning_link }}">{{ lang_warnings['Show all warnings'] }}</a></p>
							{% endif %}
							{% if panther_user['is_admmod'] %}
							<label>{{ lang_profile['Admin note'] }}
							<input id="admin_note" type="text" name="admin_note" value="{{ user['admin_note'] }}" maxlength="30" /></label>
							{% endif %}
					
					</div>
			
				<div class="blockbuttons"><div class="conl">{{ lang_profile['Instructions'] }}</div><div class="conr"><input type="submit" name="update" value="{{ lang_common['Submit'] }}" class="btn submit" /></div></div>
			</form>
	
	</div>
</div>
</div> <!-- .profile-console -->