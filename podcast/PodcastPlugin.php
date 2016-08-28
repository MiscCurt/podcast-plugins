<?php

class PodcastPlugin extends AbstractPicoPlugin
{
    protected $assetsPath = '';
    protected $episodes = null;
    protected $guidFilePath = null;
    protected $rssValues = [];

    /**
     * Compares two episodes and determines their sort order based on date
     *
     * @param PodcastEpisode $episode1 The first episode
     * @param PodcastEpisode $episode2 The second episode
     *
     * @return int -1, 0, or 1, for compatibility with PHP's usort function
     */
    protected function compareEpisodes($episode1, $episode2)
    {
        if ($episode1->date < $episode2->date) {
            return -1;
        } elseif ($episode1->date == $episode2->date) {
            return 0;
        } elseif ($episode1->date > $episode2->date) {
            return 1;
        }
    }

    public function getRssValuesFromEpisodes($episodes)
    {
        $values = [];

        $episodeCount = count($episodes);

        if ($episodeCount > 0) {
            $values['published'] = date('r', $episodes[$episodeCount - 1]->date);
            $values['lastBuildDate'] = date('r', $episodes[0]->date);
        }

        return $values;
    }

    public function getRssValuesFromPageData($pageData)
    {
        $meta = $pageData['meta'];

        if (array_key_exists('assetspath', $meta)) {
            $this->assetsPath = $meta['assetspath'];
        }

        if (array_key_exists('image', $meta)) {
            $meta['image'] = FileHelper::getFilePath($this->assetsPath, $meta['image']);
        }

        if (array_key_exists('guidfile', $meta)) {
            $this->guidFilePath = FileHelper::getFilePath($this->assetsPath, $meta['guidfile'], true, true);
        }

        return $meta;
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
        $this->episodes = [];
        $guidFile = PodcastGuidFile::createFromFile($this->guidFilePath);

        foreach ($pages as $page) {
            $episode = PodcastEpisode::createFromPage(
                $page,
                $this->assetsPath,
                $guidFile
            );

            if (!is_null($episode)) {
                $this->episodes[] = $episode;
            }
        }
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
        $this->sortEpisodes($this->episodes);
        $twigVariables['episodes'] = $this->episodes;

        $this->rssValues = array_merge(
            $this->rssValues,
            $this->getRssValuesFromEpisodes($this->episodes)
        );

        $twigVariables['rss'] = $this->rssValues;
    }

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
        $meta = $pageData['meta'];

        if (array_key_exists('contains', $meta)) {
            if ($meta['contains'] = 'rss') {
                $this->rssValues = array_merge(
                    $this->rssValues,
                    $this->getRssValuesFromPageData($pageData)
                );
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
        // Use this class's comparison function to sort the array
        usort($episodes, array('PodcastPlugin', 'compareEpisodes'));

        // Reverse the result in order to put the episodes in reverse
        // chronological order. We could just make compareEpisodes reverse the
        // order directly, but coding this way makes both functions easier to
        // understand
        $episodes = array_reverse($episodes);
    }
}
