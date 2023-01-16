<?php

namespace App\Security;

use App\Entity\Pelicula; 
use App\Entity\User; 
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;


class PeliculaVoter extends Voter
{
    private $security, $operaciones;
  
    public function __construct(Security $security) 
    {
    // objeto security, lo necesitamos para las comprobaciones
    // que requieran evaluar roles      
        $this->security = $security;
    
    // lista de operaciones que podra evaluar el voter
    // podermos poner cualquier cosa (search, addActor...)
        $this->operaciones = ['create','edit','delete'];
    }

    
    // debemos implementar los metodos: supportd() y voteOnAttribute()
    
    // supports() comprueba si atributo y sujeto estan soportados por el voter
    // si no lo estan dbemos retornar false, que es considerdo como
    // como que el voter "se abstiene" porque no es capaz de evaular esa autorizacion
    
    protected function supports(string $attribute, $subject): bool{
        
        // si la operacion (atributo) no esta soportada, retornamos false
        if(!in_array($attribute, $this->operaciones))
            return false;
        
      // si no nos pasan una pelicula (sujeto) retorna false
        if(!$subject instanceof Pelicula)
            return false;
        return true; // si todo es correcto retorna true
        
    }
    
    // El metodo voteOnAttribute() realizara una comprobacion sobre el atributo,
    // sujeto y usuario. Retornara true si el voter autoriza o false si no autoriza
    protected function voteOnAttribute(string $attribute,
                            $pelicula, TokenInterface $token): bool {
        
           $user = $token->getUser(); // recupera el usuario
           
           if (!$user instanceof User)  // si el usuario no esta identificado
               return false;
           
    // DISPATCHER: llamamos al metodo adecuado segun la comprobacion a ahcer.
    // Los metodos canEdit(), canCreate() y canDelete los definiremos debajo.
    // Preparamos el nombre a partir del atributo, p.e: view --> canView.
    $method = 'can'.ucfirst($attribute);
    
    return $this->$method($pelicula, $user);
                                                             
    }
    
    
    // METODOS PARA LAS DISTINTAS COMPROBACIONES
    // deben llamarse canOperacion(), donde las operaciones son las de la lista
    
    // todos los usuarios identificados pueden crear
    private function canCreate(Pelicula $pelicula, User $user): bool {
        return true;
    }
    
  // solo el autor o los editores pueden editar una pelicula
  // tambien usaremos este metodo para comprobar si puede añadir actor,
  // eliminar actor o eliminar la imagen
    private function canEdit(Pelicula $pelicula, User $user): bool {
        return $user === $pelicula->getUser() || $this->security->isGranted('ROLE_EDITOR');
    }
    
    // si puede editar, puede eliminar la peli
    private function canDelete(Pelicula $pelicula, User $user){
        return $this->canEdit($pelicula, $user);
    }
    
    
}
