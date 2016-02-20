<div class="content">
	
<div class="block pagetitle">
<h1>{{ lang_admin_common['Forums'] }}</h1>
</div>
		<div class="block">
		<h2>{{ lang_admin_forums['Add forum head'] }}</h2>
			<form method="post" action="{{ form_action }}">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
			
	
{% if categories is not empty %}
				
		<div class="box">
					
						<p class="boxtitle">{{ lang_admin_forums['Create new subhead'] }}</p>

						<div class="inbox">
						
								<div class="row">
									<div class="col label">{{ lang_admin_forums['Add forum label'] }}     </div>
									<div class="col inputs">
										<select name="add_to_cat" tabindex="1">
{% for category in categories %}
<option value="{{ category['id'] }}">{{ category['cat_name'] }}</option>
{% endfor %}
										</select>
										<p class="info">{{ lang_admin_forums['Add forum help'] }}</p>
									</div>
								</div>
						</div>
					
			<span class="submitform bottom"> <input type="submit" name="add_forum" value="{{ lang_admin_forums['Add forum'] }}" tabindex="2" /></span>
		</div>
		  
			
{% else %}
<div class="box">
	
					
						<p class="boxtitle">{{ lang_admin_common['None'] }}</p>

							<p>{{ lang_admin_forums['No categories exist'] }}</p>

					
			
       
	</div>
			{% endif %}
			   
			</form>
			
		</div>
		
		
		

{% if category_list is not empty %}
		<div class="block">
		<h2>{{ lang_admin_forums['Edit forums head'] }}</h2>
			<form id="edforum" method="post" action="{{ action }}">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
			<span class="submitform top"><input type="submit" name="update_positions" value="{{ lang_admin_forums['Update positions'] }}" tabindex="3" /></span>
				
			<div class="box"> 	
{% for category in category_list %}
              

					
						<p class="boxtitle">{{ lang_admin_forums['Category subhead'] }} {{ category['cat_name'] }}</p>
                       <div class="inbox"> 
						
								<div class="row th">
									<div class="col action">{{ lang_admin_common['Action'] }}</div>
									<div class="col position">{{ lang_admin_forums['Position label'] }}</div>
									<div class="col forum">{{ lang_admin_forums['Forum label'] }}</div>
								</div>
						
{% for forum_list in forums if forum_list['category_id'] == category['id'] %}
								<div class="row tr">
									<div class="col action"><a href="{{ forum_list['edit_link'] }}">{{ lang_admin_forums['Edit link'] }}</a> | <a href="{{ forum_list['delete_link'] }}">{{ lang_admin_forums['Delete link'] }}</a></div>
									<div class="col position"><input type="text" name="position[{{ forum_list['id'] }}]" value="{{ forum_list['disp_position'] }}" maxlength="3"/></div>
									<div class="col forum">{% if forum_list['parent_forum'] %}{% endif %}{{ forum_list['name'] }}</div>
								</div>
{% endfor %}
						</div>

					
				
{% endfor %}
				
			</div>
			
			<span class="submitform bottom"><input type="submit" name="update_positions" value="{{ lang_admin_forums['Update positions'] }}" /></span>
			</form>
		</div>
{% endif %}


</div>
</div><!-- .admin-console -->