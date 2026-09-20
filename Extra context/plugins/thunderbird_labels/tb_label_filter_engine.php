<?php

/**
 * Thunderbird Labels - Incoming Mail Filter & Rules Engine
 *
 * Evaluates incoming or existing emails against user-defined filter rules
 * and automatically applies labels, moves messages to target folders,
 * and/or marks messages as read.
 *
 * @license MIT
 */

class tb_label_filter_engine
{
    public const PREFS_KEY = 'tb_label_filters';

    /**
     * Get all configured filter rules for the current user.
     *
     * @param rcmail $rc
     * @return array List of filter rule definitions
     */
    public static function get_rules($rc): array
    {
        $rules = $rc->config->get(self::PREFS_KEY, []);
        if (!is_array($rules)) {
            $rules = [];
        }
        return array_values($rules);
    }

    /**
     * Save filter rules to user preferences.
     *
     * @param rcmail $rc
     * @param array $rules
     * @return bool
     */
    public static function save_rules($rc, array $rules): bool
    {
        if (!$rc->user) {
            return false;
        }
        return (bool) $rc->user->save_prefs([self::PREFS_KEY => array_values($rules)]);
    }

    /**
     * Creates or updates a filter rule.
     *
     * @param rcmail $rc
     * @param array $rule
     * @return array Saved rule with ID
     */
    public static function save_rule($rc, array $rule): array
    {
        $rules = self::get_rules($rc);
        $rule_id = !empty($rule['id']) ? (string) $rule['id'] : 'rule_' . time() . '_' . mt_rand(100, 999);
        $rule['id'] = $rule_id;
        $rule['name'] = trim($rule['name'] ?? 'Filter Rule');
        $rule['enabled'] = isset($rule['enabled']) ? (bool) $rule['enabled'] : true;
        $rule['scope'] = in_array(strtolower($rule['scope'] ?? 'all'), ['all', 'any']) ? strtolower($rule['scope']) : 'all';
        $rule['conditions'] = is_array($rule['conditions'] ?? null) ? $rule['conditions'] : [];
        $rule['actions'] = is_array($rule['actions'] ?? null) ? $rule['actions'] : [];

        // Ensure actions have consistent array format for labels
        if (!empty($rule['actions']['label']) && is_string($rule['actions']['label'])) {
            $rule['actions']['labels'] = [$rule['actions']['label']];
        } elseif (!empty($rule['actions']['labels']) && is_array($rule['actions']['labels'])) {
            $rule['actions']['labels'] = array_values(array_unique(array_filter($rule['actions']['labels'])));
        } else {
            $rule['actions']['labels'] = [];
        }

        $found = false;
        foreach ($rules as $idx => $existing) {
            if (($existing['id'] ?? '') === $rule_id) {
                $rules[$idx] = $rule;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $rules[] = $rule;
        }

        self::save_rules($rc, $rules);
        return $rule;
    }

    /**
     * Deletes a filter rule by ID.
     *
     * @param rcmail $rc
     * @param string $rule_id
     * @return bool
     */
    public static function delete_rule($rc, string $rule_id): bool
    {
        $rules = self::get_rules($rc);
        $filtered = array_filter($rules, function ($r) use ($rule_id) {
            return ($r['id'] ?? '') !== $rule_id;
        });
        return self::save_rules($rc, array_values($filtered));
    }

    /**
     * Toggles enabled state of a filter rule by ID.
     *
     * @param rcmail $rc
     * @param string $rule_id
     * @param bool|null $enabled
     * @return bool
     */
    public static function toggle_rule($rc, string $rule_id, ?bool $enabled = null): bool
    {
        $rules = self::get_rules($rc);
        $updated = false;
        foreach ($rules as &$r) {
            if (($r['id'] ?? '') === $rule_id) {
                $r['enabled'] = ($enabled !== null) ? $enabled : !($r['enabled'] ?? true);
                $updated = true;
                break;
            }
        }
        if ($updated) {
            self::save_rules($rc, $rules);
        }
        return $updated;
    }

    /**
     * Evaluates a single rule condition against message data.
     *
     * @param array $cond
     * @param array $msg_data ['from' => ..., 'to' => ..., 'cc' => ..., 'subject' => ..., 'body' => ...]
     * @return bool
     */
    public static function evaluate_condition(array $cond, array $msg_data): bool
    {
        $field = strtolower(trim($cond['field'] ?? 'subject'));
        $operator = strtolower(trim($cond['operator'] ?? 'contains'));
        $expected = trim($cond['value'] ?? '');

        if ($expected === '') {
            return true;
        }

        $actual = (string) ($msg_data[$field] ?? '');
        $actual_lower = mb_strtolower($actual, 'UTF-8');
        $expected_lower = mb_strtolower($expected, 'UTF-8');

        switch ($operator) {
            case 'contains':
                return mb_strpos($actual_lower, $expected_lower, 0, 'UTF-8') !== false;

            case 'not_contains':
            case 'does_not_contain':
                return mb_strpos($actual_lower, $expected_lower, 0, 'UTF-8') === false;

            case 'equals':
            case 'is':
                return $actual_lower === $expected_lower;

            case 'not_equals':
            case 'is_not':
                return $actual_lower !== $expected_lower;

            case 'starts_with':
                return mb_strpos($actual_lower, $expected_lower, 0, 'UTF-8') === 0;

            case 'ends_with':
                $len = mb_strlen($expected_lower, 'UTF-8');
                return $len === 0 || mb_substr($actual_lower, -$len, null, 'UTF-8') === $expected_lower;

            case 'regex':
                $delim = '/';
                $safe_regex = $delim . str_replace($delim, '\\' . $delim, $expected) . $delim . 'i';
                return @preg_match($safe_regex, $actual) === 1;

            default:
                return mb_strpos($actual_lower, $expected_lower, 0, 'UTF-8') !== false;
        }
    }

    /**
     * Evaluates whether all/any conditions of a rule match the message.
     *
     * @param array $rule
     * @param array $msg_data
     * @return bool
     */
    public static function matches_rule(array $rule, array $msg_data): bool
    {
        if (empty($rule['enabled'])) {
            return false;
        }

        $conditions = $rule['conditions'] ?? [];
        if (empty($conditions)) {
            return false;
        }

        $scope = strtolower($rule['scope'] ?? 'all');
        $matched_count = 0;

        foreach ($conditions as $cond) {
            $match = self::evaluate_condition($cond, $msg_data);
            if ($match && $scope === 'any') {
                return true;
            }
            if (!$match && $scope === 'all') {
                return false;
            }
            if ($match) {
                $matched_count++;
            }
        }

        return ($scope === 'all') ? ($matched_count === count($conditions)) : ($matched_count > 0);
    }

    /**
     * Applies the actions of a matched rule to an email message.
     *
     * @param rcmail $rc
     * @param int|string $uid
     * @param string $mbox
     * @param array $rule
     * @return array Applied actions summary
     */
    public static function apply_actions($rc, $uid, string $mbox, array $rule): array
    {
        $storage = $rc->get_storage();
        $actions = $rule['actions'] ?? [];
        $applied = [
            'labels' => [],
            'moved_to' => null,
            'marked_read' => false,
        ];

        // 1. Apply Labels (can be multiple)
        $labels = $actions['labels'] ?? [];
        if (!empty($actions['label']) && empty($labels)) {
            $labels = [$actions['label']];
        }

        foreach ($labels as $label_key) {
            $num = null;
            if (preg_match('/^LABEL([0-9]+)$/i', $label_key, $m)) {
                $num = $m[1];
            } elseif (is_numeric($label_key)) {
                $num = $label_key;
            }

            // Flag as $LabelX or custom flag
            $flag = ($num !== null) ? "\$Label{$num}" : "\${$label_key}";
            $storage->set_flag($uid, $flag, $mbox);
            $applied['labels'][] = $label_key;
        }

        // 2. Mark as Read
        if (!empty($actions['mark_read'])) {
            $storage->set_flag($uid, 'SEEN', $mbox);
            $applied['marked_read'] = true;
        }

        // 3. Move to target folder
        $target_folder = trim($actions['folder'] ?? '');
        if (!empty($target_folder) && strcasecmp($target_folder, $mbox) !== 0) {
            $moved = $storage->move_message($uid, $target_folder, $mbox);
            if ($moved) {
                $applied['moved_to'] = $target_folder;
            }
        }

        return $applied;
    }

    /**
     * Processes messages in a mailbox against all active filter rules.
     *
     * @param rcmail $rc
     * @param string $mbox Current mailbox (defaults to INBOX)
     * @param array|null $uids Specific UIDs to evaluate (or null to evaluate unseen)
     * @return array Summary of actions performed
     */
    public static function process_mailbox($rc, string $mbox = 'INBOX', ?array $uids = null): array
    {
        $rules = self::get_rules($rc);
        $active_rules = array_filter($rules, function ($r) {
            return !empty($r['enabled']) && !empty($r['conditions']);
        });

        if (empty($active_rules)) {
            return ['processed' => 0, 'matched' => 0, 'details' => []];
        }

        $storage = $rc->get_storage();
        if ($mbox) {
            $storage->set_folder($mbox);
        }

        if ($uids === null) {
            $search_res = $storage->search($mbox, 'UNSEEN RECENT');
            if (empty($search_res)) {
                $search_res = $storage->search($mbox, 'UNSEEN');
            }
            if (is_object($search_res) && method_exists($search_res, 'get')) {
                $uids = $search_res->get();
            } elseif (is_array($search_res)) {
                $uids = $search_res;
            } else {
                $uids = [];
            }
        }

        if (empty($uids)) {
            return ['processed' => 0, 'matched' => 0, 'details' => []];
        }

        $results = [
            'processed' => 0,
            'matched' => 0,
            'details' => [],
        ];

        // Check whether any active rule needs message body content
        $needs_body = false;
        foreach ($active_rules as $r) {
            foreach ($r['conditions'] ?? [] as $c) {
                if (strtolower($c['field'] ?? '') === 'body') {
                    $needs_body = true;
                    break 2;
                }
            }
        }

        foreach ($uids as $uid) {
            $results['processed']++;
            $header = $storage->get_message_headers($uid, $mbox);
            if (!$header) {
                continue;
            }

            $from = $header->from ?? ($header->sender ?? '');
            $to = is_array($header->to ?? null) ? implode(', ', $header->to) : (string) ($header->to ?? '');
            $cc = is_array($header->cc ?? null) ? implode(', ', $header->cc) : (string) ($header->cc ?? '');
            $subject = (string) ($header->subject ?? '');

            $body = '';
            if ($needs_body) {
                $msg_obj = $storage->get_message($uid, $mbox);
                if ($msg_obj) {
                    $body = (string) $msg_obj->first_text_part();
                }
            }

            $msg_data = [
                'from' => $from,
                'to' => $to,
                'cc' => $cc,
                'subject' => $subject,
                'body' => $body,
            ];

            foreach ($active_rules as $rule) {
                if (self::matches_rule($rule, $msg_data)) {
                    $applied = self::apply_actions($rc, $uid, $mbox, $rule);
                    $results['matched']++;
                    $results['details'][] = [
                        'uid' => $uid,
                        'rule_id' => $rule['id'],
                        'rule_name' => $rule['name'],
                        'actions' => $applied,
                    ];

                    // If message moved to another folder, stop processing subsequent rules for this message
                    if (!empty($applied['moved_to'])) {
                        break;
                    }
                }
            }
        }

        return $results;
    }
}
