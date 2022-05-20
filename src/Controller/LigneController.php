<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Flux;
use App\Entity\Ligne;
use App\Form\LigneType;
use App\Repository\CommandeRepository;
use App\Repository\ConteneurRepository;
use App\Repository\FluxRepository;
use App\Repository\LigneRepository;
use App\Repository\ProduitRepository;
use App\Repository\StatutRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ligne')]
class LigneController extends AbstractController
{
    #[Route('/', name: 'app_ligne_index', methods: ['GET'])]
    public function index(LigneRepository $ligneRepository): Response
    {
        return $this->render('ligne/index.html.twig', [
            'lignes' => $ligneRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_ligne_new', methods: ['GET', 'POST'])]
    public function new($id, Request $request, LigneRepository $ligneRepository, StatutRepository $statutRepository, CommandeRepository $commandeRepository, ProduitRepository $produitRepository, ConteneurRepository $conteneurRepository): Response
    {
        // Appel l'utilisateur actuel
        $user = $this->getUser();

        // Vérifie qu'un utilisateur est bien connecté sinon renvoi vers la page d'identification
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        //créer la nouvelle ligne
        $ligne = new Ligne();

        //Définir le statut en attente de validation
        $ligne->setIdStatut($statutRepository->find(1));

        //Définir le produit
        $ligne->setIdProduit($produitRepository->find($id));

        //Défini la commande sur laquelle affecter la ligne
        $commande = $commandeRepository->findBy(['idUtilisateur' => $user->getId(), 'idStatut' => '1']);

        //S'il n'y a pas de commande en créer une nouvelle
        if (!$commande) {
            $commande = new Commande();
            $commande->setNumeroCommande(hexdec(uniqid()));
            $commande->setDateCommande(new DateTime());
            $commande->setIdStatut($statutRepository->find(1));
            $commandeRepository->add($commande);
            $commande->setIdUtilisateur($user);
            $ligne->setIdCommande($commande);
        } // Sinon affecter la commande trouvée dans le findBy plus haut
        else {
            $ligne->setIdCommande($commande[0]);
        }

        $form = $this->createForm(LigneType::class, $ligne);
        $form->handleRequest($request);

        //Recuperer la quantité total en stock
        $conteneurTotal = $conteneurRepository->findBy(['idProduit' => $id]);
        $quantiteEnStock = 0;
        foreach ($conteneurTotal as $conteneur) {
            $quantiteEnStock += $conteneur->getQuantite();
        }

        //Soustraire la quantité totale deja commandé
        $commandesTotal = $ligneRepository->findBy(['idProduit' => $id,'idStatut'=> [1,2,4]]);
        foreach ($commandesTotal as $commande) {
            $quantiteEnStock -= $commande->getQuantite();
        }


        //Si la quantité commandée est supérieur à celle stocké renvoyer un addFlash
        if ($form['quantite']->getData() > $quantiteEnStock) {

            // Add Flash d'alerte
            $this->addFlash('surStock', 'Désolé vous ne pouvez pas commander plus de ' . $quantiteEnStock . ' unités du produit ' . $produitRepository->find($id));

            return $this->redirectToRoute('app_ligne_new', ['id' => $id], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $ligne->setQuantite($form['quantite']->getData());
            $ligneRepository->add($ligne, true);

            return $this->redirectToRoute('app_main', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ligne/new.html.twig', [
            'ligne' => $ligne,
            'form' => $form,
            'produit' => $produitRepository->find($id),
        ]);
    }

    #[Route('/{id}', name: 'app_ligne_show', methods: ['GET'])]
    public function show(Ligne $ligne): Response
    {
        return $this->render('ligne/show.html.twig', [
            'ligne' => $ligne,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ligne_edit', methods: ['GET', 'POST'])]
    public function edit($id, Request $request, Ligne $ligne, LigneRepository $ligneRepository, ConteneurRepository $conteneurRepository): Response
    {
        $form = $this->createForm(LigneType::class, $ligne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //Récupérer la quantité total en stock
            $conteneurTotal = $conteneurRepository->findBy(['idProduit' => $ligne->getIdProduit()->getId()]);
            $quantiteEnStock = 0;
            foreach ($conteneurTotal as $conteneur) {
                $quantiteEnStock += $conteneur->getQuantite();
            }

            //Soustraire la quantité totale deja commandé et dont les commande n'ont pas été préparé
            $commandesTotal = $ligneRepository->findBy(['idProduit' => $ligne->getIdProduit()->getId(),'idStatut'=> [1,2,4] ]);

            foreach ($commandesTotal as $commande) {
                $quantiteEnStock -= $commande->getQuantite();
            }


            //Si la quantité commandée est supérieur à celle stocké renvoyer un addFlash
            if (($form['quantite']->getData()) > $quantiteEnStock) {

                $quantiteEnStock = $quantiteEnStock+$ligne->getQuantite();
                // Add Flash d'alerte
                $this->addFlash('surStock', 'Désolé vous ne pouvez pas commander plus de ' . $quantiteEnStock . ' unités de ce produit.');

                return $this->redirectToRoute('app_ligne_edit', ['id' => $id], Response::HTTP_SEE_OTHER);
            }

            $ligneRepository->add($ligne, true);

            return $this->redirectToRoute('app_commande_show', ['id'=>$ligne->getIdCommande()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ligne/edit.html.twig', [
            'ligne' => $ligne,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ligne_delete', methods: ['POST'])]
    public function delete(Request $request, Ligne $ligne, LigneRepository $ligneRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $ligne->getId(), $request->request->get('_token'))) {
            $ligneRepository->remove($ligne, true);
        }

        return $this->redirectToRoute('app_ligne_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('validationPreparation/{id}/{idConteneur}', name: 'app_ligne_statut', methods: ['GET'])]
    public function statut($idConteneur, Ligne $ligne, LigneRepository $ligneRepository, StatutRepository $statutRepository, ConteneurRepository $conteneurRepository, FluxRepository $fluxRepository): Response
    {

        //Ajuster les quantités sur le conteneur prélevé
        $quantite = $ligne->getQuantite();
        $conteneur = $conteneurRepository->find($idConteneur);
        $conteneur->setQuantite($conteneur->getQuantite() - $quantite);

        //Définir le statut de la ligne comme étant préparé
        $ligne->setIdStatut($statutRepository->find(4));

        //Enregistrer la sortie de stock dans la table flux
        $flux = new Flux();
        $flux->setQuantite($quantite * -1);
        $flux->setDateFlux(new DateTime());
        $flux->setOrigine($ligne->getIdCommande()->getNumeroCommande());
        $flux->setType('PRÉPARATION - COMMANDE');
        $flux->setPartNumber($conteneur->getIdProduit());
        $flux->setCodeConteneur($conteneur->getCodeConteneur());
        $flux->setAdresseStock($conteneur->getIdStock());

        $conteneurRepository->add($conteneur);

        // Si le conteneur est vide le supprimer
        if ($conteneur->getQuantite() === 0) {
            $conteneurRepository->remove($conteneur);
        }

        $ligneRepository->add($ligne, true);
        $fluxRepository->add($flux, true);
        return $this->redirectToRoute('app_commande_preparation', ['id' => $ligne->getIdCommande()->getId()], Response::HTTP_SEE_OTHER);
    }


}
