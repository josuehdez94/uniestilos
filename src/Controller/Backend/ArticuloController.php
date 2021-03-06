<?php

namespace App\Controller\Backend;

use App\Entity\Articulo;
use App\Repository\ArticuloRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#formularios
use App\Form\Backend\Articulo\ArticuloType;

/**
 * @Route("/backend/articulos")
 */
class ArticuloController extends AbstractController
{
    /**
     * @Route("", name="backend_articulo_index", methods={"GET"})
     */
    public function indexArticulo(ArticuloRepository $articuloRepository): Response
    {
        return $this->render('backend/Articulo/indexArticulo.html.twig', [
            'articulos' => $articuloRepository->findAll(),
        ]);
    }

    /**
     * @Route("/nuevo", name="backend_articulo_nuevo", methods={"GET","POST"})
     */
    public function nuevoArticulo(Request $request): Response
    {
        $articulo = new Articulo();
        $form = $this->createForm(ArticuloType::class, $articulo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($articulo);
            $entityManager->flush();
            $this->addFlash('Creado', 'Articulo creado correctamente');
            return $this->redirectToRoute('backend_articulo_index');
        }

        return $this->render('backend/Articulo/nuevoArticulo.html.twig', [
            'articulo' => $articulo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/detalles/{id}", name="backend_articulo_detalles", methods={"GET"})
     */
    public function detallesArticulo($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $articulo = $entityManager->getRepository(\App\Entity\Articulo::class)->findOneBy(['id' => $id]);
        if(!$articulo){
            throw $this->createNotFoundException('Articulo no encontrado');
        }
        return $this->render('backend/Articulo/detallesArticulo.html.twig', [
            'articulo' => $articulo
        ]);
    }

    /**
     * @Route("/editar/generales/{id}", name="backend_articulo_editar", methods={"GET","POST"})
     */
    public function editarArticulo(Request $request, $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $articulo = $entityManager->getRepository(Articulo::class)->findOneBy(['id' => $id]);
        if(!$articulo){
            throw $this->createNotFoundException('Articulo no encontrado');
        }
        $form = $this->createForm(ArticuloType::class, $articulo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articulo->setUsuarioEditor($this->getUser());
            $entityManager->flush();
            $this->addFlash('Editado', 'El articulo ha sido editado correctamente');
            return $this->redirectToRoute('backend_articulo_editar', [
                'id' => $articulo->getId()
            ]);
        }

        return $this->render('backend/Articulo/editarArticuloGenerales.html.twig', [
            'articulo' => $articulo,
            'submenu' => 'generales',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="articulo_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Articulo $articulo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$articulo->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($articulo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('articulo_index');
    }
}
