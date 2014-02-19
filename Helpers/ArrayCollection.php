<?php
/**
 * Created by Jan Ooms
 */

namespace Tecbot\AMFBundle\Helpers;

class ArrayCollection extends \ArrayObject {
    public $_explicitType = 'mx.collections.ArrayCollection';

    public function __construct($config = array())
    {
        parent::__construct($config, \ArrayObject::ARRAY_AS_PROPS);
    }
} 