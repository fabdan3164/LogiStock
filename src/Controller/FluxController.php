<?php

namespace App\Controller;

use App\Repository\FluxRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/flux')]
class FluxController extends AbstractController
{
    #[Route('/', name: 'app_flux')]
    public function index(FluxRepository $fluxRepository): Response
    {
        return $this->render('flux/index.html.twig', [
            'controller_name' => 'FluxController',
            'flux'=>$fluxRepository->findAll(),
        ]);
    }

    #[Route('/{codeConteneur}', name: 'app_flux_show')]
    public function show(FluxRepository $fluxRepository,$codeConteneur): Response
    {
        return $this->render('flux/show.html.twig', [
            'controller_name' => 'FluxController',
            'flux'=>$fluxRepository->findby(['codeConteneur'=>$codeConteneur]),
        ]);
    }
}
