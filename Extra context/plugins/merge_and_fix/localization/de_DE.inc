<?php

/**
 * German localization for Merge & Fix Contacts Plugin (Zusammenführen und korrigieren)
 */

$labels = [];
$labels['merge_and_fix'] = 'Zusammenführen und korrigieren';
$labels['merge_and_fix_tip'] = 'Duplikate finden, Profile kombinieren und Kontaktliste bereinigen';
$labels['merge_and_fix_subtitle'] = 'Doppelte Kontakte bereinigen und häufige E-Mail-Kontakte entdecken';
$labels['rescan'] = 'Neu scannen';
$labels['reset_dismissed'] = 'Verworfene zurücksetzen';
$labels['confirm_reset_dismissed'] = 'Alle verworfenen Vorschläge zurücksetzen und Adressbuch neu scannen?';
$labels['dismissed_reset_success'] = 'Verworfene Vorschläge wurden zurückgesetzt. Kontakte werden neu gescannt.';

// Tabs
$labels['duplicates_tab'] = 'Duplikate';
$labels['frequent_tab'] = 'Häufige Kontakte';

// Duplicates Section
$labels['merge_duplicates_heading'] = 'Doppelte Kontakte zusammenführen';
$labels['merge_duplicates_desc'] = 'Die folgenden Kontakte stimmen in Name, Telefonnummer oder E-Mail-Adresse überein.';
$labels['merge_all'] = 'Alle zusammenführen';
$labels['scanning_duplicates'] = 'Adressbuch wird nach Duplikaten durchsucht...';
$labels['no_duplicates_title'] = 'Keine Duplikate gefunden';
$labels['no_duplicates_desc'] = 'Ihre Kontaktliste ist sauber und auf dem neuesten Stand.';
$labels['merge_suggestion'] = 'Duplikatvorschlag';
$labels['match'] = 'Übereinstimmung';
$labels['merged_preview'] = 'Zusammengeführtes Ergebnis';
$labels['merge'] = 'Zusammenführen';
$labels['dismiss'] = 'Verwerfen';
$labels['dismissed'] = 'Vorschlag verworfen';

// Reasons
$labels['reason_matching_email'] = 'Übereinstimmende E-Mail';
$labels['reason_matching_phone'] = 'Übereinstimmende Telefonnummer';
$labels['reason_matching_name'] = 'Übereinstimmender Name';

// Frequent Contacts Section
$labels['frequent_contacts_heading'] = 'Häufige Kontakte hinzufügen';
$labels['frequent_contacts_desc'] = 'Personen, mit denen Sie häufig kommunizieren, die noch nicht im Adressbuch gespeichert sind.';
$labels['add_all'] = 'Alle hinzufügen';
$labels['add_to_contacts'] = 'Zu Kontakten hinzufügen';
$labels['emails_exchanged'] = 'E-Mails ausgetauscht';
$labels['scanning_frequent'] = 'Kommunikationsverlauf wird analysiert...';
$labels['no_frequent_title'] = 'Keine ungespeicherten Kontakte';
$labels['no_frequent_desc'] = 'Alle häufigen E-Mail-Kontakte sind bereits in Ihrem Adressbuch gespeichert.';

// Operation Outcomes
$labels['contacts_merged_success'] = 'Kontakte erfolgreich zusammengeführt!';
$labels['contact_added_success'] = 'Kontakt "%s" zum Adressbuch hinzugefügt.';
$labels['batch_merged_summary'] = '%d doppelte Kontakte erfolgreich zusammengeführt.';
$labels['batch_added_summary'] = '%d häufige Kontakte erfolgreich zum Adressbuch hinzugefügt.';
$labels['no_duplicates_to_merge'] = 'Keine doppelten Kontakte zum Zusammenführen vorhanden.';
$labels['invalid_contact_ids'] = 'Ungültige Kontakt-IDs angegeben.';
$labels['invalid_email'] = 'Ungültige E-Mail-Adresse angegeben.';
$labels['contact_not_found'] = 'Ein oder mehrere Kontakte konnten nicht gefunden werden.';
$labels['merge_failed'] = 'Kontakte konnten nicht zusammengeführt werden.';
$labels['add_contact_failed'] = 'Kontakt konnte nicht hinzugefügt werden.';
