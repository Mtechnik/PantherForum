<div class="content">
<div class="block">
	<h2><span>{{ lang_misc['multi_moderate header'] }}</span></h2>
	
		<form method="post" action="{{ form_action }}">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
			<div class="box">
				<fieldset>
					<legend>{{ lang_misc['Confirm multi_mod legend'] }}</legend>
					<div class="inbox">
						<br /><select name="action">
{% for action in actions %}
<option value="{{ action['id'] }}">{{ action['title'] }}</option>
{% endfor %}
						</select>
						<br />
						<p>{{ lang_misc['multi_mod comply'] }}</p>
					</div>
				</fieldset>
			</div>
			<span class="submitform bottom"><input type="submit" name="multi_moderate_comply" value="{{ lang_misc['multi_mod apply'] }}" /> <a href="javascript:history.go(-1)">{{ lang_common['Go back'] }}</a></span>
		</form>
	
</div>

</div>
</div><!-- .admin-console -->	