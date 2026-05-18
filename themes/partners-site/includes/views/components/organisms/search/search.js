$(document).ready(function ($) {
    $('.result-entry').each(function (index, value) {
        let el_search = $(this).attr('data-check');
        let el_text = $(this).attr('data-search')
            .replace(el_search, '<b>' + el_search + '</b>')
            .replace(/[.?!:;-–](\s{2,})/g, function (match) {
                return match.trim() + " ";
            })
            .replace(/(\s{2,})[.?!:;-–]/g, function (match) {
                return " " + match.trim();
            });

        var result_text = searchAndParseText(el_text, el_search);
        $(this).find('p').append(result_text);
    })
})

function searchAndParseText(inputText, searchTerm) {
    var index = inputText.toLowerCase().indexOf(searchTerm.toLowerCase());
    globalLength = 160;
    if ($(window).width() < 997) {
        globalLength = 80;
    }
    if (index !== -1) {
        // searchTerm in content
        var dotIndex = inputText.lastIndexOf('.', index);
        var exclamationMarkIndex = inputText.lastIndexOf('!', index);
        var questionMarkIndex = inputText.lastIndexOf('?', index);
        var sentenceEndIndex = Math.max(dotIndex, exclamationMarkIndex, questionMarkIndex);
    
        if (sentenceEndIndex === -1) {
            var lastCapitalWordIndex = inputText.substring(0, index).search(/\b[A-Z][a-z]*\b(?![^<]*>)/);

            if (lastCapitalWordIndex === -1) {
                var startIndex = Math.min(0, index - 40);
            } else {
                var startIndex = lastCapitalWordIndex;
            }
        } else {
            var startIndex = sentenceEndIndex + 1;
        }
    } else {
        // searchTerm in title
        var startIndex = 0;
    }

    if (inputText.length < startIndex + 160) {
        var endIndex = inputText.length;
    } else {
        var dotIndex = inputText.lastIndexOf('.', startIndex + globalLength);
        var exclamationMarkIndex = inputText.lastIndexOf('!', startIndex + globalLength);
        var questionMarkIndex = inputText.lastIndexOf('?', startIndex + globalLength);
        var sentenceEndIndex = Math.max(dotIndex, exclamationMarkIndex, questionMarkIndex);

        if (sentenceEndIndex !== -1 && sentenceEndIndex > startIndex + globalLength) {
            var endIndex = sentenceEndIndex + 1;
        } else {
            var lastSpaceIndex = inputText.lastIndexOf(' ', startIndex + globalLength);
            var lastNewlineIndex = inputText.lastIndexOf('\n', startIndex + globalLength);
            var endIndex = Math.max(lastSpaceIndex, lastNewlineIndex);
        }
    }

    var parsedText = inputText.substring(startIndex, endIndex);

    if (endIndex !== sentenceEndIndex + 1 && endIndex !== inputText.length) {
        parsedText += "...";
    }

    var regex = new RegExp(searchTerm, "gi");

    return parsedText.replace(regex, function (match) {
        return "<b>" + match + "</b>";
    });
}