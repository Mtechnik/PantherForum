<div class="content">

		<div class="block">
			<h2>{{ lang_warnings['Confirm delete level'] }}</h2>
		
				<form method="post" action="{{ form_action }}">
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
					<div class="box">
						<fieldset>
							<legend>{{ lang_warnings['Important Information'] }}</legend>
							<div class="inbox">
								<div class="row"><p>{{ lang_warnings['Delete level'] }}</p></div>
							</div>
						</fieldset>
					</div>
					<span class="submitform bottom"><input type="submit" name="del_level_comply" value="{{ lang_warnings['Delete'] }}" class="btnform sumbit" /> <a href="javascript:history.go(-1)" class="btnform goback">{{ lang_common['Go back'] }}</a></span>
				</form>
	
		</div>
		
</div>
</div><!-- .admin-console -->	