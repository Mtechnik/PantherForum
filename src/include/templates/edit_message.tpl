<div class="linkst">
	<div class="inbox crumbsplus">
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ inbox_link }}">{{ lang_common['PM'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ post_link }}">{{ cur_topic['subject'] }}</a></li>
			<li><span>»&#160;</span><strong>{{ lang_pm['Edit message'] }}</strong></li>
		</ul>
		<div class="pagepost"></div>
		<div class="clearer"></div>
	</div>
</div>
{{ pm_menu|raw }}
{% if errors is not empty %}
<div id="posterror" class="block">
	<h2><span>{{ lang_post['Post errors'] }}</span></h2>
	<div class="box">
		<div class="inbox error-info">
			<p>{{ lang_post['Post errors info'] }}</p>
			<ul class="error-list">
{% for error in errors %}
<li><strong>{{ error }}</strong></li>
{% endfor %}
			</ul>
		</div>
	</div>
</div>
<br />
{% endif %}
<div id="editform" class="blockform">
	<h2><span>{{ lang_post['Edit post'] }}</span></h2>
	<div class="box">
		<form id="edit" method="post" action="{{ form_action }}" onsubmit="return process_form(this)">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
			<div class="inform">
				<fieldset>
					<legend>{{ lang_post['Edit post legend'] }}</legend>
					<input type="hidden" name="form_sent" value="1" />
					<div class="infldset txtarea">
					{% if can_edit_subject %}
	<label class="conl required"><strong>{{ lang_pm['Send to'] }} <span>{{ lang_common['Required'] }}</span></strong>
							<br /><input type="text" name="req_username" value="{{ username }}" size="25" tabindex="1" /><br /></label>
							<div class="clearer"></div>
							<label class="required"><strong>{{ lang_common['Subject'] }} <span>{{ lang_common['Required'] }}</span></strong>
							<br /><input class="longinput" type="text" name="req_subject" value="{{ subject }}" size="80" maxlength="70" tabindex="2" /><br /></label>
					{% endif %}
						<label class="required"><strong>{{ lang_common['Message'] }} <span>{{ lang_common['Required'] }}</span></strong><br />
						<textarea class="scedit_bbcode" name="req_message" rows="20" cols="95" tabindex="3">{{ message }}</textarea><br /></label>
						<ul class="bblinks">
							<li><span><a href="{{ quickpost_links['bbcode'] }}" onclick="window.open(this.href); return false;">{{ lang_common['BBCode'] }}</a> {% if panther_config['p_message_bbcode'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
							<li><span><a href="{{ quickpost_links['url'] }}" onclick="window.open(this.href); return false;">{{ lang_common['url tag'] }}</a> {% if panther_config['p_message_bbcode'] == '1' and panther_user['g_post_links'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
							<li><span><a href="{{ quickpost_links['img'] }}" onclick="window.open(this.href); return false;">{{ lang_common['img tag'] }}</a> {% if panther_config['p_message_bbcode'] == '1' and panther_config['p_message_img_tag'] == '1' %}{{ lang_common['on'] }}{% else %}}{{ lang_common['off'] }}{% endif %}</span></li>
							<li><span><a href="{{ quickpost_links['smilies'] }}" onclick="window.open(this.href); return false;">{{ lang_common['Smilies'] }}</a> {% if panther_config['o_smilies'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
						</ul>
					</div>
				</fieldset>
				</div>
			{% if checkboxes is not empty %}
			<div class="inform">
				<fieldset>
					<legend>{{ lang_common['Options'] }}</legend>
					<div class="infldset">
						<div class="rbox">
							{% for checkbox in checkboxes %}
							<label><input type="checkbox" name="{{ checkbox['name'] }}" value="1"{% if checkbox['checked'] %} checked="checked"{% endif %} />{{ checkbox['title'] }}<br /></label>
							{% endfor %}
						</div>
						{% if is_admmod %}
<input class="input" type="text" name="edit_reason" size="40" maxlength="50" tabindex="3" onkeyup="if (this.value != '') document.getElementById('silent_edit').checked=false;" value="{{ edit_reason }}" /><br /><br />
{% endif %}
					</div>
				</fieldset>
{% endif %}
				<p class="buttons"><input type="submit" name="submit" id="submit" value="{{ lang_common['Submit'] }}" tabindex="5" accesskey="s" /> <a href="javascript:history.go(-1)">{{ lang_common['Go back'] }}</a></p>
			</form>
		</div>
	</div>
</div>