<?php

namespace Context\Page\Search;

use Context\Page\Base\Base;

/**
 * Search page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Base
{
    /**
     * @var string
     */
    protected $path = '/search/';

    /**
     * {@inheritdoc}
     */
    public function fillField($locator, $value)
    {
        $searchField = $this->getElement('Container')->find('css', 'input#search');
        $searchField->setValue($value);
    }

    /**
     * Fill in quick search form
     * @param string $search
     * @param string $typeSearch
     */
    public function fillQuickSearch($search, $typeSearch = null)
    {
        $this->getQuickSearchPopin()->fillField('search-bar-search', $search);

        if ($typeSearch !== null) {
            $this->fillTypeSearchField($typeSearch);
        }
    }

    /**
     * Fill type search field
     * @param string $typeSearch
     */
    protected function fillTypeSearchField($typeSearch)
    {
        $this->openTypeSearchFieldList();

        $typeSearchField = $this->getQuickSearchPopin()->find('css', sprintf('li a:contains("%s")', $typeSearch));
        $typeSearchField->click();
    }

    /**
     * Open quick search popin
     */
    public function openQuickSearchPopin()
    {
        $quickSearchLink = $this->getElement('Navigation Bar')->find('css', 'div.top-search a');
        $quickSearchLink->click();
    }

    /**
     * @return \Behat\Mink\Element\NodeElement
     */
    protected function getQuickSearchPopin()
    {
        $quickSearchPopin = $this->getElement('Navigation Bar')->find('css', 'form#top-search-form');

        if (!$quickSearchPopin->isVisible()) {
            $this->openQuickSearchPopin();
        }

        return $quickSearchPopin;
    }

    /**
     * Open the type search field list
     */
    protected function openTypeSearchFieldList()
    {
        $typeSearchFieldList = $this->getQuickSearchPopin()->find('css', 'button#search-bar-button');
        $typeSearchFieldList->click();
    }
}
