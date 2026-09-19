<?php
namespace XCalendar;

class CalDavLog
{
    static string $content = '';

    public static function log($string = '', bool $backtrace = false): void
    {
        if (!defined('CALDAV_LOG') || !CALDAV_LOG) {
            return;
        }

        is_array($string) && ($string = json_encode($string));
        is_object($string) && ($string = serialize($string));

        $backtraceData = debug_backtrace();
        if (!empty($backtraceData[1])) {
            $parent = $backtraceData[1];
            $method = (empty($parent['class']) ? '' : $parent['class'] . '::') . 
                (empty($parent['function']) ? 'index.php' : $parent['function'] . '()');
        } else {
            $method = "";
        }

        self::$content .= $method . ($method && $string ? ': ' : '') . $string . "\n";

        if ($backtrace) {
            unset($backtraceData[0]);
            $result = [];
            foreach ($backtraceData as $item) {
                $result[] = $item['file'] . ' ' . $item['class'] . '::' . $item['function'];
            }
            self::$content .= "BACKTRACE:\n" . implode("\n", $result) . "\n";
        }

        self::$content .= "\n";
    }

    public static function write(): void
    {
        if (defined('CALDAV_LOG') && CALDAV_LOG) {
            file_put_contents(
                rtrim(RCUBE_INSTALL_PATH, '/') . '/logs/caldav',
                "========================================================================================\n" .
                date('[Y-m-d H:i:s] ') . "\n" . self::$content,
                FILE_APPEND
            );
        }
    }
}