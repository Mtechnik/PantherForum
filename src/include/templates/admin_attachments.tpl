<div class="content">

<div class="block pagetitle">
<h1>{{ lang_admin_common['Attachments'] }}</h1>
</div>

		<div class="block">
			<h2>{{ lang_admin_attachments['List attachments'] }}</h2>
	
					<div class="box">
						<form name="list_attachments_form" id="example" method="post" action="{{ form_action }}">
						<input name="csrf_token" type="hidden" value="{{ csrf_token }}" />
						<fieldset>
							<legend>{{ lang_admin_attachments['Search options'] }}</legend>
							<div class="inbox">
					
								<div class="row">
									<div class="col label">{{ lang_admin_attachments['Start at'] }}</div>
									<div class="col inputs">
										<span><input type="text" name="start" size="3" value="{{ increase }}" tabindex="1" /> ({{ lang_admin_attachments['Auto increase'] }} <input type="radio" name="auto_increase" value="1" tabindex="2"{% if increase != start %} checked="checked"{% endif %} /><strong>{{ lang_admin_common['Yes'] }}</strong> <input type="radio" name="auto_increase" value="0" tabindex="3"{% if increase == start %} checked="checked"{% endif %} /><strong>{{ lang_admin_common['No'] }}</strong>)</span>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_attachments['Number of attachments'] }}</div>
									<div class="col inputs">
										<span><input type="text" name="number" size="3" value="{{ limit }}"  tabindex="4" /></span>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_attachments['Order'] }}</div>
									<div class="col inputs">
										<span><input type="radio" name="order" value="0" tabindex="5"{% if order == 'a.id' %} checked="checked"{% endif %} /> {{ lang_admin_attachments['ID'] }}
										<input type="radio" name="order" value="1" tabindex="6"{% if order == 'a.downloads' %} checked="checked"{% endif %} /> {{ lang_admin_attachments['Downloads'] }}
										<input type="radio" name="order" value="2" tabindex="7"{% if order == 'a.size' %} checked="checked"{% endif %} />{{ lang_admin_attachments['Size'] }} <input type="radio" name="order" value="3" tabindex="8"{% if order == 'a.downloads*a.size' %} checked="checked"{% endif %} />{{ lang_admin_attachments['Total transfer'] }}</span>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_attachments['Direction'] }}</div>
									<div class="col inputs">
										<span><input type="radio" name="direction" value="1" tabindex="9"{% if direction == 'ASC' %} checked="checked"{% endif %} />{{ lang_admin_attachments['Ascending'] }} <input type="radio" name="direction" value="0" tabindex="10"{% if direction == 'DESC' %} checked="checked"{% endif %} />{{ lang_admin_attachments['Descending'] }}</span>
									</div>
								</div>
							
							<span class="submitform bottom"><input type="submit" name="submit" value="{{ lang_admin_attachments['List Attachments'] }}" tabindex="11" /> <input type="submit" name="delete_orphans" value="{{ lang_admin_attachments['Delete Orphans'] }}" tabindex="11" /></span>
							</div>
						</fieldset>
						</form>
					</div>
			
		</div>
{% if attachments is not empty %}
		<div class="block">
			<h2>{{ lang_admin_attachments['Attachment list'] }}</h2>

					<div class="box">
						<fieldset>
							<legend>{{ lang_admin_common['Attachments'] }}</legend>
							<div class="inbox">
							<form name="alter_attachment" method="post" action="{{ form_action }}">
								<input name="csrf_token" type="hidden" value="{{ csrf_token }}" />
								
									<div class="row">
										<div class="col">{{ lang_admin_attachments['Filename'] }}</div>
										<div class="col">{{ lang_admin_attachments['Post ID'] }}</div>
										<div class="col">{{ lang_admin_attachments['Filesize'] }}</div>
										<div class="col">{{ lang_admin_attachments['Downloads'] }}</div>
										<div class="col">{{ lang_admin_attachments['Total transfer'] }}</div>
										<div class="col">{{ lang_admin_common['Delete'] }}</div>
									</div>
								
{% for attachment in attachments %}
									<div class="row">
										<div class="col"><img src="{{ attachment['icon']['file'] }}" height="15" width="15" alt="{{ attachment['icon']['extension'] }}" /> <a href="{{ attachment['link'] }}">{{ attachment['name'] }}</a> {{ lang_admin_attachments['By']|format(attachment['username'])|raw }}</div>
										<div class="col"><a href="{{ attachment['post_link'] }}">#{{ attachment['post_id'] }}</a></div>
										<div class="col">{{ attachment['size'] }}</div>
										<div class="col">{{ attachment['downloads'] }}</div>
										<div class="col">{{ attachment['transfer'] }}</div>
										<div class="col"><input type="Submit" name="delete_attachment[{{ attachment['id'] }}]" value="{{ lang_admin_common['Delete'] }}" /></div>
									</div>
{% endfor %}
							
							</form>
							</div>
						</fieldset>
					</div>

		</div>
{% endif %}

</div>
</div><!-- .admin-console -->