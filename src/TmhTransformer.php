<?php

class TmhTransformer
{
    private $allLocales;
    protected $domain;
    protected $locale;
    protected $json;
    protected $resource;
    private $resources;
    protected $route;
    protected $transformed;
    protected $type;

    public function __construct(
        TmhDomain $domain,
        TmhJson $json,
        TmhLocale $locale,
        TmhResource $resource,
        TmhRoute $route,
        string $type
    ) {
        $this->domain = $domain;
        $this->json = $json;
        $this->locale = $locale;
        $this->resource = $resource;
        $this->route = $route;
        $this->type = $type;
        $this->domain->setDomain($this->json->domains());
        $locale = $this->domain->locale();
        $this->route->setRoute($this->json->routes($locale), $this->domain->domain());
        $this->resource->setResource($this->json->resources($this->route->resources()));
        $this->resources = $this->resource->resources();
        $this->setLocales($locale);
        $this->setSiblings($locale);
        $this->setAncestorTitles();
        $this->setReferences();
//        echo "<pre>";
//        print_r($this->route->route());
//        print_r($this->resources);
//        echo "</pre>";
    }

    protected function html()
    {
        $transformed = [];
        foreach ($this->resources['html'] as $key => $value) {
            if ($key == 'keywords') {
                $transformed[$key] = implode(", ", $this->transformAttributeParts($value));
            } else {
                $transformed[$key] = $this->transformAttribute($value);
            }
        }
        $transformed['dir'] = $this->domain->direction();
        $transformed['language'] = $this->domain->language();
        $this->transformed['html'] = $transformed;
    }

    protected function identifiers()
    {
        $transformed = [];
        foreach ($this->resources['identifiers'] as $uuid => $identifierParts) {
            $transformed[$uuid] = implode(" ", $this->transformAttributeParts($identifierParts));
        }
        $this->transformed['identifiers'] = $transformed;
    }

    protected function labels()
    {
        $transformed = [];
        foreach ($this->resources['labels'] as $name => $label) {
            $transformed[$name] = $this->transformAttribute($label);
        }
        $this->transformed['labels'] = $transformed;
    }

    protected function navigation()
    {
        $navigation = [];
        foreach ($this->route->ancestors() as $ancestor) {
            $ancestor['href'] = $ancestor['route'];
            unset($ancestor['route']);
            unset($ancestor['type']);
            unset($ancestor['uuid']);
            $ancestor['innerHtml'] = $ancestor['title'];
            $navigation[] = $ancestor;
        }
        $this->transformed['navigation'] = $navigation;
    }

    protected function references()
    {
        $transformed = [];
        foreach ($this->route->references() as $uuid => $reference) {
            $citation = implode('', $this->transformAttributeParts($this->resources['citations'][$uuid]));
            $title = implode('', $this->transformAttributeParts($this->resources['titles'][$uuid]));
            $citation = $this->strReplace('title', $title, $citation);
            if ($reference['page']) {
                $citation = $this->strReplace('page', $reference['page'], $citation);
            }
            if ($reference['plate']) {
                $citation = $this->strReplace('plate', $reference['plate'], $citation);
            }
            $transformed[$uuid] = $citation;
        }
        $this->transformed['references'] = $transformed;
    }

    protected function specimens()
    {

        $transformed = [];
        foreach ($this->route->descendants() as $descendant) {
            $images = [];
            if (array_key_exists($descendant['uuid'], $this->resources['specimens'])) {
                foreach ($this->resources['specimens'][$descendant['uuid']] as $image) {
                    $images[$image] = $this->transformed['specimen_src'][$image];
                }
            }
            $transformed[$descendant['uuid']] = [
                'diameter' => $this->resources['diameter'][$descendant['uuid']],
                'identifier' => $this->transformed['identifiers'][$descendant['uuid']],
                'images' => $images,
                'href' => $descendant['route'],
                'title' => implode(" ", $this->transformed['title']),
                'weight' => $this->resources['weight'][$descendant['uuid']],
            ];
        }
        $this->transformed['specimens'] = $transformed;
    }

    protected function src(string $resourceKey)
    {
        $transformed = [];
        foreach ($this->resources[$resourceKey] as $uuid => $srcItem) {
            $transformed[$uuid] = $this->cdnPreviewSizePath($this->getImageType($resourceKey)) . $srcItem;
        }
        $this->transformed[$resourceKey] = $transformed;
    }

    protected function title()
    {
        $titleUuid = $this->route->uuid();
        if ($this->route->type() == 'specimen') {
            $titleUuid = $this->route->ancestor();
        }
        $this->transformed['title'] = $this->transformAttributeParts($this->resources['titles'][$titleUuid]);
        $this->transformed['component_titles']['references'] = $this->transformAttribute('locales.references');
    }

    protected function topMenu()
    {
        $this->transformed['top_menu'] = [
            'languages' => $this->route->siblings(),
            'alternatives' => $this->route->alternatives()
        ];
    }

    protected function unsetTransformed()
    {
        if ($this->route->type() == 'emperor_coin') {
            unset($this->transformed['identifiers']);
            unset($this->transformed['specimen_src']);
            if ($this->type == 'json') {
                unset($this->transformed['html']);
                unset($this->transformed['labels']);
                unset($this->transformed['locale']);
                unset($this->transformed['navigation']);
                unset($this->transformed['top_menu']);
                unset($this->transformed['type']);
            }
//            unset($this->transformed['title']);
        }
    }

    private function getImageType($resourceKey): string
    {
        return $resourceKey == 'specimen_src' ? 'images' : 'uploads';
    }

    private function cdnPreviewSizePath(string $imageType): string
    {
        return $imageType == 'uploads' ? TMH_UPLOADS_128 : TMH_IMAGES_128;
    }

    private function setAncestorTitles()
    {
        $specialTypes = ['emperor_coin'];
        $titles = $this->resources['titles'];
        $transformedTitles = [];
        foreach ($titles as $uuid => $titleParts) {
            if ($uuid == $this->route->ancestor() && $this->route->type() == 'specimen') {
                $transformedTitles[$this->route->ancestor()] = $this->transformAttribute($titleParts[1]);
            } else if ($uuid == $this->route->uuid() && in_array($this->route->type(), $specialTypes)) {
                $transformedTitles[$uuid] = $this->transformAttribute($titleParts[1]);
            } else {
                $transformedTitles[$uuid] = implode(" ", $this->transformAttributeParts($titleParts));
            }
        }
        $ancestors = $this->route->ancestors();
        foreach ($ancestors as $ancestor) {
            $this->route->setAncestorTitle($ancestor['uuid'], $transformedTitles[$ancestor['uuid']]);
        }
    }

    private function setLocales($locale)
    {
        if ($this->type == 'html') {
            $this->locale->setAllLocales($this->json->locales($this->resource->locales($this->type)));
            $this->allLocales = $this->locale->locales();
        } else {
            $this->locale->setAllLocales($this->json->locales($this->resource->locales($this->type), $locale));
        }
        $this->locale->setLocale($locale);
    }

    private function setReferences()
    {
        foreach ($this->route->references() as $uuid => $reference) {
            $reference['id'] = $this->resources['ids'][$uuid];
            $reference['page'] = $this->resources['pages'][$uuid];
            if (array_key_exists($uuid, $this->resources['plates'])) {
                $reference['plate'] = $this->resources['plates'][$uuid];
            }
            $reference['title'] = implode(" ", $this->transformAttributeParts($this->resources['titles'][$uuid]));
            $this->route->setReference($reference, $uuid);
        }
    }

    private function setSiblings($locale)
    {
        $this->route->setSiblings([]);
        if ($this->type == 'html') {
            $domains = [];
            foreach ($this->domain->domains() as $domain) {
                if ($domain['default'] == '1') {
                    $domains[$domain['locale']] = ['domain' => $domain['uuid'], 'native_name' => $domain['native_name']];
                }
            }
            $titleUuid = $this->route->uuid();
            if ($this->route->type() == 'specimen') {
                $titleUuid = $this->route->ancestor();
            }
            $titles = $this->resources['titles'][$titleUuid];
            $otherRoutes = $this->json->otherRoutes($locale);
            $siblingRoutes = [];
            foreach ($otherRoutes as $otherLocale => $otherRoute) {
                $languageKey = "locales.language_" . strtolower(str_replace("-", "_", $otherLocale));
                $tmpTitles = [];
                foreach ($titles as $title) {
                    $tmpTitles[] = $this->allLocales[$otherLocale][str_replace('locales.', '', $title)];
                }
                $title = $this->transformAttribute($languageKey) . ' - ' . implode(' ', $tmpTitles);
                $language = substr($otherLocale, 0, 2);
                $domain = HTTP . $domains[$otherLocale]['domain'] . '/';
                $siblingRoutes[$otherLocale] = [
                    'hreflang' => $language == 'zh' ? $otherLocale : $language,
                    'innerHtml' => $domains[$otherLocale]['native_name'],
                    'href' => $domain . $otherRoute[$this->route->uuid()]['route'],
                    'title' => $title
                ];
            }
            $this->route->setSiblings($siblingRoutes);
        }
    }

    private function transformAttributeParts(array $attributeParts): array
    {
        $transformedParts = [];
        foreach ($attributeParts as $attributePart) {
            $transformedParts[] = $this->transformAttribute($attributePart);
        }
        return $transformedParts;
    }

    private function transformAttribute(string $attribute): string
    {
        if (preg_match('/(locales)(\.)(.+)/', $attribute, $matches)) {
            $attribute = $this->locale->get($matches[3]);
        }
        return $attribute;
    }

    private function strReplace($search, $replace, $subject)
    {
        return str_replace("{{" . $search . "}}", $replace, $subject);
    }
}