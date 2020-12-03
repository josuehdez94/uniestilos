<?php

namespace App\Controller\Frontend;

use App\Entity\User;
use App\Generales\Funciones;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CarritoController
 * @Route("/mi-cuenta/carrito")
 */
class CarritoController extends AbstractController
{
    /**
     * @Route("", name="front_carrito_index", methods={"GET"})
     */
    public function indexCarrito(): Response
    {
        return $this->render('carrito/index.html.twig', [
            'controller_name' => 'CarritoController',
        ]);
    }

    /**
     * @Route("/agregar-articulo/{user_uid}-{crypt}", name="front_carrito_agregar_articulo", methods={"GET"})
     */
    public function agregarArticulo(Request $request, $user_uid, $crypt): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $cliente = $entityManager->getRepository(User::class)->findOneByUid($user_uid);
        if(!$cliente){
            throw $this->createNotFoundException('No se encontro el usuario.');
        }
        if($cliente->getId() != $this->getUser()->getId()){
            throw $this->createNotFoundException('No puedes acceder a la información de otros clientes.');
        }
        #desencriptar
        $key = explode(',', $crypt)[1];
        $cadena = explode(',', $crypt)[0];
        $decrypt = new Funciones();
        $desencriptar = $decrypt->desencriptar($cadena, $key);
        if ($desencriptar != $cliente->getDecrypt()){
            throw $this->createNotFoundException('No puedes acceder a esta información.');
        }

        $articulo = $entityManager->getRepository(\App\Entity\Articulo::class)->findOneById($request->get('articulo'));
        $cantidad = $request->get('cantidad');
        $precio = $request->get('precio');
        $token = $request->get('token');

        $validaUsuario = new Funciones();
        $usuario = $validaUsuario->validaUsuario($request);
        if($usuario == 'hay sesion'){
            $documento = $entityManager->getRepository(\App\Entity\Documento::class)->getLastDocumentoByUser($this->getUser());
        }else{
            $documento = $entityManager->getRepository(\App\Entity\Documento::class)->getLastDocumentoByUserAnon($usuario);

        }
        $carrito = $entityManager->getRepository(\App\Entity\Documento::class)->findOneById($documento[1]);
        if($this->isCsrfTokenValid($articulo->getId(), $request->request->get('token'))) {
            if (empty($carrito)) {
                #crear carrito
                $ultimoId = $entityManager->getRepository(\App\Entity\Documento::class)->getLastDocumentoId();
                $folio = $ultimoId + 1;
                $carritoNuevo = new \App\Entity\Documento();
                $usuario == 'hay sesion' ? $carritoNuevo->setCliente($this->getUser()) : $carritoNuevo->setUserCookie($usuario);
                $carritoNuevo->getTipo('C');
                $carritoNuevo->setFolio('TO-' . $folio);
                $entityManager->persist($carritoNuevo);
                $entityManager->flush();
                #crear registro
                $documentoCreado = $entityManager->getRepository(\App\Entity\Documento::class)->findOneById($carritoNuevo->getId());
                $registro = new \App\Entity\DocumentoRegistro();
                $registro->setArticulo($articulo);
                $registro->setCantidad($cantidad);
                $registro->setDocumento($documentoCreado);
                $registro->setPrecio();
                $entityManager->persist($registro);
                $entityManager->flush();
            } else {
                $articuloRepetido = $entityManager->getRepository(\App\Entity\DocumentoRegistro::class)->findOneBy(['articulo' => $articulo->getId(), 'documento' => $carrito->getId()]);
                if (empty($articuloRepetido)) {
                    #crear registro
                    $registro = new \App\Entity\DocumentoRegistro();
                    $registro->setArticulo($articulo);
                    $registro->setCantidad($cantidad);
                    $registro->setDocumento($carrito);
                    $registro->setPrecio($precio);
                    $entityManager->persist($registro);
                } else {
                    $articuloRepetido->setCantidad($articuloRepetido->getCantidad() + $cantidad);
                }
                $entityManager->flush();
            }
        }else{
            throw $this->createNotFoundException('Error en token al agregar articulo al carrito.');
        }
    }
}
