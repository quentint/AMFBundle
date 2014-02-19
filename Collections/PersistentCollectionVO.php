<?php
/**
 * Created by Jan Ooms
 */

namespace Tecbot\AMFBundle\Collections;

use Doctrine\Common\Collections\ArrayCollection;

class PersistentCollectionVO extends ArrayCollection {
    /**
     * PersistentCollectionVO coming from the client are always initialized
     */
    public function isInitialized() {
        return true;
    }

    public function __set($name, $value) {
        if ($name == "source")
            $this->__construct($value);
    }

}