<div class="linkst">
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ forum_link }}">{{ cur_topic['forum_name'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ topic_link }}">{{ cur_topic['subject'] }}</a></li>
			<li><span>»&#160;</span>{{ lang_misc['Moderate'] }}</li>
		</ul>
		<div class="pagepost">
			<ul class="pagination">{{ pagination|raw }}</ul>
		</div>
		
</div>

<form method="post" action="{{ form_action }}">
{% for post in posts %}
<div id="p{{ post['id'] }}" class="block bpost {% if post['id'] == post['first_post_id'] %} firstpost{% endif %} {% if loop.index is divisible by (2) %}roweven{% else %}rowodd{% endif %}{% if loop.first %} blockpost1{% endif %}">
	
	<div class="box">
	
			<div class="post-body">
				<div class="postprofile">
					
						{{ post['poster']|raw }}
						{{ post['user_title'] }}
					
				</div>
				<div class="postcontent">
					<div class="postinfo"><div class="conl"><a href="{{ post['post_link'] }}">{{ post['posted'] }}</a></div><div class="conr">#{{ post['count'] }}</div></div>
					<div class="postmsg">
					{{ post['message']|raw }}
						{% if post['edited'] != '' %}<p class="postedit"><em>{{ lang_topic['Last edit'] }}  {{ post['edited_by'] }} ({{ post['edited'] }})</em></p>{% endif %}
					</div>
				</div>
			</div>
	
	
			<div class="post-foot">
				<div class="postfootright">{% if post['id'] != post['first_post_id'] %}<p class="multidelete"><label>{{ lang_misc['Select'] }}&#160;<input type="checkbox" name="posts[{{ post['id'] }}]" value="1" /></label></p>{% else %}<p>{{ lang_misc['Cannot select first'] }}</p>{% endif %}</div>
			</div>
	
	</div>
</div>
{% endfor %}

<div class="modbuttons"><input type="submit" name="split_posts" value="{{ lang_misc['Split'] }}"{% if cur_topic['num_replies'] == 0 %} disabled="disabled"{% endif %} class="btn normal"/><input type="submit" name="delete_posts" value="{{ lang_misc['Delete'] }}"{% if cur_topic['num_replies'] == 0 %} disabled="disabled"{% endif %} class="btn delete"/></div>
			
</form>

<div class="linksb">

		<div class="pagepost">
			<ul class="pagination">{{ pagination|raw }}</ul>
		</div>
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ forum_link }}">{{ cur_topic['forum_name'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ topic_link }}">{{ cur_topic['subject'] }}</a></li>
			<li><span>»&#160;</span>{{ lang_misc['Moderate'] }}</li>
		</ul>
		

</div>