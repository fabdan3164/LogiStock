<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Ligne;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\ConteneurRepository;
use App\Repository\LigneRepository;
use App\Repository\StatutRepository;
use App\Repository\StockRepository;
use DateTime;
use Dompdf\Dompdf;
use Dompdf\Options;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    #[isGranted("ROLE_USER")]
    #[Route('/', name: 'app_commande_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findAll(),
        ]);
    }

    #[isGranted("ROLE_LOG")]
    #[Route('/select', name: 'app_commande_select', methods: ['GET'])]
    public function selectCommande(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/select.html.twig', [
            'commandes' => $commandeRepository->findby(['idStatut' => [1, 2]]),
        ]);
    }

    #[isGranted("ROLE_LOG")]
    #[Route('/select/{id}', name: 'app_commande_preparation', methods: ['GET'])]
    public function preparationCommande($id, CommandeRepository $commandeRepository, ConteneurRepository $conteneurRepository, LigneRepository $ligneRepository, StatutRepository $statutRepository, StockRepository $stockRepository): Response

    {
        // Obtenir un tableau des lignes de commande à préparer
        $lignes = $ligneRepository->findBy(['idCommande' => $id, 'idStatut' => 1]);

        //Définir les lignes en statut en cours
        foreach ($lignes as $ligne) {
            $ligne->setIdStatut($statutRepository->find(2));
            $ligneRepository->add($ligne, true);
        }

        // Obtenir un tableau des lignes de commande en cours
        $lignes = $ligneRepository->findBy(['idCommande' => $id, 'idStatut' => 2]);


        // l'implémenter avec les conteneurs dans lesquelles on va prélever
        foreach ($lignes as $ligne) {

            // Si le conteneur est vide en affecter un autre et supprimer celui qui est vide
            if ($ligne->getConteneurLigne() && $ligne->getConteneurLigne()->getquantite() === 0) {
                $objCont = $conteneurRepository->findBy(['idProduit' => $ligne->getIdProduit()], ['idStock' => 'ASC'], 2);
                $objCont = $conteneurRepository->find($objCont[0]->getId());
                $ligne->setConteneurLigne($objCont);

                $conteneurRepository->remove($conteneurRepository->find($objCont[0]->getId(), true));
                $ligneRepository->add($ligne, true);
            }

            if (!$ligne->getConteneurLigne()) {
                //Ajouter le premier conteneur trouvé dans l'ordre par idStock
                $objCont = $conteneurRepository->findBy(['idProduit' => $ligne->getIdProduit()], ['idStock' => 'ASC'], 1);
                $objCont = $conteneurRepository->find($objCont[0]->getId());
                $ligne->setConteneurLigne($objCont);
                $ligneRepository->add($ligne, true);
            }

            // Re-Obtenir la lignes de commande en cours
            $ligne = $ligneRepository->find($ligne->getId());
        }

        foreach ($lignes as $ligne) {
            $ligne1 = $ligneRepository->find($ligne->getId());

            // Si la quantité commandée est supérieur à la quantité dans le conteneur, modifier la ligne et en créer  d'autre
            if ($ligne1->getQuantite() > $ligne->getConteneurLigne()->getquantite()) {

                // Appeler tout les conteneurs sur lesquels trouver le produit
                $conteneurProduits = $conteneurRepository->findBy(['idProduit' => $ligne->getIdProduit()], ['idStock' => 'ASC']);

                //excepter le premier qui est affecté à la ligne d'origine
                array_shift($conteneurProduits);

                // Calculer la quantité restante à prélever ;
                $quantiteRestante = ($ligne1->getQuantite() - $ligne->getConteneurLigne()->getquantite());

                // Verifier dans combien de conteneur repartir la liste
                foreach ($conteneurProduits as $conteneurProduit) {

                // MAJ quantité restante à chaque tour de boucle
                    $quantiteRestante = $quantiteRestante - $conteneurProduit->getQuantite();

                    // MAJ ligneBisc auantite à chaque tour de boucle, jusqu'a ce que la quantité restante soit égale à 0
                    $ligneBisQuantite = $conteneurProduit->getQuantite();
                    if ($quantiteRestante <= 0) {
                        $ligneBisQuantite = $quantiteRestante + $conteneurProduit->getQuantite();
                    }

                    //Créer une nouvelle ligne et lui passer tous les paramètres nécessaires
                    $ligneBis = new Ligne();
                    $ligneBis->setQuantite($ligneBisQuantite);
                    $ligneBis->setIdStatut($ligne1->getIdStatut());
                    $ligneBis->setIdProduit($ligne1->getIdProduit());
                    $ligneBis->setIdCommande($ligne1->getIdCommande());
                    $ligneBis->setConteneurLigne($conteneurProduit);

                    //MAJ des lignes
                    $ligneRepository->add($ligneBis, true);

                    // Supprimer le conteneur utiliser du tableau
                    array_shift($conteneurProduits);

                    // Sortir de la boucle une fois toute la quantité commandée répartie
                    if ($quantiteRestante <= 0) {
                        break;
                    }
                }

                //MAJ de la ligne!
                $ligne1->setQuantite($ligne->getConteneurLigne()->getquantite());
                $ligneRepository->add($ligne1, true);
            }
        }


        // Passer le statut de la commande à en cours
        $commande = $commandeRepository->find($id);
        if ($commande->getIdStatut() !== $statutRepository->find(2)) {
            $commande->setIdStatut($statutRepository->find(2));
            $commandeRepository->add($commande, true);
        }


        //Rappeler le tableau lignes avec les conteneurs ajouté
        $lignes = $ligneRepository->findBy(['idCommande' => $id, 'idStatut' => 2]);

        // Trier le tableau de lignes par adresse de stockage pour suivre la route numérique de préparation
        usort($lignes, function ($a, $b) {
            if ($a->getConteneurLigne()->getidStock()->getId() == $b->getConteneurLigne()->getidStock()->getId()) {
                return 0;
            }
            return ($a->getConteneurLigne()->getidStock()->getId() < $b->getConteneurLigne()->getidStock()->getId()) ? -1 : 1;
        });

        // Ajout de l'adresse de stockage ou aller prélever (en cas de déplacement au cours de la préparation)
        if ($lignes) {
            $conteneurPick = $conteneurRepository->find($lignes[0]->getConteneurLigne()->getId());
            $ligneRepository->add( $lignes[0]->setConteneurLigne($conteneurPick), true);
        }

        // Retourner la vue pour continuer la préparation
        return $this->render('commande/preparation.html.twig', [
            'commande' => $commandeRepository->find($id),
            'lignes' => $lignes,
        ]);
    }

        //TO DO etudier suppresion
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
    public function show(Commande $commande, LigneRepository $ligneRepository, StockRepository $stockRepository): Response
    {
        $lignes = $ligneRepository->findBy(['idCommande' => $commande->getId()]);
        $stocks = $stockRepository->findAll();
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
            'lignes' => $lignes,
            'stocks' => $stocks
        ]);
    }

    #[isGranted("ROLE_USER")]
    #[Route('/{id}/valide', name: 'app_commande_valide', methods: ['GET'])]
    public function valide(Commande $commande, LigneRepository $ligneRepository, StatutRepository $statutRepository, CommandeRepository $commandeRepository): Response
    {
        $commande->setIdStatut($statutRepository->find(1));
        $commandeRepository->add($commande, true);

        $lignes = $ligneRepository->findBy(['idCommande' => $commande->getId()]);

        foreach ($lignes as $ligne) {
            $ligne->setIdStatut($statutRepository->find(1));
            $ligneRepository->add($ligne, true);
        }

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }

    #[isGranted("ROLE_LOG")]
    #[Route('/{id}/prepare', name: 'app_commande_prepare', methods: ['GET'])]
    public function prepare(CommandeRepository $commandeRepository, StatutRepository $statutRepository, $id): Response
    {

        $commande = $commandeRepository->find($id);
        $commande->setIdStatut($statutRepository->find(3));
        $commandeRepository->add($commande, true);

        return $this->redirectToRoute('app_commande_select', [], Response::HTTP_SEE_OTHER);

    }



    #[Route('/{id}', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, CommandeRepository $commandeRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $commande->getId(), $request->request->get('_token'))) {
            $commandeRepository->remove($commande, true);
        }

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }

    #[isGranted("ROLE_LOG")]
    #[Route('/{id}/pdf', name: 'app_commande_print', methods: ['GET'])]
    public function printPdf($id, CommandeRepository $commandeRepository, LigneRepository $ligneRepository): Response
    {
        $commande = $commandeRepository->find($id);
        $lignes = $ligneRepository->findBy(['idCommande' => $commande]);


        $blackColor = [0, 0, 0];
        $dataBarcode = $commande->getNumeroCommande();
        $generator = new BarcodeGeneratorPNG();

        file_put_contents('barcode' . $dataBarcode . '.png', $generator->getBarcode($dataBarcode, 'C128', 3, 50, $blackColor),);


        return new Response($this->commandePdf($commande, $lignes), 200, ['Content-Type' => 'application/pdf',]);
    }

    private function commandePdf($commande, $lignes)
    {

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->setIsRemoteEnabled(true);

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('default/commandePrint.html.twig', [
            'commande' => $commande,
            'lignes' => $lignes
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (inline view)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => false
        ]);

        unlink('barcode' . $commande . '.png');
    }

}


