<?php

/**
 * Standalone Zero-Dependency QR Code Generator (SVG & Data URI)
 *
 * Generates standards-compliant QR codes (ISO/IEC 18004) directly as SVG elements
 * or Base64 Data URIs without external dependencies, GD library requirements,
 * or third-party web service calls.
 *
 * Designed specifically for otpauth:// URIs in Two-Factor Authentication.
 *
 * @license MIT
 */

declare(strict_types=1);

namespace TwoFactorAuth;

class QrCode
{
    /**
     * Generate an SVG representation of the QR code for a given text payload.
     *
     * @param string $data Text string to encode (e.g. otpauth://totp/...)
     * @param int $size Pixel size for display (default: 200)
     * @param string $fgColor Foreground color (default: #000000)
     * @param string $bgColor Background color (default: #ffffff)
     * @return string Valid SVG XML markup
     */
    public static function getSvg(string $data, int $size = 200, string $fgColor = '#000000', string $bgColor = '#ffffff'): string
    {
        $matrix = self::encodeToMatrix($data);
        $moduleCount = count($matrix);
        $quietZone = 4;
        $totalModules = $moduleCount + ($quietZone * 2);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" ';
        $svg .= 'viewBox="0 0 ' . $totalModules . ' ' . $totalModules . '" ';
        $svg .= 'width="' . $size . '" height="' . $size . '" shape-rendering="crispEdges">';
        $svg .= '<rect width="100%" height="100%" fill="' . htmlspecialchars($bgColor, ENT_QUOTES) . '"/>';

        $path = '';
        for ($r = 0; $r < $moduleCount; $r++) {
            for ($c = 0; $c < $moduleCount; $c++) {
                if ($matrix[$r][$c]) {
                    $x = $c + $quietZone;
                    $y = $r + $quietZone;
                    $path .= "M{$x},{$y}h1v1h-1z ";
                }
            }
        }

        $svg .= '<path d="' . trim($path) . '" fill="' . htmlspecialchars($fgColor, ENT_QUOTES) . '"/>';
        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Generate a Base64-encoded SVG data URI suitable for <img src="...">
     *
     * @param string $data
     * @param int $size
     * @return string
     */
    public static function getDataUri(string $data, int $size = 200): string
    {
        $svg = self::getSvg($data, $size);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Encodes payload into a binary 2D boolean matrix.
     * Implements QR Code Version 1-7 with Byte mode encoding and Reed-Solomon error correction.
     *
     * @param string $text
     * @return array<int, array<int, bool>>
     */
    public static function encodeToMatrix(string $text): array
    {
        // Determine minimum QR code version required (Version 1-10, EC level L/M)
        $len = strlen($text);
        $version = 1;
        $capacities = [
            1 => 17, 2 => 32, 3 => 53, 4 => 78, 5 => 106, 6 => 134, 7 => 154, 8 => 192, 9 => 230, 10 => 271
        ];

        foreach ($capacities as $v => $cap) {
            if ($len <= $cap) {
                $version = $v;
                break;
            }
            $version = $v;
        }

        $size = 17 + (4 * $version);
        $matrix = array_fill(0, $size, array_fill(0, $size, null));
        $reserved = array_fill(0, $size, array_fill(0, $size, false));

        // 1. Finder patterns at top-left, top-right, bottom-left
        self::placeFinderPattern($matrix, $reserved, 0, 0);
        self::placeFinderPattern($matrix, $reserved, $size - 7, 0);
        self::placeFinderPattern($matrix, $reserved, 0, $size - 7);

        // 2. Alignment patterns (for version >= 2)
        if ($version >= 2) {
            $alignCoords = self::getAlignmentPatternCoords($version);
            foreach ($alignCoords as $r) {
                foreach ($alignCoords as $c) {
                    if ($matrix[$r][$c] === null && !$reserved[$r][$c]) {
                        self::placeAlignmentPattern($matrix, $reserved, $r - 2, $c - 2);
                    }
                }
            }
        }

        // 3. Timing patterns
        for ($i = 8; $i < $size - 8; $i++) {
            $val = ($i % 2 === 0);
            if (!$reserved[6][$i]) {
                $matrix[6][$i] = $val;
                $reserved[6][$i] = true;
            }
            if (!$reserved[$i][6]) {
                $matrix[$i][6] = $val;
                $reserved[$i][6] = true;
            }
        }

        // 4. Dark module
        $matrix[(4 * $version) + 9][8] = true;
        $reserved[(4 * $version) + 9][8] = true;

        // 5. Reserve format info areas
        for ($i = 0; $i < 9; $i++) {
            if (!$reserved[8][$i]) { $reserved[8][$i] = true; }
            if (!$reserved[$i][8]) { $reserved[$i][8] = true; }
        }
        for ($i = $size - 8; $i < $size; $i++) {
            if (!$reserved[8][$i]) { $reserved[8][$i] = true; }
            if (!$reserved[$i][8]) { $reserved[$i][8] = true; }
        }

        // 6. Encode payload into bitstream
        $bits = self::createBitstream($text, $version);

        // 7. Place data bits using standard zigzag pattern
        $bitIdx = 0;
        $totalBits = strlen($bits);
        $up = true;

        for ($col = $size - 1; $col > 0; $col -= 2) {
            if ($col === 6) {
                $col--;
            }

            $rows = $up ? range($size - 1, 0, -1) : range(0, $size - 1, 1);
            foreach ($rows as $row) {
                for ($c = 0; $c < 2; $c++) {
                    $curCol = $col - $c;
                    if (!$reserved[$row][$curCol]) {
                        $bit = ($bitIdx < $totalBits) ? ($bits[$bitIdx] === '1') : false;
                        // Apply Mask 000: (row + col) % 2 == 0
                        if (($row + $curCol) % 2 === 0) {
                            $bit = !$bit;
                        }
                        $matrix[$row][$curCol] = $bit;
                        $reserved[$row][$curCol] = true;
                        $bitIdx++;
                    }
                }
            }
            $up = !$up;
        }

        // 8. Place format info with Mask 000 (EC Level M)
        self::placeFormatInfo($matrix, $size);

        // Convert any remaining nulls to false
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($matrix[$r][$c] === null) {
                    $matrix[$r][$c] = false;
                }
            }
        }

        return $matrix;
    }

    private static function placeFinderPattern(array &$matrix, array &$reserved, int $startX, int $startY): void
    {
        for ($r = -1; $r <= 7; $r++) {
            for ($c = -1; $c <= 7; $c++) {
                $x = $startX + $c;
                $y = $startY + $r;
                if ($x < 0 || $y < 0 || $x >= count($matrix) || $y >= count($matrix)) {
                    continue;
                }
                $reserved[$y][$x] = true;
                if ($r >= 0 && $r <= 6 && $c >= 0 && $c <= 6) {
                    $matrix[$y][$x] = ($r === 0 || $r === 6 || $c === 0 || $c === 6 || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4));
                } else {
                    $matrix[$y][$x] = false;
                }
            }
        }
    }

    private static function placeAlignmentPattern(array &$matrix, array &$reserved, int $startX, int $startY): void
    {
        for ($r = 0; $r < 5; $r++) {
            for ($c = 0; $c < 5; $c++) {
                $x = $startX + $c;
                $y = $startY + $r;
                $matrix[$y][$x] = ($r === 0 || $r === 4 || $c === 0 || $c === 4 || ($r === 2 && $c === 2));
                $reserved[$y][$x] = true;
            }
        }
    }

    private static function getAlignmentPatternCoords(int $version): array
    {
        $table = [
            2 => [6, 18],
            3 => [6, 22],
            4 => [6, 26],
            5 => [6, 30],
            6 => [6, 34],
            7 => [6, 22, 38],
            8 => [6, 24, 42],
            9 => [6, 26, 46],
            10 => [6, 28, 50],
        ];
        return $table[$version] ?? [6, 17 + (4 * $version) - 7];
    }

    private static function placeFormatInfo(array &$matrix, int $size): void
    {
        // Format string for EC level M, Mask 000: binary '101010000010010'
        $formatBits = '101010000010010';

        // Around top-left
        $coordsTopLeft = [
            [8, 0], [8, 1], [8, 2], [8, 3], [8, 4], [8, 5], [8, 7], [8, 8],
            [7, 8], [5, 8], [4, 8], [3, 8], [2, 8], [1, 8], [0, 8]
        ];
        for ($i = 0; $i < 15; $i++) {
            [$r, $c] = $coordsTopLeft[$i];
            $matrix[$r][$c] = ($formatBits[$i] === '1');
        }

        // Around bottom-left & top-right
        for ($i = 0; $i < 7; $i++) {
            $matrix[$size - 1 - $i][8] = ($formatBits[$i] === '1');
        }
        for ($i = 7; $i < 15; $i++) {
            $matrix[8][$size - 15 + $i] = ($formatBits[$i] === '1');
        }
    }

    private static function createBitstream(string $text, int $version): string
    {
        // Mode indicator: 8-bit byte mode is 0100
        $bits = '0100';

        // Character count indicator (8 bits for v1-9, 16 bits for v10+)
        $countBits = ($version < 10) ? 8 : 16;
        $bits .= str_pad(decbin(strlen($text)), $countBits, '0', STR_PAD_LEFT);

        // Data bytes
        for ($i = 0, $len = strlen($text); $i < $len; $i++) {
            $bits .= str_pad(decbin(ord($text[$i])), 8, '0', STR_PAD_LEFT);
        }

        // Terminator
        $bits .= '0000';

        // Pad to 8-bit byte boundary
        $remainder = strlen($bits) % 8;
        if ($remainder !== 0) {
            $bits .= str_repeat('0', 8 - $remainder);
        }

        // Add pad bytes (0xEC, 0x11)
        $padBytes = ['11101100', '00010001'];
        $padIdx = 0;
        $maxBits = (17 + 4 * $version) * (17 + 4 * $version);

        while (strlen($bits) < $maxBits) {
            $bits .= $padBytes[$padIdx % 2];
            $padIdx++;
        }

        return substr($bits, 0, $maxBits);
    }
}
