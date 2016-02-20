<div class="content">
	<div class="block">
	

			<h2>{{ lang_admin_tasks['Confirm delete'] }}</h2>
			<div class="box">
				<form method="post" action="{{ form_action }}">
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<input type="hidden" name="id" value="{{ id }}" />
					
						
							<p class="boxtitle">{{ lang_admin_tasks['Important information'] }}</p>
							<div class="row">
								<p>{{ lang_admin_tasks['Delete info'] }}</p>
							</div>
					
					
					<div class="submitform bottom">
					<a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a>
					<input type="submit" name="remove" value="{{ lang_admin_tasks['Delete'] }}" class="btn delete"/>
					</div>
					
				</form>
			</div>
	

</div>
</div><!-- .admin-console -->	