<div class="linkst">
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			{% if cur_topic['parent'] %}<li><span>»&#160;</span><a href="{{ parent_link }}">{{ cur_topic['parent'] }}</a></li>{% endif %}
			<li><span>»&#160;</span><a href="{{ forum_link }}">{{ cur_topic['forum_name'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ topic_link }}">{{ cur_topic['subject'] }}</a></li>
		</ul>
		<div class="pagepost">
			<ul class="pagination">{{ pagination|raw }}</ul>
<div class="postlink">
{% if cur_topic['archived'] == '1' %}
<span class="btn archived">{{ lang_topic['Topic archived'] }}</span>
{% else %}
{% if cur_topic['closed'] == '0' %}
{% if (cur_topic['post_replies'] == '' and panther_user['g_post_replies'] == '1') or cur_topic['post_replies'] == '1' or is_admmod %}
<a href="{{ reply_link }}" class="btn reply">{{ lang_topic['Post reply'] }}</a>
{% endif %}
{% else %}
<span class="btn closed">{{ lang_topic['Topic closed'] }}</span>{% if is_admmod %}<a href="{{ reply_link }}" class="btn reply">{{ lang_topic['Post reply'] }}</a>{% endif %}
{% endif %}
{% endif %}
</div>
		</div>
</div>


<h1 class="topic-title">{{ cur_topic['subject'] }}</h1>
{% if cur_topic['question'] != '' %}
{% if can_vote %}
<div class="block bpoll">
					<p class="blocktitle">{{ lang_topic['Poll']|format(cur_topic['question']) }}</p>
<div class="box">

		<form id="post" method="post" action="{{ poll_action }}">		
				

						<input type="hidden" name="poll_id" value="{{ id }}" />
						<input type="hidden" name="form_sent" value="1" />
						<input name="csrf_token" value="{{ csrf_token }}" type="hidden" />
{% for i in 0..options|length-1 %}
						<div class="row tr">
							<label>
								{% if cur_topic['type'] == '1' %}
								<input name="vote"{% if loop.first %} checked="checked"{% endif %} type="radio" value="{{ loop.index0 }}" />
								{% else %}
								<input name="options[{{ loop.index0 }}]" type="checkbox" value="1" />
								{% endif %}
								{{ options[i] }}
							</label>
						</div>
{% endfor %}
			
				<div class="buttons"><input type="submit" name="submit" tabindex="2" value="{{ lang_common['Submit'] }}" class="btn submit" accesskey="s" /></div>		
		</form>
</div>
</div>
{% else %}
<div class="block bpoll">
<p class="blocktitle">{{ lang_topic['Poll']|format(cur_topic['question']) }}</p>
<div class="box">
			
				
					
				
{% for poll in options %}
							<div class="row tr">
								<div class="col label">{{ poll['value'] }}</div>
								<div class="col valuebar"><span style="width:{{ poll['percent'] }}%;"></span></div>
								<div class="col valuetext"><span class="percent">{{ poll['vote'][0] }}%</span> <span class="numvoters">({{ poll['vote'][1] }})</span></div>
							</div>
{% endfor %}
							<div class="row tr">
								<div class="col voters">{{ lang_topic['Voters']|format(total_voters) }}</div>
							</div>
						

			
				<div class="postactions"><ul>
				{% if panther_user['username'] == cur_topic['poster'] or is_admmod %}
{% for action in poll_actions %}
<li class="post{{ action['class'] }}"><span><a href="{{ action['href'] }}">{{ action['lang'] }}</a></span></li>
{% endfor %}
{% endif %}
				</ul></div>
</div>		
</div>
{% endif %}

{% endif %}




{% for post in posts %}
<div id="p{{ post['id'] }}" class="block bpost {% if loop.index is divisible by (2) %}roweven{% else %}rowodd{% endif %}{% if post['id'] == cur_topic['first_post_id'] %} firstpost{% endif %}{% if loop.first %} blockpost1{% endif %}">

		
			<div class="post-body">
			
				<div class="postprofile">
					    
						{% if post['user_avatar'] is not empty %}
						<span class="row postavatar {% if post['poster_id'] != 1 and post['poster_id'] == post['is_online'] %}online{% else %}offline{% endif %}"><span class="image">{{ post['user_avatar']|raw }}</span></span>
						{% endif %}
						
						<div class="usercard">
						<span class="row username">{{ post['username']|raw }}</span>
						<span class="row usertitle">{{ post['user_title'] }}</span>
						</div>
						<div class="userinfos">
						{% if panther_config['o_reputation'] == '1' and post['poster_id'] != 1 %}
						<span class="row reputation {{ post['poster_reputation']['type'] }}">{{ post['poster_reputation']['title'] }}</span>
						{% endif %}
						
						{% if post['group_image'] is not empty %}<span class="row postavatar"><img src="{{ post['group_image']['src'] }}" {{ post['group_image']['size']|raw }} alt="{{ post['group_image']['alt'] }}" /></span>{% endif %}
						
						
						{% for info in post['user_info'] %}
						<span class="row">{% if info['href'] is not none %}<a href="{{ info['href'] }}"{% if info['label'] is not none %} title="{{ info['label'] }}"{% endif %}>{{ info['title'] }}</a>{% else %}{{ info['title'] }} {{ info['value'] }}{% endif %}</span>
						{% endfor %}
						
						{% if post['user_contacts'] is not empty %}
						<span class="row usercontacts">
						{% for contact in post['user_contacts'] %}
						<span class="row {{ contact['class'] }}"><a href="{{ contact['href'] }}"{% if contact['rel'] is not none %} rel="{{ contact['rel'] }}"{% endif %}>{{ contact['title'] }}</a></span>
						{% endfor %}
						</span>
						{% endif %}
						</div>
					
				</div>
				
				<div class="postcontent">
					<div class="postinfo"><span class="conl"><a href="{{ post['link'] }}">{{ post['posted'] }}</a></span><span class="conr">#{{ post['number'] }}</span>
					<span class="topic-smalltitle">{% if post['id'] != cur_topic['first_post_id'] %}{{ lang_topic['Re'] }} {% endif %}<span class="{% if post['id'] == cur_topic['first_post_id'] %}1{% else %}2{% endif %}">{{ cur_topic['subject'] }}</span></span></div>
					
					
					<div class="postmsg">
						{{ post['message']|raw }}
						
						{% if post['edited'] != '' %}
						<div class="boxedit"><span class="info">{{ lang_topic['Last edit'] }} {{ post['edited_by'] }} ({{ post['edited'] }})</span>
						{% if post['edit_reason'] != ''%}
						<span class="reason">{{ lang_topic['Edit reason']|format(post['edit_reason']) }}</span>
						{% endif %}
						</div>
						{% endif %}
						
						{% if post['attachments'] is not empty %}
						<ul class="boxattachments">
						<li class="atttitle">{{ lang_topic['Attachments'] }}</li>
						{% for attach in post['attachments'] %}
						<li class="attlink"><a href="{{ attach['link'] }}">{{ attach['name'] }}</a></li><li class="attsize">{{ attach['size'] }}</li> <li class="attdownload">{{ attach['downloads'] }}</li>
						{% endfor %}
						</ul>
						{% endif %}
					</div>
					{% if post['signature'] != '' %}
					<div class="postsign">{{ post['signature']|raw }}</div>
					{% endif %}
				</div>
				
			</div>
		
		
			<div class="post-foot">
				<div class="postfootleft">{% if post['poster_id'] != 1 and post['poster_id'] == post['is_online'] %}<span class="online">{{ lang_topic['Online'] }}</span>{% else %}<span class="offline">{{ lang_topic['Offline'] }}</span>{% endif %}</div>
				{% if post['post_actions'] is not empty %}
				
				<div class="postfootright">
					<ul class="postactions">
					
				{% for action in post['post_actions'] %}
				<li class="post{{ action['class'] }}">
				<span{% if action['span_id'] is not none %} id="{{ action['span_id'] }}"{% endif %}{% if action['span_class'] is not none %} class="{{ action['span_class'] }}"{% endif %}>
				{% if action['href'] is not none %}<a href="{{ action['href'] }}">{{ action['title'] }}</a>{% else %}<span class="title">{{ action['title'] }}</span>{% endif %}
				
				{% if post['actions'] is not empty and action['actions'] %}
				{% for action in post['actions'] %}
				    <span data-token="{{ csrf_token }}" data-vote="{{ action['onclick'] }}" data-id="{{ post['id'] }}" id="vote" class="{{ action['class'] }}" ></span>{% if not loop.last %}{% endif %}
				{% endfor %}
				{% endif %}
				</span></li>
				{% endfor %}
					</ul>
					
				</div>
				{% endif %}
				
			</div>
		
	
</div>
{% endfor %}



{% if panther_user['is_guest'] == '0' and panther_config['o_topic_subscriptions'] == '1' %}
{% if cur_topic['is_subscribed'] %}
<div class="subscribelink"><span class="subscribed">{{ lang_topic['Is subscribed'] }}</span><a href="{{ subscription_link }}">{{ lang_topic['Unsubscribe'] }}</a></div>
{% else %}
<div class="subscribelink"><a href="{{ subscription_link }}">{{ lang_topic['Subscribe'] }}</a></div>
{% endif %}
{% endif %}



{% if panther_config['o_users_online'] == '1' %}
<div class="topicstats">
{% if guests <= 1 and guests != 0 %}
			<p>{{ lang_online['online topic']|format(guests, lang_online['Guest'], users)|raw }}</p>
{% else %}
			<p>{{ lang_online['online topic']|format(guests, lang_online['Guests'], users)|raw }}</p>
{% endif %}
</div>
{% endif %}

<div class="linksb">
	
		<div class="pagepost">
			<ul class="pagination">{{ pagination|raw }}</ul>
<div class="postlink">
{% if cur_topic['archived'] == '1' %}
{{ lang_topic['Topic archived'] }}
{% else %}
{% if cur_topic['closed'] == '0' %}
{% if (cur_topic['post_replies'] == '' and panther_user['g_post_replies'] == '1') or cur_topic['post_replies'] == '1' or is_admmod %}
<a href="{{ reply_link }}" class="btn reply">{{ lang_topic['Post reply'] }}</a>
{% endif %}
{% else %}
<span class="btn closed">{{ lang_topic['Topic closed'] }}</span>{% if is_admmod %}<a href="{{ reply_link }}" class="btn reply">{{ lang_topic['Post reply'] }}</a>{% endif %}
{% endif %}
{% endif %}
</div>
		</div>



		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			{% if cur_topic['parent'] %}<li><span>»&#160;</span><a href="{{ parent_link }}">{{ cur_topic['parent'] }}</a></li>{% endif %}
			<li><span>»&#160;</span><a href="{{ forum_link }}">{{ cur_topic['forum_name'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ topic_link }}">{{ cur_topic['subject'] }}</a></li>
		</ul>

</div>



{% if quickpost %}
<div id="quickpost" class="block bquickpost">
	<h2 class="blocktitle">{{ lang_topic['Quick post'] }}</h2>
	<div class="box">
		<form id="quickpostform" method="post" action="{{ quickpost_links['form_action'] }}" onsubmit="this.submit.disabled=true;if(process_form(this)){return true;}else{this.submit.disabled=false;return false;}">

					<div class="box">
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="csrf_token" value="{{ quickpost_links['csrf_token'] }}" />
						{% if panther_config['o_topic_subscriptions'] == '1' and (panther_user['auto_notify'] == '1' or cur_topic['is_subscribed']) %}
						<input type="hidden" name="subscribe" value="1" />
						{% endif %}
						{% if panther_user['is_guest'] %}
						<label class="conl required">{{ lang_post['Guest name'] }} <span>{{ lang_common['Required'] }}</span><input type="text" name="req_username" maxlength="25" tabindex="1" /></label>
						<label class="conl{% if panther_config['p_force_guest_email'] == '1' %}required{% endif %}">{% if panther_config['p_force_guest_email'] == '1' %}{{ lang_common['Email'] }}<span>{{ lang_common['Required'] }}</span>{% else %}{{ lang_common['Email'] }}{% endif %}<input type="text" name="{% if panther_config['p_force_guest_email'] == '1' %}req_email{% else %}email{% endif %}" size="50" maxlength="80" tabindex="2" /></label>
						{% endif %}
						<textarea name="req_message" class="scedit_bbcode" tabindex="3"></textarea>
						<ul class="bblinks">
							<li><span><a href="{{ quickpost_links['bbcode'] }}" onclick="window.open(this.href); return false;">{{ lang_common['BBCode'] }}</a> {% if panther_config['p_message_bbcode'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
							<li><span><a href="{{ quickpost_links['url'] }}" onclick="window.open(this.href); return false;">{{ lang_common['url tag'] }}</a> {% if panther_config['p_message_bbcode'] == '1' and panther_user['g_post_links'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
							<li><span><a href="{{ quickpost_links['img'] }}" onclick="window.open(this.href); return false;">{{ lang_common['img tag'] }}</a> {% if panther_config['p_message_bbcode'] == '1' and panther_config['p_message_img_tag'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
							<li><span><a href="{{ quickpost_links['smilies'] }}" onclick="window.open(this.href); return false;">{{ lang_common['Smilies'] }}</a> {% if panther_config['o_smilies'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
						</ul>
					</div>

		
			<div class="blockbuttons"><div class="conr"><input type="submit" id="preview" name="preview" value="{{ lang_topic['Preview'] }}" tabindex="5" accesskey="p" class="btn normal"/><input type="submit" id="submit" name="submit" tabindex="4" value="{{ lang_common['Submit'] }}" accesskey="s" class="btn submit"/></div></div>
		</form>
	</div>
</div>
{% endif %}