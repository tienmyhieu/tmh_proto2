<?php

class TmhElementTree
{
    private $attributes;
    private $elements = [];
    private $mimeType;

    public function elements(): array
    {
        return $this->elements;
    }

    public function transform($attributes, $mimeType)
    {
        $this->attributes = $attributes;
        $this->mimeType = $mimeType;
        $this->baseElements();
        $this->templateElements();
        $this->creativeCommons();
    }

    private function baseElements()
    {
        if ($this->mimeType == 'html') {
            $this->elements['top_menu'] = $this->topMenu();
            $this->elements['navigation'] = $this->navigation();
        }
        $this->elements['content_title'] = $this->contentTitle();
    }

    private function contentTitle(): array
    {
        $title = implode(' - ', $this->attributes['title']);
        $h1 = $this->element([], 'h1', [], $title, false, 5);
        $div = $this->element(['class' => 'tmh_content_title'], 'div', [], $title, false, 0);
        return $this->mimeType == 'pdf' ? $h1 : $div;
    }

    private function creativeCommons()
    {
        $this->elements['creative_commons'] = $this->mimeType == 'pdf' ? $this->creativeCommonsPdf() : $this->creativeCommonsHtml();
    }

    private function creativeCommonsHtml(): array
    {
        return [];
    }

    private function creativeCommonsPdf(): array
    {
        return [];
    }

    private function element($attributes, $element, $elements, $innerHtml, $selfClosing, $tabs): array
    {
        return [
            'element' => $element,
            'attributes' => $attributes,
            'elements' => $elements,
            'innerHtml' => $innerHtml,
            'selfClosing' => $selfClosing,
            'tabs' => $tabs
        ];
    }

    private function emperorCoin(): array
    {
        return ['specimens' => $this->specimens(), 'references' => $this->references()];
    }

    private function languages(): array
    {
        $languages = [];
        foreach ($this->attributes['top_menu']['languages'] as $locale => $language) {
            $innerHtml = $language['innerHtml'];
            unset($language['innerHtml']);
            $language['class'] = 'tmh_a';
            $languages[$locale] = $this->element($language, 'a', [], $innerHtml, false, 6);
        }
        return $languages;
    }

    private function languagesAndMimeTypes(): array
    {
        $elements = $this->separatedElements($this->languages(), ' - ');
        $mimeTypes = $this->separatedElements($this->mimeTypes(), ' - ');
        $elements['mime_types'] = $this->element(['class' => 'tmh_alternatives'], 'span', $mimeTypes, '', false, 6);
        return $elements;
    }

    private function mimeTypes(): array
    {
        $mimeTypes = [];
        foreach ($this->attributes['top_menu']['alternatives'] as $key => $mimeType) {
            $mimeType['class'] = 'tmh_a';
            $mimeTypes[] = $this->element($mimeType, 'a', [], $key, false, 7);
        }
        return $mimeTypes;
    }

    private function navigation(): array
    {
        $navigations = $this->separatedElements($this->navigations(), '&raquo;', 'tmh_navigation_chevron');
        return $this->element(['class' => 'tmh_navigation'], 'div', $navigations, '', false, 0);
    }

    private function navigations(): array
    {
        $navigations = [];
        foreach ($this->attributes['navigation'] as $uuid => $navigation) {
            $innerHtml = $navigation['innerHtml'];
            unset($navigation['innerHtml']);
            if ($navigation['href']) {
                $navigation['class'] = 'tmh_a';
                $navigations[] = $this->element($navigation, 'a', [], $innerHtml, false, 5);
            } else {
                $navigations[] = $this->element($navigation, 'span', [], $innerHtml, false, 5);
            }
        }
        return $navigations;
    }

    private function references(): array
    {
        return $this->mimeType == 'pdf' ? $this->referencesPdf() : $this->referencesHtml();
    }

    private function referencesHtml(): array
    {
        return $this->element(['class' => 'tmh_references'], 'div', [], '', false, 5);
    }

    private function referencesPdf(): array
    {
        return $this->element([], 'table', [], '', false, 5);
    }

    private function separatedElements($elements, $separator, $spanClass = ''): array
    {
        $i = 0;
        $separated = [];
        foreach($elements as $element) {
            $tabs = $element['tabs'];
            $separated[] = $element;
            if ($i < count($elements) - 1) {
                $attributes = $spanClass ? ['class' => $spanClass]: [];
                $separated[] = $this->element($attributes, 'span', [], $separator, false, $tabs);
            }
            $i++;
        }
        return $separated;
    }

    private function specimen(): array
    {
        $pdfElements = [];
        $htmlElements = [];
        return $this->mimeType == 'pdf' ? $pdfElements : $htmlElements;
    }

    private function specimens(): array
    {
        return $this->mimeType == 'pdf' ? $this->specimensPdf() : $this->specimensHtml();
    }

    private function specimensHtml(): array
    {
        return $this->element(['class' => 'tmh_specimens'], 'div', [], '', false, 5);
    }

    private function specimensPdf(): array
    {
        return $this->element([], 'table', [], '', false, 5);
    }

    private function templateElements()
    {
        switch ($this->attributes['type']) {
            case 'emperor_coin':
                $this->elements['content'] = $this->emperorCoin();
                break;
            case 'specimen':
                $this->elements['content'] = $this->specimen();
                break;
        }
    }

    private function topMenu(): array
    {
        $languagesAndMimeTypes = $this->languagesAndMimeTypes();
        $topMenuElements = $this->element(['class' => 'tmh_languages'], 'div', $languagesAndMimeTypes, '', false, 5);
        return $this->element(['class' => 'tmh_top_menu'], 'div', [$topMenuElements], '', false, 0);
    }
}