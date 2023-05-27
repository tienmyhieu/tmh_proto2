<?php

class TmhLocale
{
    private $locales;

    public function get($key)
    {
        return array_key_exists($key, $this->locales) ? $this->locales[$key] : $key;
    }

    public function locales()
    {
        return $this->locales;
    }

    public function setAllLocales($locales)
    {
        $this->locales = $locales;
    }

    public function setLocale($locale)
    {
        $this->locales = $this->locales[$locale];
    }
}
