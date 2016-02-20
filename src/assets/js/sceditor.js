$(document).ready(function()
{
	$("#submit").click(function(event)
	{
		$('.scedit_bbcode').sceditor('instance').updateOriginal();
	});

	$("#preview").click(function(event)
	{
		$('.scedit_bbcode').sceditor('instance').updateOriginal();
	});

	$.sceditor.plugins.bbcode.bbcode
		.set("list",
		{
			html: function(element, attrs, content)
			{
				var type = (attrs.defaultattr === '1' ? 'ol' : 'ul');

				return '<' + type + '>' + content + '</' + type + '>';
			},
			breakAfter: false
		})
		.set("ul",
		{
			format: function($elm, content)
			{
				return '[list]' + content + '[/list]';
			}
		})
		.set("ol",
		{
			format: function($elm, content)
			{
				return '[list=1]' + content + '[/list]';
			}
		})
		.set("li",
		{
			format: function($elm, content)
			{
				return '[*]' + content;
			}
		})
		.set("*",
		{
			excludeClosing: true,
			isInline: false
		});

	$.sceditor.command
		.set("bulletlist",
		{
			txtExec: ["[list]\n[*]", "\n[/list]"]
		})
		.set("orderedlist",
		{
			txtExec: ["[list=1]\n[*]", "\n[/list]"]
		});

	$(".scedit_bbcode").sceditor(
	{
		plugins: "bbcode",
		style: url[2] + "sceditor.css",
		toolbar: "bold,italic,underline,strike|bulletlist,orderedlist,color|cut,copy,paste,pastetext|removeformat|emoticon|link,email,image,quote|code|source",
		emoticonsEnabled: true,
		emoticons:
		{
			dropdown:
			{
				':)': url[1] + 'smile.png',
				':(': url[1] + 'sad.png',
				':D': url[1] + 'big_smile.png',
				':o': url[1] + 'yikes.png',
				':p': url[1] + 'tongue.png',
				':/': url[1] + 'hmm.png',
				':|': url[1] + 'neutral.png',
				':cool:': url[1] + 'cool.png',
				':lol:': url[1] + 'lol.png',
				':mad:': url[1] + 'mad.png',
				':rolleyes:': url[1] + 'roll.png',
				';)': url[1] + 'wink.png'
			},
			hidden:
			{
				'=)': url[1] + 'smile.png',
				'=(': url[1] + 'sad.png',
				'=D': url[1] + 'big_smile.png',
				':O': url[1] + 'yikes.png',
				':P': url[1] + 'tongue.png',
				'=|': url[1] + 'neutral.png',
			}
		},
	});
});