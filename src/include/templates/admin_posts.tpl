<div class="content">

<div class="block pagetitle">
<h1>{{ lang_admin_common['Unapproved Posts'] }}</h1>
</div>

	<div class="block">
		<h2>{{ lang_admin_posts['New posts head'] }}</h2>
		
			<form method="post" action="{{ form_action }}">
			<input name="csrf_token" value="{{ csrf_token }}" type="hidden" />
			
{% for post in posts %}
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_posts['Post subhead']|format(post['posted']) }}</p>
						<div class="inbox">
							
								<div class="row">
									<div class="col label">{{ lang_admin_posts['Posted by'] }} {% if post['poster'] is not empty %}<a href="{{ post['poster']['href'] }}">{{ post['poster']['poster'] }}</a>{% else %}{{ lang_admin_posts['Deleted user'] }}{% endif %}</div>
									<div class="col inputs">
									<span>{% if post['forum'] is not empty %}<a href="{{ post['forum']['href'] }}">{{ post['forum']['forum_name'] }}</a>{% else %}{{ lang_admin_posts['Deleted'] }}{% endif %}</span>
									<span>»&#160;{% if post['topic'] is not empty %}<a href="{{ post['topic']['href'] }}">{{ post['topic']['subject'] }}</a>{% else %}{{ lang_admin_posts['Deleted'] }}{% endif %}</span>
									<span>»&#160;{% if post['post'] is not empty %}<a href="{{ post['post']['href'] }}">{{ post['post']['post'] }}</a>{% else %}{{ lang_admin_posts['Deleted'] }}{% endif %}</span>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_posts['Message'] }}<select name="action[{{ post['id'] }}]"><option value="1">{{ lang_admin_posts['Approve'] }}</option><option value="2">{{ lang_admin_posts['Delete'] }}</option>{% if panther_user['is_admmod'] and (panther_user['g_mod_sfs_report'] == '1' or panther_user['is_admin']) and panther_config['o_sfs_api'] != '' %}<option value="3">{{ lang_admin_posts['Mark as spam'] }}</option>{% endif %}</select></div>
									<div class="col inputs">{{ post['message']|raw }}{% if post['attachments'] is not empty %}<div class="postsignature">
{% for attach in post['attachments'] %}
<br />{{ attach['icon']|raw }}<a href="{{ attach['link'] }}">{{ attach['name'] }}</a>, {{ attach['size'] }}, {{ attach['downloads'] }}
{% endfor %}</div>{% endif %}</div>
								</div>
					    </div>
					
					<input type="submit" name="post_id[{{ post['id'] }}]" value="{{ lang_common['Submit'] }}" />
				</div>

{% else %}
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_common['None'] }}</p>
						<div class="inbox">
							<div class="row">
									<div class="col">{{ lang_admin_posts['No new posts'] }}</div>
							</div>
						</div>
					
				</div>
{% endfor %}
			</form>
	</div>

</div>
</div><!-- .admin-console -->	