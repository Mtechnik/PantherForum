<div class="content">

		<div class="block">
			<h2>{{ lang_warnings['Confirm delete type'] }}</h2>

				<form method="post" action="{{ form_action }}">
					<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
					<div class="box">
						<fieldset>
							<legend>{{ lang_warnings['Important Information'] }}</legend>
							<div class="inbox">
								<p>{{ lang_warnings['Del type confirm']|format(warning_type) }}</p>
								<p>{{ lang_warnings['Del warning type'] }}</p>
							</div>
						</fieldset>
					</div>
					<span class="submitform bottom"><input type="submit" name="del_type_comply" value="{{ lang_warnings['Delete'] }}" class="btnform submit"/><a href="javascript:history.go(-1)" class="btnform goback">{{ lang_common['Go back'] }}</a></span>
				</form>
		
		</div>
		
</div>
</div><!-- .admin-console -->	