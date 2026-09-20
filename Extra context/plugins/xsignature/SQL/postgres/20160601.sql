ALTER TABLE identities
    ADD xsignature_enabled SMALLINT NOT NULL DEFAULT 0,
    ADD xsignature_id BIGINT NOT NULL DEFAULT 0,
    ADD xsignature_data TEXT NULL,
    ADD xsignature_html TEXT NULL,
    ADD xsignature_plain TEXT NULL;