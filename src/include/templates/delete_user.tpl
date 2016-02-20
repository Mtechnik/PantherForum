<div class="block bdelete">
	<h2 class="blocktitle">{{ lang_profile['Confirm delete user'] }}</h2>
	<div class="box">
		<form id="confirm_del_user" method="post" action="{{ form_action }}">
			<div class="inform">
				<fieldset>
					<legend>{{ lang_profile['Confirm delete legend'] }}</legend>
					<div class="infldset">
						<p>{{ lang_profile['Confirmation info'] }} <strong>{{ username }}</strong></p>
						<div class="rbox">
							<label><input type="checkbox" name="delete_posts" value="1" checked="checked" />{{ lang_profile['Delete posts'] }}<br /></label>
							<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
						</div>
						<p class="warntext"><strong>{{ lang_profile['Delete warning'] }}</strong></p>
					</div>
				</fieldset>
			</div>
			
			<div class="blockbuttons">
			<a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a>
			<input type="submit" name="delete_user_comply" value="{{ lang_profile['Delete'] }}" class="btn delete" />
			</div>
			
		</form>
	</div>
</div>
