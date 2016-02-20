<div class="block bdelete">
	<h2 class="blocktitle">{{ lang_poll['Delete poll'] }}</h2>
	<div class="box">
		<form method="post" action="{{ form_action }}">
			<input type="hidden" name="form_sent" value="1" />
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
			<div class="inform">
				
					<p class="boxtitle">{{ lang_poll['Confirm delete legend'] }}</p>
					<div class="row">
						<div class="postmsg">
							<p>{{ lang_poll['Delete poll comply'] }}</p>
						</div>
					</div>
				
			</div>
			
			<div class="blockbuttons">
			<div class="conl"><a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a>	</div>
			<div class="conr"><input type="submit" name="delete" value="{{ lang_poll['Delete'] }}" class="btn delete"/></div>
			</div>
			
		</form>
	</div>
</div>