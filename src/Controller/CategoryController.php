<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


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

    /** 
     * @IsGranted("ROLE_ADMIN")
     */ 
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

    /** 
     * @IsGranted("ROLE_ADMIN")
     */ 
    #[Route('/add', name: 'app_category_add')]
    public function add(Request $request){
        $category = new Category;

        $form = $this->createForm(Category::class, $category);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($category);
            $manager->flush();

            return $this->redirectToRoute('app_category');
        }

        return $this->renderForm('category/index.html.twig', [
            'add_cate_form' => $form,
        ]);
    }
}
