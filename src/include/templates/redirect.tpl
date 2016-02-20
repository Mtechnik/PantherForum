<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{{ lang_common['lang_identifier'] }}" lang="{{ lang_common['lang_identifier'] }}" dir="{{ lang_common['lang_direction'] }}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="refresh" content="{{ panther_config['o_redirect_delay'] }};URL={{ destination_url }}" />
		<title>{{ page_title }}</title>
		<link rel="stylesheet" type="text/css" href="{{ css_url }}.css" />
		<link href="{{ panther_config['o_image_dir'] }}{{ panther_config['o_favicon'] }}" rel="shortcut icon" />
		
    </head>

    <body>

        <div class="redirectpage">
          

                <div class="brd-main">
<div class="block bredirect">
	<h2 class="blocktitle">{{ lang_common['Redirecting'] }}</h2>
	<div class="box">

			<p>{{ message|raw }}</p>
			<p><a href="{{ destination_url }}">{{ lang_common['Click redirect'] }}</a></p>
	</div>
</div>
                </div>

                {{ queries|raw }}

         
        </div>

    </body>
</html>