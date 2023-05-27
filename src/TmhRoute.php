<?php
require_once(__DIR__ . '/TmhRouteRelation.php');

class TmhRoute extends TmhRouteRelation
{
    public function alternatives()
    {
        return $this->route['alternatives'];
    }

    public function ancestor()
    {
        return $this->route['ancestor'];
    }

    public function ancestors()
    {
        return $this->route['ancestors'];
    }

    public function descendants()
    {
        return $this->route['descendants'];
    }

    public function references()
    {
        return $this->route['references'];
    }

    public function resources()
    {
        return $this->route['resources'];
    }

    public function route()
    {
        return $this->route;
    }

    public function routes()
    {
        return $this->routes;
    }

    public function setAncestorTitle($ancestor, $title)
    {
        $this->route['ancestors'][$ancestor]['title'] = $title;
    }

    public function setReference($reference, $uuid)
    {
        $this->route['references'][$uuid] = $reference;
    }

    public function setRoute($routes, $domain)
    {
        $this->routes = $routes;
        if (!array_key_exists('REDIRECT_QUERY_STRING', $_SERVER)) {
            $this->route = $this->routes[TMH_HOME];
            $this->setAncestors($domain);
            $this->setDescendants();
            $this->setResources();
            $this->setOtherMimeTypes();
            $this->setReferences();
            return;
        }

        parse_str($_SERVER['REDIRECT_QUERY_STRING'], $fields);
        foreach ($this->routes as $route) {
            if ($route['route'] == $fields['title']) {
                $this->route = $route;
                $this->setAncestors($domain);
                $this->setDescendants();
                $this->setResources();
                $this->setOtherMimeTypes();
                $this->setReferences();
                return;
            }
        }

        $this->route = TMH_NOT_FOUND;
    }

    public function setSiblings($siblings)
    {
        $this->route['siblings'] = $siblings;
    }

    public function siblings()
    {
        return $this->route['siblings'];
    }

    public function template()
    {
        return $this->getTemplate($this->route);
    }

    public function type()
    {
        return $this->route['type'];
    }

    public function uuid()
    {
        return $this->route['uuid'];
    }

    private function getTemplate($route) {
        return $route['type'] ? $route['type'] . '/' . $route['uuid'] : $route['uuid'];
    }

    private function setResources()
    {
        $resources = [];
        foreach ($this->ancestors as $ancestor) {
            $resources[] = $ancestor['type'] ?: 'system';
            $resources[] = $this->getTemplate($ancestor);
        }
        $resources[] = $this->route['type'] ?: 'system';
        $resources[] = $this->template();
        sort($resources);
        $this->route['resources'] = array_unique($resources);
    }
}
