<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Mailer\MailerInterface;
use App\Form\ContactoFormType;
use Symfony\Component\Mime\Email;
use App\Repository\PeliculaRepository;

class DefaultController extends AbstractController
{

    
    /**
     * @Route ("/contact", name="contacto")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contacto(Request $request, MailerInterface $mailer): Response
    {
        
        $formulario = $this->createForm(ContactoFormType::class);
        $formulario->handleRequest($request);
        
        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $datos = $formulario->getData();
            
            $email = new Email();
            $email->from($datos['email'])
                  ->to($this->getParameter('app.admin_email'))
                  ->subject($datos['asunto'])
                  ->text($datos['mensaje'])
            ;                  
            
  
            $mailer->send($email);
         
            
            $this->addFlash('success', 'Mensaje enviado correctamente');
            return $this->redirectToRoute('portada');
        }
        
        return $this->renderForm("contacto.html.twig",
            ["formulario"=>$formulario]);
        
   }
   

#[Route('/', name: 'portada')]
 public function portada(PeliculaRepository $pr){
       return $this->render('portada.html.twig', [
           'peliculas' => $pr->findLast($this->getParameter('app.portada.covers'))
           
       ]);
   }

}



    
    
    
    
    
    
    
    

