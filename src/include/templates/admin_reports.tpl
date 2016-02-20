<div class="content">

<div class="block pagetitle">
<h1>{{ lang_admin_common['Reports'] }}</h1>
</div>

	<div class="block">
		<h2>{{ lang_admin_reports['New reports head'] }}</h2>
	
			<form method="post" action="{{ form_action }}">
			<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
{% for report in reports %}
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_reports['Report subhead']|format(report['posted']) }}</p>
						<div class="inbox">
							
								<div class="row">
									<div class="col label">{% if report['reporter'] is not empty %}{{ lang_admin_reports['Reported by'] }} <a href="{{ report['reporter']['href'] }}">{{ report['reporter']['username'] }}</a>{% else %}{{ lang_admin_reports['Deleted user'] }}{% endif %}</div>
									<div class="col inputs">
									<span>{% if report['forum'] is not empty %}<a href="{{ report['forum']['href'] }}">{{ report['forum']['title'] }}</a>{% else %}{{ lang_admin_reports['Deleted'] }}{% endif %}</span>
									<span>»&#160;{% if report['topic'] is not empty %}<a href="{{ report['topic']['href'] }}">{{ report['topic']['title'] }}</a>{% else %}{{ lang_admin_reports['Deleted'] }}{% endif %}</span>
									<span>»&#160;{% if report['post'] is not empty %}<a href="{{ report['post']['href'] }}">{{ report['post']['title'] }}</a>{% else %}{{ lang_admin_posts['Deleted'] }}{% endif %}</span>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_reports['Reason'] }} <input type="submit" name="zap_id[{{ report['id'] }}]" value="{{ lang_admin_reports['Zap'] }}" /></div>
									<div class="col inputs">{{ report['message'] }}</div>
								</div>
						
						</div>
					
				</div>
{% else %}
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_common['None'] }}</p>
						<div class="inbox">
							<p class="boxinfo">{{ lang_admin_reports['No new reports'] }}</p>
						</div>
					
				</div>
{% endfor %}
			</form>

	</div>
	
	
	<div class="block">
		<h2>{{ lang_admin_reports['Last 10 head'] }}</h2>

			
{% for report in zapped %}
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_reports['Zapped subhead']|format(report['zapped']) }} {% if report['zapped_by'] is not empty %}<strong>{{ report['zapped_by'] }}</strong>{% else %}{{ lang_admin_reports['NA'] }}{% endif %}</p>
						<div class="inbox">
					
								<div class="row">
									<div class="col label">{% if report['reporter'] is not empty %}{{ lang_admin_reports['Reported by'] }} <a href="{{ report['reporter']['href'] }}">{{ report['reporter']['username'] }}</a>{% else %}{{ lang_admin_reports['Deleted user'] }}{% endif %}</div>
									<div class="col inputs">
									<span>{% if report['forum'] is not empty %}<a href="{{ report['forum']['href'] }}">{{ report['forum']['title'] }}</a>{% else %}{{ lang_admin_reports['Deleted'] }}{% endif %}</span>
									<span>»&#160;{% if report['topic'] is not empty %}<a href="{{ report['topic']['href'] }}">{{ report['topic']['title'] }}</a>{% else %}{{ lang_admin_reports['Deleted'] }}{% endif %}</span>
									<span>»&#160;{% if report['post'] is not empty %}<a href="{{ report['post']['href'] }}">{{ report['post']['title'] }}</a>{% else %}{{ lang_admin_posts['Deleted'] }}{% endif %}</span>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_reports['Reason'] }}</div>
									<div class="col inputs">{{ report['message'] }}</div>
								</div>
					
					
				</div>
{% else %}
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_common['None'] }}</p>
						<div class="inbox">
							<p class="boxinfo">{{ lang_admin_reports['No zapped reports'] }}</p>
						</div>
					
				</div>
{% endfor %}
		
		
	</div>

</div>
</div><!-- .admin-console -->	