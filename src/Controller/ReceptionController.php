<?php

namespace App\Controller;

use App\Entity\Reception;
use App\Form\ReceptionType;
use App\Repository\ReceptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reception')]
class ReceptionController extends AbstractController
{
    #[Route('/', name: 'app_reception_index', methods: ['GET'])]
    public function index(ReceptionRepository $receptionRepository): Response
    {
        return $this->render('reception/index.html.twig', [
            'receptions' => $receptionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reception_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ReceptionRepository $receptionRepository): Response
    {
        $reception = new Reception();
        $form = $this->createForm(ReceptionType::class, $reception);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $receptionRepository->add($reception, true);

            return $this->redirectToRoute('app_reception_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reception/new.html.twig', [
            'reception' => $reception,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reception_show', methods: ['GET'])]
    public function show(Reception $reception): Response
    {
        return $this->render('reception/show.html.twig', [
            'reception' => $reception,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reception_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reception $reception, ReceptionRepository $receptionRepository): Response
    {
        $form = $this->createForm(ReceptionType::class, $reception);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $receptionRepository->add($reception, true);

            return $this->redirectToRoute('app_reception_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reception/edit.html.twig', [
            'reception' => $reception,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reception_delete', methods: ['POST'])]
    public function delete(Request $request, Reception $reception, ReceptionRepository $receptionRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reception->getId(), $request->request->get('_token'))) {
            $receptionRepository->remove($reception, true);
        }

        return $this->redirectToRoute('app_reception_index', [], Response::HTTP_SEE_OTHER);
    }
}
