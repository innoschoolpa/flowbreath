<?php

namespace App\Utils;

class Formatter {
    public static function formatDate($date, $format = 'Y-m-d H:i:s') {
        if (!$date) {
            return null;
        }
        return date($format, strtotime($date));
    }

    public static function formatNumber($number, $decimals = 2) {
        return number_format($number, $decimals);
    }

    public static function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public static function formatTags($tags) {
        if (is_string($tags)) {
            return array_filter(array_map('trim', explode(',', $tags)));
        }
        return is_array($tags) ? $tags : [];
    }

    public static function formatVisibility($visibility) {
        return in_array($visibility, ['public', 'private']) ? $visibility : 'private';
    }

    public static function formatStatus($status) {
        return in_array($status, ['draft', 'published']) ? $status : 'draft';
    }
} 