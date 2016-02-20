<form id="qjump" method="get" action="{{ base_url }}/viewforum.php">
<label for="jumpto"><?php echo $lang_common['Jump to']; ?></label>
<select name="id" onchange="window.location=('{{ forum_link|raw }}'">
{% for category in categories %}
<optgroup label="{{ category['name'] }}">
{% for forum in forums if forum['category_id'] == category['id'] %}
<option data-name="{{ forum['url'] }}" value="{{ forum['id'] }}"<?php echo ($forum_id == {{ forum['id'] }}) ? ' selected="selected"' : '' ?>>{% if forum['parent_forum'] != '0' %}&nbsp;&nbsp;&nbsp;{% endif %}{{ forum['name'] }}{% if forum['redirect_url'] != '' %} &gt;&gt;&gt;{% endif %}</option>
{% endfor %}
</optgroup>
{% endfor %}
</select>
<input type="submit" value="{{ lang_common['Go'] }}" accesskey="g" />
</form>

