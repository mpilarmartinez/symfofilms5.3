<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;
use App\Entity\Pelicula;
use App\Form\PeliculaFormType;
use App\Form\PeliculaDeleteFormType;
use Psr\Log\LoggerInterface;
use App\Service\FileService;
use App\Repository\PeliculaRepository;
use App\Service\PaginatorService;
use App\Form\SearchFormType;
use App\Service\SimpleSearchService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use App\Controller\ActorController;
use App\Entity\Actor;
use App\Form\PeliculaAddActorFormType;

class PeliculaController extends AbstractController
{

    /**
     * @Route(
     * "/peliculas/{pagina}",
     * defaults={"pagina":1},
     *  name="pelicula_list"
     *  )
     */
    public function index(int $pagina, PaginatorService $paginator): Response{
        
        $paginator->setEntityType('App\Entity\Pelicula');
        
        $peliculas = $paginator->findAllEntities($pagina);
              
        return $this->render("pelicula/list.html.twig",
        ['peliculas' => $peliculas,
        'paginator'  => $paginator
    ]);
       
}
    
     /**
     * @Route("/pelicula/create", name="pelicula_create"
     * )
     */

    public function create(
        Request $request, 
        LoggerInterface $appInfoLogger, 
        FileService $fileService
    ): Response{

       $peli = new Pelicula(); // creando el objeto de tipo Pelicula
       
       // comprobacion de seguridad usando el voter
       $this->denyAccessUnlessGranted('create', $peli);
        
       $formulario = $this->createForm(PeliculaFormType::class, $peli);

       $formulario->handleRequest($request);        
       
       if($formulario->isSubmitted() && $formulario->isValid()){
                  
           if($uploadedFile = $formulario->get('caratula')->getData()){
               
               $fileService->setTargetDirectory($this->getParameter('app.covers.root'));
               
               $peli->setCaratula($fileService->upload($uploadedFile, true, 'cover_'));
           }
       
       $peli->setUser($this->getUser());
       
       // if($this->getuser())
       //   $this->getUser()->addPelicula($peli);
       
      
       $entityManager = $this->getDoctrine()->getManager();
       $entityManager->persist($peli);
       $entityManager->flush();
      
           
 
       $mensaje = 'Pelicula'.$peli->getTitulo().'guardada con id'.$peli->getId();
       $this->addFlash('success', $mensaje);
       $appInfoLogger->info($mensaje);
        
       return $this->redirectToRoute('pelicula_show', ['id' => $peli->getId()]);
       
       }
    
       
       return $this->render('pelicula/create.html.twig', 
                            ['formulario' => $formulario->createView()]);     
    }
    
    
    /**
     * @Route("/pelicula/edit/{id}", 
     * name="pelicula_edit")
     */
    
    public function edit(
        Pelicula $peli, 
        Request $request,
        FileService $fileService
    ): Response{
        
        
        // comprobacion de seguridad usando el voter
        $this->denyAccessUnlessGranted('edit', $peli);
     
        $formulario = $this->createForm(PeliculaFormType::class, $peli);
        $caratulaAntigua = $peli->getCaratula();
        
        $formulario->handleRequest($request);
        
        if($formulario->isSubmitted() && $formulario->isValid()){
            
            if($uploadedFile = $uploadedFile = $formulario->get('caratula')->getData()){
                
                $fileService->setTargetDirectory($this->getParameter('app.covers.root'));
             
                $peli->setCaratula($fileService->replace(
                    $uploadedFile,
                    $caratulaAntigua,
                    true,
                    'cover_'
                ));
                
                
            }else{
                $peli->setCaratula($caratulaAntigua);
            }
                
                     
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            
            $this->addFlash('success', 'Pelicula actualizada correctamente.');
            
            return $this->redirectToRoute('pelicula_edit', ['id' => $peli->getId()]);            
        }
        
        
        // crea el Form Type para añadir actor
        // los datos iran a la URL /pelicula/addactor/{idpelicula}
        $formularioAddActor = $this->createForm(PeliculaAddActorFormType::class, NULL, [
            'action' => $this->generateUrl('pelicula_add_actor', ['id'=>$peli->getId()])
        ]);
        
       
        return $this->renderForm('pelicula/edit.html.twig',
            ['formulario' => $formulario,
            "formularioAddActor" => $formularioAddActor,
            "pelicula" => $peli
            ]);
    }
    

    /**
     * @Route("/pelicula/delete/{id}", name="pelicula_delete")
     */
    public function delete(
        Pelicula $peli, 
        FileService $fileService,
        LoggerInterface $appInfoLogger,
        Request $request
    ): Response
    {
        
        
        // comprobacion de seguridad usando el voter
        $this->denyAccessUnlessGranted('delete', $peli);
        
        $formulario = $this->createForm(PeliculaDeleteFormType::class, $peli);
        $formulario ->handleRequest($request);
        
        if($formulario->isSubmitted() && $formulario->isValid()) {
        
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($peli);
            $entityManager->flush();
            
            if($peli->getCaratula()){
                $fileService->setTargetDirectory($this->getParameter('app.covers.root'))
                            ->remove($peli->getCaratula());
            }
            
            
            $this->addFlash('success', 'Pelicula eliminada correctamente.');
            
            return $this->redirectToRoute('pelicula_list');
        }
        
        return $this->render("pelicula/delete.html.twig",[
            "formulario"=>$formulario->createView(),
            "pelicula" => $peli
         ]);
        
    }
    
    
    /**
     * @Route(
     *  "/pelicula/addactor/{id<\d+>}", 
     *  name="pelicula_add_actor", 
     *  methods={"POST"}
     *  )
     */
    
    public function addActor(
        Pelicula $pelicula,
        EntityManagerInterface $em,
        LoggerInterface $appInfoLogger,
        Request $request)
        {
        
            
        // comprobacion de seguridad usando el voter
        $this->denyAccessUnlessGranted('edit', $pelicula);
                     
        // tomamos el valor que llega del formulario
        $formularioAddActor = $this->createForm(PeliculaAddActorFormType::class);
        $formularioAddActor ->handleRequest($request);
        $actor = $formularioAddActor->getData()['actor'];
        
        $pelicula->addActore($actor); // lo añade a la pelicula
        $em->flush(); // aplica los cambios en la BDD
        
        // flashea y loguea mensajes
        $mensaje = 'Actor'.$actor->getNombre();
        $mensaje .= 'añadido a '.$pelicula->getTitulo().'correctamente.';
        $this->addFlash('success', $mensaje);
        $appInfoLogger->info($mensaje);
            
         // redirecciona de nuevo a la vista de edicion de la peli
         return $this->redirectToRoute('pelicula_edit',['id' => $pelicula->getId()]);
         
    }
            

    
    /**
     * @Route("/pelicula/borrarcaratula/{id}", name="pelicula_delete_cover")

     */

    
    public function deleteCover(
        Pelicula $peli,
        FileService $fileService,
        PeliculaRepository $peliculaRepository,
        Request $request
        ): Response
        {
        
            // comprobacion de seguridad usando el voter
            $this->denyAccessUnlessGranted('edit', $pelicula);
        
            if($caratula = $peli->getCaratula()){
                
                $fileService->setTargetDirectory($this->getParameter('app.covers.root'))
                            ->remove($caratula);
         
            
            $peli->setCaratula(NULL);
            $peliculaRepository->add($peli, true);
            
            $this->addFlash('success', 'La caratula de '.$peli->getTitulo().'fue borrada');
            
            
    }
              
            return $this->redirectToRoute('pelicula_edit', [
               'id' => $peli->getId()
            ]);           
    }
    
    
    /**
     * @Route(
     *      "/pelicula/show/{id}", 
     *      name="pelicula_show",
     *      methods={"GET"},
     *      requirements={"id"="\d+"}
     *      )
     */
    public function show(Pelicula $peli): Response
    {
        
            return $this->render("pelicula/show.html.twig", ["pelicula"=>$peli]);
                 
   }
  
   
   /**
    * @Route("/pelicula/search",
    *  name="pelicula_search",
    *  methods={"GET","POST"}
    *  )
    */
   
   public function search(Request $request, SimpleSearchService $busqueda): Response{
                      
           $formulario = $this->createForm(SearchFormType::class, $busqueda, [
               'field_choices' => [
                   'Titulo' => 'titulo',
                   'Director' => 'director',
                   'Genero' => 'genero',
                   'Sinopsis' => 'sinopsis'
                 ],
               'order_choices' => [
                   'ID' => 'id',
                   'Titulo' => 'titulo',
                   'Director' => 'director',
                   'Genero' => 'genero',
                  ]
               
               ]);
           
           $formulario->get('campo')->setData($busqueda->campo);
           $formulario->get('orden')->setData($busqueda->orden);
           
           $formulario->handleRequest($request);
           
           $pelis = $busqueda->search('App\Entity\Pelicula');
           
           return $this->renderForm("pelicula/buscar.html.twig", [
               "formulario"=>$formulario,
               "peliculas" => $pelis
           ]);
}




/**
 * @Route(
 *  "/pelicula/removeactor/{pelicula<\d+>}/{actor<\d+>}",
 *  name="pelicula_remove_actor",
 *  methods={"GET"}
 *  )
 */

public function removeActor(
    Pelicula $pelicula, 
    Actor $actor,
    EntityManagerInterface $em)
{
    
    // comprobacion de seguridad usando el voter
    $this->denyAccessUnlessGranted('edit', $pelicula);
    
    
    $pelicula->removeActore($actor); // desvincular el actor
    $em->flush(); // aplica los cambios 
    
    // flashea  mensajes
    $mensaje = 'Actor'.$actor->getNombre();
    $mensaje .= 'eliminado de '.$pelicula->getTitulo().'correctamente.';
    $this->addFlash('success', $mensaje);
    
    // redirecciona 
    return $this->redirectToRoute('pelicula_edit',['id' => $pelicula->getId()]);
    
}

}
