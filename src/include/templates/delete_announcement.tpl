<div class="content">
	<div class="block">
		<h2><span>{{ lang_admin_announcements['announcements'] }}</span></h2>
		<div class="box">
			<form id="restrictions2" method="post" action="{{ form_action }}">
				<input type="hidden" name="form_sent" value="1" />
				<input name="csrf_token" type="hidden" value="{{ csrf_token }}" />
				<input type="hidden" name="action" value="delete" />
				<input type="hidden" name="id" value="{{ id }}" />			
				<div class="inform">
						<div class="infldset">
							<div class="forminfo">
								<p><strong>{{ lang_admin_announcements['delete announcement']|raw }}</strong></p>
							</div>		
						</div>
				</div>
				<p class="buttons"><input type="submit" name="delete" value="{{ lang_admin_common['Delete'] }}" /> <a href="javascript:history.go(-1)">{{ lang_common['Go back'] }}</a></p>
			</form>
		</div>
	</div>
	
</div>
</div><!-- .admin-console -->