<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/product')]
#[IsGranted('ROLE_STAFF')]
final class ProductController extends AbstractController
{
    private ActivityLogger $activityLogger;

    public function __construct(ActivityLogger $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                try {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );

                    $product->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload image: ' . $e->getMessage());
                    return $this->render('product/new.html.twig', [
                        'product' => $product,
                        'form' => $form,
                    ]);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
                    return $this->render('product/new.html.twig', [
                        'product' => $product,
                        'form' => $form,
                    ]);
                }
            }

            $product->setCreatedBy($this->getUser());

            $entityManager->persist($product);
            $entityManager->flush();

            $snapshot = [
                'name'       => $product->getName(),
                'price'      => $product->getPrice(),
                'image'      => $product->getImage(),
                'created_by' => $product->getCreatedBy()?->getUsername(),
            ];

            $this->activityLogger->logCreate(
                $this->getUser(),
                'Product',
                $product->getId(),
                sprintf('Product: %s (ID: %d)', $product->getName(), $product->getId()),
                $snapshot
            );

            $this->addFlash('success', 'Product created successfully!');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        if (!$this->canEditOrDelete($product)) {
            $this->addFlash('error', 'You do not have permission to edit this product. You can only edit your own records.');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        $oldImage = $product->getImage();

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Snapshot captured BEFORE flush to record old values
            $snapshot = [
                'name'  => $product->getName(),
                'price' => $product->getPrice(),
                'image' => $product->getImage(),
            ];

            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                try {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );

                    $product->setImage($newFilename);

                    if ($oldImage && $oldImage !== $newFilename) {
                        $oldImagePath = $this->getParameter('images_directory').'/'.$oldImage;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload image: ' . $e->getMessage());
                    return $this->render('product/edit.html.twig', [
                        'product' => $product,
                        'form' => $form,
                    ]);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
                    return $this->render('product/edit.html.twig', [
                        'product' => $product,
                        'form' => $form,
                    ]);
                }
            }

            $entityManager->flush();

            $this->activityLogger->logUpdate(
                $this->getUser(),
                'Product',
                $product->getId(),
                sprintf('Product: %s (ID: %d)', $product->getName(), $product->getId()),
                $snapshot
            );

            $this->addFlash('success', 'Product updated successfully!');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if (!$this->canEditOrDelete($product)) {
            $this->addFlash('error', 'You do not have permission to delete this product. You can only delete your own records.');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {

            if (method_exists($product, 'getOrders') && $product->getOrders()->count() > 0) {
                $this->addFlash('error', sprintf(
                    '❌ Cannot delete product "%s" because it has %d order(s) associated with it.',
                    $product->getName(),
                    $product->getOrders()->count()
                ));
                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
            }

            if (method_exists($product, 'getSales') && $product->getSales()->count() > 0) {
                $this->addFlash('error', sprintf(
                    '❌ Cannot delete product "%s" because it has %d sales record(s).',
                    $product->getName(),
                    $product->getSales()->count()
                ));
                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
            }

            if (method_exists($product, 'getStocks') && $product->getStocks()->count() > 0) {
                $this->addFlash('error', sprintf(
                    '❌ Cannot delete product "%s" because it has %d stock record(s). Please delete the stock entries first.',
                    $product->getName(),
                    $product->getStocks()->count()
                ));
                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
            }

            $productName = $product->getName();
            $productId   = $product->getId();

            $snapshot = [
                'name'       => $product->getName(),
                'price'      => $product->getPrice(),
                'image'      => $product->getImage(),
                'created_by' => $product->getCreatedBy()?->getUsername(),
                'deleted_at' => (new \DateTimeImmutable())->format('c'),
            ];

            $this->activityLogger->logDelete(
                $this->getUser(),
                'Product',
                $productId,
                sprintf('Product: %s (ID: %d)', $productName, $productId),
                $snapshot
            );

            if ($product->getImage()) {
                $imagePath = $this->getParameter('images_directory').'/'.$product->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', sprintf('✅ Product "%s" deleted successfully!', $productName));
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    private function canEditOrDelete(Product $product): bool
    {
        $currentUser = $this->getUser();

        if (in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            return true;
        }

        if (!$product->getCreatedBy()) {
            return true;
        }

        if (in_array('ROLE_STAFF', $currentUser->getRoles())) {
            return $product->getCreatedBy()->getId() === $currentUser->getId();
        }

        return false;
    }
}