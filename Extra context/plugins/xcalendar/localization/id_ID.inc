<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

$labels = [];
$labels['plugin_name'] = 'Kalender';
$labels['app_menu_title'] = 'Kalender';

// menu
$labels['create_event'] = 'Buat acara';
$labels['create_event_button'] = 'Acara baru';
$labels['day'] = 'Hari';
$labels['week'] = 'Minggu';
$labels['month'] = 'Bulan';
$labels['agenda'] = 'Agenda';

// editing calendars
$labels['calendars'] = 'Kalender';
$labels['my_calendar'] = 'Kalender Saya';
$labels['create_calendar'] = 'Membuat kalender';
$labels['new_calendar'] = 'Kalender baru';
$labels['edit_calendar'] = 'Edit kalender';
$labels['remove_calendar'] = 'Menghapus kalender';
$labels['confirm_remove_calendar'] = 'Apakah Anda yakin ingin menghapus kalender ini?';
$labels['calendar_removed'] = 'Kalender dihapus.';
$labels['notifications'] = 'Pemberitahuan';
$labels['default_event_visibility'] = 'Visibilitas acara default';
$labels['time_zone'] = 'Zona waktu kalender';
$labels['share_calendar'] = 'Bagikan kalender';
$labels['permissions'] = 'Izin';
$labels['permission_publish_calendar'] = 'Menerbitkan kalender';
$labels['permission_share_calendar'] = 'Bagikan kalender';
$labels['permission_edit_events'] = 'Mengedit acara';
$labels['permission_see_details'] = 'Lihat detailnya';
$labels['permission_publish_calendar_info'] = 'Jika dicentang, pengguna akan dapat mempublikasikan kalender.';
$labels['permission_share_calendar_info'] = 'Jika dicentang, pengguna akan dapat berbagi kalender dengan pengguna lain.';
$labels['permission_edit_events_info'] = 'Jika dicentang, pengguna akan dapat mengedit dan menghapus acara kalender.';
$labels['permission_see_details_info'] = 'Jika dicentang, semua detail acara akan dibagikan. Jika tidak dicentang, hanya informasi yang tersedia/sibuk yang akan dibagikan.';
$labels['share_send_notification'] = 'Memberitahukan';
$labels['share_send_notification_info'] = 'Jika dicentang, sebuah email akan dikirim ke pengguna yang memberitahukan tentang kalender yang baru dibagikan.';
$labels['new_share_not_saved_warning'] = 'Anda mengetik email untuk berbagi kalender namun tidak mengeklik tombol Bagikan untuk menambahkannya ke daftar bersama. Melanjutkan menyimpan tanpa berbagi kalender dengan pengguna yang ditentukan?';
$labels['share'] = 'Bagikan';
$labels['shared_marker'] = '(Dibagikan)';
$labels['owner'] = 'Pemilik';
$labels['publish_calendar'] = 'Menerbitkan kalender';
$labels['create_link'] = 'Buat tautan';
$labels['reset'] = 'Atur ulang';
$labels['remove'] = 'Menghapus';
$labels['publish_full'] = 'Publikasikan semua informasi acara.';
$labels['publish_busy'] = 'Publikasikan informasi yang gratis/sibuk saja.';
$labels['sync_no_id'] = 'Opsi CalDAV akan tersedia setelah Anda menyimpan kalender.';
$labels['share_no_id'] = 'Opsi berbagi kalender akan tersedia setelah Anda menyimpan kalender.';
$labels['publish_no_id'] = 'Opsi penerbitan kalender akan tersedia setelah Anda menyimpan kalender.';
$labels['import_no_id'] = 'Opsi impor dan ekspor akan tersedia setelah Anda menyimpan kalender.';
$labels['unable_to_save_calendar_data'] = 'Tidak dapat menyimpan data kalender.';
$labels['unable_to_retrieve_calendar_data'] = 'Tidak dapat mengambil data kalender. Kalender mungkin sudah tidak tersedia lagi.';
$labels['unable_add_shared_calendar'] = 'Tidak dapat menambahkan kalender bersama. Kalender mungkin tidak lagi tersedia.';
$labels['itip_organizer_message'] = 'Anda diundang ke acara ini, tetapi Anda bukan penyelenggara. Perubahan yang Anda lakukan pada acara ini tidak akan dikirim ke peserta lain.';
$labels['cannot_share_with_yourself'] = 'Anda tidak dapat berbagi kalender Anda sendiri dengan diri Anda sendiri.';
$labels['cannot_share_with_calendar_owner'] = 'Anda tidak dapat membagikan kalender ini dengan pemilik kalender ($e)';
$labels['setting_default_notification_type'] = 'Pemberitahuan default untuk acara baru';
$labels['calendar_name_cannot_be_empty'] = 'Nama kalender tidak boleh kosong.';

// caldav server
$labels['sync_calendar'] = 'Sinkronisasi kalender';
$labels['password_too_short'] = 'Kata sandi harus terdiri dari minimal 10 karakter';
$labels['read_only'] = 'Hanya baca';
$labels['add_connection'] = 'Menambahkan koneksi';
$labels['show_details'] = 'Tampilkan detail';
$labels['hide_details'] = 'Sembunyikan detail';
$labels['caldav_connection_password'] = 'Kata sandi yang Anda tentukan saat membuat sambungan ini.';
$labels['yes'] = 'Ya.';
$labels['no'] = 'Tidak.';
$labels['confirm_remove_sync'] = 'Apakah Anda yakin ingin menghapus koneksi ini secara permanen?';
$labels['remove_sync_error'] = 'Terjadi kesalahan sewaktu melepas koneksi.';
$labels['create_new_caldav_connection'] = 'Membuat koneksi CalDav baru';
$labels['your_caldav_connections'] = 'Koneksi CalDav Anda';
$labels['created_at'] = 'Dibuat';
$labels['connected_at'] = 'Sinkronisasi terakhir';
$labels['never'] = 'Tidak pernah.';
$labels['alternate_url'] = 'URL alternatif';
$labels['caldav_password_info'] = 'Untuk alasan keamanan, kata sandi ini tidak disimpan di server. Jika Anda lupa, hapus koneksi ini dan buat yang baru.';
$labels['caldav_url_info'] = 'Gunakan URL ini dengan aplikasi yang mendukung penemuan kalender, misalnya: Kalender iOS dan Mac OS, Evolution, emClient.';
$labels['caldav_alternate_url_info'] = 'Gunakan URL ini dengan aplikasi yang tidak dapat digunakan dengan url pendek di atas, misalnya: Outlook CalDav Synchronizer, Thunderbird Lightning.';
$labels['invalid_caldav_url'] = 'URL server CalDAV tidak valid.';
$labels['cannot_resolve_caldav_host'] = 'Tidak dapat menentukan host server CalDAV.';
$labels['private_caldav_addresses_not_allowed'] = 'Alamat CalDAV pribadi dan tertutup tidak diperbolehkan.';

// adding a shared calendar
$labels['add_shared_calendar'] = 'Menambahkan kalender bersama';
$labels['shared_calendars'] = 'Kalender bersama';
$labels['no_shared_calendars'] = 'Tidak ada kalender bersama yang dapat Anda tambahkan.';
$labels['add_to_my_calendars'] = 'Tambahkan ke kalender saya';
$labels['remove_from_my_calendars'] = 'Hapus dari kalender saya';
$labels['unshare'] = 'Batalkan berbagi';
$labels['unshare_warning'] = 'Jika Anda berhenti membagikan kalender ini, kalender ini akan hilang dari daftar ini dan Anda tidak akan dapat menambahkannya lagi.';
$labels['calendar_name'] = 'Nama kalender';
$labels['calendar_owner'] = 'Pemilik kalender';
$labels['shared_date'] = 'Bersama';
$labels['unable_to_share'] = 'Kalender tidak dapat dibagikan dengan pengguna berikut ini karena akun email mereka berada di server jarak jauh:';

// shared calendar email
$labels['shared_email_subject'] = '$u telah berbagi kalender dengan Anda';
$labels['shared_email_body'] = 'Pengguna $u telah memberi Anda akses ke kalender yang disebut "$c". Untuk menambahkan kalender ini ke daftar kalender aktif Anda, klik tautan di bawah ini:';
$labels['shared_email_body_cpanel'] = 'Pengguna $u telah memberi Anda akses ke kalender yang disebut "$c". Untuk menambahkan kalender ini ke daftar kalender aktif Anda, masuk ke Roundcube, buka Kalender, klik ikon plus di bilah sisi, lalu pilih Tambah kalender bersama.';

// adding holiday calendars
$labels['holiday_calendars'] = 'Kalender liburan';
$labels['edit_holiday_calendar'] = 'Edit kalender liburan';
$labels['religious_holidays'] = 'Hari libur keagamaan';
$labels['country_holidays'] = 'Liburan negara';
$labels['add'] = 'Menambahkan';
$labels['no_more_holidays_allowed'] = 'Anda tidak dapat menambahkan kalender liburan lagi.';
$labels['cannot_load_holidays'] = 'Tidak dapat memuat hari libur dari server Google.';

// adding birthday calendar
$labels['add_birthday_calendar'] = 'Tambahkan kalender Ulang Tahun';
$labels['edit_birthday_calendar'] = 'Edit Kalender Ulang Tahun';
$labels['contact_birthdays'] = 'Hubungi Ulang Tahun';
$labels['contact_birthdays_info'] = 'Kalender ini mengambil informasi ulang tahun dari daftar kontak Anda dan menampilkannya sebagai acara kalender.';
$labels['birthday_event_title'] = 'Ulang tahun: $n';

// adding google calendars
$labels['add_google_calendar'] = 'Tambahkan kalender Google';
$labels['edit_google_calendar'] = 'Edit kalender Google';
$labels['google_id'] = 'ID kalender Google';
$labels['new_google_calendar'] = 'Kalender Google baru';
$labels['specify_google_calendar_id'] = 'Silakan ketik ID kalender Google';
$labels['calendar_id_format_error'] = 'ID kalender Google harus menyertakan karakter @.';
$labels['cannot_load_google_calendar'] = 'Kalender Google "$c" tidak dapat dimuat. Pastikan ID kalender sudah benar dan kalender telah tersedia untuk umum.';

// adding caldav calendars
$labels['add_caldav_calendar'] = 'Menambahkan kalender CalDAV';
$labels['edit_caldav_calendar'] = 'Mengedit kalender CalDAV';
$labels['add_caldav_calendar_information'] = 'Untuk berlangganan kalender CalDAV, tentukan informasi server CalDAV dan klik Cari kalender.';
$labels['caldav_url'] = 'URL CalDAV';
$labels['find_calendars'] = 'Temukan kalender';
$labels['searching_for_calendars'] = 'Mencari kalender, harap tunggu...';
$labels['select_calendars'] = 'Pilih kalender yang akan ditambahkan:';
$labels['read_only_status_yes'] = 'Ya.';
$labels['read_only_status_no'] = 'Tidak.';
$labels['subscribed'] = 'Berlangganan';

$labels['refresh_calendar'] = 'Segarkan kalender';
$labels['refresh_every_minute'] = 'Setiap menit';
$labels['refresh_every_5_minutes'] = 'Setiap 5 menit';
$labels['refresh_every_15_minutes'] = 'Setiap 15 menit';
$labels['refresh_every_30_minutes'] = 'Setiap 30 menit';
$labels['refresh_every_60_minutes'] = 'Setiap 60 menit';
$labels['refresh_never'] = 'Tidak pernah.';

$labels['cannot_connect_to_caldav_server'] = 'Tidak dapat terhubung ke server CalDAV "$n".';
$labels['caldav_client_error_url'] = 'Tidak dapat menyambung ke server CalDAV. Periksa URL server.';
$labels['caldav_client_error_credentials'] = 'Nama pengguna atau kata sandi CalDAV salah.';
$labels['caldav_client_error_empty'] = 'Server tidak mengembalikan kalender apa pun yang dapat ditambahkan. Periksa URL CalDAV.';
$labels['caldav_client_error_server_info'] = 'Informasi server CalDAV salah.';
$labels['caldav_client_error_add_calendar'] = 'Tidak dapat menyimpan kalender "$n".';
$labels['caldav_client_error_delete_event'] = 'Tidak dapat menghapus acara';
$labels['caldav_client_error_save_event'] = 'Tidak dapat menyimpan acara CalDAV: acara tersebut mungkin telah dipindahkan, dihapus, atau kalendernya tidak tersedia.';
$labels['caldav_client_error_calendar_added'] = 'Kalender "$n" sudah ada di daftar kalender Anda.';

// previewing event
$labels['remove_recurring_event'] = 'Menghapus kejadian berulang';
$labels['remove_one'] = 'Hapus hanya contoh ini';
$labels['remove_all'] = 'Menghapus semua contoh';
$labels['edit_event'] = 'Mengedit acara';
$labels['event_removed'] = 'Acara dihapus.';
$labels['go_to_url'] = 'Pergi ke URL';
$labels['confirm_remove_event'] = 'Apakah Anda yakin ingin menghapus acara ini?';

// calendar/agenda grid
$labels['no_events'] = 'Tidak ada acara yang dapat ditampilkan.';

// importing events
$labels['select_calendar'] = 'Pilih kalender';
$labels['select_calendar_for_events'] = 'Pilih kalender yang ingin Anda tambahkan acara:';
$labels['add_events'] = 'Menambahkan acara';
$labels['add_events_to_calendar'] = 'Menambahkan acara ke kalender';
$labels['events_imported_successfully'] = 'Acara berhasil diimpor';
$labels['events_imported_with_errors'] = 'Beberapa acara lainnya tidak dapat diimpor.';
$labels['error_importing_events'] = 'Terjadi kesalahan saat mengimpor acara ke dalam kalender';

// editing events
$labels['calendar'] = 'Kalender';
$labels['new_event'] = 'Acara baru';
$labels['summary'] = 'Ringkasan';
$labels['recurrence'] = 'Kekambuhan';
$labels['title'] = 'Judul';
$labels['location'] = 'Lokasi';
$labels['description'] = 'Deskripsi';
$labels['comment'] = 'Komentar';
$labels['url'] = 'URL';
$labels['name'] = 'Nama';
$labels['start'] = 'Mulai';
$labels['end'] = 'Akhir';
$labels['show_timezones'] = 'Tampilkan zona waktu';
$labels['reset_timezones'] = 'Mengatur ulang zona waktu ke default';
$labels['all_day'] = 'Sepanjang hari.';
$labels['use_calendar_colors'] = 'Gunakan warna kalender';
$labels['color'] = 'Warna';
$labels['background'] = 'Latar Belakang';
$labels['text'] = 'Teks';
$labels['show_me_as'] = 'Tunjukkan padaku sebagai';
$labels['available'] = 'Tersedia';
$labels['busy'] = 'Sibuk.';
$labels['visibility'] = 'Visibilitas';
$labels['calendar_default'] = 'Kalender default';
$labels['public'] = 'Publik';
$labels['private'] = 'Pribadi';
$labels['repeat'] = 'Ulangi';
$labels['repeat_every'] = 'Ulangi setiap';
$labels['never'] = 'Tidak pernah.';
$labels['daily'] = 'Setiap hari';
$labels['weekly'] = 'Mingguan';
$labels['monthly'] = 'Bulanan';
$labels['yearly'] = 'Tahunan';
$labels['days'] = 'hari (s)';
$labels['weeks'] = 'minggu (s)';
$labels['months'] = 'bulan';
$labels['years'] = 'tahun (tahun)';
$labels['repeat_on'] = 'Ulangi pada';
$labels['repeat_by'] = 'Ulangi oleh';
$labels['day_of_month'] = 'hari dalam sebulan';
$labels['day_of_week'] = 'hari dalam seminggu';
$labels['not_specified'] = 'Tidak ditentukan';
$labels['first'] = 'Pertama';
$labels['second'] = 'Kedua';
$labels['third'] = 'Ketiga';
$labels['fourth'] = 'Keempat';
$labels['fifth'] = 'Kelima';
$labels['second_to_last'] = 'Kedua hingga terakhir';
$labels['last'] = 'Terakhir';
$labels['attachment_too_large'] = 'File lampiran terlalu besar. Ukuran maksimum yang diizinkan adalah $s.';
$labels['bg_color'] = 'Warna latar belakang';
$labels['tx_color'] = 'Warna teks';
$labels['add_notification'] = 'Tambahkan pemberitahuan';
$labels['popup'] = 'Popup';
$labels['no_notifications'] = 'Acara ini tidak memiliki notifikasi apa pun.';
$labels['minutes'] = 'menit';
$labels['hours'] = 'jam';
$labels['link'] = 'Tautan';
$labels['attach_file_from_web'] = 'Lampirkan file';
$labels['attachment_url'] = 'URL Lampiran';
$labels['invalid_url'] = 'URL ini tidak valid.';
$labels['xdropbox_name'] = 'Dropbox';
$labels['xgoogle_drive_name'] = 'Google Drive';
$labels['duplicate'] = 'Duplikasi';
$labels['duplicate_suffix'] = '(duplikat)';
$labels['no_notifications'] = 'Acara ini tidak memiliki notifikasi apa pun.';
$labels['this_calendar_is_hidden'] = 'Kalender ini disembunyikan. Acara yang telah disimpan tidak akan ditampilkan sampai kalender tersebut ditampilkan.';
$labels['make_calendar_visible'] = 'Tampilkan kalender ini.';

// recurrence
$labels['recurrence_range'] = 'Rentang pengulangan';
$labels['repeat_forever'] = 'Ulangi selamanya';
$labels['repeat_every_weekday'] = 'Ulangi setiap hari kerja';
$labels['last_day'] = 'Hari terakhir';
$labels['every'] = 'Setiap';
$labels['weekday'] = 'Hari kerja';
$labels['weekend_day'] = 'Hari akhir pekan';
$labels['day_of_week'] = 'hari dalam seminggu';
$labels['day_of_month'] = 'hari dalam sebulan';

// used together as: End after 5 occurrences
$labels['end_after'] = 'Akhiri setelah';
$labels['occurrences'] = 'kejadian';

// end recurrence on date:
$labels['end_by_date'] = 'Akhiri dengan tanggal';
$labels['removed_events_title'] = 'Peristiwa yang dihapus dari rangkaian yang berulang';
$labels['restore'] = 'Mengembalikan';

// settings page
$labels['setting_view'] = 'Tampilan kalender awal';
$labels['setting_first_day'] = 'Minggu dimulai pada';
$labels['setting_agenda_week_span'] = 'Rentang waktu agenda (minggu)';
$labels['setting_slot_duration'] = 'Slot waktu siang hari (menit)';
$labels['setting_scroll_time'] = 'Gulir ke waktu';
$labels['setting_calendar'] = 'Kalender default untuk acara';
$labels['setting_refresh'] = 'Menyegarkan kalender';
$labels['setting_week_numbers'] = 'Nomor minggu dalam kalender kecil';
$labels['setting_event_border'] = 'Warna kategori sebagai batas acara';
$labels['show_sunrise'] = 'Menampilkan matahari terbit dalam tampilan bulan';
$labels['show_sunset'] = 'Menampilkan matahari terbenam dalam tampilan bulan';
$labels['categories'] = 'Kategori';
$labels['category'] = 'Kategori';
$labels['no_category'] = 'Tidak ada kategori';
$labels['add_category'] = 'Tambahkan kategori';
$labels['setting_alarm_sound'] = 'Suara pemberitahuan';
$labels['alarm_sound_aurora'] = 'Aurora';
$labels['alarm_sound_bamboo'] = 'Bambu';
$labels['alarm_sound_bells'] = 'Lonceng';
$labels['alarm_sound_chord'] = 'Chord';
$labels['alarm_sound_circles'] = 'Lingkaran';
$labels['alarm_sound_glass'] = 'Kaca';
$labels['alarm_sound_maramba'] = 'Maramba';
$labels['alarm_sound_metal'] = 'Logam';
$labels['alarm_sound_ripple'] = 'Riak';
$labels['alarm_sound_startrek'] = 'Star Trek';
$labels['alarm_sound_whistle'] = 'Peluit';
$labels['alarm_sound_yoohoo'] = 'Yoohoo';
$labels['play_sound'] = 'Putar suara';

// alarms
$labels['upcoming_events'] = 'Acara yang akan datang';
$labels['dismiss'] = 'Bubarkan.';
$labels['dismiss_all'] = 'Bubarkan semua';
$labels['snooze'] = 'Tunda';
$labels['5_minutes'] = '5 menit';
$labels['10_minutes'] = '10 menit';
$labels['20_minutes'] = '20 menit';
$labels['30_minutes'] = '30 menit';
$labels['1_hour'] = '1 jam';
$labels['2_hours'] = '2 jam';
$labels['tomorrow'] = 'Sampai besok';
$labels['next_week'] = 'Sampai minggu depan';

// sidebar
$labels['setting_show_xcalendar'] = 'Tampilkan Agenda Kalender';
$labels['todays_agenda'] = 'Agenda hari ini';
$labels['no_events_today'] = 'Tidak ada acara yang dijadwalkan untuk hari ini.';
$labels['sidebar_more'] = 'Selengkapnya...';

// event invitation
$labels['itip_title_request'] = 'Anda diundang ke sebuah acara.';
$labels['itip_title_modified'] = 'Acara ini telah dimodifikasi.';
$labels['itip_title_reply_accepted'] = '$u telah menerima undangan Anda';
$labels['itip_title_reply_tentative'] = '$u telah menerima undangan Anda untuk sementara waktu';
$labels['itip_title_reply_declined'] = '$u telah menolak undangan Anda';
$labels['itip_title_cancel'] = 'Acara ini telah dibatalkan.';

$labels['itip_email_subject_request'] = 'Undangan:';
$labels['itip_email_subject_request_modified'] = 'Acara dimodifikasi:';
$labels['itip_email_subject_reply'] = 'Kembali:';
$labels['itip_email_subject_cancel'] = 'Dibatalkan:';

$labels['itip_popup_event_added'] = 'Acara ditambahkan ke kalender.';
$labels['itip_popup_event_updated'] = 'Acara diperbarui di kalender.';
$labels['itip_popup_event_deleted'] = 'Acara dihapus dari kalender.';
$labels['itip_popup_response_sent'] = 'Tanggapan dikirim ke penyelenggara.';

$labels['itip_message_invitation_accepted'] = 'Anda telah menerima undangan ini.';
$labels['itip_message_invitation_tentative'] = 'Anda telah menerima undangan ini dengan ragu-ragu.';
$labels['itip_message_invitation_declined'] = 'Anda telah menolak undangan ini.';
$labels['itip_message_event_deleted'] = 'Acara tersebut telah dihapus dari kalender Anda.';
$labels['itip_message_event_updated'] = 'Acara di kalender Anda telah diperbarui.';
$labels['itip_message_event_missing'] = 'Acara ini tidak ada dalam kalender Anda.';

$labels['itip_accept'] = 'Menerima';
$labels['itip_tentative'] = 'Tentatif';
$labels['itip_decline'] = 'Menolak';
$labels['itip_change_reponse'] = 'Mengubah respons.';
$labels['itip_add_to_calendar'] = 'Tambahkan ke kalender';
$labels['itip_reply_to_sender'] = 'Membalas ke pengirim';
$labels['itip_dont_import'] = 'Jangan tambahkan ke kalender';
$labels['itip_update_in_calendar'] = 'Memperbarui acara di kalender';
$labels['itip_delete_from_calendar'] = 'Menghapus acara dari kalender';

$labels['mail_subject_invitation'] = 'Undangan:';
$labels['mail_subject_update'] = 'Undangan yang diperbarui:';
$labels['mail_subject_cancel'] = 'Acara dibatalkan:';

// event email notifications
$labels['mail_title'] = 'Judul:';
$labels['mail_start'] = 'Mulai:';
$labels['mail_end'] = 'Selesai:';
$labels['mail_description'] = 'Deskripsi:';
$labels['mail_location'] = 'Lokasi:';

// event attendees
$labels['attendees'] = 'Peserta';
$labels['attendee_role'] = 'Peran';
$labels['attendee_role_required'] = 'Diperlukan';
$labels['attendee_role_optional'] = 'Opsional';
$labels['attendee_role_chair'] = 'Kursi';
$labels['attendee_role_none'] = 'Non-peserta';
$labels['organizer'] = 'Penyelenggara';
$labels['attendance'] = 'Kehadiran';
$labels['your_status'] = 'Status Anda';
$labels['attendance_going'] = 'Pergi?';
$labels['attendee_status_yes'] = 'Ya.';
$labels['attendee_status_no'] = 'Tidak.';
$labels['attendee_status_maybe'] = 'Mungkin.';
$labels['attendee_status_delegated'] = 'Didelegasikan';
$labels['attendee_status_waiting'] = 'Menunggu';
$labels['attendee_status_waiting_for_confirmation'] = 'Menunggu konfirmasi';
$labels['attendee_status_info_yes'] = 'Akan hadir';
$labels['attendee_status_info_no'] = 'Tidak akan hadir';
$labels['attendee_status_info_maybe'] = 'Mungkin hadir';
$labels['attendee_status_info_delegated'] = 'Kehadiran yang didelegasikan';
$labels['attendee_role'] = 'Peran';
$labels['attendee_role_info'] = 'Tentukan apakah peserta yang hadir diharuskan menghadiri atau memimpin rapat.';
$labels['attendee_notify'] = 'Memberitahukan';
$labels['attendee_notify_info'] = 'Jika dicentang, email undangan akan dikirim ke peserta.';
$labels['add_attendee'] = 'Tambahkan Peserta';
$labels['attendee_notify_question'] = 'Mengirim email pemberitahuan kepada peserta?';
$labels['attendee_notify_textarea'] = 'Menambahkan pesan opsional';
$labels['attendee_notify_send'] = 'Kirim';
$labels['attendee_notify_dont'] = 'Jangan kirim';
$labels['attendee_notify_cancel'] = 'Batal';
$labels['new_attendee_not_saved_warning'] = 'Anda telah memasukkan alamat email peserta tetapi belum menambahkannya ke daftar peserta. Apakah Anda ingin melanjutkan penyimpanan tanpa peserta ini?';

// import / export
$labels['import_export'] = 'Impor / Ekspor';
$labels['export_to_file'] = 'Ekspor ke file';
$labels['export_info'] = 'Klik tombol ini untuk mengekspor dan mengunduh acara kalender ini dalam format iCalendar (ics). Jika kalender berisi banyak acara, proses ekspor mungkin memerlukan waktu beberapa saat.';
$labels['export_no_events'] = 'Kalender ini tidak memiliki acara apa pun untuk diekspor.';
$labels['import_from_file'] = 'Impor dari file';
$labels['import_info'] = 'Klik tombol ini untuk mengimpor acara ke kalender ini dari file iCalendar (ics).';

// email notifications
$labels['appointment_reminder'] = 'Pengingat janji temu';
$labels['email_notification_subject'] = 'Pengingat: $t pada $d ($z)';
$labels['email_notification_cron_error'] = 'Notifikasi email dinonaktifkan. Hubungi administrator.';
$labels['alarm_before_event_starts'] = 'Sebelum acara dimulai';
$labels['alarm_after_event_starts'] = 'Setelah acara dimulai';
$labels['alarm_before_event_ends'] = 'Sebelum acara berakhir';
$labels['alarm_after_event_ends'] = 'Setelah acara berakhir';
$labels['alarm_at_specific_time'] = 'Pada waktu tertentu';

// search
$labels['specify_at_least_one_calendar'] = 'Tentukan setidaknya satu kalender yang akan dicari.';
$labels['specify_text_to_search'] = 'Ketik teks yang akan dicari.';
$labels['clear_search_results'] = 'Jelas';
$labels['search_outdated_message'] = 'Hasil pencarian mungkin sudah usang. Pertimbangkan untuk menjalankan kembali pencarian Anda.';
$labels['date_range'] = 'Rentang Tanggal';
$labels['date_range_subtitle'] = 'Rentang tanggal:';
$labels['search_results'] = 'Hasil Ditemukan: $n';
$labels['modify_search_options'] = 'Cobalah memodifikasi opsi pencarian.';
$labels['show_shortcuts'] = 'Menampilkan pintasan';
$labels['hide_shortcuts'] = 'Menyembunyikan pintasan';
$labels['this_month'] = 'Bulan ini';
$labels['this_year'] = 'Tahun ini';
$labels['past_30_days'] = '30 hari terakhir';
$labels['next_30_days'] = '30 hari ke depan';
$labels['past_6_months'] = '6 bulan terakhir';
$labels['next_6_months'] = '6 bulan ke depan';

// calendar grid
$labels['cannot_load_events'] = 'Tidak dapat memuat acara untuk kalender "$c".';
