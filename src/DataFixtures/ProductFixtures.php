<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        for($i = 0; $i < 1; $i++){
            $category = new Category;
            $category->setName('category'.$i);
            $manager->persist($category);
        }

        // for($i = 0; $i < 10; $i++){
        //     $product = new Product;
        //     $product->setName('product'.$i);
        //     $product->setPrice(200+$i);
        //     $product->setImage('https://m.media-amazon.com/images/I/61+ilDgVVwS._UL1500_.jpg');
        //     $manager->persist($product);
        // }

        $manager->flush();
    }
}
