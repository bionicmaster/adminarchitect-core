<?php

namespace Terranet\Administrator\Field;

/**
 * Class SectionView
 * @author Larry Mckuydee
 */
class SectionView extends Field
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
