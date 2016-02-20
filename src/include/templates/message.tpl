<div class="content">
<div id="msg" class="block">
	<h2><span>{{ lang_common['Info'] }}</span></h2>
	<div class="box">
		<div class="inbox">
			<p>{{ message }}</p>
			{% if no_back_link == false %}<p><a href="javascript: history.go(-1)">{{ lang_common['Go back'] }}</a></p>{% endif %}
		</div>
	</div>
</div>

</div>
</div><!-- .pm-console -->