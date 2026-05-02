<?php

namespace App\Repository;

use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stock>
 */
class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    /**
     * Returns all history entries for a product, ordered oldest → newest.
     *
     * @return Stock[]
     */
    public function findHistoryByProduct(int $productId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.product = :productId')
            ->andWhere('s.isHistoryEntry = :isHistory')
            ->setParameter('productId', $productId)
            ->setParameter('isHistory', true)
            ->orderBy('s.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns all main stock entries (excludes history entries).
     *
     * @return Stock[]
     */
    public function findMainStocks(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.isHistoryEntry = false OR s.isHistoryEntry IS NULL')
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the single main stock entry for a product (not a history entry).
     */
    public function findMainStockByProduct(int $productId): ?Stock
    {
        return $this->createQueryBuilder('s')
            ->where('s.product = :productId')
            ->andWhere('s.isHistoryEntry = false OR s.isHistoryEntry IS NULL')
            ->setParameter('productId', $productId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}