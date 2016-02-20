<div class="content">
	<div class="block">
		<h2>{{ lang_admin_index['Server statistics head'] }}</h2>
		<div id="adstats" class="box">
			
				<div class="row">
					<div>{{ lang_admin_index['Server load label'] }}</div>
					<div>
						{{ lang_admin_index['Server load data']|format(server_load, num_online) }}
					</div>
				</div>
					{% if panther_user['is_admin'] %}
				<div class="row">
					<div>{{ lang_admin_index['Environment label'] }}</div>
					<div>
						{{ lang_admin_index['Environment data OS']|format(PHP_OS) }}
						{{ lang_admin_index['Environment data version']|format(phpversion, phpinfo, lang_admin_index['Show info'])|raw }}
						{{ lang_admin_index['Environment data acc']|format(php_accelerator) }}
					</div>
				</div>
				
				<div class="row">
					<div>{{ lang_admin_index['Database label'] }}</div>
					<div>
						{{ db_version|join(' ') }}
						{{ lang_admin_index['Database data rows']|format(total_records) }}
						{{ lang_admin_index['Database data size']|format(total_size) }}
					</div>
					{% endif %}
				</div>
		
		</div>
	</div>
	
</div>
</div><!-- .admin-console -->	