<div class="content">
		<div class="block">
			<h2>{{ lang_admin_extensions['Uninstall extension'] }}</h2>
		
				<form method="post" action="{{ form_action }}">
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<input type="hidden" name="form_sent" value="1" />
					<div class="box">
						
							<p class="boxtitle">{{ lang_admin_extensions['Warning'] }}</p>
							<div class="row">
								<p>{{ lang_admin_extensions['Extension uninstall information'] }}</p>
								{% if extension['uninstall_note'] != '' %}<p>{{ extension['uninstall_note'] }}</p>{% endif %}
							</div>
					
					</div>
					<div class="blockbuttons"><a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a><input type="submit" name="submit" value="{{ lang_admin_extensions['Uninstall extension'] }}" class="btn unistall" /></div>
				</form>
	
		</div>
</div>
</div><!-- .admin-console -->