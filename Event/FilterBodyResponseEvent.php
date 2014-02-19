<?php

namespace Tecbot\AMFBundle\Event;

use Doctrine\ORM\PersistentCollection;
use MyProject\Proxies\__CG__\stdClass;
use Symfony\Component\EventDispatcher\Event;

use Tecbot\AMFBundle\Amf\BodyRequest;
use Tecbot\AMFBundle\Helpers\FlashArrayCollection;
use ZendAmf\Value\Messaging\ArrayCollection;

/**
 * Allows to filter a body response
 *
 * You can call getResponse() to retrieve the current response. With
 * setResponse() you can set a new response that will be returned to the
 * client.
 *
 * @author Thomas Adam <thomas.adam@tecbot.de>
 */
class FilterBodyResponseEvent extends Event
{
    /**
     * The current body request
     *
     * @var BodyRequest
     */
    private $bodyRequest;


    private $nesting;
    /**
     * The body response object
     *
     * @var mixed
     */
    private $bodyResponse;

    /**
     * Constructor.
     *
     * @param BodyRequest $bodyRequest
     * @param mixed       $bodyResponse
     */
    public function __construct(BodyRequest $bodyRequest, $bodyResponse)
    {
        $this->bodyRequest = $bodyRequest;

        $this->setBodyResponse($bodyResponse);
    }

    /**
     * Returns the current body request object
     *
     * @return BodyRequest
     */
    public function getBodyRequest()
    {
        return $this->bodyRequest;
    }

    /**
     * Returns the current body response object
     *
     * @return mixed
     */
    public function getBodyResponse()
    {
        return $this->bodyResponse;
    }

    /**
     *  Sets a new body response object
     *
     * @param mixed $bodyResponse
     */
    public function setBodyResponse($bodyResponse)
    {
        if ($bodyResponse instanceof \Doctrine\Common\Collections\ArrayCollection || $bodyResponse instanceof \Doctrine\ORM\PersistentCollection) {
            $this->bodyResponse = $this->writeCollection($bodyResponse);
        } else {
            if(is_array($bodyResponse) || is_object($bodyResponse)) {
                $this->nesting = 0;
                $bodyResponse = $this->checkCollections($bodyResponse);
            }
            $this->bodyResponse = $bodyResponse;
        }
    }

    public function checkCollections($collection) {

        if ($collection instanceof \Doctrine\Common\Collections\ArrayCollection || $collection instanceof \Doctrine\ORM\PersistentCollection) {
            $collection = $this->writeCollection($collection);
        }
        if(is_object($collection)) {
            $item = get_object_vars($collection);
            foreach($item as $key => $value) {
                $collection->$key = $this->checkCollections($value);
            }
        }
        if(is_array($collection)) {
            foreach($collection as $key => $value) {
                $collection[$key] = $this->checkCollections($value);
            }
        }
        return $collection;
    }

    public function writeCollection(&$object) {


        // In order to get around the problems with ZendAMF and Iterator/foreach we create a vanilla object and use _explicitType to
        // map it on the client.  The source property maps to mx.collections.ArrayCollection::source.  We also need to add whether
        // or not the collection is initialized.
        $wrappedObject = new \stdClass();
        $wrappedObject->_explicitType = "flex.messaging.io.ArrayCollection";
        if ($object instanceof \Doctrine\ORM\PersistentCollection) {
            $wrappedObject->source = $object->unwrap()->toArray();
            $wrappedObject->isInitialized__ = $object->isInitialized() || (sizeof($wrappedObject->source) > 0);
        } else {
            $wrappedObject->source = $object->toArray();
            $wrappedObject->isInitialized__ = true;
        }

        return $wrappedObject;
    }
}
