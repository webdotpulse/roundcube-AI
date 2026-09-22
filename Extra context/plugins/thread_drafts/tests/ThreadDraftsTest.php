<?php

/**
 * Test Suite for Roundcube Thread Drafts Plugin
 */

// Load Roundcube environment if available
$rc_path = '/home/koen/Downloads/Roundcube/roundcubemail-1.7.4';
if (file_exists($rc_path . '/program/include/iniset.php')) {
    require_once $rc_path . '/program/include/iniset.php';
}

if (!class_exists('rcube_plugin')) {
    abstract class rcube_plugin
    {
        public function load_config(): void {}
        public function add_texts($dir, $bool): void {}
        public function add_hook($hook, $callback): void {}
        public function include_script($file): void {}
        public function include_stylesheet($file): void {}
        public function gettext($key): string { return $key; }
    }
}

if (!class_exists('rcube')) {
    class rcube
    {
        public $storage;
        public $task = 'mail';
        public $action = '';
        public $config;

        public function __construct()
        {
            $this->config = new class {
                public function get($key, $default = null) { return $default; }
            };
        }

        public static function get_instance() { return new self(); }

        public static function Q($str): string
        {
            return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
        }

        public static function SQ($str): string
        {
            return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
        }
    }
    class rcmail extends rcube {}
}

if (!class_exists('rcube_message_header')) {
    class rcube_message_header
    {
        public $uid;
        public $subject;
        public $msgid;
        public $in_reply_to;
        public $references;
        public $depth = 0;
        public $has_children = false;
        public $parent_uid;
        public $folder;
        public $size = 0;
        public array $flags = [];
        public array $list_flags = [];
        public $list_cols = [];
        public $to;

        public function get($key)
        {
            return $this->$key ?? null;
        }
    }
}

if (!class_exists('rcube_mime')) {
    class rcube_mime
    {
        public static function decode_header($s): string
        {
            return (string)$s;
        }
    }
}

require_once __DIR__ . '/../thread_drafts.php';

/**
 * Test harness class exposing protected methods for testing
 */
class TestableThreadDrafts extends thread_drafts
{
    public function __construct()
    {
        // Don't call parent constructor to allow standalone testing
    }

    public function test_find_matching_parent($draft, $by_msgid, $by_subject, $subject_fallback = false)
    {
        return $this->find_matching_parent($draft, $by_msgid, $by_subject, $subject_fallback);
    }

    public function test_insert_drafts_into_threads($messages, $drafts, $drafts_mbox, $subject_fallback = false)
    {
        return $this->insert_drafts_into_threads($messages, $drafts, $drafts_mbox, $subject_fallback);
    }

    public function test_insert_replies_into_threads($messages, $replies, $sent_mbox, $subject_fallback = false)
    {
        return $this->insert_replies_into_threads($messages, $replies, $sent_mbox, $subject_fallback);
    }

    public function test_insert_conversation_items($messages, $replies, $drafts, $sent_mbox, $drafts_mbox, $subject_fallback = false)
    {
        return $this->insert_conversation_items($messages, $replies, $drafts, $sent_mbox, $drafts_mbox, $subject_fallback);
    }

    public function test_prepare_draft_header($draft, $parent_uid, $depth, $drafts_mbox)
    {
        return $this->prepare_draft_header($draft, $parent_uid, $depth, $drafts_mbox);
    }

    public function test_prepare_reply_header($reply, $parent_uid, $depth, $sent_mbox)
    {
        return $this->prepare_reply_header($reply, $parent_uid, $depth, $sent_mbox);
    }
}

class ThreadDraftsTestRunner
{
    private $passed = 0;
    private $failed = 0;

    public function assert($condition, $message)
    {
        if ($condition) {
            echo "  [PASS] $message\n";
            $this->passed++;
        } else {
            echo "  [FAIL] $message\n";
            $this->failed++;
        }
    }

    public function assertEquals($expected, $actual, $message)
    {
        $condition = ($expected === $actual);
        if (!$condition) {
            $message .= " (Expected: " . var_export($expected, true) . ", Actual: " . var_export($actual, true) . ")";
        }
        $this->assert($condition, $message);
    }

    public function run()
    {
        echo "Running Thread Drafts Plugin Test Suite...\n\n";

        $this->testCleanMessageId();
        $this->testParseReferences();
        $this->testNormalizeSubject();
        $this->testFindMatchingParent();
        $this->testHierarchyInsertionSingleMessage();
        $this->testHierarchyInsertionMultiLevelThread();
        $this->testMultipleDraftsInSameThread();
        $this->testComposeSanitization();
        $this->testDraftHeaderPreparation();
        $this->testReplyHeaderPreparation();
        $this->testHierarchyInsertionSingleMessageWithReply();
        $this->testHierarchyInsertionCombinedReplyAndDraft();
        $this->testHierarchyInsertionDialogueSequence();
        $this->testMultipleRepliesInSameThread();
        $this->testAutoCollapseInboxConfigAndEnvironment();

        echo "\n----------------------------------------\n";
        echo "Tests Completed: " . ($this->passed + $this->failed) . "\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        echo "----------------------------------------\n";

        return $this->failed === 0 ? 0 : 1;
    }

    private function testCleanMessageId()
    {
        echo "Test: Message-ID Cleaning\n";
        $this->assertEquals('msg1@example.com', thread_drafts::clean_message_id('<msg1@example.com>'), 'Standard bracketed ID');
        $this->assertEquals('msg2@example.com', thread_drafts::clean_message_id("  <msg2@example.com>\r\n"), 'Whitespace and newlines trimmed');
        $this->assertEquals('plain.id@test.org', thread_drafts::clean_message_id('plain.id@test.org'), 'Unbracketed plain ID');
        $this->assertEquals('', thread_drafts::clean_message_id(''), 'Empty string handling');
        $this->assertEquals('', thread_drafts::clean_message_id(null), 'Null handling');
    }

    private function testParseReferences()
    {
        echo "Test: References Header Parsing\n";
        $raw = '<ref1@a.org> <ref2@b.org> <ref3@c.org>';
        $refs = thread_drafts::parse_references($raw);
        $this->assertEquals(['ref1@a.org', 'ref2@b.org', 'ref3@c.org'], $refs, 'Parse multiple bracketed references');

        $arr = ['<refA@x.org>', '<refB@y.org>'];
        $refsArr = thread_drafts::parse_references($arr);
        $this->assertEquals(['refA@x.org', 'refB@y.org'], $refsArr, 'Parse array of references');

        $empty = thread_drafts::parse_references('');
        $this->assertEquals([], $empty, 'Empty references returns empty array');
    }

    private function testNormalizeSubject()
    {
        echo "Test: Subject Normalization\n";
        $this->assertEquals('quarterly review', thread_drafts::normalize_subject('Re: Quarterly Review'), 'Strip standard Re:');
        $this->assertEquals('system update', thread_drafts::normalize_subject('Fwd: Re: [Ticket #42] System Update'), 'Strip nested Fwd and Re');
        $this->assertEquals('project launch', thread_drafts::normalize_subject('Antw: Project launch'), 'Strip multilingual prefixes');
    }

    private function testFindMatchingParent()
    {
        echo "Test: Parent Matching Logic\n";
        $plugin = new TestableThreadDrafts();

        $by_msgid = [
            'root@example.com' => 100,
            'reply1@example.com' => 101,
            'reply2@example.com' => 102,
        ];

        // 1. Direct In-Reply-To match
        $draft1 = new rcube_message_header();
        $draft1->in_reply_to = '<reply2@example.com>';
        $this->assertEquals(102, $plugin->test_find_matching_parent($draft1, $by_msgid, []), 'Matches directly on In-Reply-To');

        // 2. References match (picking newest referenced ID)
        $draft2 = new rcube_message_header();
        $draft2->references = '<root@example.com> <reply1@example.com>';
        $this->assertEquals(101, $plugin->test_find_matching_parent($draft2, $by_msgid, []), 'Matches newest message from References list');

        // 3. In-Reply-To takes precedence over References
        $draft3 = new rcube_message_header();
        $draft3->in_reply_to = '<reply1@example.com>';
        $draft3->references = '<root@example.com>';
        $this->assertEquals(101, $plugin->test_find_matching_parent($draft3, $by_msgid, []), 'In-Reply-To takes precedence over older references');

        // 4. Unmatched returns null
        $draft4 = new rcube_message_header();
        $draft4->in_reply_to = '<unknown@example.com>';
        $draft4->references = '<unknown-root@example.com>';
        $this->assertEquals(null, $plugin->test_find_matching_parent($draft4, $by_msgid, []), 'Returns null for unrelated draft');
    }

    private function testHierarchyInsertionSingleMessage()
    {
        echo "Test: Hierarchy Insertion for Single Message Thread\n";
        $plugin = new TestableThreadDrafts();

        $msg1 = new rcube_message_header();
        $msg1->uid = 10;
        $msg1->messageID = '<msg1@example.com>';
        $msg1->depth = 0;
        $msg1->has_children = false;
        $msg1->subject = 'Discussion';

        $draft = new rcube_message_header();
        $draft->uid = 50;
        $draft->in_reply_to = '<msg1@example.com>';
        $draft->subject = 'Re: Discussion';
        $draft->size = 1024;
        $draft->to = 'client@example.com';

        $result = $plugin->test_insert_drafts_into_threads([$msg1], [$draft], 'Drafts');

        $this->assertEquals(2, count($result), 'Total message count is 2 after inserting draft');
        $this->assertEquals(10, $result[0]->uid, 'First row is root message');
        $this->assertEquals(true, $result[0]->has_children, 'Root message has_children promoted to true');
        $this->assertEquals(1, $result[0]->list_flags['has_draft'], 'Root message flagged with has_draft');

        $draftRow = $result[1];
        $this->assertEquals('50-Drafts', $draftRow->uid, 'Draft UID formatted as 50-Drafts');
        $this->assertEquals('Drafts', $draftRow->folder, 'Draft folder is Drafts');
        $this->assertEquals(1, $draftRow->depth, 'Draft depth is 1 (child of depth 0)');
        $this->assertEquals(10, $draftRow->parent_uid, 'Draft parent_uid is 10');
        $this->assertEquals(true, $draftRow->list_flags['skip_mbox_check'], 'Draft skip_mbox_check is true');
        $this->assertEquals(1, $draftRow->list_flags['is_draft'], 'Draft is_draft flag is 1');
    }

    private function testHierarchyInsertionMultiLevelThread()
    {
        echo "Test: Hierarchy Insertion for Multi-Level Thread\n";
        $plugin = new TestableThreadDrafts();

        // Thread 1
        $root = new rcube_message_header();
        $root->uid = 100;
        $root->messageID = '<root@example.com>';
        $root->depth = 0;
        $root->has_children = true;

        $child1 = new rcube_message_header();
        $child1->uid = 101;
        $child1->messageID = '<child1@example.com>';
        $child1->depth = 1;
        $child1->parent_uid = 100;
        $child1->has_children = false;

        // Thread 2
        $root2 = new rcube_message_header();
        $root2->uid = 200;
        $root2->messageID = '<root2@example.com>';
        $root2->depth = 0;
        $root2->has_children = false;

        // Draft replying to Child 1
        $draft = new rcube_message_header();
        $draft->uid = 77;
        $draft->in_reply_to = '<child1@example.com>';
        $draft->size = 500;

        $messages = [$root, $child1, $root2];
        $result = $plugin->test_insert_drafts_into_threads($messages, [$draft], 'Drafts');

        $this->assertEquals(4, count($result), 'Result has 4 items');
        $this->assertEquals(100, $result[0]->uid, 'Index 0 is root 100');
        $this->assertEquals(1, $result[0]->list_flags['has_draft'], 'Root 100 has has_draft flag');
        $this->assertEquals(101, $result[1]->uid, 'Index 1 is child 101');
        $this->assertEquals(true, $result[1]->has_children, 'Child 101 now has_children = true');

        // Draft inserted at Index 2
        $this->assertEquals('77-Drafts', $result[2]->uid, 'Index 2 is the draft');
        $this->assertEquals(2, $result[2]->depth, 'Draft depth is 2 (child of depth 1)');
        $this->assertEquals(101, $result[2]->parent_uid, 'Draft parent_uid is 101');

        // Thread 2 follows
        $this->assertEquals(200, $result[3]->uid, 'Index 3 is independent root 200');
        $this->assertEquals(false, $result[3]->has_children, 'Root 200 unchanged has_children = false');
    }

    private function testMultipleDraftsInSameThread()
    {
        echo "Test: Multiple Drafts in Same Thread\n";
        $plugin = new TestableThreadDrafts();

        $root = new rcube_message_header();
        $root->uid = 500;
        $root->messageID = '<project@example.com>';
        $root->depth = 0;
        $root->has_children = false;

        $draft1 = new rcube_message_header();
        $draft1->uid = 1;
        $draft1->in_reply_to = '<project@example.com>';
        $draft1->size = 200;

        $draft2 = new rcube_message_header();
        $draft2->uid = 2;
        $draft2->in_reply_to = '<project@example.com>';
        $draft2->size = 300;

        $result = $plugin->test_insert_drafts_into_threads([$root], [$draft1, $draft2], 'Drafts');

        $this->assertEquals(3, count($result), '3 total messages');
        $this->assertEquals(500, $result[0]->uid, 'Root at index 0');
        $this->assertEquals('1-Drafts', $result[1]->uid, 'First draft at index 1');
        $this->assertEquals('2-Drafts', $result[2]->uid, 'Second draft at index 2');
        $this->assertEquals(1, $result[1]->depth, 'Draft 1 has depth 1');
        $this->assertEquals(1, $result[2]->depth, 'Draft 2 has depth 1');
    }

    private function testComposeSanitization()
    {
        echo "Test: Compose Parameter Sanitization\n";
        $plugin = new TestableThreadDrafts();

        $args1 = ['param' => ['draft_uid' => '42-Drafts']];
        $sanitized1 = $plugin->message_compose($args1);
        $this->assertEquals('42', $sanitized1['param']['draft_uid'], 'Strips -Drafts suffix');

        $args2 = ['param' => ['draft_uid' => '99']];
        $sanitized2 = $plugin->message_compose($args2);
        $this->assertEquals('99', $sanitized2['param']['draft_uid'], 'Preserves clean numeric UID');

        $args3 = ['param' => []];
        $sanitized3 = $plugin->message_compose($args3);
        $this->assert(!isset($sanitized3['param']['draft_uid']), 'Handles empty param gracefully');
    }

    private function testDraftHeaderPreparation()
    {
        echo "Test: Draft Header Preparation\n";
        $plugin = new TestableThreadDrafts();

        $draft = new rcube_message_header();
        $draft->uid = '88';
        $draft->subject = 'Followup';
        $draft->size = 0; // Empty size should be guarded
        $draft->to = 'partner@company.com';

        $prepared = $plugin->test_prepare_draft_header($draft, 12, 1, 'Drafts');

        $this->assertEquals('88-Drafts', $prepared->uid, 'UID formatted with folder');
        $this->assertEquals('Drafts', $prepared->folder, 'Folder is set to Drafts');
        $this->assertEquals(12, $prepared->parent_uid, 'Parent UID set');
        $this->assertEquals(1, $prepared->depth, 'Depth set');
        $this->assert($prepared->size >= 1, 'Size is at least 1 byte');
        $this->assertEquals(true, $prepared->flags['skip_mbox_check'], 'Flags has skip_mbox_check');
        $this->assertEquals(1, $prepared->list_flags['is_draft'], 'List flags has is_draft');
    }

    private function testReplyHeaderPreparation()
    {
        echo "Test: Reply Header Preparation\n";
        $plugin = new TestableThreadDrafts();

        $reply = new rcube_message_header();
        $reply->uid = '150';
        $reply->subject = 'Re: Proposal';
        $reply->size = 2048;
        $reply->to = 'colleague@example.com';

        $prepared = $plugin->test_prepare_reply_header($reply, 10, 1, 'Sent');

        $this->assertEquals('150-Sent', $prepared->uid, 'UID formatted with Sent folder');
        $this->assertEquals('Sent', $prepared->folder, 'Folder is set to Sent');
        $this->assertEquals(10, $prepared->parent_uid, 'Parent UID set');
        $this->assertEquals(1, $prepared->depth, 'Depth set');
        $this->assertEquals(true, $prepared->flags['skip_mbox_check'], 'Flags has skip_mbox_check');
        $this->assertEquals(true, $prepared->list_flags['skip_mbox_check'], 'List flags has skip_mbox_check');
        $this->assertEquals(1, $prepared->list_flags['is_reply'], 'List flags has is_reply');
        $this->assertEquals(1, $prepared->list_flags['is_sent'], 'List flags has is_sent');
        $this->assertEquals('Sent', $prepared->list_flags['mbox'], 'List flags has mbox Sent');
        $this->assert(strpos($prepared->list_cols['fromto'], 'To:') !== false, 'From/To column formatted with To: prefix');
    }

    private function testHierarchyInsertionSingleMessageWithReply()
    {
        echo "Test: Hierarchy Insertion for Single Message with Sent Reply\n";
        $plugin = new TestableThreadDrafts();

        $msg1 = new rcube_message_header();
        $msg1->uid = 10;
        $msg1->messageID = '<msg1@example.com>';
        $msg1->depth = 0;
        $msg1->has_children = false;
        $msg1->subject = 'Job Inquiry';

        $reply = new rcube_message_header();
        $reply->uid = 201;
        $reply->messageID = '<rep1@example.com>';
        $reply->in_reply_to = '<msg1@example.com>';
        $reply->subject = 'Re: Job Inquiry';
        $reply->size = 1200;
        $reply->to = 'applicant@example.com';

        $result = $plugin->test_insert_replies_into_threads([$msg1], [$reply], 'Sent');

        $this->assertEquals(2, count($result), 'Total message count is 2 after inserting sent reply');
        $this->assertEquals(10, $result[0]->uid, 'First row is root message');
        $this->assertEquals(true, $result[0]->has_children, 'Root message has_children promoted to true');
        $this->assertEquals(1, $result[0]->list_flags['has_replies'], 'Root message flagged with has_replies');

        $replyRow = $result[1];
        $this->assertEquals('201-Sent', $replyRow->uid, 'Reply UID formatted as 201-Sent');
        $this->assertEquals('Sent', $replyRow->folder, 'Reply folder is Sent');
        $this->assertEquals(1, $replyRow->depth, 'Reply depth is 1 (child of depth 0)');
        $this->assertEquals(10, $replyRow->parent_uid, 'Reply parent_uid is 10');
        $this->assertEquals(true, $replyRow->list_flags['skip_mbox_check'], 'Reply skip_mbox_check is true');
        $this->assertEquals(1, $replyRow->list_flags['is_reply'], 'Reply is_reply flag is 1');
    }

    private function testHierarchyInsertionCombinedReplyAndDraft()
    {
        echo "Test: Hierarchy Insertion for Combined Sent Reply and Draft in Same Thread\n";
        $plugin = new TestableThreadDrafts();

        $root = new rcube_message_header();
        $root->uid = 10;
        $root->messageID = '<ticket-1@example.com>';
        $root->depth = 0;
        $root->timestamp = 1000;

        $reply = new rcube_message_header();
        $reply->uid = 201;
        $reply->messageID = '<ticket-reply-1@example.com>';
        $reply->in_reply_to = '<ticket-1@example.com>';
        $reply->timestamp = 2000;

        $draft = new rcube_message_header();
        $draft->uid = 50;
        $draft->in_reply_to = '<ticket-reply-1@example.com>';
        $draft->timestamp = 3000;

        $result = $plugin->test_insert_conversation_items([$root], [$reply], [$draft], 'Sent', 'Drafts');

        $this->assertEquals(3, count($result), 'Total message count is 3 with both reply and draft');
        $this->assertEquals(10, $result[0]->uid, 'Root at index 0');
        $this->assertEquals(1, $result[0]->list_flags['has_replies'], 'Root flagged with has_replies');
        $this->assertEquals(1, $result[0]->list_flags['has_draft'], 'Root flagged with has_draft');

        // Sent reply
        $this->assertEquals('201-Sent', $result[1]->uid, 'Sent reply at index 1');
        $this->assertEquals(1, $result[1]->depth, 'Sent reply has depth 1');
        $this->assertEquals(10, $result[1]->parent_uid, 'Sent reply parent_uid is 10');
        $this->assertEquals(true, $result[1]->has_children, 'Sent reply has_children is true');

        // Draft attached under sent reply
        $this->assertEquals('50-Drafts', $result[2]->uid, 'Draft at index 2');
        $this->assertEquals(2, $result[2]->depth, 'Draft has depth 2');
        $this->assertEquals('201-Sent', $result[2]->parent_uid, 'Draft parent_uid is 201-Sent');
    }

    private function testHierarchyInsertionDialogueSequence()
    {
        echo "Test: Dialogue Sequence: Incoming -> Sent Reply -> Incoming -> Draft\n";
        $plugin = new TestableThreadDrafts();

        // Message 1 from Client
        $m1 = new rcube_message_header();
        $m1->uid = 10;
        $m1->messageID = '<client-init@example.com>';
        $m1->depth = 0;
        $m1->timestamp = 1000;

        // Message 2 from Client (originally in INBOX, referencing Sent reply)
        $m2 = new rcube_message_header();
        $m2->uid = 15;
        $m2->messageID = '<client-feedback@example.com>';
        $m2->in_reply_to = '<agent-answer@example.com>';
        $m2->references = '<client-init@example.com> <agent-answer@example.com>';
        $m2->depth = 1;
        $m2->timestamp = 3000;

        // Sent reply from Agent
        $rep = new rcube_message_header();
        $rep->uid = 201;
        $rep->messageID = '<agent-answer@example.com>';
        $rep->in_reply_to = '<client-init@example.com>';
        $rep->timestamp = 2000;

        // Active Draft from Agent replying to client feedback
        $drf = new rcube_message_header();
        $drf->uid = 77;
        $drf->messageID = '<agent-wip@example.com>';
        $drf->in_reply_to = '<client-feedback@example.com>';
        $drf->timestamp = 4000;

        $result = $plugin->test_insert_conversation_items([$m1, $m2], [$rep], [$drf], 'Sent', 'Drafts');

        $this->assertEquals(4, count($result), 'Total message count is 4 in dialogue sequence');
        $this->assertEquals(10, $result[0]->uid, 'Index 0 is Client initial message');
        $this->assertEquals(0, $result[0]->depth, 'Client initial message has depth 0');

        $this->assertEquals('201-Sent', $result[1]->uid, 'Index 1 is Agent sent reply');
        $this->assertEquals(1, $result[1]->depth, 'Agent sent reply has depth 1');
        $this->assertEquals(10, $result[1]->parent_uid, 'Agent sent reply parent_uid is 10');

        $this->assertEquals(15, $result[2]->uid, 'Index 2 is Client feedback message');
        $this->assertEquals(2, $result[2]->depth, 'Client feedback message has depth 2');
        $this->assertEquals('201-Sent', $result[2]->parent_uid, 'Client feedback parent_uid is 201-Sent');

        $this->assertEquals('77-Drafts', $result[3]->uid, 'Index 3 is Agent active draft');
        $this->assertEquals(3, $result[3]->depth, 'Agent draft has depth 3');
        $this->assertEquals(15, $result[3]->parent_uid, 'Agent draft parent_uid is 15');
    }

    private function testMultipleRepliesInSameThread()
    {
        echo "Test: Multiple Sent Replies in Same Thread\n";
        $plugin = new TestableThreadDrafts();

        $root = new rcube_message_header();
        $root->uid = 100;
        $root->messageID = '<project@example.com>';
        $root->depth = 0;
        $root->timestamp = 1000;

        $r1 = new rcube_message_header();
        $r1->uid = 301;
        $r1->messageID = '<rep1@example.com>';
        $r1->in_reply_to = '<project@example.com>';
        $r1->timestamp = 2000;

        $r2 = new rcube_message_header();
        $r2->uid = 302;
        $r2->messageID = '<rep2@example.com>';
        $r2->in_reply_to = '<rep1@example.com>';
        $r2->timestamp = 3000;

        $result = $plugin->test_insert_replies_into_threads([$root], [$r1, $r2], 'Sent');

        $this->assertEquals(3, count($result), 'Total message count is 3 with multiple replies');
        $this->assertEquals(100, $result[0]->uid, 'Index 0 is root');
        $this->assertEquals('301-Sent', $result[1]->uid, 'Index 1 is first reply');
        $this->assertEquals(1, $result[1]->depth, 'First reply has depth 1');
        $this->assertEquals(100, $result[1]->parent_uid, 'First reply parent is 100');

        $this->assertEquals('302-Sent', $result[2]->uid, 'Index 2 is second reply');
        $this->assertEquals(2, $result[2]->depth, 'Second reply has depth 2');
        $this->assertEquals('301-Sent', $result[2]->parent_uid, 'Second reply parent is 301-Sent');
    }

    private function testAutoCollapseInboxConfigAndEnvironment()
    {
        echo "Test: Standard Collapsed Threads on Inbox Opening\n";
        $configFile = dirname(__DIR__) . '/config.inc.php.dist';
        $this->assert(file_exists($configFile), 'config.inc.php.dist exists');

        $config = [];
        include $configFile;

        $this->assert(isset($config['thread_drafts_auto_collapse_inbox']), 'thread_drafts_auto_collapse_inbox is configured in config.inc.php.dist');
        $this->assertEquals(true, $config['thread_drafts_auto_collapse_inbox'], 'thread_drafts_auto_collapse_inbox defaults to true');

        // Check plugin method
        $plugin = new TestableThreadDrafts();
        $this->assertEquals(true, $plugin->is_auto_collapse_inbox_enabled(), 'is_auto_collapse_inbox_enabled returns true by default');

        // Check client script thread_drafts.js has comprehensive inbox auto collapse logic
        $jsFile = dirname(__DIR__) . '/thread_drafts.js';
        $this->assert(file_exists($jsFile), 'thread_drafts.js exists');
        $jsContent = file_get_contents($jsFile);
        $this->assert(strpos($jsContent, 'ensure_inbox_threads_collapsed') !== false, 'thread_drafts.js contains ensure_inbox_threads_collapsed');
        $this->assert(strpos($jsContent, 'is_inbox_folder') !== false, 'thread_drafts.js contains is_inbox_folder detection');
        $this->assert(strpos($jsContent, 'autoexpand_threads = 0') !== false, 'thread_drafts.js enforces autoexpand_threads = 0 for Inbox');
        $this->assert(strpos($jsContent, 'collapse_all') !== false, 'thread_drafts.js calls collapse_all');
        $this->assert(strpos($jsContent, 'expand_threads') !== false, 'thread_drafts.js intercepts expand_threads');
        $this->assert(strpos($jsContent, 'add_message_row') !== false, 'thread_drafts.js intercepts add_message_row');
        $this->assert(strpos($jsContent, 'select_folder') !== false, 'thread_drafts.js intercepts select_folder');
        $this->assert(strpos($jsContent, 'list_mailbox') !== false, 'thread_drafts.js intercepts list_mailbox');
        $this->assert(strpos($jsContent, 'padding-left') !== false, 'thread_drafts.js inspects padding-left for direct DOM child collapse');
    }
}


// Run tests
$runner = new ThreadDraftsTestRunner();
exit($runner->run());
