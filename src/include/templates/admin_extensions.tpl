<div class="content">
{% if errors is not empty %}
<div id="posterror" class="block">
	<h2>{{ lang_admin_extensions['Extension errors'] }}</h2>
	<div class="box">
		<div class="inbox error-info">
			<p>{{ lang_admin_extensions['Extension errors info'] }}</p>
			<ul class="error-list">
{% for error in errors %}
<li><strong>{{ error }}</strong></li>
{% endfor %}
			</ul>
		</div>
	</div>
</div>
{% endif %}

<div class="block">
		<h2>{{ lang_admin_extensions['Upload extension'] }}</h2>
		<div class="box">
			<form method="post" enctype="multipart/form-data" action="{{ form_action }}">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
			<input type="hidden" name="form_sent" value="1" />
				<div class="inform">
					
						<legend>{{ lang_admin_extensions['Upload extension'] }}</legend>
						<div class="row">
							<label><input name="req_file" type="file"/></label>
						</div>
					
				</div>
				<div class="blockbuttons"><input name="upload" type="submit" value="{{ lang_admin_extensions['Upload'] }}" class="btn submit" /></div>
			</form>
		</div>
	</div>
	
{% if extensions is not empty %}
<div class="block">
		<h2>{{ lang_admin_extensions['Installed extensions'] }}</h2>
			<form method="post" action="{{ form_action }}">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<div class="box">
				
						<legend>{{ lang_admin_extensions['List extensions'] }}</legend>
					
					
								<div class="row">
									<div class="col">{{ lang_admin_extensions['Extension name'] }}</div>
									<div class="col">{{ lang_admin_extensions['Actions'] }}</div>
								</div>
						
{% for extension in extensions %}
								<div class="row">
										<div class="col">{% if extension['enabled'] == '0' %}{{ lang_admin_extensions['Disabled'] }} {% endif %}<strong>{{ extension['title'] }}</strong></div>
										<div class="col"><a href="{{ extension['enable_link'] }}">{% if extension['enabled'] %}{{ lang_admin_extensions['Disable extension'] }}{% else %}{{ lang_admin_extensions['Enable extension'] }}{% endif %}</a> <a href="{{ extension['uninstall_link'] }}">{{ lang_admin_extensions['Uninstall extension'] }}</a></div>
									</div>
{% endfor %}
						
						
			
				</div>
			</form>
	</div>
{% endif %}


{% if extension_files is not empty %}
<div class="block">
		<h2 class="block2"><span>{{ lang_admin_extensions['Uploaded extensions'] }}</span></h2>
			<form method="post" action="{{ form_action }}">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<div class="box">
			
						<p class="boxtite">{{ lang_admin_extensions['Extensions uploaded info'] }}</p>
						
							
								<div class="row">
									<div class="col">{{ lang_admin_extensions['Extension name'] }}</div>
									<div class="col">{{ lang_admin_extensions['Actions'] }}</div>
								</div>
								
{% for extension in extension_files %}
								<div class="row">
										<div class="col"><strong>{{ extension['title']|capitalize }}</strong></div>
										<div class="col"><a href="{{ extension['install_link'] }}">{{ lang_admin_extensions['Install extension'] }}</a></div>
									</div>
{% endfor %}
							
						
				
				</div>
			</form>
	</div>
{% endif %}
</div>
</div><!-- .admin-console -->