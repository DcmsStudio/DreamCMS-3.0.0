// ----------------------------------------------------------------------------
// markItUp!
// ----------------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
// BBCode tags example
// http://en.wikipedia.org/wiki/Bbcode
// ----------------------------------------------------------------------------
// Feel free to add more tags
// ----------------------------------------------------------------------------
var commentBBCodeToolbar = {
    nameSpace: "bbcode",
    root: systemUrl + '/',
    previewTemplatePath: '~/html/js/jquery/bbcode/templates/preview.html',
    previewParserPath: systemUrl + '/' + 'bbcode/preview/commentbbcodes', // path to your BBCode parser
    markupSet: [
        {
            name: 'Bold',
            key: 'B',
            openWith: '[b]',
            closeWith: '[/b]',
            className: 'bold'
        },
        {
            name: 'Italic',
            key: 'I',
            openWith: '[i]',
            closeWith: '[/i]',
            className: 'italic'
        },
        {
            name: 'Underline',
            key: 'U',
            openWith: '[u]',
            closeWith: '[/u]',
            className: 'underline'
        },
        {
            separator: '---------------'
        },
        {
            name: 'Picture',
            key: 'P',
            replaceWith: '[img][![Url]!][/img]',
            className: 'img'
        },
        {
            name: 'Link',
            key: 'L',
            openWith: '[url=[![Url]!]]',
            closeWith: '[/url]',
            className: 'url',
            placeHolder: 'Your text to link here...'
        },
        {
            separator: '---------------'
        },
        {
            name: 'Size',
            key: 'S',
            openWith: '[size=[![Text size]!]]',
            closeWith: '[/size]',
            className: 'size',
            dropMenu: [
                {
                    name: 'Smaller',
                    openWith: '[size=8]',
                    closeWith: '[/size]',
                    className: 'size8'
                },
                {
                    name: 'Small',
                    openWith: '[size=10]',
                    closeWith: '[/size]',
                    className: 'size10'
                },
                {
                    name: 'Normal',
                    openWith: '[size=12]',
                    closeWith: '[/size]',
                    className: 'size12'
                },
                {
                    name: 'Big',
                    openWith: '[size=18]',
                    closeWith: '[/size]',
                    className: 'size18'
                },
                {
                    name: 'Bigest',
                    openWith: '[size=24]',
                    closeWith: '[/size]',
                    className: 'size24'
                }
            ]
        },
        {
            name: 'Smilies',
            className: "smilies",
            replaceWith: function(markitup) {
                showSmilies(markitup)
            }
        },
        {
            separator: '---------------'
        },
        {
            name: 'Bulleted list',
            openWith: '[list]\n',
            closeWith: '\n[/list]',
            className: 'list'
        },
        {
            name: 'Numeric list',
            openWith: '[list=[![Starting number]!]]\n',
            closeWith: '\n[/list]',
            className: 'numlist'
        },
        {
            name: 'List item',
            openWith: '[*] ',
            className: 'listitem'
        },
        {
            separator: '---------------'
        },
        {
            name: 'Quotes',
            openWith: '[quote]',
            closeWith: '[/quote]',
            className: 'quote'
        },
        {
            name: 'Code',
            openWith: '[code]',
            closeWith: '[/code]',
            className: 'code'
        },
        {
            name: 'PHP Code',
            openWith: '[php]',
            closeWith: '[/php]',
            className: 'php'
        },
        {
            separator: '---------------'
        },
        {
            name: 'Clean',
            className: "clean",
            replaceWith: function(markitup) {
                return markitup.selection.replace(/\[(.*?)\]/g, "")
            }
        },
        {
            name: 'Preview',
            className: "preview",
            call: 'preview'
        }
    ]
}

var bbcodeBioTextarea = {
    nameSpace: "bbcodebio",
    root: systemUrl + '/',
    previewTemplatePath: '~/html/js/jquery/bbcode/templates/preview.html',
    previewParserPath: systemUrl + '/bbcode/preview/biobbcodes', // path to your BBCode parser
    markupSet: [
        {
            name: 'Bold',
            key: 'B',
            openWith: '[b]',
            closeWith: '[/b]',
            className: 'bold'
        },
        {
            name: 'Italic',
            key: 'I',
            openWith: '[i]',
            closeWith: '[/i]',
            className: 'italic'
        },
        {
            name: 'Underline',
            key: 'U',
            openWith: '[u]',
            closeWith: '[/u]',
            className: 'underline'
        },
        {
            separator: '---------------'
        },
        {
            name: 'Size',
            key: 'S',
            openWith: '[size=[![Text size]!]]',
            closeWith: '[/size]',
            className: 'size',
            dropMenu: [
                {
                    name: 'Smaller',
                    openWith: '[size=8]',
                    closeWith: '[/size]',
                    className: 'size8'
                },
                {
                    name: 'Small',
                    openWith: '[size=10]',
                    closeWith: '[/size]',
                    className: 'size10'
                },
                {
                    name: 'Normal',
                    openWith: '[size=12]',
                    closeWith: '[/size]',
                    className: 'size12'
                },
                {
                    name: 'Big',
                    openWith: '[size=18]',
                    closeWith: '[/size]',
                    className: 'size18'
                },
                {
                    name: 'Bigest',
                    openWith: '[size=24]',
                    closeWith: '[/size]',
                    className: 'size24'
                }
            ]
        },
        {
            separator: '---------------'
        },
        {
            name: 'Bulleted list',
            openWith: '[list]\n',
            closeWith: '\n[/list]',
            className: 'list'
        },
        {
            name: 'Numeric list',
            openWith: '[list=[![Starting number]!]]\n',
            closeWith: '\n[/list]',
            className: 'numlist'
        },
        {
            name: 'List item',
            openWith: '[*] ',
            className: 'listitem'
        },
        {
            separator: '---------------'
        },
        {
            name: 'Clean',
            className: "clean",
            replaceWith: function(markitup) {
                return markitup.selection.replace(/\[(.*?)\]/g, "")
            }
        },
        {
            name: 'Preview',
            className: "preview",
            call: 'preview'
        }
    ]
}

$(document).ready(function() {
    $('head').append('<link rel="stylesheet" type="text/css" href="' + systemUrl + '/html/js/jquery/bbcode/sets/bbcode/style.css" />');
});


function showSmilies(markitup) {

    if (!$('.smiliebit').length)
    {

        $('#hiddensmilielist').remove();

        $.post('index.php', {cp: 'bbcode'}, function(data) {

            if (responseIsOk(data))
            {
                var smilies = $(data.smilielist);
                smilies.insertAfter($('.markItUpHeader'));
                $('.markItUpHeader').addClass('clear-after');
                $('#smilielist a').click(function(event) {
                    event.preventDefault();
                    emoticon = ' ' + $(this).attr("rel") + ' ';
                    $.markItUp({
                        replaceWith: emoticon
                    });
                });

                $('#hiddensmilielist').slideToggle();
            }
            else
            {
                alert(data.msg);
            }
        }, 'json');
    }
    else
    {
        $('#hiddensmilielist').slideToggle();
    }
}