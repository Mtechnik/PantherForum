<div class="content">
<div class="block pagetitle">
<h1>{{ lang_admin_common['Bans'] }}</h1>
</div>
	<div class="block">
		<h2>{{ lang_admin_bans['New ban head'] }}</h2>
		<div class="box">
			<form id="bans" method="post" action="{{ form_action }}">

					<fieldset>
						<legend>{{ lang_admin_bans['Add ban subhead'] }}</legend>
						<div class="inbox">
					
								<div class="row">
									<div class="col label">{{ lang_admin_bans['Username label'] }}<div><input type="submit" name="add_ban" value="{{ lang_admin_common['Add'] }}" tabindex="2" /></div></div>
									<div class="col inputs">
										<input type="text" name="new_ban_user" maxlength="25" tabindex="1" />
										<span>{{ lang_admin_bans['Username advanced help'] }}</span>
									</div>
								</div>
							
						</div>
					</fieldset>

			</form>
		</div>
	</div>
		
		
	<div class="block switchview grid3">	
		<h2>{{ lang_admin_bans['Ban search head'] }}</h2>
	
			<form id="find_bans" method="get" action="{{ search_action }}">
				<span class="submitform top"><input type="submit" name="find_ban" value="{{ lang_admin_bans['Submit search'] }}" tabindex="3" /></span>
		<div class="box">
					<fieldset>
						<legend>{{ lang_admin_bans['Ban search subhead'] }}</legend>
						<div class="inbox">
							<p class="boxinfo">{{ lang_admin_bans['Ban search info'] }}</p>
						
								<div class="row tr">
									<div class="col label">{{ lang_admin_bans['Username label'] }}</div>
									<div class="col inputs"><input type="text" name="form[username]" maxlength="25" tabindex="4" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_bans['IP label'] }}</div>
									<div class="col inputs"><input type="text" name="form[ip]" maxlength="255" tabindex="5" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_bans['E-mail label'] }}</div>
									<div class="col inputs"><input type="text" name="form[email]" maxlength="80" tabindex="6" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_bans['Message label'] }}</div>
									<div class="col inputs"><input type="text" name="form[message]" maxlength="255" tabindex="7" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_bans['Expire after label'] }}</div>
									<div class="col inputs"><input type="text" name="expire_after" maxlength="10" tabindex="8" />
									<span>{{ lang_admin_bans['Date help'] }}</span></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_bans['Expire before label'] }}</div>
									<div class="col inputs"><input type="text" name="expire_before" maxlength="10" tabindex="9" />
									<span>{{ lang_admin_bans['Date help'] }}</span></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_bans['Order by label'] }}</div>
									<div class="col inputs">
										<select name="order_by" tabindex="10">
											<option value="username" selected="selected">{{ lang_admin_bans['Order by username'] }}</option>
											<option value="ip">{{ lang_admin_bans['Order by ip'] }}</option>
											<option value="email">{{ lang_admin_bans['Order by e-mail'] }}</option>
											<option value="expire">{{ lang_admin_bans['Order by expire'] }}</option>
										</select>&#160;&#160;&#160;<select name="direction" tabindex="11">
											<option value="ASC" selected="selected">{{ lang_admin_bans['Ascending'] }}</option>
											<option value="DESC">{{ lang_admin_bans['Descending'] }}</option>
										</select>
									</div>
								</div>
							
						</div>
					</fieldset>
					</div>
			
				<span class="submitform bottom"><input type="submit" name="find_ban" value="{{ lang_admin_bans['Submit search'] }}" tabindex="12" /></span>
			</form>
	
	</div>
</div>
</div><!-- .admin-console -->