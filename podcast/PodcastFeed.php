<?php

class PodcastFeed
{
    public static function createFromPage($page)
    {
        if (!PodcastFeed::validatePageData($page)) {
            return null;
        }

        $feed = new PodcastFeed();
        $meta = $page['meta'];

        foreach ($meta as $key => $value) {
            $feed->$key = $value;
        }

        $feed->url = $page['url'];
        $feed->guidfile = FileHelper::getFilePath($feed->assetspath, $feed->guidfile, true, true);
        $feed->image = FileHelper::getFilePath($feed->assetspath, $feed->image);

        return $feed;
    }

    public static function validatePageData($page)
    {
        if (!is_array($page)) {
            return false;
        }

        if (!array_key_exists('meta', $page)) {
            return false;
        }

        $meta = $page['meta'];

        if (!is_array($meta)) {
            return false;
        }

        if (!array_key_exists('contains', $meta)) {
            return false;
        }

        if ($meta['contains'] != 'feed') {
            return false;
        }

        return true;
    }
}
