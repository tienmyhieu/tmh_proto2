<?php

class TmhRender
{
    public function attributes($attributes): string
    {
        $html = '';
        foreach ($attributes as $key => $value) {
            $html .= ' ' . $key . '="' . $value . '"';
        }
        return $html;
    }

    public function childElements($element, $eol=PHP_EOL): string
    {
        $closingHtml = $element['selfClosing'] ? '' : '>';
        return $element['elements'] ? '>' . $eol . $this->elements($element['elements']) : $closingHtml;
    }

    public function closeElement($element): string
    {
        $class = array_key_exists('class', $element['attributes']) ? $element['attributes']['class'] : '';
        $addTabs = ['tmh_top_menu', 'tmh_languages', 'tmh_alternatives', 'tmh_navigation'];
        $tabs = in_array($class, $addTabs) ? str_repeat("\t", $element['tabs']) : '';
        if ($class == 'tmh_top_menu' || $class == 'tmh_navigation') {
            $tabs .= "\t\t\t\t";
        }
        $eol = PHP_EOL;
        return ($element['selfClosing'] ? '/>' : $tabs . '</' . $element['element']. '>') . $eol;
    }

    public function element($element): string
    {
        $html = str_repeat("\t", $element['tabs']) . $this->openElement($element);
        $html .= $this->innerHtml($element);
        $html .= $this->closeElement($element);
        return $html;
    }

    public function elements($elements): string
    {
        $html = '';
        if ($elements) {
            foreach ($elements as $element) {
                $html .= str_repeat("\t", $element['tabs']) . $this->openElement($element);
                $html .= $this->innerHtml($element);
                $html .= $this->closeElement($element);
            }
        }
        return $html;
    }

    public function innerHtml($element): string
    {
        $replacement = $element['innerHtml'];
        $eol = PHP_EOL;
        return strlen($element['innerHtml']) > 0 ? '>' . $replacement : $this->childElements($element, $eol);
    }

    public function openElement($element): string
    {
        return '<' . $element['element'] . $this->attributes($element['attributes']);
    }
}
