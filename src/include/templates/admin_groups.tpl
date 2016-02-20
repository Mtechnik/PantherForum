<div class="content">

<div class="block pagetitle">
<h1>{{ lang_admin_common['User groups'] }}</h1>
</div>

		<div class="block">
		<h2>{{ lang_admin_groups['Add groups head'] }}</h2>
			<form id="groups" method="post" action="{{ form_action }}">
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_groups['Add group subhead'] }}</p>
						
					<div class="inbox">
								<div class="row">
									<div class="col label">{{ lang_admin_groups['New group label'] }}</div>
									<div class="col inputs">
										<select id="base_group" name="base_group" tabindex="1">
{% for option in new_options %}
<option value="{{ option['id'] }}"{% if option['id'] == panther_config['o_default_user_group'] %}{% endif %}>{{ option['title'] }}</option>
{% endfor %}
										</select>
										<p class="info">{{ lang_admin_groups['New group help'] }}</p>
									</div>
									<div class="col action"><input type="submit" name="add_group" value="{{ lang_admin_common['Add'] }}" tabindex="2" /></div>
								</div>
						
						</div>
					
				</div>
				
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_groups['Default group subhead'] }}</p>
						<div class="inbox">
							
								<div class="row">
									<div class="col label">{{ lang_admin_groups['Default group label'] }}<input type="submit" name="set_default_group" value="{{ lang_admin_common['Save'] }}" tabindex="4" /></div>
									<div class="col inputs">
										<select id="default_group" name="default_group" tabindex="3">
{% for option in default_options %}
<option value="{{ option['id'] }}"{% if option['id'] == panther_config['o_default_user_group'] %}{% endif %}>{{ option['title'] }}</option>
{% endfor %}
										</select>
										<p class="info">{{ lang_admin_groups['Default group help'] }}</p>
									</div>
								</div>
					
						</div>
					
				</div>
				
			</form>
		</div>

		<div class="block">
		<h2>{{ lang_admin_groups['Existing groups head'] }}</h2>
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_groups['Edit groups subhead'] }}</p>
					<div class="inbox">
							<p class="boxinfo">{{ lang_admin_groups['Edit groups info'] }}</p>
							
{% for group in group_options %}
								<div class="row">
									<div class="col label"><a href="{{ group['edit_link'] }}">{{ lang_admin_groups['Edit link'] }}</a>{% if group['can_delete'] %} | <a href="{{ group['delete_link'] }}">{{ lang_admin_groups['Delete link'] }}</a>{% endif %}</div>
									<div class="col inputs">{{ group['title'] }}</div>
								</div>
{% endfor %}
						
					</div>
					
					</div>
			
			
		</div>
	

</div>
</div><!-- .admin-console -->	