<div class="content">

<div class="block pagetitle">
<h1>{{ lang_admin_common['Moderate'] }}</h1>
</div>

	<div class="block">
		<h2>{{ lang_admin_moderate['actions'] }}<span class="pages-label">{{ lang_common['Pages'] }}</span> {{ pagination|raw }}</h2>
		

				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_moderate['title'] }}</p>
						<div class="inbox">
							
<div class="row">
<div class="col label"><a href="{{ add_link }}" tabindex="1">{{ lang_admin_moderate['add new'] }}</a></div>
<div class="col inputs">{{ lang_admin_moderate['add new label'] }}</div></div>
{% for action in actions %}
							<div class="row">
								<div class="col label"><a href="{{ action['edit_link'] }}" tabindex="9">{{ lang_admin_moderate['edit action'] }}</a> | <a href="{{ action['delete_link'] }}" tabindex="10">{{ lang_admin_moderate['delete action 2'] }}</a></div>
								<div class="col inputs"><strong>{{ action['title'] }}</strong></div>
							</div>
{% endfor %}
							
						</div>
					
				</div>
		
	
	</div>
	
</div>
</div><!-- .admin-console -->