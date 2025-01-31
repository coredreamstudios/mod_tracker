<?php

class LinkChecker {
    private $timeout = 10; // timeout in seconds
    
    public function checkLink($url) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_NOBODY => true // HEAD request only
        ]);
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'status' => 'Dead Link',
                'error' => $error
            ];
        }
        
        // If we get a successful response or a redirect
        if ($httpCode >= 200 && $httpCode < 400) {
            return [
                'status' => 'OK',
                'code' => $httpCode
            ];
        }
        
        return [
            'status' => 'Dead Link',
            'code' => $httpCode
        ];
    }
    
    public function checkBuzzheavier($url) {
        // First make a HEAD request to check if the URL exists
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_NOBODY => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // If we get a 404, it's definitely dead
        if ($httpCode === 404) {
            return [
                'status' => 'Dead Link',
                'reason' => 'Page not found (404)'
            ];
        }

        // If it's not 404, make a full GET request to check the content
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1'
            ]
        ]);
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'status' => 'Dead Link',
                'error' => $error
            ];
        }

        // Check if we're redirected to an error page or get an error message
        if (strpos($content, 'Not Found') !== false || 
            strpos($content, 'File Not Found') !== false || 
            strpos($content, 'Error 404') !== false ||
            strpos($content, 'has been deleted') !== false ||
            strpos($content, 'does not exist') !== false) {
            return [
                'status' => 'Dead Link',
                'reason' => 'File not found on server'
            ];
        }

        // Check for successful indicators
        if (strpos($content, 'download-button') !== false ||
            strpos($content, 'Download File') !== false ||
            strpos($content, 'file-info') !== false) {
            return [
                'status' => 'OK',
                'code' => $httpCode
            ];
        }

        // If we can't find any clear indicators, check if the URL pattern is valid
        if (!preg_match('#^https?://buzzheavier\.com/f/[A-Za-z0-9_-]+$#', $url)) {
            return [
                'status' => 'Dead Link',
                'reason' => 'Invalid URL format'
            ];
        }

        // Default to OK if we got this far and the URL format is valid
        return [
            'status' => 'OK',
            'code' => $httpCode
        ];
    }

    public function check1Fichier($url) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'status' => 'Dead Link',
                'error' => $error
            ];
        }
        
        // Check for 1fichier-specific error messages
        if (strpos($content, 'The requested file has been deleted') !== false || 
            strpos($content, 'The requested file could not be found') !== false ||
            strpos($content, 'File not found') !== false ||
            strpos($content, 'Access denied') !== false) {
            return [
                'status' => 'Dead Link',
                'reason' => 'File not found or deleted'
            ];
        }
        
        // Check for successful download indicators
        if (strpos($content, 'Download') !== false &&
            strpos($content, 'The requested file has been deleted') === false) {
            return [
                'status' => 'OK',
                'code' => $httpCode
            ];
        }
        
        return [
            'status' => 'OK',
            'code' => $httpCode
        ];
    }
}
