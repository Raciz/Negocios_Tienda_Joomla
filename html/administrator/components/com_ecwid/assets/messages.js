jQuery(document).ready(function() {
    jQuery('#ecwid-vote-hide').click(function() {
        jQuery.ajax('index.php?option=com_ecwid&task=hide_vote_message', {success: function($data) {
            if ($data == 'success') {
                var parent = jQuery('#ecwid-vote-message').closest('.alert');
                jQuery('#ecwid-vote-message').remove();
                if (jQuery('.alert-message', parent).length == 1) {
                    var container = parent.closest('#system-message-container');
                    parent.remove();
                    if (container.find('.alert').length == 0) {
                        container.remove();
                    }
                }

            }
        }});
    });
})