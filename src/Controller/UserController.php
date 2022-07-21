<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\Date;

#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(RecipeRepository $recipeRepository): Response
    {
        $recipes = $recipeRepository->findAll();

        $recipesCollection = new ArrayCollection($recipes);

        $userRecipes = $recipesCollection->filter(function($recipe) {
                return $recipe->getUser()->getEmail() === $this->getUser()->getUserIdentifier(); 
                                });


        return $this->render('user/index.html.twig', [
            'recipes' => $userRecipes,
        ]);
    }
    
    #[Route('/user/create_recipe', name: 'app_user_create_recipe')]
    public function create_recipe(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {
        $recipe = new Recipe();
        $recipe->setIngredients(['sel', 'poivre']);
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            // manage card picture upload
            $card_picture = $form['card_picture']->getData();
            
            if ($card_picture) {
                $originalFilename = pathinfo($card_picture->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalFilename);
                $newFileName = $safeFileName.'-'.uniqid().'.'.$card_picture->guessExtension();
                try {
                    $card_picture->move(
                        $this->getParameter('card_directory'),
                        $newFileName
                    );
                } catch (FileException $e) {
                    //throw $th;
                }
            }            



            $entityManager = $doctrine->getManager();
            $user = $this->getUser();

            $recipe->setCardPicture($newFileName);            
            $recipe->setDate(new DateTime('now')) ;
            $recipe->setUser($user);
            $entityManager->persist($recipe);
            $entityManager->flush();
            
            return $this->redirectToRoute('app_user');
        }

        return $this->renderForm('user/create_recipe.html.twig', [
            'form' => $form,
        ]);
    }
}
