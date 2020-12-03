<?php

namespace App\Controller\Frontend;

use App\Entity\ClienteDireccion;
use App\Form\Frontend\ClienteDireccion\ClienteDireccionType;
use App\Repository\ClienteDireccionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#Funciones
use App\Generales\Funciones;

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
            #encriptar datos
            $encryt = new Funciones();
            $cadena = md5(random_int(-20000, 50000).date('Y-m-d g:i:s'));
            $key = md5(random_int(-500000, 20000).date('Y-m-d g:i:s'));
            $clienteDireccion->setCrypt($encryt->encriptar($cadena, $key).','.$key);
            $clienteDireccion->setDecrypt($cadena);
            $clienteDireccion->setCliente($this->getUser());
            $clienteDireccion->setUid($cadena);
            $entityManager->persist($clienteDireccion);
            $entityManager->flush();
            #establecer direccion como predeterminada
            $cliente = $entityManager->getRepository(\App\Entity\User::class)->findOneById($this->getUser()->getId());
            $direccionCreada = $entityManager->getRepository(ClienteDireccion::class)->findOneById($clienteDireccion->getId());
            $cliente->setDireccionPrincipal($direccionCreada);
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
     * @Route("/editar/{uid}-{crypt}", name="front_cliente_direccion_editar", methods={"GET","POST"})
     */
    public function editarDireccion(Request $request, $uid, $crypt): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $direccion = $entityManager->getRepository(ClienteDireccion::class)->findOneByUid($uid);
        if(!$direccion){
            throw $this->createNotFoundException('No se encontro la dirección.');
        }
        if($direccion->getCliente()->getId() != $this->getUser()->getId()){
            throw $this->createNotFoundException('No puedes acceder a la información de otros clientes.');
        }
        #desencriptar
        $key = explode(',', $crypt)[1];
        $cadena = explode(',', $crypt)[0];
        $decrypt = new Funciones();
        $desencriptar = $decrypt->desencriptar($cadena, $key);
        if ($desencriptar != $direccion->getDecrypt()){
            throw $this->createNotFoundException('No puedes acceder a esta información.');
        }

        $form = $this->createForm(ClienteDireccionType::class, $direccion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encryt = new Funciones();
            $cadena = md5(random_int(-20000, 50000).date('Y-m-d g:i:s'));
            $key = md5(random_int(-500, 20000).date('Y-m-d g:i:s'));
            $direccion->setCrypt($encryt->encriptar($cadena, $key).','.$key);
            $direccion->setDecrypt($cadena);
            $entityManager->flush();
            $this->addFlash('Editado', 'Dirección editada correctamente');
            return $this->redirectToRoute('front_cliente_direccion_index');
        }

        return $this->render('Frontend/ClienteDireccion/editarDireccion.html.twig', [
            'direccion' => $direccion,
            'form' => $form->createView(),
        ]);
    }

    /**
     *  @Route("/elegir-predeterminada/{uid}-{crypt}", name="front_cliente_direccion_elegir", methods={"POST"})
     */
    public function direccionPredeterminada(Request $request, $uid, $crypt){
        $entityManager = $this->getDoctrine()->getManager();
        $direccion = $entityManager->getRepository(ClienteDireccion::class)->findOneByUid($uid);
        if(!$direccion){
            throw $this->createNotFoundException('No se encontro la dirección.');
        }
        if($direccion->getCliente()->getId() != $this->getUser()->getId()){
            throw $this->createNotFoundException('No puedes acceder a la información de otros clientes.');
        }
        #desencriptar
        $key = explode(',', $crypt)[1];
        $cadena = explode(',', $crypt)[0];
        $decrypt = new Funciones();
        $desencriptar = $decrypt->desencriptar($cadena, $key);
        if ($desencriptar != $direccion->getDecrypt()){
            throw $this->createNotFoundException('No puedes acceder a esta información.');
        }

        if ($this->isCsrfTokenValid($direccion->getUid(), $request->request->get('token'))) {
            $cliente = $entityManager->getRepository(\App\Entity\User::class)->findOneById($this->getUser()->getId());
            $cliente->setDireccionPrincipal($direccion);
            $entityManager->flush();
            $this->addFlash('Editado', 'La dirección predeterminada ha sido cambiada correctamente');
            return $this->redirectToRoute('front_cliente_direccion_index');
        }else{
            $this->addFlash('Eliminado', 'No se pudo establecer la direccion como predeterminada(error token)');
            return $this->redirectToRoute('front_cliente_direccion_index');
        }

    }

    /**
     * @Route("/descartar/{uid}-{crypt}", name="front_cliente_direccion_eliminar", methods={"POST"})
     */
    public function descartarDireccion(Request $request, $uid, $crypt): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $direccion = $entityManager->getRepository(ClienteDireccion::class)->findOneByUid($uid);
        if(!$direccion){
            throw $this->createNotFoundException('No se encontro la dirección.');
        }
        if($direccion->getCliente()->getId() != $this->getUser()->getId()){
            throw $this->createNotFoundException('No puedes acceder a la información de otros clientes.');
        }
        #desencriptar
        $key = explode(',', $crypt)[1];
        $cadena = explode(',', $crypt)[0];
        $decrypt = new Funciones();
        $desencriptar = $decrypt->desencriptar($cadena, $key);
        if ($desencriptar != $direccion->getDecrypt()){
            throw $this->createNotFoundException('No puedes acceder a esta información.');
        }

        if ($this->isCsrfTokenValid($direccion->getUid(), $request->request->get('token'))) {
            $cliente = $entityManager->getRepository(\App\Entity\User::class)->findOneById($this->getUser()->getId());
            if($cliente->getDireccionPrincipal()->getId() != $direccion->getId()){
                $direccion->setEliminada(true);
                $direccion->setFechaHoraEliminada(new \DateTime());
                $entityManager->flush();
                $this->addFlash('Editado', 'La dirección ha sido eliminada correctamente');
                return $this->redirectToRoute('front_cliente_direccion_index');
            }else{
                $this->addFlash('Eliminado', 'No puedes eliminar tu direccion predeterminada');
                return $this->redirectToRoute('front_cliente_direccion_index');
            }
        }else{
            $this->addFlash('Eliminado', 'No se pudo eliminar la direccion(error de token)');
            return $this->redirectToRoute('front_cliente_direccion_index');
        }
    }
}
