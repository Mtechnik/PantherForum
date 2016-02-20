<div class="content">
{% if errors is not empty %}
<div id="posterror" class="block">
	<h2><span>{{ lang_admin_extensions['Extension errors'] }}</span></h2>
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
<br />
{% endif %}
		<div class="block">
			<h2>{{ lang_admin_extensions['Install extension'] }}</h2>
			<div class="box">
				<form method="post" action="{{ form_action }}">
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<input type="hidden" name="form_sent" value="1" />
					<div class="inform">
			
							<p class="blocktitle">{{ lang_admin_extensions['Extension information'] }}</p>
							<div class="infldset">
								<p>{{ lang_admin_extensions['Extension title']|format(extension['title']) }}</p>
								<p>{{ lang_admin_extensions['Extension author']|format(extension['author']) }}</p>
								<p>{{ lang_admin_extensions['Extension version']|format(extension['version']) }}</p>
								<p>{{ lang_admin_extensions['Extension description']|format(extension['description']) }}</p>
							</div>
		
					</div>
					{% if warnings is not empty %}
					<div id="adalerts" class="inform">
		
							<p class="blocktitle">{{ lang_admin_extensions['Install warnings info'] }}</p>
							<div class="infldset">
							{% for warning in warnings %}
								<p>{{ warning }}</p>
							{% endfor %}
							</div>
		
					</div>
					{% endif %}
					{{ lang_admin_extensions['Enable after install'] }}<input type="checkbox" name="enable" value="1" checked="checked" />
					<div class="blockbuttons"><a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a><input type="submit" name="submit" value="{{ lang_admin_extensions['Install extension'] }}" class="btn submit"/></div>
			</div>
		</div>
</div>
</div><!-- .admin-console -->