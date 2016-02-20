{% for category in categories %}
<div id="idx{{ loop.index }}" class="block bforum">
	<h2 class="blocktitle">{{ category['name'] }}</h2>
<div class="box">
			<div class="row th">
					<div class="col forum">{{ lang_common['Forum'] }}</div>
					<div class="col topics-count">{{ lang_common['Topics'] }}</div>
					<div class="col posts-count">{{ lang_common['Posts'] }}</div>
					<div class="col last-post">{{ lang_common['Last post'] }}</div>
			</div>

			{% for forum in forums if forum['cid'] == category['cid'] %}
			{% set icon_type = 'icon' %}
			{% set item_status = '' %}

			{% if new_posts[forum['fid']] is defined %}
			{% set item_status = item_status ~ ' inew' %}
			{% set icon_type = 'icon icon-new' %}
			{% endif %}

			{% if forum['redirect_url'] is not empty %}
			{% set item_status = item_status ~ ' iredirect' %}
						{% set icon_type = icon_type ~ ' icon-redirect' %}
			{% endif %}

			{% for subforum in forum['subforum_list'] %}
				{% if new_posts[subforum['fid']] is defined %}
					{% if 'inew' not in item_status %}
						{% set item_status = item_status ~ ' inew' %}
						{% set icon_type = 'icon icon-new' %}
					{% endif %}
				{% endif %}
			{% endfor %}
				<div class="row tr {% if loop.index is divisible by (2) %}roweven{% else %}rowodd{% endif %}{% if item_status is not empty %}{{ item_status }}{% endif %}">
					
					<div class="col indicator"><div class="{{ icon_type }}"><div class="nosize">{{ loop.index }}</div>{% if topic['closed'] %}<span class="icon-closed"></span>{% endif %}</div></div>
					<div class="col content">
						
						{% if forum['redirect_url'] is not empty %}
						<h3><span class="redirtext">{{ lang_index['Link to'] }}</span> <a href="{{ forum['redirect_url'] }}" title="{{ lang_index['Link to'] }} {{ forum['redirect_url'] }}">{{ forum['forum_name'] }}</a></h3>
						{% else %}
						<h3><a href="{{ forum['link'] }}">{{ forum['forum_name'] }}</a>{% if new_posts[forum['fid']] is defined %} <a href="{{ forum['search_forum'] }}" class="newtext">{{ lang_common['New posts'] }}</a>{% endif %}</h3>
						{% endif %}
						{% if forum['forum_desc'] is not null %}
						<p class="forumdesc">{{ forum['forum_desc']|raw }}</p>
						{% if forum['moderators'] is not empty %}
						<p class="modlist">(<em>{{ lang_common['Moderated by'] }}</em> {{ forum['moderators']|join(', ')|raw }})</p>
						{% endif %}

							{% if forum['subforum_list'] is not empty %}
						<div class="subforum"><ul><li class="label">{{ lang_index['Sub forums'] }}</li>
							{% for subforum in forum['subforum_list'] %}
						<li><a class="subforum_name" href="{{ subforum['link'] }}">{{ subforum['name'] }}</a></li>{% if not loop.last %}{% endif %}
							{% endfor %}
						</div>
						{% endif %}
						{% endif %}
							
						</div>
			
					<div class="col topics-count">{{ forum['num_topics'] }}</div>
					<div class="col posts-count">{{ forum['num_posts'] }}</div>
					<div class="col last-post">
					{% if forum['show_post_info'] == '0' %}
					<strong>{{ lang_common['Protected forum'] }}</strong>
					{% elseif forum['last_post'] is not empty %}
					<div class="avatar">{{ forum['last_post_avatar']|raw }}</div>
					
					<div class="info">
					<span class="row date"><a href="{{ forum['last_post_link'] }}" class="postlink">{{ forum['last_post'] }}</a> </span>
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
</div>

{% else %}
<div id="idx0" class="block bforum">
<div class="box">
<div class="inbox">
<p>{{ lang_index['Empty board'] }}</p>
</div>
</div>
</div>
{% endfor %}

{% if panther_user['is_guest'] == '0' %}

		<p class="subscribelink"><a href="{{ mark_read }}">{{ lang_common['Mark all as read'] }}</a></p>

{% endif %}

<div id="brdstats" class="brd-stats">
	<h4 class="blocktitle">{{ lang_index['Board info'] }}</h4>
	
<div class="box">
	      <div class="row statsboard">
		
				
				<div class="col users"><p class="label">{{ lang_index['No of users'] }}</p> <p class="value">{{ stats['total_users'] }}</p></div>
				<div class="col posts"><p class="label">{{ lang_index['Newest user'] }}</p> <p class="value">{{ stats['newest_user']|raw }}</p></div>
				<div class="col topics"><p class="label">{{ lang_index['No of topics'] }}</p> <p class="value">{{ stats['total_topics'] }}</p></div>
				<div class="col posts"><p class="label">{{ lang_index['No of posts'] }}</p> <p class="value">{{ stats['total_posts'] }}</p></div>
				

			</div>
			
			
					<div class="row statsusers">
			
		
				
			
{% if panther_config['o_users_online'] == '1' %}
				<div class="col useron"><p class="label">{{ lang_index['Users online'] }}</p> <p class="value">{{ (num_users) }}</p></div>
				<div class="col useron"><p class="label">{{ lang_index['Guests online'] }}</p> <p class="value">{{(num_guests) }}</p></div>
				<div class="col useron"><p class="label">{{ lang_index['Bots online'] }}</p> <p class="value">{{ (num_bots) }}</p></div>
	
	
				</div>	
			
		
	
			
			

			<div class="row online">
{% if num_users > 0 or num_bots > 0 %}
{% if num_users %}
			<div class="row">
				<span class="title">{{ lang_index['Online'] }}</span>
				{{ users|join(', ')|raw }}
			</div>
{% endif %}

{% if num_bots %}
			<div class="row">
				<span class="title">{{ lang_index['Bots online'] }}</span></dt>
				{{ bots|join(', ') }}
			</div>

		
{% endif %}
{% endif %}
			
			<div class="row legend">
{% if groups is not empty %}

	<span class="title">{{ lang_index['Legend'] }}</span>
	{% for group in groups %}
	<li>{% if panther_user['g_view_users'] == '1' %}<a href="{{ group['link'] }}">{{ group['title']|raw }}</a>{% else %}{{ group['title']|raw }}{% endif %}</li>
	{% endfor %}

{% endif %}
	    </div>
{% endif %}
</div>


	
	
</div>
</div>


<div class="row bforum indic-list">
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
<div class="icon icon-redirect"></div>
<p>{{ lang_common['Redirect'] }}</p>
</div>

<div class="col indicator">
<div class="icon icon-new"></div>
<p>{{ lang_common['New posts'] }}</p>
</div>

</div>