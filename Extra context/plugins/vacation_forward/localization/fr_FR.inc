<?php

$labels = [];
$labels['vacation_forward_title'] = 'Absence & Transfert';
$labels['vacation_title'] = 'Message d\'absence / Répondeur';
$labels['forward_title'] = 'Transfert d\'e-mails';
$labels['tab_vacation'] = 'Absence & Répondeur';
$labels['tab_templates'] = 'Modèles linguistiques';
$labels['tab_forward'] = 'Règles de transfert';
$labels['tab_exclusions'] = 'Anti-boucle & Filtres';
$labels['tab_logs'] = 'Journal d\'activité';

$labels['vacation_status'] = 'Statut de réponse automatique';
$labels['status_disabled'] = 'Désactivé';
$labels['status_enabled'] = 'Activé (Toujours actif)';
$labels['status_scheduled'] = 'Planifié (Période d\'absence)';
$labels['status_active_badge'] = 'Actif actuellement';
$labels['status_scheduled_badge'] = 'Planifié';
$labels['status_expired_badge'] = 'Expiré';

$labels['vacation_start'] = 'Date et heure de début';
$labels['vacation_end'] = 'Date et heure de fin';
$labels['vacation_auto_disable'] = 'Désactiver automatiquement à la fin de la période';
$labels['preset_weekend'] = 'Ce week-end';
$labels['preset_next_week'] = 'La semaine prochaine';
$labels['preset_two_weeks'] = 'Les 2 prochaines semaines';

$labels['rate_limit_interval'] = 'Intervalle de réponse (Limite par expéditeur)';
$labels['interval_1_hour'] = 'Toutes les heures';
$labels['interval_6_hours'] = 'Toutes les 6 heures';
$labels['interval_12_hours'] = 'Toutes les 12 heures';
$labels['interval_1_day'] = 'Toutes les 24 heures (1 jour)';
$labels['interval_2_days'] = 'Tous les 2 jours';
$labels['interval_3_days'] = 'Tous les 3 jours';
$labels['interval_7_days'] = 'Tous les 7 jours';
$labels['interval_14_days'] = 'Tous les 14 jours';
$labels['interval_unlimited'] = 'Toujours répondre (Sans limite)';

$labels['default_subject'] = 'Objet par défaut';
$labels['default_body'] = 'Corps de message par défaut';

$labels['forward_status'] = 'Statut de transfert';
$labels['forward_destinations'] = 'Transférer à (Adresses e-mail)';
$labels['forward_destinations_help'] = 'Saisissez une ou plusieurs adresses e-mail séparées par des virgules ou retours à la ligne.';
$labels['forward_keep_copy'] = 'Conserver une copie dans la boîte de réception';
$labels['forward_mode'] = 'Mode de transfert';
$labels['forward_mode_redirect'] = 'Redirection (Conserve l\'expéditeur d\'origine)';
$labels['forward_mode_inline'] = 'Transfert avec en-tête en ligne';
$labels['forward_condition'] = 'Filtre de transfert';
$labels['forward_all'] = 'Transférer tous les messages entrants';
$labels['forward_matching_only'] = 'Transférer uniquement selon critères';
$labels['forward_keywords'] = 'Mots-clés dans l\'objet (séparés par des virgules)';

$labels['templates_title'] = 'Modèles de réponse multilingues';
$labels['templates_desc'] = 'Configurez des réponses personnalisées par langue ou domaine. Le système détecte automatiquement la langue de l\'expéditeur.';
$labels['btn_add_template'] = 'Ajouter un modèle';
$labels['btn_edit'] = 'Modifier';
$labels['btn_delete'] = 'Supprimer';
$labels['btn_preview'] = 'Tester / Aperçu';
$labels['template_name'] = 'Nom du modèle';
$labels['template_lang'] = 'Langue';
$labels['template_is_default'] = 'Modèle par défaut';
$labels['template_domain_rule'] = 'Filtre de domaine ou d\'adresse (facultatif)';
$labels['template_subject'] = 'Modèle d\'objet';
$labels['template_body'] = 'Corps du message';
$labels['placeholders_legend'] = 'Variables disponibles';
$labels['placeholder_start_date'] = 'Date de début';
$labels['placeholder_end_date'] = 'Date de fin';
$labels['placeholder_return_date'] = 'Date de retour prévue';
$labels['placeholder_sender_name'] = 'Nom de l\'expéditeur';
$labels['placeholder_sender_email'] = 'Adresse e-mail de l\'expéditeur';
$labels['placeholder_original_subject'] = 'Objet d\'origine';
$labels['placeholder_user_name'] = 'Votre nom affiché';
$labels['placeholder_user_email'] = 'Votre adresse e-mail';

$labels['anti_loop_title'] = 'Anti-boucle & Suppression intelligente';
$labels['ignore_auto_submitted'] = 'Ignorer les messages automatiques et listes de diffusion (Auto-Submitted, List-Id)';
$labels['ignore_daemons'] = 'Ignorer les rebonds et démons système (mailer-daemon, postmaster, noreply)';
$labels['sender_blacklist'] = 'Liste noire d\'expéditeurs (adresses ou @domaines, une par ligne)';
$labels['subject_prefix'] = 'Préfixe d\'objet';
$labels['max_replies_daily'] = 'Max. de réponses quotidiennes (Limite globale)';

$labels['col_timestamp'] = 'Date & Heure';
$labels['col_sender'] = 'Expéditeur';
$labels['col_recipient'] = 'Destinataire / Cible';
$labels['col_action'] = 'Action';
$labels['col_template'] = 'Modèle';
$labels['col_status'] = 'Statut';
$labels['action_auto_reply'] = 'Réponse automatique';
$labels['action_forwarded'] = 'Transféré';
$labels['action_suppressed'] = 'Supprimé (Limite)';
$labels['no_logs_found'] = 'Aucune activité d\'absence ou de transfert enregistrée.';
$labels['btn_clear_logs'] = 'Effacer le journal';
$labels['btn_export_sieve'] = 'Exporter le script Sieve';
$labels['saved_success'] = 'Paramètres enregistrés avec succès.';
$labels['cleared_logs_success'] = 'Journal d\'activité effacé avec succès.';
$labels['simulate_title'] = 'Simulation de modèle';
$labels['simulate_sender'] = 'Expéditeur de test';
$labels['simulate_subject'] = 'Objet de test';
$labels['simulate_body'] = 'Message de test';
$labels['simulate_run'] = 'Lancer la simulation';
$labels['simulate_matched'] = 'Modèle sélectionné';
$labels['simulate_detected_lang'] = 'Langue détectée';
