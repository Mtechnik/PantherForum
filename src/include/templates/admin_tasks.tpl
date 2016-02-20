<div class="content">

<div class="block pagetitle">
<h1>{{ lang_admin_common['Tasks'] }}</h1>
</div>

	<div class="block">
		<h2>{{ lang_admin_tasks['Create new'] }}</h2>
	
			<form method="post" action="{{ form_action }}">
				<input name="csrf_token" value="{{ csrf_token }}" type="hidden" />
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_tasks['Task info'] }}</p>
						<div class="inbox">
						
								<div class="row">
									<div class="col label">{{ lang_admin_tasks['Task label'] }}</div>
									<div class="col inputs">
										<input type="text" name="new_task_title" size="25" maxlength="60" tabindex="1" />
										<p class="info">{{ lang_admin_tasks['Task title help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_tasks['Task script'] }}</div>
									<div class="col inputs">
										<select name="script">
{% for task in tasks %}
<option value="{{ task['file'] }}">{{ task['title'] }}</option>
{% endfor %}
										</select>
										<p class="info">{{ lang_admin_tasks['Task script help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_tasks['Minutes label'] }}</div>
									<div class="col inputs">
										<select name="minute">
											<option value="*">{{ lang_admin_tasks['Every minute'] }}</option>
											<option value="0">{{ lang_admin_tasks['Zero minutes'] }}</option>
											<option value="1">1</option>
											<option value="2">2</option>
											<option value="3">3</option>
											<option value="4">4</option>
											<option value="5">5</option>
											<option value="6">6</option>
											<option value="7">7</option>
											<option value="8">8</option>
											<option value="9">9</option>
											<option value="10">10</option>
											<option value="11">11</option>
											<option value="12">12</option>
											<option value="13">13</option>
											<option value="14">14</option>
											<option value="15">{{ lang_admin_tasks['Fifteen minutes'] }}</option>
											<option value="16">16</option>
											<option value="17">17</option>
											<option value="18">18</option>
											<option value="19">19</option>
											<option value="20">20</option>
											<option value="21">21</option>
											<option value="22">22</option>
											<option value="23">23</option>
											<option value="24">24</option>
											<option value="25">25</option>
											<option value="26">26</option>
											<option value="27">27</option>
											<option value="28">28</option>
											<option value="29">29</option>
											<option value="30">{{ lang_admin_tasks['Thirty minutes'] }}</option>
											<option value="31">31</option>
											<option value="32">32</option>
											<option value="33">33</option>
											<option value="34">34</option>
											<option value="35">35</option>
											<option value="36">36</option>
											<option value="37">37</option>
											<option value="38">38</option>
											<option value="39">39</option>
											<option value="40">40</option>
											<option value="41">41</option>
											<option value="42">42</option>
											<option value="43">43</option>
											<option value="44">44</option>
											<option value="45">{{ lang_admin_tasks['Fourty five minutes'] }}</option>
											<option value="46">46</option>
											<option value="47">47</option>
											<option value="48">48</option>
											<option value="49">49</option>
											<option value="50">50</option>
											<option value="51">51</option>
											<option value="52">52</option>
											<option value="53">53</option>
											<option value="54">54</option>
											<option value="55">55</option>
											<option value="56">56</option>
											<option value="57">57</option>
											<option value="58">58</option>
											<option value="59">59</option>
										</select>
										<p class="info">{{ lang_admin_tasks['Minutes help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_tasks['Hour label'] }}</div>
									<div class="col inputs">
										<select name="hour">
											<option value="*">{{ lang_admin_tasks['Every hour'] }}</option>
											<option value="0">{{ lang_admin_tasks['Zero hours'] }}</option>
											<option value="1">{{ lang_admin_tasks['One hours'] }}</option>
											<option value="2">{{ lang_admin_tasks['Two hours'] }}</option>
											<option value="3">{{ lang_admin_tasks['Three hours'] }}</option>
											<option value="4">{{ lang_admin_tasks['Four hours'] }}</option>
											<option value="5">{{ lang_admin_tasks['Five hours'] }}</option>
											<option value="6">{{ lang_admin_tasks['Six hours'] }}</option>
											<option value="7">{{ lang_admin_tasks['Seven hours'] }}</option>
											<option value="8">{{ lang_admin_tasks['Eight hours'] }}</option>
											<option value="9">{{ lang_admin_tasks['Nine hours'] }}</option>
											<option value="10">{{ lang_admin_tasks['Ten hours'] }}</option>
											<option value="11">{{ lang_admin_tasks['Eleven hours'] }}</option>
											<option value="12">{{ lang_admin_tasks['Twelve hours'] }}</option>
											<option value="13">{{ lang_admin_tasks['Thirteen hours'] }}</option>
											<option value="14">{{ lang_admin_tasks['Fourteen hours'] }}</option>
											<option value="15">{{ lang_admin_tasks['Fifteen hours'] }}</option>
											<option value="16">{{ lang_admin_tasks['Sixteen hours'] }}</option>
											<option value="17">{{ lang_admin_tasks['Seventeen hours'] }}</option>
											<option value="18">{{ lang_admin_tasks['Eighteen hours'] }}</option>
											<option value="19">{{ lang_admin_tasks['Nineteen hours'] }}</option>
											<option value="20">{{ lang_admin_tasks['Twenty hours'] }}</option>
											<option value="21">{{ lang_admin_tasks['Twenty one hours'] }}</option>
											<option value="22">{{ lang_admin_tasks['Twenty two hours'] }}</option>
											<option value="23">{{ lang_admin_tasks['Twenty three hours'] }}</option>
										</select>
										<p class="info">{{ lang_admin_tasks['Hour help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_tasks['Day label'] }}</div>
									<div class="col inputs">
										<select name="day">
											<option value="*">{{ lang_admin_tasks['Every day'] }}</option>
											<option value="1">{{ lang_admin_tasks['One days'] }}</option>
											<option value="2">{{ lang_admin_tasks['Two days'] }}</option>
											<option value="3">{{ lang_admin_tasks['Three days'] }}</option>
											<option value="4">{{ lang_admin_tasks['Four days'] }}</option>
											<option value="5">{{ lang_admin_tasks['Five days'] }}</option>
											<option value="6">{{ lang_admin_tasks['Six days'] }}</option>
											<option value="7">{{ lang_admin_tasks['Seven days'] }}</option>
											<option value="8">{{ lang_admin_tasks['Eight days'] }}</option>
											<option value="9">{{ lang_admin_tasks['Nine days'] }}</option>
											<option value="10">{{ lang_admin_tasks['Ten days'] }}</option>
											<option value="11">{{ lang_admin_tasks['Eleven days'] }}</option>
											<option value="12">{{ lang_admin_tasks['Twelve days'] }}</option>
											<option value="13">{{ lang_admin_tasks['Thirteen days'] }}</option>
											<option value="14">{{ lang_admin_tasks['Fourteen days'] }}</option>
											<option value="15">{{ lang_admin_tasks['Fifteen days'] }}</option>
											<option value="16">{{ lang_admin_tasks['Sixteen days'] }}</option>
											<option value="17">{{ lang_admin_tasks['Seventeen days'] }}</option>
											<option value="18">{{ lang_admin_tasks['Eighteen days'] }}</option>
											<option value="19">{{ lang_admin_tasks['Nineteen days'] }}</option>
											<option value="20">{{ lang_admin_tasks['Twenty days'] }}</option>
											<option value="21">{{ lang_admin_tasks['Twenty one days'] }}</option>
											<option value="22">{{ lang_admin_tasks['Twenty two days'] }}</option>
											<option value="23">{{ lang_admin_tasks['Twenty three days'] }}</option>
											<option value="24">{{ lang_admin_tasks['Twenty four days'] }}</option>
											<option value="25">{{ lang_admin_tasks['Twenty five days'] }}</option>
											<option value="26">{{ lang_admin_tasks['Twenty six days'] }}</option>
											<option value="27">{{ lang_admin_tasks['Twenty seven days'] }}</option>
											<option value="28">{{ lang_admin_tasks['Twenty eight days'] }}</option>
											<option value="29">{{ lang_admin_tasks['Twenty nine days'] }}</option>
											<option value="30">{{ lang_admin_tasks['Thirty days'] }}</option>
											<option value="31">{{ lang_admin_tasks['Thirty one days'] }}</option>
										</select>
										<p class="info">{{ lang_admin_tasks['Day help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_tasks['Month label'] }}</div>
									<div class="col inputs">
										<select name="month">
											<option value="*">{{ lang_admin_tasks['Every month'] }}</option>
											<option value="1">{{ lang_admin_tasks['One months'] }}</option>
											<option value="2">{{ lang_admin_tasks['Two months'] }}</option>
											<option value="3">{{ lang_admin_tasks['Three months'] }}</option>
											<option value="4">{{ lang_admin_tasks['Four months'] }}</option>
											<option value="5">{{ lang_admin_tasks['Five months'] }}</option>
											<option value="6">{{ lang_admin_tasks['Six months'] }}</option>
											<option value="7">{{ lang_admin_tasks['Seven months'] }}</option>
											<option value="8">{{ lang_admin_tasks['Eight months'] }}</option>
											<option value="9">{{ lang_admin_tasks['Nine months'] }}</option>
											<option value="10">{{ lang_admin_tasks['Ten months'] }}</option>
											<option value="11">{{ lang_admin_tasks['Eleven months'] }}</option>
											<option value="12">{{ lang_admin_tasks['Twelve months'] }}</option>
										</select>
										<p class="info">{{ lang_admin_tasks['Month help'] }}</p>
									</div>
								</div>
								<div class="row">
									<div class="col label">{{ lang_admin_tasks['Week day label'] }}</div>
									<div class="col inputs">
										<select name="week_day">
											<option value="*">{{ lang_admin_tasks['Every week day'] }}</option>
											<option value="0">{{ lang_admin_tasks['One week days'] }}</option>
											<option value="1">{{ lang_admin_tasks['Two week days'] }}</option>
											<option value="2">{{ lang_admin_tasks['Three week days'] }}</option>
											<option value="3">{{ lang_admin_tasks['Four week days'] }}</option>
											<option value="4">{{ lang_admin_tasks['Five week days'] }}</option>
											<option value="5">{{ lang_admin_tasks['Six week days'] }}</option>
											<option value="6">{{ lang_admin_tasks['Seven week days'] }}</option>
										</select>
										<p class="info">{{ lang_admin_tasks['Week day help'] }}</p>
									</div>
					
						         </div>
					</div>
					
					
				
				
				</div>
				<span class="submitform bottom"><input type="submit" name="add_task" value="{{ lang_admin_common['Add'] }}" /></span>
			</form>
		</div>
		
		<div class="block">
			<h2>{{ lang_admin_tasks['Configured tasks'] }}</h2>
		
				<form id="list_types" method="post" action="{{ form_action }}">
					<div class="box">
						
							<p class="boxtitle">{{ lang_admin_tasks['Configured tasks info'] }}</p>
							<div class="inbox">
							
{% for task in configured_tasks %}
									<div class="row">
										<div class="col"><a href="{{ task['edit_link'] }}">{{ lang_admin_tasks['Edit'] }}</a> - <a href="{{ task['delete_link'] }}">{{ lang_admin_tasks['Delete'] }}</a></div>
										<div class="col">{{ lang_admin_tasks['Name'] }} <strong>{{ task['title'] }}</strong> ({{ task['minute'] }} {{ task['hour'] }} {{ task['day'] }} {{ task['month'] }} {{ task['week_day'] }}) - {{ lang_admin_tasks['Next run'] }} {{ task['next_run'] }}</div>
									</div>
{% else %}
<p>{{ lang_admin_tasks['No tasks'] }}</p>
{% endfor %}
							
							</div>
						
					</div>
				</form>
				
		</div>
		
		<div class="block">	
		<h2>{{ lang_admin_tasks['Current tasks'] }}</h2>
		
			<form method="post" action="{{ form_action }}">
				<input name="csrf_token" value="{{ csrf_token }}" type="hidden" />
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_tasks['List tasks'] }}</p>
						<div class="inbox">
						
								<div class="row">
									<div class="col">{{ lang_admin_tasks['Filename'] }}</div>
									<div class="col">{{ lang_admin_tasks['Delete'] }}</div>
								</div>
								
{% for task in tasks %}
									<div class="row">
										<div class="col"><strong>{{ task['title'] }}</strong></div>
										<div class="col"><input name="del_tasks[]" type="checkbox" value="{{ task['file'] }}" /></div>
									</div>
{% endfor %}
							
						
						</div>
					
				</div>
				<span class="submitform bottom"><input type="submit" name="delete_task" value="{{ lang_admin_tasks['Delete'] }}" /></span>
			</form>
	
		
		</div>
		
		
		
			<div class="block">
		<h2>{{ lang_admin_tasks['Upload task'] }}</h2>
	
			<form method="post" enctype="multipart/form-data" action="{{ form_action }}">
				<input name="csrf_token" value="{{ csrf_token }}" type="hidden" />
				<div class="box">
					
						<p class="boxtitle">{{ lang_admin_tasks['Upload new task'] }}</p>
						<div class="inbox">
							<div class="row"><div class="col">
							<label>{{ lang_admin_tasks['Task'] }}&nbsp;&nbsp;<input name="req_file" type="file" size="40" /></label>
							<p>{{ lang_admin_tasks['Upload task warning']|raw }}</p>
							</div></div>
						</div>
					
				</div>
				<span class="submitform bottom"><input name="upload_task" type="submit" value="{{ lang_admin_tasks['Upload'] }}" /></span>
			</form>
		
    </div>

</div><!--content-->
</div><!-- .admin-console -->	