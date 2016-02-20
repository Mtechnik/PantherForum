<div class="linkst">
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ forum_link }}">{{ cur_posting['forum_name'] }}</a></li>
			<li><span>»&#160;</span>{{ lang_post['Post new topic'] }}</li>
		</ul>
</div>
{% if errors is not empty %}
<div id="posterror" class="block">
	<h2><span>{{ lang_post['Post errors'] }}</span></h2>
	<div class="box">
		<div class="inbox error-info">
			<p>{{ lang_post['Post errors info'] }}</p>
			<ul class="error-list">
{% for error in errors %}
<li>{{ error }}</li>
{% endfor %}
			</ul>
		</div>
	</div>
</div>
{% elseif preview %}
<div id="postpreview" class="block bpoll">
	<!--<h2 class="blocktitle">{{ question }}</h2>-->
	<div class="box">
		
			<div class="postbody">
				<div class="postright">
					<div class="postmsg">
						
							<legend>{{ lang_poll['Poll preview'] }}</legend>
							{% for input in inputs %}
							
							<div class="row tr">
							{% if type == 1 %}
							<input name="vote" type="radio" value="{{ input['id'] }}" />
							{% else %}
							<input type="checkbox" />
							{% endif %}
							<span>{{ input['option'] }}</span>
							</div>
							{% endfor %}
							
						
					</div>
				</div>
			
		    </div>
	</div>
</div>

{% endif %}
<div class="block">
	<h2 class="blocktitle">{{ lang_post['Post new topic'] }}</h2>
	<div class="box">
		<form id="post" method="post" action="{{ form_action }}" onsubmit="return process_form(this)">
			<div class="inbox">
				
					<p class="boxtitle">{{ lang_poll['New poll legend'] }}</p>
					
						<input type="hidden" name="form_sent" value="1" />
					<div class="row">	<label>{{ lang_poll['Question'] }}<input type="text" name="req_question" value="{{ question }}" maxlength="70" tabindex="1" /></label></div>
						{% for input in inputs %}
					<div class="row"><label>{{ lang_poll['Option'] }} <input type="text" name="options[{{ input['id'] }}]" value="{{ input['option'] }}" maxlength="55" /></label></div>
						{% endfor %}
					
			
			</div>
			<div class="inbox">
		
					<p class="boxtitle">{{ lang_common['Options'] }}</p>
					<div class="infldset">
						<div class="rbox">
							<label><input type="checkbox" name="type" value="1" tabindex="2"{% if type %} checked="checked"{% endif %} />
							{{ lang_poll['Allow multiselect'] }}
						</div>
					</div>
			
			</div>
			<p class="buttons"><input type="submit" name="submit" value="{{ lang_common['Submit'] }}" tabindex="3" accesskey="s" /><input type="submit" name="preview" value="{{ lang_post['Preview'] }}" tabindex="4" accesskey="p" /></p>
		</form>
	</div>
</div>