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

    public static function createFromPage($page)
    {
        $episode = null;

        if (PodcastEpisode::validatePageData($page)) {
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
