<?php
require_once(__DIR__ . '/TmhComponent.php');

class TmhPdf extends TmhComponent
{
    private $html;
    /** var TCPDF **/
    private $pdf;
    private $render;
    public function __construct(TmhRender $render, TCPDF $pdf)
    {
        $this->render = $render;
        $this->pdf = $pdf;
    }

    public function output()
    {
        $fileName = str_replace(" ", "_", implode("_", $this->attributes['title']));
        $this->pdf->Output($fileName);
    }

    public function prepare($attributes, $elements)
    {
        parent::prepare($attributes, $elements);
        $this->pdf->AddPage();
        $this->setMetadata();
        $this->setCss();
        if ($this->attributes['type'] == 'emperor_coin') {
            $this->emperorCoin();
        }
    }

    private function citationRow($citation, $locale): string
    {
        $this->setFont($locale, 7);
        return '<tr><td>' . $citation . '</td></tr>';
    }

    private function emperorCoin()
    {
        $i = 0;
        $this->setFont($this->attributes['locale'], 14);
        $this->pdf->Write(1, implode(" - ", $this->attributes['title']));
        $html = '<table style="border: 1px solid #FFFFFF; width: 40%;">';
        foreach ($this->attributes['specimens'] as $specimen) {
            if ($specimen['images'] && $i < TMH_MAX_SPECIMENS) {
                $html .= $this->identifierRow($specimen['identifier']);
                $html .= $this->imageRow($specimen['images']);
                $html .= $this->metadataRow($specimen['diameter'], $specimen['weight']);
                $html .= $this->spacerRow();
                $html .= $this->spacerRow();
            }
            $i++;
        }
        $html .= '</table>';
        $html2 = '<table style="border: 1px solid #FFFFFF; width: 100%; padding: 2px;">';
        $html2 .= $this->headerRow($this->attributes['component_titles']['references'], $this->attributes['locale']);
        foreach ($this->attributes['references'] as $reference) {
            $html2 .= $this->citationRow($reference, $this->attributes['locale']);
        }
        $html2 .= '</table>';
        $this->pdf->SetXY(0, 22);
        $this->pdf->writeHTML("<br />" . $html);
        $this->pdf->SetXY(0, 202);
        $this->pdf->writeHTML("<br />" . $html2);
    }

    private function headerRow($citation, $locale): string
    {
        $this->setFont($locale, 24);
        return '<tr><td><b>' . $citation . '</b></td></tr>';
    }

    private function identifierRow($identifier): string
    {
        $this->setFont($this->attributes['locale'], 8);
        return '<tr><td colspan="2" style="font-weight: bold;">' . $identifier . '</td></tr>';
    }

    private function imageRow($images): string
    {
        $html = '<tr>';
        foreach ($images as $image) {
            $html .= '<td><img src="' . $image . '" /></td>';
        }
       return $html . '</tr>';
    }

    private function metadataRow($diameter, $weight): string
    {
        if ($diameter || $weight) {
            $this->setFont($this->attributes['locale'], 6);
            $hasDiameter = 0 < strlen($diameter);
            $hasWeight = 0 < strlen($weight);
            $metadata = $hasDiameter ? $diameter . $this->attributes['labels']['mm'] . ' - ' : '';
            $metadata .= $hasWeight ? $weight . $this->attributes['labels']['g'] : '';
            return '<tr><td colspan="2">' . $metadata . '</td></tr>';
        }
        return '';
    }

    private function setCss()
    {
        $css = TMH_CDN . 'css/tienmyhieu-' . $this->language . '.css';
        $this->html = '<style>' . file_get_contents($css) . '</style>';
    }

    private function setFont($locale, $size)
    {
        switch ($locale) {
            case 'ja-JP':
            case 'zh-CN':
            case 'zh-HK':
            case 'zh-TW':
                $this->pdf->setFont('msungstdlight', '', $size);
            break;
            default:
                $this->pdf->setFont('dejavusans', '', $size);
        }
    }

    private function setMetadata()
    {
        $this->pdf->SetCreator(TMH_DOMAIN);
        $this->pdf->SetAuthor(TMH_DOMAIN);
        $this->pdf->SetTitle(implode(" ", $this->attributes['title']));
        $this->pdf->SetSubject($this->attributes['title'][0]);
        $this->pdf->SetKeywords(implode(", ", $this->attributes['title']));
    }

    private function spacerRow(): string
    {
        return '<tr><td colspan="2">&nbsp;</td></tr>';
    }

    protected function specimenIdentifierHtml($identifier, $specimen): string
    {
        return $identifier;
    }

    protected function specimenImageHtml($image, $specimen): string
    {
        return $image;
    }

    protected function specimenWithoutImagesHtml($specimenInfoHtml): string
    {
        return '';
    }
}