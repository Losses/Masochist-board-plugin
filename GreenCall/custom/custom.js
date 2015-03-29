/**
 * Created by Don on 3/12/2015.
 */
$(document).ready(function () {
    losses.greenCall = {};
    losses.greenCall.cache = {};

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
        '</div>' +
        '<style id="green_call_style"></style>'
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

    .delegate('.green_call', 'mouseenter', function (event) {
        var dialogElement = $('#green_call_dialog')
            , that = this
            , targetId = $(this).text().match(/>>([0-9]+)/)[1];

        if (parseInt(targetId.length) === 0)
            return;

        dialogElement.find('.author').text('');
        dialogElement.find('.content').text('');

        function fillContent(author, content) {
            setElementPosition();
            $('#green_call_dialog .loading_spin').hide();
            $('#green_call_dialog .call_content').show();

            dialogElement.find('.author').text(author);
            dialogElement.find('.content').text(content);
        }

        function setElementPosition() {
            var greenCallerPosition = $(that).offset()
                , greenCallLeft = greenCallerPosition.left + parseInt($(that).width()) - parseInt(dialogElement.width()) * 0.5 - 120
                , greenCallTop = greenCallerPosition.top + 25
                , styleElement = $('#green_call_style');

            if (greenCallLeft < 10) {
                var eventTargetPositionLeft = $(event.target).offset().left + parseInt($(event.target).width()) * 0.5;
                var cssContent = "#green_call_dialog::before," +
                    "             #green_call_dialog::after {" +
                    "                 left:" + eventTargetPositionLeft + "px;" +
                    "             }";

                styleElement.html(cssContent);
            } else {
                styleElement.html('');
            }

            dialogElement.css({
                'top': greenCallTop,
                'left': greenCallLeft < 10 ? 20 : greenCallLeft
            })
                .addClass('show');
        }

        var targetCache = losses.greenCall.cache['g' + targetId];
        if (targetCache) {
            dialogElement.addClass('show');

            fillContent(targetCache.author, targetCache.content);
            return;
        }

        dialogElement.show();
        $('#green_call_dialog .loading_spin').show();
        $('#green_call_dialog .call_content').hide();

        $.post('api/?plugin', {
            'api': 'lark.losses.green.call',
            'target_id': targetId
        }, function (data) {
            var response;
            try {
                response = JSON.parse(data);
            } catch (e) {

            }

            losses.greenCall.cache['g' + targetId] = JSON.parse(JSON.stringify(response));
            fillContent(response.author, response.content);

            setElementPosition();
        });

        setElementPosition();
    })

    .delegate('.green_call', 'mouseleave', function () {
        var dialogElement = $('#green_call_dialog');

        dialogElement.removeClass('show');
    });