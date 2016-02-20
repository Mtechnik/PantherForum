<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{{ lang_common['lang_identifier'] }}" lang="{{ lang_common['lang_identifier'] }}" dir="{{ lang_common['lang_direction'] }}">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		{% if allow_index is none %}
		<meta name="robots" content="noindex, follow" />
		{% endif %}
		
		<title>{{ page_title }}</title>
		<link href="{{ stylesheet }}/style_admin.css" rel="stylesheet" />
	
		{% if posting %}
		<link href="{{ panther_config['o_js_dir'] }}square.min.css" rel="stylesheet" />
		{% endif %}
		
		<!-- {% if admin_style != '' %}
		<link href="{{ admin_style }}/base_admin.css" rel="stylesheet" />
		{% endif %}

		{% if panther_config['o_theme'] != '' %}
		<link href="{{ stylesheet }}/themes/{{ panther_config['o_theme'] }}.css" rel="stylesheet" />
		{% endif %} -->
		
	
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
	$('#btnsidebar').click(function()
	{
		$('body').toggleClass('collapse');
	});
});

(function($) //detect the width on page load
{
	$(document).ready(function()
	{
		var current_width = $(window).width();
		if (current_width < 800)
			$('body').addClass("mobile").addClass("collapse");
		else if (current_width > 800)
			$('body').removeClass("mobile").removeClass("collapse");
	});

	$(window).resize(function()
	{ //update the width value when the browser is resized (useful for devices which switch from portrait to landscape)
		var current_width = $(window).width();
		if (current_width < 800)
			$('body').addClass("mobile").addClass("collapse");
		else if (current_width > 800)
			$('body').removeClass("mobile").removeClass("collapse");
	});

})($);

$(function()
{
	$('.btnview .btn').on('click', function(e)
	{
		if ($(this).hasClass('list'))
			$('.switchview').removeClass('grid2').removeClass('grid3').addClass('list');
		else if($(this).hasClass('grid2'))
			$('.switchview').removeClass('list').removeClass('grid3').addClass('grid2');
		else if($(this).hasClass('grid3'))
			$('.switchview').removeClass('list').removeClass('grid2').addClass('grid3');
	});
});
</script>
	</head>

	{% if focus_element is not empty %}
	<body onload="document.getElementById('{{ focus_element[0] }}').elements['{{ focus_element[1] }}'].focus();">
	{% else %}
	<body>
	{% endif %}
		<div id="panther{{ page }}" class="panther">
		
		<div class="pantherwrap">
		
		<div id="topadmin" class="top-admin">
		<span class="dashboard"><a href="{{ panther_config['o_base_url'] }}"><span class="text">{{ lang_common['Panther dashboard'] }}</span></a>	    
        <button id="btnsidebar" class="btn"><span class="text">{{ lang_common['Collapse menu'] }}</span></button>
		</span>
		
		<ul class="left">
		<li class="goforum"><a href="{{ index_url }}"><span class="text">{{ panther_config['o_board_title'] }}</span></a></li>
		</ul>
				
	
		</div> <div id="top"></div>
<div id="brdadmin" class="brd-admin">