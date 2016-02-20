<div class="content">

<div class="block pagetitle">
<h1>{{ lang_admin_common['Robots'] }}</h1>
</div>

	<div class="block">
		<h2>{{ lang_admin_robots['Robots head'] }}</h2>
		
			<form id="censoring" method="post" action="{{ form_action }}">
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_robots['Add question subhead'] }}</p>
						<div class="inbox">
							
						
								<div class="row th">
									<div class="col question">{{ lang_admin_robots['Question label'] }}</div>
									<div class="col answer">{{ lang_admin_robots['Answer label'] }}</div>
									<div class="col action">{{ lang_admin_robots['Action label'] }}</div>
								</div>
						
								<div class="row">
									<div class="col question"><input type="text" name="new_question" size="24" maxlength="60" tabindex="1" /></div>
									<div class="col answer"><input type="text" name="new_answer" size="24" maxlength="60" tabindex="2" /></div>
									<div class="col action"><input type="submit" name="add_test" value="{{ lang_admin_common['Add'] }}" tabindex="3" /></div>
								</div>
							<div class="row info"><p>{{ lang_admin_robots['Add question info'] }}</p></div>
						</div>
					
				</div>
				
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_robots['Edit remove subhead'] }}</p>
						<div class="inbox">
{% if robots is not empty %}
							
								<div class="row th">
									<div class="col question">{{ lang_admin_robots['Question label'] }}</div>
									<div class="col answer">{{ lang_admin_robots['Answer label'] }}</div>
									<div class="col action">{{ lang_admin_robots['Action label'] }}</div>
								</div>
						
{% for robot in robots %}
<div class="row">
<div class="col question"><input type="text" name="question[{{ robot['id'] }}]" value="{{ robot['question'] }}" size="24" maxlength="90" /></div>
<div class="col answer"><input type="text" name="answer[{{ robot['id'] }}]" value="{{ robot['answer'] }}" size="24" maxlength="60" /></div>
<div class="col action"><input type="submit" name="update[{{ robot['id'] }}]" value="{{ lang_admin_common['Update'] }}" />&#160;<input type="submit" name="remove[{{ robot['id'] }}]" value="{{ lang_admin_common['Remove'] }}" /></div>
</div>
{% endfor %}
					
{% else %}
<div class="row">
<div class="col">{{ lang_admin_robots['No questions in list'] }}</div>
</div>
{% endif %}
						</div>
					
				</div>
			</form>
	
	</div>

</div>
</div><!-- .admin-console -->	