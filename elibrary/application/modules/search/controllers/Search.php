<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Search extends Admin {

    public function __construct()
    {
        parent::__construct();

        $this->medialib = new Library\Media\Media;
    }

    public function index()
    {
        $term = $this->input->get('q');
        
        if (!empty($term)) {
            $category   = $this->input->get('category', '');
            $meta       = $this->input->get('meta', '');

            $results    = $this->medialib->search($term, $meta, $category)->get();
            $results    = pagination($results, 15, 'search')->appends(['q' => $term]);

            $data = [
                'term'      => $term,
                'category'  => $category,
                'meta'      => $meta,
                'results'   => $results,
            ];

            $this->template->build('result', $data);
        } else {
            $this->template->build('index');
        }
    }

}

/* End of file Search.php */
/* Location: ./application/modules/search/controllers/Search.php */