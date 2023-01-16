<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

use App\Repository\UserRepository;
use App\Entity\User;





#[AsCommand(
    name: 'app:create-user',
    description: 'Add a short description for your command',
)]


class CreateUserCommand extends Command
{
        
    protected static $defaultName = 'app:create-user';  // nombre del comando
    
    // PROPIEDADES
    private $em;
    private $userRepository;
    private $userPasswordHasher;
    
    
    // CONSTRUCTOR
    public function __construct(
        EntityManagerInterface $em, 
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ){
    
        parent::__construct();
        
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
    }
    
    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email:')
            ->addArgument('displayname', InputArgument::REQUIRED, 'Nombre para mostrar:')
            ->addArgument('password', InputArgument::REQUIRED, 'Password:')
            ->addArgument('phone', InputArgument::REQUIRED, 'Phone:')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<fg=white;bg=black>Crear usuario</>');
        
        $email = $input->getArgument('email');  
        $password = $input->getArgument('password');
        $displayname = $input->getArgument('displayname');
        $phone = $input->getArgument('phone');
        
        if($this->userRepository->findOneBy(['email'=>$email])){
            $output->writeln("<error>El usuario con email $email ya ha sdo registrado
            anteriormente</error>");
            return Command::FAILURE;
        }

        
        $user = (new User())->setEmail($email)->setDisplayName($displayname);
        
        //$hashedPassword = $this->passwordHasher->setDisplayName($displayname);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        
        $this->em->persist($user);
        $this->em->flush();
        
        
        $output->writeln("<fg=white;bg=green>Usuario $email creado</>");
        return Command::SUCCESS;
    }
}
