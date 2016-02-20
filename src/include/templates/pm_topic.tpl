{{ pm_menu|raw }}

<div class="main">

<div class="linkst">

		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ inbox_link }}">{{ lang_common['PM'] }}</a></li>
			<li><span>»&#160;</span><strong>{{ cur_topic['subject'] }}</strong></li>
		</ul>

		<div class="pagepost">
		<ul class="pagination"> {{ pagination|raw }}</ul>
		
		<div class="postlink">{% if cur_topic['fid'] != '3' %}<a href="{{ reply_link }}" class="btn reply">{{ lang_pm['Add reply'] }}</a>{% endif %}</div>
		</div>
</div>

<h1 class="topic-title">{{ cur_topic['subject'] }}</h1>
{% for post in posts %}
<div class="block">
		<div id="p{{ post['id'] }}" class="bpost {% if loop.index is divisible by (2) %}roweven{% else %}rowodd{% endif %}{% if loop.first %} firstpost blockpost1{% endif %}">

			
			<div class="post-body">
						<div class="postprofile">
						

						
						
						<div class="postavatar">{{ post['avatar']|raw }}</div>
						
						<div>{{ post['username']|raw }}</div>
						<div class="usertitle"><strong>{{ post['user_title'] }}</strong></div>
						
						{% if panther_config['o_reputation'] == '1' %}<div><span class="reputation {{ post['poster_reputation']['type'] }}">{{ post['poster_reputation']['title'] }}</span></div>{% endif %}
						
						{% if post['group_image'] is not empty %}<div class="postavatar"><img src="{{ post['group_image']['src'] }}" {{ post['group_image']['size']|raw }} alt="{{ post['group_image']['alt'] }}" /></div>{% endif %}
						{% for info in post['user_info'] %}
						<div>{% if info['href'] is not none %}<a href="{{ info['href'] }}"{% if info['label'] is not none %} title="{{ info['label'] }}"{% endif %}>{{ info['title'] }}</a>{% else %}{{ info['title'] }} {{ info['value'] }}{% endif %}</div>
						{% endfor %}
						
						{% if post['user_contacts'] is not empty %}
						<div class="usercontacts">
						{% for contact in post['user_contacts'] %}
						<span class="{{ contact['class'] }}"><a href="{{ contact['href'] }}"{% if contact['rel'] is not none %}rel="{{ contact['rel'] }}"{% endif %}>{{ contact['title'] }}</a></span>
						{% endfor %}
						</div>
						{% endif %}
				
				        </div>
				<div class="postcontent">
			<div class="postinfo">
						<span class="conl"><a href="{{ post['link'] }}">{{ post['posted'] }}</a></span>
			<span class="conr">#{{ post['number'] }}</span> 

			</div>
			
					<div class="postmsg">
						{{ post['message']|raw }}
						{% if post['edited'] != '' %}
						<p class="postedit"><em>{{ lang_topic['Last edit'] }} {{ post['edited_by'] }} ({{ post['edited'] }})</em></p>
						{% if post['edit_reason'] != ''%}
						<p class="postedit">{{ lang_topic['Edit reason']|format(post['edit_reason'])|raw }}</p>
						{% endif %}
						{% endif %}
					</div>
					{% if post['signature'] != '' %}<div class="postsignature postmsg"><hr />{{ post['signature']|raw }}</div>{% endif %}
				</div>
			</div>
	
			<div class="post-foot">
				<div class="postfootleft">
				
				{% if post['poster_id'] == post['is_online'] %}{{ lang_topic['Online'] }}{% else %}{{ lang_topic['Offline'] }}{% endif %}</div>
				
				{% if post['post_actions'] is not empty %}
				<div class="postfootright">
					<ul class="postactions">
				{% for action in post['post_actions'] %}
				<li class="post{{ action['class'] }}"><span{% if action['span_id'] is not none %} id="{{ action['span_id'] }}"{% endif %}{% if action['span_class'] is not none %} class="{{ action['span_class'] }}"{% endif %}>{% if action['href'] is not none %}<a href="{{ action['href'] }}">{{ action['title'] }}</a>{% else %}{{ action['title'] }}{% endif %}</span></li>
				{% endfor %}
					</ul>
				</div>
				{% endif %}
			</div>
	
</div>
</div>
{% endfor %}



<div class="linksb">
	<div class="pagepost">
		<ul class="pagination">{{ pagination|raw }}</ul> 
		
		<div class="postlink">{% if cur_topic['fid'] != '3' %}<a href="{{ reply_link }}" class="btn reply">{{ lang_pm['Add reply'] }}</a>{% endif %}</div>
	</div>
</div>

{% if quickpost %}
<div id="quickpost" class="block">
	<h2 class="blocktitle">{{ lang_topic['Quick post'] }}</h2>
	<div class="box">
		<form id="quickpostform" method="post" action="{{ quickpost_links['form_action'] }}" onsubmit="this.submit.disabled=true;if(process_form(this)){return true;}else{this.submit.disabled=false;return false;}">
			<div class="inbox">
		
					<!--<legend>{{ lang_common['Write message legend'] }}</legend>-->
					<div class="infldset txtarea">
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
						<label><textarea name="req_message" class="scedit_bbcode" rows="7" cols="75"></textarea></label>
						<ul class="bblinks">
							<li><span><a href="{{ quickpost_links['bbcode'] }}" onclick="window.open(this.href); return false;">{{ lang_common['BBCode'] }}</a> {% if panther_config['p_message_bbcode'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
							<li><span><a href="{{ quickpost_links['url'] }}" onclick="window.open(this.href); return false;">{{ lang_common['url tag'] }}</a> {% if panther_config['p_message_bbcode'] == '1' and panther_user['g_post_links'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
							<li><span><a href="{{ quickpost_links['img'] }}" onclick="window.open(this.href); return false;">{{ lang_common['img tag'] }}</a> {% if panther_config['p_message_bbcode'] == '1' and panther_config['p_message_img_tag'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
							<li><span><a href="{{ quickpost_links['smilies'] }}" onclick="window.open(this.href); return false;">{{ lang_common['Smilies'] }}</a> {% if panther_config['o_smilies'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
						</ul>
					</div>
		
			</div>
			<p class="buttons"><input type="submit" name="submit" id="submit" value="{{ lang_common['Submit'] }}" accesskey="s" /> <input type="submit" name="preview" id="preview" value="{{ lang_topic['Preview'] }}" accesskey="p" /></p>
		</form>
	</div>
</div>
{% endif %}

</div>
</div><!-- .pm-console -->