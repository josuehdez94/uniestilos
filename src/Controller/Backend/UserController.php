<?php

namespace App\Controller\Backend;

use App\Entity\User;
use App\Form\Backend\User\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * @Route("/backend/admin/usuario")
 */
class UserController extends AbstractController
{
     use ResetPasswordControllerTrait;

    private $resetPasswordHelper;

    public function __construct(ResetPasswordHelperInterface $resetPasswordHelper)
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
    }
    
    /**
     * @Route("", name="backend_user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('backend/User/indexUsuario.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/nuevo", name="backend_user_nuevo", methods={"GET","POST"})
     */
    public function nuevoUsuario(Request $request, UserPasswordEncoderInterface $passwordEncoder, MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            
            #encriptar datos
            $encryt = new \App\Generales\Funciones();
            $cadena = md5(random_int(-20000, 50000).date('Y-m-d g:i:s'));
            $key = md5(random_int(-50000, 20000).date('Y-m-d g:i:s'));
            $clave = md5(date('Y-m-d g:i:s').random_int(-1000, 1000), false);
            $encoded = $passwordEncoder->encodePassword($user, 'uniestilos');
            $user->setPassword($encoded);
            $user->setTipoUser('E');
            $user->setClaveVerificacion($clave);
            $user->setCrypt($encryt->encriptar($cadena, $key).','.$key);
            $user->setDecrypt($cadena);
            $user->setUid($cadena);
            $entityManager->persist($user);
            $entityManager->flush();
            //$this->addFlash('Creado', 'Usuario creado exitosamente');
            //return $this->redirectToRoute('backend_user_index');
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $mailer
            );
        }

        return $this->render('backend/User/nuevoUsuario.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
    
    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer): RedirectResponse
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);
        // Marks that you are allowed to see the app_check_email page.
        $this->setCanCheckEmailInSession();

        // Do not reveal whether a user account was found or not.
        if(!$user){
           $this->addFlash('reset_password_error', 'El email ingresado no tiene cuenta en nicenmt');
           return $this->redirectToRoute('app_forgot_password_request'); 
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'app_forgot_password_request'.
            // Caution: This may reveal if a user is registered or not.
            //
            /*$this->addFlash('reset_password_error', sprintf(
                'There was a problem handling your password reset request - %s',
                 $e->getReason()
            ));*/

            //return $this->redirectToRoute('app_check_email');
            $this->addFlash('reset_password_error', 'ocurrio un error al generar la clave de restablecimiento de contraseña');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        /*$email = (new TemplatedEmail())
            ->from(new Address('reset@nicenmt.com', 'reset password nice'))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('backend/ResetPassword/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
                'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
            ])
        ;*/
        //$resetToken = hash('sha512', date('Y-m-d g:i:s'));
        //$resetToken = $this->resetPasswordHelper->generateResetToken($user);
        
        $message = (new Email())
                ->from('contacto@uniestilos.shop')
                ->to($user->getEmail())
                ->subject('Su cuenta se ha creado, establece tu contraseña')
                ->html($this->renderView(
                    'backend/ResetPassword/email.html.twig', [
                        'resetToken' => $resetToken,
                        'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
                    ]
                    ),
                'text/html'
                )
        ;

        $mailer->send($message);

        return $this->redirectToRoute('app_check_email');
    }
}
