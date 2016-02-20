<div class="content">

<div class="block pagetitle">
<h1>{{ lang_admin_common['Archive'] }}</h1>
</div>

	<div class="block">
		<h2>{{ lang_admin_archive['Options'] }}</h2>
	
			<form method="post" action="{{ form_action }}">
			<input type="hidden" name="form_sent" value="1" />
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_archive['Archive overview'] }}</p>
						<div class="inbox">
							<p class="boxinfo">{{ lang_admin_archive['Archive available']|format(archive_lang, admin_options)|raw }}</p>
							<p class="boxinfo">{{ lang_admin_archive['Archive info']|format(archived, percentage) }}</p>
						</div>
					
				</div>
				
				<div class="box">
					
						<div class="inbox">
						
								<div class="row">
									<div class="col label">{{ lang_admin_archive['Closed topics'] }}</div>
									<div class="col inputs">
										<select name="closed">
											<option value="2"{% if archive_rules['closed'] == 2 %} selected="selected"{% endif %}>{{ lang_admin_archive['Closed or open'] }}</option>
											<option value="1"{% if archive_rules['closed'] == 1 %} selected="selected"{% endif %}>{{ lang_admin_archive['Closed'] }}</option>
											<option value="0"{% if archive_rules['closed'] == 0 %} selected="selected"{% endif %}>{{ lang_admin_archive['Open'] }}</option>
										</select>
										<p class="info">{{ lang_admin_archive['Closed topics help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_archive['Sticky topics'] }}</div>
									<div class="col inputs">
										<select name="sticky">
											<option value="2"{% if archive_rules['sticky'] == 2 %} selected="selected"{% endif %}>{{ lang_admin_archive['Sticky or unsticky'] }}</option>
											<option value="1"{% if archive_rules['sticky'] == 1 %} selected="selected"{% endif %}>{{ lang_admin_archive['Sticky'] }}</option>
											<option value="0"{% if archive_rules['sticky'] == 0 %} selected="selected"{% endif %}>{{ lang_admin_archive['Unsticky'] }}</option>
										</select>
										<p class="info">{{ lang_admin_archive['Sticky topics help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_archive['Forums'] }}</div>
									<div class="col">
										<select multiple="multiple" name="forums[]">
											<option value="0"{% if archive_rules['forums'][0] == 0 %} selected="selected"{% endif %}>{{ lang_admin_archive['All forums']|raw }}</option>
{% for category in categories %}
<optgroup label="{{ category['name'] }}">
{% for forum in forums if forum['category_id'] == category['id'] %}
<option value="{{ forum['id'] }}"{% if forum['selected'] %} selected="selected"{% endif %}>{{ forum['name'] }}</option>
{% endfor %}
</optgroup>
{% endfor %}
										</select>
										<p class="info">{{ lang_admin_archive['Forums help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_archive['Archive unit'] }}</div>
									<div class="col inputs">
										<label class="conl"><input name="time" type="text" size="10" value="{{ archive_rules['time'] }}" />
											<select name="unit">
												<option value="days"{% if archive_rules['unit'] == 'days' %}selected="selected"{% endif %}>{{ lang_common['Days'] }}</option>
												<option value="months"{% if archive_rules['unit'] == 'months' %}selected="selected"{% endif %}>{{ lang_common['Months'] }}</option>
												<option value="years"{% if archive_rules['unit'] == 'years' %}selected="selected"{% endif %}>{{ lang_common['Years'] }}</option>
											</select>
										</label>
									</div>
								</div>
						
						</div>
					
					<span class="submitform bottom"><input type="submit" name="save" value="{{ lang_admin_common['Save changes'] }}" /></span>
				</div>
				
			</form>
</div>

</div>
</div><!-- .admin-console -->