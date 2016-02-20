<div class="block">
	<h2 class="blocktitle">{{ lang_common['Debug table'] }}</h2>
	<div class="box">

			
				<div class="row">
					<div class="col">{{ lang_common['Query times'] }}</div>
					<div class="col">{{ lang_common['Query'] }}</div>
				</div>
		
{% for query in queries %}
				<div class="row">
					<div class="col">{% if query['time'] != 0 %}{{ query['time'] }}{% else %}&#160;{% endif %}</div>
					<div class="col">{{ query['sql'] }}</div>
				</div>
{% endfor %}
				<div class="row">
					<div class="col">{{ lang_common['Total query time']|format(query_time_total) }}</div>
				</div>
		

	</div>
</div>