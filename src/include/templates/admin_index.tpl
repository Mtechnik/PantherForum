	<div class="content">
	
<div class="block pagetitle">
<h1>{{ lang_admin_common['Index'] }}</h1>
</div>

		{% if alerts is not empty %}
		<div class="block alerts">
		<h2>{{ lang_admin_index['Alerts head'] }}</h2>
		<div class="box">
			{% for alert in alerts %}
			<div class="row"><div class="col">{{ alert|raw }}</div></div>
			{% endfor %}
		</div>
		</div>
		{% endif %}
		
		
		<div class="block">
		<h2>{{ lang_admin_index['Forum admin head'] }}</h2>
	
			<div class="box">
				<p class="boxinfo">{{ lang_admin_index['Welcome to admin'] }}</p>
				<ul>
					<li>{{ lang_admin_index['Welcome 1'] }}</li>
					<li>{{ lang_admin_index['Welcome 2'] }}</li>
					<li>{{ lang_admin_index['Welcome 3'] }}</li>
					<li>{{ lang_admin_index['Welcome 4'] }}</li>
					<li>{{ lang_admin_index['Welcome 5'] }}</li>
					<li>{{ lang_admin_index['Welcome 6'] }}</li>
					<li>{{ lang_admin_index['Welcome 7'] }}</li>
					<li>{{ lang_admin_index['Welcome 8'] }}</li>
					<li>{{ lang_admin_index['Welcome 9'] }}</li>
					<li>{{ lang_admin_index['Welcome 10'] }}</li>
					<li>{{ lang_admin_index['Welcome 11'] }}</li>
					<li>{{ lang_admin_index['Welcome 12'] }}</li>
					<li>{{ lang_admin_index['Welcome 13'] }}</li>
					<li>{{ lang_admin_index['Welcome 14'] }}</li>
					<li>{{ lang_admin_index['Welcome 15'] }}</li>
				</ul>
			</div>
		
		</div>
		
		<div class="block">
		<h2>{{ lang_admin_index['Notes head'] }}</h2>
			<form>
			<div class="infldset txtarea"><input type="hidden" id="notes_url" value="{{ form_action }}" /><textarea id="dashboard_notes" class="dashboard_notes">{{ panther_config['o_admin_notes'] }}</textarea></div>
			<div style="text-align: left; margin-top: 5px; display:none" id="notes_save">
			<p class="buttons" id="ajax_submit"><button type="button">{{ lang_admin_index['save notes'] }}</button></p>
			</div>
			</form>
		</div>
		
		
		<div class="block">
		<h2>{{ lang_admin_index['About head'] }}</h2>
		<div class="box">
		<div class="row col-3">
		<div class="col">
				
					<p>{{ lang_admin_index['Panther version label'] }}</p>
					<p>
						{{ lang_admin_index['Panther version data']|format(panther_config['o_cur_version'], upgrade_link, lang_admin_index['Check for upgrade'])|raw }}
					</p>
					</div>
					<div class="col">
					<p>{{ lang_admin_index['Server statistics label'] }}</p>
					<p>
						<a href="{{ stats_link }}">{{ lang_admin_index['View server statistics'] }}</a>
					</p>
					</div>
					<div class="col">
					<p>{{ lang_admin_index['Support label'] }}</p>
					<p>
						<a href="https://www.pantherforum.org/forums/">{{ lang_admin_index['Forum label'] }}</a>
					</p>
					</div>
				</div>
			
		</div>
		</div>
</div>
</div><!-- .admin-console -->