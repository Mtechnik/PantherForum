<div class="content">
	<div class="block">
		<h2>{{ lang_admin_forums['Confirm delete head'] }}</h2>
		<div class="box">
			<form method="post" action="{{ form_action }}">
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
			
						<p class="boxtitle">{{ lang_admin_forums['Confirm delete subhead'] }}</p>
						<div class="row">
							<p>{{ lang_admin_forums['Confirm delete info']|format(forum_name)|raw }}</p>
							<p class="warntext">{{ lang_admin_forums['Confirm delete warn'] }}</p>
						</div>
			
				<div class="submitform bottom">
				<a href="javascript:history.go(-1)" class="btn goback">{{ lang_admin_common['Go back'] }}</a>
				<input type="submit" name="del_forum_comply" value="{{ lang_admin_common['Delete'] }}" class="btn delete"/>
				</div>
				
			</form>
		</div>
	</div>
	
</div>
</div><!-- .admin-console -->	