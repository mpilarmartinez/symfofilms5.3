<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Psr\Log\LoggerInterface;
use App\Service\FileService;
use App\Form\UserDeleteFormType;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, 
                    UserPasswordHasherInterface $passwordHasher, 
                    UserRepository $ur
    ): Response{
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
            $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $ur->add($user, true);

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@symfofilms.com', 'Registro en Symfofilms'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('email/register_verification.html.twig')
            );
            
            
            // flasheo el mensaje a mostrar
            $this->addFlash('success', 'Operacion realizada, revisa tu email y haz clic en 
             el enlace para completar la operacion de registro.');
           

            return $this->redirectToRoute('portada');
        }

        return $this->renderForm('user/register.html.twig', [
            'registrationForm' => $form
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

     
        // flashea un mensaje de exito
        $this->addFlash('success', 'Tu email ha sido verificado.');
        
        // redireccionamos a la home, so está logueado, le aparecera 
        // la vista con el formulario de identificacion
        return $this->redirectToRoute('home');
        
    }
    
    
    /**
     * @Route(
     *      "/resendverificationemail",
     *      name="resend_verification",
     *      methods={"GET"})
     */
    public function rensedVerificationEmail(Request $request): Response {
    
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    
    $user = $this->getUser();
    
    $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
            ->from(new Address(
                'no-reply@symfofilms.robertsallent.com',
                'Registro de usuarios'))
            ->to($user->getEmail())
            ->subject('Por favor, confirma tu email')
            ->htmlTemplate('email/register_verification.html.twig')
        );
    
    
    $mensaje = 'Operacion realizada, revisa tu email y haz 
                clic en el enlace para completar la operacion de registro.
                El mensaje de advertencia desaparecera tras completar
                el proceso';
    $this->addFlash('success', $mensaje);
    
    return $this->redirectToRoute('home');
         
}


/**
 * @Route(
 *      "/unsubscribe",
 *      name="unsubscribe",
 *      methods={"GET", "POST"})
 */
public function unsubscribe(Request $request,
                            LoggerInterface $appUserInfoLogger,
                            FileService $uploader): Response{

    // rechaza usuarios no identificados
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    
    $usuario = $this->getUser();   // recupera el usuario
    
    
    // creacion del formulario de confirmacion
    $formulario = $this->createForm(UserDeleteFormType::class, $usuario);
    $formulario->handleRequest($request);
    
    // si el formulario llega y es valido
    if ($formulario->isSubmitted() && $formulario->isValid()) {
        
    // pone a NULL el user_id de las peliculas relacionadas
    
        foreach($usuario->getPeliculas() as $pelicula)
            $usuario->removePelicula($pelicula);
        
        // modifica el directorio de destino para los ficheros
       // $uploader->targetDirectory = $this->getParameter('app.users_pics_root');
        
       // if($usuario->getFotografia())  //si hay foto
       //      $uploader->delete($usuario->getFotografia());
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($usuario);   // borra el usuario
        $entityManager->flush();   // borra el usuario
        
         // cerrar la sesion
         $this->container->get('security.token_storage')->setToken(null);
         $this->container->get('session')->invalidate();
          
         // flashear el mensaje
         $mensaje = 'Usuario'.$usuario->getDisplayname().'eliminado correctamente.';
         $this->addFlash('success',$mensaje);
     
         // loguear el mensaje
         $mensaje = 'Usuario '.$usuario->getDisplayname().'se ha dado de baja.';
         $appUserInfoLogger->warning($mensaje);
         
         return $this->redirectToRoute('portada');
    }
     // redirigimos a portda
     return $this->renderForm("user/delete.html.twig", [
         "formulario"=>$formulario,
         "usuario"=> $usuario
    ]);
      
    }
    
    
   
    
}







