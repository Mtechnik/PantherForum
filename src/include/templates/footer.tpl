</div> <!-- .brd-admin -->

<div id="brdfooter" class="brd-footer">
<!--	<h2>{{ lang_common['Board footer'] }}</h2>-->
<div class="inner">
{% if footer_style is not none and (footer_style == 'viewforum' or footer_style == 'viewtopic') and controls is not empty %}
		<div class="box modcontrols">
			<ul>
{% for control in controls %}
<li><a href="{{ control['link'] }}">{{ control['lang'] }}</a>{% if control['num_pages'] is not none and control['num_pages'] > 1 %} <li><a href="{{ control['moderate_all'] }}">{{ control['all'] }}</a></li>{% endif %}</li>
{% endfor %}		
			</ul>
		</div>
{% endif %}
		
		
	<div class="box quickjump">
{{ quickjump|raw }}
		</div>


<div class="box copyright">
{% if panther_config['o_feed_type'] == '1' or panther_config['o_feed_type'] == '2' and feed is not empty %}
<p class="{{ feed['type'] }}"><a href="{{ feed['link'] }}">{{ feed['lang'] }}</a></p>
{% endif %}

{% if footer_style == 'warnings' %}		
<p>{{ lang_common['Warning links'] }}</p>
{% for link in links %}
<a href="{{ link['url'] }}">{{ link['lang'] }}</a>
{% endfor %}	
{% endif %}

<p class="powered">{{ lang_common['Powered by']|raw }}{% if panther_config['o_show_version'] %} {{ panther_config['o_cur_version'] }}{% endif %}</p>
</div>

</div>
</div>

{% if panther_config['o_debug_mode'] == '1' %}
<p class="debugtime">[ {{ debug_info }} ]</p>
{% endif %}

{% if panther_config['o_show_queries'] %}
{{ queries|raw }}
{% endif %}

</div><!-- .pantherwrap -->
</div><!-- .pantherpage -->
</body>
</html>