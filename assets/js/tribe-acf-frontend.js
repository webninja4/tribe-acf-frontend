(function($) {
    $(document).ready(function() {
        // Add the 'acf-form' class to the Community Events form.
        // The form is identified by its method and enctype, and the presence of a 'post_ID' hidden input.
        var $communityEventsForm = $('form[method="post"][enctype="multipart/form-data"][data-datepicker_format]');

        if ($communityEventsForm.length) {
            $communityEventsForm.addClass('acf-form');
            console.log('Tribe ACF Frontend: Added acf-form class to the Community Events form.');

            // Check if ACF input script is loaded
            if (typeof acf !== 'undefined' && typeof acf.do_action !== 'undefined') {
                console.log('Tribe ACF Frontend: ACF input script is loaded.');

                // Manually trigger ACF actions to prepare and submit fields
                acf.do_action('prepare_fields', $communityEventsForm);
                acf.do_action('submit', $communityEventsForm);

            } else {
                console.log('Tribe ACF Frontend: ACF input script is NOT loaded.');
            }
        }
    });
})(jQuery);