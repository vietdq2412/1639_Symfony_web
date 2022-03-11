<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/category')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'app_category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categorys = $categoryRepository->findAll();
        return $this->render('category/index.html.twig', [
            "categories" => $categorys
        ]);
    }

    #[Route('/delete/{id}', name: 'app_category_delete')]
    public function delete($id, CategoryRepository $categoryRepository){
        $category = $categoryRepository->find($id);

        if ($category == null) {
            $this->addFlash("Error","Category not found !");
            return $this->redirectToRoute("app_category");
        }else{
            $categoryRepository->remove($category);
            $this->addFlash("Success","Delete ".$category->getName());
        }

        return $this->redirectToRoute('app_category');
    }
}
