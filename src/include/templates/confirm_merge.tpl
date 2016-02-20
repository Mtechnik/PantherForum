<div class="block bconfirm">
	<h2 class="blocktitle">{{ lang_admin_maintenance['confirm merge 2'] }}</h2>
	
		<form id="usermerge" method="post" action="{{ form_action }}">
			<div class="box">
				<input type="hidden" name="form_sent" value="1" />
				<input type="hidden" name="to_merge" value="{{ uid_merge }}" />
				<input type="hidden" name="to_stay" value="{{ uid_stay }}" />
				<input type="hidden" name="action" value="merge" />
				
					<p class="boxtitle">{{ lang_admin_maintenance['merge legend'] }}</p>
					<div class="row">
						<p>{{ merge_user }}</p>
					</div>
				
			</div>
			<div class="box">
				
					<p class="boxtitle">{{ lang_admin_maintenance['merge legend 2'] }}</p>
					<div class="rox">
						<p>{{ stay_user }}</p>
					</div>
			
			</div>
			<div class="blockbuttons">
			<div class="conr"><input type="submit" name="confirm_merge" value="{{ lang_admin_maintenance['merge submit'] }}" tabindex="3" class="btn submit"/></div>
			</div>
			
			<p class="topspace">{{ lang_admin_maintenance['merge message'] }}</p>
		</form>

</div>