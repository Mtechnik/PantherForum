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
{% endif %}
<div class="blockform">
	<h2><span>{{ lang_poll['Edit poll'] }}</span></h2>
	<div class="box">
		<form id="post" method="post" action="{{ form_action }}" onsubmit="return process_form(this)">
			<div class="inform">
				<fieldset>
					<legend>{% if cur_topic['type'] == 1 %}{{ lang_poll['New poll legend'] }}{% else %}{{ lang_poll['New poll legend multiselect'] }}{% endif %}</legend>
					<div class="infldset">
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
						<label><strong>{{ lang_poll['Question'] }}</strong><br /><input type="text" name="req_question" value="{{ cur_topic['question'] }}" size="80" maxlength="70" tabindex="1" /><br /><br /></label>
						{% for option in options %}
						<label><strong>{{ lang_poll['Option'] }}</strong><br /> <input type="text" name="options[{{ loop.index0 }}]" value="{{ option }}" size="60" maxlength="55" /><br /></label>
						{% endfor %}
					</div>
				</fieldset>
			</div>
			<p class="buttons"><input type="submit" name="submit" value="{{ lang_common['Submit'] }}" accesskey="s" /></p>
		</form>
	</div>
</div>