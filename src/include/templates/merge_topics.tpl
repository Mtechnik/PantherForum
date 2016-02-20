<div class="block">
	<h2 class="blocktitle">{{ lang_misc['Merge topics'] }}</h2>
	<div class="box">
		<form method="post" action="{{ form_action }}">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
			<input type="hidden" name="topics" value="{{ topics }}" />
			
				
					<p class="boxtitle">{{ lang_misc['Confirm merge legend'] }}</p>
					<div class="row">
						<div class="rbox">
							<label><input type="checkbox" name="with_redirect" value="1" />{{ lang_misc['Leave redirect'] }}<br /></label>
						</div>
					</div>
		
			<div class="blockbuttons">
			<a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a>
			<input type="submit" name="merge_topics_comply" value="{{ lang_misc['Merge'] }}" class="btn submit" />
			
			</div>
		</form>
	</div>
</div>