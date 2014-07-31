function registerComments()
{
    splitCommentReplys();


    // make comment actions
    var form = $('.comment-form form');
    form.unbind().submit(function(e)
    {
        e.preventDefault();
        $(this).find('button[type="submit"]').trigger('click');
        return false;
    });

    form.find('button[type="submit"]').unbind().bind('click', function(e)
    {
		if (form.find('textarea.bbcodeCommentTextarea').syncComment()) {
            e.preventDefault();

			$('#comment-post-error').hide();
			var post = form.serialize();
			$('#ajax-feedback').animate(
					{
						'height': 'toggle',
						'opacity': 'toggle'
					}, 'slow');
			$.post(form.attr('action'), post, function(data)
			{
				if (responseIsOk(data))
				{
					$.fn.skinAddComment(data);
					$('#ajax-feedback').animate(
							{
								'height': 'toggle',
								'opacity': 'toggle'
							}, 'slow');
				}
				else
				{
					$('#ajax-feedback').animate(
							{
								'height': 'toggle',
								'opacity': 'toggle'
							}, 'slow');


					$('#comment-post-error').html(data.msg).slideToggle();
				}
			}, "json");

		}

        return false;
    });

    $('#content .comments-bubble').css({
        'z-index': 5
    });

    if ($('.comments-bubble').length > 0 && $('#commentFrom').length == 0)
    {
        $('.comments-bubble').remove();
    }

    $('a.scroll-to-comment').attr('href', 'javascript:void(0)').unbind().bind('click', function(e)
    {
        e.preventDefault();
        
        var top = $('.comment-form form').offset().top - 10;
        if ($('#comments-container').length == 1 ) {
            top = $('#comments-container').offset().top - 10;
        }
        
        $('html, body').animate({scrollTop: top }, {duration: 900, easing: 'swing'});


        return false;
    });

    $('.comment-form-toggle a').unbind().bind('click', function(e)
    {
        e.preventDefault();
        var el = $(this);
        el.addClass('input-link-activated');
        form.stop().slideToggle('slow', function()
        {
            if ($(this).is(':visible'))
            {
                el.addClass('input-link-activated');
            }
            else
            {
                el.removeClass('input-link-activated');
            }
        });

        return false;
    });
}

function splitCommentReplys()
{
    if ($('#comments-container').length == 0)
    {
        return;
    }
    var replyies = $('#comments-container').find('.replies');
    replyies.each(function()
    {
        var self = this;
        var totalReplys = $(this).find('.reply').length;
        var replys = $(this).find('.reply');
        if (totalReplys > 3)
        {
            var x = 0;
            replys.each(function()
            {
                if (x == 2)
                {
                    // <div id="expand-replies" class="reply"><div class="shadowed">
                    var splitExpand = $('<div>').addClass('expand-replies');
                    var splitShadow = $('<div>').addClass('shadowed');
                    var splitContent = $('<div>').addClass('inner-boundary');
                    var hidden_count = totalReplys - 3;
                    splitContent.append($('<strong>' + hidden_count + ' weitere ' + (hidden_count > 1 ? 'Antworten' : 'Antwort') + '</strong>'));
                    var link = $('<a>').addClass('hidden').append('show all');
                    splitContent.append(link);
                    link.click(function()
                    {
                        $(self).find('.closed-reply').slideToggle('slow', function()
                        {
                        });
                    });
                    splitContent.append($('<div class="post_clear"></div>'));
                    splitExpand.append(splitShadow.append(splitContent));
                    splitExpand.insertBefore(this);
                }
                x++;
                if (x > 2 && x < totalReplys)
                {
                    $(this).addClass('closed-reply').hide();
                }
            });
        }
    });
}
(function($)
{
    $.fn.skinAddComment = function(data)
    {
        var lastRepliesDiv = $('#comments-container').find('div.replies:last');
        var lastPostDiv = $('#comments-container').find('div.post:last');
        // Neuen Replies Container erzeugen
        if (!lastRepliesDiv.length && data.parentid > 0)
        {
            var repliesDiv = $('<div class="replies"></div>');
        }
        var title = null;
        if (data.title != '')
        {
            var title = $('<h3>').append(data.title);
        }
        var postContainer = $('<div>');
        if (data.parentid > 0)
        {
            postContainer.addClass('reply');
            postContainer.append($('<div>').addClass('ul-pointer'));
        }
        else
        {
            postContainer.addClass('post');
        }
        var postBoxShadowed = $('<div>').addClass('shadowed');
        var postBoxInnerBoundary = $('<div>').addClass('inner-boundary').addClass('text');
        var postHeader = $('<div>').addClass('post-header');
        var author = $('<div class="author"><small>Autor</small><div class="sash sprite"><!-- --></div></div>');
        var postername = $('<p><strong class="poster-name">' + data.username + '</strong> <small>sagt</small></p>');
        var s = $('a.author').text();
        if (data.username == s.trim())
        {
            postHeader.append(author);
        }
        postHeader.append(postername);
        postBoxInnerBoundary.append(postHeader);
        var clear = $('<div>').addClass('clear');
        var container = $('<div id="comment_content_' + data.newcomment_id + '" class="post-content">');
        if (data.title != '')
        {
            container.append($('<h3>').append(data.title))
        }
        container.append($('<p>').append(data.comment));
        var postTools = $('<div class="post-tools">');
        var datestr = phpdate('d.m.Y, H:i', data.timestamp);
        postTools.append($('<small>').append(datestr));
        postTools.append($('<a>').append(
                $('<img>').attr({
            'src': 'buttons/reply.gif',
            'width': '85',
            'height': '18',
            'title': 'Antworten'
        })).click(function()
        {
            replyComment(data.newcomment_id);
        }));


        container.append(postTools);
        postBoxInnerBoundary.append(container);
        postBoxShadowed.append(postBoxInnerBoundary);
        postContainer.append(postBoxShadowed);

        if (data.parentid > 0)
        {
            if ($('div.replies').find('#comment_content_' + data.parentid).length)
            {
                var repliesDiv = $('div.replies').find('#comment_content_' + data.parentid);
                repliesDiv.parent().parent().parent().append(postContainer);
            }
            else
            {
                var postReplyTo = $('div.post').find('#comment_content_' + data.parentid);
                var repliesDiv = $('<div class="replies"></div>');
                repliesDiv.append(postContainer);
                postReplyTo = postReplyTo.parent().parent().parent();
                repliesDiv.insertAfter(postReplyTo);
            }
        }
        else
        {
            $('#comments-container').append(postContainer);
        }

        var bubble = $('.comments-bubble a');
        var bubbletxt = $(bubble).text();
        var txt = $(bubble).find('small').text();
        $(bubble).empty();
        $(bubble).append(parseInt(bubbletxt) + 1);
        $(bubble).append($('<small>').append(txt));
        splitCommentReplys();
        //findSyntaxes();
    }
})(jQuery);
