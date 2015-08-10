<?php

namespace ACSEO\Bundle\BaseRestBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class RestLogRepository extends EntityRepository
{
    /**
     * Find the latest logs.
     */
    public function findLatest()
    {
        $qb = $this->createQueryBuilder('l');

        $qb->add('orderBy', 'l.id DESC');

        $qb->setMaxResults(200);

        //Get our query
        $q = $qb->getQuery();

        //Return result
        return $q->getResult();
    }
}
