<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Game|null find($id, $lockMode = null, $lockVersion = null)
 * @method Game|null findOneBy(array $criteria, array $orderBy = null)
 * @method Game[]    findAll()
 * @method Game[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Game::class);
    }

    /**
    * @return Game[] Returns an array of Game objects
    */    
    public function findAllJoinCoachesByRating()
    {
        return $this->createQueryBuilder('g')
            ->addSelect('i')
            ->addSelect('u')
            ->addSelect('t')            
            ->join('g.infoCoaches', 'i') 
            ->join('i.user', 'u') 
            ->join('i.team', 't')    
            ->andWhere('u.infoCoach = i')  
            ->orderBy('i.rating', 'DESC')            
            ->getQuery()
            ->getResult()
        ;
    }
          
    public function findOneByJoinCoachesByRating($game): ?Game
    {
        return $this->createQueryBuilder('g')
            ->addSelect('i')
            ->addSelect('u') 
            ->addSelect('t')                  
            ->join('g.infoCoaches', 'i') 
            ->join('i.user', 'u') 
            ->join('i.team', 't')               
            ->andWhere('u.infoCoach = i')                            
            ->andWhere('g.name = :val')
            ->orderBy('i.rating', 'DESC')
            ->setParameter('val', $game)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
}
