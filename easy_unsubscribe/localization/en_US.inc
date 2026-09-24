<?php

/**
 * Localization file for Roundcube Easy Unsubscribe Plugin
 * Language: English (US)
 */

$labels = [];

// Button & Badge Labels
$labels['unsubscribe'] = 'Unsubscribe';
$labels['unsubscribing'] = 'Unsubscribing...';
$labels['unsubscribed'] = 'Unsubscribed';
$labels['already_unsubscribed'] = 'You have already unsubscribed from this mailing list.';
$labels['tooltip_button'] = 'Unsubscribe from this mailing list';
$labels['tooltip_unsubscribed'] = 'You have unsubscribed from this mailing list.';

// Modal Dialog Strings
$labels['modal_title'] = 'Unsubscribe from %s?';
$labels['modal_title_generic'] = 'Unsubscribe from mailing list?';
$labels['modal_desc_oneclick'] = 'Are you sure you want to unsubscribe from <strong>%s</strong>? Roundcube will automatically send an opt-out request on your behalf.';
$labels['modal_desc_mailto'] = 'Are you sure you want to unsubscribe from <strong>%s</strong>? Roundcube will send an unsubscribe email on your behalf to <code>%s</code>.';
$labels['modal_desc_http'] = 'This mailing list requires you to unsubscribe through their website. Would you like to open their unsubscribe page in a new window?';
$labels['modal_website_notice'] = 'You will be redirected to an external site:';

// Modal Action Buttons
$labels['btn_unsubscribe'] = 'Unsubscribe';
$labels['btn_visit_website'] = 'Go to Website';
$labels['btn_cancel'] = 'Cancel';
$labels['btn_close'] = 'Close';

// Success Notifications
$labels['success_oneclick'] = 'Successfully unsubscribed from %s.';
$labels['success_mailto'] = 'Unsubscribe email sent to %s.';
$labels['success_generic'] = 'Unsubscribe request submitted successfully.';
$labels['notice_http_opened'] = 'External unsubscribe page opened in a new window.';

// Error Messages
$labels['error_failed'] = 'Could not process unsubscribe request. Please try visiting the website directly.';
$labels['error_no_header'] = 'No valid List-Unsubscribe header found for this message.';
$labels['error_invalid_params'] = 'Invalid message parameters provided.';
$labels['error_message_not_found'] = 'Could not load message details from mail server.';
$labels['error_csrf'] = 'Security verification failed or session expired. Please reload the page.';
$labels['error_network'] = 'Network error while attempting to unsubscribe. Please try again.';
$labels['error_ssrf'] = 'The target unsubscribe URL is invalid or restricted.';
$labels['error_mailto_failed'] = 'Failed to deliver automated unsubscribe email.';
$labels['error_http_post_failed'] = 'One-click unsubscribe server responded with error status (%s).';
