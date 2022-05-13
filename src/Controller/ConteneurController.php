<?php

namespace App\Controller;

use App\Entity\Conteneur;
use App\Entity\Flux;
use App\Form\ConteneurType;
use App\Form\ConteneurTypeMvt;
use App\Repository\ConteneurRepository;
use App\Repository\FluxRepository;
use App\Repository\StockRepository;
use DateTime;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\ByteString;

#[Route('/conteneur')]
class ConteneurController extends AbstractController
{
    #[Route('/', name: 'app_conteneur_index', methods: ['GET'])]
    public function index(ConteneurRepository $conteneurRepository): Response
    {
        return $this->render('conteneur/index.html.twig', [
            'conteneurs' => $conteneurRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_conteneur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ConteneurRepository $conteneurRepository, StockRepository $stockRepository, FluxRepository $fluxRepository): Response
    {
        $conteneur = new Conteneur();
        $conteneur->setDateReception(new DateTime());
        $conteneur->setIdStock($stockRepository->find(1));
        $codeConteneur = 'CONT-' . ByteString::fromRandom(15, '0123456789');

        if ($conteneurRepository->findby(['codeConteneur' => $codeConteneur])) {
            $conteneur->setCodeConteneur('CONT-' . ByteString::fromRandom(15, '0123456789'));
        } else {
            $conteneur->setCodeConteneur($codeConteneur);
        }


        $form = $this->createForm(ConteneurType::class, $conteneur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $flux = new Flux();
            $flux->setDateFlux(new DateTime());
            $flux->setQuantite($form['quantite']->getData());
            $flux->setType("RECEPTION");
            $flux->setAdresseStock($conteneur->getIdStock()->getAdresseStock());
            $flux->setCodeConteneur($conteneur->getCodeConteneur());

            $conteneurRepository->add($conteneur, true);
            $fluxRepository->add($flux, true);
            return $this->redirectToRoute('app_conteneur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('conteneur/new.html.twig', [
            'conteneur' => $conteneur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_conteneur_show', methods: ['GET'])]
    public function show(Conteneur $conteneur): Response
    {
        return $this->render('conteneur/show.html.twig', [
            'conteneur' => $conteneur,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_conteneur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Conteneur $conteneur, ConteneurRepository $conteneurRepository): Response
    {
        $form = $this->createForm(ConteneurType::class, $conteneur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $conteneurRepository->add($conteneur, true);

            return $this->redirectToRoute('app_conteneur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('conteneur/edit.html.twig', [
            'conteneur' => $conteneur,
            'form' => $form,
        ]);
    }


    #[Route('/{id}/mouvement', name: 'app_conteneur_mouvement', methods: ['GET', 'POST'])]
    public function mouvement( $id,Request $request, Conteneur $conteneur, ConteneurRepository $conteneurRepository, FluxRepository $fluxRepository): Response
    {
        $adresse = $conteneur->getIdStock();
        $form = $this->createForm(ConteneurTypeMvt::class, $conteneur);
        $form->handleRequest($request);

        // Si l'emplacement de destination est deja occupé bloquer et ajouter un add flash
        if ($conteneurRepository->findOneby(['idStock' => $form['idStock']->getData()]) && !$form['idStock']->getData()->isMultiStockage() ) {

            // Add Flash de validation
            $this->addFlash('MouvementImpossible', 'L\'adresse de destination '. $form['idStock']->getData().' est deja occupé');

            //Reinitialise l'adresse de stockage pour affichage
            $conteneur->setIdStock($adresse);

            // Renvoie sur la vue pour une nouvelle saisie
            return $this->renderForm('conteneur/mouvement.html.twig', [
                'conteneur' => $conteneur,
                'form' => $form,

            ]);

        }

        if ($form->isSubmitted() && $form->isValid()) {

            // Enregistre le flux sortant
            $flux1 = new Flux();
            $flux1->setDateFlux(new DateTime());
            $flux1->setQuantite($conteneur->getQuantite() * -1);
            $flux1->setType("DÉPLACEMENT");
            $flux1->setAdresseStock($adresse);
            $flux1->setCodeConteneur($conteneur->getCodeConteneur());

            // Enregistre le flux entrant
            $flux2 = new Flux();
            $flux2->setDateFlux(new DateTime());
            $flux2->setQuantite($conteneur->getQuantite());
            $flux2->setType("DÉPLACEMENT");
            $flux2->setAdresseStock($form['idStock']->getData());
            $flux2->setCodeConteneur($conteneur->getCodeConteneur());

            $conteneurRepository->add($conteneur, true);
            $fluxRepository->add($flux1, true);
            $fluxRepository->add($flux2, true);


            return $this->redirectToRoute('app_conteneur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('conteneur/mouvement.html.twig', [
            'conteneur' => $conteneur,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_conteneur_delete', methods: ['POST'])]
    public function delete(Request $request, Conteneur $conteneur, ConteneurRepository $conteneurRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $conteneur->getId(), $request->request->get('_token'))) {
            $conteneurRepository->remove($conteneur, true);
        }

        return $this->redirectToRoute('app_conteneur_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}/pdf', name: 'app_conteneur_print', methods: ['GET'])]
    public function printPdf($id, ConteneurRepository $conteneurRepository): Response
    {
        $conteneur = $conteneurRepository->find($id);
        return new Response($this->conteneurPdf($conteneur), 200, ['Content-Type' => 'application/pdf',]);
    }

    private function conteneurPdf($conteneur)
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('default/mypdf.html.twig', [
            'conteneur' => $conteneur
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
    }


}
