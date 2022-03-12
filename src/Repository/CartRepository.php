<?php

namespace App\Repository;

use DateTime;
use App\Entity\Cart;
use App\Entity\Product;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Cart|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cart|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cart[]    findAll()
 * @method Cart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Cart $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Cart $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    
    public function viewHistory()    {

        return $this->findAll();
    }
    
    /**
     * @return Cart[]
     */
    public function list_item(bool $includeUnavailableProducts = false)    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.status = :s')
            ->setParameter('s', 'incart');

        if (!$includeUnavailableProducts) {
            $qb->andWhere('p.status = TRUE');
        }

        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * @return Cart
     */
    public function create_new($userId, $productId)    {
        // $user = $this->getDoctrine()->getRepository(User::class)->find($userId);
        // $product = $this->getDoctrine()->getRepository(Product::class)->find($productId);
        $user = $this->find($userId);
        $product = $this->find($productId);

        $cart = new Cart;
        $cart->setDate(new DateTime());
        $cart->setStatus('incart');
        $cart->setProducts($product);
        $cart->setUser($user);

        return $cart;
    }

    // /**
    //  * @return Cart[] Returns an array of Cart objects
    //  */
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
    public function findOneBySomeField($value): ?Cart
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
