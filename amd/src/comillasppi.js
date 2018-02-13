define(['jquery'], function($) {
    return {
        main: function(text, url) {
            /* Create popup window */
            $('body').append("<div class='comillasppi_popup comillasppi-mask'></div>");
            $('body').append("<div class='comillasppi_popup comillasppi-popupbox'><div>");
            $('.comillasppi-popupbox').append("<div class='comillasppi_popup comillasppi-header'>Aviso</div>");
            $('.comillasppi-header').append("<div class='comillasppi_popup comillasppi-close'>x</div>");
            $('.comillasppi-popupbox').append("<div class='comillasppi_popup comillasppi-text'></div>");
            $('.comillasppi-text').text(text);
            $('.comillasppi-popupbox').append("<div class='comillasppi_popup comillasppi-url'></div>");
            var text_link = "Consulta el protocolo para subir documentos ajenos a Moodlerooms";
            $('.comillasppi-url').append("<a href='" + url + "' target='_blank'>" + text_link + "</a>");
            $('.comillasppi-popupbox').append("<div class='comillasppi_popup comillasppi-accept'>Accept</div>");

            /* Fade in box */
            $('.comillasppi-popupbox').fadeIn();

            function close_popup() {
                $('.comillasppi-mask').hide();
                $('.comillasppi-popupbox').hide();
            }
            /* Close box */
            $('.comillasppi-close').click(function(){
                close_popup();
            });

            /* Accept */
            $('.comillasppi-accept').click(function(){
                close_popup();
            });
        }
    };
});