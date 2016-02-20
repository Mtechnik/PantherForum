<div class="linkst"><ul class="pagination">{{ pagination|raw }}</ul></div>


<div id="usersonline" class="block busersonline">
	<h2 class="blocktitle">{{ lang_online['users online'] }}</h2>
	
				<div class="row th">
					<div class="col">{{ lang_common['Username'] }}</div>
					<div class="col">{{ lang_online['user currently'] }}</div>
					<div class="col">{{ lang_online['last active'] }}</div>				
				</div>
{% for user in users_online %}
				<div class="row tr">
					<div class="col">{{ user['username']|raw }}</div>
					<div class="col">
					{% if user['location']['href'] is not none %}
					{{ user['location']['lang'] }} <a href="{{ user['location']['href'] }}">{{ user['location']['name'] }}</a>
					{% else %}
					{{ user['location'] }}
					{% endif %}
					</div>
					<div class="col">{{ user['last_active'] }}</div>
				</div>
{% endfor %}

</div>


<div class="linksb"><ul class="pagination">{{ pagination|raw }}</ul></div>
