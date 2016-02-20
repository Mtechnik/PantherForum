<div class="block bdelete">
	<h2 class="blocktitle">{{ lang_pm['Delete message'] }}</h2>
	<div class="box">
		<form method="post" action="{{ form_action }}">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
			<input name="form_sent" value="1" type="hidden" />
		
				<div class="forminfo">
					<h3>{% if is_topic_post %}{{ lang_delete['Topic by']|format(poster, posted) }}{% else %}{{ lang_delete['Reply by']|format(poster, posted) }}{% endif %}</h3>
					<p>{% if is_topic_post %}{{ lang_delete['Topic warning'] }}{% else %}{{ lang_delete['Warning'] }}{% endif %}</p>
					<p>{{ lang_delete['Delete info'] }}</p>
				</div>
			
			<div class="blockbuttons">
			<a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a>
			<input type="submit" name="delete" value="{{ lang_delete['Delete'] }}" class="btn delete" />
			</div>
			
		</form>
	</div>
</div>

<div id="block bpostreview">
	<div class="blockpost">
		<div class="box">
			<div class="inbox">
				<div class="postbody">
					<div class="postleft">
						
							{{ poster }}
							{{ posted }}
						
					</div>
					<div class="postright">
						<div class="postmsg">
							{{ message|raw }}
						</div>
					</div>
				</div>
	
			</div>
		</div>
	</div>
</div>