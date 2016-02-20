<div class="linkst">
		<div class="pagepost">
			<ul class="pagination">{{ pagination|raw }}</ul>
		</div>
</div>

<div class="block bwarnuser">

	<h2 class="blocktitle">{{ lang_warnings['Recent warnings'] }}</span></h2>
	<div class="box">
	
	
			<div class="row th">
				<div class="col warning">{{ lang_warnings['Warning 2'] }}</div>
				<div class="col date">{{ lang_warnings['Date issued 2'] }}</div>
				<div class="col points">{{ lang_warnings['Points'] }}</div>
				<div class="col user">{{ lang_warnings['Warned user'] }}</div>
				<div class="col issuedby">{{ lang_warnings['Issued by 2'] }}</div>
				<div class="col details">{{ lang_warnings['Details'] }}</div>
			</div>

{% for warning in warnings %}
				<div class="row tr">
					<div class="col warning">{{ warning['title'] }}</div>
					<div class="col date">{{ warning['issued'] }}</div>
					<div class="col points">{{ warning['points'] }}</div>
					<div class="col user">{{ warning['username']|raw }}</div>
					<div class="col issuedby">{{ warning['issuer']|raw }}</div>
					<div class="col details"><a href="{{ details_link }}">{{ lang_warnings['Details'] }}</a></div>
				</div>
{% else %}
				<div class="row tr">
					<div class="col">{{ lang_warnings['No warnings'] }}</div>
				</div>
{% endfor %}
		
	
	</div>
</div>

<div class="linksb">

		<div class="pagepost">
			<p class="pagelink conl"><span class="pages-label">{{ lang_common['Pages'] }} </span>{{ pagination|raw }}</p>
		</div>

</div>