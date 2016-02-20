</div> <!-- .brd-admin -->

<div id="adminfooter" class="admin-footer">

	<div class="box">
		<div class="jump-to">
			
{{ quickjump|raw }}
		
		</div>
		
		



<div class="copyright">
{% if panther_config['o_feed_type'] == '1' or panther_config['o_feed_type'] == '2' and feed is not empty %}
<p class="{{ feed['type'] }}"><a href="{{ feed['link'] }}">{{ feed['lang'] }}</a></p>
{% endif %}
{% if footer_style == 'warnings' %}
			
				<p>{{ lang_common['Warning links'] }}</p>
{% for link in links %}
<a href="{{ link['url'] }}">{{ link['lang'] }}</a>
{% endfor %}
			
{% endif %}

<p>{{ lang_common['Powered by']|raw }}{% if panther_config['o_show_version'] %} {{ panther_config['o_cur_version'] }}{% endif %}</p>
			</div>
		
		
		
	</div>
	
	
{% if panther_config['o_debug_mode'] == '1' %}
<p class="debugtime">[ {{ debug_info }} ]</p>
{% endif %}

{% if panther_config['o_show_queries'] %}
{{ queries|raw }}
{% endif %}
</div>


</div><!-- .pantherwrap -->
</div><!-- .pantherpage -->
</body>
</html>