(function($, window, document) {

    $(".champ__image-container").each(function(i, entry) {
        $(entry).append("<div class='champ__image-loader'> <div class='uil-default-css'> <div style='-webkit-transform:rotate(0deg) translate(0,-16px);transform:rotate(0deg) translate(0,-16px);border-radius:4px;position:absolute;'></div><div style='-webkit-transform:rotate(30deg) translate(0,-16px);transform:rotate(30deg) translate(0,-16px);'></div><div style='-webkit-transform:rotate(60deg) translate(0,-16px);transform:rotate(60deg) translate(0,-16px)'></div><div style='-webkit-transform:rotate(90deg) translate(0,-16px);transform:rotate(90deg) translate(0,-16px)'></div><div style='-webkit-transform:rotate(120deg) translate(0,-16px);transform:rotate(120deg) translate(0,-16px)'></div><div style='-webkit-transform:rotate(150deg) translate(0,-16px);transform:rotate(150deg) translate(0,-16px)'></div><div style='-webkit-transform:rotate(180deg) translate(0,-16px);transform:rotate(180deg) translate(0,-16px)'></div><div style='-webkit-transform:rotate(210deg) translate(0,-16px);transform:rotate(210deg) translate(0,-16px)'></div><div style='-webkit-transform:rotate(240deg) translate(0,-16px);transform:rotate(240deg) translate(0,-16px)'></div><div style='-webkit-transform:rotate(270deg) translate(0,-16px);transform:rotate(270deg) translate(0,-16px)'></div><div style='-webkit-transform:rotate(300deg) translate(0,-16px);transform:rotate(300deg) translate(0,-16px)'></div><div style='-webkit-transform:rotate(330deg) translate(0,-16px);transform:rotate(330deg) translate(0,-16px)'></div></div></div>");
    });

    $(document).foundation();

    $(".champ__image").load(function() {
        $(this).parent().find(".champ__image-loader").fadeOut(200, function() {
            $(this).remove();
        });
    });

    // Fire load event even if image is cached
    $(".champ__image").each(function(i, entry) {
        if ($(entry)[0].complete) {
            $(entry).load();
        }
    });

})(jQuery, window, document);
