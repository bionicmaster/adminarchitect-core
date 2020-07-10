<?php

namespace Terranet\Administrator\Field;

/**
 * Class View
 * @author Larry Mckuydee
 */
class View extends Field
{
    protected $customTemplate;

    public function view(string $viewPath) {
        $this->customTemplate = $viewPath;
    }
    
    protected function template(string $page, string $field = null): string
    {
        return $this->customTemplate;
    }
}
