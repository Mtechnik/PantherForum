
<div id="useradmin" class="block bleaders">
	<h2 class="blocktitle">{{ lang_online['admin'] }}</h2>

				<div class="row th">
					<div class="col username">{{ lang_common['Username'] }}</div>
					<div class="col forums">{{ lang_online['forums'] }}</div>
					{% if panther_config['o_users_online'] == '1' %}<div class="col currently">{{ lang_online['currently 1'] }}</div>{% endif %}
				</div>
			
			
{% for admin in administrators %}
				<div class="row tr">
					<div class="col username">{{ admin['username']|raw }}</div>
					<div class="col forums">{{ lang_online['all forums'] }}</div>
					{% if panther_config['o_users_online'] == '1' %}<div class="col currently">{{ admin['location'] }}</div>{% endif %}
				</div>
{% endfor %}
			
		

</div>

{% if global_moderators is not empty %}
<div id="userglobalmod" class="block bleaders">
	<h2 class="blocktitle">{{ lang_online['global mod'] }}</h2>
	
			
				<div class="row th">
					<div class="col username">{{ lang_common['Username'] }}</div>
					<div class="col forums">{{ lang_online['forums'] }}</div>
					{% if panther_config['o_users_online'] == '1' %}<div class="col currently">{{ lang_online['currently 1'] }}</div>{% endif %}
				</div>
			
{% for moderator in global_moderators %}
				<div class="row tr">
					<div class="col username">{{ moderator['username']|raw }}</div>
					<div class="col forums">{{ lang_online['all forums'] }}</div>
					{% if panther_config['o_users_online'] == '1' %}<div class="col currently">{{ moderator['location'] }}</div>{% endif %}
				</div>
{% endfor %}
		
	
</div>
{% endif %}
{% if moderators is not empty %}
<div id="usersmod" class="block bleaders">
	<h2 class="blocktitle">{{ lang_online['mod'] }}</h2>

		
				<div class="row th">
					<div class="col username">{{ lang_common['Username'] }}</div>
					<div class="col forums">{{ lang_online['forums'] }}</div>
					{% if panther_config['o_users_online'] == '1' %}<div class="col currently">{{ lang_online['currently 1'] }}</div>{% endif %}
				</div>
		
		
{% for moderator in moderators %}
				<div class="row tr">
					<div class="col username">{{ moderator['username']|raw }}</div>
					<div class="col forums"><form method="get" action="{{ action }}">
					<select name="id" onchange="window.location=('{{ location }}'">
					<option>{{ lang_online['total forums']|format(moderator['total']) }}</option>
					{% for option in moderator['forums'] %}
					<option value="{{ option['forum_id'] }}">{{ option['forum_name'] }}</option>
					{% endfor %}
					</select></form></div>
					{% if panther_config['o_users_online'] == '1' %}<div class="col currently">{{ moderator['location'] }}</div>{% endif %}
				</div>
{% endfor %}


</div>
{% endif %}
