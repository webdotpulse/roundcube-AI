ALTER TABLE identities
    ADD xsignature_enabled TINYINT(1) NOT NULL DEFAULT 0,
    ADD xsignature_id INT UNSIGNED NOT NULL DEFAULT 0,
    ADD xsignature_data MEDIUMTEXT NULL,
    ADD xsignature_html MEDIUMTEXT NULL,
    ADD xsignature_plain MEDIUMTEXT NULL;