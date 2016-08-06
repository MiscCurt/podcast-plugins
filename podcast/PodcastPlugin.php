<?php

class PodcastPlugin extends AbstractPicoPlugin
{
    protected $episodes = null;

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

    protected function sortEpisodes(&$episodes)
    {
        usort($episodes, array('PodcastPlugin', 'compareEpisodes'));
    }
}
