<?php

class TmhBase
{
    public function __construct(TmhTransform $transform)
    {
        $transformed = $transform->prepare();
//        echo "<pre>";
//        print_r($transformed);
//        echo "</pre>";
    }
}