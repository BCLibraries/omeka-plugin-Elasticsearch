<?php

class Elasticsearch_SearchController extends Omeka_Controller_AbstractActionController
{

    public function interceptorAction()
    {
        $q_string = http_build_query(['q' => $this->_request->getParam('query')]);
        return $this->_helper->redirector->gotoUrl("/elasticsearch/search/index?$q_string");
    }

    public function indexAction(): void
    {
        $limit = 15;
        $page = $this->_request->page ?: 1;
        $start = ($page - 1) * $limit;

        $query = $this->_getSearchParams();
        $sort = $this->_getSortParams();

        // execute query
        $results = null;
        try {
            Zend_Registry::set('current_es_query', $query);
            $results = Elasticsearch_Helper_Index::search([
                'query' => $query,
                'sort' => $sort,
                'offset' => $start,
                'limit' => $limit
            ]);
            Zend_Registry::set('pagination', [
                'per_page' => $limit,
                'page' => $page,
                'total_results' => $results['hits']['total']
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }

        $sorts = $this->buildSortOptionList();

        $unlinked_aggregations = $results['aggregations'] ?? [];

        $aggregations = [];

        foreach ($unlinked_aggregations as $name => $aggregation) {
            $aggregation['url'] = "/elasticsearch/facet/$name?" . $_SERVER['QUERY_STRING'];
            $aggregations[$name] = $aggregation;
        }

        $this->view->assign('constraint_list', new Elasticsearch_Model_SearchConstraintList());
        $this->view->assign('aggregations', $aggregations);
        $this->view->assign('query', $query);
        $this->view->assign('results', $results);
        $this->view->assign('sorts', $sorts);
    }

    private function _getSearchParams(): array
    {
        return [
            'q' => $this->_request->q, // search terms
        ];
    }

    private function _getSortParams(): array
    {
        $sort = [];
        if ($this->_request->sort_field) {
            $sort['field'] = $this->_request->sort_field;
            if ($this->_request->sort_dir) {
                $sort['dir'] = $this->_request->sort_dir;
            }
        }
        return $sort;
    }

    /**
     * @return array
     * @throws Elasticsearch_Exception_BadQueryException
     */
    private function buildSortOptionList(): array
    {
        $current_sort = new Elasticsearch_Model_Sort('date', 'asc');

        if ($this->_request->sort) {
            $sort_dir = $this->_request->sort_dir ?: 'asc';
            $current_sort = new Elasticsearch_Model_Sort($this->_request->sort, $sort_dir);
        }

        $sort_options = [
            $this->buildSortOption('Newest', 'date', 'desc'),
            $this->buildSortOption('Oldest', 'date', 'asc'),
            $this->buildSortOption('Sender (A-Z)', 'facet_sender'),
            $this->buildSortOption('Sender (Z-A)', 'facet_sender', 'desc'),
            $this->buildSortOption('From (A-Z)', 'facet_from'),
            $this->buildSortOption('From (Z-A)', 'facet_from', 'desc'),
            $this->buildSortOption('Relevance', '_score', 'desc')
        ];

        return array_map(function (stdClass $option) use ($current_sort) {
            if ($option->sort == $current_sort) {
                $option->selected = 'selected';
            } else {
                $option->selected = false;
            }
            return $option;
        }, $sort_options);
    }

    /**
     * @param string $label
     * @param string $field
     * @param string $direction
     * @return stdClass
     * @throws Elasticsearch_Exception_BadQueryException
     */
    private function buildSortOption(string $label, string $field = 'date', string $direction = 'asc'): \stdClass
    {
        $option = new stdClass();
        $option->sort = new Elasticsearch_Model_Sort($field, $direction);
        $option->url = $option->sort->url();
        $option->label = $label;
        return $option;
    }

    public function facetAction(): void
    {
        $name = $this->_request->name;

        $builder = new Elasticsearch_QueryBuilder();

        $aggregation = Elasticsearch_Config::custom()->getAggregations($name)[0];


        $aggregation->setSize(2000000);


        $query = $builder->build($_GET, [$aggregation]);
        $query->limit(0);

        $acl = Zend_Registry::get('bootstrap')->getResource('Acl');
        if (!$acl->isAllowed(current_user(), 'Search', 'showNotPublic')) {
            $query->addFilter(Elasticsearch_Model_TermQuery::build('public', true));
        }


        //return self::sendSearchQuery($query);
    }
}
