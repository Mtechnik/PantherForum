<div class="content">

		<div class="block">
			<h2>{{ lang_warnings['Edit warning type'] }}</h2>
		
				<form id="edit_type" method="post" action="{{ form_action }}"> 
					<span class="submitform top"><input type="submit" name="update" value="{{ lang_warnings['Save changes'] }}" tabindex="6" /></span>
					<div class="box">
						<fieldset>
							<legend>{{ lang_warnings['Warning type details'] }}</legend>
							<div class="inbox">
								<input type="hidden" name="form_sent" value="1" />
								<input type="hidden" name="id" value="{{ warning_type['id'] }}" />
								<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
								<input type="hidden" name="action" value="types" />
								
								
									<div class="row">
										<div class="col label">{{ lang_warnings['Warning title'] }}</div>
										<div class="col inputs"><input type="text" name="warning_title" size="35" maxlength="120" value="{{ warning_type['title'] }}" tabindex="1" /></div>
									</div>
									<div class="row">
										<div class="col label">{{ lang_warnings['Points'] }}</div>
										<div class="col inputs"><input type="text" name="warning_points" size="3" maxlength="3" value="{{ warning_type['points'] }}" tabindex="2" /></div>
									</div>
									<div class="row">
										<div class="col label">{{ lang_warnings['Expiration'] }}</div>
										<div class="col inputs"><input type="text" name="expiration_time" size="3" maxlength="3" value="{{ expiration[0] }}" tabindex="3" />
											<select name="expiration_unit">
												<option value="hours"{% if expiration[1] == 'hours' %} selected="selected"{% endif %}>{{ lang_warnings['Hours'] }}</option>
												<option value="days"{% if expiration[1] == 'days' %} selected="selected"{% endif %}>{{ lang_warnings['Days'] }}</option>
												<option value="months"{% if expiration[1] == 'months' %} selected="selected"{% endif %}>{{ lang_warnings['Months'] }}</option>
												<option value="never"{% if expiration[1] == 'never' %} selected="selected"{% endif %}>{{ lang_warnings['Never'] }}</option>
											</select>
										</div>
									</div>
									<div class="row">
										<div class="col label">{{ lang_warnings['Description'] }}</div>
										<div class="col inputs"><textarea name="warning_description" rows="3" cols="50" tabindex="4">{{ warning_type['description'] }}</textarea></div>
									</div>
								
							</div>
						</fieldset>
					</div>
					<span class="submitform bottom"><input type="submit" name="update" value="{{ lang_warnings['Save changes'] }}" class="btnform submit"/></span>
				</form>
			
		</div>
		
</div>
</div><!-- .admin-console -->	