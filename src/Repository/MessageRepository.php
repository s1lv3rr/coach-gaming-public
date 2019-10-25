<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\User;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * @return Message[] Returns an array of Message objects
     */
    
    public function findConversation($userOne, $userTwo)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.sender = :userOne AND m.receiver = :userTwo')   
            ->orWhere('m.receiver = :userOne AND m.sender= :userTwo')
            ->setParameter('userOne', $userOne)
            ->setParameter('userTwo', $userTwo)
            ->orderBy('m.createdAt', 'ASC')            
            ->getQuery()
            ->getResult()
        ;
    }
    
    /**
     * @return Message[] Returns an array of Message objects
     */
    
    public function findNewMessages(User $user)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.receiver = :user')
            ->andWhere('m.isRead = false')        
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'DESC')            
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Message[] Returns an array of Message objects
     */
    
    public function findMessagesByUser(User $user)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.receiver = :user')
            ->orWhere('m.sender = :user')                  
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'DESC')            
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Message
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
