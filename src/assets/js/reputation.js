function remove_reputation(id, user, page, section)
{
    var r = confirm("Are you sure you wish to remove this reputation?");
    if (r == true)
        window.location.href = url[0] + 'include/reputation.php?action=remove&section=' + section + '&id=' + id + '&uid=' + user + '&p=' + page;
}

$(document).ready(function()
{
	$('.voterep').click(function()
	{
		var id = $(this).data('id');
		$.ajaxSetup({
			cache: false
		});

		$.get(url[0] + "include/reputation.php",
		{
				vote: $(this).data('vote'),
				id: id,
				csrf_token: $(this).data('token'),
			},
			function(data, status)
			{
				if (!$.isNumeric(data) || status != 'success')
					alert(data);
				else
				{
					switch (true)
					{
						case data > 0:
							type = 'positive';
							break;
						case data < 0:
							type = 'negative';
							break;
						default:
							type = 'zero';
							break;
					}

					$("#post_rep_" + id).text(data);
					$("#post_rep_" + id).attr('class', 'reputation ' + type);
				}
			});
	});
});