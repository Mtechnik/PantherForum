<div class="content">

<div class="block pagetitle">
<h1>{{ lang_admin_common['Ranks'] }}</h1>
</div>

	<div class="block">
		<h2>{{ lang_admin_ranks['Ranks head'] }}</h2>
		
			<form id="censoring" method="post" action="{{ form_action }}">
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				
				<div class="pageinfo">
				{% if panther_config['o_ranks'] == '1' %}
				
				<span class="enabled">{{ lang_admin_ranks['Ranks enabled']|format(admin_options, lang_admin_common['Options'])|raw }}</span>
				{% else %}
				
				<span class="disabled">{{ lang_admin_ranks['Ranks disabled']|format(admin_options, lang_admin_common['Options'])|raw }}</span>
				{% endif %}
				</div>
				
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_ranks['Add rank subhead'] }}</p>
						<div class="inbox">
							
							
								<div class="row th">
									<div class="col ranktitle">{{ lang_admin_ranks['Rank title label'] }}</div>
									<div class="col minimumpost">{{ lang_admin_ranks['Minimum posts label'] }}</div>
									<div class="col action">{{ lang_admin_ranks['Actions label'] }}</div>
								</div>
							
								<div class="row tr">
									<div class="col ranktitle"><input type="text" name="new_rank" maxlength="60" tabindex="1" /></div>
									<div class="col minimumpost"><input type="text" name="new_min_posts" maxlength="60" tabindex="2" /></div>
									<div class="col action"><input type="submit" name="add_rank" value="{{ lang_admin_common['Add'] }}" tabindex="3" /></div>
								</div>
								<div class="row">{{ lang_admin_ranks['Add rank info'] }} </div>
							
						</div>
					
				</div>
				
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_ranks['Edit remove subhead'] }}</p>
						<div class="inbox">
{% if ranks is not empty %}
						
								<div class="row th">
									<div class="col ranktitle">{{ lang_admin_ranks['Rank title label'] }}</div>
									<div class="col minimumpost">{{ lang_admin_ranks['Minimum posts label'] }}</div>
									<div class="col action">{{ lang_admin_ranks['Actions label'] }}</div>
								</div>
						
{% for rank in ranks %}
<div class="row tr">
<div class="col ranktitle"><input type="text" name="rank[{{ rank['id'] }}]" value="{{ rank['rank'] }}" size="24" maxlength="50" /></div>
<div class="col minimumpost"><input type="text" name="min_posts[{{ rank['id'] }}]" value="{{ rank['min_posts'] }}" maxlength="7" /></div>
<div class="col action"><input type="submit" name="update[{{ rank['id'] }}]" value="{{ lang_admin_common['Update'] }}" /><input type="submit" name="remove[{{ rank['id'] }}]" value="{{ lang_admin_common['Remove'] }}" /></div>
</div>
{% endfor %}
						
{% else %}
<p>{{ lang_admin_ranks['No ranks in list'] }}</p>
{% endif %}
						</div>
					
			   </div>
			
			</form>
		
	</div>

</div>
</div><!-- .admin-console -->	