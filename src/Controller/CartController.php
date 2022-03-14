<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CartRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;

#[Route('/cart')]
class CartController extends AbstractController
{
    #[Route('/incart/{user}', name: 'app_cart_item')]
    public function list_item_incart($user, CartRepository $cartRepository)
    {
        $cur_user = $this->getDoctrine()->getRepository(User::class)->find($user);
        #$items = $cartRepository->list_item();

        $items = $cartRepository->findBy(array('status' => ['incart'], 'user' => [$cur_user]));

        return $this->render('cart/index.html.twig', [
            'items' => $items,
        ]);
    }


    /** 
     * @IsGranted("ROLE_ADMIN")
     */ 
    #[Route('/view_order', name: 'cart_view_order')]
    public function view_list_ordering(CartRepository $cartRepository)
    {
        $items = $cartRepository->findBy(array('status' => ['ordering', 'deliver']));

        return $this->render('cart/admin/view_order.html.twig', [
            'items' => $items,
        ]);
    }

     /** 
     * @IsGranted("ROLE_ADMIN")
     */ 
    #[Route('/accept/{id}', name: 'cart_accept_order')]
    public function accept_order($id, CartRepository $cartRepository)
    {
        $item = $cartRepository->find($id);
        $item->setStatus('deliver');

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($item);
        $manager->flush();

        return $this->redirectToRoute("cart_view_order");
    }

     /** 
     * @IsGranted("ROLE_ADMIN")
     */ 
    #[Route('/deny/{$id}', name: 'cart_deny_order')]
    public function deny_order($id ,CartRepository $cartRepository)
    {
        $item = $cartRepository->find($id);

        if($item->getStatus() === 'ordering'){
            $item->setStatus('canceled');
        }else{
            $this->addFlash("Error","product send already!");
            return $this->redirectToRoute("cart_view_order");
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($item);
        $manager->flush();

        return $this->redirectToRoute("cart_view_order");

    }

    #[Route('/history/{user}', name: 'cart_history')]
    public function view_history($user, CartRepository $cartRepository)
    {
        $cur_user = $this->getDoctrine()->getRepository(User::class)->find($user);
        #$items = $cartRepository->list_item();

        $items = $cartRepository->findBy(array('user' => [$cur_user]));


        return $this->render('cart/order_history.html.twig', [
            'items' => $items,
        ]);
    }

    #[Route('/remove_item_/{id}/{user}', name: 'cart_remove_item')]
    public function remove_item($id, $user, CartRepository $cartRepository){
        $cur_user = $this->getDoctrine()->getRepository(User::class)->find($user);
        #$items = $cartRepository->list_item();

        $item = $cartRepository->find($id);

        $manager = $this->getDoctrine()->getManager();
        if($item->getStatus() === 'incart'){
            $manager->remove($item);
        }else{
            $this->addFlash("Error","product send already!");
            return $this->redirectToRoute('app_cart_item', [
                'user' => $user
            ]);    
        }
        $manager->flush();


        return $this->redirectToRoute('app_cart_item', [
            'user' => $user
        ]);
    }

    #[Route('/sent_order/{user}', name: 'cart_sent_order')]
    public function sent_order($user, CartRepository $cartRepository)
    {
        #$items = $cartRepository->list_item();

        $cur_user = $this->getDoctrine()->getRepository(User::class)->find($user);

        $items = $cartRepository->findBy(array('status' => ['incart'], 'user' => [$cur_user]));


        $manager = $this->getDoctrine()->getManager();
    
        foreach($items as $item){
            $pro_quatity = $item->getProducts()->getQuantity();
            if($pro_quatity < $item->getQuantity()){
                $this->addFlash("Error","over quatity!");
                return $this->redirectToRoute('cart_history', ['user' => $user]);
            }
            $item->setStatus('ordering');
            $item->getProducts()->setQuantity($pro_quatity - $item->getQuantity());
            $manager->persist($item);
        }
        $manager->flush();
        
        return $this->redirectToRoute('cart_history', ['user' => $user]);
    }

    #[Route('/receive/{id}/{user}', name: 'cart_received')]
    public function receive($id, $user, CartRepository $cartRepository)
    {

        $cur_user = $this->getDoctrine()->getRepository(User::class)->find($user);

        $item = $cartRepository->find($id);

        $item->setStatus('done');

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($item);
        $manager->flush();
        
        return $this->redirectToRoute('cart_history', ['user' => $user]);
    }

    #[Route('/incart/{id}/{user}', name: 'app_cart_add')]
    public function add_to_cart( $id,$user, CartRepository $cartRepository, Request $request)
    {
        $cur_user = $this->getDoctrine()->getRepository(User::class)->find($user);
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        $quantity = $request->get('quantity');

        $cart = new Cart;
        $cart->setQuantity($quantity);
        $cart->setDate(new DateTime());
        $cart->setStatus('incart');
        $cart->setProducts($product);
        $cart->setUser($cur_user);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($cart);
        $manager->flush();

        return $this->redirectToRoute('app_cart_item', ['user' => $user]);
    }


    #[Route('/edit_cart/{id}/{user}', name: 'cart_edit')]
    public function edit_cart( $id,$user, CartRepository $cartRepository, Request $request)
    {
        $item = $cartRepository->find($id);

        $quantity = $request->get('quantity');

    
        $item->setQuantity($quantity);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($item);
        $manager->flush();

        return $this->redirectToRoute('app_cart_item', ['user' => $user]);
    }

}
