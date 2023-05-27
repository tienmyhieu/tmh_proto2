<?php

class TmhResource
{
    private $resources;

    public function locales($contentType)
    {
        if ($contentType == 'html') {
            $this->resources['locales'] = array_merge(['creative_commons', 'head'], $this->resources['locales']);
        }
        sort($this->resources['locales']);
        return array_unique($this->resources['locales']);
    }

    public function resources()
    {
        return $this->resources;
    }

    public function setResource($resources)
    {
        $this->resources = $resources;
    }
}