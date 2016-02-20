<div class="block bsearch">
	<h2 class="blocktitle">{{ lang_search['Search'] }}</h2>

		<form id="search" method="get" action="{{ form_action }}">
			
			<div class="box">  
					<p class="boxtitle">{{ lang_search['Search criteria legend'] }}</p>
					<div class="row col-2">
						<input type="hidden" name="action" value="search" />
						<div class="col"><label for="keywords">{{ lang_search['Keyword search'] }}</label><input type="text" name="keywords" id="keywords" maxlength="100" /></div>
						<div class="col"><label for="author">{{ lang_search['Author search'] }}</label><input type="text" name="author" id="author" maxlength="50" /></div>
						
					</div>
					<p class="info">{{ lang_search['Search info'] }}</p>
			</div>
			
			
			    <div class="box">  
					<p class="boxtitle">{{ lang_search['Search in legend'] }}</p>
					<div class="row col-2">
						<div class="col {% if panther_config['o_search_all_forums'] == '1' or panther_user['is_admmod'] %} multiselect{% endif %}">
						<p class="label-like">{{ lang_search['Forum search'] }}</p>
					
{% if search_all_forums %}
<div class="checklist">
{% for category in categories %}
							
							<p class="label-like">{{ category['name'] }}</p>
								<div class="rbox">
{% for forum in forums if forum['category_id'] == category['id'] %}
									<label{% if forum['parent_forum'] != 0 %} style="margin-left: 20px;"{% endif %}><input type="checkbox" name="forums[]" id="forum-{{ forum['id'] }}" value="{{ forum['id'] }}" />{{ forum['name'] }}</label>
{% endfor %}
								</div>
						
{% endfor %}
</div>
{% else %}
<select id="forum" name="forum">
{% for category in categories %}
<optgroup label="{{ category['name'] }}">
{% for forum in forums if forum['category_id'] == category['id'] %}
<option value="{{ forum['id'] }}">{% if forum['parent_forum'] != 0 %}&nbsp;&nbsp;&nbsp;{% endif %} {{ forum['name'] }}</option>
{% endfor %}
</optgroup>
{% endfor %}
</select>
</label>
{% endif %}
						</div>
						<div class="col">
						<label for="search_in">{{ lang_search['Search in'] }}</label>
						<select name="search_in" id="search_in">
							<option value="0">{{ lang_search['Message and subject'] }}</option>
							<option value="1">{{ lang_search['Message only'] }}</option>
							<option value="-1">{{ lang_search['Topic only'] }}</option>
						</select>
						
						<p>{{ lang_search['Search in info'] }}</p>
						{% if panther_config['o_search_all_forums'] == '1' or panther_user['is_admmod'] %}
						<p>{{ lang_search['Search multiple forums info'] }}</p>
						{% endif %}
						</div>
					</div>
				</div>
			
			
		        <div class="box">
				
					<p class="boxtitle">{{ lang_search['Search results legend'] }}</p>
					
					<div class="row col-3">
					
						<div class="col">
						<label for="sort_by">{{ lang_search['Sort by'] }}</label>
						<select name="sort_by" id="sort_by">
							<option value="0">{{ lang_search['Sort by post time'] }}</option>
							<option value="1">{{ lang_search['Sort by author'] }}</option>
							<option value="2">{{ lang_search['Sort by subject'] }}</option>
							<option value="3">{{ lang_search['Sort by forum'] }}</option>
						</select>
						</div>
						
						<div class="col">
						<label for="sort_order">{{ lang_search['Sort order'] }}</label>
						<select name="sort_order" id="sort_order">
							<option value="DESC">{{ lang_search['Descending'] }}</option>
							<option value="ASC">{{ lang_search['Ascending'] }}</option>
						</select>
						</div>
						
						<div class="col">
						<label for="show_as">{{ lang_search['Show as'] }}</label>
						<select name="show_as" id="show_as">
							<option value="posts">{{ lang_search['Show as posts'] }}</option>
							<option value="topics">{{ lang_search['Show as topics'] }}</option>
						</select>
						</div>
						
					</div>
						<p class="info">{{ lang_search['Search results info'] }}</p>
						
					</div>
				
		
			<div class="blockbuttons"><div class="conr"><input type="submit" name="search" value="{{ lang_common['Submit'] }}" accesskey="s" class="btn submit"/></div></div>
		</form>

</div>
