<div class="block bdelete">
	<h2 class="blocktitle">{{ lang_misc['Delete topics'] }}</h2>
	<div class="box">
		<form method="post" action="{{ form_action }}">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
			<input type="hidden" name="topics" value="{{ topics }}" />

					<legend>{{ lang_misc['Confirm delete legend'] }}</legend>
					<div class="infldset">
						<p>{{ lang_misc['Delete topics comply'] }}</p>
					</div>
		
			<div class="blockbuttons">
			<div class="conl"><a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a></div>
			<div class="conr"><input type="submit" name="delete_topics_comply" value="{{ lang_misc['Delete'] }}" class="btn delete"/></div>
			</div>
		</form>
	</div>
</div>
