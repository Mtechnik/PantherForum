<div class="block bmove">
	<h2 class="blocktitle">{% if action == 'single' %}{{ lang_misc['Move topic'] }}{% else %}{{ lang_misc['Move topics']  }}{% endif %}</h2>
	<div class="box">
		<form method="post" action="{{ form_action }}">
			<div class="inform">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
			<input type="hidden" name="topics" value="{{ topics }}" />
				
					<legend>{{ lang_misc['Move legend'] }}</legend>
					<div class="infldset">
						<label>{{ lang_misc['Move to'] }}
						<select name="move_to_forum">
{% for category in categories %}
<optgroup label="{{ category['name'] }}">
{% for forum in forums if forum['category_id'] == category['id'] %}
<option value="{{ forum['id'] }}"{% if fid == forum['id'] %} selected="selected"{% endif %}>{{ forum['name'] }}</option>
{% endfor %}
</optgroup>
{% endfor %}
							</optgroup>
						</select>
						</label>
						<div class="rbox">
							<label><input type="checkbox" name="with_redirect" value="1"{% if action == 'single' %} checked="checked"{% endif %} />{{ lang_misc['Leave redirect'] }}</label>
						</div>
					</div>
				
			</div>
			<div class="blockbuttons">
			<div class="conl"><a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a></div>
			<div class="conr"><input type="submit" name="move_topics_to" value="{{ lang_misc['Move'] }}" class="btn move"/></div>
			
			</div>
		</form>
	</div>
</div>