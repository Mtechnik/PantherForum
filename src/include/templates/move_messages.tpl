{{ pm_menu|raw }}

<div class="main">

<div class="linkst">
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ inbox_link }}">{{ lang_common['PM'] }}</a></li>
			<li><span>»&#160;</span><strong>{{ lang_pm['My messages'] }}</strong></li>
			<li class="postlink actions conr"><span><a href="{{ message_link }}">{{ lang_pm['Send message'] }}</a></span></li>
		</ul>
</div>

	<div class="block bmove">
		
			<form method="post" action="{{ inbox_link }}">
				<input type="hidden" name="topics" value="{{ topics|join(', ') }}" />
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
				<input name="move_comply" value="1" type="hidden" />
				
		   <div class="box">
			
					
						<p class="boxtitle">{{ lang_pm['Move messages comply'] }}</p>
						
					<select name="folder">
					{% for folder in folders %}
					<option value="{{ folder['id'] }}">{{ folder['name'] }}</option>
					{% endfor %}
					</select>
				  
			   </div>
			<div class="blockbuttons"><div class="conl"><a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a></div><div class="conr"><input type="submit" name="move" value="{{ lang_pm['Move button'] }}" class="btn move"/></div></div>
		</form>
	
</div>

</div>
</div> <!-- .pm-console -->
