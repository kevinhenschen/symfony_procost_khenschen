<?php

namespace App\DataFixtures;

use App\Entity\Job;
use App\Entity\Project;
use App\Entity\User;
use App\Entity\UserProject;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;
use MongoDB\Driver\Manager;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $nbUsers = 20;
    private $nbJobs = 5;
    private $nbProjects = 60;
    private $nbUserProjects = 200;

    /**
     * @var Faker\Factory $faker
     * @var PasswordEncoderInterface $encoder
     * @var ObjectManager $manager
     */

    private $faker;
    private $encoder;
    private $manager;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->faker = Faker\Factory::create('fr_FR');
        $this->encoder = $encoder;
    }


    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadJob();
        $this->loadUsers();
        $this->loadProjects();
        $this->loadUserProject();

        $this->manager->flush();
    }

    private function loadJob()
    {
        for($i=0; $i < $this->nbJobs; $i++){
            $job = (new Job())
                ->setNom($this->faker->jobTitle);
            $this->manager->persist($job);
            $this->addReference('job'.$i,$job);
        }
    }

    private function loadUsers()
    {
        for($i=0; $i < $this->nbUsers; $i++){
            $user = (new User())
                ->setNom($this->faker->lastName)
                ->setPrenom($this->faker->firstName)
                ->setEmail($this->faker->safeEmail)
                ->setCoutHoraire($this->faker->numberBetween(1000,2000))
                ->setDateEmbauche(new DateTime());

            if($i === 0 ){
                $user
                    ->setNom('Henschen')
                    ->setPrenom('Kévin')
                    ->setEmail('rayzox57@gmail.com')
                    ->setRoles(['ROLE_MANAGER']);
            }

            /**
             * @var Job $job
             */
            $job = $this->getReference('job' . $this->faker->numberBetween(0,$this->nbJobs-1));
            $user->setJob($job);

            $plainPassowrd = '02071996';
            $encoded = $this->encoder->encodePassword($user,$plainPassowrd);
            $user->setPassword($encoded);
            $this->manager->persist($user);
            $this->addReference('user'.$i,$user);

        }
    }

    private function loadProjects()
    {
        for($i=0; $i < $this->nbProjects; $i++){
            $j = $i + 1;
            $project = (new Project())
                ->setNom('Projet n°' . $j)
                ->setCreatedAt(new DateTime())
                ->setDeliveredOn($this->faker->numberBetween(0,1) == 1 ? $this->faker->dateTimeBetween('now','+60 days') : null)
                ->setDescription($this->faker->realText(60))
                ->setPrice($this->faker->numberBetween(20000,100000));
            $this->manager->persist($project);
            $this->addReference('project'.$i,$project);
        }
    }

    private function loadUserProject()
    {
        for($i=0; $i < $this->nbUserProjects; $i++){

            /**
             * @var Project $project
             */
            $project = $this->getReference('project' . $this->faker->numberBetween(0,$this->nbProjects - 1));

            /**
             * @var User $user
             */
            $user = $this->getReference('user' . $this->faker->numberBetween(0,$this->nbUsers - 1));

            $userProject = (new UserProject())
                ->setCreatedAt($this->faker->dateTimeBetween('now',$project->getDeliveredOn() !== null ? 'now' : '+ 5days'))
                ->setProject($project)
                ->setUser($user)
                ->setTimeSpent($this->faker->numberBetween(1,10));

            $this->manager->persist($userProject);
        }
    }

}
