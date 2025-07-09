jQuery(document).ready(function($) {
    console.log('Tribe ACF Frontend INIT: Script loaded.');

    const acfWrapperId = '#tribe-acf-fields-wrapper';
    const tribeFormSelector = 'form[name="tribe-community-event"]';
    let attempts = 0;
    const maxAttempts = 50; // ~10 seconds

    function initializeAcfIntegration() {
        attempts++;
        const $acfWrapper = $(acfWrapperId);
        const $tribeForm = $(tribeFormSelector);

        console.log(`Tribe ACF Frontend INIT: Attempt ${attempts}. Wrapper found: ${$acfWrapper.length > 0}. Form found: ${$tribeForm.length > 0}.`);

        if ($acfWrapper.length && $tribeForm.length) {
            console.log('Tribe ACF Frontend INIT: Both ACF wrapper and Tribe form found.');
            
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

        } else if (attempts < maxAttempts) {
            setTimeout(initializeAcfIntegration, 200); // Check again shortly
        } else {
            console.error('Tribe ACF Frontend INIT: Timed out waiting for ACF wrapper or Tribe form.');
        }
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        console.log('Tribe ACF Frontend SUBMIT: Form submission intercepted.');

        const $form = $(this);
        const $acfWrapper = $(acfWrapperId);

        // Serialize the ACF fields data
        const acfData = $acfWrapper.find('input, select, textarea').serialize();
        const postId = $form.find('#post_ID').val();
        const nonce = $acfWrapper.find('#_wpnonce').val(); // Get nonce from the hidden field

        if (!acfData) {
            console.log('Tribe ACF Frontend SUBMIT: No ACF data found to submit. Submitting main form.');
            $form.off('submit').submit();
            return;
        }

        console.log('Tribe ACF Frontend SUBMIT: Post ID:', postId);
        console.log('Tribe ACF Frontend SUBMIT: Serialized ACF Data:', acfData);

        const data = 'action=save_acf_community_event' +
                     '&_wpnonce=' + nonce +
                     '&post_id=' + postId +
                     '&' + acfData;

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    console.log('Tribe ACF Frontend AJAX: Success -', response.data.message);
                    // Submit the main form
                    $form.off('submit').submit();
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

    // Start the process
    initializeAcfIntegration();
});
