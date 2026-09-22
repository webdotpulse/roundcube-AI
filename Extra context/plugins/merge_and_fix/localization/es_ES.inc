<?php

/**
 * Spanish localization for Merge & Fix Contacts Plugin (Combinar y corregir)
 */

$labels = [];
$labels['merge_and_fix'] = 'Combinar y corregir';
$labels['merge_and_fix_tip'] = 'Encuentra duplicados, combina perfiles y limpia tu lista de contactos';
$labels['merge_and_fix_subtitle'] = 'Limpia contactos duplicados y descubre contactos frecuentes por correo';
$labels['rescan'] = 'Volver a escanear';
$labels['reset_dismissed'] = 'Restablecer descartados';
$labels['confirm_reset_dismissed'] = '¿Restablecer sugerencias descartadas y volver a escanear la libreta de contactos?';
$labels['dismissed_reset_success'] = 'Los elementos descartados se han restablecido. Escaneando contactos de nuevo.';

// Tabs
$labels['duplicates_tab'] = 'Duplicados';
$labels['frequent_tab'] = 'Contactos frecuentes';

// Duplicates Section
$labels['merge_duplicates_heading'] = 'Combinar contactos duplicados';
$labels['merge_duplicates_desc'] = 'Las siguientes tarjetas comparten nombres, teléfonos o correos coincidentes.';
$labels['merge_all'] = 'Combinar todo';
$labels['scanning_duplicates'] = 'Buscando contactos duplicados en la libreta...';
$labels['no_duplicates_title'] = 'No se encontraron duplicados';
$labels['no_duplicates_desc'] = 'Tu lista de contactos está limpia y actualizada.';
$labels['merge_suggestion'] = 'Sugerencia de contacto duplicado';
$labels['match'] = 'Coincidencia';
$labels['merged_preview'] = 'Resultado combinado';
$labels['merge'] = 'Combinar';
$labels['dismiss'] = 'Descartar';
$labels['dismissed'] = 'Sugerencia descartada';

// Reasons
$labels['reason_matching_email'] = 'Correo coincidente';
$labels['reason_matching_phone'] = 'Teléfono coincidente';
$labels['reason_matching_name'] = 'Nombre coincidente';

// Frequent Contacts Section
$labels['frequent_contacts_heading'] = 'Añadir contactos frecuentes';
$labels['frequent_contacts_desc'] = 'Personas con las que te comunicas frecuentemente que aún no están guardadas.';
$labels['add_all'] = 'Añadir todo';
$labels['add_to_contacts'] = 'Añadir a contactos';
$labels['emails_exchanged'] = 'correos intercambiados';
$labels['scanning_frequent'] = 'Analizando historial de comunicación...';
$labels['no_frequent_title'] = 'Sin contactos frecuentes pendientes';
$labels['no_frequent_desc'] = 'Todos tus contactos frecuentes ya están guardados en tu libreta.';

// Operation Outcomes
$labels['contacts_merged_success'] = '¡Contactos combinados con éxito!';
$labels['contact_added_success'] = 'Contacto "%s" añadido a la libreta.';
$labels['batch_merged_summary'] = 'Se combinaron con éxito %d tarjetas de contactos duplicados.';
$labels['batch_added_summary'] = 'Se añadieron con éxito %d contactos frecuentes a tu libreta.';
$labels['no_duplicates_to_merge'] = 'No hay contactos duplicados para combinar.';
$labels['invalid_contact_ids'] = 'Identificadores de contacto no válidos.';
$labels['invalid_email'] = 'Dirección de correo electrónico no válida.';
$labels['contact_not_found'] = 'No se pudieron encontrar uno o más contactos.';
$labels['merge_failed'] = 'No se pudieron combinar los contactos.';
$labels['add_contact_failed'] = 'No se pudo añadir el contacto a la libreta.';
