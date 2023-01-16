<?php
namespace App\Service;

class MovieQuoteService{
    // método que retorna una frase aleatoria de película
    public function random(): string{
        // las mapeo en un array para el ejemplo, las podríamos
        // tener también en BDD.
        $movieQuotes = [
            'Francamente, querida, me importa un bledo.',
            'Le haré una oferta que no podrá rechazar.',
            'Que la Fuerza te acompañe.',
            '¿Hablas conmigo?',
            '¡Me encanta el olor del napalm por la mañana!',
            'Louis, creo que éste es el inicio de una hermosa amistad.',
            'Bond. James Bond.',
            'Bueno, nadie es perfecto.'
        ];
        
        return $movieQuotes[array_rand($movieQuotes)];
    } 
}







