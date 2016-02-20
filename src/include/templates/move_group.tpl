<div class="content">
	<div class="block">
	
		<h2>{{ lang_admin_groups['Delete group head'] }}</h2>
		<div class="box">
			<form id="groups" method="post" action="{{ form_action }}">
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<div class="inform">
					<fieldset>
						<legend>{{ lang_admin_groups['Move users subhead'] }}</legend>
						<div class="infldset">
							<p>{{ lang_admin_groups['Move users info']|format(group_title, group_members) }}</p>
							<label>{{ lang_admin_groups['Move users label'] }}
							<select name="move_to_group">
{% for option in group options %}
<option value="{{ option['id'] }}"{% if option['selected'] %} selected="selected"{% endif %}>{{ option['title'] }}</option>
{% endfor %}
							</select>
							</label>
						</div>
					</fieldset>
				</div>
				<p class="buttons"><input type="submit" name="del_group" value="{{ lang_admin_groups['Delete group'] }}" /><a href="javascript:history.go(-1)">{{ lang_admin_common['Go back'] }}</a></p>
			</form>
		</div>
	</div>
	
</div>
</div><!-- .admin-console -->