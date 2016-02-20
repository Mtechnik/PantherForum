{% if errors is not empty %}
<div class="block">
	<h2>{{ lang_post['Post errors'] }}</h2>
	<div class="box">
		<div class="inbox error-info">
			<p>{{ lang_warnings['Post errors info'] }}</p>
			<ul class="error-list">
{% for error in errors %}
<li>{{ error|raw }}</li>
{% endfor %}
			</ul>
		</div>
	</div>
</div>
{% endif %}


<div class="block bwarnuser">
	<h2 class="blocktitle">{{ lang_warnings['Issue warning'] }}</h2>
	
	<form method="post" id="post" action="{{ form_action }}" onsubmit="return process_form(this)">
		<div class="box">
			
				<p class="boxtitle">{{ lang_warnings['User details'] }}</p>
				
					<p>{{ lang_warnings['Username']|format(username)|raw }}</p>
					<p>{{ lang_warnings['Active warnings 2']|format(num_active, points_active) }}</p>
					<p>{{ lang_warnings['Expired warnings 2']|format(num_expired, points_expired) }}</p>
			
			
		</div>
		
		
		<div class="box">
		
			<p class="boxtitle">{{ lang_warnings['Enter warning details'] }}</p>
			
			<div class="row">
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<input type="hidden" name="form_sent" value="1" />
				
				
				<label>{{ lang_warnings['Warning type'] }}</label>
			
    			{% if types is not empty %}	
				
				{% for warning in types %}
				<div class="row"><input type="radio" name="warning_type" value="{{ warning['id'] }}"{% if warning_type == warning['id'] %} checked="checked"{% endif %} /> {{ warning['title'] }}({{ warning['num_points'] }} {{ lang_warnings['No of points'] }}) {{ warning['expires'] }}
				</div>
				{% else %}	
				{{ lang_warnings['No warning types'] }}
				{% endfor %}
				
				{% endif %}
				
{% if panther_config['o_warnings'] == '1' %}
				<label>{{ lang_warnings['Custom warning type'] }}</label>
				<input type="radio" name="warning_type" value="0"{% if warning_type == 0 %} checked="checked"{% endif %} /> <input type="text" name="custom_title" maxlength="120" value="{{ warning_title }}" /><input type="text" name="custom_points" size="3" maxlength="3" value="{{ warning_points }}"/>{{ lang_warnings['No of points'] }}{{ lang_warnings['Expires after period'] }}<input type="text" name="custom_expiration_time" size="3" maxlength="3" value="{{ expiration_time }}" />
				
				
				<select name="custom_expiration_unit">
					<option value="hours"{% if expiration_unit == 'hours' %} selected="selected"{% endif %}>{{ lang_warnings['Hours'] }}</option>
					<option value="days"{% if expiration_unit == 'days' %} selected="selected"{% endif %}>{{ lang_warnings['Days'] }}</option>
					<option value="months"{% if expiration_unit == 'months' %} selected="selected"{% endif %}>{{ lang_warnings['Months'] }}</option>
					<option value="never"{% if expiration_unit == 'never' %} selected="selected"{% endif %}>{{ lang_warnings['Never'] }}</option>)
				</select>
{% endif %}
				
				<label>{{ lang_warnings['Admin note'] }}<textarea name="note_admin" cols="60"></textarea></label>
			</div>
		
		</div>
{% if panther_config['o_private_messaging'] == '1' %}
		<div class="box">
		
			<p class="boxtitle">{{ lang_warnings['Enter private message'] }}</p>
			<div class="row">
				<label>{{ lang_warnings['Subject'] }}<input class="longinput" type="text" name="req_subject" value="{{ subject }}" maxlength="70" /></label>
				<label>{{ lang_warnings['Message'] }}<textarea name="req_message" cols="95">{{ message }}</textarea></label>
			</div>
		
		</div>
{% endif %}
			<p class="buttons"><input type="submit" name="submit" value="{{ lang_common['Submit'] }}" accesskey="s" /> <a href="javascript:history.go(-1)">{{ lang_common['Go back'] }}</a></p>
		</form>
	
</div>