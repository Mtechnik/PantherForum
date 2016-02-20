<div id="adminconsole" class="admin-console">

	<div class="sidebar">

		<div class="box mod">
		<span class="title"><span class="text">{{ lang_admin_common['Moderator menu'] }}</span></span>
				<ul>
					<li{% if page == 'index' %} class="index isactive"{% endif %} class="index"><a href="{{ index_link }}"><span class="text">{{ lang_admin_common['Index'] }}</span></a></li>
					<li{% if page == 'users' %} class="users isactive"{% endif %} class="users"><a href="{{ users_link }}"><span class="text">{{ lang_admin_common['Users'] }}</span></a></li>
					{% if panther_user['is_admin'] or panther_user['g_mod_ban_users'] == '1' %}<li{% if page == 'bans' %} class="bans isactive"{% endif %} class="bans"><a href="{{ ban_link }}"><span class="text">{{ lang_admin_common['Bans'] }}</span></a></li>{% endif %}
					{% if panther_user['is_admin'] or panther_config['o_report_method'] == '0' or panther_config['o_report_method'] == '2' %}<li{% if page == 'reports' %} class="report isactive"{% endif %} class="report"><a href="{{ reports_link }}"><span class="text">{{ lang_admin_common['Reports'] }}</span></a></li>{% endif %}
					<li{% if page == 'announcements' %} class="announcements isactive"{% endif %} class="announcements"><a href="{{ announce_link }}"><span class="text">{{ lang_admin_common['Announcements'] }}</span></a></li>
					<li{% if page == 'posts' %} class="unapproved isactive"{% endif %} class="unapproved"><a href="{{ posts_link }}"><span class="text">{{ lang_admin_common['Posts'] }}</span></a></li>
					{% if panther_config['o_delete_full'] == '0' %}<li{% if page == 'deleted' %} class="deleted isactive"{% endif %} class="deleted"><a href="{{ deleted_link }}"><span class="text">{{ lang_admin_common['Deleted'] }}</span></a></li>{% endif %}
				</ul>
		</div>
		{% if admin_menu is not empty %}
	
		<div class="box admin">
		<span class="title"><span class="text">{{ lang_admin_common['Admin menu'] }}</span></span>
				<ul>
{% for item in admin_menu %}
<li{% if page == item['page'] %} class="isactive"{% endif %}><a href="{{ item['href'] }}"><span class="text">{{ item['title'] }}</span></a></li>
{% endfor %}

				</ul>
		</div>
		{% endif %}
		{% if plugin_menu is not empty %}
		
		<div class="box">
		<span class="title"><span class="text">{{ lang_admin_common['Plugins menu'] }}</span></span>
				<ul>
{% for item in plugin_menu %}
<li{% if page == item['page'] %} class="isactive"{% endif %}><a href="{{ item['href'] }}">{{ item['title'] }}</a></li>
{% endfor %}
				</ul>
		</div>
		{% endif %}
		
		<a class="go-top" href="#top"></a>
	</div>