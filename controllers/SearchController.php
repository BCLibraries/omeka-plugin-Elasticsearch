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
        $limit = (int)get_option('per_page_public');
        $limit = $limit ?? 20;
        $page = $this->_request->page ?: 1;
        $start = ($page - 1) * $limit;

        $query = $this->_getSearchParams();
        $sort = $this->_getSortParams();

        // execute query
        $results = null;
        try {
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

        $this->view->assign('query', $query);
        $this->view->assign('results', $results);
        $this->view->assign('sorts', $sorts);
    }

    private function _getSearchParams(): array
    {
        $query = [
            'q' => $this->_request->q, // search terms
        ];
        return $query;
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
        $current_sort = new Elasticsearch_Model_Sort('_score', 'desc');

        if ($this->_request->sort) {
            $sort_dir = $this->_request->sort_dir ?: 'asc';
            $current_sort = new Elasticsearch_Model_Sort($this->_request->sort, $sort_dir);
        }

        $sort_options = [
            $this->buildSortOption('Newest', 'date', 'desc'),
            $this->buildSortOption('Oldest', 'date'),
            $this->buildSortOption('Sender (A-Z)', 'facet_sender'),
            $this->buildSortOption('Sender (Z-A)', 'facet_sender', 'desc'),
            $this->buildSortOption('From (A-Z)', 'facet_from'),
            $this->buildSortOption('From (Z-A)', 'facet_from','desc'),
            $this->buildSortOption('Relevance', '_score', 'desc')
        ];

        return array_map(function (stdClass $option) use ($current_sort) {
            if ($option->sort == $current_sort) {
                $option->selected = 'selected';
            }
            return $option;
        }, $sort_options);
    }

    private function buildSortOption(string $label, string $field, string $direction = 'asc')
    {
        $option = new stdClass();
        $option->sort = new Elasticsearch_Model_Sort($field, $direction);
        $option->url = $option->sort->url();
        $option->label = $label;
        return $option;
    }
}
