<?php

namespace App\Transformers;

abstract class BaseTransformer
{
    /**
     * Transform the model instance into a CSV row.
     *
     * @param  object  $model
     * @return array
     */
    abstract public function transform($model);
}
