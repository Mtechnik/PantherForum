<div class="content">

<div class="block pagetitle">
<h1>{{ lang_admin_common['Announcements'] }}</h1>
</div>

	<div class="block">
		<h2>{{ lang_admin_announcements['announcements'] }} <span class="pages-label">{{ lang_common['Pages'] }}</span>{{ pagination|raw }}</h2>
		<div class="box">

				
				
						<div class="row th"><div class="col">{{ lang_admin_announcements['title'] }}</div></div>
						
							
								<div class="row tr">
								<div class="col label"><a href="{{ add_link }}" tabindex="1">{{ lang_admin_announcements['add new'] }}</a></div>
								<div class="col input">{{ lang_admin_announcements['add new label'] }}</div>
								</div>
{% for announce in announcements %}
<div class="row tr">
<div class="col label"><a href="{{ announce['edit_link'] }}" tabindex="9">{{ lang_admin_announcements['edit announcement'] }}</a> | <a href="{{ announce['delete_link'] }}" tabindex="10">{{ lang_admin_announcements['delete announcement 2'] }}</a></div>
<div class="col input">{{ announce['subject'] }} <span class="byuser">{{ lang_admin_announcements['by']|format(announce['poster'])|raw }}</span></div>
</div>
{% endfor %}
		</div>
	</div>
</div>
</div><!-- .admin-console -->