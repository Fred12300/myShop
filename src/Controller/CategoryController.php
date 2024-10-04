<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(): Response
    {
        
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }

    #[Route('/category/add', name: 'app_category_add')]
    public function add(
        Request $request,
        EntityManagerInterface $entityManager,
        CategoryRepository $categories): Response
    {
        //Création d'un objet vie de Type Category
        $newCategory = new Category;
        //initialise un formulaire à partir de la classe de formualiare correspondant à cette enetité, puis on la relie à l'objerte vide
        $form = $this->createForm(CategoryType::class, $newCategory);
        // on demande au formulaire de traiter les requêtes, pour cela on lui fourni un objet request injecté dans la fonction add()
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //on va remplir l'objet avec les données du formulaire
            $newCategory = $form->getData();
            //on utlise le manager global pour "sauvegarder" l'entité
            $entityManager->persist($newCategory);
            //on envoie en BDD
            $entityManager->flush();
        }
        $AllCategories = $categories->findAll();
        return $this->render('category/add.html.twig', [
            //on renvoie à la vue
            'formulaire' => $form,
            'categories' => $AllCategories
        ]);
        return $this->redirectToRoute('app_category');
    }

    #[Route('/category/{id}', name: 'app_category')]
    public function show(CategoryRepository $cr, $id):Response
    {
        $category = $cr->find($id);
        return $this->render('category/show.html.twig', [
            'category' => $category
        ]);
    }
}
