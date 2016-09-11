<?php

class FileHelper
{
    public static function getCurlInfo($path, $infoKeys)
    {
        $result = [];
        $handle = curl_init($path);

        if ($handle) {
            try {
                $noOptionError = curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true)
                    && curl_setopt($handle, CURLOPT_HEADER, true)
                    && curl_setopt($handle, CURLOPT_NOBODY, true)
                    && curl_setopt($handle, CURLOPT_RETURNTRANSFER, true)
                    && curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false)
                    && curl_setopt($handle, CURLOPT_USERAGENT, 'PodcastPlugin/0.1 (+https://github.com/MiscCurt/podcast-plugins)');

                if ($noOptionError) {
                    $execResult = curl_exec($handle);

                    if ($execResult !== false) {
                        foreach ($infoKeys as $key) {
                            $result[$key] = curl_getinfo($handle, $key);
                        }
                    }
                }
            } finally {
                curl_close($handle);
            }
        }

        return $result;
    }

    public static function getFilePath($path, $fileName, $readable = true, $writable = false)
    {
        if (!is_string($path)) {
            return false;
        }

        if (!is_string($fileName)) {
            return false;
        }

        clearstatcache();

        if (substr($path, -1) != DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }

        $filePath = $path . $fileName;

        if (
            (!$readable || is_readable($filePath))
            && (!$writable || is_writable($filePath))
        ) {
            return $filePath;
        }

        if (
            (!$readable || is_readable($fileName))
            && (!$writable || is_writable($fileName))
        ) {
            return $fileName;
        }

        // $handle = curl_init($filePath);
        //
        // if ($handle) {
        //     try {
        //         $noOptionError = curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true)
        //             && curl_setopt($handle, CURLOPT_NOBODY, true)
        //             && curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false)
        //             && curl_setopt($handle, CURLOPT_USERAGENT, 'PodcastPlugin/0.1 (+https://github.com/MiscCurt/podcast-plugins)');
        //
        //         if ($noOptionError) {
        //             $execResult = curl_exec($handle);
        //
        //             if ($execResult !== false) {
        //                 $returnCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        //             }
        //         }
        //     } finally {
        //         curl_close($handle);
        //     }
        //
        //     $info = self::getCurlInfo($filePath, [CURLINFO_HTTP_CODE]);
        //
        //     if ($info[CURLINFO_HTTP_CODE] == 200 && !$writable) {
        //         return $filePath;
        //     }
        // }

        if (!$writable) {
            $curlInfo = self::getCurlInfo($filePath, [CURLINFO_HTTP_CODE]);
            return ArrayHelper::getValue(CURLINFO_HTTP_CODE, $curlInfo) == 200;
        }

        return false;
    }

    public static function getFileSize($filePath)
    {
        clearstatcache();

        $errorLevel = error_reporting();
        error_reporting($errorLevel ^ E_WARNING);

        try {
            $size = filesize($filePath);
        } finally {
            error_reporting($errorLevel);
        }

        if ($size !== false) {
            return $size;
        }

        $info = self::getCurlInfo($filePath, [CURLINFO_CONTENT_LENGTH_DOWNLOAD]);
        $length = ArrayHelper::getValue(CURLINFO_CONTENT_LENGTH_DOWNLOAD, $info);

        return $length ? $length : false;
    }
}
