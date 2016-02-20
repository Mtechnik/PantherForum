<div class="linkst">
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ forum_link }}">{{ cur_post['forum_name'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ post_link }}">{{ cur_post['subject'] }}</a></li>
			<li><span>»&#160;</span>{{ lang_misc['Report post'] }}</li>
		</ul>
</div>
{% if errors is not empty %}
<div class="block berror">
	<h2 class="blocktitle">{{ lang_misc['Report errors'] }}</h2>
	<div class="box">
		
			<p>{{ lang_misc['Report errors info'] }}</p>
			<ul class="error-list">
{% for error in errors %}
<li><strong>{{ error }}</strong></li>
{% endfor %}
			</ul>
		
	</div>
</div>
{% endif %}
<div class="block breport">
	<h2 class="blocktitle">{{ lang_misc['Report post'] }}</h2>
	<div class="box">
		<form id="report" method="post" action="{{ form_action }}" onsubmit="this.submit.disabled=true;if(process_form(this)){return true;}else{this.submit.disabled=false;return false;}">
					<p class="boxtitle">{{ lang_misc['Reason desc'] }}</p>
					<div class="row">
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
						<label class="required"><strong>{{ lang_misc['Reason'] }} <span>{{ lang_common['Required'] }}</span></strong><br /><textarea name="req_reason" rows="5" cols="60">{{ message }}</textarea><br /></label></div>
			
			<div class="blockbuttons">
			<div class="conl"><a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a></div>
			<div class="conr"><input type="submit" name="submit" value="{{ lang_common['Submit'] }}" accesskey="s" class="btn submit"/></div>
			</div>
			
		</form>
	</div>
</div>