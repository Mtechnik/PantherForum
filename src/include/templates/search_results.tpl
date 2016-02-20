<div class="linkst">
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ search_link }}">{{ crumbs_text['show_as'] }}</a></li>
			<li><span>»&#160;</span><strong>{% if crumbs_text['search_type']['href'] is not none %}<a href="{{ crumbs_text['search_type']['href'] }}">{{ crumbs_text['search_type']['title'] }}</a>{% else %}{{ crumbs_text['search_type'] }}{% endif %}</strong></li>
		</ul>
		<div class="pagepost">
			<ul class="pagination">{{ pagination|raw }}</ul>
		</div>
</div>

{% if show_as == 'posts' %}
{% for post in results %}
<div class="block bpost {% if loop.index is divisible by (2) %}roweven{% else %}rowodd{% endif %}{% if post['post_id'] == post['first_post_id'] %} firstpost{% endif %}{% if post_count == 1 %} blockpost1{% endif %}{% if post['viewed'] == '0' %} inew{% endif %}">

	<div class="post-body">
		<div class="postprofile">	
			<ul class="usercard">
			    <li class="username">{{ post['poster']|raw }}</li>
			</ul>
						
			{% if post['post_id'] == post['first_post_id'] %}
			<ul class="userinfos">
				<li><span>{{ lang_topic['Replies'] }} {{ post['num_replies'] }}</span></li>
			</ul>
			{% endif %}
						
			<div class="{% if post['viewed'] == '0' %}icon icon-new{% else %}icon{% endif %}"><div class="nosize">{% if post['viewed'] == '0' %}{{ lang_topic['New icon'] }}{% else %}<!-- -->{% endif %}</div></div>			
	    </div>
		
		<div class="postcontent">
	        <div class="postinfo">
	            <span class="conl">{% if post['post_id'] != post['first_post_id'] %}{{ lang_topic['Re'] }} {% endif %}<a href="{{ post['forum']['url'] }}">{{ post['forum']['name'] }}</a></span> 
	            <span class="conr">#{{ post['post_no'] }}</span> 
	            <span class="topic-smalltitle"><a href="{{ post['topic_url'] }}">{{ post['subject'] }}</a></span> <span>»&#160;<a href="{{ post['post_url'] }}">{{ post['posted'] }}</a></span>
	        </div>
		    
			<div class="postmsg">
				{{ post['message']|raw }}
			</div>
		</div>
	</div>
	
	<div class="post-foot">
		<div class="postfootleft">
		</div>
		
		<div class="postfootright">
			<ul class="postactions">
				<li><a href="{{ post['topic_url'] }}">{{ lang_search['Go to topic'] }}</a></li>
				<li><a href="{{ post['post_url'] }}">{{ lang_search['Go to post'] }}</a></li>
		    </ul>
		</div>
	</div>
	
</div>
{% endfor %}
{% else %}
<div id="vf" class="block btopics">
	<h2 class="blocktitle">{{ lang_search['Search results'] }}</h2>
	
	<div class="row th">
		<div class="col topic">{{ lang_common['Topic'] }}</div>
		<div class="col fromforum">{{ lang_common['Forum'] }}</div>
		<div class="col views-count">{{ lang_common['Replies'] }}</div>
		<div class="col last-post">{{ lang_common['Last post'] }}</div>
	</div>
	
{% for topic in results %}
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
						<div class="inon {{ icon_type }}"><div class="nosize">{{ loop.index }}</div></div>
					</div>
					
					<div class="col content">

							
								{% if topic['sticky'] or topic['moved_to'] or topic['closed'] or topic['question'] != '' %}
								<div class="topiclabels">
								{% if topic['sticky'] == '1' %}
								<span class="stickytext">{{ lang_forum['Sticky'] }}</span>
								{% endif %}
								{% if topic['moved_to'] != 0 %}
								<span class="movedtext">{{ lang_forum['Moved'] }}</span>
								{% elseif topic['closed'] == '1' %}
								<span class="closedtext">{{ lang_forum['Closed'] }}</span>
								{% endif %}
								{% if topic['question'] != '' %}
								<span class="tlabel poll">{{ lang_forum['Poll'] }}</span>
								{% endif %}
                                  </div>
								  {% endif %}
								  
								<div class="row topiclink">{% if topic['new'] == '1' %}{% endif %}<a href="{{ topic['topic_link'] }}">{{ topic['subject'] }}</a>{% if topic['new'] == '1' %}{% endif %}
								</div>
								<div class="row topicmore"><span class="byuser">{{ lang_common['by'] }} {{ topic['topic_poster']|raw }}</span>
								{% if topic['num_pages'] > 1 %}<span class="pagination mini">{{ topic['pagination']|raw }}</span>{% endif %}{% if topic['new'] == '1' %}<span class="newtext"><a href="{{ topic['new_link'] }}">{{ lang_common['New posts'] }}</a></span>{% endif %}
					  	        </div>
						
					</div>
					
					<div class="col fromforum"><a href="{{ topic['forum']['url'] }}">{{ topic['forum']['name'] }}</a></div>
					<div class="col views-count">{{ topic['num_replies'] }}</div>
					<div class="col last-post">
					 <div class="avatar">{{ topic['last_post_avatar']|raw }}</div>
					 <div class="info">
					 <span class="row date"><a href="{{ topic['last_post_link'] }}">{{ topic['last_post'] }}</a></span>
					 <span class="row byuser">{{ lang_common['by'] }} {{ topic['last_poster']|raw }}</span>
					 </div>
					</div>
				</div>
{% endfor %}
			

</div>
{% endif %}
<div class="{% if show_as == 'topics' %}linksb{% else %}linksb{% endif %}">
		<div class="pagepost">
			<ul class="pagination">{{ pagination|raw }}</ul>
		</div>
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ search_link }}">{{ crumbs_text['show_as'] }}</a></li>
			<li><span>»&#160;</span><strong>{% if crumbs_text['search_type']['href'] is not none %}<a href="{{ crumbs_text['search_type']['href'] }}">{{ crumbs_text['search_type']['title'] }}</a>{% else %}{{ crumbs_text['search_type'] }}{% endif %}</strong></li>
		</ul>
{% if forum_actions is not empty %}
<p class="subscribelink">
{% for action in forum_actions %}
<a href="{{ action['href'] }}">{{ action['title'] }}</a>{% if not loop.last %} - {% endif %}
{% endfor %}
</p>
{% endif %}

</div>