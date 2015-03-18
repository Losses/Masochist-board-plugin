/**
 * Created by Don on 3/12/2015.
 */
$(document).ready(function () {
    var dialogElement = $('<div>')
        .attr({
            'id': 'green_call_dialog',
            'class': 'card'
        })
        .html(
        '<div class="loading loading_spin">' +
        '    <svg class="circular">' +
        '        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="5" stroke-miterlimit="10"></circle>' +
        '    </svg>' +
        '</div>' +
        '<div class="call_content">' +
        '    <span class="author"></span>' +
        '    <span class="content"></span>' +
        '</div>'
    );

    $('#common').append(dialogElement);
});

$('body')
    .delegate('#main .post_list li', 'mouseenter', function () {
        if ($('body').hasClass('post_page')) {
            var postId = $(this).attr('id').match(/post-([1-9]+)/)[1]
                , idElement = $('<span>')
                    .text('#' + postId)
                    .attr({
                        'id': 'post_id',
                        'data-post_id': postId
                    });

            $(this)
                .children('.content_group')
                .children('.article_group')
                .children('.author')
                .after(idElement);

            $('#post_id').fadeIn(300);
        }
    })

    .delegate('#main .post_list li', 'mouseleave', function () {
        $('#post_id').fadeOut(300)
            .remove();
    })

    .delegate('#post_id', 'click', function () {
        var post_id = $(this).attr('data-post_id');

        losses.elements.contentElement[0].value = '>>' + post_id + '\n' + losses.elements.contentElement[0].value;
        $('#new_post>i').click();
    })

    .delegate('.green_call', 'mouseenter', function () {
        /*need to get message from server*/

        var dialogElement = $('#green_call_dialog')
            , greenCallerPosition = $(this).offset()
            , greenCallLeft = greenCallerPosition.left + parseInt($(this).width()) - parseInt(dialogElement.width()) * 0.5 - 20
            , greenCallTop = greenCallerPosition.top + 25;

        dialogElement.css({
            'top': greenCallTop,
            'left': greenCallLeft
        })
            .addClass('show');

        $.post('api/?plugin', {
            'api': 'lark.losses.green.call',
            'target_id': $(this).text().match(/>>([1-9]+)/)[1]
        }, function (data) {
            var response;
            try {
                response = JSON.parse(data);
            } catch (e) {

            }

            dialogElement.find('.author').text(response.author);
            dialogElement.find('.content').text(response.content);
        });
    })

    .delegate('.green_call', 'mouseleave', function () {
        var dialogElement = $('#green_call_dialog');

        dialogElement.removeClass('show');
    });