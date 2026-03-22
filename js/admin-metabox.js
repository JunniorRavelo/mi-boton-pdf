/* global mbpdfAdmin */
(function ($) {
    'use strict';

    $(function () {
        var $field = $('#mbpdf_link_field');
        var $btn = $('#mbpdf_select_pdf');
        if (!$field.length || !$btn.length) {
            return;
        }

        var frame;

        $btn.on('click', function (e) {
            e.preventDefault();

            if (frame) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: mbpdfAdmin.frameTitle,
                button: { text: mbpdfAdmin.frameButton },
                library: {
                    type: 'application/pdf',
                },
                multiple: false,
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                if (attachment && attachment.url) {
                    $field.val(attachment.url).trigger('change');
                }
            });

            frame.open();
        });
    });
})(jQuery);
