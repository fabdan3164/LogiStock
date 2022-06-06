<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use App\Repository\UtilisateurRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[isGranted("ROLE_USER")]
    #[Route('/', name: 'app_main')]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('main/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }


    #[isGranted("ROLE_LOG")]
    #[Route('/utilisateur', name: 'app_utilisateur')]
    public function utilisateur(UtilisateurRepository $utilisateurRepository): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
        ]);
    }
}
