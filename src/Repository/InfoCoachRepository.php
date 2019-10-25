<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\InfoCoach;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method InfoCoach|null find($id, $lockMode = null, $lockVersion = null)
 * @method InfoCoach|null findOneBy(array $criteria, array $orderBy = null)
 * @method InfoCoach[]    findAll()
 * @method InfoCoach[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InfoCoachRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, InfoCoach::class);
    }

    /**
    * @return InfoCoach[] Returns an array of InfoCoach objects
    */    
    public function findSixByRating()
    {   
        //Requete custom page home, 6 triées par rating...
        return $this->createQueryBuilder('i')            
            ->addSelect('t')
            ->addSelect('u')
            ->join('i.team', 't')             
            ->join('i.user', 'u') 
            ->orderBy('i.rating', 'DESC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult()
        ;
    }   
    
    public function findOneByJoinReviewsByRating($coachId)
    {
       
        return $this->createQueryBuilder('i')
            ->addSelect('r')
            ->join('App\Entity\Review', 'r')       
            ->andWhere('r.id = :val')
            ->andWhere('i.id = :val')
            ->orderBy('r.rating', 'DESC')
            ->setParameter('val', $coachId)
            ->getQuery() 
            ->getResult()           
        ;
    }

    public function joinForCoachDetails($infoCoach)
    {
       
        return $this->createQueryBuilder('i')
            ->addSelect('r')
            ->addSelect('u')
            ->addSelect('g')
            ->addSelect('t')
            ->addSelect('rU')                     
            ->join('i.reviews', 'r')
            ->join('i.user', 'u') 
            ->join('i.game', 'g') 
            ->join('i.team', 't') 
            ->join('r.user', 'rU')             
            ->orderBy('r.rating', 'DESC')
            ->andWhere('u.infoCoach = :val')
            ->andWhere('r.infoCoach = i') 
            ->andWhere('t = i.team')           
            ->setParameter('val', $infoCoach)
            ->getQuery() 
            ->getOneOrNullResult()           
        ;
    }
                 
}
