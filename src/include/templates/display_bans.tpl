<div class="content">

<div class="linkst">

		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_admin_common['Admin'] }} {{ lang_admin_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ ban_link }}">{{ lang_admin_common['Bans'] }}</a></li>
			<li><span>»&#160;</span><strong>{{ lang_admin_bans['Results head'] }}</strong></li>
		</ul>
		
		<div class="pagepost">
			<ul class="pagination">{{ pagination|raw }}</ul>
		</div>

</div>

<div id="bans1" class="block">
	<h2>{{ lang_admin_bans['Results head'] }}</h2>
	<div class="box">
	
			
				<div class="row th">
					<div class="col">{{ lang_admin_bans['Results username head'] }}</div>
					<div class="col">{{ lang_admin_bans['Results e-mail head'] }}</div>
					<div class="col">>{{ lang_admin_bans['Results IP address head'] }}</div>
					<div class="col">{{ lang_admin_bans['Results expire head'] }}</div>
					<div class="col">{{ lang_admin_bans['Results message head'] }}</div>
					<div class="col">{{ lang_admin_bans['Results banned by head'] }}</div>
					<div class="col">{{ lang_admin_bans['Results actions head'] }}</div>
				</div>
			
{% for ban in bans %}
				<div class="row tr">
					<div class="col">{% if ban['username'] is not empty %}{{ ban['username'] }}{% else %}&#160;{% endif %}</div>
					<div class="col">{% if ban['email'] is not empty %}{{ ban['email'] }}{% else %}&#160;{% endif %}</div>
					<div class="col">{% if ban['ip'] is not empty %}{{ ban['ip'] }}{% else %}&#160;{% endif %}</div>
					<div class="col">{{ ban['expires'] }}</div>
					<div class="col">{% if ban['message'] is not empty %}{{ ban['message'] }}{% else %}&#160;{% endif %}</div>
					<div class="col">{% if ban['creator'] is not none %}<a href="{{ ban['creator']['href'] }}">{{ ban['creator']['title'] }}</a>{% else %}{{ lang_admin_bans['Unknown'] }}{% endif %}</div>
					<div class="col"><a href="{{ ban['edit_link'] }}">{{ lang_admin_common['Edit'] }}</a> | <a href="{{ ban['delete_link'] }}">{{ lang_admin_common['Remove'] }}</a></div>
				</div>
{% else %}
<div class="row tr"><div class="col">{{ lang_admin_bans['No match'] }}</div></div>
{% endfor %}
			

	</div>
</div>

<div class="linksb">
		<div class="pagepost">
			<ul class="pagination">{{ pagination|raw }}</ul>
		</div>
		
		<ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_admin_common['Admin'] }} {{ lang_admin_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ ban_link }}">{{ lang_admin_common['Bans'] }}</a></li>
			<li><span>»&#160;</span><strong>{{ lang_admin_bans['Results head'] }}</strong></li>
		</ul>
</div>


</div>
</div><!-- .admin-console -->