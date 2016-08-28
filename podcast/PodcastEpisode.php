<?php

class PodcastEpisode
{
    public $title = '';
    public $link = '';
    public $date = null;
    public $bannerImage = '';
    public $thumbnailImage = '';
    public $sound = '';
    public $excerpt = '';
    public $guid = '';
    public $explicit = 'no';

    public static function createFromPage($page, $assetsPath, $guidFile)
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
        $episode->link = $page['url'];

        if (array_key_exists('date', $meta)) {
            $episode->date = strtotime($meta['date']);
        }

        if (array_key_exists('banner', $meta)) {
            // $episode->bannerImage = $assetsPath . $meta['banner'];
            $episode->bannerImage = FileHelper::getFilePath($assetsPath, $meta['banner']);
        }

        if (array_key_exists('thumbnail', $meta)) {
            // $episode->thumbnailImage = $assetsPath . $meta['thumbnail'];
            $episode->thumbnailImage = FileHelper::getFilePath($assetsPath, $meta['thumbnail']);
        }

        if (array_key_exists('content', $page)) {
            $contentText = str_replace(
                [PHP_EOL, '</p><p>', '<p>', '</p>'],
                [' ', ' ', '', ''],
                trim($page['content'])
            );

            if (strpos($contentText, '(excerpt)') !== false) {
                $episode->excerpt = explode('(excerpt)', $contentText)[0] . '...';
            } else {
                $episode->excerpt = $contentText;
            }

            $contentText = str_replace('(excerpt)', ' ', $contentText);
            $episode->description = $contentText;
        }

        if (array_key_exists('sound', $meta)) {
            // $episode->sound = $assetsPath . $meta['sound'];
            $episode->sound = FileHelper::getFilePath($assetsPath, $meta['sound']);
            $episode->size = FileHelper::getFileSize($episode->sound);
        }

        if (array_key_exists('length', $meta)) {
            $episode->length = $meta['length'];
        }

        if (array_key_exists('explicit', $meta)) {
            $episode->explicit = $meta['explicit'];
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
