<div class="content">
	<div class="block">
	
	<h2>{{ lang_admin_groups['Upload image'] }}</h2>
	<div class="box">
		<form id="upload_image" method="post" enctype="multipart/form-data" action="{{ form_action }}" onsubmit="return process_form(this)">
			
			
					<p class="boxtitle">{{ lang_admin_groups['Upload image legend'] }}</p>
					<div class="row">
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
						<input type="hidden" name="MAX_FILE_SIZE" value="{{ panther_config['o_image_group_size'] }}" />
						<label><strong>{{ lang_admin_groups['File'] }}</strong><br /><input name="req_file" type="file" size="40" /><br /></label>
						<p>{{ lang_admin_groups['Image desc']|format(panther_config['o_image_group_width'], panther_config['o_image_group_height'], size, size_unit) }}</p>
					</div>
			
			
			<span class="submitform bottom">
			<a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a>
			<input type="submit" name="upload" value="{{ lang_admin_groups['Upload'] }}" class="btn submit"/>
			</span>
			
		</form>
	</div>
	
	</div>
	
</div>
</div><!-- .admin-console -->