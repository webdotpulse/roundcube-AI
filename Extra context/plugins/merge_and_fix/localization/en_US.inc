<?php

/**
 * English localization for Merge & Fix Contacts Plugin
 */

$labels = [];
$labels['merge_and_fix'] = 'Merge & fix';
$labels['merge_and_fix_tip'] = 'Find duplicate entries, combine profiles, and clean up your contact list';
$labels['merge_and_fix_subtitle'] = 'Clean up duplicate contact cards and discover frequent email contacts';
$labels['rescan'] = 'Rescan';
$labels['reset_dismissed'] = 'Reset dismissed';
$labels['confirm_reset_dismissed'] = 'Reset all dismissed suggestions and re-scan your address book?';
$labels['dismissed_reset_success'] = 'Dismissed items have been reset. Re-scanning contacts.';

// Tabs
$labels['duplicates_tab'] = 'Duplicates';
$labels['frequent_tab'] = 'Frequent contacts';

// Duplicates Section
$labels['merge_duplicates_heading'] = 'Merge duplicate contacts';
$labels['merge_duplicates_desc'] = 'The following cards share matching names, phone numbers, or email addresses and likely belong to the same person.';
$labels['merge_all'] = 'Merge all';
$labels['scanning_duplicates'] = 'Scanning address book for duplicate contacts...';
$labels['no_duplicates_title'] = 'No duplicates found';
$labels['no_duplicates_desc'] = 'Your contact list is clean and up to date.';
$labels['merge_suggestion'] = 'Duplicate contact suggestion';
$labels['match'] = 'Match';
$labels['merged_preview'] = 'Merged result';
$labels['merge'] = 'Merge';
$labels['dismiss'] = 'Dismiss';
$labels['dismissed'] = 'Suggestion dismissed';

// Reasons
$labels['reason_matching_email'] = 'Matching email';
$labels['reason_matching_phone'] = 'Matching phone';
$labels['reason_matching_name'] = 'Matching name';

// Frequent Contacts Section
$labels['frequent_contacts_heading'] = 'Add frequent contacts';
$labels['frequent_contacts_desc'] = 'People you frequently communicate with who aren’t saved in your address book yet.';
$labels['add_all'] = 'Add all';
$labels['add_to_contacts'] = 'Add to contacts';
$labels['emails_exchanged'] = 'emails exchanged';
$labels['scanning_frequent'] = 'Analyzing communication history...';
$labels['no_frequent_title'] = 'No unsaved frequent contacts';
$labels['no_frequent_desc'] = 'All of your frequent email correspondents are already saved in your address book.';

// Operation Outcomes
$labels['contacts_merged_success'] = 'Contacts successfully merged!';
$labels['contact_added_success'] = 'Contact "%s" added to address book.';
$labels['batch_merged_summary'] = 'Successfully merged %d duplicate contact cards.';
$labels['batch_added_summary'] = 'Successfully added %d frequent contacts to your address book.';
$labels['no_duplicates_to_merge'] = 'No duplicate contacts to merge.';
$labels['invalid_contact_ids'] = 'Invalid contact IDs specified.';
$labels['invalid_email'] = 'Invalid email address provided.';
$labels['contact_not_found'] = 'One or more contacts could not be found.';
$labels['merge_failed'] = 'Could not merge contact records.';
$labels['add_contact_failed'] = 'Could not add contact to address book.';
