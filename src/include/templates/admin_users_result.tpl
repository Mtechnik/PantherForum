<div class="content">
<div class="linkst">
	
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_admin_common['Admin'] }} {{ lang_admin_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ form_action }}">{{ lang_admin_common['Users'] }}</a></li>
			<li><span>»&#160;</span><strong>{{ lang_admin_users['Results head'] }}</strong></li>
		</ul>
		<div class="pagepost">
			<ul class="pagination">{{ pagination|raw }}</p>
		</div>
	
</div>
<form id="search-users-form" action="{{ form_action }}" method="post">
<div id="users2" class="blocktable">
	<h2>{{ lang_admin_users['Results head'] }}</h2>
	<div class="box">
		<div class="inbox">
		
				<div class="row">
					<div class="col">{{ lang_admin_users['Results username head'] }}</div>
					<div class="col">{{ lang_admin_users['Results e-mail head'] }}</div>
					<div class="col">{{ lang_admin_users['Results title head'] }}</div>
					<div class="col">{{ lang_admin_users['Results posts head'] }}</div>
					<div class="col">{{ lang_admin_users['Results admin note head'] }}</div>
					<div class="col">{{ lang_admin_users['Results actions head'] }}</div>
					{% if can_action %}<div class="col">{{ lang_admin_users['Select'] }}</div>{% endif %}
				</div>
		
{% for user in users %}
				<div class="row">
					<div class="col"><a href="{{ user['profile_link'] }}">{{ user['username'] }}</a></div>
					<div class="col"><a href="mailto:{{ user['email'] }}">{{ user['email'] }}</a></div>
					<div class="col">{% if user['unverified'] %}<span class="warntext">{{ lang_admin_users['Not verified'] }}</span>{% else %}{{ user['title'] }}{% endif %}</div>
					<div class="col">{{ user['num_posts'] }}</div>
					<div class="col">{% if user_data['admin_note'] != '' %}{{ user_data['admin_note'] }}{% else %}&#160;{% endif %}</div>
					<div class="col"><a href="{{ user['ip_stats_link'] }}">{{ lang_admin_users['Results view IP link'] }}</a> | <a href="{{ user['search_posts_link'] }}">{{ lang_admin_users['Results show posts link'] }}</a></div>
					{% if can_action %}<td class="tcmod"><input type="checkbox" name="users[{{ user['id'] }}]" value="1" /></div>{% endif %}
				</div>
{% else %}
<div class="row"><td class="tcl" colspan="6">{{ lang_admin_users['No match'] }}</div></div>
{% endfor %}
			
		</div>
	</div>
</div>
<div class="linksb">
	<div class="inbox crumbsplus">
		<div class="pagepost">
			<p class="pagelink"><span class="pages-label">{{ lang_common['Pages'] }} </span>{{ pagination|raw }}</p>
			{% if can_action %}<p class="conr modbuttons"><input type="hidden" name="csrf_token" value="{{ csrf_token }}" /><a href="#" onclick="return select_checkboxes('search-users-form', this, '{{ lang_admin_users['Unselect all'] }}')">{{ lang_admin_users['Select all'] }}</a> {% if can_ban %}<input type="submit" name="ban_users" value="{{ lang_admin_users['Ban'] }}" />{% endif %} {% if can_delete %}<input type="submit" name="delete_users" value="{{ lang_admin_users['Delete'] }}" />{% endif %} {% if can_move %}<input type="submit" name="move_users" value="{{ lang_admin_users['Change group'] }}" />{% endif %}</p>{% endif %}
		</div>
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_admin_common['Admin'] }} {{ lang_admin_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ form_action }}">{{ lang_admin_common['Users'] }}</a></li>
			<li><span>»&#160;</span><strong>{{ lang_admin_users['Results head'] }}</strong></li>
		</ul>

	</div>
</div>
</form>

</div>
</div><!-- .admin-console -->	