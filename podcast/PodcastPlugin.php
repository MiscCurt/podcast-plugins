<?php

class PodcastPlugin extends AbstractPicoPlugin
{
    protected $episodes = null;

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

        foreach ($pages as $page) {
            $episode = PodcastEpisode::createFromPage($page);

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
