<?php

/**
 * French localization for Merge & Fix Contacts Plugin (Fusionner et corriger)
 */

$labels = [];
$labels['merge_and_fix'] = 'Fusionner et corriger';
$labels['merge_and_fix_tip'] = 'Trouver les doublons, combiner les profils et nettoyer votre carnet d\'adresses';
$labels['merge_and_fix_subtitle'] = 'Nettoyez les fiches en double et découvrez des contacts fréquents';
$labels['rescan'] = 'Analyser à nouveau';
$labels['reset_dismissed'] = 'Réinitialiser les éléments ignorés';
$labels['confirm_reset_dismissed'] = 'Réinitialiser toutes les suggestions ignorées et réanalyser le carnet ?';
$labels['dismissed_reset_success'] = 'Les éléments ignorés ont été réinitialisés. Nouvelle analyse en cours.';

// Tabs
$labels['duplicates_tab'] = 'Doublons';
$labels['frequent_tab'] = 'Contacts fréquents';

// Duplicates Section
$labels['merge_duplicates_heading'] = 'Fusionner les contacts en double';
$labels['merge_duplicates_desc'] = 'Les fiches suivantes partagent des noms, numéros de téléphone ou adresses e-mail similaires.';
$labels['merge_all'] = 'Tout fusionner';
$labels['scanning_duplicates'] = 'Analyse du carnet d\'adresses en cours...';
$labels['no_duplicates_title'] = 'Aucun doublon détecté';
$labels['no_duplicates_desc'] = 'Votre carnet d\'adresses est parfaitement nettoyé et à jour.';
$labels['merge_suggestion'] = 'Suggestion de doublon';
$labels['match'] = 'Correspondance';
$labels['merged_preview'] = 'Résultat fusionné';
$labels['merge'] = 'Fusionner';
$labels['dismiss'] = 'Ignorer';
$labels['dismissed'] = 'Suggestion ignorée';

// Reasons
$labels['reason_matching_email'] = 'E-mail identique';
$labels['reason_matching_phone'] = 'Téléphone identique';
$labels['reason_matching_name'] = 'Nom identique';

// Frequent Contacts Section
$labels['frequent_contacts_heading'] = 'Ajouter des contacts fréquents';
$labels['frequent_contacts_desc'] = 'Personnes avec qui vous communiquez souvent et qui ne sont pas encore enregistrées.';
$labels['add_all'] = 'Tout ajouter';
$labels['add_to_contacts'] = 'Ajouter aux contacts';
$labels['emails_exchanged'] = 'e-mails échangés';
$labels['scanning_frequent'] = 'Analyse de l\'historique des échanges...';
$labels['no_frequent_title'] = 'Aucun contact fréquent à ajouter';
$labels['no_frequent_desc'] = 'Tous vos correspondants fréquents sont déjà enregistrés dans vos contacts.';

// Operation Outcomes
$labels['contacts_merged_success'] = 'Contacts fusionnés avec succès !';
$labels['contact_added_success'] = 'Contact "%s" ajouté au carnet d\'adresses.';
$labels['batch_merged_summary'] = '%d contacts en double fusionnés avec succès.';
$labels['batch_added_summary'] = '%d contacts fréquents ajoutés à votre carnet d\'adresses.';
$labels['no_duplicates_to_merge'] = 'Aucun doublon à fusionner.';
$labels['invalid_contact_ids'] = 'Identifiants de contact non valides.';
$labels['invalid_email'] = 'Adresse e-mail non valide.';
$labels['contact_not_found'] = 'Un ou plusieurs contacts sont introuvables.';
$labels['merge_failed'] = 'Impossible de fusionner les contacts.';
$labels['add_contact_failed'] = 'Impossible d\'ajouter le contact.';
