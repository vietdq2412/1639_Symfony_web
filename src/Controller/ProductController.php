<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product')]
    public function index(ProductRepository $productRepository){
        $product =$productRepository->viewProductList();
        return $this->render('product/index.html.twig', [
            'products' => $product,
        ]);
    }

    /** 
     * @IsGranted("ROLE_ADMIN")
     */ 
    #[Route('/delete/{id}', name: 'app_product_delete')]
    public function delete($id, ProductRepository $productRepository){
        $product =$productRepository->find($id);

        if ($product == null) {
            $this->addFlash("Error","Product not found !");
            return $this->redirectToRoute("app_product");
        }else{
            $productRepository->remove($product);
            $this->addFlash("Success","Delete ".$product->getName());
        }

        return $this->redirectToRoute('app_product');
    }

    #[Route('/detail/{id}', name: 'app_product_detail')]
    public function detail($id, ProductRepository $productRepository){
        $product =$productRepository->find($id);

        if ($product == null) {
            $this->addFlash("Error","Product not found !");
            return $this->redirectToRoute("app_product");
        }
        return $this->render("product/detail.html.twig",
            [
                'product' => $product
            ]);
    }

    /** 
     * @IsGranted("ROLE_ADMIN")
     */ 
    #[Route('/add', name: 'app_product_add')]
    public function add(Request $request){
        $product = new Product;

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($product);
            $manager->flush();

            return $this->redirectToRoute('app_product');
        }

        return $this->renderForm('product/add.html.twig', [
            'form' => $form,
        ]);
    }

    /** 
     * @IsGranted("ROLE_ADMIN")
     */ 
    #[Route('/edit/{id}', name: 'app_product_edit')]
    public function edit($id, Request $request){
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($product);
            $manager->flush();
            return $this->redirectToRoute('app_product_detail', ['id' => $id]);
        }
        return $this->renderForm('product/add.html.twig', [
            'form' => $form,
        ]);
    }
}
