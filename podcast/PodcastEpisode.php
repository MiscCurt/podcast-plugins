<?php

class PodcastEpisode
{
    /**
     * Compares two episodes and determines their sort order based on date
     *
     * @param PodcastEpisode $episode1 The first episode
     * @param PodcastEpisode $episode2 The second episode
     *
     * @return int -1, 0, or 1, for compatibility with PHP's usort function
     */
    public static function compareDate($episode1, $episode2)
    {
        if ($episode1->date < $episode2->date) {
            return -1;
        } elseif ($episode1->date == $episode2->date) {
            return 0;
        } elseif ($episode1->date > $episode2->date) {
            return 1;
        }
    }

    public static function createFromPage($page)
    {
        if (!PodcastEpisode::validatePageData($page)) {
            return null;
        }

        $episode = new PodcastEpisode();
        $meta = $page['meta'];

        foreach ($meta as $key => $value) {
            $episode->$key = $value;
        }

        $episode->id = $page['id'];
        $episode->link = ArrayHelper::getValue('url', $page);
        $episode->description = PodcastEpisode::getDescriptionFromContent(
            ArrayHelper::getValue('content', $page)
        );
        $episode->excerpt = PodcastEpisode::getExcerptFromContent(
            ArrayHelper::getValue('content', $page)
        );

        return $episode;
    }

    public static function getDescriptionFromContent($content)
    {
        $contentText = PodcastEpisode::processContent($content);
        return str_replace('(excerpt)', ' ', $contentText);
    }

    public static function getExcerptFromContent($content)
    {
        $contentText = PodcastEpisode::processContent($content);

        if (strpos($contentText, '(excerpt)') !== false) {
            $contentText = explode('(excerpt)', $contentText)[0] . '...';
        }

        return $contentText;
    }

    public function getFormattedDate()
    {
        return date('r', strtotime($this->date));
    }

    public static function processContent($content)
    {
        return str_replace(
            [PHP_EOL, '</p><p>', '<p>', '</p>'],
            [' ', ' ', '', ''],
            trim($content)
        );
    }

    public static function validatePageData($page)
    {
        if (!is_array($page)) {
            return false;
        }

        if (!array_key_exists('id', $page)) {
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

        if ($meta['contains'] != 'episode') {
            return false;
        }

        return true;
    }
}
