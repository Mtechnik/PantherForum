<div class="content">
		<div class="block">
			<h2>{{ lang_warnings['Add new level label'] }}</h2>
			<div class="box">
				<form id="edit_type" method="post" action="{{ form_action }}"> 
					<span class="submitform top"><input type="submit" name="add" value="{{ lang_warnings['Add New'] }}" /></span>
					<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
					<div class="inform">
						<fieldset>
							<legend>{{ lang_warnings['Warning level details'] }}</legend>
							<div class="infldset">
								<input type="hidden" name="form_sent" value="1" />
								<input type="hidden" name="action" value="levels" />		

									<div class="row">
										<div class="col label">{{ lang_warnings['Ban message'] }}</div>
										<div class="col inputs">
											<input type="text" name="warning_title" maxlength="255" tabindex="1" />
											<span>{{ lang_warnings['Ban message help'] }}</span>
										</div>
									</div>
									<div class="row">
										<div class="col label">{{ lang_warnings['Points'] }}</div>
										<div class="col inputs">
											<input type="text" name="warning_points" maxlength="5" tabindex="2" />
											<span>{{ lang_warnings['Ban points help'] }}</span>
										</div>
									</div>
									<div class="row">
										<div class="col label">{{ lang_warnings['Ban duration'] }}</div>
										<div class="col inputs"><input type="text" name="expiration_time" maxlength="5" value="10" tabindex="3" />
											<select name="expiration_unit">
												<option value="hours">{{ lang_warnings['Hours'] }}</option>
												<option value="days" selected="selected">{{ lang_warnings['Days'] }}</option>
												<option value="months">{{ lang_warnings['Months'] }}</option>
												<option value="never">{{ lang_warnings['Permanent'] }}</option>
											</select>
										</div>
									</div>

							</div>
						</fieldset>
					</div>
					<span class="submitform bottom"><input type="submit" name="add" value="{{ lang_warnings['Add New'] }}" /></div>
				</form>
			</div>
		</div>

</div>
</div><!-- .admin-console -->	