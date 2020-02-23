<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\UserProjectRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="main_")
 */
class MainController extends AbstractController
{
    /**
     * @Route("", name="homepage")
     * @return Response
     */
    public function home(): Response
    {
        $currentUser = $this->getUser();
        if($currentUser instanceof User) {
            return $this->redirectToRoute('main_dashboard');
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    /**
     * @Route("/dashboard", name="dashboard")
     * @param ProjectRepository $projectRepository
     * @param UserRepository $userRepository
     * @param UserProjectRepository $userProjectRepository
     * @return Response
     */
    public function dashBoard(ProjectRepository $projectRepository, UserRepository $userRepository, UserProjectRepository $userProjectRepository): Response
    {
        $projects = $projectRepository->findAll();
        $nbEmployees = $userRepository->countEmployee();
        $userProjects = $userProjectRepository->findAll();
        $topEmployee = $userRepository->getTopEmployee();

        return $this->render('dashboard/main_dashboard.html.twig',
            [ 'projects'=> $projects, 'nb_employees'=> $nbEmployees, 'user_projects'=> $userProjects, 'top_employee'=> $topEmployee ]);
    }


}
