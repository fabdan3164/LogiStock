<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Ligne;
use App\Entity\Statut;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\ConteneurRepository;
use App\Repository\LigneRepository;
use App\Repository\ProduitRepository;
use App\Repository\StatutRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    #[Route('/', name: 'app_commande_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findAll(),
        ]);
    }

    #[Route('/select', name: 'app_commande_select', methods: ['GET'])]
    public function selectCommande(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/select.html.twig', [
            'commandes' => $commandeRepository->findby(['idStatut' => 1]),
        ]);
    }

    #[Route('/select/{id}', name: 'app_commande_preparation', methods: ['GET'])]
    public function preparationCommande($id, CommandeRepository $commandeRepository, ConteneurRepository $conteneurRepository, LigneRepository $ligneRepository, StatutRepository $statutRepository): Response

    {
        // Obtenir un tableau des lignes de commande à préparer
        $lignes = $ligneRepository->findBy(['idCommande' => $id, 'idStatut' => '1']);

        // l'implementer avec les conteneurs dans lesquelles on va prélever
        foreach ($lignes as $ligne) {
            $ligne->conteneur = $conteneurRepository->findBy(['idProduit' => $ligne->getIdProduit()], ['idStock' => 'ASC'], 1);


            //Si la quantité est supérieur à la quantité dans le conteneur, supprimer la ligne et en créer une autre
            if ($ligne->getQuantite() > $ligne->conteneur[0]->getQuantite()) {

                $ligne2 = new Ligne();
                $ligne2->setQuantite($ligne->getQuantite() - $ligne->conteneur[0]->getQuantite());
                $ligne2->setIdStatut($ligne->getIdStatut());
                $ligne2->setIdProduit($ligne->getIdProduit());
                $ligne2->setIdCommande($ligne->getIdCommande());
                $ligneRepository->add($ligne2, true);

                $ligne->setQuantite($ligne->conteneur[0]->getQuantite());
                $ligneRepository->add($ligne , true);
            }
        }


        // Trier le tableau par adresse de stockage pour suivre la route numérique de préparation
        usort($lignes, function ($a, $b) {
            return strcmp($a->conteneur[0]->getidStock(), $b->conteneur[0]->getidStock());
        });


        // S'il n'y a plus de ligne à préparer définir le statut de commande à préparer
        if (!$lignes) {
            $commande = $commandeRepository->find($id);
            $commande->setIdStatut($statutRepository->find(3));
            $commandeRepository->add($commande , true);
        }

        // Retourner la vue pour continuer la préparation
        return $this->render('commande/preparation.html.twig', [
            'commande' => $commandeRepository->find($id),
            'lignes' => $lignes,
        ]);
    }


    #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CommandeRepository $commandeRepository, StatutRepository $statutRepository): Response
    {
        $commande = new Commande();

        //Associer la commande au user identifié
        $user = $this->getUser();
        $commande->setIdUtilisateur($user);

        //Définir la date actuel
        $commande->setDateCommande(new DateTime());

        //Définir le statut En cours de commande
        $commande->setIdStatut($statutRepository->find(1));


        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commandeRepository->add($commande, true);

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commande/new.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, CommandeRepository $commandeRepository): Response
    {
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commandeRepository->add($commande, true);

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, CommandeRepository $commandeRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $commande->getId(), $request->request->get('_token'))) {
            $commandeRepository->remove($commande, true);
        }

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }

//    #[Route('/json/{numeroCommande}', name: 'app_commande_json', methods: [ 'GET', 'POST'])]
//    public function jsonCommande(Request $request, CommandeRepository $commandeRepository): Response
//    {
//        $commande = $commandeRepository->findby(['numeroCommande' => $request->get('numeroCommande')]);
//        json_encode($commande);
//        if ($commande) {
//
//            return $this->json($commande, 200);
//
//        } else return $this->json(false, 200);
//    }
}


