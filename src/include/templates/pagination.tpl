{% if num_pages <= 1 %}
<li class="isactive">1</li>
{% else %}
{% for page in pages %}
{% if page['href'] is not none %}
<li><a{% if page['rel'] %} rel="{{ page['rel'] }}"{% endif %}{% if page['item'] %} class=""{% endif %} href="{{ page['href'] }}">{{ page['current'] }}</a></li>
{% elseif page['item'] is not none %}
<!--<li{% if page['item'] %} class="isactive"{% endif %}>{{ page['current'] }}</li>-->
<li class="isactive">{{ page['current'] }}</li>
{% else %}
<li class="spacer">{{ page }}</li>
{% endif %}
{% endfor %}
{% endif %}