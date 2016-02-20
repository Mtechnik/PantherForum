<div class="content">

<div class="block pagetitle">
<h1>{{ lang_admin_common['Warnings'] }}</h1>
</div>

		<div class="block">
			<h2>{{ lang_warnings['Warning types'] }}</h2>
			
				<form id="list_types" method="post" action="{{ form_action }}">
					<div class="box">
						<fieldset>
							<legend>{{ lang_warnings['Modify warning types'] }}</legend>
							<div class="inbox">
								
{% for type in types %}
									<div class="row">
										<div class="col label"><a href="{{ type['edit_link'] }}">{{ lang_warnings['Edit'] }}</a> - <a href="{{ type['delete_link'] }}">{{ lang_warnings['Delete'] }}</a></div>
										<div class="col inputs">{{ lang_warnings['Points 2']|format(type['list_types']['points']) }}&nbsp;&nbsp;<strong>{{ type['list_types']['title'] }}</strong>&nbsp;&nbsp;{{ lang_warnings['Expires after x']|format(type['expiration'][0], type['expiration'][1]) }}</div>
									</div>
{% endfor %}
								
								<span class="submitform bottom"><input type="submit" name="add_type" value="{{ lang_warnings['Add'] }}" tabindex="4" /></span>
							</div>
						</fieldset>
					</div>
				</form>
			
		</div>
		
		
		<div class="block">
			<h2>{{ lang_warnings['Warning levels'] }}</h2>
		
				<form id="list_levels" method="post" action="{{ form_action }}">
					<div class="box">
						<fieldset>
							<legend>{{ lang_warnings['Modify warning levels'] }}</legend>
							<div class="inbox">
								
{% for level in levels %}
									<div class="row">
										<div class="col label"><a href="{{ level['edit_link'] }}">{{ lang_warnings['Edit'] }}</a> - <a href="{{ level['delete_link'] }}">{{ lang_warnings['Delete'] }}</a></div>
										<div class="col inputs">{{ lang_warnings['Points 2']|format(level['points']) }}<strong>{{ level['ban_title'] }}</strong></div>
									</div>
{% endfor %}
								
								<span class="submitform bottom"><input type="submit" name="add_level" value="{{ lang_warnings['Add'] }}" tabindex="4" /></span>
							</div>
						</fieldset>
					</div>
				</form>
		</div>
		
</div>
</div><!-- .admin-console -->	