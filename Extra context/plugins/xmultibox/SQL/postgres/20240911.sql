ALTER TABLE identities
    ADD xmultibox_enabled SMALLINT NOT NULL DEFAULT 0,
    ADD xmultibox_data TEXT NULL;