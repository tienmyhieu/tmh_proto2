<?php

class TmhDomain
{
    private $domain;
    private $domains;

    public function direction()
    {
        return $this->domain['direction'];
    }

    public function domain()
    {
        return $this->domain;
    }

    public function domains()
    {
        return $this->domains;
    }

    public function language()
    {
        return substr($this->domain['locale'], 0, 2);
    }

    public function locale()
    {
        return $this->domain['locale'];
    }

    public function setDomain($domains)
    {
        $this->domains = $domains;
        $domainExists = array_key_exists($_SERVER['HTTP_HOST'], $this->domains);
        $this->domain = $domainExists ? $this->domains[$_SERVER['HTTP_HOST']] : $this->domains[TMH_DOMAIN];
    }
}
