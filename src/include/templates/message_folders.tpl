{{ pm_menu|raw }}
<div class="content">
{% if errors is not empty %}
<div id="posterror" class="block">
	<h2><span>{{ lang_pm['Folder errors'] }}</span></h2>
	<div class="box">
		<div class="inbox error-info">
			<p>{{ lang_pm['Folder errors info'] }}</p>
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
		<h2><span>{{ lang_pm['My folders 2'] }}</span></h2>
		<div class="box">
			<form id="folder" action="{{ form_action }}" method="post" onsubmit="return process_form(this)">
			<div class="inform">
				<fieldset>
					<legend>{{ lang_pm['Add folder'] }}</legend>
					<div class="infldset">
						{{ lang_pm['Folder name'] }}<br />
						<input type="text" name="req_folder" size="25" value="{{ folder }}" maxlength="30" tabindex="1" /><br />
					</div>
				</fieldset>
			</div>
			<p class="buttons"><input type="submit" name="add_folder" value="{{ lang_pm['Add'] }}" accesskey="s" /></p>
			</form>
		</div>
	</div>
	<br />
{% if folders is not empty %}
	<form action="{{ form_action }}" method="post">
	<div class="blockform">
		<h2><span>{{ lang_pm['My folders'] }}</span></h2>
			<div class="inform">
					<fieldset>
						<div class="infldset">
							<table cellspacing="0">
							<thead>
								<tr>
									<th class="tcl" scope="col">{{ lang_pm['Folder name'] }}</th>
									<th class="hidehead" scope="col">{{ lang_pm['Actions'] }}</th>
								</tr>
							</thead>
							<tbody>
{% for folder in folders %}
				<tr>
					<td class="tcl"><input type="text" name="folder[{{ folder['id'] }}]" value="{{ folder['name'] }}" size="24" maxlength="30" /></td>
					<td><input type="submit" name="update[{{ folder['id'] }}]" value="{{ lang_pm['Update'] }}" />&#160;<input type="submit" name="remove[{{ folder['id'] }}]" value="{{ lang_pm['Remove'] }}" /></td>
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