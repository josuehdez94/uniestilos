<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MetodosPagoController extends AbstractController
{
    /**
     * @Route("/metodos/pago", name="metodos_pago")
     */
    public function index(): Response
    {
        return $this->render('metodos_pago/index.html.twig', [
            'controller_name' => 'MetodosPagoController',
        ]);
    }
}
