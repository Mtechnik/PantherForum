{{ pm_menu|raw }}
<div class="content">
{% if errors is not empty %}
<div id="posterror" class="block">
	<h2><span>{{ lang_pm['Block errors'] }}</span></h2>
	<div class="box">
		<div class="inbox error-info">
			<p>{{ lang_pm['Block errors info'] }}</p>
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
<div class="blockform">
		<h2><span>{{ lang_pm['My blocked'] }}</span></h2>
		<div class="box">
			<form id="block" action="{{ form_action }}" method="post" onsubmit="return process_form(this)">
			<div class="inform">
				<fieldset>
					<legend>{{ lang_pm['Add block'] }}</legend>
					<div class="infldset">
						{{ lang_common['Username'] }}<br />
						<input type="text" name="req_username" size="25" value="{{ username }}" maxlength="30" tabindex="1" /><br />
					</div>
				</fieldset>
			</div>
			<p class="buttons"><input type="submit" name="add_block" value="{{ lang_pm['Add'] }}" accesskey="s" /></p>
			</form>
		</div>
	</div>
	<br />
{% if users is not empty %}
	<form action="{{ form_action }}" method="post">
	<div class="blockform">
		<h2><span>{{ lang_pm['My blocked'] }}</span></h2>
			<div class="inform">
					<fieldset>
						<div class="infldset">
							<table cellspacing="0">
							<thead>
								<tr>
									<th class="tcl" scope="col">{{ lang_common['Username'] }}</th>
									<th class="hidehead" scope="col">{{ lang_pm['Actions'] }}</th>
								</tr>
							</thead>
							<tbody>
{% for user in users %}
							<tr>
								<td class="tcl">{{ user['name']|raw }}</td>
								<td><input type="submit" name="remove[{{ user['id'] }}]" value="{{ lang_pm['Remove'] }}" /></td>
							</tr>
{% endfor %}
							</tbody>
							</table>
						</div>
					</fieldset>
			</div>
	</div>
	</form>
{% endif %}

</div>
</div><!-- .pm-console -->