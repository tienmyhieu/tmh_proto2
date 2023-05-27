<?php

class TmhRouteRelation
{
    protected $ancestors = [];
    protected $references = [];
    protected $route;
    protected $routes;
    
    protected function getAncestorRoute($route)
    {
        if ($route['ancestor']) {
            $route['ancestor'] = $this->getAncestorRoute($this->routes[$route['ancestor']]);
        }
        return $route;
    }

    protected function setAncestor($route, $domain)
    {
        if (is_array($route['ancestor'])) {
            $this->setAncestor($route['ancestor'], $domain);
        }
        if ($route['uuid'] == TMH_HOME) {
            $route['route'] = HTTP . $domain['uuid'];
        }
        $this->ancestors[$route['uuid']] = [
            'route' => $route['route'],
            'title' => '',
            'type' => $route['type'],
            'uuid' => $route['uuid']
        ];
    }

    protected function setAncestors($domain)
    {
        $this->ancestors = [];
        if ($this->route['ancestor']) {
            $ancestors = $this->getAncestorRoute($this->routes[$this->route['ancestor']]);
            $this->setAncestor($ancestors, $domain);
        }
        if ($this->route['type'] == 'emperor_coin') {
            $route = $this->route;
            $route['route'] = '';
            $this->setAncestor($route, $domain);
        }
        $this->route['ancestors'] = $this->ancestors;
    }

    protected function setDescendants()
    {
        $descendants = array_filter($this->routes, function ($route) {
            return $route['ancestor'] == $this->route['uuid'];
        });
        $transformedDescendants = [];
        foreach ($descendants as $descendant) {
            $transformedDescendants[$descendant['order']] = [
                'route' => $descendant['route'],
                'type' => $descendant['type'],
                'uuid' => $descendant['uuid']
            ];
        }
        $this->route['descendants'] = $transformedDescendants;
    }

    protected function setOtherMimeTypes()
    {
        $otherMimeTypes = [
            'api' => ['href' => 'api/' . $this->route['route'], 'title' => 'api', 'target' => '_blank'],
            'pdf' => ['href' => 'pdf/' . $this->route['route'], 'title' => 'pdf', 'target' => '_blank']
        ];
        $this->route['alternatives'] = $otherMimeTypes;
    }

    protected function setReferences()
    {
        $transformed = [];
        foreach ($this->route['references'] as $uuid) {
            $transformed[$uuid] = [
                'href' => $this->routes[$uuid]['route'],
                'id' => '',
                'page' => '',
                'plate' => '',
                'title' => ''
            ];
        }
        $this->route['references'] = $transformed;
    }
//    public function sitemap()
//    {
//        return $this->sitemap;
//    }
//
//    protected function setKey($route)
//    {
//        $route['key'] = $route['uuid'];
//        if ($route['ancestor']) {
//            $key = implode('.', array_keys($this->ancestors)) . '.' . $route['uuid'];
//            $route['key'] = $key;
//        }
//        return $route;
//    }
//
//    protected function setSiteMsp()
//    {
//        $sitemap = [];
//        $this->sitemap = $this->setSiteMapNode($this->routes, $sitemap, TMH_HOME);
//    }
//
//    protected function setSiteMapNode($routes, $sitemap, $uuid)
//    {
//        $childNodes = $this->siteMapNodeHasChildren($routes, $uuid);
//        if (empty($childNodes)) {
//            return $routes[$uuid]['uuid'];
//        }
//
//        foreach ($childNodes as $childNode) {
//            $sitemap[$childNode['uuid']] = $childNode;
//            $sitemap[$childNode['uuid']] = $this->setSiteMapNode(
//                $routes,
//                $sitemap[$childNode['uuid']],
//                $childNode['uuid']
//            );
//        }
//
//        return $sitemap;
//    }
//
//    function siteMapNodeHasChildren($routes, $uuid): array
//    {
//        return array_filter($routes, function ($route) use($uuid) {
//            return $route['ancestor'] == $uuid;
//        });
//    }
//
//    protected function setRoutes($routes)
//    {
//        foreach ($routes as $uuid => $route) {
//            $this->routes[$uuid] = $route;
//        }
//    }
}