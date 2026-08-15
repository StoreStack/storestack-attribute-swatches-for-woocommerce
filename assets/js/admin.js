'use strict';

jQuery(document).ready(($) => {
    /**
     * Color Swatch Picker
     */
    $('.color-picker').wpColorPicker().attr('style', '');

    /**
     * Image Swatch Picker
     */
    let mediaUploader;

    $('.swatch-image-upload-button').click((e) => {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'Upload Image',
            button: {
                text: 'Upload Image',
            },
            multiple: false,
        });

        mediaUploader.on('select', () => {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#ssasfw_swatch_image').val(attachment.id);
            $('.swatch-image-preview').attr('src', attachment.url);
        });

        mediaUploader.open();
    });

    $('.swatch-image-remove-button').click((e) => {
        e.preventDefault();
        $('#ssasfw_swatch_image').val('');
        $('.swatch-image-preview').attr('src', ssasfwAdminParams.placeholderImg);
    });


    /**
     * Select2 for Groups
     */
    $('#ssasfw_groups').select2({
        tags: true,
        placeholder: ssasfwAdminParams.groupsPlaceholder,
        tokenSeparators: [',', ' '],
    });
});
