<?php

namespace App\Controller;

use App\Entity\Job;
use App\Form\JobType;
use App\Repository\JobRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/jobs", name="jobs_")
 */
class JobController extends AbstractController
{
    /**
     * @Route("", name="homepage")
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param JobRepository $jobRepository
     * @return Response
     */
    public function index(Request $request, PaginatorInterface $paginator, JobRepository $jobRepository)
    {
        $jobs = $jobRepository->findAll();
        $pagination = $this->setPagination($request,$paginator,$jobs);

        return $this->render('job/jobs.html.twig', [
            'pagination_jobs' => $pagination,
        ]);
    }

    /**
     * @Route("/add", name="add")
     * @param Request $request
     * @return Response
     */
    public function add(Request $request){

        $job = (new Job());
        $form = $this->createForm(JobType::class, $job);
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($job);
            $em->flush();
            $this->addFlash('success',
                $this->renderView('job/job_form_success_flash.html.twig')
            );

            return $this->redirectToRoute('jobs_add');
        }

        return $this->render('job/job_form.html.twig', [
            "form"=> $form->createView(),
            "app_title"=> 'Nouveau Métier'
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     * @param Request $request
     * @param int $id
     * @param JobRepository $jobRepository
     * @return Response
     */
    public function edit(Request $request, int $id, JobRepository $jobRepository){
        $job = $jobRepository->find([ 'id'=> $id ]);
        if( !is_null($job) && $job instanceof Job ) {
            $form = $this->createForm(JobType::class, $job);
            $form->handleRequest($request);

            if( $form->isSubmitted() && $form->isValid() ) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($job);
                $em->flush();
                $this->addFlash('success','Modification avec succées !');
                return $this->redirectToRoute('jobs_edit', [
                    'id'=> $id
                ]);
            }

            return $this->render('job/job_form.html.twig', [
                "form"=> $form->createView(),
                "job"=> $job,
                "app_title"=> 'Modification du métier ' . $job->getNom()
            ]);

        } else {
            return $this->redirectToRoute('main_dashboard');
        }
    }

    /**
     * @Route("/remove/{id}", name="remove")
     * @param Request $request
     * @param int $id
     * @param JobRepository $jobRepository
     * @return Response
     */
    public function remove(Request $request, int $id, JobRepository $jobRepository){
        $job = $jobRepository->find(['id' => $id]);

        if( !is_null($job) && $job instanceof Job && sizeof($job->getUsers()) === 0)
        {
            $em = $this->getDoctrine()->getManager();
            $em->remove($job);
            $em->flush();
        }
        return $this->redirectToRoute('jobs_homepage');
    }


    private function setPagination(Request $request,PaginatorInterface $paginator,Array $array)
    {
        return $pagination = $paginator->paginate($array, $request->query->getInt('page',1), 10);
    }
}
