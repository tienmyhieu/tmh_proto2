<?php
require_once(__DIR__ . '/TmhComponent.php');

class TmhElement
{
    protected function a($attributes, $innerHtml, $newLines = false): string
    {
        return $this->element('a', $attributes, $this->newLines($innerHtml, $newLines));
    }

    protected function div($attributes, $innerHtml, $newLines = true): string
    {
        return $this->element('div', $attributes, $this->newLines($innerHtml, $newLines));
    }

    protected function img($attributes): string
    {
        return $this->selfClosingElement('img', $attributes);
    }

    protected function span($attributes, $innerHtml, $newLines = false): string
    {
        return $this->element('span', $attributes, $this->newLines($innerHtml, $newLines));
    }

    private function attributesString($attributes): string
    {
        $attributesString = ' ';
        foreach ($attributes as $key => $value) {
            $attributesString .= $key . '="' . $value . '" ';
        }
        return substr($attributesString, 0, -1);
    }

    private function element($element, $attributes, $innerHtml): string
    {
        $attributes = $this->scrubAttributes($attributes);
        return '<' . $element . $this->attributesString($attributes) . '>' . $innerHtml  .'</' . $element . '>';
    }

    private function newLines($innerHtml, $newLines)
    {
        return $newLines ? PHP_EOL . $innerHtml . PHP_EOL : $innerHtml;
    }

    private function selfClosingElement($element, $attributes): string
    {
        $attributes = $this->scrubAttributes($attributes);
        return '<' . $element . $this->attributesString($attributes) . ' />';
    }

    private function scrubAttributes($attributes)
    {
        $validAttributes = ['class', 'href', 'hreflang', 'id', 'src', 'target', 'title'];
        foreach ($attributes as $key => $value) {
            if(!in_array($key, $validAttributes)) {
                unset($attributes[$key]);
            }
        }
        ksort($attributes);
        return $attributes;
    }
}