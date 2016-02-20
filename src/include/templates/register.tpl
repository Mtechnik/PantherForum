{% if errors is not empty %}
<div id="posterror" class="block berror">
	<h2>{{ lang_register['Registration errors'] }}</h2>
	<div class="box">
		<div class="inbox error-info">
			<p>{{ lang_register['Registration errors info'] }}</p>
			<ul class="error-list">
{% for error in errors %}
<li>{{ error }}</li>
{% endfor %}
			</ul>
		</div>
	</div>
</div>
{% endif %}
<div id="regform" class="block bregister">
	<h2 class="blocktitle">{{ lang_register['Register'] }}</h2>

		<form id="register" method="post" action="{{ form_action }}" onsubmit="this.register.disabled=true;if(process_form(this)){return true;}else{this.register.disabled=false;return false;}">
<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
			
			<div class="box">
				<div class="forminfo">
					<p class="boxtitle">{{ lang_common['Important information'] }}</p>
					<p>{{ lang_register['Desc 1'] }}</p>
					<p>{{ lang_register['Desc 2'] }}</p>
				</div>
			</div>
			
			
			<div class="box">
					<p class="boxtitle">{{ lang_register['Username legend'] }}</p>


						<div class="row form-box">
					
						<label for="requser" class="label-field required"><span>{{ lang_common['Username'] }} {{ lang_common['Required'] }}</span></label>
						<input id="requser" type="text" name="req_user" value="{{ POST['req_user'] }}" maxlength="25" class="input-field"/>
						

						</div>
						
					
			</div>
			
			
			{% if panther_config['o_regs_verify'] == '0' %}
		
				<div class="box">
					<p class="boxtitle">{{ lang_register['Pass legend'] }}</p>
				
						<div class="row"><label for="password1" class="conl required">{{ lang_common['Password'] }} <span>{{ lang_common['Required'] }}</span></label><input id="password1" type="password" name="req_password1" value="{{ POST['req_password1'] }}"/></div>
						<div class="row"><label for="password2" class="conl required">{{ lang_prof_reg['Confirm pass'] }}<span>{{ lang_common['Required'] }}</span></label><input id="password2" type="password" name="req_password2" value="{{ POST['req_password2'] }}"/></div>
					<div class="row"><p>{{ lang_register['Pass info'] }}</p></div>
					
					
				</div>
		
			{% endif %}
			
			
			
		
				<div class="box">
					<p class="boxtitle">{% if panther_config['o_regs_verify'] == '1' %}{{ lang_prof_reg['Email legend 2'] }}{% else %}{{ lang_prof_reg['Email legend'] }}{% endif %}</p>
					<div class="row">
					{% if panther_config['o_regs_verify'] == '1' %}<p>{{ lang_register['Email info'] }}</p>{% endif %}
						<label for="reqemail1" class="required">{{ lang_common['Email'] }}<span>{{ lang_common['Required'] }}</span></label>
						<input id="reqemail1" type="text" name="req_email1" value="{{ POST['req_email1'] }}" maxlength="80" />
						{% if panther_config['o_regs_verify'] == '1' %}
						<label for="reqemail2" class="required">{{ lang_register['Confirm email'] }}<span>{{ lang_common['Required'] }}</span></label>
						<input id="reqemail2" type="text" name="req_email2" value="{{ POST['req_email2'] }}" maxlength="80" />
						{% endif %}
					</div>
				</div>
	
			
			
			
			
				<div class="box">
					<p class="boxtitle">{{ lang_prof_reg['Localisation legend'] }}</p>
					<div class="row">
						<p>{{ lang_prof_reg['Time zone info'] }}</p>
						<label for="timezone">{{ lang_prof_reg['Time zone'] }}</label>
						<select id="timezone" name="time_zone">
							<option value="-12"{% if timezone == -12 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-12:00'] }}</option>
							<option value="-11"{% if timezone == -11 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-11:00'] }}</option>
							<option value="-10"{% if timezone == -10 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-10:00'] }}</option>
							<option value="-9.5"{% if timezone == -9.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-09:30'] }}</option>
							<option value="-9"{% if timezone == -9 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-09:00'] }}</option>
							<option value="-8.5"{% if timezone == -8.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-08:30'] }}</option>
							<option value="-8"{% if timezone == -8 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-08:00'] }}</option>
							<option value="-7"{% if timezone == -7 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-07:00'] }}</option>
							<option value="-6"{% if timezone == -6 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-06:00'] }}</option>
							<option value="-5"{% if timezone == -5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-05:00'] }}</option>
							<option value="-4"{% if timezone == -4 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-04:00'] }}</option>
							<option value="-3.5"{% if timezone == -3.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-03:30'] }}</option>
							<option value="-3"{% if timezone == -3 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-03:00'] }}</option>
							<option value="-2"{% if timezone == -2 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-02:00'] }}</option>
							<option value="-1"{% if timezone == -1 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC-01:00'] }}</option>
							<option value="0"{% if timezone == 0 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC'] }}</option>
							<option value="1"{% if timezone == 1 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+01:00'] }}</option>
							<option value="2"{% if timezone == 2 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+02:00'] }}</option>
							<option value="3"{% if timezone == 3 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+03:00'] }}</option>
							<option value="3.5"{% if timezone == 3.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+03:30'] }}</option>
							<option value="4"{% if timezone == 4 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+04:00'] }}</option>
							<option value="4.5"{% if timezone == 4.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+04:30'] }}</option>
							<option value="5"{% if timezone == 5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+05:00'] }}</option>
							<option value="5.5"{% if timezone == 5.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+05:30'] }}</option>
							<option value="5.75"{% if timezone == 5.75 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+05:45'] }}</option>
							<option value="6"{% if timezone == 6 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+06:00'] }}</option>
							<option value="6.5"{% if timezone == 6.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+06:30'] }}</option>
							<option value="7"{% if timezone == 7 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+07:00'] }}</option>
							<option value="8"{% if timezone == 8 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+08:00'] }}</option>
							<option value="8.75"{% if timezone == 8.75 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+08:45'] }}</option>
							<option value="9"{% if timezone == 9 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+09:00'] }}</option>
							<option value="9.5"{% if timezone == 9.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+09:30'] }}</option>
							<option value="10"{% if timezone == 10 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+10:00'] }}</option>
							<option value="10.5"{% if timezone == 10.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+10:30'] }}</option>
							<option value="11"{% if timezone == 11 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+11:00'] }}</option>
							<option value="11.5"{% if timezone == 11.5 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+11:30'] }}</option>
							<option value="12"{% if timezone == 12 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+12:00'] }}</option>
							<option value="12.75"{% if timezone == 12.75 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+12:45'] }}</option>
							<option value="13"{% if timezone == 13 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+13:00'] }}</option>
							<option value="14"{% if timezone == 14 %} selected="selected"{% endif %}>{{ lang_prof_reg['UTC+14:00'] }}</option>
						</select>
						</div>
						<div class="row">
							<input id="dst" type="checkbox" name="dst" value="1"{% if dst == '1' %} checked="checked"{% endif %} /><label for="dst">{{ lang_prof_reg['DST'] }}</label>
						</div>
						
					
{% if languages|length > 1 %}
<div class="row">	
<label for="languageselect">{{ lang_prof_reg['Language'] }}</label>
<select id="languageselect" name="language">
{% for language in languages %}
<option value="{{ language }}"{% if panther_config['o_defualt_lang'] == language %} selected="selected"{% endif %}>{{ language }}</option>
{% endfor %}
</select>
</div>	
{% endif %}
				
				</div>
				
				
			
			
				<div class="box">
					<p class="boxtitle">{{ lang_prof_reg['Privacy options legend'] }}</p>
					<div class="row">
						<p>{{ lang_prof_reg['Email setting info'] }}</p>
						<ul class="checklist">
							<li><input id="email_setting1" type="radio" name="email_setting" value="0"{% if email_setting == '0' %} checked="checked"{% endif %} /><label for="email_setting1">{{ lang_prof_reg['Email setting 1'] }}</label></li>
							<li><input id="email_setting2" type="radio" name="email_setting" value="1"{% if email_setting == '1' %} checked="checked"{% endif %} /><label for="email_setting2">{{ lang_prof_reg['Email setting 2'] }}</label></li>
							<li><input id="email_setting3" type="radio" name="email_setting" value="2"{% if email_setting == '2' %} checked="checked"{% endif %} /><label for="email_setting3">{{ lang_prof_reg['Email setting 3'] }}</label></li>
						</ul>
					</div>
				</div>
			
			
{% if robot_id is not none %}
			
				<div class="box">
					<p class="boxtitle">{{ lang_common['Robot title'] }}</p>
					<div class="row">
						<p>{{ lang_common['Robot info'] }}</p>
						<label for="answer" class="required">{{ robot_test['question'] }}<span>{{ lang_common['Required'] }}</span></label>
				 		<input id="answer" name="answer" type="text" maxlength="30" /><input name="id" value="{{ robot_id }}" type="hidden" />
						
					</div>
				</div>
		
{% endif %}

			<p class="buttons"><input type="submit" name="register" value="{{ lang_common['Register'] }}" /></p>
		</form>
	</div>
