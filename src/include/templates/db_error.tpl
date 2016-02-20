<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
	<head>
		<meta http-equiv="Content-Type" content="text/html"; charset="utf-8" />
		<link href="{{ error_style }}/error.css" rel="stylesheet" />
		<title>{{ page_title }}</title>
	</head>
	<body>
		<div class="database-error">
			<h3>Database Error</h3>
			<p>The server encountered a database error, we've been unable to display the page you requested. Please try again.</p>
			<p>If the issue persists, contact the server administrator and inform them of the time the error occured, along with anything you may have done which could have caused the error.</p>

			{% if panther_config['o_debug_mode'] %}
			<p>Database Reported: <strong>{{ error }}</strong></p><p>Failed SQL: <strong>{{ sql }}</strong></p><p>Parameters: <strong>{{ debug|raw }}<strong></p>
			{% endif %}

			<p><a href="{{ index }}" class="returnindex">Back to the index</a><a href="javascript:location.reload()" class="tryagain">Try again</a> <a class="tryagain" href="mailto:{{ panther_config['o_webmaster_email'] }}">Contact server administrator</a></p>
		</div>
	</body>
</html>