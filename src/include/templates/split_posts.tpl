<div class="block">
	<h2 class="blocktitle">{{ lang_misc['Split posts'] }}</h2>
	
	
		<form id="subject" method="post" action="{{ form_action }}">
			<div class="box">
					<p class="boxtitle">{{ lang_misc['Confirm split legend'] }}</p>
					<div class="row">
						<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
						<input type="hidden" name="posts" value="{{ posts }}" />
						<label class="required"><strong>{{ lang_misc['New subject'] }} <span>{{ lang_common['Required'] }}</span></strong><br /><input type="text" name="new_subject" size="80" maxlength="70" /><br /></label>
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
						<p>{{ lang_misc['Split posts comply'] }}</p>
					</div>
		
			<p class="buttons"><input type="submit" name="split_posts_comply" value="{{ lang_misc['Split'] }}" /> <a href="javascript:history.go(-1)">{{ lang_common['Go back'] }}</a></p>
			</div>
		</form>

	
</div>