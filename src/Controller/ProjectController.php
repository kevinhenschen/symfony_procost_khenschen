<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectDeliveryType;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use DateTime;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/projects", name="projects_")
 */
class ProjectController extends AbstractController
{
    /**
     * @Route("", name="homepage")
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param ProjectRepository $projectRepository
     * @return Response
     */
    public function index(Request $request, PaginatorInterface $paginator, ProjectRepository $projectRepository)
    {
        $projects = $projectRepository->findAll();
        $pagination = $this->setPagination($request,$paginator,$projects);

        return $this->render('projects/projects.html.twig', [
            'pagination_projects' => $pagination,
        ]);
    }

    /**
     * @Route("/details/{id}", name="details")
     * @param Request $request
     * @param int $id
     * @param PaginatorInterface $paginator
     * @param ProjectRepository $projectRepository
     * @return Response
     * @throws Exception
     */
    public function details(Request $request, int $id, PaginatorInterface $paginator, ProjectRepository $projectRepository)
    {
        $project = $projectRepository->find( ['id'=>$id] );

        if( is_null($project) || !($project instanceof Project) ) {

            return $this->redirectToRoute('main_dashboard');
        } else {

            if( $this->isGranted('ROLE_MANAGER') ) {

                $form = $this->createForm(ProjectDeliveryType::class, $project);
                $form->handleRequest($request);

                if( $form->isSubmitted() && $form->isValid() ) {
                    $project->setDeliveredOn(new DateTime());
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($project);
                    $em->flush();
                    return $this->redirectToRoute('projects_details',[
                        'id'=> $id
                    ]);
                }
                $form = $form->createView();
            }

            $userProjects = $project->getUserProjects()->getValues();
            $pagination = $this->setPagination($request,$paginator,$userProjects);

            return $this->render('projects/project_details.html.twig', [
                'form' => $form ?? null,
                'project' => $project,
                'pagination_user_project' => $pagination
            ]);
        }
    }

    /**
     * @Route("/add", name="add")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function add(Request $request){

        $project = (new Project());
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            $project->setCreatedAt(new DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();
            $this->addFlash('success',
                $this->renderView('projects/project_form_success_flash.html.twig', [
                    'project'=> $project,
                    'message'=> 'ajoutée à la liste !'
                ])
            );

            return $this->redirectToRoute('projects_add');
        }

        return $this->render('projects/project_form.html.twig', [
            "form"=> $form->createView(),
            "app_title"=> 'Nouveau Projet'
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     * @param Request $request
     * @param int $id
     * @param ProjectRepository $projectRepository
     * @return Response
     */
    public function edit(Request $request, int $id, ProjectRepository $projectRepository){
        $project = $projectRepository->find([ 'id'=> $id ]);
        if( !is_null($project) && $project instanceof Project ) {
            $form = $this->createForm(ProjectType::class, $project);
            $form->handleRequest($request);

            if( $form->isSubmitted() && $form->isValid() ) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($project);
                $em->flush();
                $this->addFlash('success',
                    $this->renderView('projects/project_form_success_flash.html.twig', [
                    'project'=> $project,
                    'message'=> 'modifiée avec succées !'
                    ])
                );

                return $this->redirectToRoute('projects_edit', [
                    'id'=> $id
                ]);
            }

            return $this->render('projects/project_form.html.twig', [
                "form"=> $form->createView(),
                "app_title"=> 'Modification du projet ' . $project->getNom()
            ]);

        } else {
            return $this->redirectToRoute('main_dashboard');
        }
    }


    private function setPagination(Request $request,PaginatorInterface $paginator,Array $array)
    {
        return $pagination = $paginator->paginate($array, $request->query->getInt('page',1), 10);
    }
}
