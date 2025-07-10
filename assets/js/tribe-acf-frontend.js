jQuery(document).ready(function($) {
    console.log('Tribe ACF Frontend INIT: Script loaded.');

    const acfWrapperId = '#tribe-acf-fields-wrapper';
    const tribeFormSelector = '.tribe-community-events form'; // Using a class-based selector

    const $acfWrapper = $(acfWrapperId);

    if (!$acfWrapper.length) {
        console.error('Tribe ACF Frontend INIT: ACF wrapper not found on page load. Aborting.');
        return;
    }

    // Function to perform the integration
    function integrateAcfFields(targetForm) {
        const $tribeForm = $(targetForm);
        console.log('Tribe ACF Frontend INIT: Tribe form found. Integrating ACF fields.');

        // Move ACF fields into the Tribe form
        $tribeForm.find('.tribe-events-community-footer').before($acfWrapper);
        $acfWrapper.show(); // Make the fields visible
        console.log('Tribe ACF Frontend INIT: ACF fields moved and shown.');

        // Re-initialize ACF JavaScript
        if (typeof acf !== 'undefined') {
            acf.do_action('append', $acfWrapper);
            console.log('Tribe ACF Frontend INIT: ACF re-initialized.');
        }

        // No longer intercepting form submission here.
        // ACF fields will be submitted with the main form.
        console.log('Tribe ACF Frontend INIT: ACF fields will submit with main form.');
    }

    // Use MutationObserver to wait for the form to appear
    const observer = new MutationObserver(function(mutations, me) {
        const $form = $(tribeFormSelector);
        if ($form.length) {
            integrateAcfFields($form[0]);
            me.disconnect(); // Stop observing once the form is found
        }
    });

    // Start observing the body for changes
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    console.log('Tribe ACF Frontend INIT: MutationObserver is now watching for the form.');
});