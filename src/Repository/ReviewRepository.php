<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
    * @return Review[] Returns an array of Review objects
    */    
    public function findThreeByCreation()
    {   
        //Requete custom page home, 3 reviews triées par date de creation..
        return $this->createQueryBuilder('r')          
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
    * @return Review[] Returns an array of Review objects
    */    
    public function findReviewsByUser($infoCoachId)
    {   
        return $this->createQueryBuilder('r')
            ->addSelect('i')
            ->addSelect('t')                     
            ->addSelect('g')
            ->addSelect('u')  
            ->addSelect('rW')          
            ->join('r.infoCoach', 'i')
            ->join('i.team', 't')
            ->join('i.game', 'g')
            ->join('r.user', 'u')
            ->join('i.reviews', 'rW')
            ->andWhere('r.infoCoach = :val')
            ->setParameter('val', $infoCoachId)          
            ->orderBy('r.rating', 'DESC')            
            ->getQuery()
            ->getResult()
        ;
    }
            
    

    
    public function findOneByUserAndInfocoach($user, $infoCoach): ?Review
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.infoCoach = :infoCoach')
            ->setParameter('user', $user)
            ->setParameter('infoCoach', $infoCoach)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
}
