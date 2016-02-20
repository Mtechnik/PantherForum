<div class="content">
		<div class="block">
			<h2>{{ lang_warnings['Edit warning level'] }}</h2>
			
		
				<form id="edit_level" method="post" action="{{ form_action }}"> 
					<span class="submitform top"><input type="submit" name="update" value="{{ lang_warnings['Save changes'] }}" tabindex="6" /></span>
					<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
					<input type="hidden" name="action" value="levels" />
					<input type="hidden" name="form_sent" value="1" />
					<div class="box">
						<fieldset>
							<legend>{{ lang_warnings['Warning level details'] }}</legend>
							<div class="inbox">
								<input type="hidden" name="form_sent_level" value="1" />
								<input type="hidden" name="id" value="{{ warning_level['id'] }}" />
							
									<div class="row">
										<div class="col label">{{ lang_warnings['Ban message'] }}</div>
										<div class="col inputs">
											<input type="text" name="warning_title" maxlength="255" value="{{ warning_level['message'] }}" tabindex="1" />
											<span>{{ lang_warnings['Ban message help'] }}</span>
										</div>
									</div>
									<div class="row">
										<div class="col label">{{ lang_warnings['Points'] }}</div>
										<div class="col inputs">
											<input type="text" name="warning_points" maxlength="3" value="{{ warning_level['points'] }}" tabindex="2" />
											<span>{{ lang_warnings['Ban points help'] }}</span>
										</div>
									</div>
									
									<div class="row">
										<div class="col label">{{ lang_warnings['Ban duration'] }}</div>
										<div class="col inputs"><input type="text" name="expiration_time" maxlength="3" value="{{ expiration[0] }}" tabindex="3" />

											<select name="expiration_unit">
												<option value="hours"{% if expiration[1] == 'hours' %} selected="selected"{% endif %}>{{ lang_warnings['Hours'] }}</option>
												<option value="days"{% if expiration[1] == 'days' %} selected="selected"{% endif %}>{{ lang_warnings['Days'] }}</option>
												<option value="months"{% if expiration[1] == 'months' %} selected="selected"{% endif %}>{{ lang_warnings['Months'] }}</option>
												<option value="never"{% if expiration[1] == 'never' %} selected="selected"{% endif %}>{{ lang_warnings['Permanent'] }}</option>
											</select>
										</div>
									</div>
								
							</div>
						</fieldset>
					</div>
					
					<span class="submitform bottom"><input type="submit" name="update" value="{{ lang_warnings['Save changes'] }}" /></span>
				</form>
		
		</div>

</div>
</div><!-- .admin-console -->	