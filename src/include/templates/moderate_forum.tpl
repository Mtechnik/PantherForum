<div class="linkst">
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ forum_link }}">{{ forum['forum_name'] }}</a></li>
			<li><span>»&#160;</span>{{ lang_misc['Moderate'] }}</li>
		</ul>
		<div class="pagepost">
			<ul class="pagination">{{ pagination|raw }}</ul>
		</div>
</div>


<form method="post" action="{{ form_action }}">
<div class="block btopics">
	<h2 class="blocktitle">{{ forum['forum_name'] }}</h2>
	<div class="box">
		<div class="inbox">

				<div class="row th">
					<div class="col forum">{{ lang_common['Topic'] }}</div>
					<div class="col counter">{{ lang_common['Replies'] }}</div>
					{% if panther_config['o_topic_views'] == '1' %}<div class="col counter">{{ lang_forum['Views'] }}</div>{% endif %}
					<div class="col last-post">{{ lang_common['Last post'] }}</div>
					<div class="col select">{{ lang_misc['Select'] }}</div>
				</div>
		
			
{% for topic in topics %}
			{% set icon_type = 'icon' %}
			{% set item_status = '' %}
			
			{% if topic['sticky'] == '1' %}
			{% set item_status = item_status ~ ' isticky' %}
			{% endif %}
			
			{% if panther_config['o_popular_topics'] != 0 and topic['num_replies'] >= panther_config['o_popular_topics'] %}
			{% set item_status = item_status ~ ' ipopular' %}
			{% endif %}
			
			{% if topic['closed'] == 1 %}
			{% set item_status = item_status ~ ' iclosed' %}
			{% endif %}
			
			{% if topic['moved_to'] %}
			{% set item_status = item_status ~ ' imoved' %}
			{% endif %}
			
			{% if panther_user['is_guest'] == '0' and panther_config['o_show_dot'] == '1' and cur_topic['has_posted'] == panther_user['id'] %}
			{% set item_status = item_status ~ ' iposted' %}
			{% endif %}
			
			{% if topic['new'] == '1' %}
			{% set item_status = item_status ~ ' inew' %}
			{% set icon_type = 'icon icon-new' %}
			{% endif %}
				<div class="row tr {% if loop.index is divisible by (2) %}roweven{% else %}rowodd{% endif %}{% if item_status is not empty %}{{ item_status }}{% endif %}">
					<div class="col indicator">
						<div class="{{ icon_type }}"><div class="nosize">{{ loop.index }}</div></div>
					</div>
					
					<div class="col content">
						<div class="topiclabels">
						
								{% if topic['sticky'] == '1' %}
								<span class="stickytext">{{ lang_forum['Sticky'] }}</span>
								{% endif %}
								{% if topic['moved_to'] != 0 %}
								<span class="movedtext">{{ lang_forum['Moved'] }}</span>
								{% elseif topic['closed'] == '1' %}
								<span class="closedtext">{{ lang_forum['Closed'] }}</span>
								{% endif %}
								
								{% if panther_user['is_guest'] == '0' and panther_config['o_show_dot'] == '1' and cur_topic['has_posted'] == panther_user['id'] %}<strong class="ipost">·&#160;{% endif %}
								{% if topic['new'] == '1' %}{% endif %}<a href="{{ topic['topic_link'] }}">{% if topic['question'] != '' %}{{ lang_forum['Poll']|format(topic['subject']) }}{% else %}{{ topic['subject'] }}{% endif %}</a>{% if topic['new'] == '1' %}{% endif %}
								{% if topic['num_pages'] > 1 %}<span class="pagestext">[ {{ topic['pagination']|raw }} ]</span>{% endif %}{% if topic['new'] == '1' %}<span class="newtext">[ <a href="{{ topic['new_link'] }}">{{ lang_common['New posts'] }}</a> ]</span>{% endif %}<span class="byuser">{{ lang_common['by'] }} {{ topic['topic_poster']|raw }}</span>
						
						</div>
					</div>
					<div class="col counter">{% if topic['moved_to'] is empty %}{{ topic['num_replies'] }}{% else %}-{% endif %}</div>
					{% if panther_config['o_topic_views'] == '1' %}<div class="col counter">{% if topic['moved_to'] is empty %}{{ topic['num_views'] }}{% else %}-{% endif %}</div>{% endif %}
					<div class="col last-post">
					{% if topic['moved_to'] is empty %}
					<span class="byuser_avatar">{{ topic['last_post_avatar']|raw }}</span><a href="{{ topic['last_post_link'] }}">{{ topic['last_post'] }}</a> <span class="byuser">{{ lang_common['by'] }} {{ topic['last_poster']|raw }}</span>
					{% else %}
					- - -
					{% endif %}
					</div>
					<div class="col select"><input type="checkbox" name="topics[{{ topic['cur_topic']['id'] }}]" value="1" /></div>
				</div>
{% else %}
<tr><td class="tcl" colspan="{% if panther_config['o_topic_views'] == '1' %}5{% else %}4{% endif %}">{{ lang_forum['Empty forum'] }}</td></tr>
{% endfor %}
		
		</div>
	</div>
</div>

			
			<div class="modbuttons">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}"/>
			<input type="submit" name="move_topics" value="{{ lang_misc['Move'] }}"{% if topics is empty %} disabled="disabled"{% endif %} class="btn normal"/>
			<input type="submit" name="delete_topics" value="{{ lang_misc['Delete'] }}"{% if topics is empty %} disabled="disabled"{% endif %} class="btn delete"/>
			<input type="submit" name="merge_topics" value="{{ lang_misc['Merge'] }}"{% if topics is empty %} disabled="disabled"{% endif %} class="btn normal"/>
			<input type="submit" name="open" value="{{ lang_misc['Open'] }}"{% if topics is empty %} disabled="disabled"{% endif %} class="btn normal"/>
			<input type="submit" name="close" value="{{ lang_misc['Close'] }}"{% if topics is empty %} disabled="disabled"{% endif %} class="btn normal"/>
			{% if panther_user['is_admin'] %}
			<input type="submit" name="archive_topics" value="{{ lang_misc['Archive'] }}"{% if topics is empty %} disabled="disabled"{% endif %} class="btn normal"/>
			<input type="submit" name="unarchive_topics" value="{{ lang_misc['Unarchive'] }}"{% if topics is empty %} disabled="disabled"{% endif %} class="btn normal"/>{% endif %}
			</div>
<div class="linksb">
<div class="pagepost">
       <ul class="pagination">{{ pagination|raw }}</ul>
	   </div>
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ forum_link }}">{{ forum['forum_name'] }}</a></li>
			<li><span>»&#160;</span>{{ lang_misc['Moderate'] }}</li>
		</ul>
</div>
</form>