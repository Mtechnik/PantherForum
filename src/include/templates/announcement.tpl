<div class="linkst">

		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			{% if cur_announcement['parent'] %}<li><span>»&#160;</span><a href="{{ parent_link }}">{{ cur_announcement['parent'] }}</a></li>{% endif %}
			<li><span>»&#160;</span><a href="{{ forum_link }}">{{ cur_announcement['forum_name'] }}</a></li>
			<li><span>»&#160;</span><strong><a href="{{ announce_link }}">{{ cur_announcement['subject'] }}</a></strong></li>
		</ul>

</div>

	<h1>{{ cur_announcement['subject'] }}</h1>
<div class="block bpost roweven firstpost">

	
	
			<div class="post-body">
				<div class="postprofile">
					
						{{ username|raw }}
						
						{{ user_title }}
						
						{% if panther_config['o_reputation'] == '1' %}
						<span class="reputation {{ cur_announcement['reputation']['type'] }}">{{ cur_announcement['reputation']['title'] }}</span>
						{% endif %}
						
						{% if user_avatar is not empty %}
						{{ user_avatar|raw }}
						{% endif %}
						
						{% if group_image is not empty %}
					    <img src="{{ group_image['src'] }}" {{ group_image['size'] }} alt="{{ group_image['alt'] }}" />
						{% endif %}
						
						{% for info in user_info %}
						<span>{% if info['href'] is not none %}<a href="{{ info['href'] }}"{% if info['label'] is not none %} title="{{ info['label'] }}"{% endif %}>{{ info['title'] }}</a>{% else %}{{ info['title'] }} {{ info['value'] }}{% endif %}</span>
						{% endfor %}
						
						{% if user_contacts is not empty %}
						
						{% for contact in post['user_contacts'] %}
						<span class="{{ contact['class'] }}"><a href="{{ contact['href'] }}"{% if contact['rel'] is not none %}rel="{{ contact['rel'] }}"{% endif %}>{{ contact['title'] }}</a></span>
						{% endfor %}
						
						{% endif %}
					
				</div>
				<div class="postcontent">
					<h3>{{ cur_announcement['subject'] }}</h3>
					<div class="postmsg">
						{{ message|raw }}
					</div>
				</div>
			</div>
		
	
			<div class="post-foot">
{% if post_actions is not empty %}

<ul>
{% for action in post_actions %}
<li class="post{{ action['class'] }}"><span><a href="{{ action['href'] }}">{{ action['title'] }}</a></span></li>
{% endfor %}
</ul>

{% endif %}
</div>
	
</div>