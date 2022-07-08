<?php

namespace App\Controller;

use App\Entity\Conteneur;
use App\Entity\Flux;
use App\Form\ConteneurType;
use App\Form\ConteneurTypeAjustement;
use App\Form\ConteneurTypeMvt;
use App\Repository\ConteneurRepository;
use App\Repository\FluxRepository;
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

use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\String\ByteString;

#[isGranted("ROLE_LOG")]
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


    #[Route('/select', name: 'app_conteneur_select', methods: ['GET'])]
    public function selectConteneur(ConteneurRepository $conteneurRepository): Response
    {
        return $this->render('conteneur/select.html.twig', [
        ]);
    }

    #[Route('/new', name: 'app_conteneur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ConteneurRepository $conteneurRepository, StockRepository $stockRepository, FluxRepository $fluxRepository): Response
    {
        $conteneur = new Conteneur();
        $conteneur->setDateReception(new DateTime());
        $conteneur->setIdStock($stockRepository->find(1));

        // Créer un code conteneur aléatoire et unique
        $codeConteneur = 'CC-' . hexdec(uniqid());
        $conteneur->setCodeConteneur($codeConteneur);


        $form = $this->createForm(ConteneurType::class, $conteneur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

//           Inscrit le mouvement de reception dans la table flux
            $flux = new Flux();
            $flux->setDateFlux(new DateTime());
            $flux->setQuantite($form['quantite']->getData());
            $flux->setPartNumber($conteneur->getIdProduit());
            $flux->setType("RECEPTION");
            $flux->setOrigine($conteneur->getIdReception()->getBonDeCommande());
            $flux->setAdresseStock($conteneur->getIdStock()->getAdresseStock());
            $flux->setCodeConteneur($conteneur->getCodeConteneur());
            $conteneurRepository->add($conteneur, true);
            $fluxRepository->add($flux, true);

            // Add Flash d'impression avec lien pour imprimer
            $url = $this->generateUrl('app_conteneur_print', ['id' => $conteneur->getId()], UrlGenerator::ABSOLUTE_URL);
            $this->addFlash('imprimerConteneur',
                sprintf('Imprimer le code conteneur et le coller dessus <br/><a href="%s"><i class="fa-solid fa-print"></i></a>', $url));

            return $this->redirectToRoute('app_conteneur_new', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('conteneur/new.html.twig', [
            'conteneur' => $conteneur,
            'form' => $form,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_conteneur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Conteneur $conteneur, ConteneurRepository $conteneurRepository, FluxRepository $fluxRepository): Response
    {
        $quantiteInit = $conteneur->getQuantite();

        $form = $this->createForm(ConteneurTypeAjustement::class, $conteneur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

//            Inscrit le mouvement d'ajustement dans la table flux
            $flux = new Flux();
            $flux->setDateFlux(new DateTime());

            $quantite = $form['quantite']->getData() - $quantiteInit;

            $flux->setQuantite($quantite);

            $flux->setPartNumber($conteneur->getIdProduit());
            $flux->setType("AJUSTEMENT");
            $flux->setOrigine($request->get('commentaire'));
            $flux->setAdresseStock($conteneur->getIdStock()->getAdresseStock());
            $flux->setCodeConteneur($conteneur->getCodeConteneur());


            $conteneurRepository->add($conteneur, true);
            $fluxRepository->add($flux, true);
            return $this->redirectToRoute('app_conteneur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('conteneur/edit.html.twig', [
            'conteneur' => $conteneur,
            'form' => $form,
        ]);
    }

    #[Route('/select/{id}', name: 'app_conteneur_mouvement', methods: ['GET', 'POST'])]
    public function mouvement(Request $request, Conteneur $conteneur, ConteneurRepository $conteneurRepository, FluxRepository $fluxRepository): Response
    {
        $adresse = $conteneur->getIdStock();
        $form = $this->createForm(ConteneurTypeMvt::class, $conteneur);
        $form->handleRequest($request);


        // Si l'emplacement de destination est deja occupé bloquer  ajouter un add flash
        if ($form->isSubmitted() && $conteneurRepository->findOneby(['idStock' => $form['idStock']->getData()]) && !$form['idStock']->getData()->isMultiStockage()) {

            //Reinitialise l'adresse de stockage pour affichage
            $conteneur->setIdStock($adresse);

            // Add Flash de validation
            $this->addFlash('MouvementImpossible', 'L\'adresse de destination ' . $form['idStock']->getData() . ' est deja occupé');


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
            $flux1->setPartNumber($conteneur->getIdProduit());
            $flux1->setAdresseStock($adresse);
            $flux1->setCodeConteneur($conteneur->getCodeConteneur());

            // Enregistre le flux entrant
            $flux2 = new Flux();
            $flux2->setDateFlux(new DateTime());
            $flux2->setQuantite($conteneur->getQuantite());
            $flux2->setType("DÉPLACEMENT");
            $flux2->setPartNumber($conteneur->getIdProduit());
            $flux2->setAdresseStock($form['idStock']->getData());
            $flux2->setCodeConteneur($conteneur->getCodeConteneur());

            $conteneurRepository->add($conteneur, true);
            $fluxRepository->add($flux1, true);
            $fluxRepository->add($flux2, true);

            return $this->redirectToRoute('app_conteneur_select', [], Response::HTTP_SEE_OTHER);

        }

        return $this->renderForm('conteneur/mouvement.html.twig', [
            'conteneur' => $conteneur,
            'form' => $form,
        ]);
    }

    #[Route('/mouvement/{idStock}/{idConteneur}', name: 'app_conteneur_mouvement_drag', methods: ['GET'])]
    public function mouvement_drag($idConteneur, $idStock, Request $request, ConteneurRepository $conteneurRepository, FluxRepository $fluxRepository, StockRepository $stockRepository): Response
    {
        $conteneur = $conteneurRepository->find($idConteneur);
        $adresse = $stockRepository->find($idStock);

        // Si l'emplacement de destination est deja occupé bloquer et ajouter un add flash
        if ($adresse->getConteneurs()->isEmpty() == false && $adresse->isMultiStockage() == false) {

            // Add Flash de validation
            $this->addFlash('MouvementImpossible', 'L\'adresse de destination ' . $adresse->getAdresseStock() . ' est deja occupé');

            $response['resultat'] = false;
            // Renvoie sur la vue pour une nouvelle saisie
            return $this->json($response, 403);
        }

        // Enregistre le flux sortant
        $flux1 = new Flux();
        $flux1->setDateFlux(new DateTime());
        $flux1->setQuantite($conteneur->getQuantite() * -1);
        $flux1->setType("DÉPLACEMENT");
        $flux1->setPartNumber($conteneur->getIdProduit());
        $flux1->setAdresseStock($conteneur->getIdStock()->getAdresseStock());
        $flux1->setCodeConteneur($conteneur->getCodeConteneur());

        // Enregistre le flux entrant
        $flux2 = new Flux();
        $flux2->setDateFlux(new DateTime());
        $flux2->setQuantite($conteneur->getQuantite());
        $flux2->setType("DÉPLACEMENT");
        $flux2->setPartNumber($conteneur->getIdProduit());
        $flux2->setAdresseStock($adresse->getAdresseStock());
        $flux2->setCodeConteneur($conteneur->getCodeConteneur());

        $fluxRepository->add($flux1, true);
        $fluxRepository->add($flux2, true);

        $conteneur->setIdStock($adresse);
        $conteneurRepository->add($conteneur, true);

        $response['resultat'] = true;
        return $this->json($response, 200);

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

        $blackColor = [0, 0, 0];
        $dataBarcode = $conteneur->getCodeConteneur();
        $generator = new BarcodeGeneratorPNG();

        file_put_contents('barcode' . $dataBarcode . '.png', $generator->getBarcode($dataBarcode, 'C128', 3, 50, $blackColor),);

        return new Response($this->conteneurPdf($conteneur), 200, ['Content-Type' => 'application/pdf',]);
    }

    private function conteneurPdf($conteneur)
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->setIsRemoteEnabled(true);

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

        unlink('barcode' . $conteneur . '.png');
    }

    #[Route('/json/{codeConteneur}', name: 'app_conteneur_json', methods: ['GET', 'POST'])]
    public function jsonConteneur(Request $request, ConteneurRepository $conteneurRepository): Response
    {
        $conteneur = $conteneurRepository->findby(['codeConteneur' => $request->get('codeConteneur')]);
        json_encode($conteneur);
        if ($conteneur) {

            return $this->json($conteneur, 200);

        } else return $this->json(false, 200);
    }

}
