<div class="content">
	<div class="block">
	
		<h2>{{ lang_admin_users['Move users'] }}</h2>
		<div class="box">
			<form name="confirm_move_users" method="post" action="{{ form_action }}">
				<input type="hidden" name="users" value="{{ user_ids|join(', ') }}" />
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<div class="inform">
					<fieldset>
						<legend>{{ lang_admin_users['Move users subhead'] }}</legend>
						<div class="infldset">
							<table class="aligntop">
								<tr>
									<th scope="row">{{ lang_admin_users['New group label'] }}</th>
									<td>
										<select name="new_group" tabindex="1">
{% for group in group_options %}
<option value="{{ group['id'] }}">{{ group['title'] }}</option>
{% endfor %}
										</select>
										<span>{{ lang_admin_users['New group help'] }}</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend"><input type="submit" name="move_users_comply" value="{{ lang_admin_common['Save'] }}" tabindex="2" /></p>
			</form>
		</div>

</div>
</div><!-- .admin-console -->