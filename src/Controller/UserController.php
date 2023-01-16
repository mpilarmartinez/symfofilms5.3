<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    /**
     * @Route(
     *      "/home",
     *      name="home",
     *      methods={"GET"}
     *  )
     */
    
    public function home(){

        // rechaza usuarios no identificados
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // CARGA LA VISTA CON LA INFOMRACIÓN DEL USUARIO
        return $this->render('user/home.html.twig');
        
    }
}
