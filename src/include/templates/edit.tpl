<div class="linkst">
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ forum_link }}">{{ cur_post['forum_name'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ topic_link }}">{{ cur_post['subject'] }}</a></li>
			<li><span>»&#160;</span><strong>{{ lang_post['Edit post'] }}</strong></li>
		</ul>
</div>

{% if errors is not empty %}
			<div class="block berror">
				<div class="box">
					<legend>{{ lang_post['Post errors'] }}</legend>
					<div class="inbox error-info infldset">
						<p>{{ lang_post['Post errors info'] }}</p>
							<ul class="error-list">
{% for error in errors %}
<li><strong>{{ error|raw }}</strong></li>
{% endfor %}
							</ul>
					</div>
				</div>
			</div>
			
{% elseif preview %}
<div id="postpreview" class="block bpost">
	<h2 class="blocktitle">{{ lang_post['Post preview'] }}</h2>
	<div class="box">
		<div class="inbox">
			<div class="postbody">
				<div class="postright">
					<div class="postmsg">
						{{ preview|raw }}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
{% endif %}
<div class="block bedit">
	<h2 class="blocktitle">{{ lang_post['Edit post legend'] }}</h2>
	
		<form id="edit" method="post" action="{{ form_action }}" enctype="multipart/form-data" onsubmit="return process_form(this)">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
			<div class="box">
			
					<!--<legend>{{ lang_post['Edit post'] }}</legend>-->
					<input type="hidden" name="form_sent" value="1" />
					<div class="inbox">
					{% if can_edit_subject %}
						<!--<label class="required"><strong>{{ lang_common['Subject'] }} <span>{{ lang_common['Required'] }}</span></strong>-->
						<input class="longinput" type="text" name="req_subject" size="80" maxlength="70" tabindex="1" value="{{ subject }}" /></label>
{% endif %}
						<!--<label class="required"><strong>{{ lang_common['Message'] }} <span>{{ lang_common['Required'] }}</span></strong>-->
						<textarea name="req_message" class="scedit_bbcode" rows="20" cols="95" tabindex="2">{{ message }}</textarea></label>
						<ul class="bblinks">
							<li><span><a href="{{ quickpost_links['bbcode'] }}" onclick="window.open(this.href); return false;">{{ lang_common['BBCode'] }}</a> {% if panther_config['p_message_bbcode'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
							<li><span><a href="{{ quickpost_links['url'] }}" onclick="window.open(this.href); return false;">{{ lang_common['url tag'] }}</a> {% if panther_config['p_message_bbcode'] == '1' and panther_user['g_post_links'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
							<li><span><a href="{{ quickpost_links['img'] }}" onclick="window.open(this.href); return false;">{{ lang_common['img tag'] }}</a> {% if panther_config['p_message_bbcode'] == '1' and panther_config['p_message_img_tag'] == '1' %}{{ lang_common['on'] }}{% else %}}{{ lang_common['off'] }}{% endif %}</span></li>
							<li><span><a href="{{ quickpost_links['smilies'] }}" onclick="window.open(this.href); return false;">{{ lang_common['Smilies'] }}</a> {% if panther_config['o_smilies'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
						</ul>
					</div>
				
				{% if can_upload or can_delete %}
			</div>
			<div class="box">
			
					<p class="boxtitle">{{ lang_post['Attachment'] }}</p>
					<div class="row">
					{% if can_delete %}
					{{ lang_post['Attachment existing delete'] }}
					{% else %}
					{{ lang_post['Attachment existing nodelete'] }}
					{% endif %}
					{% if can_upload %}
					{% if panther_user['g_max_attachments'] == '0' %}
					{{ lang_post['Upload']|format('<em>unlimited</em>')|raw }}
					{% else %}
					{{ lang_post['Upload']|format(panther_user['g_max_attachments']) }}
					{% endif %}
					{% for attach in attachments %}
					{% if can_delete %}
					<input type="checkbox" name="attach_delete[{{ loop.index0 }}]" value="{{ attach['id'] }}" />{{ lang_post['Delete attachment'] }} <img src="{{ attach['icon']['file'] }}" height="15" width="15" alt="{{ attach['icon']['extension'] }}" /> <a href="{{ attach['link'] }}">{{ attach['name'] }}</a>, {{ attach['size'] }}, {{ attach['downloads'] }}
					{% else %}
					<img src="{{ attach['icon']['file'] }}" height="15" width="15" alt="{{ attach['icon']['extension'] }}" /> <a href="{{ attach['link'] }}">{{ attach['name'] }}</a> {{ attach['size'] }}, {{ attach['downloads'] }}
					{% endif %}
					
					{% endfor %}
					<input type="hidden" name="MAX_FILE_SIZE" value="{{ max_size }}" /><input type="file" name="attached_file" size="80" />
					{% endif %}
					
						{{ lang_post['Note'] }}
					</div>
		
{% endif %}
{% if checkboxes is not empty %}
			</div>
			<div class="box">
			
					<p class="boxtitle">{{ lang_common['Options'] }}</p>
					<div class="row">
						<ul class="checklist">
							{% for checkbox in checkboxes %}
							<li><label><input type="checkbox" name="{{ checkbox['name'] }}"{% if checkbox['id'] is not none %}id="{{ checkbox['id'] }}"{% endif %} value="1"{% if checkbox['checked'] %} checked="checked"{% endif %} />{{ checkbox['title'] }}</label></li>
							{% endfor %}
						</ul>
						{% if is_admmod %}
<input class="input" type="text" name="edit_reason" size="40" maxlength="50" tabindex="3" onkeyup="if (this.value != '') document.getElementById('silent_edit').checked=false;" value="{{ edit_reason }}" />
{% endif %}
					</div>
			
{% endif %}
			</div>
			<div class="blockbuttons">
			<div class="conl"><a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a></div>
			<div class="conr"><input type="submit" name="preview" value="{{ lang_post['Preview'] }}" tabindex="5" accesskey="p" class="btn preview"/><input type="submit" id="submit" value="{{ lang_common['Submit'] }}" tabindex="4" accesskey="s" class="btn submit"/></div>
			</div>
		</form>
	
</div>