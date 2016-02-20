<div class="linkst">
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ forum_link }}">{{ cur_post['forum_name'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ post_link }}">{{ cur_post['subject'] }}</a></li>
			<li><span>»&#160;</span><strong>{{ lang_delete['Delete post'] }}</strong></li>
		</ul>
</div>

<div class="block bdelete">
	<h2 class="blocktitle">{{ lang_delete['Delete post'] }}</h2>
	<div class="box">
		<form method="post" action="{{ form_action }}">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
		
			
					<p class="boxtitle">{% if is_topic_post %}{{ lang_delete['Topic by']|format(cur_post['poster'], posted) }}{% else %}{{ lang_delete['Reply by']|format(cur_post['poster'], posted) }}{% endif %}</p>
					
					<p><span class="warning">{% if is_topic_post %}{{ lang_delete['Topic warning'] }}{% else %}{{ lang_delete['Warning'] }}{% endif %}</span></p>
					<p>{{ lang_delete['Delete info'] }}</p>
					
					{% if is_admmod and panther_config['o_sfs_api'] != '' %}<input type="checkbox" name="sfs_report" value="1" />{{ lang_delete['Mark as spam'] }}{% endif %}
			
		
			<div class="blockbuttons">
			<div class="conl"><a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a></div>
			<div class="conr"><input type="submit" name="delete" value="{{ lang_delete['Delete'] }}" class="btn delete"/></div>
			</div>
			
		</form>
	</div>
</div>

<div class="block bpostreview">
	<div class="blockpost">
		<div class="box">
			<div class="inbox">
				<div class="postbody">
					<div class="postleft">
					
							<strong>{{ cur_post['poster'] }}</strong>
							<span>{{ posted }}</span>
						
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