<div class="content">
	<div class="block">
	    <h2>{{ lang_admin_categories['Delete category head'] }}</h2>
		
		<div class="box">
			<form method="post" action="{{ form_action }}">
				
				<input type="hidden" name="cat_to_delete" value="{{ cat_to_delete }}" />
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
					
						<p class="blocktitle">{{ lang_admin_categories['Confirm delete subhead'] }}</p>
						<div class="row">
							<p>{{ lang_admin_categories['Confirm delete info']|format(cat_name|escape)|raw }}</p>
							<p class="warntext">{{ lang_admin_categories['Delete category warn']|raw }}</p>
						</div>
				
			
				<div class="submitform bottom">
				<a href="javascript:history.go(-1)" class="btn goback">{{ lang_admin_common['Go back'] }}</a>
				<input type="submit" name="del_cat_comply" value="{{ lang_admin_common['Delete'] }}" class="btn deleete" />
				</div>
			</form>
		</div>
		
	</div>
	
</div>
</div><!-- .admin-console -->	