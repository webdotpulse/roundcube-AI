<?php
namespace XFramework;

/**
 * Roundcube Plus Framework plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

interface DatabaseInterface
{
    public function getColumns(string $table, bool $addPrefix = true): array;
    public function getTables(): array;
    public function hasTable(string $table): bool;
    public function removeOld(string $table, string $dateField = "created_at", int $seconds = 3600, bool $addPrefix = true): bool;
}