<div class="content">
	<div class="block">
		<h2>{{ lang_admin_announcements['announcements'] }}</h2>
		<div class="box">
			<form method="post" action="{{ form_action }}">
				<input type="hidden" name="form_sent" value="1" />
				<input name="csrf_token" type="hidden" value="{{ csrf_token }}" />
				<input type="hidden" name="action" value="{{ action }}" />
				{% if action == 'edit' %}<input name="id" type="hidden" value="{{ id }}" />{% endif %}
			
					<fieldset>
						<legend>{{ lang_admin_announcements['announcements header'] }}</legend>
						<div class="inbox">
							
							
								<div class="row">
									<div class="col label">{{ lang_admin_announcements['title'] }}</div>
									<div class="col inputs">
										<input type="text" value="{{ cur_announce['subject'] }}" name="title" size="45" maxlength="50" tabindex="1" />
										<span>{{ lang_admin_announcements['title help'] }}</span>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_announcements['forum'] }}</div>
									<div class="col inputs">
										<select multiple="multiple" size="10" name="forums[]">
											<option value="0"{% if cur_announce['forum_id'] == 0 %} selected="selected"{% endif %}>{{ lang_admin_announcements['all forums']|raw }}</option>
{% for category in categories %}
<optgroup label="{{ category['cat_name'] }}">
{% for forum in forums if forum['category_id'] == category['id'] %}
<option value="{{ forum['id'] }}"{% if forum['selected'] %} selected="selected"{% endif %}>{{ forum['forum_name'] }}</option>
{% endfor %}
</optgroup>
{% endfor %}
											</select>
										<span>{{ lang_admin_announcements['forum help'] }}</span>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_announcements['announcement'] }}</div>
									<div class="col inputs">
										<textarea rows="20" cols="80" name="message">{{ cur_announce['message'] }}</textarea>
										<span>{{ lang_admin_announcements['announcement help']|format(help_link)|raw }}</span>
									</div>
								</div>
						
						</div>
					</fieldset>
				
				<input type="submit" name="submit" value="{{ lang_common['Submit'] }}" tabindex="43" />
			</form>
		</div>
	</div>

</div>
</div><!-- .admin-console -->