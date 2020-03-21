<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\OptimisticLockException;

/**
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function createQueryBuilder($alias, $indexBy = null)
    {
        return parent::createQueryBuilder($alias, $indexBy)
            ->addSelect('job')
            ->addSelect('userProjects')
            ->leftJoin($alias . '.job', 'job')
            ->leftJoin($alias . '.userProjects', 'userProjects');
    }

    public function findAll(){
        return $this->createQueryBuilder('u')
            ->getQuery()
            ->getResult();
    }

    public function countEmployee()
    {
        return $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function find($var, $lockMode = null, $lockVersion = null){
        return $this->createQueryBuilder('u')
            ->where('u.id = :id')
            ->setParameter('id', $var['id'])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getTopEmployee(){
        $res =  $this->createQueryBuilder('u')
            ->addSelect('SUM(userProjects.timeSpent * u.coutHoraire) as cout_total')
            ->groupBy("u.id")
            ->orderBy('cout_total','DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        return ['user'=>$res[0], 'coutTotal'=>$res['cout_total']];
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * @param UserInterface $user
     * @param string $newEncodedPassword
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf(
                'Instances of "%s" are not supported.',
                \get_class($user)
            ));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }
}
