<div class="block brules">
	<h2 class="blocktitle">{{ lang_register['Forum rules'] }}</h2>
		<form method="get" action="{{ form_action }}">
			<div class="box">
				
					<p class="boxtitle">{{ lang_register['Rules legend'] }}</p>
					<div class="infldset">
						<div class="usercontent">{{ panther_config['o_rules_message']|raw }}</div>
					</div>
				
			</div>
			<div class="blockbuttons"><div class="conr"><input type="submit" name="cancel" value="{{ lang_register['Cancel'] }}" class="btn normal" /><input type="submit" name="agree" value="{{ lang_register['Agree'] }}" class="btn submit"/></div></div>
		</form>
</div>