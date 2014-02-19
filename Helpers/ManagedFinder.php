<?php
/**
 * Created by Jan Ooms
 */

namespace Tecbot\AMFBundle\Helpers;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ManagedFinder {

    /**
     * @var EntityManager $em
     */
    public $em;

    /**
     * @var EntityRepository $reponame
     */
    public $repo;

    public $result;


    public function __construct(EntityManager $em, EntityRepository $repo) {
        $this->em = $em;
        $this->repo = $repo;
    }

    /**
     * @param $collection
     */
    public function matchAndSaveEntity($collection) {
        if(is_array($collection)) {
            foreach($collection as $key => $value) {
                if(!is_array($value)) {
                    $item = $this->repo->find($value->getId());
                    if($item) {
                        $collection[$key] = $this->em->merge($value);
                        $this->em->flush();
                    } else {
                        $this->em->persist($collection[$key]);
                        $this->em->flush();
                    }
                }
            }
            $this->result = $collection;
        } else {
            $item = $this->repo->find($collection->getId());
            if(!$item) {
                $this->em->persist($collection);
                $this->em->flush();
            } else {
               $this->em->merge($collection);
                $this->em->flush();
            }
            $this->result = $collection;
        }

        return $this->result;
    }
} 