<?php

/**
 * Roundcube Merge & Fix Contacts Plugin (merge_and_fix)
 *
 * Inspired by Google Contacts "Merge & fix" tool:
 * - Scans address books to find duplicate contact cards matching by email, phone, or name.
 * - Combines separate contact entries into a unified contact card preserving all fields and group memberships.
 * - Discovers and recommends unsaved frequent email contacts from mailbox communication history.
 * - Supports individual one-by-one reviews, batch "Merge all" / "Add all", and dismissals.
 *
 * @version 1.0.0
 * @author Webdotpulse
 * @license GNU GPLv3+
 */

declare(strict_types=1);

class merge_and_fix extends rcube_plugin
{
    public $task = '?(?!logout).*';

    /** @var rcmail */
    protected $rcmail;

    /**
     * Plugin initialization
     */
    public function init(): void
    {
        $this->rcmail = rcmail::get_instance();
        $this->load_config();
        $this->add_texts('localization/', true);

        // Include CSS and JS on addressbook task
        if ($this->rcmail->task === 'addressbook') {
            $this->include_stylesheet('merge_and_fix.css');
            $this->include_script('merge_and_fix.js');

            // Register main actions
            $this->register_action('plugin.merge_and_fix', [$this, 'action_index']);
            $this->register_action('plugin.merge_and_fix-scan', [$this, 'action_scan']);
            $this->register_action('plugin.merge_and_fix-merge', [$this, 'action_merge']);
            $this->register_action('plugin.merge_and_fix-merge-all', [$this, 'action_merge_all']);
            $this->register_action('plugin.merge_and_fix-dismiss', [$this, 'action_dismiss']);
            $this->register_action('plugin.merge_and_fix-reset-dismissed', [$this, 'action_reset_dismissed']);
            $this->register_action('plugin.merge_and_fix-add-suggested', [$this, 'action_add_suggested']);
            $this->register_action('plugin.merge_and_fix-add-all-suggested', [$this, 'action_add_all_suggested']);

            // Add toolbar button in addressbook toolbar
            $this->add_button([
                'command' => 'plugin.merge_and_fix-open',
                'id' => 'btn-merge-and-fix',
                'class' => 'button merge-and-fix',
                'innerclass' => 'inner',
                'label' => 'merge_and_fix.merge_and_fix',
                'title' => 'merge_and_fix.merge_and_fix_tip',
                'type' => 'link',
            ], 'toolbar');
        }
    }

    /**
     * Main action to render the Merge & Fix Studio inside Roundcube
     */
    public function action_index(): void
    {
        $this->rcmail->output->set_pagetitle($this->gettext('merge_and_fix'));
        $this->include_stylesheet('merge_and_fix.css');
        $this->include_script('merge_and_fix.js');

        $this->register_handler('plugin.body', [$this, 'render_ui']);
        $this->rcmail->output->send('plugin');
    }

    /**
     * Render the rich Google Contacts-inspired Merge & Fix Studio HTML
     */
    public function render_ui(): string
    {
        ob_start();
        ?>
        <div id="merge-and-fix-studio" class="merge-and-fix-wrapper content formcontent scroller boxcontent uibox">
            <!-- Header Bar -->
            <div class="mf-header-card card mb-4">
                <div class="mf-header-content">
                    <div class="mf-brand">
                        <div class="mf-brand-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="18" cy="18" r="3"></circle>
                                <circle cx="6" cy="6" r="3"></circle>
                                <path d="M6 21V9a9 9 0 0 0 9 9"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="mf-title"><?= htmlspecialchars($this->gettext('merge_and_fix'), ENT_QUOTES, 'UTF-8') ?></h2>
                            <p class="mf-subtitle"><?= htmlspecialchars($this->gettext('merge_and_fix_subtitle'), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                    <div class="mf-header-actions">
                        <button type="button" id="mf-btn-refresh" class="btn btn-secondary btn-sm" title="<?= htmlspecialchars($this->gettext('rescan'), ENT_QUOTES, 'UTF-8') ?>">
                            <span class="mf-icon">🔄</span> <?= htmlspecialchars($this->gettext('rescan'), ENT_QUOTES, 'UTF-8') ?>
                        </button>
                        <button type="button" id="mf-btn-reset-dismissed" class="btn btn-outline-secondary btn-sm" title="<?= htmlspecialchars($this->gettext('reset_dismissed'), ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($this->gettext('reset_dismissed'), ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    </div>
                </div>

                <!-- Navigation Tabs & Counters -->
                <div class="mf-tabs-bar">
                    <div class="mf-tabs">
                        <button type="button" class="btn btn-mf-tab active" data-tab="duplicates">
                            <span class="mf-tab-icon">👥</span>
                            <span class="mf-tab-label"><?= htmlspecialchars($this->gettext('duplicates_tab'), ENT_QUOTES, 'UTF-8') ?></span>
                            <span id="mf-badge-duplicates-count" class="mf-badge primary">0</span>
                        </button>
                        <button type="button" class="btn btn-mf-tab" data-tab="frequent">
                            <span class="mf-tab-icon">💡</span>
                            <span class="mf-tab-label"><?= htmlspecialchars($this->gettext('frequent_tab'), ENT_QUOTES, 'UTF-8') ?></span>
                            <span id="mf-badge-frequent-count" class="mf-badge accent">0</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Global Notifications / Alerts -->
            <div id="mf-alert-container"></div>

            <!-- Tab 1: Merge Duplicates -->
            <div id="mf-tab-duplicates" class="mf-tab-pane active">
                <div class="mf-section-header">
                    <div class="mf-section-info">
                        <h3 class="mf-section-title"><?= htmlspecialchars($this->gettext('merge_duplicates_heading'), ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="mf-section-desc"><?= htmlspecialchars($this->gettext('merge_duplicates_desc'), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="mf-section-actions">
                        <button type="button" id="mf-btn-merge-all" class="btn btn-primary btn-sm mf-btn-action" disabled>
                            <span class="mf-icon">⚡</span> <?= htmlspecialchars($this->gettext('merge_all'), ENT_QUOTES, 'UTF-8') ?>
                            <span id="mf-merge-all-count" class="mf-btn-badge"></span>
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="mf-duplicates-loading" class="mf-loading-state">
                    <div class="mf-spinner"></div>
                    <p><?= htmlspecialchars($this->gettext('scanning_duplicates'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <!-- Empty State -->
                <div id="mf-duplicates-empty" class="mf-empty-state d-none">
                    <div class="mf-empty-graphic">
                        <div class="mf-check-circle">✓</div>
                    </div>
                    <h4><?= htmlspecialchars($this->gettext('no_duplicates_title'), ENT_QUOTES, 'UTF-8') ?></h4>
                    <p><?= htmlspecialchars($this->gettext('no_duplicates_desc'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <!-- Duplicate Cards Container -->
                <div id="mf-duplicates-container" class="mf-cards-grid d-none"></div>
            </div>

            <!-- Tab 2: Suggest Additions (Fix Frequent Contacts) -->
            <div id="mf-tab-frequent" class="mf-tab-pane d-none">
                <div class="mf-section-header">
                    <div class="mf-section-info">
                        <h3 class="mf-section-title"><?= htmlspecialchars($this->gettext('frequent_contacts_heading'), ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="mf-section-desc"><?= htmlspecialchars($this->gettext('frequent_contacts_desc'), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="mf-section-actions">
                        <button type="button" id="mf-btn-add-all-suggested" class="btn btn-primary btn-sm mf-btn-action" disabled>
                            <span class="mf-icon">➕</span> <?= htmlspecialchars($this->gettext('add_all'), ENT_QUOTES, 'UTF-8') ?>
                            <span id="mf-add-all-count" class="mf-btn-badge"></span>
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="mf-frequent-loading" class="mf-loading-state">
                    <div class="mf-spinner"></div>
                    <p><?= htmlspecialchars($this->gettext('scanning_frequent'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <!-- Empty State -->
                <div id="mf-frequent-empty" class="mf-empty-state d-none">
                    <div class="mf-empty-graphic">
                        <div class="mf-star-circle">★</div>
                    </div>
                    <h4><?= htmlspecialchars($this->gettext('no_frequent_title'), ENT_QUOTES, 'UTF-8') ?></h4>
                    <p><?= htmlspecialchars($this->gettext('no_frequent_desc'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <!-- Frequent Contacts Cards Container -->
                <div id="mf-frequent-container" class="mf-cards-grid d-none"></div>
            </div>
        </div>
        <?php
        return ob_get_clean() ?: '';
    }

    /**
     * AJAX Action: Scan address books for duplicates and suggest frequent contacts
     */
    public function action_scan(): void
    {
        $duplicates = $this->findDuplicates();
        $frequent = $this->findFrequentContacts();

        $this->jsonResponse([
            'success' => true,
            'duplicates' => $duplicates,
            'duplicates_count' => count($duplicates),
            'frequent' => $frequent,
            'frequent_count' => count($frequent),
        ]);
    }

    /**
     * AJAX Action: Merge a specific pair of contacts
     */
    public function action_merge(): void
    {
        $targetId = trim((string)($this->rcmail->plugins->get_input_value('_target_id', rcube_plugin::INPUT_POST) ?? ''));
        $sourceId = trim((string)($this->rcmail->plugins->get_input_value('_source_id', rcube_plugin::INPUT_POST) ?? ''));
        $abookId  = trim((string)($this->rcmail->plugins->get_input_value('_source', rcube_plugin::INPUT_POST) ?? ''));

        if ($targetId === '' || $sourceId === '') {
            $this->jsonResponse(['success' => false, 'message' => $this->gettext('invalid_contact_ids')]);
            return;
        }

        $result = $this->mergeContacts($targetId, $sourceId, $abookId !== '' ? $abookId : null);
        $this->jsonResponse($result);
    }

    /**
     * AJAX Action: Merge all detected duplicate contact pairs
     */
    public function action_merge_all(): void
    {
        $result = $this->mergeAllDuplicates();
        $this->jsonResponse($result);
    }

    /**
     * AJAX Action: Dismiss a duplicate pair or a suggested contact
     */
    public function action_dismiss(): void
    {
        $type = (string)($this->rcmail->plugins->get_input_value('_type', rcube_plugin::INPUT_POST) ?? 'duplicate');

        if ($type === 'duplicate') {
            $id1 = trim((string)($this->rcmail->plugins->get_input_value('_id1', rcube_plugin::INPUT_POST) ?? ''));
            $id2 = trim((string)($this->rcmail->plugins->get_input_value('_id2', rcube_plugin::INPUT_POST) ?? ''));
            if ($id1 !== '' && $id2 !== '') {
                $this->dismissDuplicate($id1, $id2);
                $this->jsonResponse(['success' => true, 'message' => $this->gettext('dismissed')]);
                return;
            }
        } elseif ($type === 'frequent') {
            $email = trim((string)($this->rcmail->plugins->get_input_value('_email', rcube_plugin::INPUT_POST) ?? ''));
            if ($email !== '') {
                $this->dismissSuggested($email);
                $this->jsonResponse(['success' => true, 'message' => $this->gettext('dismissed')]);
                return;
            }
        }

        $this->jsonResponse(['success' => false, 'message' => 'Invalid dismiss parameters.']);
    }

    /**
     * AJAX Action: Reset all dismissed duplicate pairs and suggested contacts
     */
    public function action_reset_dismissed(): void
    {
        $this->resetDismissed();
        $this->jsonResponse([
            'success' => true,
            'message' => $this->gettext('dismissed_reset_success'),
        ]);
    }

    /**
     * AJAX Action: Add a single suggested frequent contact
     */
    public function action_add_suggested(): void
    {
        $name = trim((string)($this->rcmail->plugins->get_input_value('_name', rcube_plugin::INPUT_POST) ?? ''));
        $email = trim((string)($this->rcmail->plugins->get_input_value('_email', rcube_plugin::INPUT_POST) ?? ''));
        $org = trim((string)($this->rcmail->plugins->get_input_value('_organization', rcube_plugin::INPUT_POST) ?? ''));
        $abookId = trim((string)($this->rcmail->plugins->get_input_value('_source', rcube_plugin::INPUT_POST) ?? ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['success' => false, 'message' => $this->gettext('invalid_email')]);
            return;
        }

        $result = $this->addSuggestedContact($name, $email, $org !== '' ? $org : null, $abookId !== '' ? $abookId : null);
        $this->jsonResponse($result);
    }

    /**
     * AJAX Action: Add all suggested frequent contacts
     */
    public function action_add_all_suggested(): void
    {
        $rawList = $this->rcmail->plugins->get_input_value('_contacts', rcube_plugin::INPUT_POST);
        $abookId = trim((string)($this->rcmail->plugins->get_input_value('_source', rcube_plugin::INPUT_POST) ?? ''));

        $contacts = [];
        if (is_string($rawList)) {
            $decoded = json_decode($rawList, true);
            if (is_array($decoded)) {
                $contacts = $decoded;
            }
        } elseif (is_array($rawList)) {
            $contacts = $rawList;
        }

        if (empty($contacts)) {
            // If no explicit contact list posted, re-scan and add all current recommendations
            $contacts = $this->findFrequentContacts();
        }

        $result = $this->addAllSuggestedContacts($contacts, $abookId !== '' ? $abookId : null);
        $this->jsonResponse($result);
    }

    /**
     * Scan address books and find duplicate contact sets
     */
    public function findDuplicates(?string $filterSourceId = null): array
    {
        $contacts = $this->fetchAllContacts($filterSourceId);
        if (count($contacts) < 2) {
            return [];
        }

        $dismissedPairs = $this->getDismissedDuplicateKeys();

        $duplicates = [];
        $pairKeys = [];

        // Build indices for quick lookup
        $emailIndex = [];
        $phoneIndex = [];
        $nameIndex = [];

        foreach ($contacts as $contact) {
            $cId = (string)$contact['id'];

            // Index emails
            foreach ($contact['emails'] as $email) {
                $email = strtolower(trim($email));
                if ($email !== '') {
                    $emailIndex[$email][] = $cId;
                }
            }

            // Index normalized phones
            foreach ($contact['phones'] as $phone) {
                $normPhone = $this->normalizePhone($phone);
                if (strlen($normPhone) >= 6) {
                    $phoneIndex[$normPhone][] = $cId;
                    if (strlen($normPhone) >= 10) {
                        $phoneIndex['p10:' . substr($normPhone, -10)][] = $cId;
                    }
                    if (strlen($normPhone) >= 9) {
                        $phoneIndex['p9:' . substr($normPhone, -9)][] = $cId;
                    }
                }
            }

            // Index normalized name
            $normName = $this->normalizeName($contact['name']);
            if ($normName !== '' && strlen($normName) >= 3) {
                $nameIndex[$normName][] = $cId;
            }

            // Index first + last combination if different from full name
            if (!empty($contact['firstname']) && !empty($contact['surname'])) {
                $flName = $this->normalizeName($contact['firstname'] . ' ' . $contact['surname']);
                if ($flName !== '' && $flName !== $normName) {
                    $nameIndex[$flName][] = $cId;
                }
                $lfName = $this->normalizeName($contact['surname'] . ' ' . $contact['firstname']);
                if ($lfName !== '' && $lfName !== $normName && $lfName !== $flName) {
                    $nameIndex[$lfName][] = $cId;
                }
            }
        }

        // Map contacts by ID for quick retrieval
        $contactsById = [];
        foreach ($contacts as $c) {
            $contactsById[(string)$c['id']] = $c;
        }

        // 1. Evaluate Email Matches (Highest Confidence: 100%)
        foreach ($emailIndex as $matchedEmail => $cIds) {
            $cIds = array_values(array_unique($cIds));
            if (count($cIds) > 1) {
                for ($i = 0; $i < count($cIds) - 1; $i++) {
                    for ($j = $i + 1; $j < count($cIds); $j++) {
                        $idA = $cIds[$i];
                        $idB = $cIds[$j];
                        $key = $this->makePairKey($idA, $idB);

                        if (isset($dismissedPairs[$key])) {
                            continue;
                        }

                        if (!isset($pairKeys[$key])) {
                            $pairKeys[$key] = [
                                'id_a' => $idA,
                                'id_b' => $idB,
                                'confidence' => 100,
                                'reasons' => [$this->gettext('reason_matching_email') . ': ' . $matchedEmail],
                            ];
                        } else {
                            $pairKeys[$key]['reasons'][] = $this->gettext('reason_matching_email') . ': ' . $matchedEmail;
                        }
                    }
                }
            }
        }

        // 2. Evaluate Phone Matches (High Confidence: 95%)
        foreach ($phoneIndex as $normPhone => $cIds) {
            $cIds = array_values(array_unique($cIds));
            if (count($cIds) > 1) {
                for ($i = 0; $i < count($cIds) - 1; $i++) {
                    for ($j = $i + 1; $j < count($cIds); $j++) {
                        $idA = $cIds[$i];
                        $idB = $cIds[$j];
                        $key = $this->makePairKey($idA, $idB);

                        if (isset($dismissedPairs[$key])) {
                            continue;
                        }

                        $contactA = $contactsById[$idA] ?? null;
                        $displayPhone = $contactA['phones'][0] ?? $normPhone;

                        if (!isset($pairKeys[$key])) {
                            $pairKeys[$key] = [
                                'id_a' => $idA,
                                'id_b' => $idB,
                                'confidence' => 95,
                                'reasons' => [$this->gettext('reason_matching_phone') . ': ' . $displayPhone],
                            ];
                        } else {
                            $pairKeys[$key]['reasons'][] = $this->gettext('reason_matching_phone') . ': ' . $displayPhone;
                        }
                    }
                }
            }
        }

        // 3. Evaluate Name Matches (Confidence: 85%)
        foreach ($nameIndex as $normName => $cIds) {
            $cIds = array_values(array_unique($cIds));
            if (count($cIds) > 1) {
                for ($i = 0; $i < count($cIds) - 1; $i++) {
                    for ($j = $i + 1; $j < count($cIds); $j++) {
                        $idA = $cIds[$i];
                        $idB = $cIds[$j];
                        $key = $this->makePairKey($idA, $idB);

                        if (isset($dismissedPairs[$key])) {
                            continue;
                        }

                        $contactA = $contactsById[$idA] ?? null;
                        $displayName = !empty($contactA['name']) ? $contactA['name'] : $normName;

                        if (!isset($pairKeys[$key])) {
                            $pairKeys[$key] = [
                                'id_a' => $idA,
                                'id_b' => $idB,
                                'confidence' => 85,
                                'reasons' => [$this->gettext('reason_matching_name') . ': ' . $displayName],
                            ];
                        } else {
                            $pairKeys[$key]['reasons'][] = $this->gettext('reason_matching_name') . ': ' . $displayName;
                        }
                    }
                }
            }
        }

        // Assemble the structured results
        foreach ($pairKeys as $pairKey => $pair) {
            $cA = $contactsById[$pair['id_a']] ?? null;
            $cB = $contactsById[$pair['id_b']] ?? null;
            if (!$cA || !$cB) {
                continue;
            }

            // Decide which contact should be primary (target)
            // Primary is contact with highest completeness score or lowest ID
            $scoreA = $this->calculateCompleteness($cA);
            $scoreB = $this->calculateCompleteness($cB);

            $primary = ($scoreA >= $scoreB) ? $cA : $cB;
            $secondary = ($scoreA >= $scoreB) ? $cB : $cA;

            $mergedPreview = $this->buildMergedPreview($primary, $secondary);

            $duplicates[] = [
                'pair_key' => $pairKey,
                'target_id' => (string)$primary['id'],
                'source_id' => (string)$secondary['id'],
                'confidence' => $pair['confidence'],
                'reasons' => array_values(array_unique($pair['reasons'])),
                'primary' => $primary,
                'secondary' => $secondary,
                'preview' => $mergedPreview,
            ];
        }

        // Sort duplicates by highest confidence first
        usort($duplicates, fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        return $duplicates;
    }

    /**
     * Merge two contact cards into a single unified contact profile
     */
    public function mergeContacts(string|int $targetId, string|int $sourceId, ?string $sourceAbookId = null): array
    {
        $targetId = (string)$targetId;
        $sourceId = (string)$sourceId;

        if ($targetId === $sourceId) {
            return ['success' => false, 'message' => 'Cannot merge a contact with itself.'];
        }

        $defaultId = defined('rcube_addressbook::TYPE_CONTACT') ? (string)rcube_addressbook::TYPE_CONTACT : '0';
        $abookId = $sourceAbookId ?? $defaultId;

        $targetRecord = null;
        $sourceRecord = null;
        $abook = null;

        try {
            $abook = $this->rcmail->get_address_book($abookId, true);
            if ($abook) {
                $targetRecord = $abook->get_record($targetId, true);
                $sourceRecord = $abook->get_record($sourceId, true);
            }
        } catch (\Throwable $e) {
            // Ignore addressbook API failure, fallback to DB
        }

        // Database fallback if records not found via addressbook driver
        if ((empty($targetRecord) || empty($sourceRecord)) && method_exists($this->rcmail, 'get_dbh')) {
            $db = $this->rcmail->get_dbh();
            $userId = $this->getUserId();
            if ($db && $userId > 0) {
                $contactsTable = method_exists($db, 'table_name') ? $db->table_name('contacts') : 'contacts';
                if (empty($targetRecord)) {
                    $res = $db->query("SELECT * FROM {$contactsTable} WHERE contact_id = ? AND user_id = ? AND del <> 1", $targetId, $userId);
                    $targetRecord = $db->fetch_assoc($res) ?: null;
                }
                if (empty($sourceRecord)) {
                    $res = $db->query("SELECT * FROM {$contactsTable} WHERE contact_id = ? AND user_id = ? AND del <> 1", $sourceId, $userId);
                    $sourceRecord = $db->fetch_assoc($res) ?: null;
                }
            }
        }

        if (empty($targetRecord) || empty($sourceRecord)) {
            return [
                'success' => false,
                'message' => $this->gettext('contact_not_found'),
            ];
        }

        // Build combined merged data structure
        $mergedSaveData = $this->mergeContactRecords($targetRecord, $sourceRecord);

        $updateSuccess = false;

        // 1. Try updating target via Roundcube Addressbook Driver
        if ($abook && method_exists($abook, 'update')) {
            try {
                $res = $abook->update($targetId, $mergedSaveData);
                $updateSuccess = ($res !== false);
            } catch (\Throwable $e) {
                $updateSuccess = false;
            }
        }

        // 2. Direct database update if addressbook driver update didn't succeed
        if (!$updateSuccess && method_exists($this->rcmail, 'get_dbh')) {
            $db = $this->rcmail->get_dbh();
            $userId = $this->getUserId();
            if ($db && $userId > 0) {
                $contactsTable = method_exists($db, 'table_name') ? $db->table_name('contacts') : 'contacts';
                $db->query(
                    "UPDATE {$contactsTable} SET name = ?, firstname = ?, surname = ?, email = ?, changed = " . $db->now() . " WHERE contact_id = ? AND user_id = ?",
                    $mergedSaveData['name'] ?? '',
                    $mergedSaveData['firstname'] ?? '',
                    $mergedSaveData['surname'] ?? '',
                    $mergedSaveData['email'] ?? '',
                    $targetId,
                    $userId
                );
                $updateSuccess = true;
            }
        }

        if (!$updateSuccess) {
            return [
                'success' => false,
                'message' => $this->gettext('merge_failed'),
            ];
        }

        // 3. Migrate group memberships from source to target
        $this->migrateGroupMemberships($sourceId, $targetId);

        // 4. Delete the secondary source contact
        $deleteSuccess = false;
        if ($abook && method_exists($abook, 'delete')) {
            try {
                $delRes = $abook->delete($sourceId);
                $deleteSuccess = ($delRes !== false);
            } catch (\Throwable $e) {
                $deleteSuccess = false;
            }
        }

        if (!$deleteSuccess && method_exists($this->rcmail, 'get_dbh')) {
            $db = $this->rcmail->get_dbh();
            $userId = $this->getUserId();
            if ($db && $userId > 0) {
                $contactsTable = method_exists($db, 'table_name') ? $db->table_name('contacts') : 'contacts';
                $db->query("UPDATE {$contactsTable} SET del = 1, changed = " . $db->now() . " WHERE contact_id = ? AND user_id = ?", $sourceId, $userId);
                $deleteSuccess = true;
            }
        }

        // Remove any dismissals involving these contacts
        $this->removePairDismissal($targetId, $sourceId);

        return [
            'success' => true,
            'message' => $this->gettext('contacts_merged_success'),
            'target_id' => $targetId,
            'source_id' => $sourceId,
            'merged_name' => $mergedSaveData['name'] ?? '',
        ];
    }

    /**
     * Merge all detected duplicate contact sets in batch
     */
    public function mergeAllDuplicates(?string $sourceAbookId = null): array
    {
        $duplicates = $this->findDuplicates($sourceAbookId);
        if (empty($duplicates)) {
            return [
                'success' => true,
                'merged_count' => 0,
                'message' => $this->gettext('no_duplicates_to_merge'),
            ];
        }

        $mergedCount = 0;
        $failedCount = 0;
        $deletedIds = [];

        foreach ($duplicates as $dup) {
            $targetId = $dup['target_id'];
            $sourceId = $dup['source_id'];

            // Skip if this source or target was already deleted in this batch
            if (isset($deletedIds[$targetId]) || isset($deletedIds[$sourceId])) {
                continue;
            }

            $res = $this->mergeContacts($targetId, $sourceId, $sourceAbookId);
            if (!empty($res['success'])) {
                $mergedCount++;
                $deletedIds[$sourceId] = true;
            } else {
                $failedCount++;
            }
        }

        return [
            'success' => true,
            'merged_count' => $mergedCount,
            'failed_count' => $failedCount,
            'message' => sprintf($this->gettext('batch_merged_summary'), $mergedCount),
        ];
    }

    /**
     * Find unsaved frequent email contacts from mailbox communication history
     */
    public function findFrequentContacts(int $limit = 50): array
    {
        $dismissedEmails = $this->getDismissedFrequentEmails();
        $userEmails = $this->getUserIdentities();
        $userEmailMap = [];
        foreach ($userEmails as $u) {
            $em = strtolower(trim($u['email'] ?? ''));
            if ($em !== '') {
                $userEmailMap[$em] = true;
            }
        }

        // Fetch all currently existing contact emails to avoid suggesting already saved contacts
        $existingContacts = $this->fetchAllContacts();
        $existingEmailMap = [];
        foreach ($existingContacts as $ec) {
            foreach ($ec['emails'] as $em) {
                $existingEmailMap[strtolower(trim($em))] = true;
            }
        }

        $candidates = [];

        // 1. Scan IMAP Mailbox (Sent & INBOX) if storage is available
        try {
            $storage = $this->rcmail->get_storage();
            if ($storage) {
                $sentFolder = $storage->get_special_folder('sent') ?: 'Sent';
                $foldersToScan = [$sentFolder, 'INBOX'];

                foreach ($foldersToScan as $folder) {
                    if (!$storage->folder_exists($folder)) {
                        continue;
                    }

                    // Scan recent 100 messages from folder
                    $headers = $storage->list_headers($folder, 1, 'date', 'DESC', 100);
                    if (is_array($headers) || is_iterable($headers)) {
                        foreach ($headers as $header) {
                            if (!is_object($header)) {
                                continue;
                            }

                            // Collect from To and Cc for Sent folder, and From for INBOX
                            $fields = ($folder === $sentFolder) ? ['to', 'cc'] : ['from'];
                            foreach ($fields as $f) {
                                if (!empty($header->$f)) {
                                    $this->extractCandidateAddresses((string)$header->$f, $candidates, $userEmailMap, $existingEmailMap, $dismissedEmails);
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Defensive error handling for IMAP storage scanning
        }

        // 2. Check Roundcube's collected_addresses table / driver if populated
        if (method_exists($this->rcmail, 'get_dbh')) {
            try {
                $db = $this->rcmail->get_dbh();
                $userId = $this->getUserId();
                if ($db && $userId > 0) {
                    $collectedTable = method_exists($db, 'table_name') ? $db->table_name('collected_addresses') : 'collected_addresses';
                    $res = $db->query("SELECT name, email FROM {$collectedTable} WHERE user_id = ? LIMIT 100", $userId);
                    while ($res && ($row = $db->fetch_assoc($res))) {
                        $raw = trim($row['name'] ? "{$row['name']} <{$row['email']}>" : (string)$row['email']);
                        $this->extractCandidateAddresses($raw, $candidates, $userEmailMap, $existingEmailMap, $dismissedEmails);
                    }
                }
            } catch (\Throwable $e) {
                // Table might not exist in all Roundcube installs, ignore
            }
        }

        // Sort candidates by frequency count descending
        usort($candidates, fn($a, $b) => $b['count'] <=> $a['count']);

        // Limit to desired count
        return array_slice($candidates, 0, $limit);
    }

    /**
     * Add a suggested frequent contact to the default address book
     */
    public function addSuggestedContact(string $name, string $email, ?string $org = null, ?string $sourceAbookId = null): array
    {
        $defaultId = defined('rcube_addressbook::TYPE_CONTACT') ? (string)rcube_addressbook::TYPE_CONTACT : '0';
        $abookId = $sourceAbookId ?? $defaultId;

        $email = strtolower(trim($email));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => $this->gettext('invalid_email')];
        }

        $parts = $this->splitName($name);
        $contactData = [
            'name' => $name !== '' ? $name : $email,
            'firstname' => $parts['firstname'],
            'surname' => $parts['surname'],
            'email' => $email,
            'organization' => $org ?? '',
        ];

        $insertSuccess = false;
        $newContactId = null;

        // Try via Addressbook driver
        try {
            $abook = $this->rcmail->get_address_book($abookId, true);
            if ($abook && method_exists($abook, 'insert')) {
                $newContactId = $abook->insert($contactData);
                $insertSuccess = ($newContactId !== false && $newContactId !== null);
            }
        } catch (\Throwable $e) {
            $insertSuccess = false;
        }

        // Direct DB fallback
        if (!$insertSuccess && method_exists($this->rcmail, 'get_dbh')) {
            try {
                $db = $this->rcmail->get_dbh();
                $userId = $this->getUserId();
                if ($db && $userId > 0) {
                    $contactsTable = method_exists($db, 'table_name') ? $db->table_name('contacts') : 'contacts';
                    $db->query(
                        "INSERT INTO {$contactsTable} (user_id, changed, del, name, email, firstname, surname) VALUES (?, " . $db->now() . ", 0, ?, ?, ?, ?)",
                        $userId,
                        $contactData['name'],
                        $contactData['email'],
                        $contactData['firstname'],
                        $contactData['surname']
                    );
                    $newContactId = $db->insert_id('contacts');
                    $insertSuccess = true;
                }
            } catch (\Throwable $e) {
                $insertSuccess = false;
            }
        }

        if (!$insertSuccess) {
            return ['success' => false, 'message' => $this->gettext('add_contact_failed')];
        }

        // Auto-dismiss from suggested recommendations list
        $this->dismissSuggested($email);

        return [
            'success' => true,
            'contact_id' => (string)$newContactId,
            'name' => $contactData['name'],
            'email' => $email,
            'message' => sprintf($this->gettext('contact_added_success'), $contactData['name']),
        ];
    }

    /**
     * Add all suggested contacts at once
     */
    public function addAllSuggestedContacts(array $contacts, ?string $sourceAbookId = null): array
    {
        if (empty($contacts)) {
            return ['success' => true, 'added_count' => 0, 'message' => 'No contacts to add.'];
        }

        $addedCount = 0;
        $failedCount = 0;

        foreach ($contacts as $c) {
            $email = trim($c['email'] ?? '');
            $name = trim($c['name'] ?? '');
            $org = trim($c['organization'] ?? '');

            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $res = $this->addSuggestedContact($name, $email, $org !== '' ? $org : null, $sourceAbookId);
                if (!empty($res['success'])) {
                    $addedCount++;
                } else {
                    $failedCount++;
                }
            }
        }

        return [
            'success' => true,
            'added_count' => $addedCount,
            'failed_count' => $failedCount,
            'message' => sprintf($this->gettext('batch_added_summary'), $addedCount),
        ];
    }

    /**
     * Dismiss a detected duplicate pair
     */
    public function dismissDuplicate(string|int $id1, string|int $id2): void
    {
        $key = $this->makePairKey((string)$id1, (string)$id2);
        $dismissed = $this->getDismissedDuplicateKeys();
        $dismissed[$key] = true;

        $this->saveUserPref('merge_and_fix_dismissed', array_keys($dismissed));
    }

    /**
     * Dismiss an unsaved frequent email candidate
     */
    public function dismissSuggested(string $email): void
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            return;
        }

        $dismissed = $this->getDismissedFrequentEmails();
        $dismissed[$email] = true;

        $this->saveUserPref('merge_and_fix_dismissed_frequent', array_keys($dismissed));
    }

    /**
     * Remove pair dismissal when contacts are merged
     */
    protected function removePairDismissal(string|int $id1, string|int $id2): void
    {
        $key = $this->makePairKey((string)$id1, (string)$id2);
        $dismissed = $this->getDismissedDuplicateKeys();
        if (isset($dismissed[$key])) {
            unset($dismissed[$key]);
            $this->saveUserPref('merge_and_fix_dismissed', array_keys($dismissed));
        }
    }

    /**
     * Reset all dismissals
     */
    public function resetDismissed(): void
    {
        $this->saveUserPref('merge_and_fix_dismissed', []);
        $this->saveUserPref('merge_and_fix_dismissed_frequent', []);
    }

    /**
     * Fetch and normalize all address book contacts
     */
    public function fetchAllContacts(?string $filterSourceId = null): array
    {
        $contacts = [];
        $defaultId = defined('rcube_addressbook::TYPE_CONTACT') ? (string)rcube_addressbook::TYPE_CONTACT : '0';

        $sources = [];
        try {
            $sources = (array)$this->rcmail->get_address_sources(true);
        } catch (\Throwable $e) {
            $sources = [];
        }

        if (empty($sources)) {
            $sources = [$defaultId => ['id' => $defaultId, 'name' => 'Personal Addresses']];
        }

        foreach ($sources as $sourceKey => $source) {
            $sourceId = (string)($source['id'] ?? $sourceKey ?? $defaultId);
            if ($filterSourceId !== null && $filterSourceId !== $sourceId) {
                continue;
            }

            try {
                $abook = $this->rcmail->get_address_book($sourceId, false, true);
                if ($abook) {
                    if (method_exists($abook, 'set_group')) {
                        $abook->set_group(0);
                    }
                    $records = $abook->list_records();
                    $this->collectNormalizedContacts($records, $sourceId, $contacts);
                }
            } catch (\Throwable $e) {
                // Defensive error handling for driver
            }
        }

        // Database Fallback if 0 contacts returned from API
        if (empty($contacts) && method_exists($this->rcmail, 'get_dbh')) {
            try {
                $db = $this->rcmail->get_dbh();
                $userId = $this->getUserId();
                if ($db && $userId > 0) {
                    $contactsTable = method_exists($db, 'table_name') ? $db->table_name('contacts') : 'contacts';
                    $sql = "SELECT contact_id, name, firstname, surname, email, vcard FROM {$contactsTable} WHERE user_id = ? AND del <> 1";
                    $res = $db->query($sql, $userId);
                    while ($res && ($row = $db->fetch_assoc($res))) {
                        $c = $this->normalizeContactRecord($row, $defaultId);
                        if (!empty($c['id'])) {
                            $contacts[] = $c;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Defensive error suppression
            }
        }

        return $contacts;
    }

    /**
     * Process addressbook results into normalized contact items
     */
    protected function collectNormalizedContacts(mixed $records, string $sourceId, array &$contacts): void
    {
        if (is_object($records) && isset($records->records) && is_array($records->records)) {
            foreach ($records->records as $r) {
                $c = $this->normalizeContactRecord($r, $sourceId);
                if (!empty($c['id'])) {
                    $contacts[] = $c;
                }
            }
        } elseif (is_iterable($records)) {
            foreach ($records as $r) {
                $c = $this->normalizeContactRecord($r, $sourceId);
                if (!empty($c['id'])) {
                    $contacts[] = $c;
                }
            }
        } elseif (is_object($records) && method_exists($records, 'iterate')) {
            while ($r = $records->iterate()) {
                $c = $this->normalizeContactRecord($r, $sourceId);
                if (!empty($c['id'])) {
                    $contacts[] = $c;
                }
            }
        }
    }

    /**
     * Normalize an individual contact record into uniform array
     */
    public function normalizeContactRecord(mixed $record, string $sourceId): array
    {
        if (is_object($record)) {
            if (method_exists($record, 'get_fields')) {
                $record = (array)$record->get_fields();
            } elseif (method_exists($record, 'toArray')) {
                $record = (array)$record->toArray();
            } else {
                $record = (array)$record;
            }
        }

        if (!is_array($record)) {
            return [];
        }

        $id = (string)($record['ID'] ?? $record['contact_id'] ?? $record['id'] ?? '');

        // Extract emails
        $emails = $this->extractEmails($record);

        // Extract phone numbers
        $phones = $this->extractPhones($record);

        // Extract name
        $name = trim((string)($record['name'] ?? ''));
        $firstname = trim((string)($record['firstname'] ?? ''));
        $surname = trim((string)($record['surname'] ?? ''));

        if ($name === '') {
            if ($firstname !== '' || $surname !== '') {
                $name = trim($firstname . ' ' . $surname);
            } elseif (!empty($emails)) {
                $name = $emails[0];
            }
        }

        $organization = trim((string)($record['organization'] ?? $record['company'] ?? ''));
        $jobtitle = trim((string)($record['jobtitle'] ?? ''));
        $notes = trim((string)($record['notes'] ?? ''));

        return [
            'id' => $id,
            'source' => $sourceId,
            'name' => $name,
            'firstname' => $firstname,
            'surname' => $surname,
            'emails' => $emails,
            'phones' => $phones,
            'organization' => $organization,
            'jobtitle' => $jobtitle,
            'notes' => $notes,
            'raw' => $record,
        ];
    }

    /**
     * Deep extraction of all emails from contact record or vcard
     */
    public function extractEmails(array $record): array
    {
        $emails = [];

        // 1. Native get_col_values if available
        if (class_exists('rcube_addressbook') && method_exists('rcube_addressbook', 'get_col_values')) {
            try {
                $res = rcube_addressbook::get_col_values('email', $record, true);
                if (is_array($res)) {
                    foreach ($res as $val) {
                        $this->addValidEmail($val, $emails);
                    }
                }
            } catch (\Throwable $e) {}
        }

        // 2. Scan all email fields
        foreach ($record as $k => $val) {
            $lk = strtolower((string)$k);
            if ($lk === 'email' || str_starts_with($lk, 'email:') || str_starts_with($lk, 'email_') || str_ends_with($lk, '_email')) {
                $this->addValidEmail($val, $emails);
            }
        }

        // 3. Regex match on vCard text
        if (empty($emails) && !empty($record['vcard']) && is_string($record['vcard'])) {
            if (preg_match_all('/(?:EMAIL[^\r\n:]*:[\s]*)([^\r\n]+)/i', $record['vcard'], $m)) {
                foreach ($m[1] as $raw) {
                    $this->addValidEmail($raw, $emails);
                }
            }
        }

        return array_values(array_unique($emails));
    }

    /**
     * Deep extraction of all phone numbers from contact record or vcard
     */
    public function extractPhones(array $record): array
    {
        $phones = [];

        // 1. Native get_col_values if available
        if (class_exists('rcube_addressbook') && method_exists('rcube_addressbook', 'get_col_values')) {
            try {
                $res = rcube_addressbook::get_col_values('phone', $record, true);
                if (is_array($res)) {
                    foreach ($res as $val) {
                        $this->addValidPhone($val, $phones);
                    }
                }
            } catch (\Throwable $e) {}
        }

        // 2. Scan all phone fields
        foreach ($record as $k => $val) {
            $lk = strtolower((string)$k);
            if ($lk === 'phone' || str_starts_with($lk, 'phone:') || str_starts_with($lk, 'phone_') || str_ends_with($lk, '_phone')) {
                $this->addValidPhone($val, $phones);
            }
        }

        // 3. Regex match on vCard text
        if (empty($phones) && !empty($record['vcard']) && is_string($record['vcard'])) {
            if (preg_match_all('/(?:TEL[^\r\n:]*:[\s]*)([^\r\n]+)/i', $record['vcard'], $m)) {
                foreach ($m[1] as $raw) {
                    $this->addValidPhone($raw, $phones);
                }
            }
        }

        return array_values(array_unique($phones));
    }

    /**
     * Merge two contact records into a unified array suitable for saving
     */
    public function mergeContactRecords(array $target, array $source): array
    {
        $merged = $target;

        // Name: Pick the more complete name
        $nameT = trim((string)($target['name'] ?? ''));
        $nameS = trim((string)($source['name'] ?? ''));
        if (strlen($nameS) > strlen($nameT) && !filter_var($nameS, FILTER_VALIDATE_EMAIL)) {
            $merged['name'] = $nameS;
        }

        // Firstname & Surname
        if (empty($merged['firstname']) && !empty($source['firstname'])) {
            $merged['firstname'] = $source['firstname'];
        }
        if (empty($merged['surname']) && !empty($source['surname'])) {
            $merged['surname'] = $source['surname'];
        }

        // Combine emails
        $emailsT = $this->extractEmails($target);
        $emailsS = $this->extractEmails($source);
        $combinedEmails = array_values(array_unique(array_merge($emailsT, $emailsS)));

        // Preserve primary email or set first
        if (!empty($combinedEmails)) {
            $merged['email'] = $combinedEmails[0];
            // Format subtype emails if multiple
            foreach ($combinedEmails as $idx => $em) {
                $subtype = ($idx === 0) ? 'pref' : ($idx === 1 ? 'work' : 'home');
                $merged["email:{$subtype}"] = $em;
            }
        }

        // Combine phones
        $phonesT = $this->extractPhones($target);
        $phonesS = $this->extractPhones($source);
        $combinedPhones = array_values(array_unique(array_merge($phonesT, $phonesS)));
        if (!empty($combinedPhones)) {
            $merged['phone'] = $combinedPhones[0];
            foreach ($combinedPhones as $idx => $ph) {
                $subtype = ($idx === 0) ? 'cell' : ($idx === 1 ? 'work' : 'home');
                $merged["phone:{$subtype}"] = $ph;
            }
        }

        // Organization & Job title
        if (empty($merged['organization']) && !empty($source['organization'])) {
            $merged['organization'] = $source['organization'];
        }
        if (empty($merged['jobtitle']) && !empty($source['jobtitle'])) {
            $merged['jobtitle'] = $source['jobtitle'];
        }

        // Notes: Concatenate if different
        $notesT = trim((string)($target['notes'] ?? ''));
        $notesS = trim((string)($source['notes'] ?? ''));
        if ($notesS !== '' && $notesS !== $notesT) {
            $merged['notes'] = $notesT !== '' ? "{$notesT}\n---\n{$notesS}" : $notesS;
        }

        return $merged;
    }

    /**
     * Build preview representation of the combined merged contact
     */
    protected function buildMergedPreview(array $primary, array $secondary): array
    {
        $name = strlen($secondary['name']) > strlen($primary['name']) && !filter_var($secondary['name'], FILTER_VALIDATE_EMAIL)
            ? $secondary['name']
            : $primary['name'];

        $emails = array_values(array_unique(array_merge($primary['emails'], $secondary['emails'])));
        $phones = array_values(array_unique(array_merge($primary['phones'], $secondary['phones'])));

        $org = !empty($primary['organization']) ? $primary['organization'] : ($secondary['organization'] ?? '');
        $job = !empty($primary['jobtitle']) ? $primary['jobtitle'] : ($secondary['jobtitle'] ?? '');

        return [
            'name' => $name,
            'emails' => $emails,
            'phones' => $phones,
            'organization' => $org,
            'jobtitle' => $job,
        ];
    }

    /**
     * Calculate contact profile completeness score
     */
    protected function calculateCompleteness(array $contact): int
    {
        $score = 0;
        if (!empty($contact['name']) && !filter_var($contact['name'], FILTER_VALIDATE_EMAIL)) {
            $score += 15;
        }
        if (!empty($contact['firstname'])) {
            $score += 10;
        }
        if (!empty($contact['surname'])) {
            $score += 10;
        }
        $score += count($contact['emails']) * 15;
        $score += count($contact['phones']) * 10;
        if (!empty($contact['organization'])) {
            $score += 10;
        }
        if (!empty($contact['jobtitle'])) {
            $score += 5;
        }
        if (!empty($contact['notes'])) {
            $score += 5;
        }
        return $score;
    }

    /**
     * Migrate group memberships in contactgroupmembers
     */
    protected function migrateGroupMemberships(string $sourceContactId, string $targetContactId): void
    {
        if (method_exists($this->rcmail, 'get_dbh')) {
            try {
                $db = $this->rcmail->get_dbh();
                if ($db) {
                    $cgmTable = method_exists($db, 'table_name') ? $db->table_name('contactgroupmembers') : 'contactgroupmembers';
                    // Find groups secondary contact belongs to
                    $res = $db->query("SELECT contactgroup_id FROM {$cgmTable} WHERE contact_id = ?", $sourceContactId);
                    while ($res && ($row = $db->fetch_assoc($res))) {
                        $groupId = $row['contactgroup_id'];
                        // Check if target is already in this group
                        $check = $db->query("SELECT contactgroup_id FROM {$cgmTable} WHERE contact_id = ? AND contactgroup_id = ?", $targetContactId, $groupId);
                        if (!$check || !$db->fetch_assoc($check)) {
                            // Assign target to group
                            $db->query("INSERT INTO {$cgmTable} (contactgroup_id, contact_id, created) VALUES (?, ?, " . $db->now() . ")", $groupId, $targetContactId);
                        }
                    }
                    // Remove source from group memberships
                    $db->query("DELETE FROM {$cgmTable} WHERE contact_id = ?", $sourceContactId);
                }
            } catch (\Throwable $e) {
                // Defensive suppression
            }
        }
    }

    /**
     * Extract candidate email addresses from header string
     */
    protected function extractCandidateAddresses(
        string $headerString,
        array &$candidates,
        array $userEmails,
        array $existingEmails,
        array $dismissedEmails
    ): void {
        $addresses = [];

        if (class_exists('rcube_mime') && method_exists('rcube_mime', 'decode_address_list')) {
            try {
                $addresses = (array)rcube_mime::decode_address_list($headerString);
            } catch (\Throwable $e) {
                $addresses = [];
            }
        }

        if (empty($addresses)) {
            // Regex fallback
            if (preg_match_all('/(?:["\']?([^"\'<>\r\n]*)["\']?\s*)?<([^>@\s]+@[^>@\s]+)>/i', $headerString, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $addresses[] = [
                        'name' => trim($m[1]),
                        'mailto' => trim($m[2]),
                    ];
                }
            } elseif (filter_var(trim($headerString), FILTER_VALIDATE_EMAIL)) {
                $addresses[] = [
                    'name' => '',
                    'mailto' => trim($headerString),
                ];
            }
        }

        foreach ($addresses as $item) {
            $email = strtolower(trim((string)($item['mailto'] ?? $item['email'] ?? '')));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            // Exclude user's own email identities
            if (isset($userEmails[$email])) {
                continue;
            }

            // Exclude already saved contacts
            if (isset($existingEmails[$email])) {
                continue;
            }

            // Exclude dismissed suggestions
            if (isset($dismissedEmails[$email])) {
                continue;
            }

            // Exclude automated/noreply addresses
            if ($this->isAutomatedEmail($email)) {
                continue;
            }

            $name = trim((string)($item['name'] ?? ''));

            if (!isset($candidates[$email])) {
                $org = $this->inferOrganizationFromEmail($email);
                $candidates[$email] = [
                    'email' => $email,
                    'name' => $name !== '' ? $name : $this->inferNameFromEmail($email),
                    'organization' => $org,
                    'count' => 1,
                ];
            } else {
                $candidates[$email]['count']++;
                if (empty($candidates[$email]['name']) && $name !== '') {
                    $candidates[$email]['name'] = $name;
                }
            }
        }
    }

    /**
     * Check if email address is an automated robot or no-reply address
     */
    public function isAutomatedEmail(string $email): bool
    {
        $patterns = [
            '/^(noreply|no-reply|donotreply|do-not-reply|bounce|mailer-daemon)@/i',
            '/^(notifications?|alerts?|updates?|billing|support|newsletter|marketing)@/i',
            '/@(reply|mailer|bounces|postmaster)\./i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $email)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Infer company/organization from email domain
     */
    public function inferOrganizationFromEmail(string $email): string
    {
        $parts = explode('@', $email);
        $domain = strtolower($parts[1] ?? '');
        if ($domain === '') {
            return '';
        }

        // Ignore common freemail and ISP domains
        $genericDomains = [
            'gmail.com', 'googlemail.com', 'outlook.com', 'hotmail.com', 'live.com',
            'yahoo.com', 'yahoo.co.uk', 'icloud.com', 'me.com', 'mac.com',
            'proton.me', 'protonmail.com', 'aol.com', 'zoho.com', 'mail.com',
            'gmx.com', 'gmx.de', 'web.de', 'ziggo.nl', 'kpnmail.nl'
        ];

        if (in_array($domain, $genericDomains, true)) {
            return '';
        }

        // Extract domain root
        $domainParts = explode('.', $domain);
        if (count($domainParts) >= 2) {
            $company = $domainParts[0];
            return ucfirst($company);
        }

        return '';
    }

    /**
     * Infer a readable human name from email handle if no display name is present
     */
    public function inferNameFromEmail(string $email): string
    {
        $parts = explode('@', $email);
        $local = $parts[0] ?? '';
        $tokens = preg_split('/[._\-+]/', $local);
        if (empty($tokens)) {
            return $email;
        }

        $formatted = array_map(fn($t) => ucfirst(strtolower($t)), $tokens);
        return implode(' ', $formatted);
    }

    /**
     * Split full name into firstname and surname
     */
    public function splitName(string $name): array
    {
        $name = trim($name);
        if ($name === '') {
            return ['firstname' => '', 'surname' => ''];
        }

        $parts = preg_split('/\s+/', $name);
        if (count($parts) === 1) {
            return ['firstname' => $parts[0], 'surname' => ''];
        }

        $surname = array_pop($parts);
        $firstname = implode(' ', $parts);
        return ['firstname' => $firstname, 'surname' => $surname];
    }

    /**
     * Helper to validate and add an email
     */
    protected function addValidEmail(mixed $val, array &$emails): void
    {
        if (is_string($val)) {
            $tokens = preg_split('/[\r\n,;\x00]+/', $val);
            if (is_array($tokens)) {
                foreach ($tokens as $token) {
                    $token = trim($token);
                    if (empty($token)) {
                        continue;
                    }
                    if (preg_match('/<([^>]+)>/', $token, $m)) {
                        $candidate = trim($m[1]);
                    } else {
                        $candidate = $token;
                    }
                    if (filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                        $emails[] = strtolower($candidate);
                    }
                }
            }
        } elseif (is_array($val) || is_iterable($val)) {
            foreach ($val as $sub) {
                $this->addValidEmail($sub, $emails);
            }
        }
    }

    /**
     * Helper to validate and add a phone number
     */
    protected function addValidPhone(mixed $val, array &$phones): void
    {
        if (is_string($val)) {
            $phone = trim($val);
            if (strlen($this->normalizePhone($phone)) >= 5) {
                $phones[] = $phone;
            }
        } elseif (is_array($val) || is_iterable($val)) {
            foreach ($val as $sub) {
                $this->addValidPhone($sub, $phones);
            }
        }
    }

    /**
     * Normalize phone number to digits only
     */
    public function normalizePhone(string $phone): string
    {
        return (string)preg_replace('/[^\d]/', '', $phone);
    }

    /**
     * Normalize name for matching
     */
    public function normalizeName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = (string)preg_replace('/[.,_\-\/\(\)]/', ' ', $name);
        return (string)preg_replace('/\s+/', ' ', $name);
    }

    /**
     * Generate canonical pair key
     */
    public function makePairKey(string $id1, string $id2): string
    {
        return ($id1 < $id2) ? "{$id1}:{$id2}" : "{$id2}:{$id1}";
    }

    /**
     * Get user dismissed duplicate pairs
     */
    protected function getDismissedDuplicateKeys(): array
    {
        $prefs = $this->getUserPref('merge_and_fix_dismissed') ?: [];
        $res = [];
        if (is_array($prefs)) {
            foreach ($prefs as $k) {
                $res[(string)$k] = true;
            }
        }
        return $res;
    }

    /**
     * Get user dismissed frequent contact emails
     */
    protected function getDismissedFrequentEmails(): array
    {
        $prefs = $this->getUserPref('merge_and_fix_dismissed_frequent') ?: [];
        $res = [];
        if (is_array($prefs)) {
            foreach ($prefs as $e) {
                $res[strtolower((string)$e)] = true;
            }
        }
        return $res;
    }

    /**
     * Read a user preference
     */
    protected function getUserPref(string $key): mixed
    {
        if (is_object($this->rcmail->user) && method_exists($this->rcmail->user, 'get_prefs')) {
            $prefs = $this->rcmail->user->get_prefs();
            return $prefs[$key] ?? null;
        }
        return null;
    }

    /**
     * Save a user preference
     */
    protected function saveUserPref(string $key, mixed $value): void
    {
        if (is_object($this->rcmail->user) && method_exists($this->rcmail->user, 'save_prefs')) {
            $this->rcmail->user->save_prefs([$key => $value]);
        }
    }

    /**
     * Get logged-in user ID
     */
    protected function getUserId(): int
    {
        if (is_object($this->rcmail->user) && isset($this->rcmail->user->ID)) {
            return (int)$this->rcmail->user->ID;
        } elseif (method_exists($this->rcmail, 'get_user_id')) {
            return (int)$this->rcmail->get_user_id();
        }
        return 0;
    }

    /**
     * Get user email identities
     */
    public function getUserIdentities(): array
    {
        if (is_object($this->rcmail->user) && method_exists($this->rcmail->user, 'list_identities')) {
            return (array)$this->rcmail->user->list_identities();
        }
        return [];
    }

    /**
     * Send JSON response
     */
    public function jsonResponse(array $data): void
    {
        if (is_object($this->rcmail->output) && method_exists($this->rcmail->output, 'json_response')) {
            $this->rcmail->output->json_response($data);
            return;
        }

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data);
        exit;
    }
}
