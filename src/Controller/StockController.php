<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Form\StockType;
use App\Repository\StockRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/stock')]
#[IsGranted('ROLE_USER')]
class StockController extends AbstractController
{
    private ActivityLogger $activityLogger;

    public function __construct(ActivityLogger $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    #[Route('/', name: 'app_stock_index', methods: ['GET'])]
    public function index(Request $request, StockRepository $stockRepository): Response
    {
        $query = $request->query->get('q');

        if ($query) {
            $stocks = $stockRepository->createQueryBuilder('s')
                ->join('s.product', 'p')
                ->where('p.name LIKE :query OR s.id LIKE :query')
                ->andWhere('s.isHistoryEntry = false OR s.isHistoryEntry IS NULL')
                ->setParameter('query', '%' . $query . '%')
                ->getQuery()
                ->getResult();
        } else {
            $stocks = $stockRepository->createQueryBuilder('s')
                ->where('s.isHistoryEntry = false OR s.isHistoryEntry IS NULL')
                ->getQuery()
                ->getResult();
        }

        return $this->render('stock/index.html.twig', [
            'stocks' => $stocks,
        ]);
    }

    #[Route('/new', name: 'app_stock_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, StockRepository $stockRepository): Response
    {
        $stock = new Stock();
        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $stock->getProduct();
            $quantityToAdd = $stock->getQuantity();

            // Only match main stock entries, not history entries
            $existingStock = $product
                ? $stockRepository->findOneBy(['product' => $product, 'isHistoryEntry' => false])
                : null;

            if ($existingStock) {
                // Update existing stock quantity
                $existingStock->setQuantity($existingStock->getQuantity() + $quantityToAdd);
                $existingStock->setLastUpdated(new \DateTimeImmutable());

                // Create history entry only — do NOT persist $stock
                $historyEntry = new Stock();
                $historyEntry->setProduct($product);
                $historyEntry->setQuantity($quantityToAdd);
                $historyEntry->setCreatedBy($this->getUser());
                $historyEntry->setCreatedAt(new \DateTimeImmutable());
                $historyEntry->setLastUpdated(new \DateTimeImmutable());
                $historyEntry->setIsHistoryEntry(true);

                $entityManager->persist($historyEntry);
                $entityManager->flush();

                $product->setQuantity($existingStock->getQuantity());
                $entityManager->persist($product);
                $entityManager->flush();

                $this->activityLogger->logCreate(
                    $this->getUser(),
                    'Stock',
                    $existingStock->getId(),
                    sprintf('Stock restocked: %s +%d units', $product?->getName() ?? 'Unknown', $quantityToAdd),
                    [
                        'product'    => $product?->getName(),
                        'product_id' => $product?->getId(),
                        'quantity'   => $quantityToAdd,
                        'created_by' => $this->getUser()?->getUsername(),
                    ]
                );

            } else {
                // First time — persist $stock as the main entry
                $stock->setCreatedBy($this->getUser());
                $stock->setCreatedAt(new \DateTimeImmutable());
                $stock->setIsHistoryEntry(false);

                $entityManager->persist($stock);
                $entityManager->flush();

                // Also save it as the first history entry
                $historyEntry = new Stock();
                $historyEntry->setProduct($product);
                $historyEntry->setQuantity($quantityToAdd);
                $historyEntry->setCreatedBy($this->getUser());
                $historyEntry->setCreatedAt(new \DateTimeImmutable());
                $historyEntry->setLastUpdated(new \DateTimeImmutable());
                $historyEntry->setIsHistoryEntry(true);

                $entityManager->persist($historyEntry);

                if ($product) {
                    $product->setQuantity($stock->getQuantity());
                    $entityManager->persist($product);
                }

                $entityManager->flush();

                $this->activityLogger->logCreate(
                    $this->getUser(),
                    'Stock',
                    $stock->getId(),
                    sprintf('Stock: %s (ID: %d)', $product?->getName() ?? 'Unknown', $stock->getId()),
                    [
                        'product'    => $product?->getName(),
                        'product_id' => $product?->getId(),
                        'quantity'   => $stock->getQuantity(),
                        'created_by' => $stock->getCreatedBy()?->getUsername(),
                    ]
                );
            }

            $this->addFlash('success', 'Stock added successfully!');
            return $this->redirectToRoute('app_stock_index');
        }

        return $this->render('stock/new.html.twig', [
            'stock' => $stock,
            'form'  => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stock_show', methods: ['GET'])]
    public function show(Stock $stock, StockRepository $stockRepository): Response
    {
        $stockHistory = $stock->getProduct()
            ? $stockRepository->findHistoryByProduct($stock->getProduct()->getId())
            : [];

        return $this->render('stock/show.html.twig', [
            'stock'        => $stock,
            'stockHistory' => $stockHistory,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_stock_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Stock $stock, EntityManagerInterface $entityManager, StockRepository $stockRepository): Response
    {
        if (!$this->canEditOrDelete($stock)) {
            $this->addFlash('error', 'You do not have permission to edit this stock record.');
            return $this->redirectToRoute('app_stock_index');
        }

        $oldQuantity = $stock->getQuantity();

        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $snapshot = [
                'product'      => $stock->getProduct()?->getName(),
                'product_id'   => $stock->getProduct()?->getId(),
                'old_quantity' => $oldQuantity,
                'new_quantity' => $stock->getQuantity(),
            ];

            $entityManager->flush();

            $product = $stock->getProduct();
            if ($product) {
                $totalStock = $stockRepository->createQueryBuilder('s')
                    ->select('SUM(s.quantity)')
                    ->where('s.product = :product')
                    ->andWhere('s.isHistoryEntry = false OR s.isHistoryEntry IS NULL')
                    ->setParameter('product', $product)
                    ->getQuery()
                    ->getSingleScalarResult();

                $product->setQuantity((int)($totalStock ?? 0));
                $entityManager->persist($product);
                $entityManager->flush();
            }

            $this->activityLogger->logUpdate(
                $this->getUser(),
                'Stock',
                $stock->getId(),
                sprintf('Stock: %s (ID: %d)', $product?->getName() ?? 'Unknown', $stock->getId()),
                $snapshot
            );

            $this->addFlash('success', 'Stock updated successfully!');
            return $this->redirectToRoute('app_stock_index');
        }

        return $this->render('stock/edit.html.twig', [
            'stock' => $stock,
            'form'  => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stock_delete', methods: ['POST'])]
    public function delete(Request $request, Stock $stock, EntityManagerInterface $entityManager): Response
    {
        if (!$this->canEditOrDelete($stock)) {
            $this->addFlash('error', 'You do not have permission to delete this stock record.');
            return $this->redirectToRoute('app_stock_index');
        }

        if ($this->isCsrfTokenValid('delete' . $stock->getId(), $request->request->get('_token'))) {
            $product       = $stock->getProduct();
            $stockId       = $stock->getId();
            $productName   = $product?->getName() ?? 'Unknown';
            $stockQuantity = $stock->getQuantity();

            $snapshot = [
                'product'    => $productName,
                'product_id' => $product?->getId(),
                'quantity'   => $stockQuantity,
                'deleted_at' => (new \DateTimeImmutable())->format('c'),
            ];

            $this->activityLogger->logDelete(
                $this->getUser(),
                'Stock',
                $stockId,
                sprintf('Stock: %s (ID: %d)', $productName, $stockId),
                $snapshot
            );

            $entityManager->remove($stock);
            $entityManager->flush();

            if ($product) {
                $totalStock = $entityManager->getRepository(Stock::class)
                    ->createQueryBuilder('s')
                    ->select('SUM(s.quantity)')
                    ->where('s.product = :product')
                    ->andWhere('s.isHistoryEntry = false OR s.isHistoryEntry IS NULL')
                    ->setParameter('product', $product)
                    ->getQuery()
                    ->getSingleScalarResult();

                $product->setQuantity((int)($totalStock ?? 0));
                $entityManager->persist($product);
                $entityManager->flush();
            }

            $this->addFlash('success', 'Stock deleted successfully!');
        }

        return $this->redirectToRoute('app_stock_index');
    }

    private function canEditOrDelete(Stock $stock): bool
    {
        $currentUser = $this->getUser();

        if (!$stock->getCreatedBy()) {
            return true;
        }

        if (in_array('ROLE_ADMIN', $currentUser->getRoles()) || in_array('ROLE_STAFF', $currentUser->getRoles())) {
            return true;
        }

        return false;
    }
}