<div class="main">

<div class="block bprofile">
		<h2 class="blocktitle">{{ user['username'] }} - {{ lang_profile['Section privacy'] }}</h2>
		
			<form id="profile6" method="post" action="{{ form_action }}">
				
					<div class="box">
						<p class="boxtitle">{{ lang_prof_reg['Privacy options legend'] }}</p>
						
							<input type="hidden" name="form_sent" value="1" />
							<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
							<p class="info">{{ lang_prof_reg['Email setting info'] }}</p>
							<ul class="checklist">
								<li><label><input type="radio" name="form[email_setting]" value="0"{% if user['email_setting'] == '0' %} checked="checked"{% endif %} />{{ lang_prof_reg['Email setting 1'] }}</label></li>
								<li><label><input type="radio" name="form[email_setting]" value="1"{% if user['email_setting'] == '1' %} checked="checked"{% endif %} />{{ lang_prof_reg['Email setting 2'] }}</label></li>
								<li><label><input type="radio" name="form[email_setting]" value="2"{% if user['email_setting'] == '2' %} checked="checked"{% endif %} />{{ lang_prof_reg['Email setting 3'] }}</label></li>
							</ul>
						
					</div>
				
				{% if panther_config['o_forum_subscriptions'] == '1' or panther_config['o_topic_subscriptions'] == '1' %}
					<div class="box">
						<p class="boxtitle">{{ lang_profile['Subscription legend'] }}</p>
						
							<ul class="checklist">
								<li><label><input type="checkbox" name="form[notify_with_post]" value="1"{% if user['notify_with_post'] == '1' %} checked="checked"{% endif %} />{{ lang_profile['Notify full'] }}</label></li>
								{% if panther_config['o_topic_subscriptions'] == '1' %}
								<li><label><input type="checkbox" name="form[auto_notify]" value="1"{% if user['auto_notify'] == '1' %} checked="checked"{% endif %} />{{ lang_profile['Auto notify full'] }}</label></li>
								{% endif %}
							</ul>
						
					</div>	
				{% endif %}
				
				{% if panther_config['o_private_messaging'] == '1' and user['g_use_pm'] == '1' %}
					<div class="box">
						<p class="boxtitle">{{ lang_profile['Private messaging'] }}</p>
						
							<ul class="checklist">
								<li><label><input type="checkbox" name="form[pm_enabled]" value="1"{% if user['pm_enabled'] == '1' %} checked="checked"{% endif %} />{{ lang_profile['PM enabled'] }}</label></li>
								<li><label><input type="checkbox" name="form[pm_notify]" value="1"{% if user['pm_notify'] == '1' %} checked="checked"{% endif %} />{{ lang_profile['PM notify'] }}</label></li>
							</ul>
						
					</div>
				{% endif %}
				
				<div class="blockbuttons"><div class="conl">{{ lang_profile['Instructions'] }}</div><div class="conr"><input type="submit" name="update" value="{{ lang_common['Submit'] }}" class="btn submit" /></div></div>
			</form>
		</div>
		
</div>
</div> <!-- .profile-console -->