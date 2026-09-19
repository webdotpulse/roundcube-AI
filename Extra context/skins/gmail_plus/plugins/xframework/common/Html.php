<?php
namespace XFramework;

/**
 * Roundcube Plus Framework plugin.
 *
 * Copyright 2017, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

require_once __DIR__ . '/Singleton.php';

class Html
{
    use Singleton;

    /**
     * Adds classes to the <html> element.
     *
     * @param array|string $classes
     * @param string $html
     * @return bool
     */
    public function addClassesToHtml(array|string $classes, string &$html): bool
    {
        if (empty($classes = trim(is_array($classes) ? implode(' ', $classes) : $classes))) {
            return false;
        }

        $count = 0;
        $html = preg_replace(
            '/(<html\b[^>]*\bclass\s*=\s*)(["\'])([^"\']*)(\2)/i',
            '$1$2' . $classes . ' $3$4',
            $html,
            1,
            $count
        );

        if (!$count) {
            $html = preg_replace('/(<html\b)([^>]*)>/i', '$1$2 class="' . $classes . '">', $html, 1, $count);
        }

        return $count > 0;
    }

    /**
     * Adds classes to the <body> element.
     *
     * @param array|string $classes
     * @param string $html
     * @return bool
     */
    public function addClassesToBody(array|string $classes, string &$html): bool
    {
        if (empty($classes = trim(is_array($classes) ? implode(' ', $classes) : $classes))) {
            return false;
        }

        $count = 0;
        $html = preg_replace(
            '/(<body\b[^>]*\bclass\s*=\s*)(["\'])([^"\']*)(\2)/i',
            '$1$2' . $classes . ' $3$4',
            $html,
            1,
            $count
        );

        if (!$count) {
            $html = preg_replace('/(<body\b)([^>]*)>/i', '$1$2 class="' . $classes . '">', $html, 1, $count);
        }

        return $count > 0;
    }

    /**
     * @param string $marker
     * @param string $insertString
     * @param string $html
     * @param string $container
     * @return bool
     */
    public function insertBefore(string $marker, string $insertString, string &$html, string $container = ""): bool
    {
        if (($pos = $this->findStart($container, $marker, $html, false)) !== false) {
            $html = substr_replace($html, $insertString, $pos, 0);
            return true;
        }

        return false;
    }

    /**
     * @param string $marker
     * @param string $tagName
     * @param string $insertString
     * @param string $html
     * @param string $container
     * @return bool
     */
    public function insertAfter(string $marker, string $tagName, string $insertString, string &$html,
                                string $container = ""): bool
    {
        if (($pos = $this->findEnd($container, $marker, $tagName, $html)) !== false) {
            $html = substr_replace($html, $insertString, $pos, 0);
            return true;
        }

        return false;
    }

    /**
     * @param string $marker
     * @param string $insertString
     * @param string $html
     * @param string $container
     * @return bool
     */
    public function insertAtBeginning(string $marker, string $insertString, string &$html, string $container = ""): bool
    {
        if (($pos = $this->findStart($container, $marker, $html, true)) !== false) {
            $html = substr_replace($html, $insertString, $pos, 0);
            return true;
        }

        return false;
    }

    /**
     * @param string $marker - String to search for, it can be a class or id within a tag or a text within a tag. The
     *          function will search for the first tag to the left of the marker to identify the element at the end of
     *          which the text should be inserted.
     * @param string $insertString - String to insert before the closing tag.
     * @param string $html - Html code to modify.
     * @return bool - True if the string has been successfully inserted, false otherwise.
     */
    public function insertAtEnd(string $marker, string $insertString, string &$html): bool
    {
        // find marker
        if (($i = stripos($html, $marker)) === false) {
            return false;
        }

        // get the html element
        if (($i = strripos(substr($html, 0, $i), "<")) === false ||
            ($j = stripos($html, " ", $i)) === false) {
            return false;
        }

        $tag = substr($html, $i + 1, $j - $i - 1);
        $count = 0;

        do {
            if (($c = stripos($html, "</$tag>", $i)) === false) {
                return false;
            }

            if (($n = stripos($html, "<$tag ", $i)) === false) {
                $n = $c + 1;
            }

            if ($c > $n) {
                $count++;
                $i = $n + 1;
            } else {
                $count--;
                $i = $c + 1;
            }
        } while ($count);

        $html = substr_replace($html, $insertString, $i - 1, 0);

        return true;
    }

    /**
     * WARNING: Don't use this to insert html because it causes Roundcube to re-order script tag positioning and some
     * plugins that insert their code at the end of the page might not get the scripts they expect (for example,
     * Thunderbird labels.)
     *
     * @param string $insertString
     * @param string $html
     */
    public function insertBeforeBodyEnd(string $insertString, string &$html): void
    {
        $html = str_replace("</body>", $insertString . "</body>", $html);
    }

    /**
     * @param string $insertString
     * @param string $html
     * @return bool
     */
    public function insertAfterBodyStart(string $insertString, string &$html): bool
    {
        if (($i = strpos($html, "<body ")) !== false &&
            ($j = strpos($html, ">", $i + 1))
        ) {
            $html = substr_replace($html, "\n" . $insertString, $j + 1, 0);
            return true;
        }

        return false;
    }

    /**
     * @param string $insertString
     * @param string $html
     */
    public function insertBeforeHeadEnd(string $insertString, string &$html): void
    {
        $html = str_replace("</head>", $insertString . "</head>", $html);
    }

    /**
     * @param string $container
     * @param string $marker
     * @param string $html
     * @param bool $inner
     * @return bool|int
     */
    private function findStart(string $container, string $marker, string $html, bool $inner): bool|int
    {
        if (($pos = $this->findMarker($container, $marker, $html)) === false) {
            return false;
        }

        if ($inner) {
            if (!str_ends_with($marker, ">")) {
                $pos = strpos($html, ">", $pos);
                if ($pos !== false) {
                    $pos++;
                }
            }
        } else {
            // if marker doesn't include the opening tag name, find the beginning of the tag
            if (!str_starts_with($marker, "<")) {
                $pos = strrpos(substr($html, 0, $pos + 1), "<");
            }
        }

        return $pos;
    }

    /**
     * @param string $container
     * @param string $marker
     * @param string $tagName
     * @param string $html
     * @return bool|int
     */
    private function findEnd(string $container, string $marker, string $tagName, string $html): bool|int
    {
        if (($pos = $this->findMarker($container, $marker, $html)) === false) {
            return false;
        }

        // find the closing tag
        $end = $pos;

        do {
            $innerTagStart = strpos($html, "<$tagName ", $end + 1);
            $end = strpos($html, "</$tagName>", $end + 1);
        } while ($end !== false && $innerTagStart !== false && $innerTagStart < $end);

        if ($end === false) {
            return false;
        }

        return $end + strlen("</$tagName>");
    }

    /**
     * @param string $container
     * @param string $marker
     * @param string $html
     * @return bool|int
     */
    private function findMarker(string $container, string $marker, string $html): bool|int
    {
        $start = empty($container) ? strpos($html, "<body ") : strpos($html, $container);

        if ($start === false) {
            return false;
        }

        return strpos($html, $marker, $start);
    }
}