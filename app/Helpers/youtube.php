<?php

if (!function_exists('youtube_embed_url')) {
    /**
     * Chuyển đổi URL YouTube thành embed URL
     *
     * @param string $url
     * @return string
     */
    function youtube_embed_url($url)
    {
        if (empty($url)) {
            return '';
        }

        // Regex để tìm YouTube video ID
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        // Nếu không phải YouTube URL, trả về URL gốc
        return $url;
    }
}