<?php

/**
 * Automated test suite for Task 1:
 * Independent Styling for the Compose Button (Skin & Preferences)
 * across xskin and customizr plugins.
 */

declare(strict_types=1);

function assert_true(bool $expr, string $message): void
{
    if (!$expr) {
        echo "FAILED: {$message}\n";
        exit(1);
    }
    echo "PASSED: {$message}\n";
}

echo "=== Running Compose Button Styling Test Suite ===\n\n";

$repoRoot = dirname(__DIR__);

// Test 1: xskin Plugin Configuration & Schema Integrity
echo "--- Test 1: xskin Plugin Configuration & Schema Integrity ---\n";
$xskinContent = file_get_contents($repoRoot . '/Extra context/plugins/xskin/xskin.php');
assert_true($xskinContent !== false, "xskin.php is readable");

assert_true(
    strpos($xskinContent, "'compose_button_bg_color' => ['type' => 'string', 'default' => '']") !== false,
    "xskin.php includes compose_button_bg_color in \$configSchema"
);
assert_true(
    strpos($xskinContent, "'compose_button_text_color' => ['type' => 'string', 'default' => '']") !== false,
    "xskin.php includes compose_button_text_color in \$configSchema"
);

// Test 2: xskin Look & Feel Settings Controls
echo "\n--- Test 2: xskin Look & Feel UI Controls ---\n";
assert_true(
    strpos($xskinContent, 'setting_compose_button_bg_color') !== false,
    "xskin.php references setting_compose_button_bg_color label"
);
assert_true(
    strpos($xskinContent, 'setting_compose_button_text_color') !== false,
    "xskin.php references setting_compose_button_text_color label"
);
assert_true(
    strpos($xskinContent, "'name' => \$field") !== false,
    "xskin.php renders HTML input with field name"
);
assert_true(
    strpos($xskinContent, "xskin.applyCustomColor('{\$field}', this.value)") !== false,
    "xskin.php binds real-time JS preview handler for custom colors"
);

// Test 3: xskin Preview Card Compose Button
echo "\n--- Test 3: xskin Preview Card Compose Button ---\n";
assert_true(
    strpos($xskinContent, 'id="preview-fab-btn"') !== false,
    "xskin.php includes preview-fab-btn to visualize compose button"
);
assert_true(
    strpos($xskinContent, 'class="button compose"') !== false,
    "xskin.php preview card uses standard compose class 'button compose'"
);

// Test 4: xskin CSS Generation & Selectors Specificity
echo "\n--- Test 4: xskin CSS Rules & Selectors Specificity ---\n";
assert_true(
    strpos($xskinContent, '--compose-btn-bg') !== false,
    "xskin.php defines --compose-btn-bg CSS custom property"
);
assert_true(
    strpos($xskinContent, '--compose-btn-color') !== false,
    "xskin.php defines --compose-btn-color CSS custom property"
);
assert_true(
    strpos($xskinContent, '#compose-plus') !== false,
    "xskin.php CSS rules target #compose-plus"
);
assert_true(
    strpos($xskinContent, 'a.button.compose') !== false,
    "xskin.php CSS rules target a.button.compose"
);
assert_true(
    strpos($xskinContent, '.floating-action-buttons a.button.compose') !== false,
    "xskin.php CSS rules target mobile floating action buttons"
);
assert_true(
    strpos($xskinContent, '.btn.compose') !== false,
    "xskin.php CSS rules target .btn.compose"
);
assert_true(
    strpos($xskinContent, 'filter: brightness(0.92)') !== false,
    "xskin.php defines hover contrast state"
);
assert_true(
    strpos($xskinContent, 'filter: brightness(0.85)') !== false,
    "xskin.php defines active press state"
);
assert_true(
    strpos($xskinContent, 'outline: 2px solid') !== false,
    "xskin.php defines accessible focus state outline"
);

// Test 5: xskin Preference Persistence & Fallback Logic
echo "\n--- Test 5: xskin Preference Persistence & Backward Compatibility ---\n";
assert_true(
    strpos($xskinContent, "\$arg['prefs']['custom_compose_bg'] = strtoupper(\$val);") !== false,
    "xskin.php syncs compose_button_bg_color to custom_compose_bg for backward compatibility"
);
assert_true(
    strpos($xskinContent, "\$compose_bg = \$this->rcmail->config->get('compose_button_bg_color', \$this->rcmail->config->get('custom_compose_bg'));") !== false,
    "xskin.php falls back gracefully to custom_compose_bg if compose_button_bg_color is not set"
);

// Test 6: xskin Localization Entries
echo "\n--- Test 6: xskin Localization Entries ---\n";
$xskinLoc = file_get_contents($repoRoot . '/Extra context/plugins/xskin/localization/en_US.inc');
assert_true($xskinLoc !== false, "xskin en_US.inc is readable");
assert_true(
    strpos($xskinLoc, "\$labels['setting_compose_button_bg_color']") !== false,
    "en_US.inc defines setting_compose_button_bg_color"
);
assert_true(
    strpos($xskinLoc, "\$labels['setting_compose_button_text_color']") !== false,
    "en_US.inc defines setting_compose_button_text_color"
);

// Test 7: customizr Plugin Independent Compose Button Styling
echo "\n--- Test 7: customizr Plugin Independent Compose Button Styling ---\n";
$customizrContent = file_get_contents($repoRoot . '/Extra context/plugins/customizr/customizr.php');
assert_true($customizrContent !== false, "customizr.php is readable");
assert_true(
    strpos($customizrContent, 'private $compose_button_bg_color') !== false,
    "customizr.php defines compose_button_bg_color property"
);
assert_true(
    strpos($customizrContent, 'private $compose_button_text_color') !== false,
    "customizr.php defines compose_button_text_color property"
);
assert_true(
    strpos($customizrContent, "render_color_field('compose_button_bg_color'") !== false,
    "customizr.php renders color input for compose_button_bg_color"
);
assert_true(
    strpos($customizrContent, "render_color_field('compose_button_text_color'") !== false,
    "customizr.php renders color input for compose_button_text_color"
);
assert_true(
    strpos($customizrContent, "a.button.compose") !== false && strpos($customizrContent, ".btn.compose") !== false,
    "customizr.php injects scoped CSS targeting compose buttons across skins"
);

echo "\nAll Compose Button Styling tests PASSED successfully!\n";
