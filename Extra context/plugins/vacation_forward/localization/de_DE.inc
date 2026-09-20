<?php

$labels = [];
$labels['vacation_forward_title'] = 'Abwesenheit & Weiterleitung';
$labels['vacation_title'] = 'Abwesenheit / Automatische Antwort';
$labels['forward_title'] = 'E-Mail-Weiterleitung';
$labels['tab_vacation'] = 'Abwesenheit & Auto-Antwort';
$labels['tab_templates'] = 'Sprachvorlagen';
$labels['tab_forward'] = 'Weiterleitungsregeln';
$labels['tab_exclusions'] = 'Schleifenschutz & Filter';
$labels['tab_logs'] = 'Aktivitätsprotokoll';

$labels['vacation_status'] = 'Status Auto-Antwort';
$labels['status_disabled'] = 'Deaktiviert';
$labels['status_enabled'] = 'Aktiviert (Immer aktiv)';
$labels['status_scheduled'] = 'Geplant (Abwesenheitszeitraum)';
$labels['status_active_badge'] = 'Jetzt aktiv';
$labels['status_scheduled_badge'] = 'Geplant';
$labels['status_expired_badge'] = 'Abgelaufen';

$labels['vacation_start'] = 'Startdatum & -uhrzeit';
$labels['vacation_end'] = 'Enddatum & -uhrzeit';
$labels['vacation_auto_disable'] = 'Nach Ablauf automatisch deaktivieren';
$labels['preset_weekend'] = 'Dieses Wochenende';
$labels['preset_next_week'] = 'Nächste Woche';
$labels['preset_two_weeks'] = 'Nächste 2 Wochen';

$labels['rate_limit_interval'] = 'Antwortintervall (Drosselung pro Absender)';
$labels['interval_1_hour'] = 'Jede Stunde';
$labels['interval_6_hours'] = 'Alle 6 Stunden';
$labels['interval_12_hours'] = 'Alle 12 Stunden';
$labels['interval_1_day'] = 'Alle 24 Stunden (1 Tag)';
$labels['interval_2_days'] = 'Alle 2 Tage';
$labels['interval_3_days'] = 'Alle 3 Tage';
$labels['interval_7_days'] = 'Alle 7 Tage';
$labels['interval_14_days'] = 'Alle 14 Tage';
$labels['interval_unlimited'] = 'Immer antworten (Keine Drosselung)';

$labels['default_subject'] = 'Standard-Betreff';
$labels['default_body'] = 'Standard-Nachricht';

$labels['forward_status'] = 'Weiterleitungsstatus';
$labels['forward_destinations'] = 'Weiterleiten an (E-Mail-Adressen)';
$labels['forward_destinations_help'] = 'Geben Sie eine oder mehrere E-Mail-Adressen ein (durch Komma oder Zeilenumbruch getrennt).';
$labels['forward_keep_copy'] = 'Kopie im Posteingang behalten';
$labels['forward_mode'] = 'Weiterleitungsmodus';
$labels['forward_mode_redirect'] = 'Umleiten (Behält ursprünglichen Absender bei)';
$labels['forward_mode_inline'] = 'Mit Vorspann weiterleiten';
$labels['forward_condition'] = 'Weiterleitungsfilter';
$labels['forward_all'] = 'Alle eingehenden E-Mails weiterleiten';
$labels['forward_matching_only'] = 'Nur bei Übereinstimmung weiterleiten';
$labels['forward_keywords'] = 'Schlüsselwörter im Betreff (kommagetrennt)';

$labels['templates_title'] = 'Mehrsprachige Antwortvorlagen';
$labels['templates_desc'] = 'Erstellen Sie individuelle Abwesenheitsnachrichten für verschiedene Sprachen oder Domains.';
$labels['btn_add_template'] = 'Vorlage hinzufügen';
$labels['btn_edit'] = 'Bearbeiten';
$labels['btn_delete'] = 'Löschen';
$labels['btn_preview'] = 'Testen / Vorschau';
$labels['template_name'] = 'Vorlagenname';
$labels['template_lang'] = 'Sprache';
$labels['template_is_default'] = 'Standard-Fallback-Vorlage';
$labels['template_domain_rule'] = 'Domain- oder Adressfilter (optional)';
$labels['template_subject'] = 'Betreffvorlage';
$labels['template_body'] = 'Nachrichtentext';
$labels['placeholders_legend'] = 'Verfügbare Platzhalter';
$labels['placeholder_start_date'] = 'Startdatum';
$labels['placeholder_end_date'] = 'Enddatum';
$labels['placeholder_return_date'] = 'Rückkehrdatum';
$labels['placeholder_sender_name'] = 'Name des Absenders';
$labels['placeholder_sender_email'] = 'E-Mail des Absenders';
$labels['placeholder_original_subject'] = 'Ursprünglicher Betreff';
$labels['placeholder_user_name'] = 'Ihr Anzeigename';
$labels['placeholder_user_email'] = 'Ihre E-Mail-Adresse';

$labels['anti_loop_title'] = 'Schleifenschutz & Intelligente Unterdrückung';
$labels['ignore_auto_submitted'] = 'Automatische Nachrichten und Mailinglisten ignorieren (Auto-Submitted, List-Id)';
$labels['ignore_daemons'] = 'Bounces und System-Daemons ignorieren (mailer-daemon, postmaster, noreply)';
$labels['sender_blacklist'] = 'Absender-Sperrliste (E-Mails oder @Domains, eine pro Zeile)';
$labels['subject_prefix'] = 'Betreff-Präfix';
$labels['max_replies_daily'] = 'Max. tägliche Antworten (Globales Limit)';

$labels['col_timestamp'] = 'Datum & Uhrzeit';
$labels['col_sender'] = 'Absender';
$labels['col_recipient'] = 'Empfänger / Ziel';
$labels['col_action'] = 'Aktion';
$labels['col_template'] = 'Vorlage';
$labels['col_status'] = 'Status';
$labels['action_auto_reply'] = 'Automatisch geantwortet';
$labels['action_forwarded'] = 'Weitergeleitet';
$labels['action_suppressed'] = 'Unterdrückt (Limit)';
$labels['no_logs_found'] = 'Noch keine Abwesenheits- oder Weiterleitungsaktivität.';
$labels['btn_clear_logs'] = 'Protokoll leeren';
$labels['btn_export_sieve'] = 'Sieve-Skript exportieren';
$labels['saved_success'] = 'Einstellungen erfolgreich gespeichert.';
$labels['cleared_logs_success'] = 'Aktivitätsprotokoll erfolgreich geleert.';
$labels['simulate_title'] = 'Vorlagensimulation';
$labels['simulate_sender'] = 'Test-Absender';
$labels['simulate_subject'] = 'Test-Betreff';
$labels['simulate_body'] = 'Test-Nachricht';
$labels['simulate_run'] = 'Simulation starten';
$labels['simulate_matched'] = 'Gewählte Vorlage';
$labels['simulate_detected_lang'] = 'Erkannte Sprache';
