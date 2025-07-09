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

        // Attach the submit handler
        $tribeForm.on('submit', handleFormSubmit);
        console.log('Tribe ACF Frontend INIT: Submit handler attached.');
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

    function handleFormSubmit(e) {
        e.preventDefault();
        console.log('Tribe ACF Frontend SUBMIT: Form submission intercepted.');

        const $form = $(this);
        const $acfWrapper = $(acfWrapperId);

        // Serialize the ACF fields data
        const acfData = $acfWrapper.find('input, select, textarea').serialize();
        const postId = $form.find('#post_ID').val();
        const nonce = $acfWrapper.find('input[name="_acf_nonce"]').val(); // Match ACF's actual nonce field name

        if (!nonce) {
            console.error('Tribe ACF Frontend SUBMIT: Security nonce missing. Aborting.');
            alert('Security verification failed. Please reload the page and try again.');
            return;
        }
        
        if (!acfData) {
            console.log('Tribe ACF Frontend SUBMIT: No ACF data found to submit. Submitting main form.');
            $form.off('submit').submit();
            return;
        }

        // Disable submit button to prevent duplicates
        $form.find('input[type="submit"]').prop('disabled', true);

        console.log('Tribe ACF Frontend SUBMIT: Post ID:', postId);
        console.log('Tribe ACF Frontend SUBMIT: Nonce:', nonce);
        console.log('Tribe ACF Frontend SUBMIT: Serialized ACF Data:', acfData);

        const data = 'action=save_acf_community_event' +
                     '&_acf_nonce=' + nonce +
                     '&post_id=' + postId +
                     '&' + acfData;

        $.ajax({
            url: tribe_acf_frontend_ajax.ajax_url, // Use localized ajax URL
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    console.log('Tribe ACF Frontend AJAX: Success -', response.data.message);
                    // Submit the main form
                    // Re-enable button and allow original form submission
                    $form.find('input[type="submit"]').prop('disabled', false);
                    
                    // Submit the form normally without AJAX
                    $form[0].submit();
                } else {
                    console.error('Tribe ACF Frontend AJAX: Error -', response.data.message);
                    alert('Error saving custom fields: ' + response.data.message);
                    // Re-enable submit button if it was disabled
                    $form.find('input[type="submit"]').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Tribe ACF Frontend AJAX: Fatal Error -', status, error);
                alert('A fatal error occurred while saving custom fields. Please try again.');
                // Re-enable submit button
                $form.find('input[type="submit"]').prop('disabled', false);
            }
        });
    }
});
