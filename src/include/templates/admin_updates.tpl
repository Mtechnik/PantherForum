<script>
$(document).ready(function()
{
	$("#ajax_submit").click(function()
	{
		$.ajaxSetup({
			cache: false
		});

		$("#ajax_submit").html('<br /><img src="{{ panther_config['o_image_dir'] }}preloader.gif" width="40" height="40">');
		$.get("{{ form_action }}",
		{
			action: 'install_update'
		},
		function(data, status)
		{
			if (data != '')
			{
				$("#ajax_response").css("color", "red");
				$("#ajax_response").html(data);
				$("#ajax_response").slideDown();
				
			}
			else
			{
				$("#ajax_response").css("color", "green");
				$("#ajax_result").html(data);
				$("#ajax_response").slideDown();
			}
			
			$("#ajax_submit").html('');
			$("#release_notes").slideUp();
		});
	});
	$("#view_changelog").click(function()
	{
		$("#changelog").slideToggle();
		
		var text = $('#changelog_text').text();
		$('#changelog_text').text(
        text == "{{ lang_admin_update['View release notes'] }}" ? "{{ lang_admin_update['Hide release notes'] }}" : "{{ lang_admin_update['View release notes'] }}");
	});
});
</script>
<div class="content">
		<div id="plugin_mod" class="plugin blockform">
			<h2>{{ lang_admin_update['Admin updates'] }}</h2>
			<div class="box">
					<div class="inform">
						
							<p class="boxtitle">{{ lang_admin_update['Information'] }}</p>
							<div class="infldset">
							<p>{{ lang_admin_update['update panther'] }}</p>
							<p><span id="ajax_response" style="color:green; font-weight: bold; display: none;">{{ lang_admin_update['Update successful'] }}</span></p>
							<div id="release_notes">
							<ul>
								<li>{{ lang_admin_update['Patch'] }} <span style="color:#3ADF00; font-weight:bold;">{{ updater.panther_updates['version'] }}</span><br /></li>
								<li>{{ lang_admin_update['Release'] }} <span style="color:#BF00FF; font-weight:bold;">{{ released }}</span><br /></li>
								<li>{{ lang_admin_update['Changelog'] }} <span id="view_changelog" style="color:#FE642E; font-weight:bold;"><a style="cursor:pointer;" id="changelog_text">{{ lang_admin_update['View release notes'] }}</a></span></li>
							</ul>
							<div id="changelog">{{ changelog|nl2br }}</div>
							<p style="color:#00f">{{ lang_admin_update['Disclaimer']|raw }}</p>
							</div>
							<div id="ajax_submit"><button type="button">{{ lang_admin_update['Install update'] }}</button></div>
							</div>
						
					</div>
			</div>
		</div>
</div>
</div><!-- .admin-console -->	