<?php

namespace App\Controller\Frontend;

use App\Entity\ClienteDireccion;
use App\Form\Frontend\ClienteDireccion\ClienteDireccionType;
use App\Repository\ClienteDireccionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cliente/direcciones")
 */
class ClienteDireccionController extends AbstractController
{
    /**
     * @Route("/", name="front_cliente_direccion_index", methods={"GET"})
     */
    public function misDirecciones(ClienteDireccionRepository $clienteDireccionRepository): Response
    {
        
        
        $entityManager = $this->getDoctrine()->getManager();
        $direcciones = $entityManager->getRepository(ClienteDireccion::class)->findByCliente($this->getUser());
        return $this->render('Frontend/ClienteDireccion/misDirecciones.html.twig', [
            'direcciones' => $direcciones,
        ]);
    }

    /**
     * @Route("/nueva", name="font_cliente_direccion_nueva", methods={"GET","POST"})
     */
    public function nuevaDireccion(Request $request): Response
    {
        $clienteDireccion = new ClienteDireccion();
        $form = $this->createForm(ClienteDireccionType::class, $clienteDireccion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $clienteDireccion->setCliente($this->getUser());
            $entityManager->persist($clienteDireccion);
            $entityManager->flush();
            $this->addFlash('Creado', 'Dirección añadida correctamente');
            return $this->redirectToRoute('front_cliente_direccion_index');
        }

        return $this->render('Frontend/ClienteDireccion/new.html.twig', [
            'cliente_direccion' => $clienteDireccion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="cliente_direccion_show", methods={"GET"})
     */
    public function show(ClienteDireccion $clienteDireccion): Response
    {
        return $this->render('cliente_direccion/show.html.twig', [
            'cliente_direccion' => $clienteDireccion,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="cliente_direccion_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ClienteDireccion $clienteDireccion): Response
    {
        $form = $this->createForm(ClienteDireccionType::class, $clienteDireccion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('cliente_direccion_index');
        }

        return $this->render('cliente_direccion/edit.html.twig', [
            'cliente_direccion' => $clienteDireccion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="cliente_direccion_delete", methods={"DELETE"})
     */
    public function delete(Request $request, ClienteDireccion $clienteDireccion): Response
    {
        if ($this->isCsrfTokenValid('delete'.$clienteDireccion->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($clienteDireccion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('cliente_direccion_index');
    }
}
