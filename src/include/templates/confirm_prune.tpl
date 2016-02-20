	<div class="block bconfirm">
		<h2 class="blocktitle">{{ lang_admin_maintenance['Prune head'] }}</h2>
		<div class="box">
			<form method="post" action="{{ form_action }}">
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				
					<input type="hidden" name="action" value="prune" />
					<input type="hidden" name="prune_days" value="{{ prune_days }}" />
					<input type="hidden" name="prune_sticky" value="{{ prune_sticky }}" />
					<input type="hidden" name="prune_from" value="{{ prune_from }}" />
					
						<p class="boxtitle">{{ lang_admin_maintenance['Confirm prune subhead'] }}</legend>
						<div class="row">
							<p>{{ lang_admin_maintenance['Confirm prune info']|format(prune_days, forum, num_topics) }}</p>
							<p class="warntext">{{ lang_admin_maintenance['Confirm prune warn'] }}</p>
						</div>
					
				
				<p class="buttons"><input type="submit" name="prune_comply" value="{{ lang_admin_common['Prune'] }}" /><a href="javascript:history.go(-1)">{{ lang_admin_common['Go back'] }}</a></p>
			</form>
		</div>
	</div>

	
	