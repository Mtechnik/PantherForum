<div class="block">
		<h2><span>{{ lang_admin_moderate['actions'] }}</span></h2>
		<div class="box">
			<form id="restrictions2" method="post" action="{{ form_action }}">
				<input type="hidden" name="form_sent" value="1" />
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<div class="inform">
					<fieldset>
						<legend>{{ lang_admin_moderate['action header'] }}</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">{{ lang_admin_moderate['title'] }}</th>
									<td>
										<input type="text" value="{{ action['title'] }}" name="title" size="45" maxlength="50" tabindex="1" />
										<span>{{ lang_admin_moderate['title help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_moderate['add start']|raw }}</th>
									<td>
										<input type="text" value="{{ action['add_start'] }}" name="add_start" size="30" maxlength="50" tabindex="1" />
										<span>{{ lang_admin_moderate['add start help']|raw }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_moderate['add end']|raw }}</th>
									<td>
										<input type="text" value="{{ action['add_end'] }}" name="add_end" size="30" maxlength="50" tabindex="1" />
										<span>{{ lang_admin_moderate['add end help']|raw }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_moderate['forum'] }}</th>
									<td>
										<select name="forum">
											<option value="0">{{ lang_admin_moderate['do not move'] }}</option>
{% for category in categories %}
<optgroup label="{{ category['name'] }}">
{% for forum in forums if forum['category_id'] == category['id'] %}
<option value="{{ forum['id'] }}">{{ forum['name'] }}</option>
{% endfor %}
</optgroup>
{% endfor %}
										</select>
										<span>{{ lang_admin_moderate['forum help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_moderate['leave redirect'] }}</th>
									<td>
										<select name="redirect">
											<option value="0"{% if action['leave_redirect'] == '0' %} selected="selected"{% endif %}>{{ lang_admin_moderate['no redirect'] }}</option>
											<option value="1"{% if action['leave_redirect'] == '1' %} selected="selected"{% endif %}>{{ lang_admin_moderate['do redirect'] }}</option>

										</select>
										<span>{{ lang_admin_moderate['redirect help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_moderate['close'] }}</th>
									<td>
										<select name="close">
											<option value="2"{% if action['close'] == '2' %} selected="selected"{% endif %}>{{ lang_admin_moderate['do not alter'] }}</option>
											<option value="1"{% if action['close'] == '1' %} selected="selected"{% endif %}>{{ lang_admin_moderate['close topic'] }}</option>
											<option value="0"{% if action['close'] == '0' %} selected="selected"{% endif %}>{{ lang_admin_moderate['open topic'] }}</option>
										</select>
										<span>{{ lang_admin_moderate['close help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_moderate['stick'] }}</th>
									<td>
										<select name="stick">
											<option value="2"{% if action['stick'] == '2' %} selected="selected"{% endif %}>{{ lang_admin_moderate['do not alter'] }}</option>
											<option value="1"{% if action['stick'] == '1' %} selected="selected"{% endif %}>{{ lang_admin_moderate['stick topic'] }}</option>
											<option value="0"{% if action['stick'] == '0' %} selected="selected"{% endif %}>{{ lang_admin_moderate['unstick topic'] }}</option>
										</select>
										<span>{{ lang_admin_moderate['stick help'] }}</span>
									</td>
								</tr>
                                <tr>
									<th scope="row">{{ lang_admin_moderate['archive'] }}</th>
									<td>
										<select name="archive">
											<option value="2"{% if action['archive'] == '2' %} selected="selected"{% endif %}>{{ lang_admin_moderate['do not alter'] }}</option>
											<option value="1"{% if action['archive'] == '1' %} selected="selected"{% endif %}>{{ lang_admin_moderate['archive topic'] }}</option>
											<option value="0"{% if action['archive'] == '0' %} selected="selected"{% endif %}>{{ lang_admin_moderate['unarchive topic'] }}</option>
										</select>
										<span>{{ lang_admin_moderate['archive help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_moderate['increment post count'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="increment" value="1"{% if action['increment_posts'] == '1' %} checked="checked"{% endif %} tabindex="37" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="increment" value="0"{% if action['increment_posts'] == '0' %} checked="checked"{% endif %} tabindex="38" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_moderate['increment help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_moderate['email'] }}</th>
									<td>
										<label class="conl"><input type="radio" name="send_email" value="1"{% if action['send_email'] == '1' %} checked="checked"{% endif %} tabindex="37" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="send_email" value="0"{% if action['send_email'] == '0' %} checked="checked"{% endif %} tabindex="38" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<span class="clearb">{{ lang_admin_moderate['email help'] }}</span>
									</td>
								</tr>								
								<tr>
									<th scope="row">{{ lang_admin_moderate['message'] }}</th>
									<td>
										<textarea rows="20" cols="80" name="message">{{ action['reply_message'] }}</textarea>
										<span class="clearb">{{ lang_admin_moderate['message help'] }}</span>
									</td>
								</tr>

							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend"><input type="submit" name="submit" value="{{ lang_common['Submit'] }}" tabindex="43" /></p>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
</div>