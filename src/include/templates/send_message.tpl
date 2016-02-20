<div class="linkst">
	<div class="inbox crumbsplus">
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ inbox_link }}">{{ lang_common['PM'] }}</a></li>
			<li><span>»&#160;</span><strong>{{ lang_pm['Send message'] }}</strong></li>
		</ul>
		<div class="pagepost"></div>
		<div class="clearer"></div>
	</div>
</div>
{{ pm_menu|raw }}

<div class="content">
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
{% elseif preview %}
<div id="postpreview" class="block bpostpreview">
	<h2><span>{{ lang_post['Post preview'] }}</span></h2>
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
	<div id="postform" class="block bform">
		<h2><span>{{ lang_pm['Send message'] }}</span></h2>
		<div class="box">
			<form id="post" method="post" action="{{ form_action }}" onsubmit="return process_form(this)">
			
					<fieldset>
						<legend>{{ lang_pm['Submit message legend'] }}</legend>
						<div class="infldset txtarea">
							<input name="form_sent" value="1" type="hidden" />
							<input name="csrf_token" type="hidden" value="{{ csrf_token }}" />
							{% if tid == '' %}
								<label class="conl required"><strong>{{ lang_pm['Send to'] }} <span>{{ lang_common['Required'] }}</span></strong>
							<br /><input type="text" name="req_username" value="{{ username }}" size="25" tabindex="1" /></label>
							<div class="clearer"></div>
							<label class="required"><strong>{{ lang_common['Subject'] }} <span>{{ lang_common['Required'] }}</span></strong>
							<input class="longinput" type="text" name="req_subject" value="{{ subject }}" size="80" maxlength="70" tabindex="2" /></label>
							{% endif %}
							<label class="required"><strong>{{ lang_common['Message'] }} <span>{{ lang_common['Required'] }}</span></strong><br />
							<textarea name="req_message" class="scedit_bbcode" rows="20" cols="95" tabindex="3">{{ message }}</textarea></label>
							<ul class="bblinks">
								<li><span><a href="{{ quickpost_links['bbcode'] }}" onclick="window.open(this.href); return false;">{{ lang_common['BBCode'] }}</a> {% if panther_config['p_message_bbcode'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
								<li><span><a href="{{ quickpost_links['url'] }}" onclick="window.open(this.href); return false;">{{ lang_common['url tag'] }}</a> {% if panther_config['p_message_bbcode'] == '1' and panther_user['g_post_links'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
								<li><span><a href="{{ quickpost_links['img'] }}" onclick="window.open(this.href); return false;">{{ lang_common['img tag'] }}</a> {% if panther_config['p_message_bbcode'] == '1' and panther_config['p_message_img_tag'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
								<li><span><a href="{{ quickpost_links['smilies'] }}" onclick="window.open(this.href); return false;">{{ lang_common['Smilies'] }}</a> {% if panther_config['o_smilies'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
							</ul>
						</div>
					</fieldset>
				
					<fieldset>
						<legend>{{ lang_common['Options'] }}</legend>
						<div class="infldset">
							<div class="rbox">
								<label><input type="checkbox" name="hide_smilies" value="1" tabindex="4" />{{ lang_pm['Hide smilies'] }}<br /></label>
							</div>
						</div>
					</fieldset>
				
				{% if robot_id is not none %}
		
				<fieldset>
					<legend>{{ lang_common['Robot title'] }}</legend>
					<div class="infldset">
						<p>{{ lang_common['Robot info'] }}</p>
						<label class="required"><strong>{{ robot_test['question'] }}
					 	<span>{{ lang_common['Required'] }}</span></strong>
				 		<input name="answer" id="answer" type="text" size="10" maxlength="30" /><input name="id" value="{{ robot_id }}" type="hidden" /><br />
						</label>
					</div>
				</fieldset>
				{% endif %}
				
				<p class="buttons"><input type="submit" id="submit" name="submit" value="{{ lang_common['Submit'] }}" tabindex="5" accesskey="s" />  <input type="submit" id="preview" name="preview" value="{{ lang_pm['Preview'] }}" tabindex="7" accesskey="p" /> <a href="javascript:history.go(-1)">{{ lang_common['Go back'] }}</a></p>
			</form>
		</div>
	</div>
	
</div>
</div><!-- .pm-console -->