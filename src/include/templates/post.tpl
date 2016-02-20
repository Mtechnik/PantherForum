<div class="linkst">
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ forum_link }}">{{ cur_posting['forum_name'] }}</a></li>
			{% if cur_posting['subject'] is not none %}
			<li><span>»&#160;</span><a href="{{ forum_link }}">{{ cur_posting['subject'] }}</a></li>
			{% elseif POST['req_subject'] is not none %}
			<li><span>»&#160;</span>{{ POST['req_subject'] }}</li>
			{% endif %}
			<li><span>»&#160;</span>{{ action }}</li>
		</ul>
</div>


{% if errors is not empty %}
<div class="block berror">
	<h2 class="blocktitle">{{ lang_post['Post errors'] }}</h2>
	<div class="box">
	
			<p>{{ lang_post['Post errors info'] }}</p>
			<ul class="error-list">
			{% for error in errors %}
			<li><strong>{{ error }}</strong></li>
			{% endfor %}
			</ul>
	
	</div>
</div>
{% elseif POST['preview'] is not none %}
<div id="postpreview" class="block bpostpreview">
	<h2  class="blocktitle">{{ lang_post['Post preview'] }}</h2>

			<div class="postbody">
				<div class="postright">
					<div class="postmsg">
						{{ preview|raw }}
					</div>
				</div>
			</div>
</div>
{% endif %}

<div id="postform" class="block bnewpost">
	<h2 class="blocktitle">{{ action }}</h2>
	
		<form id="post" method="post" action="{{ post_link }}" onsubmit="{% if tid %}return process_form(this){% else %}this.submit.disabled=true;if(process_form(this)){return true;}else{this.submit.disabled=false;return false;}{% endif %}" enctype="multipart/form-data">
			
			
					<div class="box">
				
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
						<div class="row head">
						{% if fid %}
						<div class="col">
						<label for="req_subject">{{ lang_common['Subject'] }} <span class="required">{{ lang_common['Required'] }}</span></label>
						<input id="req_subject" class="longinput" type="text" name="req_subject" value="{{ POST['req_subject'] }}" maxlength="70" />
						</div>
						{% endif %}
						
						
						{% if panther_user['is_guest'] %}
						<div class="col">
						<label for="username" class="required">{{ lang_post['Guest name'] }} <span class="required">{{ lang_common['Required'] }}</span></label>
						<input id="username" type="text" name="req_username" value="{% if POST['req_username'] is not none %}{{ username }}{% endif %}" maxlength="25" />
						</div>
						
						<div class="col">
						<label for="req_email" class="{% if panther_config['p_force_guest_email'] == '1' %} required{% endif %}">{% if panther_config['p_force_guest_email'] == '1' %}{{ lang_common['Email'] }} <span>{{ lang_common['Required'] }}</span>{% else %}{{ lang_common['Email'] }}{% endif %}</label>
						<input id="req_email" type="email" name="{{ email_form_name }}" value="{{ email }}" maxlength="80" />
						</div>
						{% endif %}
						</div>
						<div class="row">
						<!--<label for="req_message" class="required">{{ lang_common['Message'] }} <span class="required">{{ lang_common['Required'] }}</span></label>-->
						<textarea id="req_message" name="req_message" class="scedit_bbcode">{{ message }}</textarea>
						
						<ul class="bblinks">
							<li><span><a href="{{ quickpost_links['bbcode'] }}" onclick="window.open(this.href); return false;">{{ lang_common['BBCode'] }}</a> {% if panther_config['p_message_bbcode'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
							<li><span><a href="{{ quickpost_links['url'] }}" onclick="window.open(this.href); return false;">{{ lang_common['url tag'] }}</a> {% if panther_config['p_message_bbcode'] == '1' and panther_user['g_post_links'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
							<li><span><a href="{{ quickpost_links['img'] }}" onclick="window.open(this.href); return false;">{{ lang_common['img tag'] }}</a> {% if panther_config['p_message_bbcode'] == '1' and panther_config['p_message_img_tag'] == '1' %}{{ lang_common['on'] }}{% else %}}{{ lang_common['off'] }}{% endif %}</span></li>
							<li><span><a href="{{ quickpost_links['smilies'] }}" onclick="window.open(this.href); return false;">{{ lang_common['Smilies'] }}</a> {% if panther_config['o_smilies'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
						</ul>
							</div>
					
					
			
			       </div>	
				
				{% if can_upload %}
				<div class="box">
					<p class="boxtitle">{{ lang_post['Attachment'] }}</p>
				   <div class="row">
						<input type="hidden" name="MAX_FILE_SIZE" value="{{ max_size }}" /><input type="file" name="attached_file" />
						
					</div>
					<div class="row"><span class="info">{{ lang_post['Note'] }}</span></div>
			    </div>
				{% endif %}
				
				
				
				{% if checkboxes is not empty %}
			<div class="box">
					<p class="boxtitle">{{ lang_common['Options'] }}</p>
					
						<ul class="checklist">
							{% for checkbox in checkboxes %}
<li><label><input type="checkbox" name="{{ checkbox['name'] }}" value="1"{% if checkbox['checked'] %} checked="checked"{% endif %} />{{ checkbox['title'] }}</label></li>
							{% endfor %}
						</ul>
					</div>

				{% endif %}
		
		
		
				{% if robot_id is not none %}
			
					
					<div class="box">
					<p class="boxtitle">{{ lang_common['Robot title'] }}</p>
						<p>{{ lang_common['Robot info'] }}</p>
						<label for="robot_test" class="required">{{ test['question'] }} <span class="required">{{ lang_common['Required'] }}</span></label>
				 		<input id="robot_test" name="answer" id="answer" type="text" maxlength="30" /><input name="id" value="{{ robot_id }}" type="hidden" />
						
					</div>
	
				{% endif %}
				
			<div class="blockbuttons"><div class="conl"><a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a></div><div class="conr"><input type="submit" id="preview" name="preview" value="{{ lang_post['Preview'] }}" accesskey="p" class="btn preview"/><input type="submit" id="submit" name="submit" value="{{ lang_common['Submit'] }}" accesskey="s"  class="btn submit"/></div></div>
		</form>
	</div>

{% if tid and panther_config['o_topic_review'] != '0' %}
<div id="postreview">
	<h2>{{ lang_post['Topic review'] }}</h2>
{% for post in posts %}
	<div class="blockpost">
		<div class="box {% if loop.index is divisible by (2) %}roweven{% else %}rowodd{% endif %}">
		
				<div class="postbody">
					<div class="postleft">
						
							{{ post['username']|raw }}
							{{ post['posted'] }}
						
					</div>
					
					<div class="postright">
						<div class="postmsg">
							{{ post['message']|raw }}
						</div>
					</div>
					
				</div>
			
		</div>
	</div>
{% endfor %}
</div>
{% endif %}