<div class="main">
<div class="block bprofile">
		<h2 class="blocktitle">{{ user['username'] }} - {{ lang_profile['Section admin'] }}</h2>
	
			<form id="profile7" method="post" action="{{ form_action }}">
			
				<input type="hidden" name="form_sent" value="1" />
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				
				{% if posting_ban %}
					<div class="box">
						<p class="boxtitle">{{ lang_profile['restrictions'] }}</p>
						
						<p>{{ lang_profile['Posting ban'] }} {{ ban_info|raw }}</p>
						<label><input type="checkbox" name="remove_ban" value="1">{{ lang_profile['posting ban delete'] }}
						<input type="text" name="expiration_time" maxlength="5" value="{{ posting_ban[0] }}" tabindex="3" />
						<select name="expiration_unit">
							<option value="minutes"{% if posting_ban[2] == lang_profile['Minutes'] %} selected="selected"{% endif %}>{{ lang_profile['Minutes'] }}</option>
							<option value="hours"{% if posting_ban[2] == lang_profile['Hours'] %} selected="selected"{% endif %}>{{ lang_profile['Hours'] }}</option>
							<option value="days"{% if posting_ban[2] == lang_profile['Days'] %} selected="selected"{% endif %}>{{ lang_profile['Days'] }}</option>
							<option value="months"{% if posting_ban[2] == lang_profile['Months'] %} selected="selected"{% endif %}>{{ lang_profile['Months'] }}</option>
						</select>
						</label>
							<input type="submit" name="update_posting_ban" value="{{ lang_profile['Save'] }}" />
					
					</div>
				{% endif %}
				
				
				
					{% if is_moderator %}
						<div class="box">
					<p class="boxtitle">{{ lang_profile['Delete ban legend'] }}</p>
					
							<p><input type="submit" name="ban" value="{{ lang_profile['Ban user'] }}" /></p>
					
					</div>
			
			
					{% else %}
					{% if edit_groups %}
					<div class="box">
					<p class="boxtitle">{{ lang_profile['Group membership legend'] }}</p>
						
							<select id="group_id" name="group_id">
{% for group in groups %}
<option value="{{ group['id'] }}"{% if group['checked'] %} selected="selected"{% endif %}>{{ group['title'] }}</option>
{% endfor %}
							</select>
							<input type="submit" name="update_group_membership" value="{{ lang_profile['Save'] }}" />
					
					</div>
				
					{% endif %}
				
					<div class="box">
						<p class="boxtitle">{{ lang_profile['Delete ban legend'] }}</p>
				
							<p>{% if can_delete %}<input type="submit" name="delete_user" value="{{ lang_profile['Delete user'] }}" /> {% endif %}<input type="submit" name="ban" value="{{ lang_profile['Ban user'] }}" /></p>
				
					</div>
			
					{% endif %}
					
					
					
					
					{% if user_is_moderator %}
				
					<div class="box">
						<p class="boxtitle">{{ lang_profile['Set mods legend'] }}</p>
						
							<p>{{ lang_profile['Moderator in info'] }}</p>
{% for category in categories %}
<div class="conl">
								<p><strong>{{ category['name'] }}</strong></p>
								<div class="checklist">
{% for forum in forums if forum['category_id'] == category['cid'] %}
<label><input type="checkbox" name="moderator_in[{{ forum['id'] }}]" value="1"{% if forum['checked'] %} checked="checked"{% endif %} />{{ forum['name'] }}<br /></label>
{% endfor %}
								</div>
							</div>
{% endfor %}
							
							
							<input type="submit" name="update_forums" value="{{ lang_profile['Update forums'] }}" />
				
					</div>
					{% endif %}
			</form>
			
	
		
	</div>	
</div>
</div> <!-- .profile-console -->