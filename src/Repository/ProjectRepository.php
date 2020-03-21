<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function createQueryBuilder($alias, $indexBy = null)
    {
        return parent::createQueryBuilder($alias, $indexBy)
            ->addSelect('userProjects')
            ->leftJoin($alias . '.userProjects', 'userProjects');
    }

    public function findAll(){
        return $this->createQueryBuilder('p')
            ->orderBy('p.created_at','DESC')
            ->getQuery()
            ->getResult();
    }

    public function find($var, $lockMode = null, $lockVersion = null){
        return $this->createQueryBuilder('p')
            ->where('p.id = :id')
            ->setParameter('id', $var['id'])
            ->getQuery()
            ->getOneOrNullResult();
    }
}
