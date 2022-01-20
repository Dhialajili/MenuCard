<?php

namespace App\Controller;

use App\Entity\Dish;
use App\Form\Dish1Type;
use App\Repository\DishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @Route("/dish")
 */
class DishController extends AbstractController
{
    /**
     * @Route("/", name="dish_index", methods={"GET"})
     */
    public function index(DishRepository $dishRepository): Response
    {
        return $this->render('dish/index.html.twig', [
            'dishes' => $dishRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="dish_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $dish = new Dish();
        $form = $this->createForm(Dish1Type::class, $dish);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('images_folder'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $dish->setImage($newFilename);
            }

            $entityManager->persist($dish);
            $entityManager->flush();

            return $this->redirectToRoute('dish_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('dish/new.html.twig', [
            'dish' => $dish,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="dish_show", methods={"GET"})
     */
    public function show(Dish $dish): Response
    {
        return $this->render('dish/show.html.twig', [
            'dish' => $dish,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="dish_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Dish $dish, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Dish1Type::class, $dish);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('dish_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('dish/edit.html.twig', [
            'dish' => $dish,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="dish_delete", methods={"POST"})
     */
    public function delete(Request $request, Dish $dish, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dish->getId(), $request->request->get('_token'))) {
            $entityManager->remove($dish);
            $entityManager->flush();
        }

        return $this->redirectToRoute('dish_index', [], Response::HTTP_SEE_OTHER);
    }

     
}
