<?php

namespace App\Security;

use App\Entity\Actor; 
use App\Entity\User; 
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;


class ActorVoter extends Voter
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
    // si no lo estan debemos retornar false, que es considerdo como
    // como que el voter "se abstiene" porque no es capaz de evaular esa autorizacion
    
    protected function supports(string $attribute, $subject): bool{
        
        // si la operacion (atributo) no esta soportada, retornamos false
        if(!in_array($attribute, $this->operaciones))
            return false;
        
      // si no nos pasan un actor (sujeto) retorna false
        if(!$subject instanceof Actor)
            return false;
        return true; // si todo es correcto retorna true
        
    }
    
    // El metodo voteOnAttribute() realizara una comprobacion sobre el atributo,
    // sujeto y usuario. Retornara true si el voter autoriza o false si no autoriza
    protected function voteOnAttribute(string $attribute,
                            $actor, TokenInterface $token): bool {
        
           $user = $token->getUser(); // recupera el usuario
           
           if (!$user instanceof User)  // si el usuario no esta identificado
               return false;
           
    // DISPATCHER: llamamos al metodo adecuado segun la comprobacion a hacer.
    // Los metodos canEdit(), canCreate() y canDelete los definiremos debajo.
    // Preparamos el nombre a partir del atributo, p.e: view --> canView.
    $method = 'can'.ucfirst($attribute);
    
    return $this->$method($actor, $user);
                                                             
    }
    
    
    // METODOS PARA LAS DISTINTAS COMPROBACIONES
    // deben llamarse canOperacion(), donde las operaciones son las de la lista
    
    // todos los usuarios identificados pueden crear
    private function canCreate(Actor $actor, User $user): bool {
        return true;
    }
    
  // solo el autor o los editores pueden editar un actor
  // tambien usaremos este metodo para comprobar si puede añadir actor,
  // eliminar actor o eliminar la imagen
    private function canEdit(Actor $actor, User $user): bool {
        return true;
    }
    
    // si puede editar, puede eliminar el actor
    private function canDelete(Actor $actor, User $user){
        return $this->canEdit($actor, $user);
    }
    
    
}
