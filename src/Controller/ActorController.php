<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Actor;
use App\Form\ActorFormType;
use App\Form\ActorDeleteFormType;
use Psr\Log\LoggerInterface;
use App\Service\FileService;
use App\Repository\ActorRepository;
use App\Service\PaginatorService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


class ActorController extends AbstractController
{

    /**
     * @Route("/actores/{pagina}", defaults={"pagina":1}, name="actor_list")
     */
    public function index(int $pagina, PaginatorService $paginator): Response{
        
        $paginator->setEntityType('App\Entity\Actor');
        
        $actors = $paginator->findAllEntities($pagina);
        
        return $this->render("actor/list.html.twig",
            ['actores' => $actors,
             'paginator'  => $paginator
        ]);
    }
    
     
    
/**
     * @Route("/actor/create", name="actor_create")
     */
    public function create( Request $request,
        LoggerInterface $appInfoLogger,
        FileService $fileService): Response{

       $actor = new Actor();
       
       // comprobacion de seguridad usando el voter
       $this->denyAccessUnlessGranted('create', $actor);
        
       $formulario = $this->createForm(ActorFormType::class, $actor);

       $formulario->handleRequest($request);        
       
       if($formulario->isSubmitted() && $formulario->isValid()){
           
      
           if($uploadedFile = $formulario->get('fotografia')->getData()){
               
               $fileService->setTargetDirectory($this->getParameter('app.photos.root'));
               
               $actor->setFotografia($fileService->upload($uploadedFile, true, 'photo_'));
           }
            
           
           $entityManager = $this->getDoctrine()->getManager();
           $entityManager->persist($actor);
           $entityManager->flush();
           
        $this->addFlash('success', 'Actor guardado con id'.$actor->getId());
        
        return $this->redirectToRoute('actor_show', ['id' => $actor->getId()]);
       
       }
    
       
       return $this->render('actor/create.html.twig', 
                            ['formulario' => $formulario->createView()]);     
    }
    
    
    /**
     * @Route("/actor/edit/{id}", name="actor_edit")
     */
    public function edit(Actor $actor, Request $request, FileService $fileService): Response{
        
        
        // comprobacion de seguridad usando el voter
        $this->denyAccessUnlessGranted('edit', $actor);
        
       
        $formulario = $this->createForm(ActorFormType::class, $actor);
        $fotografiaAntigua = $actor->getFotografia();
        
        $formulario->handleRequest($request);
        
        if($formulario->isSubmitted() && $formulario->isValid()){
            
            
            if($uploadedFile = $uploadedFile = $formulario->get('fotografia')->getData()){
                
                $fileService->setTargetDirectory($this->getParameter('app.photos.root'));
                
                $actor->setFotografia($fileService->replace(
                    $uploadedFile,
                    $fotografiaAntigua,
                    true,
                    'photo_'
                    ));
                
                
            }else{
                $actor->setFotografia($fotografiaAntigua);
            }
                     
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            
            $this->addFlash('success', 'Actor actualizado correctamente.');
            
            
            // crea el Form Type para añadir pelicula
            // los datos iran a la URL/actor/addActor/{idactor}
            $formularioAddPelicula = $this->createForm(ActorAddPeliculaFormType::class, NULL, [
                
                'action' => $this->generateURL('actor_add_pelicula', ['id'=>$actor->getId()])
            ]);
            
          
            
            return $this->redirectToRoute('actor_show', ['id' => $actor->getId()]);            
        }
        
        
        return $this->render('actor/edit.html.twig',
            ['formulario' => $formulario->createView(),
              // "formularioAddpelicula"=> $formularioAddpelicula,
            "actor" => $actor
            ]);
    }
    
       
    
    /**
     * @Route("/actor/delete/{id}", name="actor_delete")
     */
    public function delete(Actor $actor, Request $request): Response
    {
        
        // comprobacion de seguridad usando el voter
        $this->denyAccessUnlessGranted('delete', $actor);
     
   
        $formulario = $this->createForm(ActorDeleteFormType::class, $actor);
        $formulario ->handleRequest($request);
        
        if($formulario->isSubmitted() && $formulario->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($actor);
            $entityManager->flush();
            
            if($actor->getFotografia()){
                $fileService->setTargetDirectory($this->getParameter('app.photos.root'))
                ->remove($actor->getFotografia());
            }
            
            $this->addFlash('success', 'Actor eliminado correctamente.');
            
            return $this->redirectToRoute('actor_list');
        }
        
        return $this->render("actor/delete.html.twig",[
            "formulario"=>$formulario->createView(),
            "actor" => $actor
         ]);
        
    }
    

    /**
     * @Route(
     *       "/actor/deleteimage/{id<\d+>}",
     *       name="actor_delete_photo")
     * )
     */
    
    public function deletePhoto(
        Actor $actor, 
        FileService $fileService,
        ActorRepository $actorRepository,
        Request $request): Response
    {
        
        // comprobacion de seguridad usando el voter
        $this->denyAccessUnlessGranted('edit', $actor);
        
        
        if($fotografia = $actor->getFotografia()){
            
            $fileService->setTargetDirectory($this->getParameter('app.photos.root'))
            ->remove($fotografia);
            
            
            $actor->setFotografia(NULL);
            $actorRepository->add($actor, true);
            
            $this->addFlash('success', 'La fotografia de '.$actor->getNombre().'fue borrada');
            
            
        }
        
        return $this->redirectToRoute('actor_edit', [
            'id' => $actor->getId()
        ]);
    }
    
    
    
    /**
     * @Route("/actor/{id<\d+>}", name="actor_show")
     */
    public function show(Actor $actor): Response
    {
        
            return $this->render("actor/show.html.twig", ["actor"=>$actor]);
                 
   }
   
   
   }

   
  
