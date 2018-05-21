<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function findByUser(string $user)
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.user', 'u')
            ->andWhere('u.pseudo = :pseudo')
            ->setParameter('pseudo', $user)
            ->getQuery()
            ->getResult();
    }

    public function findByTrick(string $trick)
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.trick', 't')
            ->andWhere('t.name = :name')
            ->setParameter('name', $trick)
            ->getQuery()
            ->getResult();
    }

    public function findAll()
    {
        return $this->findBy([], ['updatedAt' => 'desc']);
    }

//    /**
//     * @return Comment[] Returns an array of Comment objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
