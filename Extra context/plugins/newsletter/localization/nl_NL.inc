<?php

/**
 * Localization strings for newsletter plugin (nl_NL)
 */

$labels = [];

$labels['newsletter'] = 'Nieuwsbrief';
$labels['newsletters'] = 'Nieuwsbrieven';
$labels['campaign_composer'] = 'Nieuwsbrief Opsteller';
$labels['new_campaign'] = 'Nieuwe Campagne';
$labels['campaigns'] = 'Campagnes';
$labels['suppressions'] = 'Uitschrijflijst (Suppressies)';
$labels['deliverability_guide'] = 'Bezorgbaarheid & Anti-Spam Gids';

// Step 1: Recipients
$labels['step_recipients'] = '1. Ontvangers';
$labels['select_recipients'] = 'Selecteer Ontvangers';
$labels['all_contacts'] = 'Alle Contacten in Adresboek';
$labels['contact_groups'] = 'Adresboekgroepen';
$labels['custom_addresses'] = 'Aangepaste e-maillijst (Plakken)';
$labels['custom_addresses_placeholder'] = "Plak hier e-mailadressen (één per regel of door komma's gescheiden):\nJan Jansen <jan@voorbeeld.nl>\npeter@voorbeeld.nl";
$labels['recipient_count'] = 'Totaal Aantal Ontvangers';
$labels['suppressed_excluded'] = 'Uitgeschreven Uitgesloten';
$labels['refresh_recipients'] = 'Vernieuw & Valideer Ontvangers';

// Step 2: Content
$labels['step_content'] = '2. Opstellen & Vormgeving';
$labels['sender_identity'] = 'Afzenderidentiteit';
$labels['subject'] = 'Onderwerpregel';
$labels['subject_placeholder'] = 'Duidelijk, aantrekkelijk onderwerp (vermijd hoofdletters en overmatig uitroeptekens)';
$labels['templates'] = 'E-mail Sjablonen';
$labels['template_blank'] = 'Leeg Canvas';
$labels['template_modern'] = 'Moderne Nieuwsbrief';
$labels['template_announcement'] = 'Productaankondiging';
$labels['template_digest'] = 'Wekelijks Overzicht';
$labels['insert_tokens'] = 'Token Invoegen:';
$labels['token_name'] = 'Volledige Naam';
$labels['token_first_name'] = 'Voornaam';
$labels['token_email'] = 'E-mailadres';
$labels['token_unsubscribe'] = 'Uitschrijf-link URL';
$labels['token_date'] = 'Datum Vandaag';
$labels['preview'] = 'Voorvertoning';
$labels['preview_sample_recipient'] = 'Voorbeeld met Testontvanger';

// Step 3: Anti-Spam Check
$labels['step_spam_check'] = '3. Anti-Spam & Bezorgbaarheidscontrole';
$labels['run_spam_check'] = 'Voer Anti-Spam Controle Uit';
$labels['spam_risk_score'] = 'Spam Risicoscore';
$labels['risk_low'] = 'Laag Risico (Uitstekende Bezorgbaarheid)';
$labels['risk_medium'] = 'Gemiddeld Risico (Bekijk Aanbevelingen)';
$labels['risk_high'] = 'Hoog Risico (Grote kans op spamfilter-blokkade)';
$labels['deliverability_tips'] = 'Aanbevelingen voor Bezorging';
$labels['individual_delivery_badge'] = 'Individuele 1-op-1 Envelop Verzending';
$labels['rfc8058_badge'] = 'RFC 8058 Eén-klik Uitschrijfheaders Actief';
$labels['dual_mime_badge'] = 'Automatische HTML + Tekst Dual MIME';

// Step 4: Dispatch
$labels['step_dispatch'] = '4. Batch Verzending';
$labels['batch_settings'] = 'Batch Verzendinstellingen';
$labels['batch_size'] = 'Batchgrootte (e-mails per batch)';
$labels['batch_delay'] = 'Pauze tussen batches (seconden)';
$labels['start_sending'] = 'Start Batchverzending';
$labels['sending_in_progress'] = 'Bezig met verzenden...';
$labels['pause_sending'] = 'Pauzeren';
$labels['resume_sending'] = 'Hervatten';
$labels['cancel_sending'] = 'Annuleren';
$labels['progress'] = 'Voortgang';
$labels['sent_count'] = 'Verzonden';
$labels['failed_count'] = 'Mislukt';
$labels['remaining_count'] = 'Resterend';
$labels['campaign_completed'] = 'Campagne succesvol voltooid!';

// Suppression list
$labels['email_address'] = 'E-mailadres';
$labels['reason'] = 'Reden';
$labels['date_unsubscribed'] = 'Datum Uitschrijving';
$labels['actions'] = 'Acties';
$labels['add_suppression'] = 'Toevoegen aan Uitschrijflijst';
$labels['remove_suppression'] = 'Verwijderen';
$labels['no_suppressions'] = 'Geen uitgeschreven adressen gevonden.';
$labels['confirm_remove_suppression'] = 'Weet u zeker dat u dit adres van de uitschrijflijst wilt verwijderen?';

// Unsubscribe public page
$labels['unsubscribe_title'] = 'Uitschrijving Bevestigd';
$labels['unsubscribe_success'] = 'U bent succesvol uitgeschreven van deze mailinglijst.';
$labels['unsubscribe_description'] = 'Uw e-mailadres (%s) is toegevoegd aan de uitschrijflijst en zal geen toekomstige nieuwsbrieven meer ontvangen.';
$labels['unsubscribe_btn'] = 'Bevestig Uitschrijving';

// Compose button
$labels['compose_newsletter_btn'] = 'Nieuwsbrief Opstellen';
$labels['compose_newsletter_tip'] = 'Open de batch nieuwsbrief verzender met anti-spam optimalisaties';
