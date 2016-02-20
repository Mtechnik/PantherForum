$(document).ready(function()
{
	$("#dashboard_notes").click(function()
	{
		$("#notes_save").slideDown();
	});

	$("#notes_save").click(function()
	{
		var url = $("#notes_url").val();
		var notes = $("textarea#dashboard_notes").val();
		$.post(url,
		{
			notes: notes,
		},
		function(data, status)
		{
			if (data != '')
				alert(data);
			else
				$("#notes_save").slideUp();
		});
	});
});