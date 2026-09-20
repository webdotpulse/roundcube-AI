<?php

$labels = [];
$labels['vacation_forward_title'] = 'Vacation & Forwarding';
$labels['vacation_title'] = 'Vacation / Out of Office';
$labels['forward_title'] = 'Mail Forwarding';
$labels['tab_vacation'] = 'Vacation & Auto-Reply';
$labels['tab_templates'] = 'Language Templates';
$labels['tab_forward'] = 'Forwarding Rules';
$labels['tab_exclusions'] = 'Anti-Loop & Filters';
$labels['tab_logs'] = 'Activity Logs';

$labels['vacation_status'] = 'Auto-Reply Status';
$labels['status_disabled'] = 'Disabled';
$labels['status_enabled'] = 'Enabled (Always Active)';
$labels['status_scheduled'] = 'Scheduled (Vacation Period)';
$labels['status_active_badge'] = 'Active Now';
$labels['status_scheduled_badge'] = 'Scheduled';
$labels['status_expired_badge'] = 'Expired';

$labels['vacation_start'] = 'Start Date & Time';
$labels['vacation_end'] = 'End Date & Time';
$labels['vacation_auto_disable'] = 'Automatically disable when period ends';
$labels['preset_weekend'] = 'This Weekend';
$labels['preset_next_week'] = 'Next Week';
$labels['preset_two_weeks'] = 'Next 2 Weeks';

$labels['rate_limit_interval'] = 'Reply Interval (Per Sender Throttle)';
$labels['interval_1_hour'] = 'Every 1 Hour';
$labels['interval_6_hours'] = 'Every 6 Hours';
$labels['interval_12_hours'] = 'Every 12 Hours';
$labels['interval_1_day'] = 'Every 24 Hours (1 Day)';
$labels['interval_2_days'] = 'Every 2 Days';
$labels['interval_3_days'] = 'Every 3 Days';
$labels['interval_7_days'] = 'Every 7 Days';
$labels['interval_14_days'] = 'Every 14 Days';
$labels['interval_unlimited'] = 'Always Reply (No Throttle)';

$labels['default_subject'] = 'Default Subject';
$labels['default_body'] = 'Default Message Body';

$labels['forward_status'] = 'Forwarding Status';
$labels['forward_destinations'] = 'Forward To (Email Addresses)';
$labels['forward_destinations_help'] = 'Enter one or more email addresses separated by comma or new line.';
$labels['forward_keep_copy'] = 'Keep a copy in Inbox';
$labels['forward_mode'] = 'Forwarding Mode';
$labels['forward_mode_redirect'] = 'Redirect (Preserves original sender)';
$labels['forward_mode_inline'] = 'Forward with inline preamble';
$labels['forward_condition'] = 'Forwarding Filter';
$labels['forward_all'] = 'Forward all incoming messages';
$labels['forward_matching_only'] = 'Only forward matching criteria';
$labels['forward_keywords'] = 'Subject keywords (comma separated)';

$labels['templates_title'] = 'Multi-Language Auto-Reply Templates';
$labels['templates_desc'] = 'Configure custom out-of-office responses for specific languages or domains. The system detects the sender\'s language and selects the best matching template.';
$labels['btn_add_template'] = 'Add Template';
$labels['btn_edit'] = 'Edit';
$labels['btn_delete'] = 'Delete';
$labels['btn_preview'] = 'Test / Preview';
$labels['template_name'] = 'Template Name';
$labels['template_lang'] = 'Language';
$labels['template_is_default'] = 'Default Fallback Template';
$labels['template_domain_rule'] = 'Domain or Email Match Filter (optional)';
$labels['template_subject'] = 'Subject Template';
$labels['template_body'] = 'Message Body';
$labels['placeholders_legend'] = 'Available Placeholders';
$labels['placeholder_start_date'] = 'Vacation start date';
$labels['placeholder_end_date'] = 'Vacation end date';
$labels['placeholder_return_date'] = 'Expected return date';
$labels['placeholder_sender_name'] = 'Sender name';
$labels['placeholder_sender_email'] = 'Sender email address';
$labels['placeholder_original_subject'] = 'Original message subject';
$labels['placeholder_user_name'] = 'Your display name';
$labels['placeholder_user_email'] = 'Your email address';

$labels['anti_loop_title'] = 'Anti-Loop & Smart Suppression';
$labels['ignore_auto_submitted'] = 'Ignore automated messages and mailing lists (Auto-Submitted, List-Id)';
$labels['ignore_daemons'] = 'Ignore bounces and system daemons (mailer-daemon, postmaster, noreply)';
$labels['sender_blacklist'] = 'Sender Blacklist (emails or @domains to ignore, one per line)';
$labels['subject_prefix'] = 'Subject Prefix';
$labels['max_replies_daily'] = 'Max Daily Auto-Replies (Global Limit)';

$labels['col_timestamp'] = 'Date & Time';
$labels['col_sender'] = 'Sender';
$labels['col_recipient'] = 'Recipient / Target';
$labels['col_action'] = 'Action';
$labels['col_template'] = 'Template';
$labels['col_status'] = 'Status';
$labels['action_auto_reply'] = 'Auto-Replied';
$labels['action_forwarded'] = 'Forwarded';
$labels['action_suppressed'] = 'Suppressed (Rate Limit)';
$labels['no_logs_found'] = 'No vacation or forwarding activity logged yet.';
$labels['btn_clear_logs'] = 'Clear Activity Logs';
$labels['btn_export_sieve'] = 'Export Sieve Script';
$labels['saved_success'] = 'Vacation & Forwarding settings successfully saved.';
$labels['cleared_logs_success'] = 'Activity logs cleared successfully.';
$labels['simulate_title'] = 'Auto-Reply Template Simulation';
$labels['simulate_sender'] = 'Test Sender Email';
$labels['simulate_subject'] = 'Test Subject';
$labels['simulate_body'] = 'Test Message Body';
$labels['simulate_run'] = 'Simulate Response';
$labels['simulate_matched'] = 'Matched Template';
$labels['simulate_detected_lang'] = 'Detected Language';
