<div class="main">

<div class="block bprofile">
		<h2 class="blocktitle">{{ user['username'] }} - {{ lang_profile['Section display'] }}</h2>

			<form id="profile5" method="post" action="{{ form_action }}">
				<input type="hidden" name="form_sent" value="1" /><input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				
{% if styles|length > 1 %}
					<div class="box">
						<p class="boxtitle">{{ lang_profile['Style legend'] }}</p>
						<div class="infldset">
							<label for="choosestyle">{{ lang_profile['Styles'] }}</label>
							<select id="choosestyle" name="form[style]">
{% for style in styles %}
<option value="{{ style }}"{% if user['style'] == style %} selected="selected"{% endif %}>{{ style|replace({'_': ' '}) }}</option>
{% endfor %}
							</select>
							
						</div>
					</div>
{% endif %}


					<div class="box">
						<p class="boxtitle">{{ lang_profile['Post display legend'] }}</p>
						<div class="infldset">
							<p>{{ lang_profile['Post display info'] }}</p>
							<ul class="checklist">
{% for checkbox in checkboxes %}
<li>
<input id="rbox" type="checkbox" name="form[{{ checkbox['name'] }}]" value="1"{% if checkbox['checked'] %} checked="checked"{% endif %} />
<label for="rbox">{{ checkbox['title'] }}</label>
</li>
{% endfor %}
							</ul>
						</div>
					</div>

				

					<div class="box">
						<p class="boxtitle">{{ lang_profile['Pagination legend'] }}</p>
						<div class="infldset">
							<label for="topicperpage">{{ lang_profile['Topics per page'] }}</label>
							<input id="topicperpage" type="text" name="form[disp_topics]" value="{{ user['disp_topics'] }}" maxlength="2" />
							
							<label for="postperpage">{{ lang_profile['Posts per page'] }}</label>
							<input id="postperpage" type="text" name="form[disp_posts]" value="{{ user['disp_posts'] }}" maxlength="2" />
							
							<p class="info">{{ lang_profile['Paginate info'] }} {{ lang_profile['Leave blank'] }}</p>
						</div>
					</div>
					
				{% if panther_config['o_reputation'] == '1' %}
                    <div class="box">
                        <p class="boxtitle">{{ lang_profile['Reputation'] }}</p>
                        <div class="infldset">
						<ul class="list">
						<li><span class="reputation {{ reputation['type'] }}">{{ lang_profile['Reputation'] }}: {{ reputation['value'] }}</span>  </li>
                       <li><a href="{{ received_link }}">{{ lang_profile['Rep_received'] }}</a> </li>
                       <li><a href="{{ given_link }}">{{ lang_profile['Rep_given'] }}</a> </li>
					   </ul>
                        </div>
                    </div>
				{% endif %}
				
				<div class="blockbuttons"><div class="conl">{{ lang_profile['Instructions'] }}</div><div class="conr"><input type="submit" name="update" value="{{ lang_common['Submit'] }}" class="btn submit" /></div></div>
			</form>
	
	</div>	
	
</div>
</div> <!-- .profile-console -->