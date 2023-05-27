<?php
require_once(__DIR__ . '/TmhComponent.php');

class TmhHtml extends TmhComponent
{
    private $content = [];
    private $components;
    private $html;
    private $render;

    public function __construct(TmhRender $render)
    {
        $this->html = file_get_contents(__DIR__ . '/resources/html/template.html');
        $this->render = $render;
    }

    public function output()
    {
        return $this->html;
    }

    public function prepare($attributes, $elements)
    {
        parent::prepare($attributes, $elements);
        $this->setComponents($this->attributes['type']);
        $this->initializeContent($this->attributes['type']);
        $this->setMetadata();
        $componentMethodMap = [
            'content' => $this->setContent(),
            'content_title' => $this->setContentTitle(),
            'creative_commons' => $this->setCreativeCommons(),
            'navigation' => $this->setNavigation(),
            'top_menu' => $this->setTopMenu()
        ];
        foreach ($this->components as $component) {
            if ($component == 'top_menu' || $component == 'navigation' || $component == 'content_title') {
                $this->html = $this->strReplace($component, $this->render->element($elements[$component]), $this->html);
            } else {
                $this->html = $this->strReplace($component, $componentMethodMap[$component], $this->html);
            }
        }
    }

    private function initializeContent($type)
    {
        if ($type == 'emperor_coin') {
            $this->content = ['specimens', 'references'];
        }
    }

    private function setComponents($type)
    {
        $creativeCommonsTypes = ['emperor_coin', 'specimen'];
        $this->components = ['top_menu', 'navigation', 'content_title', 'content'];
        if (in_array($type, $creativeCommonsTypes)) {
            $this->components = array_merge(['creative_commons'], $this->components);
        }
    }

    private function setContent(): string
    {
        $contentMethodMap = [
            'specimens' => $this->setSpecimens(),
            'references' => $this->setReferences()
        ];
        $html = '';
        foreach ($this->content as $content) {
            $html .= $contentMethodMap[$content];
        }
        return $html;
    }

    private function setContentTitle(): string
    {
        $divAttributes = ['class' => 'tmh_content_title'];
        return $this->div($divAttributes, implode(" - ", $this->attributes['title']));
    }

    private function setCreativeCommons(): string
    {
        return '';
    }

    private function setMetadata()
    {
        $this->html = $this->strReplace('title', implode(" ", $this->attributes['title']), $this->html);
        $this->html = $this->strReplace('cdn', TMH_CDN,  $this->html);
        foreach ($this->attributes['html'] as $key => $value) {
            $this->html = $this->strReplace($key, $value, $this->html);
        }
    }

//    private function a($attributes, $innerHtml): string
//    {
//        $output = '<a class="tmh_a" ';
//        foreach ($attributes as $key => $value) {
//            $output .= $key . '="' . $value . '" ';
//        }
//        $output = substr($output, 0, -1);
//        $output .= '>' . $innerHtml .'</a>';
//        return $output;
//    }
//
//    private function div($className, $innerHtml): string
//    {
//        return '<div class="'. $className . '">' . PHP_EOL . $innerHtml . PHP_EOL . '</div>' . PHP_EOL;
//    }

//    private function img($alt, $class, $src): string
//    {
//        return '<img class="'. $class . '" alt="'. $alt . '" src="'. $src . '"/>';
//    }

//    private function span($className, $innerHtml): string
//    {
//        $class = $className ? ' class="' .$className . '"' : ';';
//        return '<span' . $class . '>' . PHP_EOL . $innerHtml . '</span>' . PHP_EOL;
//    }

    private function setNavigation(): string
    {
        $output = '';
        $separator = ' &raquo; ';
        $slice = true;
        $i = 0;
        foreach ($this->attributes['navigation'] as $ancestor) {
            $innerHtml = $ancestor['title'];
//            $ancestor['href'] = $ancestor['route'];
//            unset($ancestor['route']);
//            unset($ancestor['type']);
//            unset($ancestor['uuid']);
            $ancestor['class'] = 'tmh_a';
            if($ancestor['href'] || $i == 0) {
                $output .= $this->a($ancestor, $innerHtml) . $separator . PHP_EOL;
            } else {
                $output .= $this->span([], $ancestor['title']);
                $slice = false;
            }
            $i++;
        }
        $output = $slice ? substr($output, 0, -strlen($separator)) : $output;
        $divAttributes = ['class' => 'tmh_navigation'];
        return $this->div($divAttributes, $output);
    }

    private function setReferences(): string
    {
        return '';
    }

    private function setSpecimens(): string
    {
        return $this->specimenHtml();
    }

    private function setTopMenu(): string
    {
        $output = '';
        $separator = ' - ';
        $spanOutput = '';
        foreach ($this->attributes['top_menu']['alternatives'] as $type => $alternative) {
            $innerHtml = $type;
            $alternative['class'] = 'tmh_a';
            $spanOutput .= $this->a($alternative, $innerHtml) . $separator . PHP_EOL;
        }
        $spanAttributes = ['class' => 'tmh_alternatives'];
        $tmhAlternatives = $this->span($spanAttributes, substr($spanOutput, 0, -strlen($separator)));

        foreach ($this->attributes['top_menu']['languages'] as $language) {
            $innerHtml = $language['innerHtml'];
            unset($language['innerHtml']);
            $language['class'] = 'tmh_a';
            $output .= $this->a($language, $innerHtml) . $separator . PHP_EOL;
        }
        $divAttributes = ['class' => 'tmh_languages'];
        $tmhLanguages = $this->div(
            $divAttributes,
            substr($output, 0, -strlen($separator)) . PHP_EOL . $tmhAlternatives
        );
        $divAttributes = ['class' => 'tmh_top_menu'];
        return $this->div($divAttributes, $tmhLanguages);
    }

    protected function specimenIdentifierHtml($identifier, $specimen): string
    {
        $specimen['class'] = 'tmh_a';
        $specimen['id'] = str_replace(' ', '_', strtolower($identifier));
        return $this->a($specimen, $identifier);
    }

    protected function specimenImageHtml($image, $specimen): string
    {
        return $this->a($specimen, $image);
    }

    protected function specimenWithoutImagesHtml($specimenInfoHtml): string
    {
        return $specimenInfoHtml;
    }

    private function strReplace($search, $replace, $subject)
    {
        return str_replace("{{" . $search . "}}", $replace, $subject);
    }
}