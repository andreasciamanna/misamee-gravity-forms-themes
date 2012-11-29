// CLEAR FORM FIELDS OF LABEL ON FOCUS THEN ADD BACK ON BLUR IF EMPTY (class of 'clear_field' must be added to form field) //
(function () {
    // Your base, I'm in it!
    var originalAddClassMethod = jQuery.fn.addClass;

    jQuery.fn.addClass = function () {
        // Execute the original method.
        var result = originalAddClassMethod.apply(this, arguments);

        // call your function
        // this gets called everytime you use the addClass method
        jQuery(this).trigger('cssClassChanged');

        // return the original result
        return result;
    }
})();

jQuery(document).ready(function ($) {
    var $templateMainElement = $('.themed_form');
    if (!("placeholder" in document.createElement("input"))) {
        $("input[placeholder], textarea[placeholder]", $templateMainElement).each(function () {
            var val = $(this).attr("placeholder");
            if (this.value == "") {
                this.value = val;
            }
            $(this).focus(function () {
                if (this.value == val) {
                    this.value = "";
                }
            }).blur(function () {
                    if ($.trim(this.value) == "") {
                        this.value = val;
                    }
                })
        });

        // Clear default placeholder values on form submit
        $('form', $templateMainElement).submit(function () {
            $(this).find("input[placeholder], textarea[placeholder]").each(function () {
                if (this.value == $(this).attr("placeholder")) {
                    this.value = "";
                }
            });
        });
    }

    $($templateMainElement).removeClass('gfield_error');
    $('.gfield', $templateMainElement).bind('cssClassChanged', function () {
        $(this).removeClass('gfield_error');
    });

    $('.gfield input', $templateMainElement).each(function () {
        if ($(this).attr('id') != '') {
            if ($(this).attr('type') != 'checkbox' && $(this).attr('type') != 'radio') {
                var $label = $('label[for="' + $(this).attr('id') + '"]');
                $label.hide();
                $(this).addClass('misamee_themed_form_input');
                $(this).attr('placeholder', $label.text());
            }
        }

        var $descElement = $(this).parent('.ginput_container').siblings('.gfield_description:not(".validation_message")');
        var $toolTip = $descElement.text();
        if ($toolTip != '') {
            $descElement.hide();
            $(this).tooltipsy({
                content: $toolTip,
                alignTo: 'element',
                offset: [-10, 10],
                show: function (e, $el) {
                    $el.css({
                        'left': parseInt($el[0].style.left.replace(/[a-z]/g, '')) - 50 + 'px',
                        'opacity': '0.0',
                        'display': 'block'
                    }).animate({
                            'left': parseInt($el[0].style.left.replace(/[a-z]/g, '')) + 50 + 'px',
                            'opacity': '1.0'
                        }, 300);
                },
                hide: function (e, $el) {
                    $el.slideUp(100);
                }
            });
        }

        var $validationElement = $(this).parent('.ginput_container').siblings('.validation_message');
        var $validationToolTip = $validationElement.text();
        if ($validationToolTip != '') {
            $validationElement.hide();
            //$(this).data('tooltipsy').destroy();
            $(this).tooltipsy({
                content: $validationToolTip,
                alignTo: 'element',
                offset: [-10, 10],
                className: 'tooltipsy-error'
            });
            $(this).trigger('focus');
            $(this).trigger('mouseenter');
        } else {

        }
    });
});