<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Ημερολόγιο';
$labels['app_menu_title'] = 'Ημερολόγιο';

// menu
$labels['create_event'] = 'Δημιουργία εκδήλωσης';
$labels['create_event_button'] = 'Νέα εκδήλωση';
$labels['day'] = 'Ημέρα';
$labels['week'] = 'Εβδομάδα';
$labels['month'] = 'Μήνας';
$labels['agenda'] = 'Ατζέντα';

// editing calendars
$labels['calendars'] = 'Ημερολόγια';
$labels['my_calendar'] = 'Το ημερολόγιό μου';
$labels['create_calendar'] = 'Δημιουργία ημερολογίου';
$labels['new_calendar'] = 'Νέο ημερολόγιο';
$labels['edit_calendar'] = 'Επεξεργασία ημερολογίου';
$labels['remove_calendar'] = 'Διαγραφή ημερολογίου';
$labels['confirm_remove_calendar'] = 'Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το ημερολόγιο;';
$labels['calendar_removed'] = 'Διαγραφή ημερολογίου.';
$labels['notifications'] = 'Ειδοποιήσεις';
$labels['default_event_visibility'] = 'Προεπιλεγμένη ορατότητα συμβάντος';
$labels['time_zone'] = 'Ζώνη ώρας ημερολογίου';
$labels['share_calendar'] = 'Μοιραστείτε το ημερολόγιο';
$labels['permissions'] = 'Δικαιώματα';
$labels['permission_publish_calendar'] = 'Δημοσίευση ημερολογίου';
$labels['permission_share_calendar'] = 'Μοιραστείτε το ημερολόγιο';
$labels['permission_edit_events'] = 'Επεξεργασία συμβάντων';
$labels['permission_see_details'] = 'Δείτε λεπτομέρειες';
$labels['permission_publish_calendar_info'] = 'Εάν επιλεγεί, ο χρήστης θα μπορεί να δημοσιεύσει το ημερολόγιο.';
$labels['permission_share_calendar_info'] = 'Εάν επιλεγεί, ο χρήστης θα μπορεί να μοιράζεται το ημερολόγιο με άλλους χρήστες.';
$labels['permission_edit_events_info'] = 'Εάν είναι επιλεγμένο, ο χρήστης θα μπορεί να επεξεργάζεται και να διαγράφει συμβάντα ημερολογίου.';
$labels['permission_see_details_info'] = 'Εάν επιλεγεί, θα κοινοποιηθούν όλες οι λεπτομέρειες της εκδήλωσης. Εάν δεν είναι επιλεγμένο, θα κοινοποιηθούν μόνο οι διαθέσιμες/επιφυλακτικές πληροφορίες.';
$labels['share_send_notification'] = 'Ειδοποίηση';
$labels['share_send_notification_info'] = 'Εάν επιλεγεί, θα σταλεί ένα μήνυμα ηλεκτρονικού ταχυδρομείου στον χρήστη που θα τον ενημερώνει για το νέο κοινόχρηστο ημερολόγιο.';
$labels['new_share_not_saved_warning'] = 'Εισάγατε μια διεύθυνση ηλεκτρονικού ταχυδρομείου για να μοιραστείτε το ημερολόγιο, αλλά δεν κάνατε κλικ στο κουμπί Κοινή χρήση για να το προσθέσετε στη λίστα κοινής χρήσης. Θα θέλατε να συνεχίσετε την αποθήκευση χωρίς να μοιραστείτε το ημερολόγιο με αυτόν τον χρήστη;';
$labels['share'] = 'Μοιραστείτε το';
$labels['shared_marker'] = '(Κοινόχρηστο)';
$labels['owner'] = 'Ιδιοκτήτης';
$labels['publish_calendar'] = 'Δημοσίευση ημερολογίου';
$labels['create_link'] = 'Δημιουργία συνδέσμου';
$labels['reset'] = 'Επαναφορά';
$labels['remove'] = 'Αφαιρέστε το';
$labels['publish_full'] = 'Δημοσιεύστε όλες τις πληροφορίες της εκδήλωσης.';
$labels['publish_busy'] = 'Δημοσιεύστε μόνο τις πληροφορίες για τις ελεύθερες/απασχολημένες ώρες.';
$labels['sync_no_id'] = 'Οι επιλογές CalDAV θα γίνουν διαθέσιμες αφού αποθηκεύσετε το ημερολόγιο.';
$labels['share_no_id'] = 'Οι επιλογές κοινής χρήσης ημερολογίου θα γίνουν διαθέσιμες αφού αποθηκεύσετε το ημερολόγιο.';
$labels['publish_no_id'] = 'Οι επιλογές δημοσίευσης ημερολογίου θα γίνουν διαθέσιμες αφού αποθηκεύσετε το ημερολόγιο.';
$labels['import_no_id'] = 'Οι επιλογές εισαγωγής και εξαγωγής θα γίνουν διαθέσιμες αφού αποθηκεύσετε το ημερολόγιο.';
$labels['unable_to_save_calendar_data'] = 'Δεν είναι δυνατή η αποθήκευση δεδομένων ημερολογίου.';
$labels['unable_to_retrieve_calendar_data'] = 'Αδυναμία ανάκτησης δεδομένων ημερολογίου. Το ημερολόγιο ενδέχεται να μην είναι πλέον διαθέσιμο.';
$labels['unable_add_shared_calendar'] = 'Δεν είναι δυνατή η προσθήκη του κοινόχρηστου ημερολογίου. Το ημερολόγιο ενδέχεται να μην είναι πλέον διαθέσιμο.';
$labels['itip_organizer_message'] = 'Προσκληθήκατε σε αυτή την εκδήλωση, αλλά δεν είστε ο διοργανωτής. Οι αλλαγές που κάνετε σε αυτή την εκδήλωση δεν θα αποσταλούν στους άλλους συμμετέχοντες.';
$labels['cannot_share_with_yourself'] = 'Δεν μπορείτε να μοιραστείτε το ημερολόγιό σας με τον εαυτό σας.';
$labels['cannot_share_with_calendar_owner'] = 'Δεν μπορείτε να μοιραστείτε αυτό το ημερολόγιο με τον ιδιοκτήτη του ημερολογίου ($e)';
$labels['setting_default_notification_type'] = 'Προεπιλεγμένες ειδοποιήσεις για νέα συμβάντα';
$labels['calendar_name_cannot_be_empty'] = 'Το όνομα του ημερολογίου δεν μπορεί να είναι κενό.';

// caldav server
$labels['sync_calendar'] = 'Συγχρονισμός ημερολογίου';
$labels['password_too_short'] = 'Ο κωδικός πρόσβασης πρέπει να αποτελείται από τουλάχιστον 10 χαρακτήρες';
$labels['read_only'] = 'Μόνο ανάγνωση';
$labels['add_connection'] = 'Προσθήκη σύνδεσης';
$labels['show_details'] = 'Εμφάνιση λεπτομερειών';
$labels['hide_details'] = 'Απόκρυψη λεπτομερειών';
$labels['caldav_connection_password'] = 'Ο κωδικός πρόσβασης που καθορίσατε κατά τη δημιουργία αυτής της σύνδεσης.';
$labels['yes'] = 'Ναι';
$labels['no'] = 'Όχι';
$labels['confirm_remove_sync'] = 'Είστε σίγουροι ότι θέλετε να καταργήσετε οριστικά αυτή τη σύνδεση;';
$labels['remove_sync_error'] = 'Υπήρξε σφάλμα κατά την κατάργηση της σύνδεσης.';
$labels['create_new_caldav_connection'] = 'Δημιουργία νέας σύνδεσης CalDav';
$labels['your_caldav_connections'] = 'Οι συνδέσεις σας CalDav';
$labels['created_at'] = 'Δημιουργήθηκε';
$labels['connected_at'] = 'Τελευταίος συγχρονισμός';
$labels['never'] = 'Ποτέ';
$labels['alternate_url'] = 'Εναλλακτική διεύθυνση URL';
$labels['caldav_password_info'] = 'Για λόγους ασφαλείας ο κωδικός αυτός δεν αποθηκεύεται στον διακομιστή. Αν τον ξεχάσατε, διαγράψτε αυτή τη σύνδεση και δημιουργήστε μια νέα.';
$labels['caldav_url_info'] = 'Χρησιμοποιήστε αυτή τη διεύθυνση URL με εφαρμογές που υποστηρίζουν την ανακάλυψη ημερολογίου, για παράδειγμα: iOS και Mac OS Calendar, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Χρησιμοποιήστε αυτή τη διεύθυνση URL για εφαρμογές που δεν λειτουργούν με την παραπάνω σύντομη διεύθυνση URL, για παράδειγμα: Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'Η διεύθυνση URL του διακομιστή CalDAV δεν είναι έγκυρη.';
$labels['cannot_resolve_caldav_host'] = 'Δεν είναι δυνατή η ανίχνευση του διακομιστή CalDAV.';
$labels['private_caldav_addresses_not_allowed'] = 'Δεν επιτρέπονται οι ιδιωτικές και οι αποκλειστικές διευθύνσεις CalDAV.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Προσθήκη κοινόχρηστου ημερολογίου';
$labels['shared_calendars'] = 'Κοινόχρηστα ημερολόγια';
$labels['no_shared_calendars'] = 'Δεν υπάρχουν κοινά ημερολόγια που μπορείτε να προσθέσετε.';
$labels['add_to_my_calendars'] = 'Προσθήκη στα ημερολόγιά μου';
$labels['remove_from_my_calendars'] = 'Διαγραφή από τα ημερολόγιά μου';
$labels['unshare'] = 'Απελευθέρωση κοινοποίησης';
$labels['unshare_warning'] = 'Εάν καταργήσετε την κοινή χρήση αυτού του ημερολογίου, θα εξαφανιστεί από τη λίστα και δεν θα μπορείτε να το προσθέσετε ξανά.';
$labels['calendar_name'] = 'Όνομα ημερολογίου';
$labels['calendar_owner'] = 'Ιδιοκτήτης ημερολογίου';
$labels['shared_date'] = 'Κοινόχρηστο';
$labels['unable_to_share'] = 'Το ημερολόγιο δεν μπορεί να διαμοιραστεί με τους ακόλουθους χρήστες επειδή οι λογαριασμοί ηλεκτρονικού ταχυδρομείου τους βρίσκονται σε απομακρυσμένο διακομιστή:';

// shared calendar email
$labels['shared_email_subject'] = '$u έχει μοιραστεί μαζί σας ένα ημερολόγιο';
$labels['shared_email_body'] = 'Ο χρήστης $u σας έχει δώσει πρόσβαση σε ένα ημερολόγιο που ονομάζεται "$c". Για να προσθέσετε αυτό το ημερολόγιο στη λίστα των ενεργών ημερολογίων σας, κάντε κλικ στον παρακάτω σύνδεσμο:';
$labels['shared_email_body_cpanel'] = 'Ο χρήστης $u σας έχει δώσει πρόσβαση σε ένα ημερολόγιο που ονομάζεται "$c". Για να προσθέσετε αυτό το ημερολόγιο στη λίστα των ενεργών ημερολογίων σας, συνδεθείτε στο Roundcube, μεταβείτε στο Ημερολόγιο, κάντε κλικ στο εικονίδιο συν στην πλαϊνή γραμμή και επιλέξτε Προσθήκη κοινόχρηστου ημερολογίου.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Ημερολόγια διακοπών';
$labels['edit_holiday_calendar'] = 'Επεξεργασία ημερολογίου διακοπών';
$labels['religious_holidays'] = 'Θρησκευτικές αργίες';
$labels['country_holidays'] = 'Διακοπές στην εξοχή';
$labels['add'] = 'Προσθέστε';
$labels['no_more_holidays_allowed'] = 'Δεν μπορείτε να προσθέσετε άλλα ημερολόγια διακοπών.';
$labels['cannot_load_holidays'] = 'Δεν είναι δυνατή η φόρτωση διακοπών από τον διακομιστή της Google.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Προσθέστε ημερολόγιο γενεθλίων';
$labels['edit_birthday_calendar'] = 'Επεξεργασία ημερολογίου γενεθλίων';
$labels['contact_birthdays'] = 'Επικοινωνία Γενέθλια';
$labels['contact_birthdays_info'] = 'Αυτό το ημερολόγιο ανακτά πληροφορίες γενεθλίων από τη λίστα επαφών σας και τις εμφανίζει ως συμβάντα ημερολογίου.';
$labels['birthday_event_title'] = 'Γενέθλια: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Προσθήκη ημερολογίου Google';
$labels['edit_google_calendar'] = 'Επεξεργασία ημερολογίου Google';
$labels['google_id'] = 'Αναγνωριστικό ημερολογίου Google';
$labels['new_google_calendar'] = 'Νέο ημερολόγιο Google';
$labels['specify_google_calendar_id'] = 'Πληκτρολογήστε το αναγνωριστικό του ημερολογίου Google';
$labels['calendar_id_format_error'] = 'Το αναγνωριστικό του ημερολογίου Google θα πρέπει να περιλαμβάνει τον χαρακτήρα @.';
$labels['cannot_load_google_calendar'] = 'Το ημερολόγιο Google "$c" δεν μπορεί να φορτωθεί. Βεβαιωθείτε ότι το αναγνωριστικό του ημερολογίου είναι σωστό και ότι το ημερολόγιο έχει γίνει διαθέσιμο στο κοινό.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Προσθήκη ημερολογίου CalDAV';
$labels['edit_caldav_calendar'] = 'Επεξεργασία ημερολογίου CalDAV';
$labels['add_caldav_calendar_information'] = 'Για να εγγραφείτε σε ένα ημερολόγιο CalDAV, καθορίστε τις πληροφορίες του διακομιστή CalDAV και κάντε κλικ στην επιλογή Εύρεση ημερολογίων.';
$labels['caldav_url'] = 'Διεύθυνση URL CalDAV';
$labels['find_calendars'] = 'Βρείτε ημερολόγια';
$labels['searching_for_calendars'] = 'Αναζήτηση ημερολογίων, παρακαλώ περιμένετε...';
$labels['select_calendars'] = 'Επιλέξτε ημερολόγια προς προσθήκη:';
$labels['read_only_status_yes'] = 'Ναι';
$labels['read_only_status_no'] = 'Όχι';
$labels['subscribed'] = 'Συνδρομημένος';

$labels['refresh_calendar'] = 'Ανανέωση ημερολογίου';
$labels['refresh_every_minute'] = 'Κάθε λεπτό';
$labels['refresh_every_5_minutes'] = 'Κάθε 5 λεπτά';
$labels['refresh_every_15_minutes'] = 'Κάθε 15 λεπτά';
$labels['refresh_every_30_minutes'] = 'Κάθε 30 λεπτά';
$labels['refresh_every_60_minutes'] = 'Κάθε 60 λεπτά';
$labels['refresh_never'] = 'Ποτέ';

$labels['cannot_connect_to_caldav_server'] = 'Δεν είναι δυνατή η σύνδεση στον διακομιστή CalDAV "$n".';
$labels['caldav_client_error_url'] = 'Δεν είναι δυνατή η σύνδεση στο διακομιστή CalDAV. Ελέγξτε τη διεύθυνση URL του διακομιστή.';
$labels['caldav_client_error_credentials'] = 'Λανθασμένο όνομα χρήστη ή κωδικός πρόσβασης CalDAV.';
$labels['caldav_client_error_empty'] = 'Ο διακομιστής δεν επέστρεψε κανένα ημερολόγιο που μπορεί να προστεθεί. Ελέγξτε τη διεύθυνση URL CalDAV.';
$labels['caldav_client_error_server_info'] = 'Λανθασμένες πληροφορίες διακομιστή CalDAV.';
$labels['caldav_client_error_add_calendar'] = 'Δεν είναι δυνατή η αποθήκευση του ημερολογίου "$n".';
$labels['caldav_client_error_delete_event'] = 'Δεν είναι δυνατή η διαγραφή συμβάντος';
$labels['caldav_client_error_save_event'] = 'Δεν είναι δυνατή η αποθήκευση της εκδήλωσης CalDAV: ενδέχεται να έχει μετακινηθεί, να έχει διαγραφεί ή το ημερολόγιο να μην είναι διαθέσιμο.';
$labels['caldav_client_error_calendar_added'] = 'Το ημερολόγιο "$n" βρίσκεται ήδη στη λίστα ημερολογίων σας.';

// previewing event
$labels['remove_recurring_event'] = 'Διαγραφή επαναλαμβανόμενου συμβάντος';
$labels['remove_one'] = 'Διαγραφή μόνο αυτής της περίπτωσης';
$labels['remove_all'] = 'Διαγραφή όλων των περιπτώσεων';
$labels['edit_event'] = 'Επεξεργασία συμβάντος';
$labels['event_removed'] = 'Το γεγονός διαγράφηκε.';
$labels['go_to_url'] = 'Πηγαίνετε στο URL';
$labels['confirm_remove_event'] = 'Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το συμβάν;';

// calendar/agenda grid
$labels['no_events'] = 'Δεν υπάρχουν γεγονότα προς εμφάνιση.';

// importing events
$labels['select_calendar'] = 'Επιλέξτε ημερολόγιο';
$labels['select_calendar_for_events'] = 'Επιλέξτε το ημερολόγιο στο οποίο θέλετε να προσθέσετε τα συμβάντα:';
$labels['add_events'] = 'Προσθήκη συμβάντων';
$labels['add_events_to_calendar'] = 'Προσθήκη εκδηλώσεων στο ημερολόγιο';
$labels['events_imported_successfully'] = 'Γεγονότα που εισήχθησαν με επιτυχία';
$labels['events_imported_with_errors'] = 'Δεν ήταν δυνατή η εισαγωγή κάποιου άλλου συμβάντος.';
$labels['error_importing_events'] = 'Υπήρξε σφάλμα κατά την εισαγωγή γεγονότων στο ημερολόγιο';

// editing events
$labels['calendar'] = 'Ημερολόγιο';
$labels['new_event'] = 'Νέα εκδήλωση';
$labels['summary'] = 'Περίληψη';
$labels['recurrence'] = 'Υποτροπή';
$labels['title'] = 'Τίτλος';
$labels['location'] = 'Τοποθεσία';
$labels['description'] = 'Περιγραφή';
$labels['comment'] = 'Σχόλιο';
$labels['url'] = 'URL';
$labels['name'] = 'Όνομα';
$labels['start'] = 'Έναρξη';
$labels['end'] = 'Τέλος';
$labels['show_timezones'] = 'Εμφάνιση ζωνών ώρας';
$labels['reset_timezones'] = 'Επαναφορά χρονοζωνών στην προεπιλογή';
$labels['all_day'] = 'Όλη την ημέρα';
$labels['use_calendar_colors'] = 'Χρήση χρωμάτων ημερολογίου';
$labels['color'] = 'Χρώμα';
$labels['background'] = 'Ιστορικό';
$labels['text'] = 'Κείμενο';
$labels['show_me_as'] = 'Δείξε μου ως';
$labels['available'] = 'Διαθέσιμο';
$labels['busy'] = 'Απασχολημένος';
$labels['visibility'] = 'Ορατότητα';
$labels['calendar_default'] = 'Προεπιλογή ημερολογίου';
$labels['public'] = 'Δημόσιο';
$labels['private'] = 'Ιδιωτικό';
$labels['repeat'] = 'Επανάληψη';
$labels['repeat_every'] = 'Επαναλάβετε κάθε';
$labels['never'] = 'Ποτέ';
$labels['daily'] = 'Καθημερινά';
$labels['weekly'] = 'Εβδομαδιαία';
$labels['monthly'] = 'Μηνιαία';
$labels['yearly'] = 'Ετήσια';
$labels['days'] = 'ημέρα(ες)';
$labels['weeks'] = 'εβδομάδα(ες)';
$labels['months'] = 'μήνας(ες)';
$labels['years'] = 'έτος(α)';
$labels['repeat_on'] = 'Επαναλάβετε στο';
$labels['repeat_by'] = 'Επαναλάβετε από';
$labels['day_of_month'] = 'Ημέρα του μήνα';
$labels['day_of_week'] = 'Ημέρα της εβδομάδας';
$labels['not_specified'] = 'Δεν προσδιορίζεται';
$labels['first'] = 'Πρώτη';
$labels['second'] = 'Δεύτερο';
$labels['third'] = 'Τρίτο';
$labels['fourth'] = 'Τέταρτο';
$labels['fifth'] = 'Πέμπτο';
$labels['second_to_last'] = 'Προτελευταία';
$labels['last'] = 'Τελευταία';
$labels['attachment_too_large'] = 'Το συνημμένο αρχείο είναι πολύ μεγάλο. Το μέγιστο επιτρεπόμενο μέγεθος είναι $s.';
$labels['bg_color'] = 'Χρώμα φόντου';
$labels['tx_color'] = 'Χρώμα κειμένου';
$labels['add_notification'] = 'Προσθήκη ειδοποίησης';
$labels['popup'] = 'Αναδυόμενο παράθυρο';
$labels['no_notifications'] = 'Αυτό το συμβάν δεν έχει ειδοποιήσεις.';
$labels['minutes'] = 'λεπτά';
$labels['hours'] = 'ώρες';
$labels['link'] = 'Σύνδεσμος';
$labels['attach_file_from_web'] = 'Επισύναψη αρχείου';
$labels['attachment_url'] = 'URL επισύναψης';
$labels['invalid_url'] = 'Αυτή η διεύθυνση URL είναι άκυρη.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = 'Αντίγραψε';
$labels['duplicate_suffix'] = '(αντίγραφο)';
$labels['no_notifications'] = 'Αυτό το συμβάν δεν έχει ειδοποιήσεις.';
$labels['this_calendar_is_hidden'] = 'Αυτό το ημερολόγιο είναι κρυφό. Τα αποθηκευμένα συμβάντα δεν θα εμφανίζονται έως ότου το ημερολόγιο γίνει ορατό.';
$labels['make_calendar_visible'] = 'Κάντε αυτό το ημερολόγιο ορατό.';

// recurrence
$labels['recurrence_range'] = 'Εύρος υποτροπής';
$labels['repeat_forever'] = 'Επαναλάβετε για πάντα';
$labels['repeat_every_weekday'] = 'Επαναλάβετε κάθε εργάσιμη ημέρα';
$labels['last_day'] = 'Τελευταία ημέρα';
$labels['every'] = 'Κάθε';
$labels['weekday'] = 'Ημέρα της εβδομάδας';
$labels['weekend_day'] = 'Ημέρα Σαββατοκύριακου';
$labels['day_of_week'] = 'Ημέρα της εβδομάδας';
$labels['day_of_month'] = 'Ημέρα του μήνα';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Τέλος μετά';
$labels['occurrences'] = 'περιστατικό(α)';

// end recurrence on date:
$labels['end_by_date'] = 'Τέλος από ημερομηνία';
$labels['removed_events_title'] = 'Γεγονότα που αφαιρούνται από επαναλαμβανόμενες σειρές';
$labels['restore'] = 'Επαναφορά';

// settings page
$labels['setting_view'] = 'Αρχική προβολή ημερολογίου';
$labels['setting_first_day'] = 'Η εβδομάδα αρχίζει στις';
$labels['setting_agenda_week_span'] = 'Χρονική διάρκεια της ημερήσιας διάταξης (εβδομάδες)';
$labels['setting_slot_duration'] = 'Ημερήσια χρονοθυρίδα (λεπτά)';
$labels['setting_scroll_time'] = 'Μετακινηθείτε στο χρόνο';
$labels['setting_calendar'] = 'Προεπιλεγμένο ημερολόγιο για εκδηλώσεις';
$labels['setting_refresh'] = 'Ανανέωση ημερολογίων';
$labels['setting_week_numbers'] = 'Αριθμοί εβδομάδας σε μικρό ημερολόγιο';
$labels['setting_event_border'] = 'Χρώματα κατηγορίας ως σύνορα συμβάντων';
$labels['show_sunrise'] = 'Εμφάνιση της ανατολής σε προβολή μήνα';
$labels['show_sunset'] = 'Εμφάνιση ηλιοβασιλέματος σε προβολή μήνα';
$labels['categories'] = 'Κατηγορίες';
$labels['category'] = 'Κατηγορία';
$labels['no_category'] = 'Καμία κατηγορία';
$labels['add_category'] = 'Προσθήκη κατηγορίας';
$labels['setting_alarm_sound'] = 'Ήχος ειδοποίησης';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Μπαμπού';
$labels['alarm_sound_bells'] = 'Καμπάνες';
$labels['alarm_sound_chord'] = 'Χορδή';
$labels['alarm_sound_circles'] = 'Κύκλοι';
$labels['alarm_sound_glass'] = 'Γυαλί';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Μέταλλο';
$labels['alarm_sound_ripple'] = 'Κυματισμός';
$labels['alarm_sound_startrek'] = 'Star Trek';
$labels['alarm_sound_whistle'] = 'Σφυρίχτρα';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Αναπαραγωγή ήχου';

// alarms
$labels['upcoming_events'] = 'Επόμενες εκδηλώσεις';
$labels['dismiss'] = 'Απορρίψτε το';
$labels['dismiss_all'] = 'Απορρίψτε όλους';
$labels['snooze'] = 'Snooze';
$labels['5_minutes'] = '5 λεπτά';
$labels['10_minutes'] = '10 λεπτά';
$labels['20_minutes'] = '20 λεπτά';
$labels['30_minutes'] = '30 λεπτά';
$labels['1_hour'] = '1 ώρα';
$labels['2_hours'] = '2 ώρες';
$labels['tomorrow'] = 'Μέχρι αύριο';
$labels['next_week'] = 'Μέχρι την επόμενη εβδομάδα';

// sidebar
$labels['setting_show_xcalendar'] = 'Εμφάνιση ημερολογίου Ατζέντα';
$labels['todays_agenda'] = 'Η σημερινή ατζέντα';
$labels['no_events_today'] = 'Δεν έχουν προγραμματιστεί εκδηλώσεις για σήμερα.';
$labels['sidebar_more'] = 'Περισσότερα...';

// event invitation
$labels['itip_title_request'] = 'Είστε προσκεκλημένοι σε μια εκδήλωση.';
$labels['itip_title_modified'] = 'Αυτή η εκδήλωση έχει τροποποιηθεί.';
$labels['itip_title_reply_accepted'] = '$u έχει αποδεχτεί την πρόσκλησή σας';
$labels['itip_title_reply_tentative'] = '$u έχει αποδεχτεί προσωρινά την πρόσκλησή σας';
$labels['itip_title_reply_declined'] = '$u απέρριψε την πρόσκλησή σας';
$labels['itip_title_cancel'] = 'Αυτή η εκδήλωση έχει ακυρωθεί.';

$labels['itip_email_subject_request'] = 'Πρόσκληση:';
$labels['itip_email_subject_request_modified'] = 'Το γεγονός τροποποιήθηκε:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'Ακυρώθηκε:';

$labels['itip_popup_event_added'] = 'Η εκδήλωση προστέθηκε στο ημερολόγιο.';
$labels['itip_popup_event_updated'] = 'Η εκδήλωση ενημερώθηκε στο ημερολόγιο.';
$labels['itip_popup_event_deleted'] = 'Διαγραφή συμβάντος από το ημερολόγιο.';
$labels['itip_popup_response_sent'] = 'Η απάντηση αποστέλλεται στον διοργανωτή.';

$labels['itip_message_invitation_accepted'] = 'Αποδεχθήκατε αυτή την πρόσκληση.';
$labels['itip_message_invitation_tentative'] = 'Έχετε αποδεχθεί προσωρινά αυτή την πρόσκληση.';
$labels['itip_message_invitation_declined'] = 'Απορρίψατε αυτή την πρόσκληση.';
$labels['itip_message_event_deleted'] = 'Η εκδήλωση έχει διαγραφεί από το ημερολόγιό σας.';
$labels['itip_message_event_updated'] = 'Η εκδήλωση στο ημερολόγιό σας έχει ενημερωθεί.';
$labels['itip_message_event_missing'] = 'Αυτή η εκδήλωση δεν υπάρχει σε κανένα από τα ημερολόγιά σας.';

$labels['itip_accept'] = 'Αποδοχή';
$labels['itip_tentative'] = 'Προσωρινό';
$labels['itip_decline'] = 'Πτώση';
$labels['itip_change_reponse'] = 'Αλλαγή απάντησης.';
$labels['itip_add_to_calendar'] = 'Προσθήκη στο ημερολόγιο';
$labels['itip_reply_to_sender'] = 'Απάντηση στον αποστολέα';
$labels['itip_dont_import'] = 'Μην προσθέτετε στο ημερολόγιο';
$labels['itip_update_in_calendar'] = 'Ενημέρωση συμβάντος στο ημερολόγιο';
$labels['itip_delete_from_calendar'] = 'Διαγραφή συμβάντος από το ημερολόγιο';

$labels['mail_subject_invitation'] = 'Πρόσκληση:';
$labels['mail_subject_update'] = 'Ενημερωμένη πρόσκληση:';
$labels['mail_subject_cancel'] = 'Η εκδήλωση ακυρώθηκε:';

// event email notifications
$labels['mail_title'] = 'Τίτλος:';
$labels['mail_start'] = 'Ξεκινήστε:';
$labels['mail_end'] = 'Τέλος:';
$labels['mail_description'] = 'Περιγραφή:';
$labels['mail_location'] = 'Τοποθεσία:';

// event attendees
$labels['attendees'] = 'Συμμετέχοντες';
$labels['attendee_role'] = 'Ρόλος';
$labels['attendee_role_required'] = 'Απαιτούμενο';
$labels['attendee_role_optional'] = 'Προαιρετικό';
$labels['attendee_role_chair'] = 'Καρέκλα';
$labels['attendee_role_none'] = 'Μη συμμετέχων';
$labels['organizer'] = 'Διοργανωτής';
$labels['attendance'] = 'Συμμετοχή';
$labels['your_status'] = 'Η κατάστασή σας';
$labels['attendance_going'] = 'Φεύγεις;';
$labels['attendee_status_yes'] = 'Ναι';
$labels['attendee_status_no'] = 'Όχι';
$labels['attendee_status_maybe'] = 'Ίσως';
$labels['attendee_status_delegated'] = 'Εντεταλμένη';
$labels['attendee_status_waiting'] = 'Αναμονή';
$labels['attendee_status_waiting_for_confirmation'] = 'Αναμονή για επιβεβαίωση';
$labels['attendee_status_info_yes'] = 'Θα παραστεί';
$labels['attendee_status_info_no'] = 'Δεν θα παραστεί';
$labels['attendee_status_info_maybe'] = 'Μπορεί να παρευρεθεί';
$labels['attendee_status_info_delegated'] = 'Εντεταλμένη παρουσία';
$labels['attendee_role'] = 'Ρόλος';
$labels['attendee_role_info'] = 'Προσδιορίστε αν ο συμμετέχων υποχρεούται να παραστεί ή να προεδρεύσει της συνεδρίασης.';
$labels['attendee_notify'] = 'Ειδοποίηση';
$labels['attendee_notify_info'] = 'Εάν επιλεγεί, θα σταλεί email πρόσκλησης στον συμμετέχοντα.';
$labels['add_attendee'] = 'Προσθήκη συμμετέχοντα';
$labels['attendee_notify_question'] = 'Αποστολή μηνυμάτων ηλεκτρονικού ταχυδρομείου ειδοποίησης στους συμμετέχοντες;';
$labels['attendee_notify_textarea'] = 'Προσθέστε ένα προαιρετικό μήνυμα';
$labels['attendee_notify_send'] = 'Στείλτε';
$labels['attendee_notify_dont'] = 'Μην στέλνετε';
$labels['attendee_notify_cancel'] = 'Ακύρωση';
$labels['new_attendee_not_saved_warning'] = 'Εισαγάγατε τη διεύθυνση email ενός συμμετέχοντα, αλλά δεν τον προσθέσατε στη λίστα των συμμετεχόντων. Θέλετε να συνεχίσετε την αποθήκευση χωρίς αυτόν τον συμμετέχοντα;';

// import / export
$labels['import_export'] = 'Εισαγωγή / Εξαγωγή';
$labels['export_to_file'] = 'Εξαγωγή σε αρχείο';
$labels['export_info'] = 'Κάντε κλικ σε αυτό το κουμπί για να εξαγάγετε και να κατεβάσετε τα συμβάντα αυτού του ημερολογίου σε μορφή iCalendar (ics). Εάν το ημερολόγιο περιέχει πολλά συμβάντα, η διαδικασία εξαγωγής ενδέχεται να διαρκέσει λίγα λεπτά.';
$labels['export_no_events'] = 'Αυτό το ημερολόγιο δεν έχει συμβάντα προς εξαγωγή.';
$labels['import_from_file'] = 'Εισαγωγή από αρχείο';
$labels['import_info'] = 'Κάντε κλικ σε αυτό το κουμπί για να εισαγάγετε συμβάντα σε αυτό το ημερολόγιο από ένα αρχείο iCalendar (ics).';

// email notifications
$labels['appointment_reminder'] = 'Υπενθύμιση ραντεβού';
$labels['email_notification_subject'] = 'Υπενθύμιση: $t σε $d ($z)';
$labels['email_notification_cron_error'] = 'Οι ειδοποιήσεις μέσω ηλεκτρονικού ταχυδρομείου είναι απενεργοποιημένες. Επικοινωνήστε με τον διαχειριστή.';
$labels['alarm_before_event_starts'] = 'Πριν από την έναρξη της εκδήλωσης';
$labels['alarm_after_event_starts'] = 'Μετά την έναρξη της εκδήλωσης';
$labels['alarm_before_event_ends'] = 'Πριν από το τέλος της εκδήλωσης';
$labels['alarm_after_event_ends'] = 'Μετά το τέλος της εκδήλωσης';
$labels['alarm_at_specific_time'] = 'Σε συγκεκριμένη χρονική στιγμή';

// search
$labels['specify_at_least_one_calendar'] = 'Καθορίστε τουλάχιστον ένα ημερολόγιο για αναζήτηση.';
$labels['specify_text_to_search'] = 'Πληκτρολογήστε το κείμενο για αναζήτηση.';
$labels['clear_search_results'] = 'Σαφής';
$labels['search_outdated_message'] = 'Τα αποτελέσματα αναζήτησης ενδέχεται να είναι ξεπερασμένα. Εξετάστε το ενδεχόμενο να εκτελέσετε εκ νέου την αναζήτησή σας.';
$labels['date_range'] = 'Εύρος ημερομηνίας';
$labels['date_range_subtitle'] = 'Εύρος ημερομηνιών:';
$labels['search_results'] = 'Βρέθηκαν αποτελέσματα: $n';
$labels['modify_search_options'] = 'Δοκιμάστε να τροποποιήσετε τις επιλογές αναζήτησης.';
$labels['show_shortcuts'] = 'Εμφάνιση συντομεύσεων';
$labels['hide_shortcuts'] = 'Απόκρυψη συντομεύσεων';
$labels['this_month'] = 'Αυτόν τον μήνα';
$labels['this_year'] = 'Φέτος';
$labels['past_30_days'] = 'Προηγούμενες 30 ημέρες';
$labels['next_30_days'] = 'Επόμενες 30 ημέρες';
$labels['past_6_months'] = 'Παρελθόντες 6 μήνες';
$labels['next_6_months'] = 'Επόμενοι 6 μήνες';

// calendar grid
$labels['cannot_load_events'] = 'Δεν είναι δυνατή η φόρτωση συμβάντων για το ημερολόγιο "$c".';
