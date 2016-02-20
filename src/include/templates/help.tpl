<div class="block bhelp">

<h2 class="blocktitle">{{ lang_help['BBCode'] }}</h2>
<div class="box">
	<div class="inbox">
		<p class="info">{{ lang_help['BBCode info 1'] }}</p>
		<p>{{ lang_help['BBCode info 2'] }}</p>
	</div>
</div>


<div class="box">
<p class="boxtitle">{{ lang_help['Text style'] }}</p>
	<div class="inbox">
		<p class="info">{{ lang_help['Text style info'] }}</p>
		<p><code>[b]{{ lang_help['Bold text'] }}[/b]</code> {{ lang_help['produces'] }} <samp><strong>{{ lang_help['Bold text'] }}</strong></samp></p>
		<p><code>[u]{{ lang_help['Underlined text'] }}[/u]</code> {{ lang_help['produces'] }} <samp><span class="bbu">{{ lang_help['Underlined text'] }}</span></samp></p>
		<p><code>[i]{{ lang_help['Italic text'] }}[/i]</code> {{ lang_help['produces'] }} <samp><em>{{ lang_help['Italic text'] }}</em></samp></p>
		<p><code>[s]{{ lang_help['Strike-through text'] }}[/s]</code> {{ lang_help['produces'] }} <samp><span class="bbs">{{ lang_help['Strike-through text'] }}</span></samp></p>
		<p><code>[del]{{ lang_help['Deleted text'] }}[/del]</code> {{ lang_help['produces'] }} <samp><del>{{ lang_help['Deleted text'] }}</del></samp></p>
		<p><code>[ins]{{ lang_help['Inserted text'] }}[/ins]</code> {{ lang_help['produces'] }} <samp><ins>{{ lang_help['Inserted text'] }}</ins></samp></p>
		<p><code>[em]{{ lang_help['Emphasised text'] }}[/em]</code> {{ lang_help['produces'] }} <samp><em>{{ lang_help['Emphasised text'] }}</em></samp></p>
		<p><code>[color=#FF0000]{{ lang_help['Red text'] }}[/color]</code> {{ lang_help['produces'] }} <samp><span style="color: #ff0000">{{ lang_help['Red text'] }}</span></samp></p>
		<p><code>[color=blue]{{ lang_help['Blue text'] }}[/color]</code> {{ lang_help['produces'] }} <samp><span style="color: blue">{{ lang_help['Blue text'] }}</span></samp></p>
		<p><code>[h]{{ lang_help['Heading text'] }}[/h]</code> {{ lang_help['produces'] }}</p> <div class="postmsg"><h5>{{ lang_help['Heading text'] }}</h5></div>
	</div>
</div>


<div class="box">
<p class="boxtitle">{{ lang_help['Links and images'] }}</p>
	<div class="inbox">
		<p class="info">{{ lang_help['Links info'] }}</p>
		<p><a name="url"></a><code>[url={{ base_url }}]{{ panther_config['o_board_title'] }}[/url]</code> {{ lang_help['produces'] }} <samp><a href="{{ base_url }}">{{ panther_config['o_board_title'] }}</a></samp></p>
		<p><code>[url]{{ base_url }}[/url]</code> {{ lang_help['produces'] }} <samp><a href="{{ base_url }}">{{ base_url }}</a></samp></p>
		<p><code>[url={{ help_page }}]{{ lang_help['This help page'] }}[/url]</code> {{ lang_help['produces'] }} <samp><a href="{{ help_page }}">{{ lang_help['This help page'] }}</a></samp></p>
		<p><code>[email]myname@example.com[/email]</code> {{ lang_help['produces'] }} <samp><a href="mailto:myname@example.com">myname@example.com</a></samp></p>
		<p><code>[email=myname@example.com]{{ lang_help['My email address'] }}[/email]</code> {{ lang_help['produces'] }} <samp><a href="mailto:myname@example.com">{{ lang_help['My email address'] }}</a></samp></p>
		<p><code>[topic={{ topic_id }}]{{ lang_help['Test topic'] }}[/topic]</code> {{ lang_help['produces'] }} <samp><a href="{{ topic_link }}">{{ lang_help['Test topic'] }}</a></samp></p>
		<p><code>[topic]{{ topic_id }}[/topic]</code> {{ lang_help['produces'] }} <samp><a href="{{ topic_link }}">{{ topic_link }}</a></samp></p>
		<p><code>[post={{ post_id }}]{{ lang_help['Test post'] }}[/post]</code> {{ lang_help['produces'] }} <samp><a href="{{ post_link }}">{{ lang_help['Test post'] }}</a></samp></p>
		<p><code>[post]{{ post_id }}[/post]</code> {{ lang_help['produces'] }} <samp><a href="{{ post_link }}">{{ post_link }}</a></samp></p>
		<p><code>[forum={{ forum_id }}]{{ lang_help['Test forum'] }}[/forum]</code> {{ lang_help['produces'] }} <samp><a href="{{ forum_link }}">{{ lang_help['Test forum'] }}</a></samp></p>
		<p><code>[forum]{{ forum_id }}[/forum]</code> {{ lang_help['produces'] }} <samp><a href="{{ forum_link }}">{{ forum_link }}</a></samp></p>
		<p><code>[user]{{ username }}[/user]</code> {{ lang_help['produces'] }} <samp>{{ formatted_username|raw }}</samp></p>
	</div>
	<div class="inbox">
		<p><a name="img"></a>{{ lang_help['Images info'] }}</p>
		<p><code>[img={{ lang_help['Panther bbcode test'] }}]{{ panther_config['o_image_dir'] }}test.png[/img]</code> {{ lang_help['produces'] }} <samp><img style="height: 35px" src="{{ panther_config['o_image_dir'] }}test.png" alt="{{ lang_help['Panther bbcode test'] }}" /></samp></p>
	</div>
</div>


<div class="box">
<p class="boxtitle">{{ lang_help['Quotes'] }}</p>
	<div class="inbox">
		<p>{{ lang_help['Quotes info'] }}</p>
		<p><code>[quote=James]{{ lang_help['Quote text'] }}[/quote]</code></p>
		<p>{{ lang_help['produces quote box'] }}</p>
		<div class="postmsg">
			<div class="quotebox"><cite>James {{ lang_common['wrote'] }}</cite><blockquote><div><p>{{ lang_help['Quote text'] }}</p></div></blockquote></div>
		</div>
		<p>{{ lang_help['Quotes info 2'] }}</p>
		<p><code>[quote]{{ lang_help['Quote text'] }}[/quote]</code></p>
		<p>{{ lang_help['produces quote box'] }}</p>
		<div class="postmsg">
			<div class="quotebox"><blockquote><div><p>{{ lang_help['Quote text'] }}</p></div></blockquote></div>
		</div>
		<p>{{ lang_help['quote note'] }}</p>
	</div>
</div>



<div class="box">
<p class="boxtitle">{{ lang_help['Code'] }}</p>
	<div class="inbox">
		<p class="info">{{ lang_help['Code info'] }}></p>
		<p><code>[code]{{ lang_help['Code text'] }}[/code]</code></p>
		<p>{{ lang_help['produces code box'] }}></p>
		<div class="postmsg">
			<div class="codebox"><pre><code>{{ lang_help['Code text'] }}</code></pre></div>
		</div>
	</div>
</div>


<div class="box">
<p class="boxtitle">{{ lang_help['Lists'] }}</p>
	<div class="inbox">
		<p class="info">{{ lang_help['List info'] }}</p>
		<p><code>[list][*]{{ lang_help['List text 1'] }}[/*][*]{{ lang_help['List text 2'] }}[/*][*]{{ lang_help['List text 3'] }}[/*][/list]</code>
		<span>{{ lang_help['produces list'] }}</span></p>
		<div class="postmsg">
			<ul><li><p>{{ lang_help['List text 1'] }}</p></li><li><p>{{ lang_help['List text 2'] }}</p></li><li><p>{{ lang_help['List text 3'] }}</p></li></ul>
		</div>
		<p><code>[list=1][*]{{ lang_help['List text 1'] }}[/*][*]{{ lang_help['List text 2'] }}[/*][*]{{ lang_help['List text 3'] }}[/*][/list]</code>
		<span>{{ lang_help['produces decimal list'] }}</span></p>
		<div class="postmsg">
			<ol class="decimal"><li><p>{{ lang_help['List text 1'] }}</p></li><li><p>{{ lang_help['List text 2'] }}</p></li><li><p>{{ lang_help['List text 3'] }}</p></li></ol>
		</div>
		<p><code>[list=a][*]{{ lang_help['List text 1'] }}[/*][*]{{ lang_help['List text 2'] }}[/*][*]{{ lang_help['List text 3'] }}[/*][/list]</code>
		<span>{{ lang_help['produces alpha list'] }}</span></p>
		<div class="postmsg">
			<ol class="alpha"><li><p>{{ lang_help['List text 1'] }}</p></li><li><p>{{ lang_help['List text 2'] }}</p></li><li><p>{{ lang_help['List text 3'] }}</p></li></ol>
		</div>
	</div>
</div>



<div class="box">
<p class="boxtitle">{{ lang_help['Nested tags'] }}</p>
	<div class="inbox">
		<p class="info">{{ lang_help['Nested tags info'] }}</p>
		<p><code>[b][u]{{ lang_help['Bold, underlined text'] }}[/u][/b]</code> {{ lang_help['produces'] }} <samp><strong><span class="bbu">{{ lang_help['Bold, underlined text'] }}</span></strong></samp></p>
	</div>
</div>

<div class="box">
<p class="boxtitle">{{ lang_help['Smilies'] }}</p>
	<div class="inbox">
		<p><a name="smilies"></a>{{ lang_help['Smilies info'] }}</p>
{% for image,groups in smiley_groups %}
<p>{% for smiley in groups %}<code>{{ smiley }}</code>{% if not loop.last %} {{ lang_common['and'] }} {% endif %}{% endfor %} <span>{{ lang_help['produces'] }}</span> <samp><img src="{{ smiley_path }}{{ image }}" width="15" height="15" alt="{{ smiley_groups[image][0] }}" /></samp></p>
{% endfor %}
	</div>
</div>

</div>