<div class="main">

<div class="block bprofile">
		<h2 class="blocktitle">{{ user['username'] }} - {{ lang_profile['Section personality'] }}</h2>
		
	
			<form id="profile4" method="post" action="{{ form_action }}">
				<input type="hidden" name="form_sent" value="1" />
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				
				{% if panther_config['o_avatars'] == '1'%}
					<div id="profileavatar" class="box">
						<p class="boxtitle">{{ lang_profile['Avatar legend'] }}</p>
						<div class="infldset">
						
						<div class="useravatar">{{ user_avatar|raw }}</div>
						
							<p class="info">{{ lang_profile['Avatar info']|raw }}</p>
							<div class="actions">
							{% if panther_config['o_avatar_upload'] == '1' and can_delete %}<a href="{{ avatar_link }}">{{ lang_profile['Change avatar'] }}</a>{% endif %}
							{% if can_delete %}<a href="{{ delete_link }}">{{ lang_profile['Delete avatar'] }}</a>{% elseif panther_config['o_avatar_upload'] == '1' %}<a href="{{ upload_link }}">{{ lang_profile['Upload avatar'] }}</a>{% endif %}
							<a href="{{ gravatar_link }}">{% if user['use_gravatar'] == '1' %}{{ lang_profile['Disable gravatar'] }}{% else %}{{ lang_profile['Use gravatar'] }}{% endif %}</a></div>
						</div>
					</div>
				{% endif %}
				
				{% if panther_config['o_signatures'] == '1' %}			

		
					<div class="box">
						<p class="boxtitle">{{ lang_profile['Signature legend'] }}</p>
						<div class="infldset">
							<p class="info">{{ lang_profile['Signature info'] }}</p>
							
							<div class="row txtarea">
								<label for="signature">{{ lang_profile['Sig max size']|format(signature_length, panther_config['p_sig_lines']) }}</label>
								<textarea id="signature" name="signature" class="scedit_bbcode" rows="6" cols="65">{{ user['signature'] }}</textarea>
							</div>
							
							<ul class="bblinks">
								<li><span><a href="{{ quickpost_links['bbcode'] }}" onclick="window.open(this.href); return false;">{{ lang_common['BBCode'] }}</a> {% if panther_config['p_sig_bbcode'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
								<li><span><a href="{{ quickpost_links['url'] }}" onclick="window.open(this.href); return false;">{{ lang_common['url tag'] }}</a> {% if panther_config['p_sig_bbcode'] == '1' and panther_user['g_post_links'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
								<li><span><a href="{{ quickpost_links['img'] }}" onclick="window.open(this.href); return false;">{{ lang_common['img tag'] }}</a> {% if panther_config['p_sig_bbcode'] == '1' and panther_config['p_sig_img_tag'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
								<li><span><a href="{{ quickpost_links['smilies'] }}" onclick="window.open(this.href); return false;">{{ lang_common['Smilies'] }}</a> {% if panther_config['o_smilies'] == '1' %}{{ lang_common['on'] }}{% else %}{{ lang_common['off'] }}{% endif %}</span></li>
							</ul>
						</div>
					</div>
					
					<div class="box">
							
							{% if user['signature'] != '' %}
							<p class="boxtitle">{{ lang_profile['Sig preview'] }}</p>
							<div class="postsignature postmsg">
							{{ signature|raw }}
							</div>
							{% else %}
							<p>{{ lang_profile['No sig'] }}</p>
							{% endif %}
							
					
					</div>
				{% endif %}
				
				<div class="blockbuttons"><div class="conl">{{ lang_profile['Instructions'] }}</div><div class="conr"><input type="submit" id="submit" name="update" value="{{ lang_common['Submit'] }}" class="btn submit" /></div></div>
			</form>
	
    </div>
</div>
</div> <!-- .profile-console -->