{{ pm_menu|raw }}

<div class="main">
    <div class="linkst">
	    <ul class="crumbs">
			<li><a href="{{ index_link }}">{{ lang_common['Index'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ inbox_link }}">{{ lang_common['PM'] }}</a></li>
			<li><span>»&#160;</span><a href="{{ box_link }}">{{ box_name }}</a></li>
			<li><span>»&#160;</span><strong>{{ lang_pm['My messages'] }}</strong></li>
		</ul>
		<div class="pagepost">
		    <ul class="pagination">{{ pagination|raw }}</ul>
	        <div class="postlink"><a href="{{ message_link }}" class="btn newtopic">{{ lang_pm['Send message'] }}</a></div>
       </div>
    </div>

{% if topics is not empty %}
	<div class="block bpm">
		<form method="post" action="{{ inbox_link }}" id="topics" name="posttopic">
		<input type="hidden" name="csrf_token" value="{{ csrf_token }}" />
		<input type="hidden" name="p" value="{{ page }}" />

			<h2 class="blocktitle">{{ lang_pm['My messages'] }}</h2>
			<div class="row th">
				<div class="col subject">{{ lang_pm['Subject'] }}</div>
				<div class="col sender">{{ lang_pm['Sender'] }}</div>
			    <div class="col receiver">{{ lang_pm['Receiver'] }}</div>
				<div class="col counter">{{ lang_pm['Replies'] }}</div>
				<div class="col last-post">{{ lang_pm['Last post'] }}</div>
				<div class="col select"><input type="checkbox" onclick="javascript:select_checkboxes('topics', this, '')" /></div>
			</div>
					
{% for topic in topics %}
			<div class="row tr {% if loop.index is divisible by (2) %}roweven{% else %}rowodd{% endif %}{% if topic['viewed'] == 0 %} inew{% endif %}">
				<div class="col indicator">
					<div class="{% if topic['viewed'] == '1' %}icon{% else %}icon icon-new{% endif %}"><div class="nosize"></div></div>
				</div>
				<div class="col content">
					{% if topic['viewed'] == '0' %}<strong>{% endif %}<a href="{{ topic['url'] }}">{{ topic['subject'] }}</a>{% if topic['viewed'] == '0' %}</strong>{% endif %}
					{% if topic['viewed'] == 0 %}
				   <a href="{{ topic['new_post_link'] }}" title="{{ lang_common['New posts info'] }}" class="newtext">{{ lang_common['New posts'] }}</a>
					{% endif %}
					{% if topic['num_pages'] > 1 %}
					<span class="pagestext">[ {{ topic['pagination']|raw }} ]</span>
					{% endif %}			
				</div>
							
							
				<div class="col sender">{{ topic['poster']|raw }}</div>
				<div class="col receiver">{{ topic['users']|join('<br />')|raw }}</div>
							
				<div class="col counter">{{ topic['num_replies'] }}</div>
				<div class="col last-post">
					<div class="avatar">{{ topic['last_post_avatar']|raw }}</div>
					<div class="info"><div class="row date"><a href="{{ topic['last_post_link'] }}">{{ topic['last_post'] }}</a></div> <span class="byuser">{{ lang_common['by'] }} {{ topic['last_poster']|raw }}</span></div>	
				</div>
				<div class="col select"><input type="checkbox" name="topics[]" value="{{ topic['id'] }}" /></div>
			</div>
{% endfor %}
		
			
	<div class="blockbuttons">
	<div class="conr">
	{% if box_id != 3 %}<input type="submit" name="move" value="{{ lang_pm['Move button'] }}" class="btn move"/>{% endif %}
	<input type="submit" name="delete" value="{{ lang_pm['Delete button'] }}" class="btn delete"/>
	</div>
	</div>
		</form>
			
	<div class="linksb">
		<div class="pagepost">
			<ul class="pagination">{{ pagination|raw }}</ul>
		</div>
	</div>
		
	</div>
{% else %}
	<div class="block bempty">
		<h2 class="blocktitle">{{ lang_common['Info'] }}</h2>
		<div class="box">
	 <p>{{ lang_pm['No messages in folder'] }}</p>
				
					
			
		
		</div>
	</div>
{% endif %}

</div>
</div> <!-- .pm-console -->