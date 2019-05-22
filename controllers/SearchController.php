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

        $this->view->assign('query', $query);
        $this->view->assign('results', $results);
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
}
