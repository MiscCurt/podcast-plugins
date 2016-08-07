<?php

class PodcastEpisode
{
    public $title;
    public $address;
    public $date;
    public $bannerImage;
    public $thumbnailImage;
    public $excerpt;

    public static function createFromPage($page)
    {
        $episode = null;

        if (PodcastEpisode::validatePageData($page)) {
            $meta = $page['meta'];

            $episode = new PodcastEpisode();
            $episode->title = $meta['title'];

            $episode->address = $meta['address'];

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

            $episode->excerpt = explode('(excerpt)', $page['content'])[0];
        }

        return $episode;
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
