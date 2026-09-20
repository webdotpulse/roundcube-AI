ALTER TABLE identities
    ADD xmultibox_enabled TINYINT(1) NOT NULL DEFAULT 0,
    ADD xmultibox_data MEDIUMTEXT NULL;