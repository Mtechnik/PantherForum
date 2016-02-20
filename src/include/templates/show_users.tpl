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

<div class="block">
	<h2 class="blocktitle">{{ lang_admin_users['Results head'] }}</h2>
	<div class="box">
		<div class="inbox">
			
				<div class="row">
					<div class="col">{{ lang_admin_users['Results username head'] }}</div>
					<div class="col">{{ lang_admin_users['Results e-mail head'] }}</div>
					<div class="col">{{ lang_admin_users['Results title head'] }}</div>
					<div class="col">{{ lang_admin_users['Results posts head'] }}</div>
					<div class="col">{{ lang_admin_users['Results admin note head'] }}</div>
					<div class="col">{{ lang_admin_users['Results actions head'] }}</div>
				</div>
			
{% for user in users %}
{% if user['poster'] is not none %}
				<div class="row">
					<div class="col">{{ user['poster'] }}</div>
				
					<div class="col">{{ lang_admin_users['Results guest'] }}</div>
				</div>
{% else %}
				<div class="row">
					<div class="col"><a href="{{ user['link'] }}">{{ user['username'] }}</a></div>
					<div class="col"><a href="mailto:{{ user['email'] }}">{{ user['email'] }}</a></div>
					<div class="col">{{ user['title'] }}</div>
					<div class="col">{{ user['num_posts'] }}</div>
					<div class="col">{% if user['admin_note'] != '' %}{{ user['admin_note'] }}{% else %}&#160;{% endif %}</div>
					<div class="col"><a href="{{ user['ip_stats_link'] }}">{{ lang_admin_users['Results view IP link'] }}</a> | <a href="{{ user['search_posts_link'] }}">{{ lang_admin_users['Results show posts link'] }}</a></div>
				</div>
{% endif %}
{% else %}
<div class="row"><div class="col">{{ lang_admin_users['Results no IP found'] }}</div></div>
{% endfor %}
		
		</div>
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