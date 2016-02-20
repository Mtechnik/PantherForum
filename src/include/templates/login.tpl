{% if errors is not empty %}
<div id="posterror" class="block">
	<h2>{{ lang_login['Login errors'] }}</h2>
	<div class="box">
		<div class="inbox error-info">
			<p>{{ lang_login['Login errors info'] }}</p>
			<ul class="error-list">
{% for error in errors %}
<li><strong>{{ error|raw }}</strong></li>
{% endfor %}
			</ul>
		</div>
	</div>
</div>
{% endif %}
<div class="block blogin">
	<h2 class="blocktitle">{{ lang_common['Login'] }}</h2>

		<form id="login" method="post" action="{{ form_action }}" onsubmit="return process_form(this)">
		<input type="hidden" name="form_sent" value="1" />
		<input type="hidden" name="redirect_url" value="{{ redirect_url }}" />
		
			<div class="box left">
		
					<p class="boxtitle">{{ lang_login['Login legend'] }}</p>
					    
						<div class="row">
						<label for="username" class="required">{{ lang_common['Username'] }} <span>{{ lang_common['Required'] }}</span></label>
						<input id="username" type="text" name="req_username" maxlength="25" tabindex="1" />
						</div>
						
						<div class="row">
						<label for="password" class="required">{{ lang_common['Password'] }} <span>{{ lang_common['Required'] }}</span></label>
						<input id="password" type="password" name="req_password" tabindex="2" />
						</div>
						
						<div class="row">
							<input id="save_pass" type="checkbox" name="save_pass" value="1" tabindex="3" checked="checked" />
							<label for="save_pass">{{ lang_login['Remember me'] }}</label>
						</div>
							<div class="blockbuttons"><div class="conr"><input type="submit" name="login" value="{{ lang_common['Login'] }}" tabindex="4" class="btn submit"/></div></div>
		
			</div>
			
						<div class="box right">
						
						<div class="row">
						<p>{{ lang_login['Login info'] }}</p>
						<a href="{{ register }}" tabindex="5" class="button notregister">{{ lang_login['Not registered'] }}</a>
						<a href="{{ request_password }}" tabindex="6" class="button forgotpass">{{ lang_login['Forgotten pass'] }}</a>
					</div>
		
			</div>
			
		
		</form>
	
</div>