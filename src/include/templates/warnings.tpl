<div class="block bwarnuser">
	<h2 class="blocktitle">{{ lang_warnings['Warning types'] }}</h2>
	<div class="box">
	
		
			<div class="row">
				<div class="col">{{ lang_warnings['Name'] }}</div>
				<div class="col">{{ lang_warnings['Description'] }}</div>
				<div class="col">{{ lang_warnings['Points'] }}</div>
			</div>

{% for warning in warning_types %}
				<div class="row">
					<div class="col">{{ warning['title'] }}</div>
					<div class="col">{{ warning['description']|raw }}</div>
					<div class="col">{{ warning['points'] }}</div>
				</div>
{% endfor %}
	

	</div>
</div>

<div class="block bwarnuser">

	<h2 class="blocktitle">{{ lang_warnings['Automatic bans'] }}</h2>
	<div class="box">
	
			<div class="row">
				<div class="col">{{ lang_warnings['Ban period'] }}</div>
				<div class="col">{{ lang_warnings['Reason'] }}</div>
			</div>

{% for warning in warning_levels %}
				<div class="row">
					<div class="col">{{ warning['title'] }}</div>
					<div class="col">{{ warning['points'] }} {{ lang_warnings['No of points'] }}</div>
			</div>

{% endfor %}
		</div>
	</div>