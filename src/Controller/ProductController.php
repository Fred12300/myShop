<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product')]
    public function index(ProductRepository $prod): Response
    {
        $products = $prod->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/add', name: 'app_product_add')]
    public function add(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads')] string $uploadsDirectory): Response
    {

        $newProduct = new Product;
        $form = $this->createForm(ProductType::class, $newProduct);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            /** @var UploadedFile  $thumbnail */
            $thumbnail = $form->get('thumbnail')->getData();
            
            if($thumbnail){
                $originalFilename = pathinfo($thumbnail->getClientOriginalName(

                ), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$thumbnail->guessExtension();
                try {
                    $thumbnail->move($uploadsDirectory, $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $newProduct->setThumbnail($newFilename);
            }
            $newProduct = $form->getData();
            $entityManager->persist($newProduct);
            $entityManager->flush();
            return $this->redirectToRoute('app_product');
        }

        return $this->render('product/add.html.twig', [
            'formulaire' => $form
        ]);
    }
}
