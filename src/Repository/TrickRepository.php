<?php

namespace App\Repository;

use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Trick::class);
    }

    public function findByAuthor(string $author)
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.user', 'u')
            ->andWhere('u.pseudo = :pseudo')
            ->setParameter('pseudo', $author)
            ->getQuery()
            ->getResult();
    }

    public function findByCategory(string $category)
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.category', 'c')
            ->andWhere('c.name = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Trick[] Returns an array of Trick objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Trick
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
