<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/produit')]
class ProduitController extends AbstractController
{
    #[isGranted("ROLE_LOG")]
    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index( ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[isGranted("ROLE_ADMIN")]
    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProduitRepository $produitRepository): Response
    {
        $produit = new Produit();
        $produit->setPartNumber('PN'.hexdec(uniqid()));
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //Récupérer les données du formulaire
            $file = $form['image']->getData();

            //Définir le chemin vers le dossier de stockage situé dans public
            $directory = './imgProduits/';

            // Vérifier si le fichier existe
            if ($file) {
                // Récupérer l'extension
                $extension = $file->guessExtension();

                // Déplacer le fichier dans le dossier de stockage
                $file->move($directory, 'Produit-' . $produit->getPartNumber() . '.' . $extension);

                // Définir le chemin vers l'image associée à la ville qui sera enregistré dans la base de donnée
                $produit->setImage($directory . 'Produit-' . $produit->getPartNumber() . '.' . $extension);
            }

            $produitRepository->add($produit, true);

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[isGranted("ROLE_LOG")]
    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[isGranted("ROLE_ADMIN")]
    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //Récupérer les données du formulaire
            $file = $form['image']->getData();

            //Définir le chemin vers le dossier de stockage situé dans public
            $directory = './imgProduits/';

            // Vérifier si le fichier existe
            if ($file) {
                // Récupérer l'extension
                $extension = $file->guessExtension();

                // Déplacer le fichier dans le dossier de stockage
                $file->move($directory, 'Produit-' . $produit->getPartNumber() . '.' . $extension);

                // Définir le chemin vers l'image associée à la ville qui sera enregistré dans la base de donnée
                $produit->setImage($directory . 'Produit-' . $produit->getPartNumber() . '.' . $extension);
            }


            $produitRepository->add($produit, true);

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[isGranted("ROLE_ADMIN")]
    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            unlink($produit->getImage());
            $produitRepository->remove($produit, true);
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }

}
