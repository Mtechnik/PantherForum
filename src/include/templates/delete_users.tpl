	<div class="blockform">
		<h2><span>{{ lang_admin_users['Delete users'] }}</span></h2>
		<div class="box">
			<form name="confirm_del_users" method="post" action="{{ form_action }}">
				<input type="hidden" name="users" value="{{ user_ids|join(', ') }}" />
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<div class="inform">
					<fieldset>
						<legend>{{ lang_admin_users['Confirm delete legend'] }}</legend>
						<div class="infldset">
							<p>{{ lang_admin_users['Confirm delete info'] }}</p>
							<div class="rbox">
								<label><input type="checkbox" name="delete_posts" value="1" checked="checked" />{{ lang_admin_users['Delete posts'] }}<br /></label>
							</div>
							<p class="warntext"><strong>{{ lang_admin_users['Delete warning'] }}</strong></p>
						</div>
					</fieldset>
				</div>
				<p class="buttons"><input type="submit" name="delete_users_comply" value="{{ lang_admin_users['Delete'] }}" /> <a href="javascript:history.go(-1)">{{ lang_admin_common['Go back'] }}</a></p>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>