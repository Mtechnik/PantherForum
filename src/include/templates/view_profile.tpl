<div id="viewprofile" class="main">
	<div class="block bprofile">
	
	<h2 class="blocktitle">{{ lang_common['Profile'] }}</h2>
			
				<div class="box">
				<p class="boxtitle">{{ lang_profile['Section personal'] }}</p>
					<div class="inbox">
						
							{% for info in user_personal %}
							
							{% if info['title'] is not none %}
							<div class="row">
							<div class="col label">{{ info['title'] }}</div>
							
							{% else %}
							
							<div class="col value">{% if info['icon'] is not none %}<span class="online"></span>{% endif %}
							{% if info['href'] is not none %}<span class="{{ info['class'] }}"><a href="{{ info['data'] }}" rel="nofollow">{{ info['lang'] }}</a></span>{% else %}
							{% if info['raw'] is not none %}{{ info['data']|raw }}{% else %}{{ info['data'] }}{% endif %}{% endif %}</div>
							</div>
							{% endif %}
							
							{% endfor %}
					
						
					</div>
				</div>
			
			
			
			{% if panther_config['o_reputation'] == '1' %}

				<div class="box">
				<p class="boxtitle">{{ lang_profile['Reputation'] }}</p>
					<div class="inbox">
						<ul class="list">
							<li><span class="reputation {{ reputation['type'] }}">{{ lang_profile['Reputation'] }}: {{ reputation['value'] }}</span></li>
							<li><label><a href="{{ reputation['link_given'] }}">{{ lang_profile['Rep_received'] }}</a></li>
							<li><label><a href="{{ reputation['link_received'] }}">{{ lang_profile['Rep_given'] }}</a></li>
					    </ul>
						
					</div>
				</div>
			
			{% endif %}
			
			
			
			
			{% if user_messaging is not empty %}

				<div class="box">
				<p class="boxtitle">{{ lang_profile['Section messaging'] }}</p>
					<div class="inbox">
						
			{% for info in user_messaging %}
			{% if info['title'] is not none %}
			<div class="col label">{{ info['title'] }}</div>
			{% else %}
			<div class="col value">{{ info['data'] }}</div>
			{% endif %}
			{% endfor %}
						
						
					</div>
				</div>
		
			{% endif %}
			{% if user_personality is not empty %}
				<div class="box">
				<p class="boxtitle">{{ lang_profile['Section personality'] }}</p>
					<div class="inbox">
						
							{% for info in user_personality %}
			{% if info['title'] is not none %}
			<dt>{{ info['title'] }}</dt>
			{% else %}
			<dd>{% if info['signature'] is not none %}<div class="postsignature postmsg">{{ info['data']|raw }}</div>{% else %}{{ info['data']|raw }}{% endif %}</dd>
			{% endif %}
							{% endfor %}
						
						
					</div>
				</div>
			{% endif %}
			
				<div class="box">
				<p class="boxtitle">{{ lang_profile['User activity'] }}</p>
					<div class="inbox">
					
							{% for info in user_activity %}
							{% if info['title'] is not none %}
							<dt>{{ info['title'] }}</dt>
							{% else %}
							<dd>{% if info['implode'] is not none %}
							{% for search in info['data'] %}
							{% if search['data'] is not none %}
							{{ search['data'] }}
							{% else %}
							<a href="{{ search[0] }}">{{ search[1] }}</a>
							{% if not loop.last %} - {% endif %}
							{% endif %}
							{% endfor %}
							{% else %}
							{{ info['data'] }}{% if info['href'] is not none %} - <a href="{{ info['href'] }}">{{ info['lang'] }}</a> {% endif %}{% if info['href2'] is not none %}- <a href="{{ info['href2'] }}">{{ info['lang2'] }}</a>{% endif %}
							{% endif %}
							</dd>
							{% endif %}
							{% endfor %}
						
					</div>
				</div>
		
	</div>

</div>
</div> <!-- .profile-console -->