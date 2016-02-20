<div class="content">
	<div class="block">

		<h2>{{ lang_admin_moderate['actions'] }}</h2>
		<div class="box">
			<form id="restrictions2" method="post" action="{{ form_action }}">
				<input type="hidden" name="form_sent" value="1" />
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<div class="inform">
						<div class="infldset">
							<div class="forminfo">
								<p><strong>{{ lang_admin_moderate['delete action']|raw }}</strong></p>
							</div>		
						</div>
				</div>
				<div class="submitform bottom">
				<a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a>
				<input type="submit" name="delete" value="{{ lang_admin_common['Delete'] }}" class="btn delete" />
				</div>
			</form>
		</div>
	
	
</div>
</div><!-- .admin-console -->	