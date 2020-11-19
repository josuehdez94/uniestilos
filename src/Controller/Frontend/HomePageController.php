<?php

namespace App\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class HomePageController extends AbstractController
{
    /**
     * @Route("/", name="frontend_home_page")
     */
    public function index()
    {
        /*return $this->render('home_page/paginaPrincipal.html.twig', [
            'controller_name' => 'Default',
        ]); */
        return $this->render('home_page/paginaPrincipal2.html.twig', [
            'controller_name' => 'Default',
        ]); 

    }
    
    /**
     * @Route("/formulario-inicio", name="home_formulario_ingreso", methods={"GET"})
     */
    public function formularioIngreso(){

        return $this->render('home_page/formularioInicio.html.twig', [
            'controller_name' => 'Default',
        ]);
    }

    /**
     * @Route("/inicio", name="home_pagina_inicio", methods={"GET"})
     */
    public function paginaInicio(){

        return $this->render('home_page/formularioInicio.html.twig', [
            'controller_name' => 'Bienvenido a mi sitio',
        ]);
    }
}
