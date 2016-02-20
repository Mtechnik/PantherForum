<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
	<head>
		<meta http-equiv="Content-Type" content="text/html"; charset="utf-8" />
		<link href="{{ error_style }}/error.css" rel="stylesheet" />
		<title>{{ page_title }}</title>
	</head>
	<body>
		<div class="server-error">
			<h3>Server Error</h3>
			<p>The server encountered an internal error or misconfiguration, we've been unable to display the page you requested.</p>
			<p>We apologise for the inconvenience this may have caused, use the link below to try again, alternatively you can return to the board index.</p>

			{% if panther_config['o_debug_mode'] %}
			<p>Errno [{{ errrno }}] {{ errstr }} in {{ errfile }} on line {{ errline }}</p>
			{% endif %}

			<p><a href="{{ index }}" class="returnindex">Back to the index</a><a href="javascript:location.reload()" class="tryagain">Try again</a></p>
		</div>
	</body>
</html>