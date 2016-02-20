<div class="content">
<div class="block pagetitle">
<h1>{{ lang_admin_common['Maintenance'] }}</h1>
</div>
	<div class="block">
		<h2>{{ lang_admin_maintenance['Maintenance head'] }}</h2>
		
			<form method="get" action="{{ form_action }}">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<div class="box">
					<input type="hidden" name="action" value="rebuild" />
					
						<p class="boxtitle">{{ lang_admin_maintenance['Rebuild index subhead'] }}</p>
						<div class="inbox">
							<p class="boxinfo">{{ lang_admin_maintenance['Rebuild index info']|format(options_link, lang_admin_common['Maintenance mode'])|raw }}</p>
								<div class="row">
									<div class="col label">{{ lang_admin_maintenance['Posts per cycle label'] }}</div>
									<div class="col inputs">
										<input type="text" name="i_per_page" size="7" maxlength="7" value="300" tabindex="1" />
										<p class="info">{{ lang_admin_maintenance['Posts per cycle help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_maintenance['Starting post label'] }}</div>
									<div class="col inputs">
										<input type="text" name="i_start_at" size="7" maxlength="7" value="{{ first_id }}" tabindex="2" />
										<p class="info">{{ lang_admin_maintenance['Starting post help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_maintenance['Empty index label'] }}</div>
									<div class="inputadmin">
										<label><input type="checkbox" name="i_empty_index" value="1" tabindex="3" checked="checked" />&#160;&#160;{{ lang_admin_maintenance['Empty index help'] }}</label>
									</div>
								</div>
						
							<p class="topspace">{{ lang_admin_maintenance['Rebuild completed info'] }}</p>
							<span class="submitform bottom"><input type="submit" name="rebuild_index" value="{{ lang_admin_maintenance['Rebuild index'] }}" tabindex="4" /></span>
						
					
		        </div>
			</form>
			
			<form method="post" action="{{ form_action }}" onsubmit="return process_form(this)">
				<div class="box">
					<input type="hidden" name="action" value="prune" />
					
						<p class="boxtitle">{{ lang_admin_maintenance['Prune subhead'] }}</p>
						<div class="inbox">
							
								<div class="row">
									<div class="col label">{{ lang_admin_maintenance['Days old label'] }}</div>
									<div class="col inputs">
										<input type="text" name="req_prune_days" size="3" maxlength="3" tabindex="5" />
										<p class="info">{{ lang_admin_maintenance['Days old help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_maintenance['Prune sticky label'] }}</div>
									<div class="col inputs">
										<label class="conl"><input type="radio" name="prune_sticky" value="1" tabindex="6" checked="checked" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong></label>
										<label class="conl"><input type="radio" name="prune_sticky" value="0" />&#160;<strong>{{ lang_admin_common['No'] }}</strong></label>
										<p class="info">{{ lang_admin_maintenance['Prune sticky help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_maintenance['Prune from label'] }}</div>
									<div class="col inputs">
										<select name="prune_from" tabindex="7">
											<option value="all">{{ lang_admin_maintenance['All forums'] }}</option>
{% for category in categories %}
<optgroup label="{{ category['name'] }}">
{% for forum in forums if forum['category_id'] == category['id'] %}
<option value="{{ forum['id'] }}">{{ forum['name'] }}</option>
{% endfor %}
</optgroup>
{% endfor %}
											</optgroup>
										</select>
										<p class="info">{{ lang_admin_maintenance['Prune from help'] }}</p>
									</div>
								</div>
							
							<p class="topspace">{{ lang_admin_maintenance['Prune info']|format(options_link, lang_admin_common['Maintenance mode'])|raw }}</p>
							<span class="submitform bottom"><input type="submit" name="prune" value="{{ lang_admin_common['Prune'] }}" tabindex="8" /></span>
						</div>
					
				</div>
			</form>
	
	</div>
	
	
	
	
	
	<div class="block">
		<h2>{{ lang_admin_maintenance['merge legend'] }}</h2>
		<div class="box">
			<form id="usermerge" method="post" action="{{ form_action }}">
			<input type="hidden" name="form_sent" value="1" />
			<input type="hidden" name="action" value="confirm_merge" />
			<div class="inform">
				
					<p class="boxtitle">{{ lang_admin_maintenance['Settings subhead'] }}</p>
					<div class="infldset">
						
							<div class="row">
								<div class="col inputs">
									<select name="to_merge" tabindex="3">
{% for option in options %}
<option value="{{ option['id'] }}">{{ option['username'] }} <{{ option['group_title'] }}></option>
{% endfor %}
									</select>
									<p class="info">{{ lang_admin_maintenance['user merge legend'] }}</p>
								</div>
							</div>
							<div class="row">
								<div class="col inputs">
									<select name="to_stay" tabindex="3">
{% for option in options %}
<option value="{{ option['id'] }}">{{ option['username'] }} <{{ option['group_title'] }}></option>
{% endfor %}
									</select>
									<p class="info">{{ lang_admin_maintenance['merge help']|format(options_link, lang_admin_common['Maintenance mode'])|raw }}</p>
								</div>
							</div>
						
					</div>
				
			</div>
			<span class="submitform bottom"><input type="submit" name="submit" value="{{ lang_admin_maintenance['continue'] }}" tabindex="3" /></span>
			</form>
		</div>
	</div>
	<div class="block">
		<h2>{{ lang_admin_maintenance['User prune head'] }}</h2>
	
			<form id="example" method="post" action="{{ form_action }}">
				<input name="action" type="hidden" value="prune_users" />
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_maintenance['Settings subhead'] }}</p>
						<div class="inbox">
						
							<div class="row">
								<div class="col label">{{ lang_admin_maintenance['Prune by label'] }}</div>
								<div class="col inputs">
									<input type="radio" name="prune_by" value="1" checked="checked" />&#160;<strong>{{ lang_admin_maintenance['Registered date'] }}</strong>&#160;&#160;&#160;<input type="radio" name="prune_by" value="0" />&#160;<strong>{{ lang_admin_maintenance['Last login'] }}</strong>
									<p class="info">{{ lang_admin_maintenance['Prune help'] }}</p>
								</div>
							</div>
							<div class="row">
								<div class="col label">{{ lang_admin_maintenance['Minimum days label'] }}</div>
								<div class="col inputs">
									<input type="text" name="days" value="28" size="3" tabindex="1" />
									<p class="info">{{ lang_admin_maintenance['Minimum days help'] }}</p>
								</div>
							</div>
							<div class="row">
								<div class="col label">{{ lang_admin_maintenance['Maximum posts label'] }}</div>
								<div class="col inputs">
									<input type="text" name="posts" value="1"  size="7" tabindex="2" />
									<p class="info">{{ lang_admin_maintenance['Maximum posts help'] }}</p>
								</div>
							</div>
							<div class="row">
								<div class="col label">{{ lang_admin_maintenance['Delete admins and mods label'] }}</div>
								<div class="col inputs">
									<input type="radio" name="admmods_delete" value="1" />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong>&#160;&#160;&#160;<input type="radio" name="admmods_delete" value="0" checked="checked" />&#160;<strong>{{ lang_admin_common['No'] }}</strong>
									<p class="info">{{ lang_admin_maintenance['Delete admins and mods help'] }}</p>
								</div>
							</div>
							<div class="row">
								<div class="col label">{{ lang_admin_maintenance['User status label'] }}</div>
								<div class="col inputs">
									<input type="radio" name="verified" value="1" />&#160;<strong>{{ lang_admin_maintenance['Delete any'] }}</strong>&#160;&#160;&#160;<input type="radio" name="verified" value="0" />&#160;<strong>{{ lang_admin_maintenance['Delete only verified'] }}</strong>&#160;&#160;&#160;<input type="radio" name="verified" value="2" checked="checked" />&#160;<strong>{{ lang_admin_maintenance['Delete only unverified'] }}</strong>
									<p class="info">{{ lang_admin_maintenance['User status help'] }}</p>
								</div>
							</div>
						
						</div>
					
				</div>
			<span class="submitform bottom"><input type="submit" name="prune" value="{{ lang_admin_common['Prune'] }}" tabindex="3" /></span>
			</form>
		
		</div>
		
		
		<div class="block">
		<h2>{{ lang_admin_maintenance['Add user head'] }}</h2>
		
{% if errors is not empty %}
			<div id="posterror" style="border-style:none">
				<div class="box">
					<p class="boxtitle">{{ lang_admin_maintenance['Registration errors'] }}</p>
					<div class="inbox error-info infldset">
						<p>{{ lang_admin_maintenance['Registration errors info'] }}</p>
							<ul class="error-list">
{% for error in errors %}
<li><strong>{{ error }}</strong></li>
{% endfor %}
							</ul>
					</div>
				</div>
			</div>
	
{% endif %}
		
			<form id="example" method="post" action="{{ form_action }}">
			<input name="action" type="hidden" value="add_user" />
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_maintenance['Settings subhead'] }}</p>
						<div class="inbox">
						
							<div class="row">
								<div class="col label">{{ lang_common['Username'] }}</div>
								<div class="col inputs">
									<input type="text" name="username" value="{{ POST['username'] }}" tabindex="4" />
								</div>
							</div>
							<div class="row">
								<div class="col label">{{ lang_common['Email'] }}</div>
								<div class="col inputs">
									<input type="text" name="email" value="{{ POST['email'] }}" tabindex="5" />
								</div>
							</div>
							<div class="row">
								<div class="col label">{{ lang_admin_maintenance['Generate random password label'] }}</div>
								<div class="col inputs">
									<input type="radio" name="random_pass" value="1"{% if panther_config['o_regs_verify'] == '1' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['Yes'] }}</strong>&#160;&#160;&#160;<input type="radio" name="random_pass" value="0"{% if panther_config['o_regs_verify'] == '0' %} checked="checked"{% endif %} />&#160;<strong>{{ lang_admin_common['No'] }}</strong>
									<p class="info">{{ lang_admin_maintenance['Generate random password help'] }}</p>
								</div>
							</div>
							<div class="row">
								<div class="col label">{{ lang_common['Password'] }}</div>
								<div class="col inputs">
									<input type="password" name="password1" value="{{ POST['password1'] }}" tabindex="6" />
									<p class="info">{{ lang_admin_maintenance['Password help'] }}</p>
								</div>
							</div>
							<div class="row">
								<div class="col label">{{ lang_admin_maintenance['Confirm pass'] }}</div>
								<div class="col inputs">
									<input type="password" name="password2" value="{{ POST['password2'] }}" tabindex="6" />
									<p class="info">{{ lang_admin_maintenance['Pass info'] }}</p>
								</div>
							</div>
					
						</div>
					
				</div>
				<span class="submitform bottom"><input type="submit" name="add_user" value="{{ lang_admin_common['Add'] }}" tabindex="7" /></span>
			</form>
	
	</div>

</div>
</div><!-- .admin-console -->	