<div class="linkst">
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ profile_link }}">{{ lang_profile['User rep link']|format(user['username']) }}</a></li>
			<li><span>»&#160;</span><strong>{{ rep_section }}</strong></li>
		</ul>
		<div class="pagepost"><ul class="pagination">{{ pagination|raw }}</ul></div>
</div>

<div class="block">
	<h2 class="blocktitle">{{ rep_section }}</h2>

				<div class="row th">
					<div class="col">{{ lang_profile['Rep type'] }}</div>
					<div class="col">{% if section == 'rep_received' %}{{ lang_profile['Given by'] }}{% else %}{{ lang_profile['Given to'] }}{% endif %}</div>
					<div class="col">{{ lang_profile['Date rep given'] }}</div>
					<div class="col">{{ lang_profile['Rep post topic'] }}</div>
					{% if panther_user['is_admmod'] and (panther_user['g_mod_edit_users'] == '1' or panther_user['is_admin']) %}
					<div class="col">{{ lang_profile['Remove reputation'] }}</div>
					{% endif %}
				</div>

		
{% for row in reputation %}
				<div class="row tr{% if loop.index is divisible by (2) %}roweven{% else %}rowodd{% endif %}">
					<div class="col"><img src="{{ panther_config['o_image_dir'] }}{% if row['vote'] == '1' %}plus{% else %}minus{% endif %}.png" width="16" height="16">&nbsp;{% if row['vote'] == '1' %}{{ lang_profile['Positive'] }}{% else %}{{ lang_profile['Negative'] }}{% endif %}</div>
					<div class="col">{{ row['user']|raw }}</div>
					<div class="col">{{ row['given'] }}</div>
					<div class="col">{% if row['subject'] == '' %}{{ lang_profile['Deleted post'] }}{% else %}<a href="{{ row['link'] }}">{{ row['subject'] }}</a>{% endif %}</div>
					{% if panther_user['is_admmod'] and (panther_user['g_mod_edit_users'] == '1' or panther_user['is_admin']) %}
					<div class="col"><a href="javascript:remove_reputation('{{ row['id'] }}', {{ id }}, {{ page }}, '{{ section }}');">{{ lang_profile['Remove reputation'] }}</a></div>
					{% endif %}
				</div>
{% endfor %}

</div>

<div class="linksb">
		<div class="pagepost">
			<ul class="pagination">{{ pagination|raw }}</ul>
		</div>

		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ profile_link }}">{{ lang_profile['User rep link']|format(user['username']) }}</a></li>
			<li><span>»&#160;</span><strong>{{ rep_section }}</strong></li>
		</ul>
</div>