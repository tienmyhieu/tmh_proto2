<?php

class TmhOutput
{
    private $elementTree;
    private $html;
    private $pdf;
    private $transform;
    public function __construct(TmhElementTree $elementTree, TmhHtml $html, TmhPdf $pdf, TmhTransform $transform)
    {
        $this->elementTree = $elementTree;
        $this->html = $html;
        $this->pdf = $pdf;
        $this->transform = $transform;
    }

    public function output($mimeType)
    {
        $attributes = $this->transform->prepare();
        $this->elementTree->transform($attributes, $mimeType);
        if ($mimeType == 'html') {
            header('Content-Type: text/html; charset=utf-8');
            $this->html->prepare($attributes, $this->elementTree->elements());
            echo $this->html->output();
        } else if ($mimeType == 'json') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($attributes);
        } else if ($mimeType == 'pdf') {
            header("Content-type: application/pdf");
            $this->pdf->prepare($attributes, $this->elementTree->elements());
            $this->pdf->output();
        }
    }
}