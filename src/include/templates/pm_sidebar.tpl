<div class="pm-console wrapper">
	<div class="menu">
		
		<div class="box">
		<span class="title">{{ lang_pm['My folders'] }}</span>
			<div class="inbox">
				<ul>
				{% for folder in folders %}
				<li{% if page == folder['id'] %} class="isactive"{% endif %}><a href="{{ folder['link'] }}">{{ folder['name'] }}{% if folder['amount'] !=0 %}{{ lang_pm['Amount']|format(folder['amount']) }}{% endif %}</a></li>
				{% endfor %}
				</ul>
			</div>
		</div>
		
	    
		<div class="box">
		<span class="title">{{ lang_pm['Storage'] }}</span>
			<div class="inbox">
				<ul>
					<li>{{ lang_pm['Inbox percent']|format(percent) }}</li>
					<li><div id="pm_bar_style" style="width:{{ percent }}px;"></div></li>
					<li>{{ lang_pm['Quota label']|format(num_pms, limit)|raw }}</li>
				</ul>
			</div>
		</div>
		<br />
		
		<div class="box">
		<span class="title">{{ lang_pm['Options'] }}</span>
			<div class="inbox">
				<ul>
					<li{% if page == 'blocked' %} class="isactive"{% endif %}><a href="{{ blocked_link }}">{{ lang_pm['Blocked users'] }}</a></li>
					<li{% if page == 'folders' %} class="isactive"{% endif %}><a href="{{ folders_link }}">{{ lang_pm['PM Folders'] }}</a></li>
				</ul>
			</div>
		</div>
	</div>