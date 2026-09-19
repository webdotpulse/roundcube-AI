<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Calendrier';
$labels['app_menu_title'] = 'Calendrier';

// menu
$labels['create_event'] = 'Créer un événement';
$labels['create_event_button'] = 'Créer';
$labels['day'] = 'Jour';
$labels['week'] = 'Semaine';
$labels['month'] = 'Mois';
$labels['agenda'] = 'Agenda';

// editing calendars
$labels['calendars'] = 'Calendrier';
$labels['my_calendar'] = 'Mon calendrier';
$labels['create_calendar'] = 'Créer un calendrier';
$labels['new_calendar'] = 'Nouveau calendrier';
$labels['edit_calendar'] = 'Éditer un calendrier';
$labels['remove_calendar'] = 'Supprimer un calendrier';
$labels['confirm_remove_calendar'] = 'Êtes-vous sûr de vouloir supprimer ce calendrier ?';
$labels['calendar_removed'] = 'Calendrier supprimé.';
$labels['notifications'] = 'Notifications';
$labels['default_event_visibility'] = 'Visibilité par défaut';
$labels['time_zone'] = 'Fuseau horaire';
$labels['share_calendar'] = 'Partager le calendrier';
$labels['permissions'] = 'Permissions';
$labels['permission_publish_calendar'] = 'Publier le calendrier';
$labels['permission_share_calendar'] = 'Partager le calendrier';
$labels['permission_edit_events'] = 'Éditer les événements';
$labels['permission_see_details'] = 'Voir les détails';
$labels['permission_publish_calendar_info'] = 'Si coché, l\'utilisateur pourra publier le calendrier.';
$labels['permission_share_calendar_info'] = 'Si coché, l\'utilisateur pourra partager le calendrier avec d\'autres utilisateurs.';
$labels['permission_edit_events_info'] = 'Si coché, l\'utilisateur pourra modifier et supprimer les événements du calendrier.';
$labels['permission_see_details_info'] = 'Si cette case est cochée, tous les détails de l\'événement seront partagés. Si elle n\'est pas cochée, seules les informations disponible/occupé seront partagées.';
$labels['share_send_notification'] = 'Notification';
$labels['share_send_notification_info'] = 'Si coché, un email sera envoyé à l\'utilisateur pour l\'informer du partage de ce calendrier.';
$labels['new_share_not_saved_warning'] = 'Vous avez renseigné un email avec qui partager le calendrier, mais vous n\'avez pas cliqué sur le bouton Partage pour l\'ajouter à la liste partagée. Continuer à enregistrer sans partager le calendrier avec l\'utilisateur spécifié?';
$labels['share'] = 'Partager';
$labels['shared_marker'] = '(Partagé)';
$labels['owner'] = 'Propriétaire';
$labels['publish_calendar'] = 'Publier le calendrier';
$labels['create_link'] = 'Créer un lien';
$labels['reset'] = 'Réinitialiser';
$labels['remove'] = 'Supprimer';
$labels['publish_full'] = 'Publier les informations des événements.';
$labels['publish_busy'] = 'Publier seulement les événements libres ou occupés.';
$labels['sync_no_id'] = 'Les options CalDAV seront disponibles après avoir enregistré le calendrier.';
$labels['share_no_id'] = 'Les options de partage seront disponibles après l\'enregistrement du calendrier.';
$labels['publish_no_id'] = 'Les informations publiées deviendront visibles après l\'enregistrement du calendrier.';
$labels['import_no_id'] = 'Les options d\'importation et d\'exportation seront disponibles après l\'enregistrement du calendrier.';
$labels['unable_to_save_calendar_data'] = 'Impossible d\'enregistrer les données du calendrier.';
$labels['unable_to_retrieve_calendar_data'] = 'Impossible de récupérer les données du calendrier. Le calendrier n\'est peut-être plus disponible.';
$labels['unable_add_shared_calendar'] = 'Impossible d\'ajouter le calendrier partagé. Le calendrier n\'est peut-être plus disponible.';
$labels['itip_organizer_message'] = 'Vous avez été invité à cet événement mais vous n\'en êtes pas l\'organisateur. Les modifications que vous apportez à cet événement ne seront pas transmises aux autres participants.';
$labels['cannot_share_with_yourself'] = 'Vous ne pouvez pas partager votre propre calendrier avec vous-même.';
$labels['cannot_share_with_calendar_owner'] = 'Vous ne pouvez pas partager ce calendrier avec le propriétaire du calendrier ($e)';
$labels['setting_default_notification_type'] = 'Notifications par défaut pour les nouveaux événements';
$labels['calendar_name_cannot_be_empty'] = 'Le nom du calendrier ne peut pas être vide.';

// caldav server
$labels['sync_calendar'] = 'Synchroniser le calendrier';
$labels['password_too_short'] = 'Le mot de passe doit comporter au moins 10 caractères.';
$labels['read_only'] = 'Lecture seule';
$labels['add_connection'] = 'Ajouter une connexion';
$labels['show_details'] = 'Afficher les détails';
$labels['hide_details'] = 'Masquer les détails';
$labels['caldav_connection_password'] = 'Le mot de passe que vous avez spécifié lors de la création de cette synchronisation.';
$labels['yes'] = 'Oui';
$labels['no'] = 'Non';
$labels['confirm_remove_sync'] = 'Êtes-vous sûr de vouloir supprimer définitivement cette synchronisation ?';
$labels['remove_sync_error'] = 'Une erreur s\'est produite lors de la suppression de la synchronisation.';
$labels['create_new_caldav_connection'] = 'Créer une nouvelle synchronisation CalDav';
$labels['your_caldav_connections'] = 'Vos synchronisations CalDav';
$labels['created_at'] = 'Créé';
$labels['connected_at'] = 'Dernière synchronisation';
$labels['never'] = 'Jamais';
$labels['alternate_url'] = 'URL alternative';
$labels['caldav_password_info'] = 'Pour des raisons de sécurité, ce mot de passe n\'est pas enregistré sur le serveur. Si vous l\'avez oublié, supprimez cette connexion et créez-en une nouvelle.';
$labels['caldav_url_info'] = 'Utilisez cette URL avec des applications prenant en charge la découverte de calendrier, par exemple: Calendrier iOS et Mac OS, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Utilisez cette URL avec des applications qui ne fonctionneront pas avec l\'URL courte ci-dessus, par exemple: Thunderbird Lightning. (Cette URL ne fonctionnera pas avec les calendriers iOS et OSX.)';
$labels['invalid_caldav_url'] = 'URL du serveur CalDAV non valide.';
$labels['cannot_resolve_caldav_host'] = 'Impossible de résoudre l\'hôte du serveur CalDAV.';
$labels['private_caldav_addresses_not_allowed'] = 'Les adresses CalDAV privées et réservées ne sont pas autorisées.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Ajouter un calendrier partagé';
$labels['shared_calendars'] = 'Calendriers partagés';
$labels['no_shared_calendars'] = 'Il n\'y a pas de calendrier partagé que vous puissiez ajouter.';
$labels['add_to_my_calendars'] = 'Ajouter à mes calendriers';
$labels['remove_from_my_calendars'] = 'Supprimer de mes calendriers';
$labels['unshare'] = 'Ne pas ajouter';
$labels['unshare_warning'] = 'Si vous n\'ajoutez pas ce calendrier, il disparaîtra de cette liste et vous n\'aurez plus de moyen de l\'ajouter.';
$labels['calendar_name'] = 'Nom du calendrier';
$labels['calendar_owner'] = 'Propriétaire du calendrier';
$labels['shared_date'] = 'Partagé';
$labels['unable_to_share'] = 'Le calendrier ne peut pas être partagé avec les utilisateurs suivants car leurs comptes de messagerie se trouvent sur un serveur distant :';

// shared calendar email
$labels['shared_email_subject'] = '$u a partagé un agenda avec vous';
$labels['shared_email_body'] = 'L\'utilisateur $u vous a donné accès au calendrier appelé « $c ». Pour ajouter celui-ci à votre liste des calendriers actifs, cliquez sur le lien ci-dessous :';
$labels['shared_email_body_cpanel'] = 'L\'utilisateur $u vous a donné accès au calendrier appelé « $c ». Pour ajouter celui-ci à votre liste de calendriers actifs, connectez-vous à votre application de messagerie, rubrique « Agenda », allez dans « Calendrier », cliquez sur l\'icône « plus » dans la barre latérale et sélectionnez « Ajouter un calendrier partagé ».';

// adding holiday calendars
$labels['holiday_calendars'] = 'Calendriers des fêtes';
$labels['edit_holiday_calendar'] = 'Modifier le calendrier des vacances';
$labels['religious_holidays'] = 'Vacances religieuses';
$labels['country_holidays'] = 'Vacances d\'un pays';
$labels['add'] = 'Ajouter';
$labels['no_more_holidays_allowed'] = 'Vous ne pouvez pas ajouter d\'autres calendriers de vacances.';
$labels['cannot_load_holidays'] = 'Impossible de charger les vacances à partir du serveur Google.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Ajouter un calendrier des anniversaires';
$labels['edit_birthday_calendar'] = 'Modifier le calendrier des anniversaires';
$labels['contact_birthdays'] = 'Contact Anniversaires';
$labels['contact_birthdays_info'] = 'Ce calendrier récupère les informations relatives aux anniversaires dans votre liste de contacts et les affiche sous forme d\'événements.';
$labels['birthday_event_title'] = 'Anniversaire : $n';

// adding google calendars
$labels['add_google_calendar'] = 'Ajouter un agenda Google';
$labels['edit_google_calendar'] = 'Modifier le calendrier Google';
$labels['google_id'] = 'ID de l\'agenda Google';
$labels['new_google_calendar'] = 'Nouvel agenda Google';
$labels['specify_google_calendar_id'] = 'Tapez l\'ID de l\'agenda Google';
$labels['calendar_id_format_error'] = 'L\'ID de l\'agenda Google devrait inclure le caractère « @ ».';
$labels['cannot_load_google_calendar'] = 'Le calendrier Google "$c" ne peut pas être chargé. Assurez-vous que l\'ID du calendrier est correct et que le calendrier a été mis à la disposition du public.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Ajouter un calendrier CalDAV';
$labels['edit_caldav_calendar'] = 'Modifier le calendrier CalDAV';
$labels['add_caldav_calendar_information'] = 'Pour s\'abonner à un calendrier CalDAV, indiquez les informations relatives au serveur CalDAV et cliquez sur Rechercher des calendriers.';
$labels['caldav_url'] = 'URL CalDAV';
$labels['find_calendars'] = 'Trouver des calendriers';
$labels['searching_for_calendars'] = 'Recherche de calendriers, veuillez patienter...';
$labels['select_calendars'] = 'Sélectionnez les calendriers à ajouter :';
$labels['read_only_status_yes'] = 'Oui';
$labels['read_only_status_no'] = 'Non';
$labels['subscribed'] = 'Abonné';

$labels['refresh_calendar'] = 'Actualiser le calendrier';
$labels['refresh_every_minute'] = 'Chaque minute';
$labels['refresh_every_5_minutes'] = 'Toutes les 5 minutes';
$labels['refresh_every_15_minutes'] = 'Toutes les 15 minutes';
$labels['refresh_every_30_minutes'] = 'Toutes les 30 minutes';
$labels['refresh_every_60_minutes'] = 'Toutes les 60 minutes';
$labels['refresh_never'] = 'Jamais';

$labels['cannot_connect_to_caldav_server'] = 'Impossible de se connecter au serveur CalDAV "$n".';
$labels['caldav_client_error_url'] = 'Impossible de se connecter au serveur CalDAV. Vérifier l\'URL du serveur.';
$labels['caldav_client_error_credentials'] = 'Nom d\'utilisateur ou mot de passe CalDAV incorrect.';
$labels['caldav_client_error_empty'] = 'Le serveur n\'a renvoyé aucun calendrier pouvant être ajouté. Vérifiez l\'URL CalDAV.';
$labels['caldav_client_error_server_info'] = 'Informations incorrectes sur le serveur CalDAV.';
$labels['caldav_client_error_add_calendar'] = 'Impossible d\'enregistrer le calendrier "$n".';
$labels['caldav_client_error_delete_event'] = 'Impossible de supprimer l\'événement';
$labels['caldav_client_error_save_event'] = 'Impossible d\'enregistrer l\'événement CalDAV : il a peut-être été déplacé ou supprimé, ou bien le calendrier n\'est pas disponible.';
$labels['caldav_client_error_calendar_added'] = 'Le calendrier "$n" figure déjà dans votre liste de calendriers.';

// previewing event
$labels['remove_recurring_event'] = 'Supprimer l\'événement récurrent';
$labels['remove_one'] = 'Supprimer uniquement cette instance';
$labels['remove_all'] = 'Supprimer toutes les instances';
$labels['edit_event'] = 'Modifier l\'événement';
$labels['event_removed'] = 'Événement supprimé.';
$labels['go_to_url'] = 'Aller à l\'URL';
$labels['confirm_remove_event'] = 'Êtes-vous sûr de vouloir supprimer cet événement ?';

// calendar/agenda grid
$labels['no_events'] = 'Aucun événement à afficher.';

// importing events
$labels['select_calendar'] = 'Sélectionnez le calendrier';
$labels['select_calendar_for_events'] = 'Sélectionnez le calendrier auquel vous souhaitez ajouter les événements :';
$labels['add_events'] = 'Ajouter des événements';
$labels['add_events_to_calendar'] = 'Ajouter des événements au calendrier';
$labels['events_imported_successfully'] = 'Événements importés avec succès';
$labels['events_imported_with_errors'] = 'D\'autres événements n\'ont pas pu être importés.';
$labels['error_importing_events'] = 'Une erreur s\'est produite lors de l\'importation d\'événements dans le calendrier';

// editing events
$labels['calendar'] = 'Calendrier';
$labels['new_event'] = 'Nouvel événement';
$labels['summary'] = 'Options principales';
$labels['recurrence'] = 'Récurrence';
$labels['title'] = 'Titre';
$labels['location'] = 'Lieu';
$labels['description'] = 'Description';
$labels['comment'] = 'Commentaire (facultatif)';
$labels['url'] = 'URL';
$labels['name'] = 'Nom';
$labels['start'] = 'Début';
$labels['end'] = 'Fin';
$labels['show_timezones'] = 'Afficher les fuseaux horaires';
$labels['reset_timezones'] = 'Réinitialiser les fuseaux horaires par défaut';
$labels['all_day'] = 'Toute la journée';
$labels['use_calendar_colors'] = 'Utiliser les couleurs du calendrier';
$labels['color'] = 'Couleur';
$labels['background'] = 'Fond';
$labels['text'] = 'Texte';
$labels['show_me_as'] = 'Montrez-moi comme';
$labels['available'] = 'Disponible';
$labels['busy'] = 'Occupé';
$labels['visibility'] = 'Visibilité';
$labels['calendar_default'] = 'Calendrier par défaut';
$labels['public'] = 'Public';
$labels['private'] = 'Privé';
$labels['repeat'] = 'Répéter';
$labels['repeat_every'] = 'Répétez tous les';
$labels['never'] = 'Jamais';
$labels['daily'] = 'Quotidien';
$labels['weekly'] = 'Hebdomadaire';
$labels['monthly'] = 'Mensuel';
$labels['yearly'] = 'Annuel';
$labels['days'] = 'jours';
$labels['weeks'] = 'semaines';
$labels['months'] = 'Mois';
$labels['years'] = 'Année(s)';
$labels['repeat_on'] = 'Répéter sur';
$labels['repeat_by'] = 'Répéter par';
$labels['day_of_month'] = 'Jour du mois';
$labels['day_of_week'] = 'jour de la semaine';
$labels['not_specified'] = 'Pas spécifié';
$labels['first'] = 'Premier';
$labels['second'] = 'Second';
$labels['third'] = 'Troisième';
$labels['fourth'] = 'Quatrième';
$labels['fifth'] = 'Cinquième';
$labels['second_to_last'] = 'L\'avant-dernière place';
$labels['last'] = 'Dernier';
$labels['attachment_too_large'] = 'Le fichier attaché est trop grand. La taille maximale autorisée est $s.';
$labels['bg_color'] = 'Couleur du fond';
$labels['tx_color'] = 'Couleur du texte';
$labels['add_notification'] = 'Ajouter une notification';
$labels['popup'] = 'Faire apparaître';
$labels['no_notifications'] = 'Cet événement n\'a aucune notification.';
$labels['minutes'] = 'minutes';
$labels['hours'] = 'heures';
$labels['link'] = 'Lien';
$labels['attach_file_from_web'] = 'Joindre un fichier';
$labels['attachment_url'] = 'URL de la pièce jointe';
$labels['invalid_url'] = 'Cette URL n\'est pas valide.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = 'Dupliquer';
$labels['duplicate_suffix'] = '(duplicata)';
$labels['no_notifications'] = 'Cet événement n\'a aucune notification.';
$labels['this_calendar_is_hidden'] = 'Ce calendrier est masqué. Les événements enregistrés n\'apparaîtront pas tant que le calendrier ne sera pas visible.';
$labels['make_calendar_visible'] = 'Rendez ce calendrier visible.';

// recurrence
$labels['recurrence_range'] = 'Plage de récurrence';
$labels['repeat_forever'] = 'Répéter à l\'infini';
$labels['repeat_every_weekday'] = 'Répéter tous les jours de la semaine';
$labels['last_day'] = 'Dernier jour';
$labels['every'] = 'Chaque';
$labels['weekday'] = 'Jour de la semaine';
$labels['weekend_day'] = 'Jour du week-end';
$labels['day_of_week'] = 'jour de la semaine';
$labels['day_of_month'] = 'Jour du mois';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Fin après';
$labels['occurrences'] = 'occurrences';

// end recurrence on date:
$labels['end_by_date'] = 'Date de fin';
$labels['removed_events_title'] = 'Événements supprimés des séries récurrentes';
$labels['restore'] = 'Restaurer';

// settings page
$labels['setting_view'] = 'Vue d\'ensemble du calendrier';
$labels['setting_first_day'] = 'La semaine commence le';
$labels['setting_agenda_week_span'] = 'Durée de l\'agenda (semaines)';
$labels['setting_slot_duration'] = 'Durée minimum d\'un événement (mn)';
$labels['setting_scroll_time'] = 'Faites défiler jusqu\'à l\'heure';
$labels['setting_calendar'] = 'Calendrier par défaut';
$labels['setting_refresh'] = 'Actualiser les calendriers';
$labels['setting_week_numbers'] = 'Numéro de semaine dans le petit calendrier';
$labels['setting_event_border'] = 'Couleur de catégorie comme bordure d\'événement';
$labels['show_sunrise'] = 'Afficher le lever du soleil en vue mensuelle';
$labels['show_sunset'] = 'Afficher le coucher de soleil en vue mensuelle';
$labels['categories'] = 'Catégories';
$labels['category'] = 'Catégorie';
$labels['no_category'] = 'Pas de catégorie';
$labels['add_category'] = 'Ajouter une catégorie';
$labels['setting_alarm_sound'] = 'Notification sonore';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bamboo';
$labels['alarm_sound_bells'] = 'Bells';
$labels['alarm_sound_chord'] = 'Chord';
$labels['alarm_sound_circles'] = 'Circles';
$labels['alarm_sound_glass'] = 'Glass';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Metal';
$labels['alarm_sound_ripple'] = 'Ripple';
$labels['alarm_sound_startrek'] = 'Star Trek';
$labels['alarm_sound_whistle'] = 'Whistle';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Jouer un son';

// alarms
$labels['upcoming_events'] = 'Évènements à venir';
$labels['dismiss'] = 'Rejeter';
$labels['dismiss_all'] = 'Tout rejeter';
$labels['snooze'] = 'Veille';
$labels['5_minutes'] = '5 minutes';
$labels['10_minutes'] = '10 minutes';
$labels['20_minutes'] = '20 minutes';
$labels['30_minutes'] = '30 minutes';
$labels['1_hour'] = '1 heure';
$labels['2_hours'] = '2 heures';
$labels['tomorrow'] = 'Jusqu\'à demain';
$labels['next_week'] = 'Jusqu\'à la prochaine semaine';

// sidebar
$labels['setting_show_xcalendar'] = 'Afficher l\'Agenda';
$labels['todays_agenda'] = 'Aujourd\'hui';
$labels['no_events_today'] = 'Aucun événement.';
$labels['sidebar_more'] = 'Plus...';

// event invitation
$labels['itip_title_request'] = 'Vous êtes invité à un événement.';
$labels['itip_title_modified'] = 'Cet événement a été modifié.';
$labels['itip_title_reply_accepted'] = '$u a accepté votre invitation';
$labels['itip_title_reply_tentative'] = '$u a provisoirement accepté votre invitation';
$labels['itip_title_reply_declined'] = '$u a refusé votre invitation';
$labels['itip_title_cancel'] = 'Cet événement a été annulé.';

$labels['itip_email_subject_request'] = 'Invitation :';
$labels['itip_email_subject_request_modified'] = 'Événement modifié :';
$labels['itip_email_subject_reply'] = 'Réponse :';
$labels['itip_email_subject_cancel'] = 'Annulé :';

$labels['itip_popup_event_added'] = 'Événement ajouté au calendrier.';
$labels['itip_popup_event_updated'] = 'Événement mis à jour dans le calendrier.';
$labels['itip_popup_event_deleted'] = 'Événement supprimé du calendrier.';
$labels['itip_popup_response_sent'] = 'Réponse envoyée à l\'organisateur.';

$labels['itip_message_invitation_accepted'] = 'Vous avez accepté cette invitation.';
$labels['itip_message_invitation_tentative'] = 'Vous avez provisoirement accepté cette invitation.';
$labels['itip_message_invitation_declined'] = 'Vous avez décliné cette invitation.';
$labels['itip_message_event_deleted'] = 'L\'événement a été supprimé de votre calendrier.';
$labels['itip_message_event_updated'] = 'L\'événement de votre calendrier a été mis à jour.';
$labels['itip_message_event_missing'] = 'Cet événement n\'existe dans aucun de vos agendas.';

$labels['itip_accept'] = 'Accepter';
$labels['itip_tentative'] = 'Provisoire';
$labels['itip_decline'] = 'Refuser';
$labels['itip_change_reponse'] = 'Changer de réponse.';
$labels['itip_add_to_calendar'] = 'Ajouter au calendrier';
$labels['itip_reply_to_sender'] = 'Répondre à l\'expéditeur';
$labels['itip_dont_import'] = 'Ne pas ajouter au calendrier';
$labels['itip_update_in_calendar'] = 'Mettre à jour l\'événement dans le calendrier';
$labels['itip_delete_from_calendar'] = 'Supprimer l\'événement du calendrier';

$labels['mail_subject_invitation'] = 'Invitation :';
$labels['mail_subject_update'] = 'Invitation mise à jour :';
$labels['mail_subject_cancel'] = 'Événement annulé :';

// event email notifications
$labels['mail_title'] = 'Titre :';
$labels['mail_start'] = 'Début :';
$labels['mail_end'] = 'Fin :';
$labels['mail_description'] = 'Description :';
$labels['mail_location'] = 'Emplacement :';

// event attendees
$labels['attendees'] = 'Participants';
$labels['attendee_role'] = 'Rôle';
$labels['attendee_role_required'] = 'Obligatoire';
$labels['attendee_role_optional'] = 'Optionnel';
$labels['attendee_role_chair'] = 'Dirigeant';
$labels['attendee_role_none'] = 'Non-participant';
$labels['organizer'] = 'Organisateur';
$labels['attendance'] = 'Présence';
$labels['your_status'] = 'Votre statut';
$labels['attendance_going'] = 'Vous partez ?';
$labels['attendee_status_yes'] = 'Oui';
$labels['attendee_status_no'] = 'Non';
$labels['attendee_status_maybe'] = 'Peut-être';
$labels['attendee_status_delegated'] = 'Délégué';
$labels['attendee_status_waiting'] = 'En attente';
$labels['attendee_status_waiting_for_confirmation'] = 'En attente de confirmation';
$labels['attendee_status_info_yes'] = 'Sera présent';
$labels['attendee_status_info_no'] = 'Ne sera pas présent';
$labels['attendee_status_info_maybe'] = 'Pourra peut-être assister';
$labels['attendee_status_info_delegated'] = 'Participation déléguée';
$labels['attendee_role'] = 'Rôle';
$labels['attendee_role_info'] = 'Indiquez si le participant est tenu d\'assister ou de diriger la réunion.';
$labels['attendee_notify'] = 'Notifier';
$labels['attendee_notify_info'] = 'Si cette case est cochée, un email d\'invitation sera envoyé au participant.';
$labels['add_attendee'] = 'Ajouter un participant';
$labels['attendee_notify_question'] = 'Envoyer des emails de notification aux participants ?';
$labels['attendee_notify_textarea'] = 'Ajouter un message optionnel';
$labels['attendee_notify_send'] = 'Envoyer';
$labels['attendee_notify_dont'] = 'Ne pas envoyer';
$labels['attendee_notify_cancel'] = 'Annuler';
$labels['new_attendee_not_saved_warning'] = 'Vous avez saisi l\'adresse e-mail d\'un participant, mais vous ne l\'avez pas ajouté à la liste des participants. Souhaitez-vous enregistrer sans ce participant ?';

// import / export
$labels['import_export'] = 'Importation / Exportation';
$labels['export_to_file'] = 'Exporter vers un fichier';
$labels['export_info'] = 'Cliquez sur ce bouton pour exporter et télécharger les événements de ce calendrier au format iCalendar (ics). Si le calendrier contient beaucoup d\'événements, le processus d\'exportation peut prendre quelques instants.';
$labels['export_no_events'] = 'Ce calendrier n\'a pas d\'événements à exporter.';
$labels['import_from_file'] = 'Importation à partir d\'un fichier';
$labels['import_info'] = 'Cliquez sur ce bouton pour importer des événements dans ce calendrier à partir d\'un fichier iCalendar (ics).';

// email notifications
$labels['appointment_reminder'] = 'Rappel de rendez-vous';
$labels['email_notification_subject'] = 'Rappel: $t à $d ($z)';
$labels['email_notification_cron_error'] = 'Les notifications par courriel sont désactivées. Contactez l\'administrateur.';
$labels['alarm_before_event_starts'] = 'Avant le début de l\'événement';
$labels['alarm_after_event_starts'] = 'Après le début de l\'événement';
$labels['alarm_before_event_ends'] = 'Avant la fin de l\'événement';
$labels['alarm_after_event_ends'] = 'Après la fin de l\'événement';
$labels['alarm_at_specific_time'] = 'À un moment précis';

// search
$labels['specify_at_least_one_calendar'] = 'Spécifiez au moins un calendrier à rechercher.';
$labels['specify_text_to_search'] = 'Tapez le texte à rechercher.';
$labels['clear_search_results'] = 'Clair';
$labels['search_outdated_message'] = 'Les résultats de la recherche peuvent être obsolètes. Pensez à relancer votre recherche.';
$labels['date_range'] = 'Plage de dates';
$labels['date_range_subtitle'] = 'Date d\'entrée en vigueur :';
$labels['search_results'] = 'Résultats trouvés : $n';
$labels['modify_search_options'] = 'Essayez de modifier les options de recherche.';
$labels['show_shortcuts'] = 'Afficher les raccourcis';
$labels['hide_shortcuts'] = 'Masquer les raccourcis';
$labels['this_month'] = 'Ce mois-ci';
$labels['this_year'] = 'Cette année';
$labels['past_30_days'] = '30 derniers jours';
$labels['next_30_days'] = '30 prochains jours';
$labels['past_6_months'] = '6 derniers mois';
$labels['next_6_months'] = 'Les 6 prochains mois';

// calendar grid
$labels['cannot_load_events'] = 'Impossible de charger les événements du calendrier "$c".';
