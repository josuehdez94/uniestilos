<?php

namespace App\Controller\Frontend;

use App\Entity\User;
use App\Form\Frontend\User\CambiarUsuarioType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
#Funciones
use App\Generales\Funciones;

/**
 * Class UserSecurityController
 * @Route("/cliente/mi-cuenta/seguridad")
 */
class UserSecurityController extends AbstractController
{
    /**
     * @Route("", name="front_cliente_security")
     */
    public function index(): Response
    {
        return $this->render('Frontend/UserSecurity/indexSeguridad.html.twig', [
            'controller_name' => 'UserSecurityController',
        ]);
    }

    /**
     *@Route("/cambiar-usuario/{user_uid}-{crypt}", name="front_cliente_cambiar_usuario", methods={"GET", "POST"})
     */
    public function cambiarUsuario(Request $request, $user_uid, $crypt){
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

        $form = $this->createForm(CambiarUsuarioType::class, $cliente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            #encriptar datos
            $encryt = new Funciones();
            $cadena = md5(random_int(-20000, 50000).date('Y-m-d g:i:s'));
            $key = md5(random_int(-50000, 20000).date('Y-m-d g:i:s'));
            $cliente->setCrypt($encryt->encriptar($cadena, $key).','.$key);
            $cliente->setDecrypt($cadena);
            $entityManager->flush();
            $this->addFlash('Editado', 'Nombre de usuario cambiado correctamente');
            return $this->redirectToRoute('front_cliente_security');
        }

        return $this->render('Frontend/UserSecurity/cambiarUsuario.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/cambiar-contraseña/{user_uid}-{crypt}", name="front_cliente_change_contrasena")
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $passwordEncoder, $user_uid, $crypt) {
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

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);
        $cliente = $entityManager->getRepository(\App\Entity\User::class)->findOneById($this->getUser()->getId());


        if ($form->isSubmitted() && $form->isValid()) {
            #encriptar datos
            $encryt = new Funciones();
            $cadena = md5(random_int(-20000, 50000).date('Y-m-d g:i:s'));
            $key = md5(random_int(-50000, 20000).date('Y-m-d g:i:s'));
            $encoded = $passwordEncoder->encodePassword($this->getUser(), $form->get('plainPassword')->getData());
            $cliente->setCrypt($encryt->encriptar($cadena, $key).','.$key);
            $cliente->setDecrypt($cadena);
            $cliente->setPassword($encoded);
            $entityManager->flush();
            $this->addFlash('Editado', 'Contraseña cambiada correctamente');
            return $this->redirectToRoute('front_cliente_security');
        }

        return $this->render('Frontend/UserSecurity/cambiarContrasena.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}
