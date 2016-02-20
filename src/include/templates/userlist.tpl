<div class="linkst">
		<ul class="pagination">{{ pagination|raw }}</ul>
</div>


<div class="block bsearch">
	<h2 class="blocktitle">{{ lang_search['User search'] }}</h2>
	<form id="userlist" method="get" action="{{ userlist_link }}">
	<div class="box">
		

			
					<!---{{ lang_ul['User find legend'] }}--->
					<div class="row col-4">
					
					<div class="col">
					{% if panther_user['g_search_users'] == '1' %}
					<label for="username">{{ lang_common['Username'] }}</label><input id="username" type="text" name="username" value="{{ username }}" maxlength="25" />
					{% endif %}
					</div>
					
					<div class="col">
						<label for="show_group">{{ lang_ul['User group'] }}</label>
						<select id="show_group" name="show_group" id="show_group">
							<option value="-1"{% if show_group == -1 %} selected="selected"{% endif %}>{{ lang_ul['All users'] }}</option>
							{% for group in panther_groups %}
							<option value="{{ group['g_id'] }}"{% if group['g_id'] == show_group %} selected="selected"{% endif %}>{{ group['g_title'] }}</option>
							{% endfor %}
						</select>
					</div>
					
					<div class="col">	
						<label for="sort_by">{{ lang_search['Sort by'] }}</label>
						<select id="sort_by" name="sort_by">
							<option value="username"{% if sort_by == 'username' %} selected="selected"{% endif %}>{{ lang_common['Username'] }}</option>
							<option value="registered"{% if sort_by == 'registered' %} selected="selected"{% endif %}>{{ lang_common['Registered'] }}</option>
							{% if show_post_count %}<option value="num_posts"{% if sort_by == 'num_posts' %} selected="selected"{% endif %}>{{ lang_ul['No of posts'] }}</option>{% endif %}
				      	</select>
					</div>
					
					<div class="col">
						<label for="sort_dir">{{ lang_search['Sort order'] }}</label>
						<select id="sort_dir" name="sort_dir"">
							<option value="ASC"{% if sort_dir == 'ASC' %} selected="selected"{% endif %}>{{ lang_search['Ascending'] }}</option>
							<option value="DESC"{% if sort_dir == 'DESC' %} selected="selected"{% endif %}>{{ lang_search['Descending'] }}</option>
						</select>
					</div>
					
					</div>	
						<p>{% if panther_user['g_post_links'] %}{{ lang_ul['User search info'] }} {% endif %}{{ lang_ul['User sort info'] }}</p>
					
			
				
		
			
		
	</div>
	
			<div class="blockbuttons"><div class="conr"><input type="submit" name="search" value="{{ lang_common['Submit'] }}" accesskey="s" class="btn submit"/></div></div>
	</form>
</div>

<div id="users1" class="block buserlist">
	<h2 class="blocktitle">{{ lang_common['User list'] }}</h2>

			
		
				<div class="row th">
					<div class="col avatar">{{ lang_common['Avatar'] }}</div>
					<div class="col username">{{ lang_common['Username'] }}</div>
					<div class="col usertitle">{{ lang_common['Title'] }}</div>
					{% if show_post_count %}<div class="col counter">{{ lang_common['Posts'] }}</div>{% endif %}
					<div class="col registered">{{ lang_common['Registered'] }}</div>
				</div>
			
			
{% for user in users %}
				<div class="row tr">
					<div class="col avatar">{{ user['avatar']|raw }}</div>
					<div class="col username">{% if user['is_online'] %}<!--<img src="{{ panther_config['o_image_dir'] }}status_online.png" title="{{ lang_online['user is online'] }}" />-->{% else %}<!-- <img src="{{ panther_config['o_image_dir'] }}status_offline.png" title="{{ lang_online['user is offline'] }}" /> --> {% endif %} {{ user['username']|raw }}</div>
					<div class="col usertitle">{{ user['title'] }}</div>
					{% if show_post_count %}<div class="col counter">{{ user['num_posts'] }}</div>{% endif %}
					<div class="col registered">{{ user['registered'] }}</div>
				</div>
{% else %}
				<div class="row tr">
					<div class="col" >{{ lang_search['No hits'] }}</div>
				</div>
{% endfor %}
		
			

</div>


<div class="linksb">
		<ul class="pagination">{{ pagination|raw }}</ul>
</div>