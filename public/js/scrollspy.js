$(function () {
    //Mouvement fluide et scrollspy
    $('.a_scrollspy').on('click', function (e) {
        e.preventDefault();

        if (this.hash !== "") {
            var hash = this.hash;
            $('html, body').animate({
                scrollTop: $(this.hash).offset().top - 56
            }, 1000);
        }
    });

    //Changement bouton
    $(document).on('scroll', function () {
        var top = document.documentElement.scrollTop + 56;

        var section = $('section');
        var position = section.position();

        if (top >= position.top) {
            $('.btn .a_scrollspy .material-icons').text('keyboard_arrow_up')
        } else {
            $('.btn .a_scrollspy .material-icons').text('keyboard_arrow_down')
        }
    });
});
