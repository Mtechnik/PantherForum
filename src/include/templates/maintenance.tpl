<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{{ lang_common['lang_identifier'] }}" lang="{{ lang_common['lang_identifier'] }}" dir="{{ lang_common['lang_direction'] }}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>{{ page_title }}</title>
		<link rel="stylesheet" type="text/css" href="{{ style }}.css" />
		<link href="{{ panther_config['o_image_dir'] }}{{ panther_config['o_favicon'] }}" rel="shortcut icon" />
    </head>

    <body>

        <div id="panthermaint" class="panther">
            <div class="top-box"></div>
            <div class="pantherwrap">

                <div id="brdmain">
                    <div class="block">
						<h2>{{ lang_common['Maintenance'] }}</h2>
						<div class="box">
							<div class="inbox">
								<p>{{ message|raw }}</p>
							</div>
						</div>
					</div>
                </div>

            </div>
      
        </div>

    </body>
</html>