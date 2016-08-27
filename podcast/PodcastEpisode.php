<?php

class PodcastEpisode
{
    public $title = '';
    public $address = '';
    public $date = null;
    public $bannerImage = '';
    public $thumbnailImage = '';
    public $sound = '';
    public $excerpt = '';
    public $guid = '';

    public static function createFromPage($page, $guidFile)
    {
        if (!PodcastEpisode::validatePageData($page)) {
            return null;
        }

        if (!$guidFile) {
            return null;
        }

        $meta = $page['meta'];

        $episode = new PodcastEpisode();
        $episode->title = $meta['title'];
        $episode->address = $page['url'];

        if (array_key_exists('date', $meta)) {
            $episode->date = strtotime($meta['date']);
        }

        if (array_key_exists('banner', $meta)) {
            $episode->bannerImage = $meta['banner'];
        }

        if (array_key_exists('thumbnail', $meta)) {
            $episode->thumbnailImage = $meta['thumbnail'];
        }

        if (array_key_exists('sound', $meta)) {
            $episode->sound = $meta['sound'];
        }

        if (array_key_exists('content', $page)) {
            if (strpos($page['content'], '(excerpt)') !== false) {
                $episode->excerpt = explode('(excerpt)', $page['content'])[0] . '...';
            } else {
                $episode->excerpt = $page['content'];
            }
        }

        $episode->guid = $guidFile->getGuidForId($page['id']);

        return $episode;
    }

    public function getFormattedDate()
    {
        return date('r', $this->date);
    }

    public static function validatePageData($page)
    {
        if (!array_key_exists('meta', $page)) {
            return false;
        }

        $meta = $page['meta'];

        if (!array_key_exists('template', $meta)) {
            return false;
        }

        if ($meta['template'] != 'episode') {
            return false;
        }

        return true;
    }
}
