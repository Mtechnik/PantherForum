<div class="content">
	<div class="block">
	
		<h2>{{ lang_admin_restrictions['restrictions head'] }}</h2>
		<div class="box">
			<form id="restrictions" method="post" action="{{ form_action }}">
				<input type="hidden" name="form_sent" value="1" />
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
		
					<input type="hidden" name="admin_id" value="{{ user }}" />
				
							<div class="row forminfo">
								<p>{{ lang_admin_restrictions['delete label'] }}</p>
							</div>		
						
			
				<div class="submitform bottom">
				<a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a>
				<input type="submit" name="delete" value="{{ lang_admin_restrictions['delete'] }}" class="btn delete" />
				</div>
				
			</form>
		</div>
	</div>

</div>
</div><!-- .admin-console -->	