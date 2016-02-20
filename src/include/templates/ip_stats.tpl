<div class="linkst">
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_admin_common['Admin'] }} {{ lang_admin_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ users_link }}">{{ lang_admin_common['Users'] }}</a></li>
			<li><span>»&#160;</span><strong>{{ lang_admin_users['Results head'] }}</strong></li>
		</ul>
		<div class="pagepost">
			<ul class="pagination">{{ pagination|raw }}</ul>
		</div>	
</div>


<div id="users1" class="block">
	<h2 class="blocktitle">{{ lang_admin_users['Results head'] }}</h2>
	<div class="box">
		
			
				<div class="row">
					<div class="col">{{ lang_admin_users['Results IP address head'] }}</div>
					<div class="col">{{ lang_admin_users['Results last used head'] }}</div>
					<div class="col">{{ lang_admin_users['Results times found head'] }}</div>
					<div class="col">{{ lang_admin_users['Results action head'] }}</div>
				</div>
			
{% for user in users %}
				<div class="row">
					<div class="col"><a href="{{ user['host'] }}">{{ user['poster_ip'] }}</a></div>
					<div class="col">{{ user['last_used'] }}</div>
					<div class="col">{{ user['used_times'] }}</div>
					<div class="col"><a href="{{ user['show_link'] }}">{{ lang_admin_users['Results find more link'] }}</a></div>
				</div>
{% else %}
<div class="row"><div class="col">{{ lang_admin_users['Results no posts found'] }}</div></div>
{% endfor %}
			
	
	</div>
</div>

<div class="linksb">
		<div class="pagepost">
			<ul class="pagination">{{ pagination|raw }}</ul>
		</div>
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_admin_common['Admin'] }} {{ lang_admin_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ users_link }}">{{ lang_admin_common['Users'] }}</a></li>
			<li><span>»&#160;</span><strong>{{ lang_admin_users['Results head'] }}</strong></li>
		</ul>
</div>