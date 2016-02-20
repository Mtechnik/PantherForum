<div class="content">
<div class="block pagetitle">
<h1>Users</h1>
</div>

	<div class="block switchview grid3">
		<h2>{{ lang_admin_users['User search head'] }}</h2>
	
			<form id="find_user" method="get" action="{{ form_action }}">
				<span class="submitform top"><input type="submit" name="find_user" value="{{ lang_admin_users['Submit search'] }}" tabindex="1" /></span>
				
				<div class="box">
			    
					
						<p class="boxtitle">{{ lang_admin_users['User search subhead'] }}</p>
						
							<p class="fldsetinfo">{{ lang_admin_users['User search info'] }}</p>
							<div class="inbox">
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Username label'] }}</div>
									<div class="col input"><input type="text" name="form[username]" maxlength="25" tabindex="2" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['E-mail address label'] }}</div>
									<div class="col input"><input type="text" name="form[email]" maxlength="80" tabindex="3" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Title label'] }}</div>
									<div class="col input"><input type="text" name="form[title]" maxlength="50" tabindex="4" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Real name label'] }}</div>
									<div class="col input"><input type="text" name="form[realname]" maxlength="40" tabindex="5" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Website label'] }}</div>
									<div class="col input"><input type="text" name="form[url]" maxlength="100" tabindex="6" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Facebook label'] }}</div>
									<div class="col input"><input type="text" name="form[facebook]" maxlength="75" tabindex="7" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Steam label'] }}</div>
									<div class="col input"><input type="text" name="form[steam]" maxlength="12" tabindex="8" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Skype label'] }}</div>
									<div class="col input"><input type="text" name="form[skype]" maxlength="50" tabindex="9" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Twitter label'] }}</div>
									<div class="col input"><input type="text" name="form[twitter]" maxlength="20" tabindex="10" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Google label'] }}</div>
									<div class="col input"><input type="text" name="form[google]" maxlength="20" tabindex="11" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Location label'] }}</div>
									<div class="col input"><input type="text" name="form[location]" maxlength="30" tabindex="12" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Signature label'] }}</div>
									<div class="col input"><input type="text" name="form[signature]" maxlength="512" tabindex="13" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Admin note label'] }}</div>
									<div class="col input"><input type="text" name="form[admin_note]" maxlength="30" tabindex="14" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Posts more than label'] }}</div>
									<div class="col input"><input type="text" name="posts_greater" maxlength="8" tabindex="15" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Posts less than label'] }}</div>
									<div class="col input"><input type="text" name="posts_less" maxlength="8" tabindex="16" /></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Last post after label'] }}</div>
									<div class="col input"><input type="text" name="last_post_after" maxlength="19" tabindex="17" />
									<span>{{ lang_admin_users['Date help'] }}</span></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Last post before label'] }}</div>
									<div class="col input"><input type="text" name="last_post_before" maxlength="19" tabindex="18" />
									<span>{{ lang_admin_users['Date help'] }}</span></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Last visit after label'] }}</div>
									<div class="col input"><input type="text" name="last_visit_after" maxlength="19" tabindex="17" />
									<span>{{ lang_admin_users['Date help'] }}</span></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Last visit before label'] }}</div>
									<div class="col input"><input type="text" name="last_visit_before" size="24" maxlength="19" tabindex="18" />
									<span>{{ lang_admin_users['Date help'] }}</span></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Registered after label'] }}</div>
									<div class="col input"><input type="text" name="registered_after" size="24" maxlength="19" tabindex="19" />
									<span>{{ lang_admin_users['Date help'] }}</span></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Registered before label'] }}</div>
									<div class="col input"><input type="text" name="registered_before" size="24" maxlength="19" tabindex="20" />
									<span>{{ lang_admin_users['Date help'] }}</span></div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['Order by label'] }}</div>
									<div class="col input">
										<select name="order_by" tabindex="21">
											<option value="username" selected="selected">{{ lang_admin_users['Order by username'] }}</option>
											<option value="email">{{ lang_admin_users['Order by e-mail'] }}</option>
											<option value="num_posts">{{ lang_admin_users['Order by posts'] }}</option>
											<option value="last_post">{{ lang_admin_users['Order by last post'] }}</option>
											<option value="last_visit">{{ lang_admin_users['Order by last visit'] }}</option>
											<option value="registered">{{ lang_admin_users['Order by registered'] }}</option>
										</select>&#160;&#160;&#160;<select name="direction" tabindex="22">
											<option value="ASC" selected="selected">{{ lang_admin_users['Ascending'] }}</option>
											<option value="DESC">{{ lang_admin_users['Descending'] }}</option>
										</select>
									</div>
								</div>
								<div class="row tr">
									<div class="col label">{{ lang_admin_users['User group label'] }}</div>
									<div class="col input">
										<select name="user_group" tabindex="23">
											<option value="-1" selected="selected">{{ lang_admin_users['All groups'] }}</option>
											<option value="0">{{ lang_admin_users['Unverified users'] }}</option>
{% for group in groups %}
<option value="{{ group['id'] }}">{{ group['title'] }}</option>';
{% endfor %}
										</select>
									</div>
								</div>
			</div>
					
					
					</div>
					
					
					
					
				
				<span class="submitform bottom"><input type="submit" name="find_user" value="{{ lang_admin_users['Submit search'] }}" tabindex="25" /></span>
			</form>
		
		</div>
		
		<div class="block">
		<h2>{{ lang_admin_users['IP search head'] }}</h2>
		
			<form method="get" action="{{ form_action }}">
				<div class="box">
					
					<p class="boxtitle">{{ lang_admin_users['IP search subhead'] }}</p>
					
					<div class="inbox">
							<div class="row tr">
								<div class="col label">{{ lang_admin_users['IP address label'] }}</div>
								<div class="col input"><input type="text" name="show_users" maxlength="15" tabindex="24" />
									{{ lang_admin_users['IP address help'] }}</div>
							   </div>
								
					</div>	
					
					
				</div>
				<span class="submitform bottom"><input type="submit" value="{{ lang_admin_users['Find IP address'] }}" tabindex="26" /></span>
				
			</form>
	
		
       </div>
	
</div>
</div><!-- .admin-console -->	