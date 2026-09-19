<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Takvim';
$labels['app_menu_title'] = 'Takvim';

// menu
$labels['create_event'] = 'Etkinlik oluştur';
$labels['create_event_button'] = 'Yeni etkinlik';
$labels['day'] = 'Gün';
$labels['week'] = 'Hafta';
$labels['month'] = 'Ay';
$labels['agenda'] = 'Gündem';

// editing calendars
$labels['calendars'] = 'Takvimler';
$labels['my_calendar'] = 'Takvimim';
$labels['create_calendar'] = 'Takvim oluşturun';
$labels['new_calendar'] = 'Yeni takvim';
$labels['edit_calendar'] = 'Takvimi düzenle';
$labels['remove_calendar'] = 'Takvimi sil';
$labels['confirm_remove_calendar'] = 'Bu takvimi silmek istediğinizden emin misiniz?';
$labels['calendar_removed'] = 'Takvim silindi.';
$labels['notifications'] = 'Bildirimler';
$labels['default_event_visibility'] = 'Varsayılan olay görünürlüğü';
$labels['time_zone'] = 'Takvim saat dilimi';
$labels['share_calendar'] = 'Takvimi paylaşın';
$labels['permissions'] = 'İzinler';
$labels['permission_publish_calendar'] = 'Takvim yayınlayın';
$labels['permission_share_calendar'] = 'Takvimi paylaşın';
$labels['permission_edit_events'] = 'Etkinlikleri düzenle';
$labels['permission_see_details'] = 'Ayrıntılara bakın';
$labels['permission_publish_calendar_info'] = 'İşaretlenirse, kullanıcı takvimi yayınlayabilecektir.';
$labels['permission_share_calendar_info'] = 'İşaretlenirse, kullanıcı takvimi diğer kullanıcılarla paylaşabilecektir.';
$labels['permission_edit_events_info'] = 'İşaretlenirse, kullanıcı takvim etkinliklerini düzenleyebilir ve silebilir.';
$labels['permission_see_details_info'] = 'İşaretlenirse, tüm etkinlik ayrıntıları paylaşılır. İşaretli değilse, yalnızca mevcut/ meşgul bilgileri paylaşılır.';
$labels['share_send_notification'] = 'Bildir';
$labels['share_send_notification_info'] = 'İşaretlenirse, kullanıcıya yeni paylaşılan takvim hakkında bilgi veren bir e-posta gönderilir.';
$labels['new_share_not_saved_warning'] = 'Takvimi paylaşmak için bir e-posta yazdınız ancak paylaşılanlar listesine eklemek için Paylaş düğmesine tıklamadınız. Takvimi belirtilen kullanıcıyla paylaşmadan kaydetmeye devam mı edeyim?';
$labels['share'] = 'Paylaş';
$labels['shared_marker'] = '(Paylaşıldı)';
$labels['owner'] = 'Sahibi';
$labels['publish_calendar'] = 'Takvim yayınlayın';
$labels['create_link'] = 'Bağlantı oluştur';
$labels['reset'] = 'Sıfırla';
$labels['remove'] = 'Kaldırmak';
$labels['publish_full'] = 'Tüm etkinlik bilgilerini yayınlayın.';
$labels['publish_busy'] = 'Yalnızca boş/mesgul bilgilerini yayınlayın.';
$labels['sync_no_id'] = 'CalDAV seçenekleri takvimi kaydettikten sonra kullanılabilir hale gelecektir.';
$labels['share_no_id'] = 'Takvim paylaşım seçenekleri, takvimi kaydettikten sonra kullanılabilir hale gelecektir.';
$labels['publish_no_id'] = 'Takvim yayınlama seçenekleri, takvimi kaydettikten sonra kullanılabilir hale gelecektir.';
$labels['import_no_id'] = 'Takvimi kaydettikten sonra içe ve dışa aktarma seçenekleri kullanılabilir hale gelecektir.';
$labels['unable_to_save_calendar_data'] = 'Takvim verileri kaydedilemiyor.';
$labels['unable_to_retrieve_calendar_data'] = 'Takvim verileri alınamıyor. Takvim artık mevcut olmayabilir.';
$labels['unable_add_shared_calendar'] = 'Paylaşılan takvim eklenemiyor. Takvim artık kullanılamıyor olabilir.';
$labels['itip_organizer_message'] = 'Bu etkinliğe davet edildiniz ancak organizatör siz değilsiniz. Bu etkinlikte yaptığınız değişiklikler diğer katılımcılara gönderilmeyecektir.';
$labels['cannot_share_with_yourself'] = 'Kendi takviminizi kendinizle paylaşamazsınız.';
$labels['cannot_share_with_calendar_owner'] = 'Bu takvimi takvim sahibiyle paylaşamazsınız ($e)';
$labels['setting_default_notification_type'] = 'Yeni olaylar için varsayılan bildirimler';
$labels['calendar_name_cannot_be_empty'] = 'Takvim adı boş olamaz.';

// caldav server
$labels['sync_calendar'] = 'Takvimi senkronize et';
$labels['password_too_short'] = 'Parola en az 10 karakter uzunluğunda olmalıdır';
$labels['read_only'] = 'Sadece okuyun';
$labels['add_connection'] = 'Bağlantı ekle';
$labels['show_details'] = 'Detayları göster';
$labels['hide_details'] = 'Ayrıntıları gizle';
$labels['caldav_connection_password'] = 'Bu bağlantıyı oluştururken belirttiğiniz parola.';
$labels['yes'] = 'Evet';
$labels['no'] = 'Hayır';
$labels['confirm_remove_sync'] = 'Bu bağlantıyı kalıcı olarak kaldırmak istediğinizden emin misiniz?';
$labels['remove_sync_error'] = 'Bağlantı kaldırılırken bir hata oluştu.';
$labels['create_new_caldav_connection'] = 'Yeni CalDav bağlantısı oluşturma';
$labels['your_caldav_connections'] = 'CalDav bağlantılarınız';
$labels['created_at'] = 'Oluşturuldu';
$labels['connected_at'] = 'Son senkronizasyon';
$labels['never'] = 'Asla';
$labels['alternate_url'] = 'Alternatif URL';
$labels['caldav_password_info'] = 'Güvenlik nedeniyle bu şifre sunucuda kayıtlı değildir. Eğer unuttuysanız, bu bağlantıyı silin ve yeni bir tane oluşturun.';
$labels['caldav_url_info'] = 'Bu URL\'yi takvim bulmayı destekleyen uygulamalarla kullanın, örneğin: iOS ve Mac OS Calendar, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Bu URL\'yi yukarıdaki kısa URL ile çalışmayan uygulamalarla kullanın, örneğin: Outlook CalDav Synchronizer, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'CalDAV sunucu URL\'si geçersiz.';
$labels['cannot_resolve_caldav_host'] = 'CalDAV sunucusunun ana bilgisayarı çözümlenemiyor.';
$labels['private_caldav_addresses_not_allowed'] = 'Özel ve ayrılmış CalDAV adresleri kullanılamaz.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Paylaşılan takvim ekleme';
$labels['shared_calendars'] = 'Paylaşılan takvimler';
$labels['no_shared_calendars'] = 'Ekleyebileceğiniz paylaşılan takvim yoktur.';
$labels['add_to_my_calendars'] = 'Takvimlerime ekle';
$labels['remove_from_my_calendars'] = 'Takvimlerimden kaldır';
$labels['unshare'] = 'Paylaşımsız';
$labels['unshare_warning'] = 'Bu takvimin paylaşımını kaldırırsanız, takvim bu listeden kaybolur ve bir daha ekleyemezsiniz.';
$labels['calendar_name'] = 'Takvim adı';
$labels['calendar_owner'] = 'Takvim sahibi';
$labels['shared_date'] = 'Paylaşılan';
$labels['unable_to_share'] = 'E-posta hesapları uzak bir sunucuda bulunduğu için takvim aşağıdaki kullanıcılarla paylaşılamaz:';

// shared calendar email
$labels['shared_email_subject'] = 'u sizinle bir takvim paylaştı';
$labels['shared_email_body'] = 'Kullanıcı $u size "$c" adlı bir takvime erişim izni verdi. Bu takvimi etkin takvimler listenize eklemek için aşağıdaki bağlantıya tıklayın:';
$labels['shared_email_body_cpanel'] = 'Kullanıcı $u size "$c" adlı bir takvime erişim izni verdi. Bu takvimi etkin takvimler listenize eklemek için Roundcube\'de oturum açın, Takvim\'e gidin, kenar çubuğundaki artı simgesine tıklayın ve Paylaşılan takvim ekle\'yi seçin.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Tatil takvimleri';
$labels['edit_holiday_calendar'] = 'Tatil takvimini düzenleme';
$labels['religious_holidays'] = 'Dini bayramlar';
$labels['country_holidays'] = 'Ülke tatilleri';
$labels['add'] = 'Ekle';
$labels['no_more_holidays_allowed'] = 'Daha fazla tatil takvimi ekleyemezsiniz.';
$labels['cannot_load_holidays'] = 'Google sunucusundan tatil günleri yüklenemiyor.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Doğum günü takvimi ekle';
$labels['edit_birthday_calendar'] = 'Doğum günü takvimini düzenle';
$labels['contact_birthdays'] = 'İletişim Doğum Günleri';
$labels['contact_birthdays_info'] = 'Bu takvim, kişi listenizden doğum günü bilgilerini alır ve takvim etkinlikleri olarak görüntüler.';
$labels['birthday_event_title'] = 'Doğum günü: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Google takvimi ekleyin';
$labels['edit_google_calendar'] = 'Google takvimini düzenleme';
$labels['google_id'] = 'Google takvim kimliği';
$labels['new_google_calendar'] = 'Yeni Google Takvim';
$labels['specify_google_calendar_id'] = 'Lütfen Google takvim kimliğini yazın';
$labels['calendar_id_format_error'] = 'Google takvim kimliği @ karakterini içermelidir.';
$labels['cannot_load_google_calendar'] = 'Google takvimi "$c" yüklenemiyor. Takvim kimliğinin doğru olduğundan ve takvimin herkese açık hale getirildiğinden emin olun.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'CalDAV takvimi ekleme';
$labels['edit_caldav_calendar'] = 'CalDAV takvimini düzenleme';
$labels['add_caldav_calendar_information'] = 'Bir CalDAV takvimine abone olmak için CalDAV sunucu bilgilerini belirtin ve Takvimleri bul\'a tıklayın.';
$labels['caldav_url'] = 'CalDAV URL\'si';
$labels['find_calendars'] = 'Takvimleri bulun';
$labels['searching_for_calendars'] = 'Takvimler aranıyor, lütfen bekleyin...';
$labels['select_calendars'] = 'Eklenecek takvimleri seçin:';
$labels['read_only_status_yes'] = 'Evet';
$labels['read_only_status_no'] = 'Hayır';
$labels['subscribed'] = 'Abone ol';

$labels['refresh_calendar'] = 'Takvimi yenileyin';
$labels['refresh_every_minute'] = 'Her dakika';
$labels['refresh_every_5_minutes'] = 'Her 5 dakikada bir';
$labels['refresh_every_15_minutes'] = 'Her 15 dakikada bir';
$labels['refresh_every_30_minutes'] = 'Her 30 dakikada bir';
$labels['refresh_every_60_minutes'] = 'Her 60 dakikada bir';
$labels['refresh_never'] = 'Asla';

$labels['cannot_connect_to_caldav_server'] = 'CalDAV sunucusu "$n "ye bağlanılamıyor.';
$labels['caldav_client_error_url'] = 'CalDAV sunucusuna bağlanılamıyor. Sunucu URL\'sini kontrol edin.';
$labels['caldav_client_error_credentials'] = 'Yanlış CalDAV kullanıcı adı veya parolası.';
$labels['caldav_client_error_empty'] = 'Sunucu eklenebilecek herhangi bir takvim döndürmedi. CalDAV URL\'sini kontrol edin.';
$labels['caldav_client_error_server_info'] = 'Yanlış CalDAV sunucu bilgisi.';
$labels['caldav_client_error_add_calendar'] = 'Takvim "$n" kaydedilemiyor.';
$labels['caldav_client_error_delete_event'] = 'Olay silinemiyor';
$labels['caldav_client_error_save_event'] = 'CalDAV etkinliği kaydedilemiyor: etkinlik taşınmış veya silinmiş olabilir ya da takvim kullanılamıyor olabilir.';
$labels['caldav_client_error_calendar_added'] = '"$n" takvimi zaten takvim listenizde yer almaktadır.';

// previewing event
$labels['remove_recurring_event'] = 'Yinelenen etkinliği sil';
$labels['remove_one'] = 'Yalnızca bu örneği sil';
$labels['remove_all'] = 'Tüm örnekleri sil';
$labels['edit_event'] = 'Etkinliği düzenle';
$labels['event_removed'] = 'Olay silindi.';
$labels['go_to_url'] = 'URL\'ye git';
$labels['confirm_remove_event'] = 'Bu etkinliği silmek istediğinizden emin misiniz?';

// calendar/agenda grid
$labels['no_events'] = 'Görüntülenecek etkinlik yok.';

// importing events
$labels['select_calendar'] = 'Takvim seçin';
$labels['select_calendar_for_events'] = 'Etkinlikleri eklemek istediğiniz takvimi seçin:';
$labels['add_events'] = 'Etkinlik ekleme';
$labels['add_events_to_calendar'] = 'Takvime etkinlik ekleme';
$labels['events_imported_successfully'] = 'Olaylar başarıyla içe aktarıldı';
$labels['events_imported_with_errors'] = 'Başka bir olay içe aktarılamadı.';
$labels['error_importing_events'] = 'Etkinlikler takvime aktarılırken bir hata oluştu';

// editing events
$labels['calendar'] = 'Takvim';
$labels['new_event'] = 'Yeni etkinlik';
$labels['summary'] = 'Özet';
$labels['recurrence'] = 'Nüks';
$labels['title'] = 'Başlık';
$labels['location'] = 'Konum';
$labels['description'] = 'Açıklama';
$labels['comment'] = 'Yorum';
$labels['url'] = 'URL';
$labels['name'] = 'İsim';
$labels['start'] = 'Başlangıç';
$labels['end'] = 'Bitiş';
$labels['show_timezones'] = 'Zaman dilimlerini göster';
$labels['reset_timezones'] = 'Saat dilimlerini varsayılana sıfırla';
$labels['all_day'] = 'Tüm gün';
$labels['use_calendar_colors'] = 'Takvim renklerini kullanın';
$labels['color'] = 'Renk';
$labels['background'] = 'Arka plan';
$labels['text'] = 'Metin';
$labels['show_me_as'] = 'Bana göster';
$labels['available'] = 'Mevcut';
$labels['busy'] = 'Meşgul';
$labels['visibility'] = 'Görünürlük';
$labels['calendar_default'] = 'Varsayılan takvim';
$labels['public'] = 'Kamu';
$labels['private'] = 'Özel';
$labels['repeat'] = 'Tekrarla';
$labels['repeat_every'] = 'Her seferinde tekrarla';
$labels['never'] = 'Asla';
$labels['daily'] = 'Günlük';
$labels['weekly'] = 'Haftalık';
$labels['monthly'] = 'Aylık';
$labels['yearly'] = 'Yıllık';
$labels['days'] = 'gün(ler)';
$labels['weeks'] = 'hafta(lar)';
$labels['months'] = 'ay(lar)';
$labels['years'] = 'yıl(lar)';
$labels['repeat_on'] = 'Tekrarla';
$labels['repeat_by'] = 'Tekrarla';
$labels['day_of_month'] = 'ayın günü';
$labels['day_of_week'] = 'haftanın günü';
$labels['not_specified'] = 'Belirtilmemiş';
$labels['first'] = 'Birinci';
$labels['second'] = 'İkinci';
$labels['third'] = 'Üçüncü';
$labels['fourth'] = 'Dördüncü';
$labels['fifth'] = 'Beşinci';
$labels['second_to_last'] = 'Sondan ikinci';
$labels['last'] = 'En son';
$labels['attachment_too_large'] = 'Ek dosyası çok büyük. İzin verilen maksimum boyut $s\'dir.';
$labels['bg_color'] = 'Arka plan rengi';
$labels['tx_color'] = 'Metin rengi';
$labels['add_notification'] = 'Bildirim ekle';
$labels['popup'] = 'Popup';
$labels['no_notifications'] = 'Bu etkinliğin herhangi bir bildirimi yoktur.';
$labels['minutes'] = 'dakika';
$labels['hours'] = 'saatler';
$labels['link'] = 'Bağlantı';
$labels['attach_file_from_web'] = 'Dosya ekle';
$labels['attachment_url'] = 'Ek URL\'si';
$labels['invalid_url'] = 'Bu URL geçersiz.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = 'Çoğalt';
$labels['duplicate_suffix'] = '(kopya)';
$labels['no_notifications'] = 'Bu etkinliğin herhangi bir bildirimi yoktur.';
$labels['this_calendar_is_hidden'] = 'Bu takvim gizlenmiştir. Takvim görünür hale gelene kadar kaydedilmiş etkinlikler gösterilmeyecektir.';
$labels['make_calendar_visible'] = 'Bu takvimi görünür hale getirin.';

// recurrence
$labels['recurrence_range'] = 'Nüks aralığı';
$labels['repeat_forever'] = 'Sonsuza kadar tekrarla';
$labels['repeat_every_weekday'] = 'Hafta içi her gün tekrarlayın';
$labels['last_day'] = 'Son gün';
$labels['every'] = 'Her';
$labels['weekday'] = 'Hafta içi';
$labels['weekend_day'] = 'Hafta sonu günü';
$labels['day_of_week'] = 'haftanın günü';
$labels['day_of_month'] = 'ayın günü';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Sonra bitir';
$labels['occurrences'] = 'olaylar';

// end recurrence on date:
$labels['end_by_date'] = 'Tarihe göre bitiş';
$labels['removed_events_title'] = 'Yinelenen serilerden kaldırılan etkinlikler';
$labels['restore'] = 'Geri Yükleme';

// settings page
$labels['setting_view'] = 'İlk takvim görünümü';
$labels['setting_first_day'] = 'Hafta başlıyor';
$labels['setting_agenda_week_span'] = 'Gündem zaman aralığı (haftalar)';
$labels['setting_slot_duration'] = 'Gün zaman aralığı (dakika)';
$labels['setting_scroll_time'] = 'Zamana kaydırın';
$labels['setting_calendar'] = 'Etkinlikler için varsayılan takvim';
$labels['setting_refresh'] = 'Takvimleri yenileyin';
$labels['setting_week_numbers'] = 'Küçük takvimdeki hafta numaraları';
$labels['setting_event_border'] = 'Etkinlik kenarlıkları olarak kategori renkleri';
$labels['show_sunrise'] = 'Ay görünümünde gün doğumunu göster';
$labels['show_sunset'] = 'Ay görünümünde gün batımını göster';
$labels['categories'] = 'Kategoriler';
$labels['category'] = 'Kategori';
$labels['no_category'] = 'Kategori yok';
$labels['add_category'] = 'Kategori ekle';
$labels['setting_alarm_sound'] = 'Bildirim sesi';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bambu';
$labels['alarm_sound_bells'] = 'Çanlar';
$labels['alarm_sound_chord'] = 'Akor';
$labels['alarm_sound_circles'] = 'Daireler';
$labels['alarm_sound_glass'] = 'Cam';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Metal';
$labels['alarm_sound_ripple'] = 'Dalgalanma';
$labels['alarm_sound_startrek'] = 'Star Trek';
$labels['alarm_sound_whistle'] = 'Düdük';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Ses çal';

// alarms
$labels['upcoming_events'] = 'Yaklaşan etkinlikler';
$labels['dismiss'] = 'Dağılın';
$labels['dismiss_all'] = 'Tümünü reddet';
$labels['snooze'] = 'Ertele';
$labels['5_minutes'] = '5 dakika';
$labels['10_minutes'] = '10 dakika';
$labels['20_minutes'] = '20 dakika';
$labels['30_minutes'] = '30 dakika';
$labels['1_hour'] = '1 saat';
$labels['2_hours'] = '2 saat';
$labels['tomorrow'] = 'Yarına kadar.';
$labels['next_week'] = 'Gelecek haftaya kadar';

// sidebar
$labels['setting_show_xcalendar'] = 'Takvim Ajandasını Göster';
$labels['todays_agenda'] = 'Bugünün Gündemi';
$labels['no_events_today'] = 'Bugün için herhangi bir etkinlik planlanmamıştır.';
$labels['sidebar_more'] = 'Daha fazlası...';

// event invitation
$labels['itip_title_request'] = 'Bir etkinliğe davetlisiniz.';
$labels['itip_title_modified'] = 'Bu etkinlik değiştirilmiştir.';
$labels['itip_title_reply_accepted'] = 'u davetinizi kabul etti';
$labels['itip_title_reply_tentative'] = 'u davetinizi geçici olarak kabul etti';
$labels['itip_title_reply_declined'] = 'u davetinizi reddetti';
$labels['itip_title_cancel'] = 'Bu etkinlik iptal edilmiştir.';

$labels['itip_email_subject_request'] = 'Davetiye:';
$labels['itip_email_subject_request_modified'] = 'Etkinlik değiştirildi:';
$labels['itip_email_subject_reply'] = 'Re:';
$labels['itip_email_subject_cancel'] = 'İptal edildi:';

$labels['itip_popup_event_added'] = 'Etkinlik takvime eklendi.';
$labels['itip_popup_event_updated'] = 'Etkinlik takvimde güncellendi.';
$labels['itip_popup_event_deleted'] = 'Etkinlik takvimden silindi.';
$labels['itip_popup_response_sent'] = 'Yanıt organizatöre gönderildi.';

$labels['itip_message_invitation_accepted'] = 'Bu daveti kabul ettiniz.';
$labels['itip_message_invitation_tentative'] = 'Bu daveti geçici olarak kabul ettiniz.';
$labels['itip_message_invitation_declined'] = 'Bu daveti reddettiniz.';
$labels['itip_message_event_deleted'] = 'Etkinlik takviminizden silindi.';
$labels['itip_message_event_updated'] = 'Takviminizdeki etkinlik güncellendi.';
$labels['itip_message_event_missing'] = 'Bu etkinlik takvimlerinizde bulunmamaktadır.';

$labels['itip_accept'] = 'Kabul et';
$labels['itip_tentative'] = 'Geçici';
$labels['itip_decline'] = 'Düşüş';
$labels['itip_change_reponse'] = 'Yanıtı değiştir.';
$labels['itip_add_to_calendar'] = 'Takvime ekle';
$labels['itip_reply_to_sender'] = 'Gönderene yanıt ver';
$labels['itip_dont_import'] = 'Takvime eklemeyin';
$labels['itip_update_in_calendar'] = 'Takvimdeki etkinliği güncelleme';
$labels['itip_delete_from_calendar'] = 'Etkinliği takvimden silme';

$labels['mail_subject_invitation'] = 'Davetiye:';
$labels['mail_subject_update'] = 'Güncellenmiş davetiye:';
$labels['mail_subject_cancel'] = 'Etkinlik iptal edildi:';

// event email notifications
$labels['mail_title'] = 'Başlık:';
$labels['mail_start'] = 'Başla:';
$labels['mail_end'] = 'Bitti:';
$labels['mail_description'] = 'Açıklama:';
$labels['mail_location'] = 'Konum:';

// event attendees
$labels['attendees'] = 'Katılımcılar';
$labels['attendee_role'] = 'Rol';
$labels['attendee_role_required'] = 'Gerekli';
$labels['attendee_role_optional'] = 'Opsiyonel';
$labels['attendee_role_chair'] = 'Sandalye';
$labels['attendee_role_none'] = 'Katılımcı olmayan';
$labels['organizer'] = 'Organizatör';
$labels['attendance'] = 'Katılım';
$labels['your_status'] = 'Sizin durumunuz';
$labels['attendance_going'] = 'Gidiyor musun?';
$labels['attendee_status_yes'] = 'Evet';
$labels['attendee_status_no'] = 'Hayır';
$labels['attendee_status_maybe'] = 'Belki de';
$labels['attendee_status_delegated'] = 'Yetkilendirilmiş';
$labels['attendee_status_waiting'] = 'Bekliyorum';
$labels['attendee_status_waiting_for_confirmation'] = 'Onay için bekliyorum';
$labels['attendee_status_info_yes'] = 'Katılacağım';
$labels['attendee_status_info_no'] = 'Katılmayacağım';
$labels['attendee_status_info_maybe'] = 'Katılabilirsiniz';
$labels['attendee_status_info_delegated'] = 'Yetkilendirilmiş katılım';
$labels['attendee_role'] = 'Rol';
$labels['attendee_role_info'] = 'Katılımcının toplantıya katılması veya başkanlık etmesi gerekip gerekmediğini belirtin.';
$labels['attendee_notify'] = 'Bildir';
$labels['attendee_notify_info'] = 'İşaretlenirse, katılımcıya bir davet e-postası gönderilecektir.';
$labels['add_attendee'] = 'Katılımcı Ekle';
$labels['attendee_notify_question'] = 'Katılımcılara bildirim e-postaları gönderin?';
$labels['attendee_notify_textarea'] = 'İsteğe bağlı bir mesaj ekleyin';
$labels['attendee_notify_send'] = 'Gönder';
$labels['attendee_notify_dont'] = 'Gönderme';
$labels['attendee_notify_cancel'] = 'İptal';
$labels['new_attendee_not_saved_warning'] = 'Bir katılımcı e-posta adresi girdiniz ancak bunu katılımcı listesine eklemediniz. Bu katılımcıyı eklemeden kaydetmeye devam etmek ister misiniz?';

// import / export
$labels['import_export'] = 'İthalat / İhracat';
$labels['export_to_file'] = 'Dosyaya aktar';
$labels['export_info'] = 'Bu takvimin etkinliklerini iCalendar (ics) biçiminde dışa aktarmak ve indirmek için bu düğmeyi tıklayın. Takvim çok sayıda etkinlik içeriyorsa, dışa aktarma işlemi birkaç dakika sürebilir.';
$labels['export_no_events'] = 'Bu takvimde dışa aktarılacak herhangi bir etkinlik yok.';
$labels['import_from_file'] = 'Dosyadan içe aktar';
$labels['import_info'] = 'Etkinlikleri bir iCalendar (ics) dosyasından bu takvime aktarmak için bu düğmeyi tıklayın.';

// email notifications
$labels['appointment_reminder'] = 'Randevu hatırlatma';
$labels['email_notification_subject'] = 'Hatırlatma: $t at $d ($z)';
$labels['email_notification_cron_error'] = 'E-posta bildirimleri devre dışı bırakıldı. Yönetici ile iletişime geçin.';
$labels['alarm_before_event_starts'] = 'Etkinlik başlamadan önce';
$labels['alarm_after_event_starts'] = 'Etkinlik başladıktan sonra';
$labels['alarm_before_event_ends'] = 'Etkinlik sona ermeden önce';
$labels['alarm_after_event_ends'] = 'Etkinlik bittikten sonra';
$labels['alarm_at_specific_time'] = 'Belirli bir zamanda';

// search
$labels['specify_at_least_one_calendar'] = 'Aranacak en az bir takvim belirtin.';
$labels['specify_text_to_search'] = 'Aranacak metni yazın.';
$labels['clear_search_results'] = 'Temiz';
$labels['search_outdated_message'] = 'Arama sonuçları güncel olmayabilir. Aramanızı yeniden çalıştırmayı düşünün.';
$labels['date_range'] = 'Tarih Aralığı';
$labels['date_range_subtitle'] = 'Tarih aralığı:';
$labels['search_results'] = 'Bulunan Sonuçlar: $n';
$labels['modify_search_options'] = 'Arama seçeneklerini değiştirmeyi deneyin.';
$labels['show_shortcuts'] = 'Kısayolları göster';
$labels['hide_shortcuts'] = 'Kısayolları gizle';
$labels['this_month'] = 'Bu ay';
$labels['this_year'] = 'Bu yıl';
$labels['past_30_days'] = 'Son 30 gün';
$labels['next_30_days'] = 'Önümüzdeki 30 gün';
$labels['past_6_months'] = 'Son 6 ay';
$labels['next_6_months'] = 'Önümüzdeki 6 ay';

// calendar grid
$labels['cannot_load_events'] = '"$c" takvimi için etkinlikler yüklenemiyor.';
