<div class="content">
	<div class="block">
		<h2>{{ lang_admin_groups['Group delete head'] }}</h2>
		<div class="box">
			<form method="post" action="{{ form_action }}">
				
				<input type="hidden" name="group_to_delete" value="{{ group_id }}" />
					<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
					
						<p class="boxtitle">{{ lang_admin_groups['Confirm delete subhead'] }}</p>
						<div class="row">
							<p>{{ lang_admin_groups['Confirm delete info']|format(group_title)|raw }}</p>
							<p class="warntext">{{ lang_admin_groups['Confirm delete warn'] }}</p>
						</div>
				
				<div class="submitform bottom">
				<a href="javascript:history.go(-1)" tabindex="2" class="btn goback">{{ lang_admin_common['Go back'] }}</a>
				<input type="submit" name="del_group_comply" value="{{ lang_admin_common['Delete'] }}" tabindex="1"class="btn delete"/>
				</div>
			</form>
		</div>
	</div>
	
</div>
</div><!-- .admin-console -->	