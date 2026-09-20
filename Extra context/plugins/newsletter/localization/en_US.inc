<?php

/**
 * Localization strings for newsletter plugin (en_US)
 */

$labels = [];

$labels['newsletter'] = 'Newsletter';
$labels['newsletters'] = 'Newsletters';
$labels['campaign_composer'] = 'Newsletter Campaign Composer';
$labels['new_campaign'] = 'New Campaign';
$labels['campaigns'] = 'Campaigns';
$labels['suppressions'] = 'Suppression List';
$labels['deliverability_guide'] = 'Deliverability & Anti-Spam Guide';

// Step 1: Recipients
$labels['step_recipients'] = '1. Recipients';
$labels['select_recipients'] = 'Select Recipients';
$labels['all_contacts'] = 'All Contacts in Address Book';
$labels['contact_groups'] = 'Address Book Groups';
$labels['custom_addresses'] = 'Custom Email List (Paste)';
$labels['custom_addresses_placeholder'] = "Paste email addresses here (one per line or comma-separated):\nJohn Doe <john@example.com>\njane@example.com";
$labels['recipient_count'] = 'Total Recipients';
$labels['suppressed_excluded'] = 'Suppressed Excluded';
$labels['refresh_recipients'] = 'Refresh & Validate Recipients';

// Step 2: Content
$labels['step_content'] = '2. Compose & Design';
$labels['sender_identity'] = 'Sender Identity';
$labels['subject'] = 'Subject Line';
$labels['subject_placeholder'] = 'Clear, engaging subject (avoid ALL CAPS or exclamation spam)';
$labels['templates'] = 'Email Templates';
$labels['template_blank'] = 'Blank Canvas';
$labels['template_modern'] = 'Modern Newsletter';
$labels['template_announcement'] = 'Product Announcement';
$labels['template_digest'] = 'Weekly Digest';
$labels['insert_tokens'] = 'Insert Token:';
$labels['token_name'] = 'Full Name';
$labels['token_first_name'] = 'First Name';
$labels['token_email'] = 'Email';
$labels['token_unsubscribe'] = 'Unsubscribe URL';
$labels['token_date'] = 'Today\'s Date';
$labels['preview'] = 'Preview';
$labels['preview_sample_recipient'] = 'Preview with Sample Recipient';

// Step 3: Anti-Spam Check
$labels['step_spam_check'] = '3. Anti-Spam & Deliverability Audit';
$labels['run_spam_check'] = 'Run Anti-Spam Check';
$labels['spam_risk_score'] = 'Spam Risk Score';
$labels['risk_low'] = 'Low Risk (Excellent Deliverability)';
$labels['risk_medium'] = 'Moderate Risk (Review Suggestions)';
$labels['risk_high'] = 'High Risk (Likely to trigger spam filters)';
$labels['deliverability_tips'] = 'Deliverability Recommendations';
$labels['individual_delivery_badge'] = 'Individual 1-to-1 Envelope Delivery';
$labels['rfc8058_badge'] = 'RFC 8058 One-Click Unsubscribe Headers Active';
$labels['dual_mime_badge'] = 'Automatic HTML + Plaintext Dual MIME';

// Step 4: Dispatch
$labels['step_dispatch'] = '4. Batch Dispatch';
$labels['batch_settings'] = 'Batch Transmission Settings';
$labels['batch_size'] = 'Batch Size (emails per batch)';
$labels['batch_delay'] = 'Pause between batches (seconds)';
$labels['start_sending'] = 'Start Batch Sending';
$labels['sending_in_progress'] = 'Sending in Progress...';
$labels['pause_sending'] = 'Pause';
$labels['resume_sending'] = 'Resume';
$labels['cancel_sending'] = 'Cancel Campaign';
$labels['progress'] = 'Progress';
$labels['sent_count'] = 'Sent';
$labels['failed_count'] = 'Failed';
$labels['remaining_count'] = 'Remaining';
$labels['campaign_completed'] = 'Campaign completed successfully!';

// Suppression list
$labels['email_address'] = 'Email Address';
$labels['reason'] = 'Reason';
$labels['date_unsubscribed'] = 'Date Unsubscribed';
$labels['actions'] = 'Actions';
$labels['add_suppression'] = 'Add to Suppression List';
$labels['remove_suppression'] = 'Remove';
$labels['no_suppressions'] = 'No suppressed addresses found.';
$labels['confirm_remove_suppression'] = 'Are you sure you want to remove this address from the suppression list?';

// Unsubscribe public page
$labels['unsubscribe_title'] = 'Unsubscribe Confirmation';
$labels['unsubscribe_success'] = 'You have been successfully unsubscribed from this mailing list.';
$labels['unsubscribe_description'] = 'Your email address (%s) has been added to our suppression list and will not receive future newsletters.';
$labels['unsubscribe_btn'] = 'Confirm Unsubscribe';

// Compose button
$labels['compose_newsletter_btn'] = 'Create Newsletter';
$labels['compose_newsletter_tip'] = 'Open the batch Newsletter sender with anti-spam optimization';
