<div class="linkst">
		<ul class="crumbs">
			<li><a href="{{ profile_link }}">{{ username }}</a></li>
			<li><span>»&#160;</span><a href="{{ view_link }}">{{ lang_warnings['Warnings'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ details_link }}">{{ lang_warnings['Details'] }}</a></li>
		</ul>
</div>

<div class="block">
	<h2 class="blocktitle">{{ lang_warnings['Warning details'] }}</h2>

	<form method="post" id="post" action="{{ form_action }}" onsubmit="return process_form(this)">
		<div class="box">
		
				<p class="boxtitle">{{ lang_warnings['Warning info'] }}</p>
				<div class="row">
					<p>{{ lang_warnings['Username']|format(issued_to)|raw }}</p>
					<p>{{ lang_warnings['Warning']|format(warning_title) }}</p>
					<p>{{ lang_warnings['Date issued']|format(issued) }}</p>
					<p>{{ warning_expires }}</p>
					<p>{{ lang_warnings['Issued by']|format(issued_by)|raw }}</a></p>
			</div>
		
		</div>
{% if panther_user['is_admmod'] %}
		<div class="box">
			
				<p class="boxtitle">{{ lang_warnings['Admin note'] }}</p>
				<div class="row">
					{{ admin_note|raw }}
			</div>
		
		</div>
{% endif %}

{% if panther_config['o_private_messaging'] == '1' %}
		<div class="box">
		
				<p class="boxtitle">{{ lang_warnings['Private message sent'] }}</p>
				<div class="row">
					{{ pm_note|raw }}
			</div>
	
		</div>
{% endif %}

		<div class="box">
			
				<p class="boxtitle">{{ lang_warnings['Copy of post'] }}</p>
				<div class="row">
{% if post_id %}
{{ message|raw }} <p><a href="{{ post_link }}">{{ lang_warnings['Link to post'] }}</a></p>
{% else %}
<p>{{ lang_warnings['Issued from profile'] }}</p>
{% endif %}
			</div>
			
		</div>
		
		
{% if panther_user['is_admmod'] and (panther_user['g_mod_warn_users'] == '1' or panther_user['is_admin']) %}
		<div class="box">
		<input type="hidden" name="delete_id" value="{{ warning_id }}" />
		<input type="hidden" name="user_id" value="{{ user_id }}" />
		<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
	
				<p class="boxtitle">{{ lang_warnings['Delete'] }}</p>
				<div class="row">
					<input type="submit" name="delete_warning" value="{{ lang_warnings['Delete warning'] }}" />
				</div>
	
		</div>
{% endif %}
	</form>

</div>

<div class="linksb">
		<ul class="crumbs">
			<li><a href="{{ profile_link }}">{{ username }}</a></li>
			<li><span>»&#160;</span><a href="{{ view_link }}">{{ lang_warnings['Warnings'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ details_link }}">{{ lang_warnings['Details'] }}</a></li>
		</ul>
</div>