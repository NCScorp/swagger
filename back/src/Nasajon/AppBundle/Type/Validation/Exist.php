<?php

namespace Nasajon\AppBundle\Type\Validation;

use Nasajon\MDABundle\Type\Validation\Exist as ParentConstraint;

/**
 * @Annotation
 */
class Exist extends ParentConstraint {
    /**
     * Quando true: será utilizada a entidade que engloba o objeto validado para buscar os construtores.
     */
    public $use_parent_form_entity;
}
