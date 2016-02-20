<div class="content">

<div class="block pagetitle">
<h1>{{ lang_admin_common['Smilies'] }}</h1>
</div>

	<div class="block">
		<h2>{{ lang_admin_smilies['Current Smilies'] }}</h2>
		
		<div class="box">
{% if emoticons is not empty %}
			<form method="post" action="{{ form_action }}">
				<input name="csrf_token" value="{{ csrf_token }}" type="hidden" />
				
					<fieldset>
						<legend>{{ lang_admin_smilies['List Current Smilies'] }}</legend>
						<div class="inbox">
							
								<div class="row th">
									<div class="col position">{{ lang_admin_smilies['Position'] }}</div>
									<div class="col imgfilename">{{ lang_admin_smilies['Image Filename'] }}</div>
									<div class="col code">{{ lang_admin_smilies['Code'] }}</div>
									<div class="col image">{{ lang_admin_smilies['Image'] }}</div>
									<div class="col remove">{{ lang_admin_smilies['Remove'] }}</div>
								</div>
							
{% for emoticon in emoticons %}
									<div class="row">
										<div class="col position"><input type="text" name="disp_position[{{ emoticon['id'] }}]" value="{{ emoticon['disp_position'] }}" size="3" maxlength="3" /></div>
										<div class="col imgfilename"><select name="smilies_img[{{ emoticon['id'] }}]">
{% for image in options[emoticon['id']] %}
<option value="{{ image }}"{% if image == emoticon['file']%} selected="selected"{% endif %}>{{ image }}</option>
{% endfor %}
										</select></div>
										<div class="col code"><input type="text" name="smilies_code[{{ emoticon['id'] }}]" value="{{ emoticon['code'] }}" size="5" maxlength="60" /></div>
										<div class="col image"><img src="{{ emoticon['image'] }}" alt="{{ emoticon['code'] }}" /></div>
										<div class="col remove"><input name="remove_smilies[]" type="checkbox" value="{{ emoticon['id'] }}" /></div>
									</div>
{% endfor %}
								
					
						</div>
					</fieldset>
				
				<span class="submitform bottom"><input name="reorder" type="submit" value="{{ lang_admin_smilies['Edit smilies'] }}" /> <input name="remove" type="submit" value="{{ lang_admin_smilies['Remove Selected'] }}" /></span>
			</form>
		</div>
{% else %}
			<div class="fakeform">
				<div class="inbox">
					<p>{{ lang_admin_smilies['No smiley'] }}</p>
				</div>
			</div>
{% endif %}
       <div class="box">
			<form method="post" action="{{ form_action }}">
				<input name="csrf_token" value="{{ csrf_token }}" type="hidden" />
				
					<fieldset>
						<legend>{{ lang_admin_smilies['Submit New Smiley'] }}</legend>
						<div class="inbox">
							
								<div class="row">
									<div class="col">{{ lang_admin_smilies['Smiley Code'] }}</div>
									<div class="col">
										<input type="text" name="smiley_code" size="25" />
										<span>{{ lang_admin_smilies['Smiley Code Description'] }}</span>
									</div>
								</div>
								<div class="row">
									<div class="col">{{ lang_admin_smilies['Smiley Image'] }}</div>
									<div class="col">
										<select name="smiley_image">
											<option selected="selected" value="">{{ lang_admin_smilies['Choose Image']|raw }}</option>
{% for image in images %}
<option value="{{ image }}">{{ image }}</option>
{% endfor %}
										</select>
										<span>{{ lang_admin_smilies['Smiley Image Description'] }}</span>
									</div>
								</div>
							
						</div>
					</fieldset>
				<span class="submitform bottom"><input type="submit" name="add_smiley" value="{{ lang_admin_smilies['Submit Smiley'] }}" /></span>
			</form>
		</div>
    </div>


	<div class="block">
		<h2>{{ lang_admin_smilies['Current Images'] }}</h2>
		<div class="box">
			<form method="post" action="{{ form_action }}">
				<input name="csrf_token" value="{{ csrf_token }}" type="hidden" />
					<fieldset>
						<legend>{{ lang_admin_smilies['List Images Smilies'] }}</legend>
						<div class="inbox">
							
								<div class="row">
									<div class="col">{{ lang_admin_smilies['Image Filename'] }}</div>
									<div class="col">{{ lang_admin_smilies['Image'] }}</div>
									<div class="col">{{ lang_admin_smilies['Delete'] }}</div>
								</div>
								
{% for smiley in smiley_list %}
									<div class="row">
										<div class="col">{{ smiley['file'] }}</div>
										<div class="col"><img src="{{ smiley['image'] }}" alt="" /></div>
										<div class="col"><input name="del_smilies[{{ smiley['id'] }}]" type="checkbox" value="{{ smiley['file'] }}" /></div>
									</div>
{% endfor %}
								
						</div>
					</fieldset>
				<span class="submitform bottom"><input name="delete" type="submit" value="{{ lang_admin_smilies['Delete Selected'] }}" /></span>
			</form>
		</div>
		
		<div class="box">
			<form method="post" enctype="multipart/form-data" action="{{ form_action }}">
				<input name="csrf_token" value="{{ csrf_token }}" type="hidden" />

					<fieldset>
						<legend>{{ lang_admin_smilies['Add Images Smilies'] }}</legend>
						<div class="inbox">
							<div class="row"><label>{{ lang_admin_smilies['Image file'] }}&nbsp;&nbsp;<input name="req_file" type="file" size="40" /></label></div>
						</div>
					</fieldset>
				<span class="submitform bottom"><input name="add_image" type="submit" value="{{ lang_admin_smilies['Upload'] }}" /></span>
			</form>
		</div>
    </div>
	
</div>
</div><!-- .admin-console -->	