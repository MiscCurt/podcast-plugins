<?php

class PodcastPlugin extends AbstractPicoPlugin
{
    protected $currentPageId = null;
    protected $episodes = null;
    protected $feeds = null;

    /**
     * Triggered when Pico reads a single page from the list of all known pages
     *
     * The `$pageData` parameter consists of the following values:
     *
     * | Array key      | Type   | Description                              |
     * | -------------- | ------ | ---------------------------------------- |
     * | id             | string | relative path to the content file        |
     * | url            | string | URL to the page                          |
     * | title          | string | title of the page (YAML header)          |
     * | description    | string | description of the page (YAML header)    |
     * | author         | string | author of the page (YAML header)         |
     * | time           | string | timestamp derived from the Date header   |
     * | date           | string | date of the page (YAML header)           |
     * | date_formatted | string | formatted date of the page               |
     * | raw_content    | string | raw, not yet parsed contents of the page |
     * | meta           | string | parsed meta data of the page             |
     *
     * @see    DummyPlugin::onPagesLoaded()
     * @param  array &$pageData data of the loaded page
     * @return void
     */
    public function onSinglePageLoaded(array &$pageData)
    {
        // As each of the site's pages is loaded, create and save data objects
        if (PodcastEpisode::validatePageData($pageData)) {
            $this->episodes[$pageData['id']] = PodcastEpisode::createFromPage($pageData);
        }

        if (PodcastFeed::validatePageData($pageData)) {
            $this->feeds[$pageData['id']] = PodcastFeed::createFromPage($pageData);
        }
    }

    /**
     * Triggered after Pico has read all known pages
     *
     * See {@link DummyPlugin::onSinglePageLoaded()} for details about the
     * structure of the page data.
     *
     * @see    Pico::getPages()
     * @see    Pico::getCurrentPage()
     * @see    Pico::getPreviousPage()
     * @see    Pico::getNextPage()
     * @param  array[]    &$pages        data of all known pages
     * @param  array|null &$currentPage  data of the page being served
     * @param  array|null &$previousPage data of the previous page
     * @param  array|null &$nextPage     data of the next page
     * @return void
     */
    public function onPagesLoaded(
        array &$pages,
        array &$currentPage = null,
        array &$previousPage = null,
        array &$nextPage = null
    ) {
        $this->currentPageId = $currentPage['id'];

        // While much of the processing to extract data from the pages is done
        // in onSinglePageLoaded, some things can only be done once all the
        // pages are loaded - particularly things that require information from
        // more than one page type. Do those things here
        $this->sortEpisodes($this->episodes);
        $this->setEpisodeFeedValues($this->episodes, $this->feeds);
    }

    /**
     * Triggered before Pico renders the page
     *
     * @see    Pico::getTwig()
     * @see    DummyPlugin::onPageRendered()
     * @param  Twig_Environment &$twig          twig template engine
     * @param  array            &$twigVariables template variables
     * @param  string           &$templateName  file name of the template
     * @return void
     */
    public function onPageRendering(Twig_Environment &$twig, array &$twigVariables, &$templateName)
    {
        $twigVariables['episodes'] = $this->episodes;
        $twigVariables['feeds'] = $this->feeds;

        if ($this->episodes) {
            $twigVariables['latestEpisode'] = reset($this->episodes);
            $twigVariables['earliestEpisode'] = end($this->episodes);
            $twigVariables['episode'] = ArrayHelper::getValue($this->currentPageId, $this->episodes);
        }

        if ($this->feeds) {
            $twigVariables['feed'] = ArrayHelper::getValue($this->currentPageId, $this->feeds);
        }
    }

    protected function setEpisodeFeedValues($episodes, $feeds)
    {
        if (!is_array($episodes) || !is_array($feeds)) {
            return;
        }

        foreach ($episodes as $episode) {
            if (
                property_exists($episode, 'feed')
                && array_key_exists($episode->feed, $feeds)
            ) {
                $feed = $feeds[$episode->feed];

                if (property_exists($episode, 'banner')) {
                    $episode->banner = FileHelper::getFilePath(
                        $feed->assetspath,
                        $episode->banner
                    );
                }

                if (property_exists($episode, 'thumbnail')) {
                    $episode->thumbnail = FileHelper::getFilePath(
                        $feed->assetspath,
                        $episode->thumbnail
                    );
                }

                if (property_exists($episode, 'sound')) {
                    $episode->sound = FileHelper::getFilePath(
                        $feed->assetspath,
                        $episode->sound
                    );

                    $episode->size = FileHelper::getFileSize($episode->sound);
                }

                // $guidFile = PodcastGuidFile::createFromFile($feed->guidfile);
                //
                // if ($guidFile) {
                //     $episode->guid = $guidFile->getGuidForId($episode->id);
                // }
                $episode->guid = "test-guid";
            }
        }
    }

    /**
     * Sorts the given array of episodes by date
     *
     * @param PodcastEpisode[] $episodes The array of episodes
     *
     * @return void The episode array is passed by reference and sorted directly
     */
    protected function sortEpisodes(&$episodes)
    {
        if ($episodes) {
            // Use this class's comparison function to sort the array
            uasort($episodes, 'PodcastEpisode::compareDate');

            // Reverse the result in order to put the episodes in reverse
            // chronological order. We could just make compareEpisodes reverse the
            // order directly, but coding this way makes both functions easier to
            // understand
            $episodes = array_reverse($episodes, true);
        }
    }
}
