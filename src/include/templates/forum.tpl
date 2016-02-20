<div class="linkst">
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
{% if cur_forum['parent'] %}
<li><span>»&#160;</span><a href="{{ parent_link }}">{{ cur_forum['parent'] }}</a></li>
{% endif %}
			<li><span>»&#160;</span><strong><a href="{{ forum_link }}">{{ cur_forum['forum_name'] }}</a></strong></li>
		</ul>
		
				<div class="pagepost">
			<ul class="pagination">{{ pagination|raw }}</ul>
{% if ((cur_forum['post_topics'] == '' and panther_user['g_post_topics'] == '1') or cur_forum['post_topics'] == '1' or is_admmod) %}
<div class="postlink"><a href="{{ post_link }}" class="btn newtopic">{{ lang_forum['Post topic'] }}</a></div>
{% endif %}
		</div>
</div>


{% if forums is not empty %}
<div id="vfsubforum" class="block bsubforum">
	<h2 class="blocktitle">{{ lang_forum['Sub forums'] }}</h2>

		
			<div class="row th">
				
					<div class="col forum">{{ lang_common['Forum'] }}</div>
					<div class="col topics-count">{{ lang_common['Topics'] }}</div>
					<div class="col posts-count">{{ lang_common['Posts'] }}</div>
					<div class="col last-post">{{ lang_common['Last post'] }}</div>
			</div>
	
{% for forum in forums %}
			{% set icon_type = 'icon' %}
			{% set item_status = '' %}
			
			{% if forum['new'] %}
			{% set item_status = item_status ~ ' inew' %}
			{% set icon_type = 'icon icon-new' %}
			{% endif %}

			{% if forum['redirect_url'] is not empty %}
			{% set item_status = item_status ~ ' iredirect' %}
			{% set icon_type = icon_type ~ ' icon-redirect' %}
			{% endif %}

				<div class="row tr {% if loop.index is divisible by (2) %}roweven{% else %}rowodd{% endif %}{% if item_status is not empty %}{{ item_status }}{% endif %}">
					<div class="col indicator">
							<div class="{{ icon_type }}"><div class="nosize">{{ loop.index }}</div></div>
					</div>
					
					<div class="col content">
							<div class="content">
								{% if forum['redirect_url'] is not empty %}
								<h3><span class="redirtext">{{ lang_common['Link to'] }}</span> <a href="{{ forum['redirect_url'] }}" title="{{ lang_common['Link to'] }} {{ forum['redirect_url'] }}">{{ forum['forum_name'] }}</a></h3>
								{% else %}
								<h3><a href="{{ forum['link'] }}">{{ forum['forum_name'] }}</a>{% if forum['new'] %}<a href="{{ forum['search_link'] }}" class="newtext">{{ lang_common['New posts'] }}</a>{% endif %}</h3>
								{% endif %}
								{% if forum['forum_desc'] is not null %}
								<div class="forumdesc">{{ forum['forum_desc']|raw }}</div>
								{% endif %}
								{% if forum['moderators'] is not empty %}
								<p class="modlist">(<em>{{ lang_common['Moderated by'] }}</em> {{ forum['moderators']|join(', ')|raw }})</p>
								{% endif %}
							</div>
					
					</div>
					
					<div class="col topics-count">{{ forum['num_topics'] }}</div>
					<div class="col posts-count">{{ forum['num_posts'] }}</div>
					<div class="col last-post">
					{% if forum['show_post_info'] == '0' %}
					<strong>{{ lang_common['Protected forum'] }}</strong>
					{% elseif forum['last_post'] is not empty %}
					<div class="col avatar">
					{{ forum['last_post_avatar']|raw }}
					</div>
					<div class="col info">
					<span class="row date"><a href="{{ forum['last_post_link'] }}" class="postlink">{{ forum['last_post'] }}</a></span>
					<span class="row topic">{{ lang_common['In'] }} <a href="{{ forum['last_topic_link'] }}">{{ forum['last_topic'] }}</a></span>
					<span class="row byuser">{{ lang_common['by'] }} {{ forum['last_poster']|raw }}</span>
				    </div>
					{% elseif forum['redirect_url'] is not empty %}
					- - -
					{% else %}
					{{ lang_common['Never'] }}
					{% endif %}
					</div>
				</div>
{% endfor %}


</div>
{% endif %}



{% if announcements is not empty %}
<div id="vfannounce" class="block bannounce">
	<h2 class="blocktitle">{{ lang_forum['Forum announcements'] }}</h2>

{% for announce in announcements %}
				<div class="row tr {% if loop.index is divisible by (2) %}roweven{% else %}rowodd{% endif %} iannounce">
					<div class="col indicator">
						<div class="icon icon-announce"><div class="nosize"></div></div>
					</div>
					<div class="col content">
						<div class="tclcon">
						    <a href="{{ announce['link'] }}">{{ announce['subject'] }}</a> <span class="byuser">{{ lang_common['by'] }} {{ announce['user']|raw }}</span>
						</div>
					</div>
				</div>
{% endfor %}
</div>
{% endif %}


<div id="vf" class="block btopics">
	<h2 class="blocktitle">{{ cur_forum['forum_name'] }}</h2>

		
		
				<div class="row th">
					<div class="col topic">{{ lang_common['Topic'] }}</div>
					<div class="col replies-count">{{ lang_common['Replies'] }}</div>
					{% if panther_config['o_topic_views'] == '1' %}<div class="col views-count">{{ lang_forum['Views'] }}</div>{% endif %}
					<div class="col last-post">{{ lang_common['Last post'] }}</div>
				</div>
		
			
{% for topic in topics %}
			{% set icon_type = 'icon' %}
			{% set item_status = '' %}
			
			{% if topic['sticky'] == '1' %}
			{% set item_status = item_status ~ ' isticky' %}
			{% set icon_type = icon_type ~ ' icon-sticky' %}
			{% endif %}
			
			{% if panther_config['o_popular_topics'] != 0 and topic['num_replies'] >= panther_config['o_popular_topics'] %}
			{% set item_status = item_status ~ ' ipopular' %}
			{% endif %}
			
			{% if topic['closed'] == 1 %}
			{% set item_status = item_status ~ ' iclosed' %}
			{% endif %}
			
			{% if topic['moved_to'] %}
			{% set item_status = item_status ~ ' imoved' %}
			{% set icon_type = icon_type ~ ' icon-moved' %}
			{% endif %}
			
			{% if panther_user['is_guest'] == '0' and panther_config['o_show_dot'] == '1' and cur_topic['has_posted'] == panther_user['id'] %}
			{% set item_status = item_status ~ ' iposted' %}
			{% endif %}
			
			{% if topic['new'] == '1' %}
			{% set item_status = item_status ~ ' inew' %}
		   {% set icon_type = icon_type ~ ' icon-new' %}
			{% endif %}
				<div class="row tr {% if loop.index is divisible by (2) %}roweven{% else %}rowodd{% endif %}{% if item_status is not empty %}{{ item_status }}{% endif %}">
					<div class="col indicator">
						<div class="{{ icon_type }}"><div class="nosize">{{ loop.index }}</div>{% if topic['closed'] %}<span class="icon-closed"></span>{% endif %} {% if panther_config['o_popular_topics'] != 0 and topic['num_replies'] >= panther_config['o_popular_topics'] %}<span class="icon-popular"></span>{% endif %}</div>
					</div>
					<div class="col content">
					
							
								<div class="row topiclink">

								{% if topic['sticky'] or topic['moved_to'] or topic['closed'] or topic['question'] != '' %}
								<div class="topiclabels">
								{% if topic['sticky'] == '1' %}
								<span class="tlabel sticky">{{ lang_forum['Sticky'] }}</span>
								{% endif %}
								
								{% if topic['moved_to'] != 0 %}
								<span class="tlabel moved">{{ lang_forum['Moved'] }}</span>
								{% elseif topic['closed'] == '1' %}
								<span class="tlabel closed">{{ lang_forum['Closed'] }}</span>
								{% endif %}
								
								{% if topic['question'] != '' %}
								<span class="tlabel poll">{{ lang_forum['Poll'] }} </span>
								{% endif %}
								</div>
								{% endif %}
						
								{% if panther_user['is_guest'] == '0' and panther_config['o_show_dot'] == '1' and cur_topic['has_posted'] == panther_user['id'] %}<p class="ipost">·&#160;</p>{% endif %}
								
							
								
								{% if topic['new'] == '1' %}<strong>{% endif %}<a href="{{ topic['topic_link'] }}">{{ topic['subject'] }}</a>{% if topic['new'] == '1' %}</strong>{% endif %}
								{% if topic['new'] == '1' %}<a href="{{ topic['new_link'] }}" class="newtext">{{ lang_common['New posts'] }}</a>{% endif %}
								
								</div>
								
								<div class="row topicmore">
								<span class="byuser">{{ lang_common['by'] }} {{ topic['topic_poster']|raw }}</span>
								{% if topic['num_pages'] > 1 %}<ul class="pagination mini">{{ topic['pagination']|raw }}</ul>{% endif %}
								</div>
						
					</div>
					<div class="col replies-count">{% if topic['moved_to'] is empty %}{{ topic['num_replies'] }}{% else %}-{% endif %}</div>
					{% if panther_config['o_topic_views'] == '1' %}<div class="col views-count">{% if topic['moved_to'] is empty %}{{ topic['num_views'] }}{% else %}-{% endif %}</div>{% endif %}
					<div class="col last-post">
					{% if topic['moved_to'] is empty %}
					<div class="avatar">{{ topic['last_post_avatar']|raw }}</div>
					<div class="info">
					<span class="row date"><a href="{{ topic['last_post_link'] }}">{{ topic['last_post'] }}</a></span>
					<span class="row byuser">{{ lang_common['by'] }} {{ topic['last_poster']|raw }}</span>
					</div>
					{% else %}
					- - -
					{% endif %}

					</div>
				</div>
				
				
{% else %}


<div class="row tr rowodd inone">
					
						<div class="col">
							
								{{ lang_forum['Empty forum'] }}
						
						</div>
					
				</div>
{% endfor %}



</div>

		{% if forum_actions is not empty %}<div class="subscribelink">
		{% for action in forum_actions %}
		{% if action['info'] is not none %}<p class="subscribed">{{ action['info'] }}</p>{% endif %}<a href="{{ action['href'] }}">{{ action['title'] }}</a>
		{% if not loop.last %}{% endif %}
		{% endfor %}
		</div>{% endif %}









{% if panther_config['o_users_online'] == '1' %}
<div class="forumstats">
{% if guests <= 1 and guests != 0 %}
			<p>{{ lang_online['online forum']|format(guests, lang_online['Guest'], users)|raw }}</p>
{% else %}
			<p>{{ lang_online['online forum']|format(guests, lang_online['Guests'], users)|raw }}</p>
{% endif %}
</div>
{% endif %}
<div class="linksb">

	<div class="pagepost">
		<ul class="pagination">{{ pagination|raw }}</ul>
			
		{% if ((cur_forum['post_topics'] == '' and panther_user['g_post_topics'] == '1') or cur_forum['post_topics'] == '1' or is_admmod) %}
		<p class="postlink"><a href="{{ post_link }}" class="btn newtopic">{{ lang_forum['Post topic'] }}</a></p>
		{% endif %}
	</div>

		


		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
{% if cur_forum['parent'] %}
<li><span>»&#160;</span><a href="{{ parent_link }}">{{ cur_forum['parent'] }}</a></li>
{% endif %}
			<li><span>»&#160;</span><strong><a href="{{ forum_link }}">{{ cur_forum['forum_name'] }}</a></strong></li>
		</ul>
		
</div>




<div class="row bforum indic-list">
<div class="col indicator">
    <div class="icon icon-announce"></div>
	<p>{{ lang_common['Announcement'] }}</p>
</div>
<div class="col indicator">
    <div class="icon icon-sticky"></div>
	<p>{{ lang_common['Sticky'] }}</p>
</div>

<div class="col indicator">
    <div class="icon"></div>
	<p>{{ lang_common['No new posts'] }}</p>
</div>

<div class="col indicator">
<div class="icon">
<span class="icon-closed"></span>
</div>
<p>{{ lang_common['Closed'] }}</p>

</div>
<div class="col indicator">
<div class="icon">
<span class="icon-popular"></span>
</div>
<p>{{ lang_common['Popular'] }}</p>

</div>
<div class="col indicator">
<div class="icon icon-moved"></div>
<p>{{ lang_common['Moved'] }}</p>
</div>

<div class="col indicator">
<div class="icon icon-new"></div>
<p>{{ lang_common['New posts'] }}</p>
</div>

<div class="col indicator">
<div class="icon icon-sticky icon-new"></div>
<p>{{ lang_common['New sticky posts'] }}</p>
</div>
</div>