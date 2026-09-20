<?php

$labels = [];
$labels['vacation_forward_title'] = 'Afwezigheid & Doorsturen';
$labels['vacation_title'] = 'Afwezigheid / Automatisch antwoord';
$labels['forward_title'] = 'E-mail doorsturen';
$labels['tab_vacation'] = 'Afwezigheid & Auto-antwoord';
$labels['tab_templates'] = 'Taalsjablonen';
$labels['tab_forward'] = 'Doorstuurregels';
$labels['tab_exclusions'] = 'Anti-lus & Filters';
$labels['tab_logs'] = 'Activiteitenlog';

$labels['vacation_status'] = 'Status automatisch antwoord';
$labels['status_disabled'] = 'Uitgeschakeld';
$labels['status_enabled'] = 'Ingeschakeld (Altijd actief)';
$labels['status_scheduled'] = 'Gepland (Vakantieperiode)';
$labels['status_active_badge'] = 'Nu actief';
$labels['status_scheduled_badge'] = 'Gepland';
$labels['status_expired_badge'] = 'Verlopen';

$labels['vacation_start'] = 'Begindatum & -tijd';
$labels['vacation_end'] = 'Einddatum & -tijd';
$labels['vacation_auto_disable'] = 'Automatisch uitschakelen na afloop van periode';
$labels['preset_weekend'] = 'Dit weekend';
$labels['preset_next_week'] = 'Volgende week';
$labels['preset_two_weeks'] = 'Komende 2 weken';

$labels['rate_limit_interval'] = 'Antwoordinterval (Limiet per afzender)';
$labels['interval_1_hour'] = 'Elk uur';
$labels['interval_6_hours'] = 'Elke 6 uur';
$labels['interval_12_hours'] = 'Elke 12 uur';
$labels['interval_1_day'] = 'Elke 24 uur (1 dag)';
$labels['interval_2_days'] = 'Elke 2 dagen';
$labels['interval_3_days'] = 'Elke 3 dagen';
$labels['interval_7_days'] = 'Elke 7 dagen';
$labels['interval_14_days'] = 'Elke 14 dagen';
$labels['interval_unlimited'] = 'Altijd antwoorden (Geen limiet)';

$labels['default_subject'] = 'Standaardonderwerp';
$labels['default_body'] = 'Standaardbericht';

$labels['forward_status'] = 'Status doorsturen';
$labels['forward_destinations'] = 'Doorsturen naar (E-mailadressen)';
$labels['forward_destinations_help'] = 'Voer een of meerdere e-mailadressen in, gescheiden door komma of nieuwe regel.';
$labels['forward_keep_copy'] = 'Kopie bewaren in Postvak IN';
$labels['forward_mode'] = 'Doorstuurmodus';
$labels['forward_mode_redirect'] = 'Omleiden (Behoudt oorspronkelijke afzender)';
$labels['forward_mode_inline'] = 'Doorsturen met inline voorbericht';
$labels['forward_condition'] = 'Doorstuurfilter';
$labels['forward_all'] = 'Alle inkomende berichten doorsturen';
$labels['forward_matching_only'] = 'Alleen doorsturen indien voldaan aan criteria';
$labels['forward_keywords'] = 'Trefwoorden in onderwerp (komma-gescheiden)';

$labels['templates_title'] = 'Meertalige antwoordsjablonen';
$labels['templates_desc'] = 'Stel aangepaste afwezigheidsberichten in voor specifieke talen of domeinen. Het systeem herkent automatisch de taal van de afzender.';
$labels['btn_add_template'] = 'Sjabloon toevoegen';
$labels['btn_edit'] = 'Bewerken';
$labels['btn_delete'] = 'Verwijderen';
$labels['btn_preview'] = 'Testen / Voorbeeld';
$labels['template_name'] = 'Sjabloonnaam';
$labels['template_lang'] = 'Taal';
$labels['template_is_default'] = 'Standaard terugval-sjabloon';
$labels['template_domain_rule'] = 'Domein- of e-mailfilter (optioneel)';
$labels['template_subject'] = 'Onderwerpsjabloon';
$labels['template_body'] = 'Berichttekst';
$labels['placeholders_legend'] = 'Beschikbare variabelen';
$labels['placeholder_start_date'] = 'Begindatum';
$labels['placeholder_end_date'] = 'Einddatum';
$labels['placeholder_return_date'] = 'Verwachte terugkeerdatum';
$labels['placeholder_sender_name'] = 'Naam afzender';
$labels['placeholder_sender_email'] = 'E-mailadres afzender';
$labels['placeholder_original_subject'] = 'Oorspronkelijk onderwerp';
$labels['placeholder_user_name'] = 'Uw weergavenaam';
$labels['placeholder_user_email'] = 'Uw e-mailadres';

$labels['anti_loop_title'] = 'Anti-lus & Slimme onderdrukking';
$labels['ignore_auto_submitted'] = 'Geautomatiseerde berichten en mailinglijsten negeren (Auto-Submitted, List-Id)';
$labels['ignore_daemons'] = 'Systeem-daemons en bounces negeren (mailer-daemon, postmaster, noreply)';
$labels['sender_blacklist'] = 'Zwarte lijst afzenders (e-mails of @domeinen, één per regel)';
$labels['subject_prefix'] = 'Voorvoegsel onderwerp';
$labels['max_replies_daily'] = 'Max. dagelijkse antwoorden (Globale limiet)';

$labels['col_timestamp'] = 'Datum & Tijd';
$labels['col_sender'] = 'Afzender';
$labels['col_recipient'] = 'Ontvanger / Doel';
$labels['col_action'] = 'Actie';
$labels['col_template'] = 'Sjabloon';
$labels['col_status'] = 'Status';
$labels['action_auto_reply'] = 'Automatisch beantwoord';
$labels['action_forwarded'] = 'Doorgestuurd';
$labels['action_suppressed'] = 'Onderdrukt (Limiet)';
$labels['no_logs_found'] = 'Nog geen afwezigheids- of doorstuuractiviteit geregistreerd.';
$labels['btn_clear_logs'] = 'Activiteitenlog wissen';
$labels['btn_export_sieve'] = 'Sieve-script exporteren';
$labels['saved_success'] = 'Instellingen succesvol opgeslagen.';
$labels['cleared_logs_success'] = 'Activiteitenlog succesvol gewist.';
$labels['simulate_title'] = 'Sjabloonsimulatie';
$labels['simulate_sender'] = 'Test afzender';
$labels['simulate_subject'] = 'Test onderwerp';
$labels['simulate_body'] = 'Test berichttekst';
$labels['simulate_run'] = 'Simulatie uitvoeren';
$labels['simulate_matched'] = 'Gekozen sjabloon';
$labels['simulate_detected_lang'] = 'Gedetecteerde taal';
