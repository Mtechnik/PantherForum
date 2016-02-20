<div class="content">
	<div class="block">

		<h2>{{ lang_admin_tasks['Edit task'] }}</h2>
		<div class="box">
			<form method="post" action="{{ form_action }}">
				<input name="csrf_token" value="{{ csrf_token }}" type="hidden" />
				<input name="id" value="{{ id }}" type="hidden" />
				<div class="inform">
					<fieldset>
						<legend>{{ lang_admin_tasks['Edit task info'] }}</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">{{ lang_admin_tasks['Task label'] }}</th>
									<td>
										<input type="text" name="task_title" size="25" maxlength="60" tabindex="1" value="{{ cur_task['title'] }}" />
										<span>{{ lang_admin_tasks['Task title help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_tasks['Task script'] }}</th>
									<td>
										<select name="script">
{% for task in tasks %}
<option value="{{ task['option'] }}"{% if task['option'] == cur_task['script'] %} selected="selected"{% endif %}>{{ task['title'] }}</option>
{% endfor %}
										</select>
										<span>{{ lang_admin_tasks['Task script help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_tasks['Minutes label'] }}</th>
									<td>
										<select name="minute">
											<option value="*"{% if cur_task['minute'] == '*' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Every minute'] }}</option>
											<option value="0"{% if cur_task['minute'] == '0' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Zero minutes'] }}</option>
											<option value="1"{% if cur_task['minute'] == '1' %} selected="selected"{% endif %}>1</option>
											<option value="2"{% if cur_task['minute'] == '2' %} selected="selected"{% endif %}>2</option>
											<option value="3"{% if cur_task['minute'] == '3' %} selected="selected"{% endif %}>3</option>
											<option value="4"{% if cur_task['minute'] == '4' %} selected="selected"{% endif %}>4</option>
											<option value="5"{% if cur_task['minute'] == '5' %} selected="selected"{% endif %}>5</option>
											<option value="6"{% if cur_task['minute'] == '6' %} selected="selected"{% endif %}>6</option>
											<option value="7"{% if cur_task['minute'] == '7' %} selected="selected"{% endif %}>7</option>
											<option value="8"{% if cur_task['minute'] == '8' %} selected="selected"{% endif %}>8</option>
											<option value="9"{% if cur_task['minute'] == '9' %} selected="selected"{% endif %}>9</option>
											<option value="10"{% if cur_task['minute'] == '10' %} selected="selected"{% endif %}>10</option>
											<option value="11"{% if cur_task['minute'] == '11' %} selected="selected"{% endif %}>11</option>
											<option value="12"{% if cur_task['minute'] == '12' %} selected="selected"{% endif %}>12</option>
											<option value="13"{% if cur_task['minute'] == '13' %} selected="selected"{% endif %}>13</option>
											<option value="14"{% if cur_task['minute'] == '14' %} selected="selected"{% endif %}>14</option>
											<option value="15"{% if cur_task['minute'] == '15' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Fifteen minutes'] }}</option>
											<option value="16"{% if cur_task['minute'] == '16' %} selected="selected"{% endif %}>16</option>
											<option value="17"{% if cur_task['minute'] == '17' %} selected="selected"{% endif %}>17</option>
											<option value="18"{% if cur_task['minute'] == '18' %} selected="selected"{% endif %}>18</option>
											<option value="19"{% if cur_task['minute'] == '19' %} selected="selected"{% endif %}>19</option>
											<option value="20"{% if cur_task['minute'] == '20' %} selected="selected"{% endif %}>20</option>
											<option value="21"{% if cur_task['minute'] == '21' %} selected="selected"{% endif %}>21</option>
											<option value="22"{% if cur_task['minute'] == '22' %} selected="selected"{% endif %}>22</option>
											<option value="23"{% if cur_task['minute'] == '23' %} selected="selected"{% endif %}>23</option>
											<option value="24"{% if cur_task['minute'] == '24' %} selected="selected"{% endif %}>24</option>
											<option value="25"{% if cur_task['minute'] == '25' %} selected="selected"{% endif %}>25</option>
											<option value="26"{% if cur_task['minute'] == '26' %} selected="selected"{% endif %}>26</option>
											<option value="27"{% if cur_task['minute'] == '27' %} selected="selected"{% endif %}>27</option>
											<option value="28"{% if cur_task['minute'] == '28' %} selected="selected"{% endif %}>28</option>
											<option value="29"{% if cur_task['minute'] == '29' %} selected="selected"{% endif %}>29</option>
											<option value="30"{% if cur_task['minute'] == '30' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Thirty minutes'] }}</option>
											<option value="31"{% if cur_task['minute'] == '31' %} selected="selected"{% endif %}>31</option>
											<option value="32"{% if cur_task['minute'] == '32' %} selected="selected"{% endif %}>32</option>
											<option value="33"{% if cur_task['minute'] == '33' %} selected="selected"{% endif %}>33</option>
											<option value="34"{% if cur_task['minute'] == '34' %} selected="selected"{% endif %}>34</option>
											<option value="35"{% if cur_task['minute'] == '35' %} selected="selected"{% endif %}>35</option>
											<option value="36"{% if cur_task['minute'] == '36' %} selected="selected"{% endif %}>36</option>
											<option value="37"{% if cur_task['minute'] == '37' %} selected="selected"{% endif %}>37</option>
											<option value="38"{% if cur_task['minute'] == '38' %} selected="selected"{% endif %}>38</option>
											<option value="39"{% if cur_task['minute'] == '39' %} selected="selected"{% endif %}>39</option>
											<option value="40"{% if cur_task['minute'] == '40' %} selected="selected"{% endif %}>40</option>
											<option value="41"{% if cur_task['minute'] == '41' %} selected="selected"{% endif %}>41</option>
											<option value="42"{% if cur_task['minute'] == '42' %} selected="selected"{% endif %}>42</option>
											<option value="43"{% if cur_task['minute'] == '43' %} selected="selected"{% endif %}>43</option>
											<option value="44"{% if cur_task['minute'] == '44' %} selected="selected"{% endif %}>44</option>
											<option value="45"{% if cur_task['minute'] == '45' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Fourty five minutes'] }}</option>
											<option value="46"{% if cur_task['minute'] == '46' %} selected="selected"{% endif %}>46</option>
											<option value="47"{% if cur_task['minute'] == '47' %} selected="selected"{% endif %}>47</option>
											<option value="48"{% if cur_task['minute'] == '48' %} selected="selected"{% endif %}>48</option>
											<option value="49"{% if cur_task['minute'] == '49' %} selected="selected"{% endif %}>49</option>
											<option value="50"{% if cur_task['minute'] == '50' %} selected="selected"{% endif %}>50</option>
											<option value="51"{% if cur_task['minute'] == '51' %} selected="selected"{% endif %}>51</option>
											<option value="52"{% if cur_task['minute'] == '52' %} selected="selected"{% endif %}>52</option>
											<option value="53"{% if cur_task['minute'] == '53' %} selected="selected"{% endif %}>53</option>
											<option value="54"{% if cur_task['minute'] == '54' %} selected="selected"{% endif %}>54</option>
											<option value="55"{% if cur_task['minute'] == '55' %} selected="selected"{% endif %}>55</option>
											<option value="56"{% if cur_task['minute'] == '56' %} selected="selected"{% endif %}>56</option>
											<option value="57"{% if cur_task['minute'] == '57' %} selected="selected"{% endif %}>57</option>
											<option value="58"{% if cur_task['minute'] == '58' %} selected="selected"{% endif %}>58</option>
											<option value="59"{% if cur_task['minute'] == '59' %} selected="selected"{% endif %}>59</option>
										</select>
										<span>{{ lang_admin_tasks['Minutes help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_tasks['Hour label'] }}</th>
									<td>
										<select name="hour">
											<option value="*"{% if cur_task['hour'] == '*' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Every hour'] }}</option>
											<option value="0"{% if cur_task['hour'] == '0' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Zero hours'] }}</option>
											<option value="1"{% if cur_task['hour'] == '1' %} selected="selected"{% endif %}>{{ lang_admin_tasks['One hours'] }}</option>
											<option value="2"{% if cur_task['hour'] == '2' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Two hours'] }}</option>
											<option value="3"{% if cur_task['hour'] == '3' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Three hours'] }}</option>
											<option value="4"{% if cur_task['hour'] == '4' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Four hours'] }}</option>
											<option value="5"{% if cur_task['hour'] == '5' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Five hours'] }}</option>
											<option value="6"{% if cur_task['hour'] == '6' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Six hours'] }}</option>
											<option value="7"{% if cur_task['hour'] == '7' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Seven hours'] }}</option>
											<option value="8"{% if cur_task['hour'] == '8' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Eight hours'] }}</option>
											<option value="9"{% if cur_task['hour'] == '9' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Nine hours'] }}</option>
											<option value="10"{% if cur_task['hour'] == '10' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Ten hours'] }}</option>
											<option value="11"{% if cur_task['hour'] == '11' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Eleven hours'] }}</option>
											<option value="12"{% if cur_task['hour'] == '12' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Twelve hours'] }}</option>
											<option value="13"{% if cur_task['hour'] == '13' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Thirteen hours'] }}</option>
											<option value="14"{% if cur_task['hour'] == '14' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Fourteen hours'] }}</option>
											<option value="15"{% if cur_task['hour'] == '15' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Fifteen hours'] }}</option>
											<option value="16"{% if cur_task['hour'] == '16' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Sixteen hours'] }}</option>
											<option value="17"{% if cur_task['hour'] == '17' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Seventeen hours'] }}</option>
											<option value="18"{% if cur_task['hour'] == '18' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Eighteen hours'] }}</option>
											<option value="19"{% if cur_task['hour'] == '19' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Nineteen hours'] }}</option>
											<option value="20"{% if cur_task['hour'] == '20' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Twenty hours'] }}</option>
											<option value="21"{% if cur_task['hour'] == '21' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Twenty one hours'] }}</option>
											<option value="22"{% if cur_task['hour'] == '22' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Twenty two hours'] }}</option>
											<option value="23"{% if cur_task['hour'] == '23' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Twenty three hours'] }}</option>
										</select>
										<span>{{ lang_admin_tasks['Hour help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_tasks['Day label'] }}</th>
									<td>
										<select name="day">
											<option value="*"{% if cur_task['day'] == '*' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Every day'] }}</option>
											<option value="1"{% if cur_task['day'] == '1' %} selected="selected"{% endif %}>{{ lang_admin_tasks['One days'] }}</option>
											<option value="2"{% if cur_task['day'] == '2' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Two days'] }}</option>
											<option value="3"{% if cur_task['day'] == '3' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Three days'] }}</option>
											<option value="4"{% if cur_task['day'] == '4' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Four days'] }}</option>
											<option value="5"{% if cur_task['day'] == '5' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Five days'] }}</option>
											<option value="6"{% if cur_task['day'] == '6' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Six days'] }}</option>
											<option value="7"{% if cur_task['day'] == '7' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Seven days'] }}</option>
											<option value="8"{% if cur_task['day'] == '8' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Eight days'] }}</option>
											<option value="9"{% if cur_task['day'] == '9' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Nine days'] }}</option>
											<option value="10"{% if cur_task['day'] == '10' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Ten days'] }}</option>
											<option value="11"{% if cur_task['day'] == '11' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Eleven days'] }}</option>
											<option value="12"{% if cur_task['day'] == '12' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Twelve days'] }}</option>
											<option value="13"{% if cur_task['day'] == '13' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Thirteen days'] }}</option>
											<option value="14"{% if cur_task['day'] == '14' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Fourteen days'] }}</option>
											<option value="15"{% if cur_task['day'] == '15' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Fifteen days'] }}</option>
											<option value="16"{% if cur_task['day'] == '16' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Sixteen days'] }}</option>
											<option value="17"{% if cur_task['day'] == '17' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Seventeen days'] }}</option>
											<option value="18"{% if cur_task['day'] == '18' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Eighteen days'] }}</option>
											<option value="19"{% if cur_task['day'] == '19' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Nineteen days'] }}</option>
											<option value="20"{% if cur_task['day'] == '20' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Twenty days'] }}</option>
											<option value="21"{% if cur_task['day'] == '21' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Twenty one days'] }}</option>
											<option value="22"{% if cur_task['day'] == '22' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Twenty two days'] }}</option>
											<option value="23"{% if cur_task['day'] == '23' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Twenty three days'] }}</option>
											<option value="24"{% if cur_task['day'] == '24' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Twenty four days'] }}</option>
											<option value="25"{% if cur_task['day'] == '25' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Twenty five days'] }}</option>
											<option value="26"{% if cur_task['day'] == '26' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Twenty six days'] }}</option>
											<option value="27"{% if cur_task['day'] == '27' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Twenty seven days'] }}</option>
											<option value="28"{% if cur_task['day'] == '28' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Twenty eight days'] }}</option>
											<option value="29"{% if cur_task['day'] == '29' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Twenty nine days'] }}</option>
											<option value="30"{% if cur_task['day'] == '30' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Thirty days'] }}</option>
											<option value="31"{% if cur_task['day'] == '31' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Thirty one days'] }}</option>
										</select>
										<span>{{ lang_admin_tasks['Day help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_tasks['Month label'] }}</th>
									<td>
										<select name="month">
											<option value="*"{% if cur_task['month'] == '*' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Every month'] }}</option>
											<option value="1"{% if cur_task['month'] == '1' %} selected="selected"{% endif %}>{{ lang_admin_tasks['One months'] }}</option>
											<option value="2"{% if cur_task['month'] == '2' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Two months'] }}</option>
											<option value="3"{% if cur_task['month'] == '3' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Three months'] }}</option>
											<option value="4"{% if cur_task['month'] == '4' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Four months'] }}</option>
											<option value="5"{% if cur_task['month'] == '5' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Five months'] }}</option>
											<option value="6"{% if cur_task['month'] == '6' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Six months'] }}</option>
											<option value="7"{% if cur_task['month'] == '7' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Seven months'] }}</option>
											<option value="8"{% if cur_task['month'] == '8' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Eight months'] }}</option>
											<option value="9"{% if cur_task['month'] == '9' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Nine months'] }}</option>
											<option value="10"{% if cur_task['month'] == '10' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Ten months'] }}</option>
											<option value="11"{% if cur_task['month'] == '11' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Eleven months'] }}</option>
											<option value="12"{% if cur_task['month'] == '12' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Twelve months'] }}</option>
										</select>
										<span>{{ lang_admin_tasks['Month help'] }}</span>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ lang_admin_tasks['Week day label'] }}</th>
									<td>
										<select name="week_day">
											<option value="*"{% if cur_task['week_day'] == '*' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Every week day'] }}</option>
											<option value="0"{% if cur_task['week_day'] == '0' %} selected="selected"{% endif %}>{{ lang_admin_tasks['One week days'] }}</option>
											<option value="1"{% if cur_task['week_day'] == '1' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Two week days'] }}</option>
											<option value="2"{% if cur_task['week_day'] == '2' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Three week days'] }}</option>
											<option value="3"{% if cur_task['week_day'] == '3' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Four week days'] }}</option>
											<option value="4"{% if cur_task['week_day'] == '4' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Five week days'] }}</option>
											<option value="5"{% if cur_task['week_day'] == '5' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Six week days'] }}</option>
											<option value="6"{% if cur_task['week_day'] == '6' %} selected="selected"{% endif %}>{{ lang_admin_tasks['Seven week days'] }}</option>
										</select>
										<span>{{ lang_admin_tasks['Week day help'] }}</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend"><input type="submit" name="update" value="{{ lang_common['Submit'] }}" /> <a href="javascript:history.go(-1)">{{ lang_common['Go back'] }}</a></p>
			</form>
		</div>


	
</div>
</div><!-- .admin-console -->