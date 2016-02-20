<div class="main">
        <div class="block bprofile">
		<h2 class="blocktitle">{{ user['username'] }} - {{ lang_profile['Section personal'] }}</h2>
		
			<form id="profile2" method="post" action="{{ form_action }}">
				
					<div class="box">
						<p class="boxtitle">{{ lang_profile['Personal details legend'] }}</p>
						<div class="inbox">
						
							<input type="hidden" name="form_sent" value="1" />
							<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
							
							
							<div class="row">
							<label for="realname">{{ lang_profile['Realname'] }}</label>
							<input id="realname" type="text" name="form[realname]" value="{{ user['realname'] }}" maxlength="40" />
							</div>
							
							<div class="row">
							{% if panther_user['g_set_title'] == '1' %}
							<label for="title">{{ lang_common['Title'] }}</label>
							<em>({{ lang_profile['Leave blank'] }})</em>
							
							<input id="title" type="text" name="title" value="{{ user['title'] }}"  maxlength="50" />
							{% endif %}
							</div>
							
							<div class="row">
							<label for="location">{{ lang_profile['Location'] }}</label>
							<input id="location" type="text" name="form[location]" value="{{ user['location'] }}" maxlength="30" />
							</div>
							
							<div class="row">
							{% if panther_user['g_post_links'] == '1' or panther_user['is_admin'] %}
							<label for="website">{{ lang_profile['Website'] }}</label>
							<input id="website"  type="text" name="form[url]" value="{{ user['url'] }}" maxlength="80" />
							{% endif %}
							</div>
							
						</div>
						
					</div>
				
				<div class="blockbuttons"><div class="conl">{{ lang_profile['Instructions'] }}</div><div class="conr"><input type="submit" name="update" value="{{ lang_common['Submit'] }}" class="btn submit" /></div></div>
			</form>
	
		</div>
</div>
</div> <!-- .profile-console -->