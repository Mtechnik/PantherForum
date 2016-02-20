<div class="block bdelete">
	<h2 class="blocktitle">{{ lang_misc['Delete posts'] }}</h2>
	<div class="box">
		<form method="post" action="{{  form_action }}">
			
					<legend>{{ lang_misc['Confirm delete legend'] }}</legend>
					<div class="infldset">
						<input type="hidden" name="csrf_token" value="{{  csrf_token }}" />
						<input type="hidden" name="posts" value="{{ posts }}" />
						<p>{{ lang_misc['Delete posts comply'] }}</p>
					</div>
		
			<div class="blockbuttons">
			<a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a>
			<input type="submit" name="delete_posts_comply" value="{{ lang_misc['Delete'] }}" class="btn delete"/>

			</div>
			
		</form>
	</div>
</div>