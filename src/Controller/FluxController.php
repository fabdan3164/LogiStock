<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FluxController extends AbstractController
{
    #[Route('/flux', name: 'app_flux')]
    public function index(): Response
    {
        return $this->render('flux/index.html.twig', [
            'controller_name' => 'FluxController',
        ]);
    }
}
