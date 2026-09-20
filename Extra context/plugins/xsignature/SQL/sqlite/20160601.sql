ALTER TABLE identities ADD xsignature_enabled TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE identities ADD xsignature_id INT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE identities ADD xsignature_data MEDIUMTEXT NULL;
ALTER TABLE identities ADD xsignature_html MEDIUMTEXT NULL;
ALTER TABLE identities ADD xsignature_plain MEDIUMTEXT NULL;