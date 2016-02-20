<div class="content">

		<div class="block">
			<h2>{{ lang_warnings['Add new type label'] }}</h2>
		
				<form id="edit_type" method="post" action="{{ form_action }}"> 
					<span class="submitform top"><input type="submit" name="add" value="{{ lang_warnings['Add New'] }}" /></span>
					<div class="box">
						<fieldset>
							<legend>{{ lang_warnings['Type details'] }}</legend>
							<div class="inbox">
								<input type="hidden" name="form_sent" value="1" />
								<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
								<input type="hidden" name="action" value="types" />								
							
									<div class="row">
										<div class="col label">{{ lang_warnings['Warning title'] }}</div>
										<div class="col inputs"><input type="text" name="warning_title" size="35" maxlength="120" tabindex="1" /></div>
									</div>
									<div class="row">
										<div class="col label">{{ lang_warnings['Points'] }}</div>
										<div class="col inputs"><input type="text" name="warning_points" size="5" maxlength="5" tabindex="2" /></div>
									</div>
									<div class="row">
										<div class="col label">{{ lang_warnings['Expiration'] }}</div>
										<div class="col inputs"><input type="text" name="expiration_time" size="5" maxlength="5" value="10" tabindex="3" />

											<select name="expiration_unit">
												<option value="hours">{{ lang_warnings['Hours'] }}</option>
												<option value="days" selected="selected">{{ lang_warnings['Days'] }}</option>
												<option value="months">{{ lang_warnings['Months'] }}</option>
												<option value="never">{{ lang_warnings['Never'] }}</option>
											</select>
										</div>
									</div>
									<div class="row">
										<div class="col label">{{ lang_warnings['Description'] }}</div>
										<div class="col inputs"><textarea name="warning_description" rows="3" cols="50" tabindex="4"></textarea></div>
									</div>
								
							</div>
						</fieldset>
					</div>
					<span class="submitform bottom"><input type="submit" name="add" value="{{ lang_warnings['Add New'] }}" class="btnform submit"/></span>
				</form>
		
		</div>
		
</div>
</div><!-- .admin-console -->	