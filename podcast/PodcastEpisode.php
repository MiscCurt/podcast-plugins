<?php

class PodcastEpisode
{
    public $title;
    public $date;
    public $bannerImage;
    public $thumbnailImage;

    public static function createFromPage($page)
    {
        $episode = null;

        if (PodcastEpisode::validatePageData($page)) {
            $meta = $page['meta'];

            $episode = new PodcastEpisode();
            $episode->title = $meta['title'];

            if (array_key_exists('date', $meta)) {
                $episode->date = strtotime($meta['date']);
            }

            if (array_key_exists('image', $meta)) {
                foreach ($meta['image'] as $image) {
                    if (array_key_exists('Banner', $image)) {
                        $episode->bannerImage = $image['Banner'];
                    }

                    if (array_key_exists('Thumbnail', $image)) {
                        $episode->thumbnailImage = $image['Thumbnail'];
                    }
                }
            }

            if (array_key_exists('sound', $meta)) {
                $episode->sound = $meta['sound'];
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
