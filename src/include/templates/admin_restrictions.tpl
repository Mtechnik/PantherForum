<div class="content">

<div class="block pagetitle">
<h1>{{ lang_admin_common['Restrictions'] }}</h1>
</div>

	<div class="block">
		<h2>{{ lang_admin_restrictions['restrictions head'] }}</h2>

			<form id="restrictions2" method="post" action="{{ add_action }}">
				<input type="hidden" name="form_sent" value="1" />
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<div class="box">
					<fieldset>
						<legend>{{ lang_admin_restrictions['restriction information'] }}</legend>
						<div class="inbox">
						<div class="row">
							
								<select name="user">
{% for administrator in administrators %}
<option value="{{ administrator['id'] }}">{{ administrator['username'] }}</option>
{% endfor %}
								</select>
							<input type="submit" name="submit" value="{{ lang_common['Submit'] }}" tabindex="43" />
					
						</div>
						</div>
					</fieldset>
				</div>
			</form>
			
			
			
			<form id="restrictions2" method="post" action="{{ edit_action }}">
				<input type="hidden" name="form_sent" value="1" />
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<div class="box">
					<fieldset>
						<legend>{{ lang_admin_restrictions['restriction information 2'] }}</legend>
						<div class="inbox">
						<div class="row">
								<select name="user">
{% for restriction in restrictions %}
<option value="{{ restriction['id'] }}">{{ restriction['username'] }}</option>
{% else %}
<optgroup label="{{ lang_admin_restrictions['no other admins'] }}"></optgroup>
{% endfor %}
								</select>				
							<input type="submit" name="submit" value="{{ lang_common['Submit'] }}" tabindex="43" />
					
						</div>
						</div>
					</fieldset>
				</div>
			</form>
			
			
			
			<form id="restrictions2" method="post" action="{{ delete_action }}">
				<input type="hidden" name="form_sent" value="1" />
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<div class="box">
					<fieldset>
						<legend>{{ lang_admin_restrictions['restriction information 3'] }}</legend>
						<div class="inbox">
						<div class="row">
								<select name="user">
{% for restriction in restrictions %}
<option value="{{ restriction['id'] }}">{{ restriction['username'] }}</option>
{% else %}
<optgroup label="{{ lang_admin_restrictions['no other admins'] }}"></optgroup>
{% endfor %}
								</select>				
							<input type="submit" name="submit" value="{{ lang_admin_restrictions['delete'] }}" tabindex="43" />
					
						</div>
						</div>
					</fieldset>
				</div>
			</form>

	
	</div>
	
</div>
</div><!-- .admin-console -->	