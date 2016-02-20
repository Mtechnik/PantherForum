<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{{ lang_common['lang_identifier'] }}" lang="{{ lang_common['lang_identifier'] }}" dir="{{ lang_common['lang_direction'] }}">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		{% if allow_index is none %}
		<meta name="robots" content="noindex, follow" />
		{% endif %}
		
		<title>{{ page_title }}</title>
		<link href="{{ stylesheet }}.css" rel="stylesheet" />
		
		{% if posting %}
		<link href="{{ panther_config['o_js_dir'] }}square.min.css" rel="stylesheet" />
		{% endif %}
		
		<!--{% if admin_style != '' %}
		<link href="{{ admin_style }}/base_admin.css" rel="stylesheet" />
		{% endif %}-->

		{% if panther_config['o_theme'] != '' %}
		<link href="{{ stylesheet }}/themes/{{ panther_config['o_theme'] }}.css" rel="stylesheet" />
		{% endif %}

		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
		<script>var url = new Array("{{ panther_config['o_base_url'] }}/", "{{ smiley_path }}", "{{ panther_config['o_js_dir'] }}");</script>
		
		{% if reputation %}
		<script src="{{ panther_config['o_js_dir'] }}reputation.js"></script>
		{% endif %}
		
		{% if posting %}
		<script src="{{ panther_config['o_js_dir'] }}bbcode.js"></script>
		<script src="{{ panther_config['o_js_dir'] }}sceditor.js"></script>
		{% endif %}
		
		{% if admin_index %}
		<script src="{{ panther_config['o_js_dir'] }}admin_notes.js"></script>
		{% endif %}
		
		{% if required_fields %}
        <script>
        /* <![CDATA[ */
        function process_form(the_form)
        {
            var required_fields = {
			{{ required_fields|raw }}
            if (document.all || document.getElementById)
            {
                for (var i = 0; i < the_form.length; ++i)
                {
                    var elem = the_form.elements[i];
                    if (elem.name && required_fields[elem.name] && !elem.value && elem.type && (/^(?:text(?:area)?|password|file)$/i.test(elem.type)))
                    {
                        alert('"' + required_fields[elem.name] + '" {{ lang_common['required field'] }}');
                        elem.focus();
                        return false;
                    }
                }
            }
            return true;
        }
        /* ]]> */
        </script>
		{% endif %}
		
		{% if common %}
		<script src="{{ panther_config['o_js_dir'] }}common.js"></script>
		{% endif %}

		<link href="{{ favicon }}" type="image/x-icon" rel="shortcut icon" />
		<link href="https://fonts.googleapis.com/css?family=Lato:400,400italic,700" rel="stylesheet" type="text/css">
		<link href="https://fonts.googleapis.com/css?family=Open+Sans:600,600italic,400,400italic,700" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
		<style type="text/css">{{ panther_config['o_colourize_groups'] }}</style>

		
		{% if page_head is not empty %}
		
			{% for link in page_head %}
				<link href="{{ link['href'] }}" {% if link['rel'] %}rel="{{ link['rel'] }}" {% endif %}{% if link['type'] %}type="{{ link['type'] }}" {% endif %}{% if link['title'] %}title="{{ link['title'] }}" {% endif %}/>
			{% endfor %}

		{% endif %}

<script type="text/javascript">
$(document).ready(function()
{ // Toggle sidebar
	$('#togglemenu').click(function()
	{
		$('body').toggleClass('collapse');
	});
});

(function($) //detect the width on page load
{
	$(document).ready(function()
	{
		var current_width = $(window).width();
		if (current_width < 900)
			$('body').addClass("mobile").addClass("collapse");
		else if (current_width > 900)
			$('body').removeClass("mobile").removeClass("collapse");
	});

	$(window).resize(function()
	{ //update the width value when the browser is resized (useful for devices which switch from portrait to landscape)
		var current_width = $(window).width();
		if (current_width < 900)
			$('body').addClass("mobile").addClass("collapse");
		else if (current_width > 900)
			$('body').removeClass("mobile").removeClass("collapse");
	});

})($);
</script>

	</head>
	
	{% if focus_element is not empty %}
	<body onload="document.getElementById('{{ focus_element[0] }}').elements['{{ focus_element[1] }}'].focus();">
	{% else %}
	<body>
	{% endif %}
		<div id="panther{{ page }}" class="panther">
		
		<div class="pantherwrap">
		<div id="brdheader" class="brd-header">
			
			
				<div id="brdtitle" class="brd-branding">
					{% if '<img src' not in panther_config['o_board_desc'] %}
						<h1><a href="{{ index_url }}">{{ panther_config['o_board_title'] }}</a></h1>
					{% endif %}
					<div id="brddesc">{{ panther_config['o_board_desc']|raw }}</div>
				</div>
				
				<div id="brdmenu" class="brd-menu">
				<div class="inner">
				    <button id="togglemenu" class="btn"><span class="text">Collapse the menu</span></button>
					<ul class="pmenu">

					{% for link in links %}
			
					<li id="{{ link['id'] }}"{% if link['class'] %} class="{{ link['class'] }}"{% endif %}><a href="{{ link['page'] }}">{{ link['title'] }}</a></li>
				
					{% endfor %}

					</ul>
				</div>
				</div>
				
		<div id="brdwelcome" class="brd-welcome">
		
		<div class="inner">
			<ul class="info">
				{% if panther_user['is_guest'] %}
				<p clas="conl">{{ lang_common['Not logged in'] }}</p>
				{% else %}
				<li class="loggedin"><span>{{ lang_common['Logged in as'] }} {{ username|raw }}</span></li>
				<li class="lastvisit"><span>{{ lang_common['Last visit']| format(last_visit) }}</span></li>
				
					{% if panther_config['o_private_messaging'] == '1' and panther_user['g_use_pm'] == '1' and panther_user['pm_enabled'] == '1' and num_messages %}
				<li class="linkalert reportlink"><a href="{{ inbox_link }}">{{ lang_common['New PM'] }}</a></li>
					{% endif %}

					{% if panther_user['is_admmod'] %}
						{% for report in reports %}
						<li class="linkalert reportlink"><a href="{{ report['link'] }}">{{ report['title'] }}</a></li>
						{% endfor %}
						
						{% if panther_config['o_maintenance'] == '1' %}
						<li class="linkalert maintenancelink"><a href="{{ maintenance_link }}">{{ lang_common['Maintenance mode enabled'] }}</a></li>
						{% endif %}
					{% endif %}
					
				{% endif %}
			</ul>

			{% if panther_user['g_read_board'] == '1' and panther_user['g_search'] == '1' %}
			<ul class="topics">
				<li>{{ lang_common['Topic searches'] }}</li>
				{% for status in status_info %}<li class="mytopics">
					<a href="{{ status['link'] }}" title="{{ status['title'] }}">{{ status['display'] }}</a>
					{% if not loop.last %}{% endif %}
				</li>{% endfor %}
			</ul>
			{% endif %}
		</div>
		</div>
		</div>
		
		{% if panther_user['g_read_board'] == '1' and panther_config['o_announcement'] == '1' %}
<div id="announce" class="brd-announce">
	<div class="hd"><h2><span>{{ lang_common['Announcement'] }}</span></h2></div>
	<div class="box">
		<div id="announce-block" class="inbox">
			<div class="usercontent">{{ panther_config['o_announcement_message']|raw }}</div>
		</div>
	</div>
</div>
		{% endif %}
<div id="brdmain" class="brd-main">