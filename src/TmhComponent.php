<?php
require_once(__DIR__ . '/TmhElement.php');

class TmhComponent extends TmhElement
{
    protected $attributes;
    protected $language;
    protected $locale;

    protected function image($attributes): string
    {
        $attributes['class'] = 'tmh_img';
        return $this->img($attributes);
    }

    protected function imageGroup($attributes, $innerHtml): string
    {
        $attributes['class'] = 'tmh_image_route';
        return $this->div($attributes, $innerHtml);
    }

    protected function prepare($attributes, $elements)
    {
        $this->attributes = $attributes;
        $this->locale = $this->attributes['locale'];
        $this->language = substr($this->locale, 0, 2);
    }

    protected function references()
    {

    }

    protected function specimen($attributes, $innerHtml): string
    {
        $attributes['class'] = 'tmh_specimen';
        return $this->div($attributes, $innerHtml);
    }

    protected function specimenHtml()
    {
        $i = 0;
        $html = '';
        foreach ($this->attributes['specimens'] as $specimen) {
            $specimenInfoParts = $this->specimenInfoParts($specimen);
            $specimenInfoParts['identifier'] = $this->specimenIdentifierHtml($specimenInfoParts['identifier'], $specimen);
            $specimenInfoHtml = $this->specimenInfo([], $specimenInfoParts);
            $imagesHtml = '';
            if ($i < TMH_MAX_SPECIMENS) {
                $imageHtml = '';
                $images = $this->specimenImages($specimen);
                foreach ($images as $image) {
                    if (!$image['is_spacer']) {
                        $imageHtml .= $this->specimenImageHtml($image['img'], $specimen);
                    } else {
                        $imageHtml .= $image['img'];
                    }
                }
                $imagesHtml .= $this->imageGroup([], $imageHtml) . PHP_EOL;
            } else {
                $specimenInfoHtml = $this->specimenWithoutImagesHtml($specimenInfoHtml);
            }
            $i++;
            $html .= $this->specimen($specimen, $specimenInfoHtml . $imagesHtml);
        }
        return $this->specimens([], $html) . PHP_EOL;
    }

    protected function specimenImages($specimen): array
    {
        $images = [];
        foreach ($specimen['images'] as $image) {
            $isSpacer = substr($image, -37) == TMH_SPACER_IMAGE;
            $imageAttributes = ['alt' => $specimen['title'], 'src' => $image];
            $images[] = ['img' => $this->image($imageAttributes), 'is_spacer' => $isSpacer];
        }
        return $images;
    }

    protected function specimenInfo($attributes, $specimenInfoParts): string
    {
        $attributes['class'] = 'tmh_specimen_info';
        $innerHtml = implode(' - ', $specimenInfoParts);
        return $this->span($attributes, $innerHtml);
    }

    protected function specimenInfoParts($specimen): array
    {
        $specimenInfoParts = ['identifier' => $specimen['identifier']];
        if (0 < strlen($specimen['diameter'])) {
            $specimenInfoParts['diameter'] = $specimen['diameter'] . $this->attributes['labels']['mm'];
        }
        if (0 < strlen($specimen['weight'])) {
            $specimenInfoParts['weight'] = $specimen['weight'] . $this->attributes['labels']['g'];
        }
        return $specimenInfoParts;
    }

    protected function specimens($attributes, $innerHtml): string
    {
        $attributes['class'] = 'tmh_specimens';
        return $this->div($attributes, $innerHtml);
    }
}