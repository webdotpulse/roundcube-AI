<?php

/**
 * Automated test suite for Roundcube Newsletter Plugin (newsletter)
 *
 * Validates:
 * - Database schema definitions (MySQL, SQLite, PostgreSQL)
 * - Deep anti-spam heuristic engine (spam words, ALL CAPS, punctuation, unsubscribe compliance)
 * - RFC 8058 One-Click Unsubscribe headers & HMAC token generation
 * - Dual MIME plaintext alternative generation from HTML
 * - Recipient resolution, deduplication, and syntax validation
 * - Permanent suppression list management (add, check, remove, auto-exclusion)
 * - Dynamic token personalization ({name}, {first_name}, {email}, {unsubscribe_url})
 * - Taskbar button and task registration
 * - Tooltip unclipped sidebar styling in gmail_plus
 * - Compose button removals (Save draft removed from composer, Send Later removed from right sidebar)
 */

declare(strict_types=1);

function assert_test(bool $expr, string $message): void
{
    if (!$expr) {
        echo "FAILED: {$message}\n";
        exit(1);
    }
    echo "PASSED: {$message}\n";
}

echo "=== Running Roundcube Newsletter Plugin Test Suite ===\n\n";

$pluginDir = dirname(__DIR__) . '/Extra context/plugins/newsletter';
$skinDir = dirname(__DIR__) . '/Extra context/skins/gmail_plus';

// --------------------------------------------------------------------------
// Test Suite 1: Database Schema Definitions
// --------------------------------------------------------------------------
echo "--- Test Suite 1: Database Schemas ---\n";

$mysql = file_get_contents($pluginDir . '/SQL/mysql.sql');
assert_test($mysql !== false && str_contains($mysql, 'CREATE TABLE IF NOT EXISTS `newsletter_campaigns`'), "MySQL defines newsletter_campaigns table");
assert_test(str_contains($mysql, '`newsletter_suppressions`'), "MySQL defines newsletter_suppressions table");
assert_test(str_contains($mysql, '`spam_score` INT NOT NULL DEFAULT 0'), "MySQL defines spam_score column");

$sqlite = file_get_contents($pluginDir . '/SQL/sqlite.sql');
assert_test($sqlite !== false && str_contains($sqlite, 'CREATE TABLE IF NOT EXISTS newsletter_campaigns'), "SQLite defines newsletter_campaigns table");
assert_test(str_contains($sqlite, 'CREATE TABLE IF NOT EXISTS newsletter_suppressions'), "SQLite defines newsletter_suppressions table");
assert_test(str_contains($sqlite, 'CREATE UNIQUE INDEX IF NOT EXISTS newsletter_suppressions_user_email'), "SQLite defines unique index on user and email");

$postgres = file_get_contents($pluginDir . '/SQL/postgres.sql');
assert_test($postgres !== false && str_contains($postgres, 'CREATE TABLE IF NOT EXISTS newsletter_campaigns'), "PostgreSQL defines newsletter_campaigns table");
assert_test(str_contains($postgres, 'CREATE TABLE IF NOT EXISTS newsletter_suppressions'), "PostgreSQL defines newsletter_suppressions table");

// --------------------------------------------------------------------------
// Test Suite 2: Mock Environment & Initialization
// --------------------------------------------------------------------------
echo "\n--- Test Suite 2: Plugin Initialization & Registration ---\n";

if (!class_exists('rcube')) {
    class rcube
    {
        private static $inst;
        public $config;
        public $output;
        public $user;
        public $task = 'newsletter';
        public $action = 'index';

        public function __construct()
        {
            $this->config = new rcube_config();
            $this->output = new rcmail_output_mock();
            $this->user = new rcube_user_mock();
        }

        public static function get_instance()
        {
            if (!self::$inst) {
                self::$inst = new self();
            }
            return self::$inst;
        }

        public function get_address_sources($required = false)
        {
            return [
                ['id' => '0', 'name' => 'Personal Addresses']
            ];
        }

        public function get_address_book($id)
        {
            return new rcube_address_book_mock();
        }
    }

    class rcmail extends rcube {}

    class rcube_config
    {
        public array $data = [];
        public function get($key, $default = null)
        {
            return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
        }
        public function set($key, $val): void
        {
            $this->data[$key] = $val;
        }
    }

    class rcmail_output_mock
    {
        public array $env = [];
        public string $pagetitle = '';
        public array $commands = [];
        public $lastJsonResponse = null;

        public function set_env($key, $val): void
        {
            $this->env[$key] = $val;
        }
        public function set_pagetitle($title): void
        {
            $this->pagetitle = $title;
        }
        public function json_response($data): void
        {
            $this->lastJsonResponse = $data;
        }
        public function command(...$args): void
        {
            $this->commands[] = $args;
        }
        public function send($template): void {}
    }

    class rcube_user_mock
    {
        public int $ID = 42;
        public function list_identities()
        {
            return [
                ['email' => 'editor@example.com', 'name' => 'Newsletter Editor']
            ];
        }
    }

    class rcube_address_book_mock
    {
        private array $contacts = [
            ['name' => 'Alice Martin', 'email' => 'alice@example.org', 'firstname' => 'Alice', 'surname' => 'Martin'],
            ['name' => 'Bob Builder', 'email' => 'bob@example.org', 'firstname' => 'Bob', 'surname' => 'Builder'],
            ['name' => 'Charlie Chaplin', 'email' => 'charlie@example.org', 'firstname' => 'Charlie', 'surname' => 'Chaplin'],
        ];
        private int $index = 0;

        public function list_groups(): array
        {
            return [
                ['ID' => 'vip', 'name' => 'VIP Customers'],
                ['ID' => 'team', 'name' => 'Internal Team']
            ];
        }

        public function list_records()
        {
            return $this->contacts;
        }

        public function reset(): void
        {
            $this->index = 0;
        }
    }

    class rcube_plugin
    {
        public $ID;
        public array $actions = [];
        public array $buttons = [];
        public array $hooks = [];
        public array $texts = [];
        public array $scripts = [];
        public array $styles = [];
        public string $registeredTask = '';

        public function load_config(): void {}
        public function add_texts($dir, $bool): void {}
        public function gettext($key): string { return $key; }
        public function register_task($task): void { $this->registeredTask = $task; }
        public function register_action($action, $callback): void { $this->actions[$action] = $callback; }
        public function register_handler($name, $callback): void {}
        public function add_hook($hook, $callback): void { $this->hooks[$hook] = $callback; }
        public function add_button($args, $container): void { $this->buttons[] = ['args' => $args, 'container' => $container]; }
        public function include_script($file): void { $this->scripts[] = $file; }
        public function include_stylesheet($file): void { $this->styles[] = $file; }
    }

    class rcube_utils
    {
        public const INPUT_POST = 1;
        public const INPUT_GPC = 2;
        public static array $mockPost = [];

        public static function get_input_value($key, $mode)
        {
            return self::$mockPost[$key] ?? null;
        }
    }
}

require_once $pluginDir . '/newsletter.php';

$newsletter = new newsletter();
$newsletter->init();

assert_test($newsletter->registeredTask === 'newsletter', "Registers 'newsletter' main task");
assert_test(isset($newsletter->actions['index']), "Registers 'index' action");
assert_test(isset($newsletter->actions['plugin.newsletter-recipients']), "Registers 'plugin.newsletter-recipients' action");
assert_test(isset($newsletter->actions['plugin.newsletter-spam-score']), "Registers 'plugin.newsletter-spam-score' action");
assert_test(isset($newsletter->actions['plugin.newsletter-send-batch']), "Registers 'plugin.newsletter-send-batch' action");
assert_test(isset($newsletter->actions['plugin.newsletter-unsubscribe']), "Registers 'plugin.newsletter-unsubscribe' public action");
assert_test(isset($newsletter->actions['plugin.newsletter-suppressions']), "Registers 'plugin.newsletter-suppressions' action");

$taskbarBtn = null;
foreach ($newsletter->buttons as $b) {
    if ($b['container'] === 'taskbar') {
        $taskbarBtn = $b;
        break;
    }
}
assert_test($taskbarBtn !== null, "Registers taskbar button in 'taskbar' container");
assert_test($taskbarBtn['args']['command'] === 'newsletter', "Taskbar button invokes 'newsletter' command");

// --------------------------------------------------------------------------
// Test Suite 3: Anti-Spam Heuristic Analyzer
// --------------------------------------------------------------------------
echo "\n--- Test Suite 3: Anti-Spam Heuristics Engine ---\n";

// 1. Clean compliant newsletter
$cleanSubject = "Community Updates: Key features launched this month";
$cleanHtml = "<p>Hi {first_name},</p><p>We are delighted to share our latest achievements and feature updates.</p><p><a href=\"{unsubscribe_url}\">Unsubscribe</a></p>";
$cleanAnalysis = $newsletter->analyzeSpamRisk($cleanSubject, $cleanHtml);
assert_test($cleanAnalysis['score'] <= 25, "Compliant newsletter receives Low Risk score ({$cleanAnalysis['score']}/100)");
assert_test($cleanAnalysis['severity'] === 'low', "Compliant newsletter severity is 'low'");

// 2. High Risk: ALL CAPS subject + spam buzzwords + missing unsubscribe
$spamSubject = "FREE MONEY ACT NOW AND DOUBLE YOUR CASH $$$";
$spamHtml = "<h1>CONGRATULATIONS YOU WON!</h1><p>Claim fast cash instantly!</p>";
$spamAnalysis = $newsletter->analyzeSpamRisk($spamSubject, $spamHtml);
assert_test($spamAnalysis['score'] >= 60, "Severe spam patterns trigger High Risk score ({$spamAnalysis['score']}/100)");
assert_test($spamAnalysis['severity'] === 'high', "Severe spam severity is 'high'");

$flagRules = array_column($spamAnalysis['flags'], 'rule');
assert_test(in_array('ALL_CAPS_SUBJECT', $flagRules, true), "Detects ALL_CAPS_SUBJECT violation");
assert_test(in_array('SPAM_TRIGGER_WORDS', $flagRules, true), "Detects SPAM_TRIGGER_WORDS violation");
assert_test(in_array('MISSING_UNSUBSCRIBE', $flagRules, true), "Detects MISSING_UNSUBSCRIBE violation");

// 3. Dangerous HTML tags
$dangerousHtml = "<p>Test</p><script>alert('pwn');</script><a href='{unsubscribe_url}'>unsub</a>";
$dangerAnalysis = $newsletter->analyzeSpamRisk("Normal Subject", $dangerousHtml);
$dangerRules = array_column($dangerAnalysis['flags'], 'rule');
assert_test(in_array('DANGEROUS_HTML_TAGS', $dangerRules, true), "Flags dangerous HTML tags (<script>)");

// --------------------------------------------------------------------------
// Test Suite 4: RFC 8058 One-Click Unsubscribe Headers & HMAC
// --------------------------------------------------------------------------
echo "\n--- Test Suite 4: RFC 8058 Headers & Token Signatures ---\n";

$testEmail = "subscriber@example.com";
$campaignId = "camp_2026_01";
$headers = $newsletter->buildRfc8058Headers($testEmail, $campaignId);

assert_test(isset($headers['List-Unsubscribe']), "Generates List-Unsubscribe header");
assert_test(str_contains($headers['List-Unsubscribe'], 'plugin.newsletter-unsubscribe'), "List-Unsubscribe contains endpoint URL");
assert_test(isset($headers['List-Unsubscribe-Post']), "Generates List-Unsubscribe-Post header");
assert_test($headers['List-Unsubscribe-Post'] === 'List-Unsubscribe=One-Click', "List-Unsubscribe-Post equals 'List-Unsubscribe=One-Click'");
assert_test($headers['Precedence'] === 'bulk', "Header Precedence set to 'bulk'");
assert_test($headers['Auto-Submitted'] === 'auto-generated', "Header Auto-Submitted set to 'auto-generated'");

// Token verification
$token = $newsletter->generateUnsubscribeToken($testEmail, $campaignId);
assert_test(!empty($token), "Generates non-empty HMAC unsubscribe token");
assert_test($newsletter->verifyUnsubscribeToken($testEmail, $campaignId, $token), "Verifies authentic unsubscribe token");
assert_test(!$newsletter->verifyUnsubscribeToken($testEmail, $campaignId, 'tampered_token'), "Rejects forged unsubscribe token");
assert_test(!$newsletter->verifyUnsubscribeToken("attacker@example.com", $campaignId, $token), "Rejects token for mismatched email");

// --------------------------------------------------------------------------
// Test Suite 5: Dual MIME Plaintext Alternative Generation
// --------------------------------------------------------------------------
echo "\n--- Test Suite 5: Dual MIME Plaintext Alternative ---\n";

$richHtml = "<h1>Welcome</h1><p>Check out our <a href=\"https://example.com/docs\">Documentation</a> for details.<br>Stay tuned!</p>";
$plain = $newsletter->generatePlainText($richHtml);
assert_test(!str_contains($plain, '<h1>') && !str_contains($plain, '<p>'), "Strips HTML tags from plaintext alternative");
assert_test(str_contains($plain, 'Documentation (https://example.com/docs)'), "Converts link into readable text with target URL");
assert_test(str_contains($plain, 'Stay tuned!'), "Preserves text following <br> breaks");

// --------------------------------------------------------------------------
// Test Suite 6: Suppression List & Recipient Filtering
// --------------------------------------------------------------------------
echo "\n--- Test Suite 6: Suppression List & Recipient Filtering ---\n";

$suppressEmail = "optout@example.org";
// Ensure clean state
$newsletter->removeSuppression($suppressEmail);
assert_test(!$newsletter->isSuppressed($suppressEmail), "Email is not suppressed initially");

$newsletter->addSuppression($suppressEmail, 'user_unsubscribe');
assert_test($newsletter->isSuppressed($suppressEmail), "addSuppression records email as suppressed");

// Test recipient resolution with custom list
$customInput = "John Doe <john@example.com>\noptout@example.org\nJane Smith <jane@example.com>\njohn@example.com\ninvalid_address";
$res = $newsletter->resolveRecipients('custom', [], $customInput);

$deliverableEmails = array_column($res['deliverable'], 'email');
$suppressedEmails = array_column($res['suppressed'], 'email');

assert_test(in_array('john@example.com', $deliverableEmails, true), "Resolves valid deliverable recipient john@example.com");
assert_test(in_array('jane@example.com', $deliverableEmails, true), "Resolves valid deliverable recipient jane@example.com");
assert_test(count(array_keys($deliverableEmails, 'john@example.com')) === 1, "Deduplicates repeated recipient");
assert_test(!in_array('invalid_address', $deliverableEmails, true), "Filters out malformed email address");
assert_test(in_array('optout@example.org', $suppressedEmails, true), "Auto-excludes suppressed email to suppressed array");
assert_test(!in_array('optout@example.org', $deliverableEmails, true), "Suppressed email omitted from deliverable recipients");

// Clean up suppression
$newsletter->removeSuppression($suppressEmail);
assert_test(!$newsletter->isSuppressed($suppressEmail), "removeSuppression restores email from suppression list");

// --------------------------------------------------------------------------
// Test Suite 7: Dynamic Personalization Tokens
// --------------------------------------------------------------------------
echo "\n--- Test Suite 7: Personalization Tokens ---\n";

$rawSubject = "Special announcement for {first_name}";
$rawHtml = "<p>Hello {name} ({email}),</p><p>Today is {date}.</p><p><a href=\"{unsubscribe_url}\">Unsub</a></p>";
$personalized = $newsletter->personalizeContent($rawSubject, $rawHtml, "sarah@example.com", "Sarah Connor", "camp123");

assert_test($personalized['subject'] === "Special announcement for Sarah", "Replaces {first_name} in subject line");
assert_test(str_contains($personalized['body_html'], "Hello Sarah Connor (sarah@example.com)"), "Replaces {name} and {email} in body");
assert_test(str_contains($personalized['body_html'], "plugin.newsletter-unsubscribe"), "Substitutes {unsubscribe_url} with valid URL");
assert_test(!str_contains($personalized['body_html'], '{date}'), "Substitutes {date} token");

// --------------------------------------------------------------------------
// Test Suite 8: Task 1 Verification (Compose Save & Send Later Removals)
// --------------------------------------------------------------------------
echo "\n--- Test Suite 8: Compose Button Removals (Save & Send Later) ---\n";

$schedCss = file_get_contents(dirname(__DIR__) . '/Extra context/plugins/email_scheduler/email_scheduler.css');
assert_test(str_contains($schedCss, 'button[command="savedraft"]') || str_contains($schedCss, '.formbuttons .save-draft-btn'), "email_scheduler.css hides composer draft save button");
assert_test(str_contains($schedCss, '#messagetoolbar a.send.schedule') && str_contains($schedCss, '#btn-send-later-toolbar'), "email_scheduler.css hides right sidebar toolbar send later button");

$schedJs = file_get_contents(dirname(__DIR__) . '/Extra context/plugins/email_scheduler/email_scheduler.js');
assert_test(str_contains($schedJs, 'button[command="savedraft"]'), "email_scheduler.js removes composer Save button from DOM");
assert_test(str_contains($schedJs, 'btn-send-later-toolbar'), "email_scheduler.js removes toolbar send later button");

// --------------------------------------------------------------------------
// Test Suite 9: Task 2 Verification (Sidebar Tooltip Uncapped Fix)
// --------------------------------------------------------------------------
echo "\n--- Test Suite 9: Sidebar Tooltip Uncapped / Unclipped Fix ---\n";

$skinScss = file_get_contents($skinDir . '/assets/styles/styles.scss');
assert_test(str_contains($skinScss, 'overflow: visible !important;') && str_contains($skinScss, '#layout-menu'), "styles.scss sets #layout-menu overflow to visible !important");
assert_test(str_contains($skinScss, '#taskmenu') && str_contains($skinScss, 'overflow-x: visible !important;'), "styles.scss sets #taskmenu overflow-x to visible !important");
assert_test(str_contains($skinScss, 'right: calc(100% + 10px) !important;'), "styles.scss positions sidebar tooltips to pop out to the left");

$skinCss = file_get_contents($skinDir . '/assets/styles/styles.css');
assert_test(str_contains($skinCss, 'overflow:visible !important') && str_contains($skinCss, '#layout-menu'), "styles.css contains overflow:visible !important for #layout-menu");
assert_test(str_contains($skinCss, '#layout-menu #taskmenu') && str_contains($skinCss, 'overflow-x:visible !important'), "styles.css contains overflow-x:visible !important for #layout-menu #taskmenu");
assert_test(str_contains($skinCss, 'right:calc(100% + 10px) !important'), "styles.css contains unclipped tooltip positioning");

// --------------------------------------------------------------------------
// Test Suite 10: Action Endpoints & Scrolling Layout CSS Fixes
// --------------------------------------------------------------------------
echo "\n--- Test Suite 10: Action Endpoints & Scrolling Layout CSS Fixes ---\n";

// 1. action_spam_score
rcube_utils::$mockPost = [
    'subject' => 'Exclusive Member Updates',
    'body' => '<p>Hello {first_name}, here is your weekly news. <a href="{unsubscribe_url}">Unsubscribe</a></p>',
    'from' => 'newsletter@example.com'
];
$newsletter->action_spam_score();
$res = rcmail::get_instance()->output->lastJsonResponse;
assert_test(is_array($res) && ($res['success'] ?? false) === true, "action_spam_score executes and outputs success");
assert_test(isset($res['score']) && isset($res['severity']), "action_spam_score returns score and severity");

// 2. action_recipients
rcube_utils::$mockPost = [
    'source_type' => 'custom',
    'custom_text' => "Alice Smith <alice@example.com>\nBob Jones <bob@example.com>"
];
$newsletter->action_recipients();
$res = rcmail::get_instance()->output->lastJsonResponse;
assert_test(is_array($res) && ($res['success'] ?? false) === true, "action_recipients executes and outputs success");
assert_test(($res['deliverable_count'] ?? 0) === 2, "action_recipients resolves 2 custom recipients");

// 3. extractContactRecord with object mock
class mock_contact_record {
    public function get_fields(): array {
        return ['email' => 'object_contact@example.com', 'firstname' => 'Object', 'surname' => 'User'];
    }
}
$extracted = $newsletter->extractContactRecord(new mock_contact_record());
assert_test($extracted['email'] === 'object_contact@example.com', "extractContactRecord handles contact objects with get_fields()");
assert_test($extracted['name'] === 'Object User', "extractContactRecord handles contact object names");

// 4. Scrolling CSS verification
$newsletterCss = file_get_contents($pluginDir . '/newsletter.css');
assert_test(str_contains($newsletterCss, 'body.task-newsletter #layout-content') && str_contains($newsletterCss, 'overflow-y: auto !important;'), "newsletter.css enables overflow-y: auto on #layout-content");
assert_test(str_contains($newsletterCss, '.newsletter-studio-wrapper') && str_contains($newsletterCss, 'flex: 1 0 auto;'), "newsletter.css configures .newsletter-studio-wrapper for unclipped flex expansion");

// 5. Template layout classes
$renderedUi = $newsletter->render_newsletter_ui();
assert_test(str_contains($renderedUi, 'class="newsletter-studio-wrapper content formcontent scroller boxcontent uibox"'), "render_newsletter_ui includes content, formcontent, and scroller classes");

echo "\n*** ALL NEWSLETTER & SYSTEM TESTS PASSED SUCCESSFULLY (11/11) ***\n";
