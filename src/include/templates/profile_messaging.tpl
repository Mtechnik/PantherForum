<div class="main">
 
    <div class="block bprofile">
		<h2 class="blocktitle">{{ user['username'] }} - {{ lang_profile['Section messaging'] }}</h2>
	
			<form id="profile3" method="post" action="{{ form_action }}">
					<div class="box">
						<p class="boxtitle">{{ lang_profile['Contact details legend'] }}</p>
						<div class="inbox">
							<input type="hidden" name="form_sent" value="1" />
							<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
							
							<div class="">
							<label for="facebook">{{ lang_profile['Facebook'] }}</label>
							<input id="facebook" type="text" name="form[facebook]" value="{{ user['facebook'] }}" maxlength="75" />
							</div>
							
							<div class="">
							<label for="steam">{{ lang_profile['Steam'] }}</label>
							<input id="steam" type="text" name="form[steam]" value="{{ user['steam'] }}" maxlength="12" />
							</div>
							
							<div class="">
							<label for="skype">{{ lang_profile['Skype'] }}</label>
							<input id="skype" type="text" name="form[skype]" value="{{ user['skype'] }}" maxlength="50" />
							</div>
							
							<div class="">
							<label for="twitter">{{ lang_profile['Twitter'] }}</label>
							<input id="twitter" type="text" name="form[twitter]" value="{{ user['twitter'] }}" maxlength="30" />
							</div>
							
							<div class="">
							<label for="google">{{ lang_profile['Google'] }}</label>
							<input id="google" type="text" name="form[google]" value="{{ user['google'] }}" maxlength="30" />
							</div>
							
						</div>
					</div>
				<div class="blockbuttons"><div class="conl">{{ lang_profile['Instructions'] }}</div><div class="conr"><input type="submit" name="update" value="{{ lang_common['Submit'] }}" class="btn submit" /></div></div>
			</form>
		
	</div>	
</div>
</div> <!-- .profile-console -->