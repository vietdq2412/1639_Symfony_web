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
        $item->setStatus('canceled');

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

    #[Route('/sent_order/{user}', name: 'cart_sent_order')]
    public function sent_order($user, CartRepository $cartRepository)
    {
        #$items = $cartRepository->list_item();

        $cur_user = $this->getDoctrine()->getRepository(User::class)->find($user);

        $items = $cartRepository->findBy(array('status' => ['incart'], 'user' => [$cur_user]));


        $manager = $this->getDoctrine()->getManager();
    
        foreach($items as $item){
            $item->setStatus('ordering');

            $manager->persist($item);
        }
        $manager->flush();
        
        return $this->redirectToRoute('cart_history', ['user' => $user]);
    }

     

    #[Route('/incart/{id}/{user}', name: 'app_cart_add')]
    public function add_to_cart( $id,$user, CartRepository $cartRepository)
    {
        $cur_user = $this->getDoctrine()->getRepository(User::class)->find($user);
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);


        $cart = new Cart;
        $cart->setDate(new DateTime());
        $cart->setStatus('incart');
        $cart->setProducts($product);
        $cart->setUser($cur_user);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($cart);
        $manager->flush();

        return $this->redirectToRoute('app_cart_item', ['user' => $user]);
    }

}
