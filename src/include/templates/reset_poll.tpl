<div class="block">
	<h2 class="blocktitle">{{ lang_poll['Reset poll'] }}</h2>
	<div class="box">
		<form method="post" action="{{ form_action }}">
			<input type="hidden" name="form_sent" value="1" />
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
			<div class="inform">
				
					<legend class="warntext">{{ lang_poll['Confirm reset legend'] }}</legend>
					<div class="infldset">
						<div class="postmsg">
							<p>{{ lang_poll['Reset poll comply'] }}</p>
						</div>
					</div>
			
			</div>
			<p class="buttons"><input type="submit" name="reset" value="{{ lang_poll['Reset'] }}" /><a href="javascript:history.go(-1)">{{ lang_common['Go back'] }}</a></p>
		</form>
	</div>
</div>