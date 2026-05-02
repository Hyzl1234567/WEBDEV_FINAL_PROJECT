<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/category')]
#[IsGranted('ROLE_USER')]
final class CategoryController extends AbstractController
{
    private ActivityLogger $activityLogger;

    public function __construct(ActivityLogger $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    #[Route(name: 'app_category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setCreatedBy($this->getUser());

            $entityManager->persist($category);
            $entityManager->flush();

            $snapshot = [
                'name'       => $category->getName(),
                'created_by' => $category->getCreatedBy()?->getUsername(),
            ];

            $this->activityLogger->logCreate(
                $this->getUser(),
                'Category',
                $category->getId(),
                sprintf('Category: %s (ID: %d)', $category->getName(), $category->getId()),
                $snapshot
            );

            $this->addFlash('success', 'Category created successfully!');
            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if (!$this->canEditOrDelete($category)) {
            $this->addFlash('error', 'You do not have permission to edit this category. You need staff or admin privileges.');
            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Snapshot BEFORE flush to capture old name
            $snapshot = [
                'name' => $category->getName(),
            ];

            $entityManager->flush();

            $this->activityLogger->logUpdate(
                $this->getUser(),
                'Category',
                $category->getId(),
                sprintf('Category: %s (ID: %d)', $category->getName(), $category->getId()),
                $snapshot
            );

            $this->addFlash('success', 'Category updated successfully!');
            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if (!$this->canEditOrDelete($category)) {
            $this->addFlash('error', 'You do not have permission to delete this category. You need staff or admin privileges.');
            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'))) {
            $categoryName = $category->getName();
            $categoryId   = $category->getId();

            $snapshot = [
                'name'       => $category->getName(),
                'created_by' => $category->getCreatedBy()?->getUsername(),
                'deleted_at' => (new \DateTimeImmutable())->format('c'),
            ];

            $this->activityLogger->logDelete(
                $this->getUser(),
                'Category',
                $categoryId,
                sprintf('Category: %s (ID: %d)', $categoryName, $categoryId),
                $snapshot
            );

            $entityManager->remove($category);
            $entityManager->flush();

            $this->addFlash('success', 'Category deleted successfully!');
        }

        return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
    }

    private function canEditOrDelete(Category $category): bool
    {
        $currentUser = $this->getUser();

        if (in_array('ROLE_ADMIN', $currentUser->getRoles()) ||
            in_array('ROLE_STAFF', $currentUser->getRoles())) {
            return true;
        }

        return false;
    }
}