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
#[IsGranted('ROLE_STAFF')] // Only Staff and Admin can access
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

            // Set who created this product
            $product->setCreatedBy($this->getUser());

            $entityManager->persist($product);
            $entityManager->flush();

            // Log the activity
            $this->activityLogger->log(
                $this->getUser(),
                'create',
                'Product',
                $product->getId(),
                sprintf('%s created product: %s (Price: ₱%.2f)', 
                    in_array('ROLE_ADMIN', $this->getUser()->getRoles()) ? 'Admin' : 'Staff',
                    $product->getName(),
                    $product->getPrice()
                )
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
        // Check if user can edit this product
        if (!$this->canEditOrDelete($product)) {
            $this->addFlash('error', 'You do not have permission to edit this product. You can only edit your own records.');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        $oldImage = $product->getImage();
        
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
                    
                    // Delete old image if exists and it's different from the new one
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

            // Log the activity
            $this->activityLogger->log(
                $this->getUser(),
                'update',
                'Product',
                $product->getId(),
                sprintf('%s updated product: %s (Price: ₱%.2f)', 
                    in_array('ROLE_ADMIN', $this->getUser()->getRoles()) ? 'Admin' : 'Staff',
                    $product->getName(),
                    $product->getPrice()
                )
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
        // Check if user can delete this product
        if (!$this->canEditOrDelete($product)) {
            $this->addFlash('error', 'You do not have permission to delete this product. You can only delete your own records.');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            
            // Check if product has orders
            if (method_exists($product, 'getOrders') && $product->getOrders()->count() > 0) {
                $this->addFlash('error', sprintf(
                    '❌ Cannot delete product "%s" because it has %d order(s) associated with it.',
                    $product->getName(),
                    $product->getOrders()->count()
                ));
                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
            }
            
            // Check if product has sales
            if (method_exists($product, 'getSales') && $product->getSales()->count() > 0) {
                $this->addFlash('error', sprintf(
                    '❌ Cannot delete product "%s" because it has %d sales record(s).',
                    $product->getName(),
                    $product->getSales()->count()
                ));
                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
            }

            // Check if product has stocks
            if (method_exists($product, 'getStocks') && $product->getStocks()->count() > 0) {
                $this->addFlash('error', sprintf(
                    '❌ Cannot delete product "%s" because it has %d stock record(s). Please delete the stock entries first.',
                    $product->getName(),
                    $product->getStocks()->count()
                ));
                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
            }

            $productName = $product->getName();
            $productId = $product->getId();
            $productPrice = $product->getPrice();

            // Log before deletion
            $this->activityLogger->log(
                $this->getUser(),
                'delete',
                'Product',
                $productId,
                sprintf('%s deleted product: %s (Price: ₱%.2f)', 
                    in_array('ROLE_ADMIN', $this->getUser()->getRoles()) ? 'Admin' : 'Staff',
                    $productName,
                    $productPrice
                )
            );

            // Delete image file if exists
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

    /**
     * Check if the current user can edit or delete the product
     * Admin: can edit/delete everything
     * Staff: can only edit/delete their own records
     */
    private function canEditOrDelete(Product $product): bool
    {
        $currentUser = $this->getUser();
        
        // Admin can access everything
        if (in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            return true;
        }

        // If no creator is set, allow staff access (for legacy records)
        if (!$product->getCreatedBy()) {
            return true;
        }

        // Staff can only access their own records
        if (in_array('ROLE_STAFF', $currentUser->getRoles())) {
            return $product->getCreatedBy()->getId() === $currentUser->getId();
        }

        return false;
    }
}