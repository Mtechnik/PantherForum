<div class="content">
<div class="block pagetitle">
<h1>{{ lang_admin_common['Categories'] }}</h1>
</div>

			<div class="block">
		<h2>{{ lang_admin_categories['Add categories head'] }}</h2>

			<form method="post" action="{{ form_action }}">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
			<div class="box">
					
						<p class="boxtitle">{{ lang_admin_categories['Add categories subhead'] }}</p>
						<div class="inbox">
							
								<div class="row">
									<div class="col label">{{ lang_admin_categories['Add category label'] }}</div>
									<div class="col inputs">
										<input type="text" name="new_cat_name" maxlength="80" tabindex="1" />
										<p class="info">{{ lang_admin_categories['Add category help']|format(admin_forums, lang_admin_common['Forums'])|raw }}</p>
									</div>
								</div>
						
						</div>
					
					
			<span class="submitform bottom"><input type="submit" name="add_cat" value="{{ lang_admin_categories['Add new submit'] }}" tabindex="2" /></span>		
			</div>
			
			
			</form>
		</div>
		
		
{% if categories is not empty %}
		<div class="block">
		<h2>{{ lang_admin_categories['Delete categories head'] }}</h2>
		
		
			<form method="post" action="{{ form_action }}">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
			<div class="box">
					
						<p class="boxtitle">{{ lang_admin_categories['Delete categories subhead'] }}</p>
						<div class="inbox">
								<div class="row">
									<div class="col label">
									{{ lang_admin_categories['Delete category label'] }}
									
									</div>
									
									<div class="col inputs">
										<select name="cat_to_delete" tabindex="3">
{% for category in categories %}
<option value="{{ category['id'] }}">{{ category['name'] }}</option>
{% endfor %}
										</select>
										<p class="info">{{ lang_admin_categories['Delete category help'] }}</p>
									</div>
								</div>
							
					</div>
					
					
			<span class="submitform bottom"><input type="submit" name="del_cat" value="{{ lang_admin_common['Delete'] }}" tabindex="4" /></span>
		</div>

			</form>
			
			
		</div>
		
		
		
		
		
		<div class="block">	
		    <h2>{{ lang_admin_categories['Edit categories head'] }}</h2>

			<form method="post" action="{{ form_action }}">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
			
			<div class="box">
					
						<p class="boxtitle">{{ lang_admin_categories['Edit categories subhead'] }}</p>
						
							<div class="inbox">
								<div class="row th">
								    <div class="col position">{{ lang_admin_categories['Category position label'] }}</div>
									<div class="col name">{{ lang_admin_categories['Category name label'] }}</div>
									
								</div>
						
						
{% for category in categories %}
								<div class="row tr">
								<div class="col position"><input type="text" name="cat[{{ category['id'] }}][order]" value="{{ category['disp_position'] }}" maxlength="3" /></div>
									<div class="col name"><input type="text" name="cat[{{ category['id'] }}][name]" value="{{ category['name'] }}" maxlength="80" /></div>
									
								</div>
{% endfor %}
							
						
							
							</div>
					
	       <span class="submitform bottom"><input type="submit" name="update" value="{{ lang_admin_common['Update'] }}" /></span>
	       </div>

			</form>
		</div>
{% endif %}


</div>
</div><!-- .admin-console -->