<?php
require_once(__DIR__ . '/TmhTransformer.php');

class TmhTransform extends TmhTransformer
{
    public function prepare()
    {
        $this->transformed['locale'] =$this->domain->locale();
        $this->transformed['type'] = $this->route->type();
        $this->transformed['title'] = [];
        $this->transformed['html'] = [];
        $this->transformed['specimens'] = [];
        $this->transformed['references'] = [];
        $this->transformed['navigation'] = [];
        $this->transformed['top_menu'] = ['alternatives' => [], 'languages' => []];
        if ($this->route->type() == 'emperor_coin') {
            $this->prepareEmperorCoin();
        } else if ($this->route->type() == 'specimen') {
            $this->prepareSpecimen();
        }
        ksort($this->transformed);
        return $this->transformed;
    }

    private function prepareEmperorCoin()
    {
        $this->html();
        $this->identifiers();
        $this->labels();
        $this->src('specimen_src');
        $this->title();
        $this->specimens();
        $this->references();
        $this->topMenu();
        $this->navigation();
        $this->unsetTransformed();
    }

    private function prepareSpecimen()
    {
        $this->html();
        $this->topMenu();
        $this->title();
    }
}