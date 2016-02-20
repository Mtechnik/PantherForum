<div class="linkst">
		<ul class="crumbs">
			<li><a href="{{ profile_link }}">{{ username }}</a></li>
			<li><span>»&#160;</span><a href="{{ warning_link }}"><strong>{{ lang_warnings['Warnings'] }}</strong></a></li>
		</ul>
</div>

<div class="block bwarnuser">
	<h2 class="blocktitle">{{ lang_warnings['Active warnings']|format(username) }}</h2>
	<div class="box">
		
		
			<div class="row th">
				<div class="col warning">{{ lang_warnings['Warning 2'] }}</div>
				<div class="col date">{{ lang_warnings['Date issued 2'] }}</div>
				<div class="col points">{{ lang_warnings['Points'] }}</div>
				<div class="col expires">{{ lang_warnings['Expires'] }}</div>
				<div class="col issuedby">{{ lang_warnings['Issued by 2'] }}</div>
				<div class="col details">{{ lang_warnings['Details'] }}</div>
			</div>
	
{% if active_warnings is not empty %}
{% for warning in active_warnings %}
				<div class="row tr">
					<div class="col warning">{{ warning['title'] }}</div>
					<div class="col date">{{ warning['issued'] }}</div>
					<div class="col points">{{ warning['points'] }}</div>
					<div class="col expires">{{ warning['expires'] }}</div>
					<div class="col issuedby">{{ warning['issuer']|raw }}</div>
					<div class="col details"><a href="{{ warning['details_link'] }}">{{ lang_warnings['Details'] }}</a></div>
				</div>
{% endfor %}
				<div class="row tr">
					<div class="col">{{ lang_warnings['No of warnings']|format(num_active) }}</div>
					
					<div class="col">{{ points_active }}</div>
					
				</div>
{% else %}
				<div class="row tr">
					<div class="col">{{ lang_warnings['No active warnings'] }}</div>
				</div>
{% endif %}

	
	</div>
</div>

<div class="block bwarnuser">
	<h2 class="blocktitle">{{ lang_warnings['Expired warnings']|format(username) }}</h2>
	<div class="box">

	
			<div class="row th">
				<div class="col warning">{{ lang_warnings['Warning 2'] }}</div>
				<div class="col date">{{ lang_warnings['Date issued 2'] }}</div>
				<div class="col points">{{ lang_warnings['Points'] }}</div>
				<div class="col expires">{{ lang_warnings['Expired'] }}</div>
				<div class="col issuedby">{{ lang_warnings['Issued by 2'] }}</div>
				<div class="col details">{{ lang_warnings['Details'] }}</div>
			</div>

{% if expired_warnings is not empty %}
{% for warning in expired_warnings %}
				<div class="row tr">
					<div class="col warning">{{ warning['title'] }}</div>
					<div class="col date">{{ warning['issued'] }}</div>
					<div class="col points">{{ warning['points'] }}</div>
					<div class="col expires">{{ warning['expired'] }}</div>
					<div class="col issuedby">{{ warning['issuer']|raw }}</div>
					<div class="col details"><a href="{{ warning['details_link'] }}">{{ lang_warnings['Details'] }}</a></div>
				</div>
{% endfor %}
				<div class="row tr">
					<div class="col">{{ lang_warnings['No of warnings']|format(num_expired) }}</div>
					
					<div class="col">{{ points_expired }}</div>
				
				</div>
{% else %}
				<div class="row tr">
					<div class="col">{{ lang_warnings['No expired warnings'] }}</div>
				</div>
{% endif %}
	
		
	</div>
</div>

<div class="linksb">
		<ul class="crumbs">
			<li><a href="{{ profile_link }}">{{ username }}</a></li>
			<li><span>»&#160;</span><a href="{{ warning_link }}"><strong>{{ lang_warnings['Warnings'] }}</strong></a></li>
		</ul>
</div>