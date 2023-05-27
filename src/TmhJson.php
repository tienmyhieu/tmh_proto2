<?php

class TmhJson
{
    public function domains()
    {
        return $this->toKeyed($this->load(__DIR__ . '/resources/', 'domains'), 'uuid');
    }

    public function inscriptions()
    {
        return $this->load(__DIR__ . '/resources/', 'inscriptions');
    }

    public function locales($resourceLocales, $locale = ''): array
    {
        $allLocales = [];
        $localesDirectories = array_diff(scandir(__DIR__ . '/locales'), ['.', '..']);
        if ($locale) {
            $localesDirectories = [$locale];
        }
        foreach ($localesDirectories as $directory) {
            $locales = [];
            foreach ($resourceLocales as $resourceLocale) {
                $localeArray = $this->load(__DIR__ . '/locales/' . $directory . '/', $resourceLocale);
                $locales = array_merge_recursive($locales, $localeArray);
            }
            ksort($locales);
            $allLocales[$directory] = $locales;
        }
        return $allLocales;
    }

    public function load($path, $file, $associative=true)
    {
        $contents = '[]';
        if ($this->exists($path .  $file . '.json')) {
            $contents = file_get_contents($path .  $file . '.json');
        }
        return json_decode($contents, $associative);
    }

    public function otherRoutes($locale): array
    {
        $otherRoutes = [];
        $localesDirectories = array_diff(scandir(__DIR__ . '/locales'), ['.', '..']);
        foreach ($localesDirectories as $directory) {
            if ($directory == $locale) {
                continue;
            }
            $otherRoutes[$directory] = $this->routes($directory);
        }
        return $otherRoutes;
    }

    public function resources($resources): array
    {
        $merged = [];
        foreach ($resources as $resource) {
            $resourceArray = $this->load(__DIR__ . '/resources/',  $resource);
            $merged = array_merge_recursive($merged, $resourceArray);
        }
        ksort($merged);
        return $merged;
    }

    public function routes($locale=TMH_LOCALE)
    {
        return $this->toKeyed($this->load(__DIR__ . '/locales/' . $locale . '/', 'routes'), 'uuid');
    }

    public function template($template)
    {
        return $this->load(__DIR__ . '/templates/', $template);
    }

    private function exists($url): bool
    {
        return (false !== @file_get_contents($url, 0, null, 0, 1));
    }

    private function toKeyed($entities, $key)
    {
        $transformed = [];
        foreach ($entities as $entity) {
            if (array_key_exists($key, $entity)) {
                $transformed[$entity[$key]] = $entity;
            }
        }
        return $transformed ?: $entities;
    }
}
