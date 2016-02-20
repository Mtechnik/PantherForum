<div class="content">

<div class="block pagetitle">
<h1>{{ lang_admin_common['Deleted'] }}</h1>
</div>

	<div class="block">
		<h2>{{ lang_admin_deleted['Deleted posts head'] }}</h2>

			<form method="post" action="{{ form_action }}">
			<input name="csrf_token" value="{{ csrf_token }}" type="hidden" />
{% for post in posts %}
<div class="box">
				
						<p class="boxtitle">{{ lang_admin_deleted['Post subhead']|format(post['posted']) }}</p>
						<div class="inbox">

								<div class="row">
									<div class="col label">{{ lang_admin_deleted['Posted by'] }} {% if post['poster'] is not empty %}<a href="{{ post['poster']['href'] }}">{{ post['poster']['poster'] }}</a>{% else %}{{ lang_admin_deleted['Deleted user'] }}{% endif %}</div>
									<div class="col inputs">
									<span>{% if post['forum'] is not empty %}<a href="{{ post['forum']['href'] }}">{{ post['forum']['forum_name'] }}</a>{% else %}{{ lang_admin_deleted['Deleted'] }}{% endif %}</span>
									<span>»&#160;{% if post['topic'] is not empty %}<a href="{{ post['topic']['href'] }}">{{ post['topic']['subject'] }}</a>{% else %}{{ lang_admin_deleted['Deleted'] }}{% endif %}</span>
									<span>»&#160;{% if post['post'] is not empty %}<a href="{{ post['post']['href'] }}">{{ post['post']['post'] }}</a>{% else %}{{ lang_admin_deleted['Deleted'] }}{% endif %}</span>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_deleted['Message'] }}
									
									<select name="action[{{ post['id'] }}]"><option value="1">{{ lang_admin_deleted['Restore'] }}</option>
									<option value="2">{{ lang_admin_deleted['Delete'] }}</option></select>
									<input type="submit" name="post_id[{{ post['id'] }}]" value="{{ lang_common['Submit'] }}" />
									
									</div>
									<div class="col inputs">{{ post['message']|raw }}</div>
								</div>
							
						</div>
					
				
					</div>
					
{% else %}
				
						<p class="boxtitle">{{ lang_admin_common['None'] }}</p>
						<div class="inbox">
							<p>{{ lang_admin_deleted['No new posts'] }}</p>
						</div>
				

{% endfor %}

			</form>
	
	</div>
	
</div>
</div><!-- .admin-console -->