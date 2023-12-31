<?php

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Author>
 *
 * @method Author|null find($id, $lockMode = null, $lockVersion = null)
 * @method Author|null findOneBy(array $criteria, array $orderBy = null)
 * @method Author[]    findAll()
 * @method Author[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }
    public function listAuthorByEmail()
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.email', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Author[] Returns an array of Author objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Author
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
public function findAuthorsByBookCountRange($minBookCount, $maxBookCount)
{
    $qb = $this->createQueryBuilder('a');
    
    if ($minBookCount !== null) {
        $qb->andWhere('a.nb_books >= :minBookCount')
            ->setParameter('minBookCount', $minBookCount);
    }

    if ($maxBookCount !== null) {
        $qb->andWhere('a.nb_books <= :maxBookCount')
            ->setParameter('maxBookCount', $maxBookCount);
    }

    return $qb->getQuery()->getResult();
}
public function findAuthorsWithNoBooks()
{
    return $this->createQueryBuilder('a')
        ->where('a.nb_books = 0')
        ->getQuery()
        ->getResult();
}
}
