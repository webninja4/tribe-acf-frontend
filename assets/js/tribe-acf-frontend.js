(function($) {
    $(document).ready(function() {
        // Add the 'acf-form' class to the Community Events form.
        // The form is identified by its method and enctype, and the presence of a 'post_ID' hidden input.
        var $communityEventsForm = $('form[method="post"][enctype="multipart/form-data"][data-datepicker_format]');

        if ($communityEventsForm.length) {
            $communityEventsForm.addClass('acf-form');
            console.log('Tribe ACF Frontend: Added acf-form class to the Community Events form.');
        }
    });
})(jQuery);