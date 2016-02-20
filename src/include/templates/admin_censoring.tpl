<div class="content">

<div class="block pagetitle">
<h1>{{ lang_admin_common['Censoring'] }}</h1>
</div>

	<div class="block">
		<h2>{{ lang_admin_censoring['Censoring head'] }}</h2>

							
			<form id="censoring" method="post" action="{{ form_action }}">
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				
		<div class="pageinfo">
							{% if panther_config['o_censoring'] == '1' %}
							<span class="enabled">{{ lang_admin_censoring['Censoring enabled']|format(link, lang_admin_common['Options'])|raw }}</span>
							{% else %}
							<span class="disabled">{{ lang_admin_censoring['Censoring disabled']|format(link, lang_admin_common['Options'])|raw }}</span>
							{% endif %}
		</div>
		
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_censoring['Add word subhead'] }}</p>
						<div class="inbox">

						
								<div class="row th">
									<div class="col censoredword">{{ lang_admin_censoring['Censored word label'] }}</div>
									<div class="col replaceword">{{ lang_admin_censoring['Replacement label'] }}</div>
									<div class="col action">{{ lang_admin_censoring['Action label'] }}</div>
								</div>
								
								<div class="row">
									<div class="col censoredword"><input type="text" name="new_search_for" maxlength="60" tabindex="1" /></div>
									<div class="col replaceword"><input type="text" name="new_replace_with" maxlength="60" tabindex="2" /></div>
									<div class="col action"><input type="submit" name="add_word" value="{{ lang_admin_common['Add'] }}" tabindex="3" /></div>
								</div>
								<div class="row">				{{ lang_admin_censoring['Add word info'] }}</div>
							
						</div>
					
				</div>
				
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_censoring['Edit remove subhead'] }}</p>
						<div class="inbox">
{% if words is not empty %}
						
								<div class="row th">
									<div class="col censoredword">{{ lang_admin_censoring['Censored word label'] }}</div>
									<div class="col replaceword">{{ lang_admin_censoring['Replacement label'] }}</div>
									<div class="col action">{{ lang_admin_censoring['Action label'] }}</div>
								</div>
						
{% for word in words %}
							<div class="row">
								<div class="col censoredword"><input type="text" name="search_for[{{ word['id'] }}]" value="{{ word['search_for'] }}" maxlength="60" /></div>
								<div class="col replaceword"><input type="text" name="replace_with[{{ word['id'] }}]" value="{{ word['replace_with'] }}" maxlength="60" /></div>
								<div class="col action"><input type="submit" name="update[{{ word['id'] }}]" value="{{ lang_admin_common['Update'] }}" /><input type="submit" name="remove[{{ word['id'] }}]" value="{{ lang_admin_common['Remove'] }}" /></div>
							</div>
{% endfor %}
							
{% else %}
<p>{{ lang_admin_censoring['No words in list'] }}</p>
{% endif %}
						</div>
					
				</div>
			</form>

	</div>
</div>
</div><!-- .admin-console -->