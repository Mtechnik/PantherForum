{{ pm_menu|raw }}

<div class="main">

<div class="linkst">
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ inbox_link }}">{{ lang_common['PM'] }}</a></li>
			<li><span>»&#160;</span><strong>{{ lang_pm['My messages'] }}</strong></li>
		</ul>
</div>



<div class="block bmove">
		<div class="box">
			<form method="post" action="{{ inbox_link }}">
				<input type="hidden" name="topics" value="{{ topics|join(',') }}" />
				<input name="delete_comply" value="1" type="hidden" />
				<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />

				<div class="forminfo">
					<p>{{ lang_pm['Delete messages comply'] }}</p>
				</div>
		
			<div class="blockbuttons">
			<div class="conl"><a href="javascript:history.go(-1)" class="btn goback">{{ lang_common['Go back'] }}</a></div>
			<div class="conr"><input type="submit" name="delete" value="{{ lang_pm['Delete button'] }}" class="btn delete" /></div>
			</div>
		</form>
	</div>
</div>
</div></div>