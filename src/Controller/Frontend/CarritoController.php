<?php

namespace App\Controller\Frontend;

use App\Entity\User;
use App\Generales\Funciones;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CarritoController
 * @Route("/mi-cuenta/carrito")
 */
class CarritoController extends AbstractController
{
    /**
     * @Route("/", name="front_carrito_index", methods={"GET"})
     */
    public function indexCarrito(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $usuario = $this->validaUsuario($request);
        if($usuario == 'hay sesion'){
            $documento = $entityManager->getRepository(\App\Entity\Documento::class)->getLastDocumentoByUser($this->getUser());
        }else{
            $documento = $entityManager->getRepository(\App\Entity\Documento::class)->getLastDocumentoByUserAnon($usuario);

        }
        $carrito = $entityManager->getRepository(\App\Entity\Documento::class)->findOneById($documento[1]);
        $totalArticulos = $entityManager->getRepository(\App\Entity\Documento::class)->getTotalArticulosByDocumento($carrito->getId())['cantidad'];

        return $this->render('Frontend/Carrito/carrito.html.twig', [
            'carrito' => $carrito,
            'totalArticulos' => $totalArticulos
        ]);
    }

    /**
     * @Route("/agregar-articulo/{user_uid}-{crypt}", name="front_carrito_agregar_articulo", methods={"POST"})
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

        //$validaUsuario = new Funciones();
        $usuario = $this->validaUsuario($request);
        if($usuario == 'hay sesion'){
            $documento = $entityManager->getRepository(\App\Entity\Documento::class)->getLastDocumentoByUser($this->getUser());
        }else{
            $documento = $entityManager->getRepository(\App\Entity\Documento::class)->getLastDocumentoByUserAnon($usuario);

        }
        $carrito = $entityManager->getRepository(\App\Entity\Documento::class)->findOneById($documento[1]);
        if($this->isCsrfTokenValid($articulo->getUrlAmigable(), $token)) {
            if (empty($carrito)) {
                #crear carrito
                $ultimoId = $entityManager->getRepository(\App\Entity\Documento::class)->getLastDocumentoId();
                $folio = $ultimoId[1] + 1;
                $carritoNuevo = new \App\Entity\Documento();
                $usuario == 'hay sesion' ? $carritoNuevo->setCliente($this->getUser()) : $carritoNuevo->setUserCookie($usuario);
                $carritoNuevo->setTipo('C');
                $carritoNuevo->setFolio('TO-' . $folio);
                $entityManager->persist($carritoNuevo);
                $entityManager->flush();
                #crear registro
                $documentoCreado = $entityManager->getRepository(\App\Entity\Documento::class)->findOneById($carritoNuevo->getId());
                $registro = new \App\Entity\DocumentoRegistro();
                $registro->setArticulo($articulo);
                $registro->setCantidad($cantidad);
                $registro->setDocumento($documentoCreado);
                $registro->setPrecio($precio);
                $registro->setTotal(number_format($cantidad * $precio, 2, '.', ''));
                $entityManager->persist($registro);
                $entityManager->flush();
                #calcular totales de carrito
                $this->calcularTotales($documentoCreado);
            } else {
                $articuloRepetido = $entityManager->getRepository(\App\Entity\DocumentoRegistro::class)->findOneBy(['articulo' => $articulo->getId(), 'Documento' => $carrito->getId()]);
                if (empty($articuloRepetido)) {
                    #crear registro
                    $registro = new \App\Entity\DocumentoRegistro();
                    $registro->setArticulo($articulo);
                    $registro->setCantidad($cantidad);
                    $registro->setDocumento($carrito);
                    $registro->setPrecio($precio);
                    $registro->setTotal(number_format($cantidad * $precio, 2, '.', ''));
                    $entityManager->persist($registro);
                } else {
                    $total = ($articuloRepetido->getCantidad() + $cantidad) * ($precio);
                    $articuloRepetido->setCantidad($articuloRepetido->getCantidad() + $cantidad);
                    $articuloRepetido->setTotal(number_format($total,2, '.', ''));
                }
                $entityManager->flush();
                #calcular totales de carrito
                $this->calcularTotales($carrito);
            }
            return $this->redirectToRoute('front_carrito_articulo_agregado', [
                'articulo_urlAmigable' => $articulo->getUrlAmigable()
            ]);
        }else{
            throw $this->createNotFoundException('Error en token al agregar articulo al carrito.');
        }
    }

    /**
     * @Route("/articulo-agregado/{articulo_urlAmigable}", name="front_carrito_articulo_agregado", methods={"GET", "POST"})
     */
    public function articuloAgregado(Request $request, $articulo_urlAmigable)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $articulo = $entityManager->getRepository(\App\Entity\Articulo::class)->findOneByUrlAmigable($articulo_urlAmigable);
        if(!$articulo){
            throw $this->createNotFoundException('El articulo no ha sido encontrado');
        }
        $usuario = $this->validaUsuario($request);
        if($usuario == 'hay sesion'){
            $documento = $entityManager->getRepository(\App\Entity\Documento::class)->getLastDocumentoByUser($this->getUser());
        }else{
            $documento = $entityManager->getRepository(\App\Entity\Documento::class)->getLastDocumentoByUserAnon($usuario);

        }
        $carrito = $entityManager->getRepository(\App\Entity\Documento::class)->findOneById($documento[1]);
        foreach ($carrito->getRegistros() as $item){
            if($item->getArticulo()->getId() == $articulo->getId()){
                $cantidad = $item->getCantidad();
            }
        }
        return $this->render('Frontend/Carrito/articuloAgregado.html.twig', [
            'articulo' => $articulo,
            'cantidad' => $cantidad
        ]);
    }

    /**
     * funcion para validar sesion de usuario, si no tiene se crea cookie para poder generar carrito
     * @param request
     */
    public function validaUsuario($request){
        if(empty($this->getUser())){
            #se verifica si existe la cookie
            if (isset($request->cookies->all()['usertp'])) {
                $cookieSesion = $request->cookies->all()['usertp'];
                #se crea cookie de sesion
            }else{
                $hash = hash('md5', date('Y-m-d g:i:s'));
                $response = new Response();
                $time = time() + (31536000);
                $response->headers->setCookie(new Cookie('userAnon', $hash, $time));
                $response->sendHeaders();
                $cookieSesion = $response->headers->getCookies()[0]->getValue();
            }
            return $cookieSesion;

        }
        return 'hay sesion';
    }

    /**
     * funcion para calcular los totales de un carrito
     * @param $documento
     */
    public function calcularTotales($documento){
        $entityManager = $this->getDoctrine()->getManager();
        $total = 0;
        foreach ($documento->getRegistros() as $registro){
            $total += $registro->getTotal();
        }
        $total > 1600 ? $totalConEnvio = $total : $totalConEnvio = $total + 109.00;
        #actualizar totales
        $documento->setTotal(number_format($total, 2, '.', ''));
        $documento->setTotalConEnvio(number_format($totalConEnvio, 2, '.', ''));
        $entityManager->flush();
    }
}
