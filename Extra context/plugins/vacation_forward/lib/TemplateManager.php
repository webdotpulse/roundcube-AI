<?php

/**
 * Multi-Language Template Engine for vacation_forward Roundcube Plugin
 *
 * Handles language detection, domain rule routing, placeholder interpolation,
 * and template storage.
 *
 * @license MIT
 * @author Webdotpulse & LifePrisma Contributors
 */

declare(strict_types=1);

class VacationForwardTemplateManager
{
    /**
     * Stop words dictionary for heuristic text language detection.
     */
    private const STOP_WORDS = [
        'nl' => [' en ', ' het ', ' een ', ' van ', ' voor ', ' niet ', ' maar ', ' ook ', ' met ', ' naar ', ' over ', ' graag ', ' bedankt ', ' groeten '],
        'de' => [' und ', ' der ', ' die ', ' das ', ' ein ', ' eine ', ' nicht ', ' mit ', ' für ', ' auf ', ' vielen dank ', ' grüße ', ' bitte '],
        'fr' => [' et ', ' le ', ' la ', ' les ', ' un ', ' une ', ' dans ', ' pour ', ' avec ', ' vous ', ' merci ', ' cordialement ', ' bonjour '],
        'es' => [' y ', ' el ', ' la ', ' los ', ' las ', ' un ', ' una ', ' con ', ' para ', ' por ', ' gracias ', ' saludos ', ' atentamente '],
        'en' => [' and ', ' the ', ' with ', ' for ', ' this ', ' have ', ' from ', ' your ', ' please ', ' thanks ', ' regarding ', ' hello ', ' regards ']
    ];

    /**
     * TLD mapping for language hints.
     */
    private const TLD_MAP = [
        'nl' => 'nl',
        'be' => 'nl', // Default Flemish/Dutch, or checked against body
        'de' => 'de',
        'at' => 'de',
        'ch' => 'de',
        'fr' => 'fr',
        'es' => 'es',
        'mx' => 'es',
        'ar' => 'es',
        'co' => 'es',
        'cl' => 'es',
        'uk' => 'en',
        'us' => 'en',
        'au' => 'en',
        'ca' => 'en',
        'nz' => 'en',
        'ie' => 'en',
    ];

    /**
     * Get default predefined templates if user has no templates configured.
     */
    public static function getDefaultTemplates(): array
    {
        return [
            [
                'id' => 'tpl_en_default',
                'name' => 'English Out of Office (Default)',
                'lang' => 'en',
                'is_default' => true,
                'domain_rule' => '',
                'subject' => 'Out of Office: {ORIGINAL_SUBJECT}',
                'body' => "Hello {SENDER_NAME},\n\nThank you for reaching out. I am currently out of the office starting from {START_DATE} until {END_DATE} with limited access to email.\n\nI will review your message upon my return on {RETURN_DATE}.\n\nFor urgent inquiries, please contact our support team.\n\nKind regards,\n{USER_NAME}",
            ],
            [
                'id' => 'tpl_nl_vacation',
                'name' => 'Nederlands Afwezigheidsbericht',
                'lang' => 'nl',
                'is_default' => false,
                'domain_rule' => '',
                'subject' => 'Automatisch antwoord: {ORIGINAL_SUBJECT}',
                'body' => "Beste {SENDER_NAME},\n\nBedankt voor uw bericht. Ik ben momenteel afwezig van {START_DATE} tot en met {END_DATE} en heb beperkte toegang tot mijn e-mail.\n\nNa mijn terugkeer op {RETURN_DATE} zal ik uw bericht zo spoedig mogelijk beantwoorden.\n\nVoor dringende zaken kunt u contact opnemen met onze algemene helpdesk.\n\nMet vriendelijke groet,\n{USER_NAME}",
            ],
            [
                'id' => 'tpl_de_urlaub',
                'name' => 'Deutsch Abwesenheitsnotiz',
                'lang' => 'de',
                'is_default' => false,
                'domain_rule' => '',
                'subject' => 'Automatische Antwort: {ORIGINAL_SUBJECT}',
                'body' => "Guten Tag {SENDER_NAME},\n\nVielen Dank für Ihre Nachricht. Ich bin vom {START_DATE} bis einschließlich {END_DATE} nicht im Büro erreichbar.\n\nIhre E-Mail wird nach meiner Rückkehr am {RETURN_DATE} bearbeitet.\n\nIn dringenden Fällen wenden Sie sich bitte an unsere Zentrale.\n\nMit freundlichen Grüßen,\n{USER_NAME}",
            ],
            [
                'id' => 'tpl_fr_absence',
                'name' => 'Français Message d\'absence',
                'lang' => 'fr',
                'is_default' => false,
                'domain_rule' => '',
                'subject' => 'Message d\'absence: {ORIGINAL_SUBJECT}',
                'body' => "Bonjour {SENDER_NAME},\n\nMerci pour votre message. Je suis actuellement absent(e) du {START_DATE} au {END_DATE} inclus.\n\nJe prendrai connaissance de votre courriel à mon retour le {RETURN_DATE}.\n\nEn cas d'urgence, veuillez contacter notre équipe d'assistance.\n\nCordialement,\n{USER_NAME}",
            ],
            [
                'id' => 'tpl_es_vacaciones',
                'name' => 'Español Fuera de la oficina',
                'lang' => 'es',
                'is_default' => false,
                'domain_rule' => '',
                'subject' => 'Fuera de la oficina: {ORIGINAL_SUBJECT}',
                'body' => "Hola {SENDER_NAME},\n\nGracias por su mensaje. Me encuentro fuera de la oficina desde el {START_DATE} hasta el {END_DATE}.\n\nResponderé a su correo a mi regreso el {RETURN_DATE}.\n\nPara asuntos urgentes, por favor comuníquese con nuestro equipo de soporte.\n\nAtentamente,\n{USER_NAME}",
            ],
        ];
    }

    /**
     * Detect language of the incoming message.
     */
    public static function detectLanguage(string $senderEmail, string $subject, string $body, array $headers = []): string
    {
        // 1. Check Content-Language / Accept-Language headers
        foreach (['Content-Language', 'content-language', 'Accept-Language', 'accept-language'] as $h) {
            if (!empty($headers[$h])) {
                $hl = strtolower(trim((string)$headers[$h]));
                $code = substr($hl, 0, 2);
                if (in_array($code, ['en', 'nl', 'de', 'fr', 'es', 'it'], true)) {
                    return $code;
                }
            }
        }

        // 2. Text heuristics via stop-words
        $combinedText = ' ' . strtolower(preg_replace('/[^\p{L}\s]/u', ' ', $subject . ' ' . substr($body, 0, 1000))) . ' ';
        $scores = ['en' => 0, 'nl' => 0, 'de' => 0, 'fr' => 0, 'es' => 0];

        foreach (self::STOP_WORDS as $lang => $words) {
            foreach ($words as $w) {
                if (str_contains($combinedText, $w)) {
                    $scores[$lang]++;
                }
            }
        }

        arsort($scores);
        $topScore = reset($scores);
        $topLang = key($scores);

        if ($topScore >= 2) {
            return (string)$topLang;
        }

        // 3. Sender domain TLD heuristic
        if (preg_match('/\.([a-z]{2,3})$/i', $senderEmail, $m)) {
            $tld = strtolower($m[1]);
            if (isset(self::TLD_MAP[$tld])) {
                return self::TLD_MAP[$tld];
            }
        }

        return 'en';
    }

    /**
     * Select best matching template from user's template list.
     *
     * @param array $templates User's configured templates
     * @param string $senderEmail Incoming sender email
     * @param string $subject Incoming subject
     * @param string $body Incoming body snippet
     * @param array $headers MIME headers
     * @return array [template, matched_reason, detected_lang]
     */
    public static function resolveTemplate(
        array $templates,
        string $senderEmail,
        string $subject,
        string $body,
        array $headers = []
    ): array {
        if (empty($templates)) {
            $templates = self::getDefaultTemplates();
        }

        $detectedLang = self::detectLanguage($senderEmail, $subject, $body, $headers);
        $cleanSender = strtolower(trim($senderEmail));

        // 1. Check for specific domain/address match rules first
        foreach ($templates as $tpl) {
            $rule = strtolower(trim($tpl['domain_rule'] ?? ''));
            if ($rule !== '') {
                // Rule can be @domain.com or full user@domain.com
                if (str_starts_with($rule, '@')) {
                    if (str_ends_with($cleanSender, $rule)) {
                        return [$tpl, "Domain rule match: {$rule}", $detectedLang];
                    }
                } elseif ($cleanSender === $rule || str_contains($cleanSender, $rule)) {
                    return [$tpl, "Sender filter match: {$rule}", $detectedLang];
                }
            }
        }

        // 2. Match by detected language (for templates without domain constraints)
        foreach ($templates as $tpl) {
            $rule = trim($tpl['domain_rule'] ?? '');
            if ($rule === '' && !empty($tpl['lang']) && strtolower($tpl['lang']) === $detectedLang) {
                return [$tpl, "Language match: {$detectedLang}", $detectedLang];
            }
        }

        // 3. Fallback to designated default template
        foreach ($templates as $tpl) {
            if (!empty($tpl['is_default'])) {
                return [$tpl, "Default fallback template", $detectedLang];
            }
        }

        // 4. Return first template available without domain constraint
        foreach ($templates as $tpl) {
            if (empty($tpl['domain_rule'])) {
                return [$tpl, "First available template", $detectedLang];
            }
        }

        return [$templates[0], "Fallback template", $detectedLang];
    }

    /**
     * Replace placeholders with dynamic values.
     */
    public static function interpolate(
        string $content,
        array $context
    ): string {
        $search = [
            '{START_DATE}',
            '{END_DATE}',
            '{RETURN_DATE}',
            '{SENDER_NAME}',
            '{SENDER_EMAIL}',
            '{ORIGINAL_SUBJECT}',
            '{USER_NAME}',
            '{USER_EMAIL}',
        ];

        $replace = [
            $context['start_date'] ?? '',
            $context['end_date'] ?? '',
            $context['return_date'] ?? '',
            $context['sender_name'] ?? '',
            $context['sender_email'] ?? '',
            $context['original_subject'] ?? '',
            $context['user_name'] ?? '',
            $context['user_email'] ?? '',
        ];

        return str_replace($search, $replace, $content);
    }

    /**
     * Format estimated return date from end date string.
     */
    public static function calculateReturnDate(?string $endDateStr, string $timezone = 'UTC'): string
    {
        if (empty($endDateStr)) {
            return '';
        }

        try {
            $tz = new DateTimeZone($timezone);
            $dt = new DateTime($endDateStr, $tz);
            // Move to following day
            $dt->modify('+1 day');
            // If following day is Saturday or Sunday, advance to Monday
            $dayOfWeek = (int)$dt->format('N');
            if ($dayOfWeek === 6) {
                $dt->modify('+2 days'); // Sat -> Mon
            } elseif ($dayOfWeek === 7) {
                $dt->modify('+1 day'); // Sun -> Mon
            }
            return $dt->format('l, M j, Y');
        } catch (\Throwable) {
            return $endDateStr;
        }
    }

    /**
     * Extract human friendly sender name.
     */
    public static function extractSenderName(string $fromHeader, string $senderEmail): string
    {
        if (preg_match('/^"?([^"<]+)"?\s*<.+>$/', trim($fromHeader), $m)) {
            $name = trim($m[1]);
            if ($name !== '') {
                return $name;
            }
        }

        // Fallback: capitalize username portion of email
        $parts = explode('@', $senderEmail);
        $userPart = $parts[0] ?? '';
        $clean = preg_replace('/[._\-]+/', ' ', $userPart);
        return ucwords(trim($clean)) ?: $senderEmail;
    }
}
