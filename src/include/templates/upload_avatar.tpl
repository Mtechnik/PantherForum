<div class="content">
	<div class="block">
	<h2>{{ lang_profile['Upload avatar'] }}</h2>
	<div class="box">
		<form id="upload_avatar" method="post" enctype="multipart/form-data" action="{{ form_action }}" onsubmit="return process_form(this)">
			<div class="inform">
			
					<p class="boxtitle">{{ lang_profile['Upload avatar legend'] }}</p>
					<div class="row">
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="MAX_FILE_SIZE" value="{{ panther_config['o_avatars_size'] }}" />
						<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
						<label class="required"><strong>{{ lang_profile['File'] }}<span>{{ lang_common['Required'] }}</span></strong><br /><input name="req_file" type="file" size="40" /><br /></label>
						<p>{{ lang_profile['Avatar desc'] }} {{ panther_config['o_avatars_width'] }} x {{ panther_config['o_avatars_height'] }} {{ lang_profile['pixels'] }}  {{ lang_common['and'] }} {{ avatar_size }} {{ lang_profile['bytes'] }} ({{ file_size }}</p>
					</div>
				
			</div>
			<p class="buttons"><input type="submit" name="upload" value="{{ lang_profile['Upload'] }}" /> <a href="javascript:history.go(-1)">{{ lang_common['Go back'] }}</a></p>
		</form>
	</div>
</div>
</div><!-- .admin-console -->