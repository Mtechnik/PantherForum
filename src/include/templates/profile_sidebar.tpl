<div id="profileconsole" class="profile-console wrapper">
	<div class="menu">

		<div class="box">
		<span class="title">{{ lang_profile['Profile menu'] }}</span>
				<ul>
				{% for section in sections %}
					<li{% if page == section['page'] %} class="isactive"{% endif %}><a href="{{ section['link'] }}">{{ section['lang'] }}</a></li>
				{% endfor %}
				</ul>
			
		</div>
	</div>